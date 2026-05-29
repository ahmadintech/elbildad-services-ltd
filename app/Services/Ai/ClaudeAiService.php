<?php

namespace App\Services\Ai;

use App\Models\Rfq;
use App\Models\SourcingCompany;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiService
{
    public function enrichRfq(Rfq $rfq): array
    {
        $rfq->loadMissing(['customer', 'category']);

        $apiKey = config('services.anthropic.key');
        $model = config('services.anthropic.model', 'claude-sonnet-4-6');
        $version = config('services.anthropic.version', '2023-06-01');

        $quantity = $rfq->quantity?->value ?? $rfq->quantity;
        $deliveryMethod = $rfq->delivery_method?->value ?? $rfq->delivery_method;

        $userPrompt = "Please analyze the following RFQ details:\n";
        $userPrompt .= "Product Name: " . $rfq->product_name . "\n";
        $userPrompt .= "Category: " . ($rfq->category?->name ?? 'N/A') . "\n";
        $userPrompt .= "Specifications: " . $rfq->specifications . "\n";
        $userPrompt .= "Quantity Required: " . $quantity . "\n";
        $userPrompt .= "Preferred Delivery Method: " . $deliveryMethod . "\n";
        
        if ($rfq->target_price) {
            $userPrompt .= "Target Price: " . $rfq->target_price . "\n";
        }
        if ($rfq->additional_requirements) {
            $userPrompt .= "Additional Requirements: " . $rfq->additional_requirements . "\n";
        }
        if ($rfq->location) {
            $userPrompt .= "Delivery Location in Nigeria: " . $rfq->location . "\n";
        }
        if ($rfq->company_name) {
            $userPrompt .= "Company Name: " . $rfq->company_name . "\n";
        }

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

        $categories = \App\Models\Category::all();
        $categoriesList = $categories->pluck('name')->implode(', ');
        $userPrompt .= "\n\nExisting Product Categories in our system: " . ($categoriesList ?: 'None') . "\n";

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => $version,
                'Content-Type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 1500,
                'system' => "You are an expert China product sourcing consultant. Analyze the customer's RFQ details and recommend exactly 3 reputable Chinese suppliers. If a company from the \"Available Sourcing Companies in our portal\" provided in the user's prompt matches the customer's request, you MUST include exactly 1 matching portal company AND 2 other reputable external Chinese suppliers. If no portal company is a good match, simply provide 3 reputable external Chinese suppliers. The total number of recommended suppliers MUST always be exactly 3. You must also determine the most appropriate product category for the requested item based on the \"Existing Product Categories\" provided. If it matches an existing category, return that exact category name. If it does not match any existing category, invent a suitable new category name. For phone/whatsapp numbers, provide real or realistic Chinese export trade contact numbers (format: +86 XXXXXXXXXX) or note 'Via Alibaba Trade Manager'. Respond ONLY with a valid raw JSON object. Do not wrap in markdown backticks or code blocks. The JSON structure MUST be exactly:\n{\n  \"category\": \"Exact category name\",\n  \"summary\": \"2-3 sentence summary\",\n  \"suppliers\": [\n    {\n      \"company_name\": \"Supplier name\",\n      \"location\": \"City, Province, China\",\n      \"specialization\": \"What they specialize in\",\n      \"estimated_moq\": \"Min order quantity\",\n      \"phone\": \"+86 XXXXXXXXXX or Via Alibaba Trade Manager\",\n      \"whatsapp\": \"+86 XXXXXXXXXX or N/A\",\n      \"contact_hint\": \"e.g. Search on Alibaba\",\n      \"why_recommended\": \"Reason for recommendation\"\n    }\n  ]\n}",
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ]
                ],
            ]);

            if (!$response->successful()) {
                Log::error("Anthropic API returned error. Status: " . $response->status() . " Body: " . $response->body());
                return $this->getDefaults();
            }

            $data = $response->json();
            $text = $data['content'][0]['text'] ?? '';

            $cleanedText = trim($text);
            $cleanedText = preg_replace('/^```(?:json)?|```$/m', '', $cleanedText);
            $cleanedText = trim($cleanedText);

            $decoded = json_decode($cleanedText, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded['summary']) || !isset($decoded['suppliers']) || !isset($decoded['category'])) {
                Log::error("Failed to parse Claude JSON response. Raw text: " . $text . " | Error: " . json_last_error_msg());
                return $this->getDefaults();
            }

            return [
                'ai_category'      => $decoded['category'],
                'ai_summary'       => $decoded['summary'],
                'ai_suppliers'     => $decoded['suppliers'], // kept for backward compat
                'claude_suppliers' => $decoded['suppliers'],
            ];

        } catch (\Throwable $e) {
            Log::error("Exception in ClaudeAiService: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return $this->getDefaults();
        }
    }

    private function getDefaults(): array
    {
        return [
            'ai_category'      => null,
            'ai_summary'       => '',
            'ai_suppliers'     => [],
            'claude_suppliers' => [],
        ];
    }
}
