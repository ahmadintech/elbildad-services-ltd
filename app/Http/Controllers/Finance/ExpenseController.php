<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Rfq;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses with KPI summaries.
     */
    public function index(Request $request)
    {
        $query = Expense::with([
            'rfq:id,product_name,tracking_token',
            'agent:id,name,email',
            'recorder:id,name',
        ]);

        // Category filter
        if ($request->filled('category') && $request->category !== 'all') {
            if (in_array($request->category, ['running_cost', 'running_shipment'])) {
                $query->whereIn('category', ['running_cost', 'running_shipment']);
            } else {
                $query->where('category', $request->category);
            }
        }

        // RFQ filter
        if ($request->filled('rfq_id') && $request->rfq_id !== 'all') {
            $query->where('rfq_id', $request->rfq_id);
        }

        // Agent filter
        if ($request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('agent_id', $request->agent_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('expense_number', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%")
                  ->orWhereHas('rfq', function ($rfqQ) use ($search) {
                      $rfqQ->where('product_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('agent', function ($agentQ) use ($search) {
                      $agentQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filters
        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        // Clone query for KPI metrics calculation before pagination
        $currentMonth = now()->format('Y-m');
        $allExpenses = Expense::all();

        $stats = [
            'total_expenses' => (float) $allExpenses->sum('amount'),
            'total_purchase' => (float) $allExpenses->where('category', 'purchase')->sum('amount'),
            'total_shipment' => (float) $allExpenses->where('category', 'shipment')->sum('amount'),
            'total_running_cost' => (float) $allExpenses->whereIn('category', ['running_cost', 'running_shipment'])->sum('amount'),
            'total_running_shipment' => (float) $allExpenses->whereIn('category', ['running_cost', 'running_shipment'])->sum('amount'),
            'total_salary' => (float) $allExpenses->where('category', 'salary')->sum('amount'),
            'this_month' => (float) $allExpenses->filter(function ($e) use ($currentMonth) {
                return $e->expense_date && $e->expense_date->format('Y-m') === $currentMonth;
            })->sum('amount'),
            'count' => $allExpenses->count(),
        ];

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // RFQ options for dropdown
        $rfqs = Rfq::select('id', 'product_name', 'tracking_token', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Agents options for salary and assignment
        $agents = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'super_agent', 'admin', 'owner']);
        })->select('id', 'name', 'email')->get();

        return Inertia::render('Admin/Finance/Expenses/Index', [
            'expenses' => $expenses,
            'stats' => $stats,
            'rfqs' => $rfqs,
            'agents' => $agents,
            'filters' => [
                'category' => $request->category ?? 'all',
                'search' => $request->search ?? '',
                'rfq_id' => $request->rfq_id ?? 'all',
                'agent_id' => $request->agent_id ?? 'all',
                'start_date' => $request->start_date ?? '',
                'end_date' => $request->end_date ?? '',
            ],
        ]);
    }

    /**
     * Store a newly created expense record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:purchase,shipment,running_shipment,running_cost,salary,operational',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'note' => 'nullable|string',
            'rfq_id' => 'nullable|exists:rfqs,id',
            'item_number' => 'nullable|string|max:100',
            'shipping_method' => 'nullable|string|max:50',
            'vendor_name' => 'nullable|string|max:255',
            'agent_id' => 'nullable|exists:users,id',
            'salary_period' => 'nullable|string|max:50',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ]);

        if (in_array($validated['category'], ['running_cost', 'running_shipment']) && empty($validated['note'])) {
            return Redirect::back()->withErrors(['note' => 'A descriptive note is required for Running Cost expenses.']);
        }

        // Normalize running_shipment to running_cost
        if ($validated['category'] === 'running_shipment') {
            $validated['category'] = 'running_cost';
        }

        if ($validated['category'] === 'salary' && empty($validated['agent_id'])) {
            return Redirect::back()->withErrors(['agent_id' => 'Please select the agent for this salary disbursement.']);
        }

        // Generate sequential unique expense number: EXP-YYYYMM-XXXX
        $prefix = 'EXP-' . date('Ym') . '-';
        $latest = Expense::withTrashed()
            ->where('expense_number', 'like', "{$prefix}%")
            ->orderBy('expense_number', 'desc')
            ->first();

        $sequence = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->expense_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        do {
            $expenseNumber = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Expense::withTrashed()->where('expense_number', $expenseNumber)->exists());

        $data = [
            'expense_number' => $expenseNumber,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'currency' => 'NGN', // Strictly NGN
            'expense_date' => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'note' => $validated['note'] ?? null,
            'rfq_id' => $validated['rfq_id'] ?? null,
            'item_number' => $validated['item_number'] ?? null,
            'shipping_method' => $validated['shipping_method'] ?? null,
            'vendor_name' => $validated['vendor_name'] ?? null,
            'agent_id' => $validated['agent_id'] ?? null,
            'salary_period' => $validated['salary_period'] ?? null,
            'status' => 'paid',
            'recorded_by' => auth()->id(),
        ];

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        Expense::create($data);

        return Redirect::back()->with('success', "Expense {$expenseNumber} recorded successfully.");
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:purchase,shipment,running_shipment,running_cost,salary,operational',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'note' => 'nullable|string',
            'rfq_id' => 'nullable|exists:rfqs,id',
            'item_number' => 'nullable|string|max:100',
            'shipping_method' => 'nullable|string|max:50',
            'vendor_name' => 'nullable|string|max:255',
            'agent_id' => 'nullable|exists:users,id',
            'salary_period' => 'nullable|string|max:50',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ]);

        if (in_array($validated['category'], ['running_cost', 'running_shipment']) && empty($validated['note'])) {
            return Redirect::back()->withErrors(['note' => 'A descriptive note is required for Running Cost expenses.']);
        }

        // Normalize running_shipment to running_cost
        if ($validated['category'] === 'running_shipment') {
            $validated['category'] = 'running_cost';
        }

        if ($validated['category'] === 'salary' && empty($validated['agent_id'])) {
            return Redirect::back()->withErrors(['agent_id' => 'Please select the agent for this salary disbursement.']);
        }

        $data = [
            'category' => $validated['category'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'note' => $validated['note'] ?? null,
            'rfq_id' => $validated['rfq_id'] ?? null,
            'item_number' => $validated['item_number'] ?? null,
            'shipping_method' => $validated['shipping_method'] ?? null,
            'vendor_name' => $validated['vendor_name'] ?? null,
            'agent_id' => $validated['agent_id'] ?? null,
            'salary_period' => $validated['salary_period'] ?? null,
        ];

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        $expense->update($data);

        return Redirect::back()->with('success', "Expense {$expense->expense_number} updated successfully.");
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $number = $expense->expense_number;
        $expense->delete();

        return Redirect::back()->with('success', "Expense {$number} deleted successfully.");
    }

    /**
     * Export expenses to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Expense::with(['rfq', 'agent', 'recorder'])->orderBy('expense_date', 'desc');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        if ($request->filled('rfq_id') && $request->rfq_id !== 'all') {
            $query->where('rfq_id', $request->rfq_id);
        }
        if ($request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('agent_id', $request->agent_id);
        }

        $filename = 'expenses-export-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Expense #',
                'Date',
                'Category',
                'Title / Subject',
                'Amount (NGN)',
                'RFQ Product',
                'Item / Waybill #',
                'Shipping Method',
                'Vendor / Supplier',
                'Agent (Salary)',
                'Salary Period',
                'Payment Method',
                'Notes',
                'Recorded By',
            ]);

            $query->chunk(100, function ($expenses) use ($handle) {
                foreach ($expenses as $e) {
                    fputcsv($handle, [
                        $e->expense_number,
                        $e->expense_date ? $e->expense_date->format('Y-m-d') : '',
                        in_array($e->category, ['running_cost', 'running_shipment']) ? 'Running Cost' : ucwords(str_replace('_', ' ', $e->category)),
                        $e->title,
                        number_format($e->amount, 2, '.', ''),
                        $e->rfq ? $e->rfq->product_name : '',
                        $e->item_number ?? '',
                        $e->shipping_method ?? '',
                        $e->vendor_name ?? '',
                        $e->agent ? $e->agent->name : '',
                        $e->salary_period ?? '',
                        ucfirst(str_replace('_', ' ', $e->payment_method)),
                        $e->note ?? '',
                        $e->recorder ? $e->recorder->name : '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
