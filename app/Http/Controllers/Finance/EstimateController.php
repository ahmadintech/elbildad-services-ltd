<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Models\Estimate;
use App\Models\User;
use App\Models\Item;
use App\Services\Zoho\ZohoEstimateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class EstimateController extends Controller
{
    protected ZohoEstimateService $estimateService;

    public function __construct(ZohoEstimateService $estimateService)
    {
        $this->estimateService = $estimateService;
    }

    public function index()
    {
        $authUser = auth()->user();
        $query = Estimate::with(['rfq', 'customer'])->orderBy('created_at', 'desc');

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

        $estimates = $query->get();

        if (request()->wantsJson()) {
            return response()->json(['data' => $estimates]);
        }

        return Inertia::render('Admin/Finance/Estimates/Index', ['estimates' => $estimates]);
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

        $this->estimateService->createEstimate($rfq, $validated['line_items']);

        return Redirect::back()->with('success', 'Zoho Estimate created successfully from RFQ.');
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

        return Inertia::render('Admin/Finance/Estimates/Create', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'valid_date' => 'required|date',
            'notes' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.name' => 'required|string',
            'line_items.*.rate' => 'required|numeric|min:0',
            'line_items.*.quantity' => 'required|numeric|min:1',
            'line_items.*.description' => 'nullable|string',
            'line_items.*.zoho_item_id' => 'nullable|string',
        ]);

        $customer = User::findOrFail($validated['customer_id']);
        
        $this->estimateService->createManualEstimate($customer, $validated['line_items'], [
            'valid_date' => $validated['valid_date'],
            'notes' => $validated['notes'] ?? '',
        ]);

        return Redirect::route('finance.estimates.index')->with('success', 'Quotation created successfully.');
    }

    public function show(Estimate $estimate)
    {
        $estimate->load(['rfq', 'customer']);
        try {
            $zohoData = $this->estimateService->getEstimate($estimate->zoho_estimate_id);
        } catch (\Exception $e) {
            $zohoData = [];
        }

        if (request()->wantsJson()) {
            return response()->json([
                'local' => $estimate,
                'zoho' => $zohoData,
            ]);
        }

        return Inertia::render('Admin/Finance/Estimates/Show', [
            'estimate' => $estimate,
            'zoho' => $zohoData,
        ]);
    }

    public function downloadPdf(Estimate $estimate)
    {
        try {
            $pdfContent = $this->estimateService->getEstimatePdf($estimate->zoho_estimate_id);
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Quotation-' . ($estimate->zoho_estimate_id) . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Failed to retrieve PDF: ' . $e->getMessage());
        }
    }

    public function send(Estimate $estimate): RedirectResponse
    {
        $this->estimateService->sendEstimate($estimate->zoho_estimate_id);

        return Redirect::back()->with('success', 'Estimate sent and emailed to customer.');
    }

    public function accept(Estimate $estimate): RedirectResponse
    {
        $this->estimateService->acceptEstimate($estimate->zoho_estimate_id);

        return Redirect::back()->with('success', 'Estimate accepted.');
    }

    public function decline(Estimate $estimate): RedirectResponse
    {
        $this->estimateService->declineEstimate($estimate->zoho_estimate_id);

        return Redirect::back()->with('success', 'Estimate declined.');
    }

    public function destroy(Estimate $estimate): RedirectResponse
    {
        // Delete in Zoho
        try {
            $client = app(\App\Services\Zoho\ZohoClient::class);
            $client->delete("/estimates/{$estimate->zoho_estimate_id}");
        } catch (\Exception $e) {
            // Ignore Zoho API failures on cascade deletion to maintain DB clean states
        }

        $estimate->delete();

        return Redirect::back()->with('success', 'Estimate deleted.');
    }
}
