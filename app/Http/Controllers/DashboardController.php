<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Estimate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on the user role.
     */
    public function index(Request $request): Response|\Illuminate\View\View
    {
        $user = $request->user();

        if ($user->hasRole('admin') || $user->hasRole('owner')) {
            return $this->adminDashboard($user);
        }

        // For now, keep others as Blade or update them later
        if ($user->hasRole('super_agent') || $user->hasRole('agent')) {
            return $this->agentDashboard($user);
        }

        return $this->customerDashboard($user);
    }

    private function adminDashboard($user): Response
    {
        // RFQ Statistics
        $totalRfqs = Rfq::count();
        $pendingRfqs = Rfq::where('status', 'pending')->count();
        $completedRfqs = Rfq::where('status', 'completed')->count();
        $inProgressRfqs = Rfq::whereIn('status', ['assigned', 'sourcing', 'purchased', 'shipped'])->count();

        // Financial Statistics
        $totalInvoices = Invoice::count();
        $totalInvoiceAmount = Invoice::sum('total') ?? 0;
        $paidAmount = Payment::sum('amount') ?? 0;
        $pendingPayments = Invoice::sum('balance_due') ?? 0;
        $totalEstimates = Estimate::count();

        // User Statistics
        $totalUsers = User::count();
        $totalAgents = User::role(['agent', 'super_agent'])->count();
        $totalCustomers = User::role('customer')->count();

        // Top KPI Stats
        $stats = [
            [
                'title' => 'Total RFQs',
                'value' => $totalRfqs,
                'change' => $this->calculateMonthlyChange(Rfq::class),
                'icon' => 'rfq',
                'color' => 'blue'
            ],
            [
                'title' => 'Revenue',
                'value' => env('APP_CURRENCY_SYMBOL', '₦') . number_format($paidAmount, 0),
                'change' => $this->calculateMonthlyPaymentChange(),
                'icon' => 'revenue',
                'color' => 'green'
            ],
            [
                'title' => 'Pending Payments',
                'value' => env('APP_CURRENCY_SYMBOL', '₦') . number_format($pendingPayments, 0),
                'change' => $this->calculateMonthlyPendingChange(),
                'icon' => 'pending',
                'color' => 'orange'
            ],
            [
                'title' => 'Active Agents',
                'value' => $totalAgents,
                'change' => $this->calculateMonthlyChange(User::class),
                'icon' => 'agents',
                'color' => 'purple'
            ]
        ];

        // RFQ Status Breakdown
        $labels = [];
        $data = [];
        $colors = [];
        $colorMap = [
            'pending' => '#FF6B6B',
            'assigned' => '#FFA500',
            'sourcing' => '#4ECDC4',
            'purchased' => '#45B7D1',
            'shipped' => '#96CEB4',
            'completed' => '#6BCB77',
            'queued' => '#FF8C00',
            'not_found' => '#D32F2F',
            'quoted' => '#03A9F4',
            'awaiting_payment' => '#FFC107',
            'payment_received' => '#4CAF50',
        ];

        foreach (\App\Enums\RfqStatusEnum::cases() as $status) {
            $count = Rfq::where('status', $status->value)->count();
            // Show if it has data or if it's one of the common statuses
            if ($count > 0 || in_array($status->value, ['pending', 'assigned', 'sourcing', 'completed'])) {
                $labels[] = ucfirst(str_replace('_', ' ', $status->value));
                $data[] = $count;
                $colors[] = $colorMap[$status->value] ?? '#808080';
            }
        }

        $rfqStatusData = [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors
        ];

        // Monthly RFQ Trend (last 12 months)
        $monthlyTrend = $this->getMonthlyRfqTrend();

        // Monthly Revenue Trend (last 12 months)
        $monthlyRevenue = $this->getMonthlyRevenueTrend();

        // Recent RFQs
        $recent_rfqs = Rfq::with(['assignedAgent', 'customer:id,name', 'assignments' => function($q) {
                $q->latest('assigned_at');
            }])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($rfq) {
                $latestAssignment = $rfq->assignments->first();
                return [
                    'id' => $rfq->id,
                    'product_name' => $rfq->product_name,
                    'status' => $rfq->status->value,
                    'created_at_formatted' => $rfq->created_at->format('M d, Y'),
                    'assigned_at' => $latestAssignment && $latestAssignment->assigned_at ? $latestAssignment->assigned_at->format('M d, Y h:i A') : 'Not Assigned',
                    'target_price' => $rfq->target_price ?? 'N/A',
                    'quantity' => $rfq->quantity->value,
                    'assigned_agent' => $rfq->assignedAgent ? ['name' => $rfq->assignedAgent->name] : ['name' => 'Unassigned'],
                    'customer' => $rfq->customer ? $rfq->customer->name : 'Unknown'
                ];
            });

        // Recent Invoices
        $recent_invoices = Invoice::with('customer:id,name', 'rfq:id,product_name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'customer' => $invoice->customer?->name ?? 'Unknown',
                    'amount' => env('APP_CURRENCY_SYMBOL', '₦') . number_format((float)$invoice->total, 0),
                    'status' => $invoice->status->value,
                    'due_date' => $invoice->due_date->format('M d, Y'),
                    'product' => $invoice->rfq?->product_name ?? 'N/A'
                ];
            });

        // Summary Stats
        $summaryStats = [
            [
                'label' => 'Total RFQs',
                'value' => $totalRfqs,
                'percentage' => 100,
            ],
            [
                'label' => 'Completed',
                'value' => $completedRfqs,
                'percentage' => $totalRfqs > 0 ? round(($completedRfqs / $totalRfqs) * 100, 1) : 0,
            ],
            [
                'label' => 'In Progress',
                'value' => $inProgressRfqs,
                'percentage' => $totalRfqs > 0 ? round(($inProgressRfqs / $totalRfqs) * 100, 1) : 0,
            ],
            [
                'label' => 'Pending',
                'value' => $pendingRfqs,
                'percentage' => $totalRfqs > 0 ? round(($pendingRfqs / $totalRfqs) * 100, 1) : 0,
            ]
        ];

        // Top Agents by RFQ assignment
        $topAgents = User::whereHas('assignedRfqs')
            ->withCount(['assignedRfqs', 'assignedRfqs as completed_rfqs_count' => function($q) {
                $q->where('status', 'completed');
            }])
            ->orderBy('assigned_rfqs_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($agent) {
                $percentage = $agent->assigned_rfqs_count > 0 
                    ? round(($agent->completed_rfqs_count / $agent->assigned_rfqs_count) * 100) 
                    : 0;

                return [
                    'name' => $agent->name,
                    'rfq_count' => $agent->assigned_rfqs_count,
                    'completed_count' => $agent->completed_rfqs_count,
                    'percentage' => $percentage,
                    'company' => $agent->email
                ];
            });

        // Category breakdown
        $categoryBreakdown = \App\Models\Category::withCount('rfqs')
            ->orderBy('rfqs_count', 'desc')
            ->limit(6)
            ->get()
            ->map(function($cat) {
                return [
                    'name' => $cat->name,
                    'count' => $cat->rfqs_count
                ];
            });

        // System health score calculation
        $completionRate = $totalRfqs > 0 ? round(($completedRfqs / $totalRfqs) * 100, 1) : 0;
        $paymentCompletionRate = $totalInvoiceAmount > 0 ? round(($paidAmount / $totalInvoiceAmount) * 100, 1) : 0;
        $systemHealth = round(($completionRate + $paymentCompletionRate) / 2, 1);

        // Agent Activities
        $agentActivities = User::role(['agent', 'super_agent'])
            ->withCount(['assignedRfqs as completed_rfqs' => function($q) {
                $q->where('status', 'completed');
            }])
            ->limit(5)
            ->get()
            ->map(function($agent) {
                return [
                    'name' => $agent->name,
                    'last_login_at' => $agent->last_login_at ? Carbon::parse($agent->last_login_at)->diffForHumans() : 'Never',
                    'avg_response_time' => $agent->avg_response_time ? $agent->avg_response_time . ' mins' : 'N/A',
                    'completed_rfqs' => $agent->completed_rfqs
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'summaryStats' => $summaryStats,
            'recent_rfqs' => $recent_rfqs,
            'recent_invoices' => $recent_invoices,
            'rfqStatusData' => $rfqStatusData,
            'monthlyTrend' => $monthlyTrend,
            'monthlyRevenue' => $monthlyRevenue,
            'topAgents' => $topAgents,
            'agentActivities' => $agentActivities,
            'categoryBreakdown' => $categoryBreakdown,
            'systemHealth' => [
                'score' => $systemHealth,
                'completionRate' => $completionRate,
                'paymentRate' => $paymentCompletionRate,
                'status' => $systemHealth >= 75 ? 'Excellent' : ($systemHealth >= 50 ? 'Good' : 'Needs Improvement')
            ],
            'totalStats' => [
                'totalRfqs' => $totalRfqs,
                'totalInvoices' => $totalInvoices,
                'totalInvoiceAmount' => env('APP_CURRENCY_SYMBOL', '₦') . number_format($totalInvoiceAmount, 0),
                'paidAmount'        => env('APP_CURRENCY_SYMBOL', '₦') . number_format($paidAmount, 0),
                'pendingPayments'   => env('APP_CURRENCY_SYMBOL', '₦') . number_format($pendingPayments, 0),
                'totalEstimates' => $totalEstimates,
                'totalUsers' => $totalUsers,
                'totalAgents' => $totalAgents,
                'totalCustomers' => $totalCustomers,
            ]
        ]);

    }

    /**
     * Calculate monthly change percentage for models
     */
    private function calculateMonthlyChange($model): string
    {
        $currentMonth = $model::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $lastMonth = $model::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        if ($lastMonth == 0) {
            return $currentMonth > 0 ? '+100%' : '0%';
        }

        $change = (($currentMonth - $lastMonth) / $lastMonth) * 100;
        $symbol = $change >= 0 ? '+' : '';
        return $symbol . round($change, 1) . '%';
    }

    /**
     * Calculate monthly payment change
     */
    private function calculateMonthlyPaymentChange(): string
    {
        $currentMonth = Payment::whereYear('paid_at', Carbon::now()->year)
            ->whereMonth('paid_at', Carbon::now()->month)
            ->sum('amount') ?? 0;

        $lastMonth = Payment::whereYear('paid_at', Carbon::now()->subMonth()->year)
            ->whereMonth('paid_at', Carbon::now()->subMonth()->month)
            ->sum('amount') ?? 0;

        if ($lastMonth == 0) {
            return $currentMonth > 0 ? '+100%' : '0%';
        }

        $change = (($currentMonth - $lastMonth) / $lastMonth) * 100;
        $symbol = $change >= 0 ? '+' : '';
        return $symbol . round($change, 1) . '%';
    }

    /**
     * Calculate monthly pending payments change
     */
    private function calculateMonthlyPendingChange(): string
    {
        $currentMonth = Invoice::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('balance_due') ?? 0;

        $lastMonth = Invoice::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('balance_due') ?? 0;

        if ($lastMonth == 0) {
            return $currentMonth > 0 ? '+100%' : '0%';
        }

        $change = (($currentMonth - $lastMonth) / $lastMonth) * 100;
        $symbol = $change >= 0 ? '+' : '';
        return $symbol . round($change, 1) . '%';
    }

    /**
     * Get monthly RFQ trend for last 12 months
     */
    private function getMonthlyRfqTrend(): array
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            $count = Rfq::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
            'color' => '#3B82F6'
        ];
    }

    /**
     * Get monthly revenue trend for last 12 months
     */
    private function getMonthlyRevenueTrend(): array
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            $revenue = Payment::whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount') ?? 0;
            $data[] = round($revenue, 2);
        }

        return [
            'labels' => $months,
            'data' => $data,
            'color' => '#10B981'
        ];
    }

    private function agentDashboard($user): Response
    {
        $rfqs = Rfq::where('assigned_agent_id', $user->id)
            ->with(['customer:id,name,whatsapp_number,email'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($rfq) => [
                'id'                     => $rfq->id,
                'tracking_token'         => $rfq->tracking_token,
                'product_name'           => $rfq->product_name,
                'specifications'         => $rfq->specifications,
                'additional_requirements'=> $rfq->additional_requirements,
                'quantity'               => $rfq->quantity->value,
                'delivery_method'        => $rfq->delivery_method->value,
                'target_price'           => $rfq->target_price,
                'location'               => $rfq->location,
                'company_name'           => $rfq->company_name,
                'status'                 => $rfq->status->value,
                'created_at_formatted'   => $rfq->created_at->format('M d, Y'),
                'ai_summary'             => $rfq->ai_summary,
                'ai_suppliers'           => $rfq->ai_suppliers,
                'image_url'              => $rfq->image_url,
                'customer'               => $rfq->customer ? [
                    'name'             => $rfq->customer->name,
                    'email'            => $rfq->customer->email,
                    'whatsapp_number'  => $rfq->customer->whatsapp_number,
                ] : null,
            ]);

        $stats = [
            ['title' => 'Total Assigned',  'value' => $rfqs->count(),                                                        'color' => 'blue'],
            ['title' => 'Pending / New',   'value' => $rfqs->whereIn('status', ['pending','assigned','queued'])->count(),     'color' => 'orange'],
            ['title' => 'In Progress',     'value' => $rfqs->whereIn('status', ['sourcing','purchased','shipped'])->count(),  'color' => 'purple'],
            ['title' => 'Completed',       'value' => $rfqs->where('status', 'completed')->count(),                           'color' => 'green'],
        ];

        return Inertia::render('Agent/Dashboard', [
            'rfqs'  => $rfqs,
            'stats' => $stats,
        ]);
    }

    private function customerDashboard($user): Response
    {
        $rfqs = Rfq::where('customer_id', $user->id)
            ->with(['assignedAgent:id,name,whatsapp_number'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($rfq) => [
                'id'                     => $rfq->id,
                'tracking_token'         => $rfq->tracking_token,
                'product_name'           => $rfq->product_name,
                'status'                 => $rfq->status->value,
                'created_at_formatted'   => $rfq->created_at->format('M d, Y'),
                'image_url'              => $rfq->image_url,
                'assigned_agent'         => $rfq->assignedAgent ? [
                    'name'             => $rfq->assignedAgent->name,
                    'whatsapp_number'  => $rfq->assignedAgent->whatsapp_number,
                ] : null,
            ]);

        $stats = [
            ['title' => 'Total Requests', 'value' => $rfqs->count(), 'color' => 'blue'],
            ['title' => 'Pending',        'value' => $rfqs->where('status', 'pending')->count() + $rfqs->where('status', 'assigned')->count(), 'color' => 'orange'],
            ['title' => 'In Progress',    'value' => $rfqs->whereIn('status', ['sourcing', 'purchased', 'shipped'])->count(), 'color' => 'purple'],
            ['title' => 'Completed',      'value' => $rfqs->where('status', 'completed')->count(), 'color' => 'green'],
        ];

        return Inertia::render('Customer/Dashboard', [
            'rfqs'  => $rfqs,
            'stats' => $stats,
        ]);
    }
}
