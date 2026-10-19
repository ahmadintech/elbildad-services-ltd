<?php

namespace App\Services;

use App\Models\AgentActivityLog;
use App\Models\AgentNotificationSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class AgentActivityNotificationService
{
    /**
     * Record an agent login and notify admins who configured login alerts.
     */
    public function recordLogin(User $agent, ?string $ip = null, ?string $userAgent = null): void
    {
        if (!$agent->hasRole(['agent', 'super_agent'])) {
            return;
        }

        // Log the activity
        AgentActivityLog::create([
            'agent_id' => $agent->id,
            'event' => 'login',
            'ip_address' => $ip,
            'user_agent' => $userAgent ? Str::limit($userAgent, 255) : null,
            'occurred_at' => now(),
        ]);

        // Find all admins/owners and check their settings
        $admins = User::role(['admin', 'owner'])->get();

        foreach ($admins as $admin) {
            $setting = AgentNotificationSetting::forAdmin($admin->id);

            if (!$setting->notify_on_login) {
                continue;
            }

            if (!$this->isAgentMonitored($setting, $agent->id)) {
                continue;
            }

            // Create notification for admin
            $admin->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\AgentActivityNotification',
                'data' => [
                    'type' => 'Agent Login',
                    'event' => 'login',
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name,
                    'message' => "Agent {$agent->name} logged in (" . ($ip ?? 'portal') . ").",
                    'action_url' => '/admin/agent-activity',
                    'sound' => $setting->sound_chime,
                    'time' => now()->diffForHumans(),
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Record an agent logout and notify admins who configured logout alerts.
     */
    public function recordLogout(User $agent, ?string $ip = null, ?string $userAgent = null): void
    {
        if (!$agent->hasRole(['agent', 'super_agent'])) {
            return;
        }

        // Log the activity
        AgentActivityLog::create([
            'agent_id' => $agent->id,
            'event' => 'logout',
            'ip_address' => $ip,
            'user_agent' => $userAgent ? Str::limit($userAgent, 255) : null,
            'occurred_at' => now(),
        ]);

        // Find all admins/owners and check their settings
        $admins = User::role(['admin', 'owner'])->get();

        foreach ($admins as $admin) {
            $setting = AgentNotificationSetting::forAdmin($admin->id);

            if (!$setting->notify_on_logout) {
                continue;
            }

            if (!$this->isAgentMonitored($setting, $agent->id)) {
                continue;
            }

            // Create notification for admin
            $admin->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\AgentActivityNotification',
                'data' => [
                    'type' => 'Agent Logout',
                    'event' => 'logout',
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name,
                    'message' => "Agent {$agent->name} has logged out.",
                    'action_url' => '/admin/agent-activity',
                    'sound' => $setting->sound_chime,
                    'time' => now()->diffForHumans(),
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Check inactive agents based on each admin's configured business hours threshold (excluding weekends).
     */
    public function checkInactivity(): array
    {
        $now = Carbon::now();
        $agents = User::role(['agent', 'super_agent'])->get();
        $admins = User::role(['admin', 'owner'])->get();
        $flagged = [];

        foreach ($admins as $admin) {
            $setting = AgentNotificationSetting::forAdmin($admin->id);

            if (!$setting->notify_on_inactivity) {
                continue;
            }

            $thresholdHours = $setting->inactivity_hours ?: 24;
            $excludeWeekends = $setting->exclude_weekends ?? true;

            foreach ($agents as $agent) {
                if (!$this->isAgentMonitored($setting, $agent->id)) {
                    continue;
                }

                $lastLogin = $agent->last_login_at ? Carbon::parse($agent->last_login_at) : null;
                $businessHours = $this->calculateBusinessHoursSince($lastLogin, $now, $excludeWeekends);

                if ($businessHours >= $thresholdHours) {
                    $flagged[] = [
                        'agent_id' => $agent->id,
                        'agent_name' => $agent->name,
                        'business_hours_inactive' => $businessHours,
                        'threshold_hours' => $thresholdHours,
                        'last_login_at' => $lastLogin ? $lastLogin->format('M d, Y H:i') : 'Never',
                    ];

                    // Check cooldown: avoid duplicate inactivity notification within last 18 hours
                    $recentAlert = DatabaseNotification::where('notifiable_id', $admin->id)
                        ->where('data->type', 'Agent Inactivity Alert')
                        ->where('data->agent_id', $agent->id)
                        ->where('created_at', '>=', now()->subHours(18))
                        ->exists();

                    if (!$recentAlert) {
                        $weekendNote = $excludeWeekends ? ' (excluding weekends)' : '';
                        $hoursDisplay = round($businessHours, 1);

                        $admin->notifications()->create([
                            'id' => Str::uuid()->toString(),
                            'type' => 'App\Notifications\AgentActivityNotification',
                            'data' => [
                                'type' => 'Agent Inactivity Alert',
                                'event' => 'inactivity',
                                'agent_id' => $agent->id,
                                'agent_name' => $agent->name,
                                'message' => "Agent {$agent->name} has not logged in for {$hoursDisplay}h{$weekendNote}. Threshold is {$thresholdHours}h.",
                                'action_url' => '/admin/agent-activity',
                                'sound' => $setting->sound_chime,
                                'time' => now()->diffForHumans(),
                            ],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        return $flagged;
    }

    /**
     * Calculate business hours between two timestamps, optionally skipping Saturdays and Sundays.
     */
    public function calculateBusinessHoursSince(?Carbon $from, Carbon $to, bool $excludeWeekends = true): float
    {
        if (!$from) {
            // Never logged in, count business hours from account creation or default to 999
            return 999.0;
        }

        if ($from->gte($to)) {
            return 0.0;
        }

        if (!$excludeWeekends) {
            return round($from->diffInMinutes($to) / 60, 1);
        }

        $current = $from->copy();
        $businessMinutes = 0;

        while ($current->lt($to)) {
            // End of current day or $to, whichever is earlier
            $endOfDay = $current->copy()->endOfDay();
            $segmentEnd = $endOfDay->lt($to) ? $endOfDay : $to->copy();

            if (!$current->isWeekend()) {
                $businessMinutes += $current->diffInMinutes($segmentEnd);
            }

            // Move current to the very start of the next day
            $current = $current->copy()->addDay()->startOfDay();
        }

        return round($businessMinutes / 60, 1);
    }

    /**
     * Check if a specific agent is in the monitored list of the setting.
     */
    protected function isAgentMonitored(AgentNotificationSetting $setting, string $agentId): bool
    {
        $monitored = $setting->monitored_agents;

        if (empty($monitored) || in_array('all', $monitored, true)) {
            return true;
        }

        return in_array($agentId, $monitored, true);
    }
}
