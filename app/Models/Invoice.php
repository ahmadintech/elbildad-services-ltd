<?php

namespace App\Models;

use App\Enums\InvoiceStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends AbstractModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatusEnum::class,
            'total' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'due_date' => 'date',
            'issued_at' => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
