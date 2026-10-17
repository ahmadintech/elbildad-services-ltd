<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Estimate;
use App\Services\Zoho\ZohoEstimateService;

#[Signature('zoho:sync-estimate-totals')]
#[Description('Syncs the total amount from Zoho Books to local database for all estimates.')]
class SyncZohoEstimateTotals extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ZohoEstimateService $service)
    {
        $estimates = Estimate::all();
        $this->info('Found ' . $estimates->count() . ' estimates to sync.');

        $updatedCount = 0;
        foreach ($estimates as $estimate) {
            try {
                $zohoData = $service->getEstimate($estimate->zoho_estimate_id);
                if (isset($zohoData['total']) && $zohoData['total'] != $estimate->total) {
                    $this->line("Updating Estimate #{$estimate->id} ({$estimate->zoho_estimate_id}) - Old Total: {$estimate->total}, New Total: {$zohoData['total']}");
                    $estimate->update(['total' => $zohoData['total']]);
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to sync estimate #{$estimate->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully synced totals for {$updatedCount} estimates.");
    }
}
