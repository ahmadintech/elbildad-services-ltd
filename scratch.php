<?php
$rfq = App\Models\Rfq::latest()->first();
if ($rfq) {
    $allSuppliers = array_merge($rfq->claude_suppliers ?? [], $rfq->qwen_suppliers ?? []);
    $summary = $rfq->ai_summary;
    $summary = explode("\n\n**Sourcing Companies Details:**", $summary)[0];
    if (!empty($allSuppliers)) {
        $summary .= "\n\n**Sourcing Companies Details:**\n";
        $unique = collect($allSuppliers)->unique('company_name')->take(3);
        foreach ($unique as $s) {
            $summary .= "- **" . ($s['company_name'] ?? 'Supplier') . "**\n";
            if (!empty($s['phone'])) $summary .= "  - Phone: " . $s['phone'] . "\n";
            if (!empty($s['whatsapp'])) $summary .= "  - WhatsApp: " . $s['whatsapp'] . "\n";
            if (!empty($s['contact_hint'])) $summary .= "  - Contact Hint: " . $s['contact_hint'] . "\n";
        }
    }
    $rfq->update(['ai_summary' => $summary]);
    echo "Updated summary\n";
}
