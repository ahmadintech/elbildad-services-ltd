<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Zoho\ZohoClient;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class ReportController extends Controller
{
    protected ZohoClient $client;

    public function __construct(ZohoClient $client)
    {
        $this->client = $client;
    }

    public function index()
    {
        return Inertia::render('Admin/Finance/Reports/Index');
    }

    public function invoiceSummary(): JsonResponse
    {
        $invoices = \App\Models\Invoice::all();

        return response()->json([
            'invoice_summary' => [
                'draft_count' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::DRAFT)->count(),
                'sent_count' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::SENT)->count(),
                'partially_paid_count' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::PARTIALLY_PAID)->count(),
                'paid_count' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::PAID)->count(),
                'overdue_count' => $invoices->filter(fn($i) => $i->due_date && $i->due_date->isPast() && in_array($i->status, [\App\Enums\InvoiceStatusEnum::SENT, \App\Enums\InvoiceStatusEnum::PARTIALLY_PAID]))->count(),
                
                'draft_amount' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::DRAFT)->sum('total'),
                'sent_amount' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::SENT)->sum('total'),
                'partially_paid_amount' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::PARTIALLY_PAID)->sum('total'),
                'paid_amount' => $invoices->where('status', \App\Enums\InvoiceStatusEnum::PAID)->sum('total'),
                'overdue_amount' => $invoices->filter(fn($i) => $i->due_date && $i->due_date->isPast() && in_array($i->status, [\App\Enums\InvoiceStatusEnum::SENT, \App\Enums\InvoiceStatusEnum::PARTIALLY_PAID]))->sum('balance_due'),

                'total_invoiced' => $invoices->where('status', '!=', \App\Enums\InvoiceStatusEnum::VOID)->sum('total'),
                'total_payments' => \App\Models\Payment::sum('amount'),
                'total_receivables' => $invoices->sum('balance_due'),
            ]
        ]);
    }

    public function paymentReceived(): JsonResponse
    {
        $payments = \App\Models\Payment::with(['invoice.customer'])->latest()->get();
        
        $formatted = $payments->map(function ($p) {
            return [
                'payment_id' => $p->id,
                'date' => $p->paid_at ? $p->paid_at->format('Y-m-d') : $p->created_at->format('Y-m-d'),
                'customer_name' => $p->invoice?->customer?->name ?? 'Unknown',
                'invoice_number' => $p->invoice?->invoice_number ?? 'Unknown',
                'payment_mode' => $p->payment_mode,
                'reference_number' => $p->zoho_payment_id ?? $p->id,
                'amount' => $p->amount,
            ];
        });

        return response()->json(['payments_received' => $formatted]);
    }

    public function outstandingReceivables(): JsonResponse
    {
        $invoices = \App\Models\Invoice::with('customer')
            ->whereIn('status', [\App\Enums\InvoiceStatusEnum::SENT, \App\Enums\InvoiceStatusEnum::PARTIALLY_PAID, \App\Enums\InvoiceStatusEnum::DRAFT])
            ->get();

        $receivables = $invoices->groupBy('customer_id')->map(function ($group) {
            $customer = $group->first()->customer;
            
            $balance = $group->sum('balance_due');
            $overdue_balance = $group->filter(fn($i) => $i->due_date && $i->due_date->isPast() && in_array($i->status, [\App\Enums\InvoiceStatusEnum::SENT, \App\Enums\InvoiceStatusEnum::PARTIALLY_PAID]))->sum('balance_due');
            $draft_balance = $group->where('status', \App\Enums\InvoiceStatusEnum::DRAFT)->sum('total');
            
            if ($balance == 0 && $draft_balance == 0) return null;
            
            return [
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->name ?? 'Unknown',
                'invoice_count' => $group->count(),
                'balance' => $balance,
                'overdue_balance' => $overdue_balance,
                'draft_balance' => $draft_balance,
            ];
        })->filter()->values();

        return response()->json(['receivables' => $receivables]);
    }
}
