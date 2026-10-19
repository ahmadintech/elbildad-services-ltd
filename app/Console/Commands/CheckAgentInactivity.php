<?php

namespace App\Console\Commands;

use App\Services\AgentActivityNotificationService;
use Illuminate\Console\Command;

class CheckAgentInactivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agents:check-inactivity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for agents inactive for 24+ business hours (excluding weekends) and alert admins';

    /**
     * Execute the console command.
     */
    public function handle(AgentActivityNotificationService $service): int
    {
        $this->info('Checking agents for inactivity...');

        $flagged = $service->checkInactivity();

        if (empty($flagged)) {
            $this->info('No inactive agents found exceeding configured thresholds.');
            return Command::SUCCESS;
        }

        $this->warn('Flagged ' . count($flagged) . ' inactive agent(s):');
        foreach ($flagged as $item) {
            $this->line("- {$item['agent_name']}: inactive for {$item['business_hours_inactive']}h (Threshold: {$item['threshold_hours']}h, Last login: {$item['last_login_at']})");
        }

        return Command::SUCCESS;
    }
}
