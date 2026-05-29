<?php

namespace App\Jobs;

use App\Models\Rfq;
use App\Services\Ai\ClaudeAiService;
use App\Services\Ai\QwenAiService;
use App\Services\Mail\RfqMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessRfqWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Rfq $rfq;

    public function __construct(Rfq $rfq)
    {
        $this->rfq = $rfq;
    }

    public function handle(ClaudeAiService $claudeService, QwenAiService $qwenService, RfqMailService $mailService): void
    {
        try {
            Log::info("Starting dual-AI processing for RFQ: " . $this->rfq->id);

            // ── Step 1: Run Claude ──────────────────────────────────────────────
            $claudeResult = $claudeService->enrichRfq($this->rfq);
            $claudeSuppliers = $claudeResult['claude_suppliers'] ?? [];

            Log::info("Claude returned " . count($claudeSuppliers) . " suppliers for RFQ #{$this->rfq->id}");

            // ── Step 2: Run Qwen ────────────────────────────────────────────────
            $qwenResult   = $qwenService->enrichRfq($this->rfq);
            $qwenSuppliers = $qwenResult['qwen_suppliers'] ?? [];

            Log::info("Qwen returned " . count($qwenSuppliers) . " suppliers for RFQ #{$this->rfq->id}");

            // ── Step 3: Pick the best supplier from all 6 combined ──────────────
            $allSuppliers  = array_merge($claudeSuppliers, $qwenSuppliers);
            $bestSupplier  = null;

            if (!empty($allSuppliers)) {
                $bestSupplier = $this->pickBestSupplier($allSuppliers, $this->rfq);
            }

            // ── Step 4: Determine category & summary ────────────────────────────
            // Prefer Claude's analysis for category/summary; fall back to Qwen
            $aiCategoryName = $claudeResult['ai_category'] ?? $qwenResult['ai_category'] ?? null;
            $aiSummary      = $claudeResult['ai_summary']  ?? $qwenResult['ai_summary']  ?? '';

            if (!empty($allSuppliers)) {
                $aiSummary .= "\n\n**Sourcing Companies Details:**\n";
                $uniqueSuppliers = collect($allSuppliers)->unique('company_name')->take(3);
                foreach ($uniqueSuppliers as $supplier) {
                    $aiSummary .= "- **" . ($supplier['company_name'] ?? 'Supplier') . "**\n";
                    if (!empty($supplier['phone'])) {
                        $aiSummary .= "  - Phone: " . $supplier['phone'] . "\n";
                    }
                    if (!empty($supplier['whatsapp'])) {
                        $aiSummary .= "  - WhatsApp: " . $supplier['whatsapp'] . "\n";
                    }
                    if (!empty($supplier['contact_hint'])) {
                        $aiSummary .= "  - Contact Hint: " . $supplier['contact_hint'] . "\n";
                    }
                }
            }

            // Use merged supplier list as the legacy ai_suppliers field
            $mergedSuppliers = $allSuppliers;

            // ── Step 5: Resolve/create category ────────────────────────────────
            $categoryId  = null;
            $isNewCategory = false;

            if ($aiCategoryName) {
                $category = \App\Models\Category::whereRaw('LOWER(name) = ?', [strtolower(trim($aiCategoryName))])->first();
                if ($category) {
                    $categoryId = $category->id;
                } else {
                    $category = \App\Models\Category::create([
                        'name' => ucwords(trim($aiCategoryName)),
                        'slug' => \Illuminate\Support\Str::slug(trim($aiCategoryName))
                    ]);
                    $categoryId    = $category->id;
                    $isNewCategory = true;
                }
            }

            // ── Step 6: Persist everything ──────────────────────────────────────
            $this->rfq->update([
                'category_id'      => $categoryId,
                'ai_summary'       => $aiSummary,
                'ai_suppliers'     => $mergedSuppliers,   // backward compat (all 6)
                'claude_suppliers' => $claudeSuppliers,
                'qwen_suppliers'   => $qwenSuppliers,
                'best_supplier'    => $bestSupplier,
            ]);

            // ── Step 7: Agent assignment ────────────────────────────────────────
            $this->assignRfq($this->rfq, $isNewCategory);

            Log::info("Dual-AI processing complete for RFQ: " . $this->rfq->id);

        } catch (\Throwable $e) {
            Log::error("Failed to process RFQ with AI. RFQ ID: {$this->rfq->id}. Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Ask Claude to compare all suppliers and pick the single best one.
     * Falls back to the first supplier if the comparison call fails.
     */
    private function pickBestSupplier(array $allSuppliers, Rfq $rfq): ?array
    {
        $apiKey  = config('services.anthropic.key');
        $model   = config('services.anthropic.model', 'claude-sonnet-4-6');
        $version = config('services.anthropic.version', '2023-06-01');

        if (empty($apiKey)) {
            return $allSuppliers[0] ?? null;
        }

        $suppliersJson = json_encode($allSuppliers, JSON_PRETTY_PRINT);

        $prompt = "You are given {count} supplier recommendations (sourced from two different AI systems) for this RFQ:\n"
            . "Product: {$rfq->product_name}\n"
            . "Specs: {$rfq->specifications}\n"
            . "Quantity: " . ($rfq->quantity?->value ?? $rfq->quantity) . "\n\n"
            . "Here are all the suppliers:\n{$suppliersJson}\n\n"
            . "Analyze all suppliers and pick the single BEST one based on: specialization match, MOQ suitability, contact availability, and overall reliability.\n"
            . "Respond ONLY with a valid raw JSON object — the full supplier object you chose, adding one extra field:\n"
            . "  \"selection_reason\": \"1-2 sentence explanation of why this is the best pick overall\"\n"
            . "Do not wrap in markdown, no extra text.";

        $prompt = str_replace('{count}', count($allSuppliers), $prompt);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => $version,
                'Content-Type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => $model,
                'max_tokens' => 512,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (!$response->successful()) {
                Log::warning("Best-supplier comparison call failed. Using first supplier.");
                return $allSuppliers[0] ?? null;
            }

            $data = $response->json();
            $text = $data['content'][0]['text'] ?? '';
            $cleaned = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text)));
            $decoded = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($decoded)) {
                Log::warning("Could not parse best-supplier response. Fallback to first.");
                return $allSuppliers[0] ?? null;
            }

            Log::info("Best supplier picked for RFQ #{$rfq->id}: " . ($decoded['company_name'] ?? 'unknown'));
            return $decoded;

        } catch (\Throwable $e) {
            Log::warning("Exception in pickBestSupplier: " . $e->getMessage());
            return $allSuppliers[0] ?? null;
        }
    }

    private function assignRfq(Rfq $rfq, bool $isNewCategory): void
    {
        $agent = null;

        if (!$isNewCategory) {
            $agent = $this->findAgent('agent', $rfq->category_id);
        }

        if (!$agent) {
            $agent = $this->findAgent('super_agent');
        }

        if ($agent) {
            \App\Models\AgentRfqAssignment::create([
                'rfq_id'      => $rfq->id,
                'agent_id'    => $agent->id,
                'assigned_at' => now(),
                'is_active'   => true,
            ]);

            $rfq->update([
                'assigned_agent_id' => $agent->id,
                'status'            => \App\Enums\RfqStatusEnum::ASSIGNED->value,
            ]);
        } else {
            $rfq->update(['status' => \App\Enums\RfqStatusEnum::QUEUED->value]);
            event(new \App\Events\RfqQueued($rfq));
        }
    }

    private function findAgent(string $role, ?string $categoryId = null): ?\App\Models\User
    {
        $query = \App\Models\User::role($role)
            ->withCount(['rfqAssignments as active_count' => function ($q) {
                $q->where('is_active', true);
            }])
            ->having('active_count', '<', 5)
            ->orderBy('active_count', 'asc');

        if ($categoryId && $role === 'agent') {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $categoryId));
        }

        return $query->first();
    }
}
