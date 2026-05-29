<?php

namespace App\Models;

use App\Enums\DeliveryMethodEnum;
use App\Enums\QuantityEnum;
use App\Enums\RfqStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends AbstractModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'quantity'         => QuantityEnum::class,
            'delivery_method'  => DeliveryMethodEnum::class,
            'status'           => RfqStatusEnum::class,
            'ai_suppliers'     => 'array',
            'internal_matches' => 'array',
            'claude_suppliers' => 'array',
            'qwen_suppliers'   => 'array',
            'best_supplier'    => 'array',
        ];
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AgentRfqAssignment::class);
    }
}
