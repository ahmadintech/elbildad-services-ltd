<?php

namespace App\Models;

use App\Enums\PaymentModeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends AbstractModel
{
    protected function casts(): array
    {
        return [
            'payment_mode' => PaymentModeEnum::class,
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
