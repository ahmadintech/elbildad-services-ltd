<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Item;
use App\Services\Zoho\ZohoInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    protected ZohoInvoiceService $invoiceService;

    public function __construct(ZohoInvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        $authUser = auth()->user();
        $query = Invoice::with(['rfq', 'estimate', 'customer'])->orderBy('created_at', 'desc');

        if ($authUser->hasRole(['agent', 'super_agent'])) {
            $customerIds = \App\Models\Rfq::where('assigned_agent_id', $authUser->id)
                ->pluck('customer_id')
                ->filter()
                ->unique();

            $query->where(function ($q) use ($authUser, $customerIds) {
                $q->whereHas('rfq', function ($q) use ($authUser) {
                    $q->where('assigned_agent_id', $authUser->id);
                })->orWhere(function ($q) use ($customerIds) {
                    $q->whereNull('rfq_id')->whereIn('customer_id', $customerIds);
                });
            });
        }

        $invoices = $query->get();

        if (request()->wantsJson()) {
            return response()->json(['data' => $invoices]);
        }

        return Inertia::render('Admin/Finance/Invoices/Index', ['invoices' => $invoices]);
    }

    public function createFromRfq(Request $request, Rfq $rfq): RedirectResponse
    {
        $validated = $request->validate([
            'line_items' => 'required|array|min:1',
            'line_items.*.name' => 'required|string',
            'line_items.*.rate' => 'required|numeric|min:0',
            'line_items.*.quantity' => 'required|numeric|min:1',
            'line_items.*.description' => 'nullable|string',
            'line_items.*.zoho_item_id' => 'nullable|string',
        ]);

        $this->invoiceService->createInvoiceFromRfq($rfq, $validated['line_items']);

        return Redirect::back()->with('success', 'Zoho Invoice generated directly from RFQ.');
    }

    public function createFromEstimate(Estimate $estimate): RedirectResponse
    {
        $this->invoiceService->createInvoice($estimate);

        return Redirect::back()->with('success', 'Estimate converted to Zoho Invoice successfully.');
    }

    public function create()
    {
        $authUser = auth()->user();

        if ($authUser->hasRole(['agent', 'super_agent'])) {
            // Agents see only customers from their own assigned RFQs
            $customerIds = \App\Models\Rfq::where('assigned_agent_id', $authUser->id)
                ->pluck('customer_id')
                ->filter()
                ->unique();

            $customers = User::role('customer')
                ->whereIn('id', $customerIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } else {
            // Admins/owners see all customers
            $customers = User::role('customer')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        $items = Item::orderBy('name')->get();

        return Inertia::render('Admin/Finance/Invoices/Create', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.name' => 'required|string',
            'line_items.*.rate' => 'required|numeric|min:0',
            'line_items.*.quantity' => 'required|numeric|min:1',
            'line_items.*.description' => 'nullable|string',
            'line_items.*.zoho_item_id' => 'nullable|string',
        ]);

        $customer = User::findOrFail($validated['customer_id']);

        $this->invoiceService->createManualInvoice($customer, $validated['line_items'], [
            'date' => $validated['date'],
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? '',
        ]);

        return Redirect::route('finance.invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['rfq', 'estimate', 'customer', 'payments']);
        try {
            $zohoData = $this->invoiceService->getInvoice($invoice->zoho_invoice_id);
        } catch (\Exception $e) {
            $zohoData = [];
        }

        if (request()->wantsJson()) {
            return response()->json([
                'local' => $invoice,
                'zoho' => $zohoData,
            ]);
        }

        return Inertia::render('Admin/Finance/Invoices/Show', [
            'invoice' => $invoice,
            'zoho' => $zohoData,
        ]);
    }

    public function downloadPdf(Invoice $invoice)
    {
        try {
            $pdfContent = $this->invoiceService->getInvoicePdf($invoice->zoho_invoice_id);
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Invoice-' . ($invoice->invoice_number ?: $invoice->zoho_invoice_id) . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Failed to retrieve PDF: ' . $e->getMessage());
        }
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->sendInvoice($invoice->zoho_invoice_id);

        return Redirect::back()->with('success', 'Invoice sent and emailed to customer.');
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->voidInvoice($invoice->zoho_invoice_id);

        return Redirect::back()->with('success', 'Invoice voided successfully.');
    }

    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
            'payment_mode' => 'required|string',
        ]);

        $this->invoiceService->recordPayment(
            $invoice->zoho_invoice_id,
            (float)$validated['amount'],
            $validated['paid_at'],
            $validated['payment_mode']
        );

        return Redirect::back()->with('success', 'Payment logged and invoice balance updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        try {
            $client = app(\App\Services\Zoho\ZohoClient::class);
            $client->delete("/invoices/{$invoice->zoho_invoice_id}");
        } catch (\Exception $e) {
            // Ignore Zoho API failures on cascade deletion
        }

        $invoice->delete();

        return Redirect::back()->with('success', 'Invoice deleted.');
    }
}
