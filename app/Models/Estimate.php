<?php

namespace App\Models;

use App\Enums\EstimateStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estimate extends AbstractModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => EstimateStatusEnum::class,
            'total' => 'decimal:2',
            'valid_date' => 'date',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
