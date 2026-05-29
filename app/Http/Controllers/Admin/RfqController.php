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
        $rfqs = Rfq::with(['customer', 'assignedAgent'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rfq) {
                return [
                    'id' => $rfq->id,
                    'tracking_token' => $rfq->tracking_token,
                    'product_name' => $rfq->product_name,
                    'customer_name' => $rfq->customer ? $rfq->customer->name : 'Unknown',
                    'customer_email' => $rfq->customer ? $rfq->customer->email : null,
                    'status' => $rfq->status,
                    'assigned_agent_id' => $rfq->assigned_agent_id,
                    'assigned_agent_name' => $rfq->assignedAgent ? $rfq->assignedAgent->name : 'Unassigned',
                    'created_at' => $rfq->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $agents = User::role(['agent', 'super_agent'])->get(['id', 'name']);

        return Inertia::render('Admin/Rfqs/Index', [
            'rfqs' => $rfqs,
            'agents' => $agents
        ]);
    }

    public function show(Rfq $rfq)
    {
        $rfq->load(['customer', 'assignedAgent', 'category']);
        
        return Inertia::render('Admin/Rfqs/Show', [
            'rfq' => [
                'id' => $rfq->id,
                'tracking_token' => $rfq->tracking_token,
                'product_name' => $rfq->product_name,
                'specifications' => $rfq->specifications,
                'additional_requirements' => $rfq->additional_requirements,
                'quantity' => $rfq->quantity,
                'delivery_method' => $rfq->delivery_method,
                'target_price' => $rfq->target_price,
                'location' => $rfq->location,
                'company_name' => $rfq->company_name,
                'status' => $rfq->status,
                'ai_summary' => $rfq->ai_summary,
                'ai_suppliers' => $rfq->ai_suppliers,
                'claude_suppliers' => $rfq->claude_suppliers,
                'qwen_suppliers' => $rfq->qwen_suppliers,
                'best_supplier' => $rfq->best_supplier,
                'internal_matches' => $rfq->internal_matches,
                'image_url' => $rfq->image_url,
                'created_at' => $rfq->created_at->format('M d, Y H:i'),
                'customer' => $rfq->customer ? [
                    'name' => $rfq->customer->name,
                    'email' => $rfq->customer->email,
                    'phone' => $rfq->customer->whatsapp_number,
                ] : null,
                'assigned_agent' => $rfq->assignedAgent ? [
                    'id' => $rfq->assignedAgent->id,
                    'name' => $rfq->assignedAgent->name,
                ] : null,
                'category' => $rfq->category ? [
                    'name' => $rfq->category->name,
                ] : null,
            ]
        ]);
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
