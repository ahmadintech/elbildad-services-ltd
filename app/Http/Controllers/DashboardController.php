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

        if ($user->hasRole(['admin', 'owner', 'super_admin'])) {
            return $this->adminDashboard($user);
        }

        if ($user->hasRole('visa_admin')) {
            return $this->visaAdminDashboard($user);
        }

        if ($user->hasRole('visa_agent')) {
            return $this->visaAgentDashboard($user);
        }

        // Agents
        if ($user->hasRole(['super_agent', 'agent'])) {
            return $this->agentDashboard($user);
        }

        // Any custom staff role (e.g. accountant, auditor, manager) or user with assigned permissions
        if (
            !$user->hasRole('customer') ||
            $user->getAllPermissions()->isNotEmpty() ||
            $user->roles->count() > 1
        ) {
            return $this->adminDashboard($user);
        }

        return $this->customerDashboard($user);
    }

    private function visaAdminDashboard($user): Response
    {
        $applications = \App\Models\VisaApplication::with(['assignedAgent:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            ['title' => 'Total Applications', 'value' => $applications->count(), 'color' => 'blue', 'icon' => 'total'],
            ['title' => 'Pending', 'value' => $applications->where('status', 'pending')->count(), 'color' => 'orange', 'icon' => 'pending'],
            ['title' => 'In Progress', 'value' => $applications->whereIn('status', ['processing', 'assigned'])->count(), 'color' => 'purple', 'icon' => 'progress'],
            ['title' => 'Approved', 'value' => $applications->where('status', 'approved')->count(), 'color' => 'green', 'icon' => 'completed'],
        ];

        $agents = User::role(['visa_agent', 'visa_admin'])->get(['id', 'name']);

        return Inertia::render('Admin/Visa/Dashboard', [
            'applications' => $applications,
            'stats' => $stats,
            'agents' => $agents,
        ]);
    }

    private function visaAgentDashboard($user): Response
    {
        $applications = \App\Models\VisaApplication::where('assigned_agent_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            ['title' => 'Assigned to Me', 'value' => $applications->count(), 'color' => 'blue'],
            ['title' => 'Pending', 'value' => $applications->whereIn('status', ['pending', 'assigned'])->count(), 'color' => 'orange'],
            ['title' => 'Processing', 'value' => $applications->where('status', 'processing')->count(), 'color' => 'purple'],
            ['title' => 'Completed', 'value' => $applications->whereIn('status', ['approved', 'rejected'])->count(), 'color' => 'green'],
        ];

        return Inertia::render('Agent/Visa/Dashboard', [
            'applications' => $applications,
            'stats' => $stats,
        ]);
    }

    private function adminDashboard($user): Response
    {
        // RFQ Statistics
        $totalRfqs      = Rfq::count();
        $completedRfqs  = Rfq::where('status', 'completed')->count();
        $inProgressRfqs = Rfq::whereIn('status', [
            'sourcing', 'quoted', 'awaiting_payment', 'payment_received', 'purchased', 'shipped'
        ])->count();
        $pendingRfqs    = Rfq::whereIn('status', [
            'pending', 'assigned', 'queued'
        ])->count();
        $awaitingFeeRfqs = Rfq::where('status', 'commitment_fee_pending')->count();
        $notFoundRfqs   = Rfq::where('status', 'not_found')->count();

        // Financial Statistics
        $totalInvoices = Invoice::count();
        $totalInvoiceAmount = (float)(Invoice::sum('total') ?? 0);
        $paidAmount = (float)(Payment::sum('amount') ?? 0) + (float)(\App\Models\Transaction::where('status', 'successful')->sum('amount') ?? 0);
        $pendingPayments = (float)(Invoice::sum('balance_due') ?? 0);
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
                'change' => $this->calculateMonthlyAgentChange(),
                'icon' => 'agents',
                'color' => 'purple'
            ]
        ];

        // RFQ Status Breakdown
        $labels = [];
        $data = [];
        $colors = [];
        $colorMap = [
            'commitment_fee_pending' => '#8B5CF6',
            'pending' => '#FF6B6B',
            'queued' => '#FF8C00',
            'assigned' => '#FFA500',
            'sourcing' => '#4ECDC4',
            'quoted' => '#03A9F4',
            'awaiting_payment' => '#FFC107',
            'payment_received' => '#4CAF50',
            'purchased' => '#45B7D1',
            'shipped' => '#96CEB4',
            'completed' => '#6BCB77',
            'not_found' => '#D32F2F',
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
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A',
                    'product' => $invoice->rfq?->product_name ?? 'N/A'
                ];
            });

        // Summary Stats (complete breakdown covering 100% of pipeline)
        $summaryStats = [
            [
                'label' => 'Total RFQs',
                'value' => $totalRfqs,
                'percentage' => 100,
            ],
            [
                'label' => 'In Progress (Active)',
                'value' => $inProgressRfqs,
                'percentage' => $totalRfqs > 0 ? round(($inProgressRfqs / $totalRfqs) * 100, 1) : 0,
            ],
            [
                'label' => 'Pending / Queued',
                'value' => $pendingRfqs,
                'percentage' => $totalRfqs > 0 ? round(($pendingRfqs / $totalRfqs) * 100, 1) : 0,
            ],
            [
                'label' => 'Awaiting Commitment Fee',
                'value' => $awaitingFeeRfqs,
                'percentage' => $totalRfqs > 0 ? round(($awaitingFeeRfqs / $totalRfqs) * 100, 1) : 0,
            ],
            [
                'label' => 'Completed',
                'value' => $completedRfqs,
                'percentage' => $totalRfqs > 0 ? round(($completedRfqs / $totalRfqs) * 100, 1) : 0,
            ],
            [
                'label' => 'Not Found / Closed',
                'value' => $notFoundRfqs,
                'percentage' => $totalRfqs > 0 ? round(($notFoundRfqs / $totalRfqs) * 100, 1) : 0,
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
        $resolvedRfqs = $completedRfqs + $notFoundRfqs;
        $fulfillmentRate = $resolvedRfqs > 0 ? round(($completedRfqs / $resolvedRfqs) * 100, 1) : 0;
        $paymentCompletionRate = $totalInvoiceAmount > 0 ? round(($paidAmount / $totalInvoiceAmount) * 100, 1) : 0;
        $systemHealth = round(($fulfillmentRate * 0.5) + ($paymentCompletionRate * 0.5), 1);

        // Agent Activities - ordered by recent activity with real response times
        $agentActivities = User::role(['agent', 'super_agent'])
            ->with(['assignedRfqs' => function($q) {
                $q->select('id', 'assigned_agent_id', 'status', 'created_at', 'updated_at');
            }])
            ->withCount(['assignedRfqs as completed_rfqs' => function($q) {
                $q->where('status', 'completed');
            }])
            ->get()
            ->sortByDesc(function($agent) {
                return $agent->last_login_at 
                    ? Carbon::parse($agent->last_login_at)->timestamp 
                    : ($agent->assignedRfqs->max('updated_at')?->timestamp ?? 0);
            })
            ->take(5)
            ->values()
            ->map(function($agent) {
                $rfqs = $agent->assignedRfqs;
                $lastActivity = $agent->last_login_at 
                    ? Carbon::parse($agent->last_login_at) 
                    : $rfqs->sortByDesc('updated_at')->first()?->updated_at;

                $actedRfqs = $rfqs->filter(
                    fn($r) => !in_array($r->status->value, ['pending', 'assigned', 'queued'])
                              && $r->updated_at->gt($r->created_at)
                );
                if ($actedRfqs->isNotEmpty()) {
                    $totalMinutes = $actedRfqs->sum(
                        fn($r) => (int) $r->created_at->diffInMinutes($r->updated_at)
                    );
                    $avgMinutes = (int) round($totalMinutes / $actedRfqs->count());
                    if ($avgMinutes < 60) {
                        $avgResponseLabel = $avgMinutes . 'm';
                    } elseif ($avgMinutes < 1440) {
                        $avgResponseLabel = round($avgMinutes / 60, 1) . 'h';
                    } else {
                        $avgResponseLabel = round($avgMinutes / 1440, 1) . 'd';
                    }
                } else {
                    $avgResponseLabel = 'N/A';
                }

                return [
                    'name' => $agent->name,
                    'last_login_at' => $lastActivity ? $lastActivity->diffForHumans() : 'Never',
                    'avg_response_time' => $avgResponseLabel,
                    'completed_rfqs' => $agent->completed_rfqs
                ];
            });

        // Agent Tasks & Scheduled Talks (monday.com overview for super admin)
        $tasksQuery = \App\Models\Task::with('assignedAgent:id,name');
        $agentTasksStats = [
            'total'       => (clone $tasksQuery)->count(),
            'talks'       => (clone $tasksQuery)->where('type', 'talk')->count(),
            'in_progress' => (clone $tasksQuery)->where('status', 'in_progress')->count(),
            'done'        => (clone $tasksQuery)->where('status', 'done')->count(),
            'overdue'     => (clone $tasksQuery)->where('due_at', '<', now())->where('status', '!=', 'done')->count(),
        ];

        $agentTasks = (clone $tasksQuery)
            ->orderByRaw("CASE WHEN status = 'done' THEN 2 ELSE 1 END")
            ->orderByRaw("CASE WHEN due_at IS NULL THEN 1 ELSE 0 END, due_at ASC")
            ->limit(8)
            ->get()
            ->map(function ($task) {
                $isOverdue = $task->due_at && $task->due_at->isPast() && $task->status !== 'done';
                $dueLabel = 'No due date';
                if ($task->due_at) {
                    if ($task->due_at->isToday()) {
                        $dueLabel = 'Today ' . $task->due_at->format('h:i A');
                    } elseif ($task->due_at->isTomorrow()) {
                        $dueLabel = 'Tomorrow ' . $task->due_at->format('h:i A');
                    } else {
                        $dueLabel = $task->due_at->format('M d, h:i A');
                    }
                }

                return [
                    'id'                  => $task->id,
                    'title'               => $task->title,
                    'type'                => $task->type,
                    'status'              => $task->status,
                    'priority'            => $task->priority,
                    'contact_name'        => $task->contact_name,
                    'due_label'           => $dueLabel,
                    'is_overdue'          => $isOverdue,
                    'assigned_agent_name' => $task->assignedAgent?->name ?? 'Unassigned',
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
            'agentTasks' => $agentTasks,
            'agentTasksStats' => $agentTasksStats,
            'categoryBreakdown' => $categoryBreakdown,
            'systemHealth' => [
                'score' => $systemHealth,
                'completionRate' => $fulfillmentRate,
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
     * Calculate monthly change percentage for agents
     */
    private function calculateMonthlyAgentChange(): string
    {
        $currentMonth = User::role(['agent', 'super_agent'])
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $lastMonth = User::role(['agent', 'super_agent'])
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
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
     * Calculate monthly payment change (including online gateway transactions)
     */
    private function calculateMonthlyPaymentChange(): string
    {
        $now = Carbon::now();
        $currentPayments = Payment::whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount') ?? 0;
        $currentTransactions = \App\Models\Transaction::where('status', 'successful')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('amount') ?? 0;
        $currentMonth = $currentPayments + $currentTransactions;

        $lastMonthDate = $now->copy()->subMonth();
        $lastPayments = Payment::whereYear('paid_at', $lastMonthDate->year)
            ->whereMonth('paid_at', $lastMonthDate->month)
            ->sum('amount') ?? 0;
        $lastTransactions = \App\Models\Transaction::where('status', 'successful')
            ->whereYear('created_at', $lastMonthDate->year)
            ->whereMonth('created_at', $lastMonthDate->month)
            ->sum('amount') ?? 0;
        $lastMonth = $lastPayments + $lastTransactions;

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
            
            $payments = Payment::whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount') ?? 0;

            $transactions = \App\Models\Transaction::where('status', 'successful')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount') ?? 0;

            $revenue = $payments + $transactions;
            $data[] = round((float)$revenue, 2);
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
