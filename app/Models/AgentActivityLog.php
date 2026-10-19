<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentActivityLog extends AbstractModel
{
    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
