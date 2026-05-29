<?php

namespace App\Services\Ai;

use App\Models\Rfq;
use App\Models\SourcingCompany;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QwenAiService
{
    /**
     * Enrich an RFQ with AI-generated category, summary, and supplier recommendations
     * using Alibaba Cloud Model Studio (DashScope) — OpenAI-compatible mode.
     */
    public function enrichRfq(Rfq $rfq): array
    {
        $rfq->loadMissing(['customer', 'category']);

        $apiKey  = config('services.dashscope.key');
        $model   = config('services.dashscope.model', 'qwen-turbo');
        $baseUrl = rtrim(config('services.dashscope.base_url', 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'), '/');

        if (empty($apiKey)) {
            Log::error('QwenAiService: DASHSCOPE_API_KEY is not configured.');
            return $this->getDefaults();
        }

        $quantity       = $rfq->quantity?->value       ?? $rfq->quantity;
        $deliveryMethod = $rfq->delivery_method?->value ?? $rfq->delivery_method;

        // ── Build the user prompt (identical structure to ClaudeAiService) ──
        $userPrompt  = "Please analyze the following RFQ details:\n";
        $userPrompt .= "Product Name: "              . $rfq->product_name                      . "\n";
        $userPrompt .= "Category: "                  . ($rfq->category?->name ?? 'N/A')        . "\n";
        $userPrompt .= "Specifications: "            . $rfq->specifications                    . "\n";
        $userPrompt .= "Quantity Required: "         . $quantity                               . "\n";
        $userPrompt .= "Preferred Delivery Method: " . $deliveryMethod                         . "\n";

        if ($rfq->target_price)           { $userPrompt .= "Target Price: "             . $rfq->target_price            . "\n"; }
        if ($rfq->additional_requirements){ $userPrompt .= "Additional Requirements: "  . $rfq->additional_requirements . "\n"; }
        if ($rfq->location)               { $userPrompt .= "Delivery Location in Nigeria: " . $rfq->location            . "\n"; }
        if ($rfq->company_name)           { $userPrompt .= "Company Name: "              . $rfq->company_name            . "\n"; }

        // Include portal sourcing companies
        $companies = SourcingCompany::all();
        if ($companies->isNotEmpty()) {
            $userPrompt .= "\n\nAvailable Sourcing Companies in our portal:\n";
            foreach ($companies as $company) {
                $userPrompt .= "- " . $company->name . " (Location: " . ($company->location ?? 'N/A') . ")\n";
                if ($company->description) {
                    $userPrompt .= "  Description/Specialization: " . $company->description . "\n";
                }
            }
        }

        // Include existing categories so AI can reuse them
        $categories     = \App\Models\Category::all();
        $categoriesList = $categories->pluck('name')->implode(', ');
        $userPrompt    .= "\n\nExisting Product Categories in our system: " . ($categoriesList ?: 'None') . "\n";

        // ── System prompt (identical requirements to ClaudeAiService) ──
        $systemPrompt = 'You are an expert China product sourcing consultant. Analyze the customer\'s RFQ details and recommend exactly 3 reputable Chinese suppliers. If a company from the "Available Sourcing Companies in our portal" provided in the user\'s prompt matches the customer\'s request, you MUST include exactly 1 matching portal company AND 2 other reputable external Chinese suppliers. If no portal company is a good match, simply provide 3 reputable external Chinese suppliers. The total number of recommended suppliers MUST always be exactly 3. You must also determine the most appropriate product category for the requested item based on the "Existing Product Categories" provided. If it matches an existing category, return that exact category name. If it does not match any existing category, invent a suitable new category name. For phone/whatsapp numbers, provide real or realistic Chinese export trade contact numbers (format: +86 XXXXXXXXXX) or note "Via Alibaba Trade Manager". Respond ONLY with a valid raw JSON object. Do not wrap the response in markdown backticks, markdown code blocks, or include any extra conversational text or explanations. The JSON structure MUST be exactly:
{
  "category": "Exact name of existing category OR a suitable new category name",
  "summary": "2-3 sentence summary of the RFQ for an internal sourcing agent, noting if any portal companies matched",
  "suppliers": [
    {
      "company_name": "Name of the Chinese supplier",
      "location": "City, Province, China",
      "specialization": "What this supplier specializes in",
      "estimated_moq": "Estimated minimum order quantity suitable for this RFQ",
      "phone": "+86 XXXXXXXXXX or Via Alibaba Trade Manager",
      "whatsapp": "+86 XXXXXXXXXX or N/A",
      "contact_hint": "e.g. Portal Company, or Search on Alibaba, Made-in-China, or Global Sources",
      "why_recommended": "Why this supplier is recommended for this RFQ"
    }
  ]
}';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(60)->post("{$baseUrl}/chat/completions", [
                'model'       => $model,
                'max_tokens'  => 1500,
                'temperature' => 0.3,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('DashScope API error. Status: ' . $response->status() . ' Body: ' . $response->body(), [
                    'rfq_id' => $rfq->id,
                ]);
                return $this->getDefaults();
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';

            // Strip any markdown code fences the model may wrap around JSON
            $cleanedText = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text)));

            $decoded = json_decode($cleanedText, true);

            if (
                json_last_error() !== JSON_ERROR_NONE
                || !isset($decoded['summary'], $decoded['suppliers'], $decoded['category'])
            ) {
                Log::error('Failed to parse Qwen JSON response. Raw: ' . $text . ' | Error: ' . json_last_error_msg(), [
                    'rfq_id' => $rfq->id,
                ]);
                return $this->getDefaults();
            }

            Log::info("QwenAiService: Successfully enriched RFQ #{$rfq->id} using model {$model}.");

            return [
                'ai_category'   => $decoded['category'],
                'ai_summary'    => $decoded['summary'],
                'ai_suppliers'  => $decoded['suppliers'], // backward compat
                'qwen_suppliers'=> $decoded['suppliers'],
            ];

        } catch (\Throwable $e) {
            Log::error('Exception in QwenAiService: ' . $e->getMessage(), [
                'rfq_id'    => $rfq->id,
                'exception' => $e,
            ]);
            return $this->getDefaults();
        }
    }

    private function getDefaults(): array
    {
        return [
            'ai_category'    => null,
            'ai_summary'     => '',
            'ai_suppliers'   => [],
            'qwen_suppliers' => [],
        ];
    }
}
