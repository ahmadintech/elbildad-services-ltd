<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\RfqStatusEnum;
use App\Models\Rfq;
use App\Models\User;
use App\Models\AgentRfqAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RfqController extends Controller
{
    public function index()
    {
        $allRfqs = Rfq::with(['customer', 'assignedAgent', 'transactions' => fn($q) => $q->latest()])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rfq) {
                $latestTxn = $rfq->transactions->first();
                return [
                    'id'                   => $rfq->id,
                    'tracking_token'       => $rfq->tracking_token,
                    'product_name'         => $rfq->product_name,
                    'customer_name'        => $rfq->customer ? $rfq->customer->name : 'Unknown',
                    'customer_email'       => $rfq->customer ? $rfq->customer->email : null,
                    'status'               => $rfq->status->value,
                    'assigned_agent_id'    => $rfq->assigned_agent_id,
                    'assigned_agent_name'  => $rfq->assignedAgent ? $rfq->assignedAgent->name : 'Unassigned',
                    'created_at'           => $rfq->created_at->format('Y-m-d H:i:s'),
                    // Payment info for admin visibility
                    'payment_status'       => $latestTxn ? $latestTxn->status : 'none',
                    'payment_reference'    => $latestTxn ? $latestTxn->reference : null,
                    'payment_amount'       => $latestTxn ? $latestTxn->amount : null,
                    'payment_gateway'      => $latestTxn ? $latestTxn->gateway : null,
                    'is_paid'              => $latestTxn && $latestTxn->status === 'successful',
                ];
            });

        // Separate unpaid RFQs (awaiting commitment fee) from active ones
        $unpaidRfqs = $allRfqs->filter(fn($r) => $r['status'] === 'commitment_fee_pending')->values();
        $activeRfqs = $allRfqs->filter(fn($r) => $r['status'] !== 'commitment_fee_pending')->values();

        $agents = User::role(['agent', 'super_agent'])->get(['id', 'name']);

        return Inertia::render('Admin/Rfqs/Index', [
            'rfqs'        => $activeRfqs,
            'unpaidRfqs'  => $unpaidRfqs,
            'agents'      => $agents,
        ]);
    }

    public function show(Rfq $rfq)
    {
        $rfq->load(['customer', 'assignedAgent', 'category', 'transactions' => fn($q) => $q->latest()]);
        $latestTxn = $rfq->transactions->first();

        return Inertia::render('Admin/Rfqs/Show', [
            'rfq' => [
                'id'                    => $rfq->id,
                'tracking_token'        => $rfq->tracking_token,
                'product_name'          => $rfq->product_name,
                'specifications'        => $rfq->specifications,
                'additional_requirements' => $rfq->additional_requirements,
                'quantity'              => $rfq->quantity,
                'delivery_method'       => $rfq->delivery_method,
                'target_price'          => $rfq->target_price,
                'location'              => $rfq->location,
                'company_name'          => $rfq->company_name,
                'status'                => $rfq->status->value,
                'ai_summary'            => $rfq->ai_summary,
                'ai_suppliers'          => $rfq->ai_suppliers,
                'claude_suppliers'      => $rfq->claude_suppliers,
                'qwen_suppliers'        => $rfq->qwen_suppliers,
                'best_supplier'         => $rfq->best_supplier,
                'internal_matches'      => $rfq->internal_matches,
                'image_url'             => $rfq->image_url,
                'created_at'            => $rfq->created_at->format('M d, Y H:i'),
                'customer' => $rfq->customer ? [
                    'name'  => $rfq->customer->name,
                    'email' => $rfq->customer->email,
                    'phone' => $rfq->customer->whatsapp_number,
                ] : null,
                'assigned_agent' => $rfq->assignedAgent ? [
                    'id'   => $rfq->assignedAgent->id,
                    'name' => $rfq->assignedAgent->name,
                ] : null,
                'category' => $rfq->category ? [
                    'name' => $rfq->category->name,
                ] : null,
                // Payment details for admin
                'payment' => $latestTxn ? [
                    'status'    => $latestTxn->status,
                    'reference' => $latestTxn->reference,
                    'amount'    => $latestTxn->amount,
                    'gateway'   => $latestTxn->gateway,
                    'paid_at'   => $latestTxn->updated_at->format('M d, Y H:i'),
                ] : null,
            ]
        ]);
    }

    public function verifyPayment(Rfq $rfq)
    {
        // Must be in commitment_fee_pending to be verified manually this way
        if ($rfq->status !== RfqStatusEnum::COMMITMENT_FEE_PENDING) {
            return redirect()->back()->with('error', 'RFQ is not pending commitment fee.');
        }

        $transaction = $rfq->transactions()->where('status', 'pending')->latest()->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'successful',
                'metadata' => array_merge($transaction->metadata ?? [], ['manual_verification' => true])
            ]);
        } else {
            $rfq->transactions()->create([
                'user_id' => $rfq->customer_id,
                'reference' => 'RFQ_MANUAL_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'amount' => 5000.00,
                'currency' => 'NGN',
                'status' => 'successful',
                'gateway' => 'manual',
            ]);
        }

        $rfq->update(['status' => RfqStatusEnum::PENDING->value]);

        \App\Jobs\ProcessRfqWithAI::dispatch($rfq);

        return redirect()->back()->with('success', 'Payment verified manually. RFQ moved to Active and AI processing started.');
    }

    public function checkZainpay(Rfq $rfq, \App\Services\Zainpay\ZainpayService $zainpay)
    {
        if ($rfq->status !== RfqStatusEnum::COMMITMENT_FEE_PENDING) {
            return redirect()->back()->with('error', 'RFQ is not pending commitment fee.');
        }

        $transaction = $rfq->transactions()->where('status', 'pending')->latest()->first();

        if (!$transaction) {
            return redirect()->back()->with('error', 'No pending transaction found to verify.');
        }

        $verification = $zainpay->verifyDeposit($transaction->reference);

        if ($verification['success'] ?? false) {
            $transaction->update([
                'status' => 'successful',
                'metadata' => array_merge($transaction->metadata ?? [], $verification['data'] ?? [])
            ]);

            $rfq->update(['status' => RfqStatusEnum::PENDING->value]);

            \App\Jobs\ProcessRfqWithAI::dispatch($rfq);

            $rfq->load('customer');
            if ($rfq->customer && $rfq->customer->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($rfq->customer->email)
                        ->send(new \App\Mail\PaymentConfirmedEmail($rfq, $transaction));
                } catch (\Exception $e) {}
            }

            return redirect()->back()->with('success', 'Zainpay confirmed payment! RFQ is now Active.');
        }

        return redirect()->back()->with('error', 'Zainpay reports this transaction is still pending or not found.');
    }

    public function update(Request $request, Rfq $rfq)
    {
        $request->validate([
            'assigned_agent_id' => 'required|exists:users,id'
        ]);

        // Deactivate old assignment
        if ($rfq->assigned_agent_id) {
            AgentRfqAssignment::where('rfq_id', $rfq->id)
                ->where('agent_id', $rfq->assigned_agent_id)
                ->update(['is_active' => false]);
        }

        // Assign new
        AgentRfqAssignment::create([
            'rfq_id' => $rfq->id,
            'agent_id' => $request->assigned_agent_id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        $rfq->update([
            'assigned_agent_id' => $request->assigned_agent_id,
            'status' => RfqStatusEnum::ASSIGNED,
        ]);

        return redirect()->back()->with('success', 'RFQ re-assigned successfully.');
    }

    public function destroy(Rfq $rfq)
    {
        $rfq->delete();
        return redirect()->back()->with('success', 'RFQ deleted successfully.');
    }
}
