<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentActivityLog;
use App\Models\AgentNotificationSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AgentActivityController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $agents = User::role(['agent', 'super_agent'])
            ->with(['assignedRfqs' => function ($q) {
                $q->select('id', 'assigned_agent_id', 'status', 'product_name', 'tracking_token', 'created_at', 'updated_at')
                  ->orderBy('updated_at', 'desc');
            }])
            ->get()
            ->map(function ($agent) use ($now) {
                $rfqs = $agent->assignedRfqs;
                $total = $rfqs->count();

                $completed       = $rfqs->filter(fn($r) => $r->status->value === 'completed')->count();
                $inProgress      = $rfqs->filter(fn($r) => in_array($r->status->value, ['sourcing', 'purchased', 'shipped']))->count();
                $pending         = $rfqs->filter(fn($r) => in_array($r->status->value, ['pending', 'assigned', 'queued']))->count();
                $quoted          = $rfqs->filter(fn($r) => $r->status->value === 'quoted')->count();
                $awaitingPayment = $rfqs->filter(fn($r) => $r->status->value === 'awaiting_payment')->count();
                $paymentReceived = $rfqs->filter(fn($r) => $r->status->value === 'payment_received')->count();
                $notFound        = $rfqs->filter(fn($r) => $r->status->value === 'not_found')->count();

                $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

                $thisMonthRfqs = $rfqs->filter(
                    fn($r) => $r->created_at->month === $now->month && $r->created_at->year === $now->year
                )->count();

                $lastRfqActivity = $rfqs->sortByDesc('updated_at')->first()?->updated_at;

                // Build per-RFQ detail list
                $rfqList = $rfqs->map(function ($rfq) use ($now) {
                    $idleMinutes = (int) $rfq->updated_at->diffInMinutes($now);
                    $idleDays    = (int) $rfq->updated_at->diffInDays($now);
                    $idleHours   = (int) $rfq->updated_at->diffInHours($now);

                    if ($idleMinutes < 60) {
                        $idleLabel = $idleMinutes . 'm ago';
                    } elseif ($idleHours < 24) {
                        $idleLabel = $idleHours . 'h ago';
                    } elseif ($idleDays === 1) {
                        $idleLabel = '1 day ago';
                    } else {
                        $idleLabel = $idleDays . ' days ago';
                    }

                    // Risk level based on idle days and status
                    $activeStatuses = ['pending', 'assigned', 'sourcing', 'queued', 'quoted', 'awaiting_payment'];
                    $isActive = in_array($rfq->status->value, $activeStatuses);

                    $risk = 'low';
                    if ($isActive) {
                        if ($idleDays >= 7) $risk = 'critical';
                        elseif ($idleDays >= 3) $risk = 'high';
                        elseif ($idleDays >= 1) $risk = 'medium';
                    }

                    return [
                        'id'              => $rfq->id,
                        'tracking_token'  => 'ELB-' . strtoupper(substr($rfq->tracking_token, 0, 8)),
                        'product_name'    => $rfq->product_name,
                        'status'          => $rfq->status->value,
                        'assigned_date'   => $rfq->created_at->format('M d, Y'),
                        'assigned_human'  => $rfq->created_at->diffForHumans(),
                        'last_updated'    => $rfq->updated_at->format('M d, Y H:i'),
                        'last_updated_human' => $rfq->updated_at->diffForHumans(),
                        'idle_days'       => $idleDays,
                        'idle_label'      => $idleLabel,
                        'risk'            => $risk,
                    ];
                })->values();

                // Login info
                // Calculate real avg response time from RFQs that have been acted on
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
                    $avgResponseLabel = null;
                }

                $lastLogin = $agent->last_login_at ? Carbon::parse($agent->last_login_at) : null;
                $effectiveActivity = $lastLogin;
                if (!$effectiveActivity || ($lastRfqActivity && $lastRfqActivity->gt($effectiveActivity))) {
                    $effectiveActivity = $lastRfqActivity;
                }

                $loginHuman     = $effectiveActivity?->diffForHumans() ?? 'Never logged in';
                $loginFormatted = $effectiveActivity?->format('M d, Y H:i') ?? null;
                $daysSinceLogin = $effectiveActivity ? (int) $effectiveActivity->diffInDays($now) : null;

                return [
                    'id'               => $agent->id,
                    'name'             => $agent->name,
                    'email'            => $agent->email,
                    'role'             => $agent->roles->first()?->name ?? 'agent',
                    'joined_at'        => $agent->created_at->format('M d, Y'),
                    // Login activity
                    'last_login_at'     => $loginFormatted,
                    'last_login_human'  => $loginHuman,
                    'days_since_login'   => $daysSinceLogin,
                    'avg_response_time'  => $avgResponseLabel,
                    // RFQ stats
                    'total'            => $total,
                    'completed'        => $completed,
                    'in_progress'      => $inProgress,
                    'pending'          => $pending,
                    'quoted'           => $quoted,
                    'awaiting_payment' => $awaitingPayment,
                    'payment_received' => $paymentReceived,
                    'not_found'        => $notFound,
                    'completion_rate'  => $completionRate,
                    'this_month'       => $thisMonthRfqs,
                    'last_rfq_activity'     => $lastRfqActivity?->diffForHumans() ?? 'No activity',
                    'last_rfq_activity_raw' => $lastRfqActivity?->format('M d, Y H:i') ?? null,
                    // RFQ details
                    'rfqs'             => $rfqList,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalAgents      = $agents->count();
        $totalSuperAgents = $agents->where('role', 'super_agent')->count();
        $totalRfqs        = $agents->sum('total');
        $totalCompleted   = $agents->sum('completed');
        $totalInProgress  = $agents->sum('in_progress');
        $totalPending     = $agents->sum('pending');
        $totalNotFound    = $agents->sum('not_found');
        $overallRate      = $totalRfqs > 0 ? round(($totalCompleted / $totalRfqs) * 100, 1) : 0;

        // Count RFQs that have been idle 3+ days in active statuses
        $stalledCount = $agents->sum(fn($a) => collect($a['rfqs'])->where('risk', 'high')->count()
                                              + collect($a['rfqs'])->where('risk', 'critical')->count());

        $summary = [
            ['title' => 'Total Agents',       'value' => $totalAgents,       'color' => 'blue',   'icon' => 'agents'],
            ['title' => 'Super Agents',        'value' => $totalSuperAgents,  'color' => 'purple', 'icon' => 'star'],
            ['title' => 'Total RFQs Handled', 'value' => $totalRfqs,         'color' => 'indigo', 'icon' => 'rfq'],
            ['title' => 'Completion Rate',     'value' => $overallRate . '%', 'color' => 'green',  'icon' => 'check'],
        ];

        $statusOverview = [
            ['label' => 'Completed',       'count' => $totalCompleted,                      'color' => 'green'],
            ['label' => 'In Progress',      'count' => $totalInProgress,                     'color' => 'purple'],
            ['label' => 'Pending / New',    'count' => $totalPending,                        'color' => 'orange'],
            ['label' => 'Not Found',        'count' => $totalNotFound,                       'color' => 'red'],
            ['label' => 'Quoted',           'count' => $agents->sum('quoted'),               'color' => 'sky'],
            ['label' => 'Awaiting Payment', 'count' => $agents->sum('awaiting_payment'),     'color' => 'amber'],
            ['label' => 'Payment Received', 'count' => $agents->sum('payment_received'),     'color' => 'emerald'],
            ['label' => 'Stalled (3d+)',    'count' => $stalledCount,                        'color' => 'red'],
        ];

        $notificationSettings = AgentNotificationSetting::forAdmin(auth()->id());

        $recentLogs = AgentActivityLog::with('agent:id,name,email')
            ->latest('occurred_at')
            ->limit(40)
            ->get()
            ->map(function ($log) {
                return [
                    'id'                    => $log->id,
                    'agent_name'            => $log->agent?->name ?? 'Unknown Agent',
                    'agent_email'           => $log->agent?->email ?? '',
                    'event'                 => $log->event,
                    'ip_address'            => $log->ip_address,
                    'user_agent'            => $log->user_agent,
                    'occurred_at_human'     => $log->occurred_at?->diffForHumans(),
                    'occurred_at_formatted' => $log->occurred_at?->format('M d, Y H:i:s'),
                ];
            });

        $agentList = User::role(['agent', 'super_agent'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/AgentActivity/Index', [
            'agents'               => $agents,
            'summary'              => $summary,
            'statusOverview'       => $statusOverview,
            'totalRfqs'            => $totalRfqs,
            'notificationSettings' => $notificationSettings,
            'recentLogs'           => $recentLogs,
            'agentList'            => $agentList,
        ]);
    }

    /**
     * Update notification settings for the authenticated administrator.
     */
    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'notify_on_login'      => 'required|boolean',
            'notify_on_logout'     => 'required|boolean',
            'notify_on_inactivity' => 'required|boolean',
            'inactivity_hours'     => 'required|integer|min:1|max:720',
            'exclude_weekends'     => 'required|boolean',
            'sound_chime'          => 'required|boolean',
            'monitored_agents'     => 'nullable|array',
        ]);

        if (empty($validated['monitored_agents'])) {
            $validated['monitored_agents'] = ['all'];
        }

        AgentNotificationSetting::updateOrCreate(
            ['admin_id' => auth()->id()],
            $validated
        );

        return back()->with('success', 'Agent notification preferences updated successfully.');
    }

    /**
     * Send a test alert to verify notification bell and audio chime.
     */
    public function sendTestNotification(Request $request)
    {
        $admin = auth()->user();
        $setting = AgentNotificationSetting::forAdmin($admin->id);

        $admin->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\AgentActivityNotification',
            'data' => [
                'type' => 'Test Notification',
                'event' => 'test',
                'agent_id' => $admin->id,
                'agent_name' => 'System Tester',
                'message' => 'Agent Activity Notification test successful. Audio chime and alerts are configured.',
                'action_url' => '/admin/agent-activity',
                'sound' => $setting->sound_chime,
                'time' => now()->diffForHumans(),
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Test alert sent! Check your notification bell.');
    }
}

