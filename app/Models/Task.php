<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'due_at'            => 'datetime',
        'reminder_at'       => 'datetime',
        'reminder_sent_at'  => 'datetime',
        'completed_at'      => 'datetime',
        'timing_started_at' => 'datetime',
        'duration_minutes'  => 'integer',
    ];

    /** The user who created the task */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The agent assigned to execute the task */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Optional RFQ linked to the task */
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    /** User who completed the task */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scope tasks visible to a user:
     * Admins/owners see all tasks; agents see their assigned or created tasks.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->hasAnyRole(['admin', 'owner'])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhere('user_id', $user->id);
        });
    }

    /**
     * Scope pending reminders that should trigger an alert for the current user.
     */
    public function scopeDueRemindersFor($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhere('user_id', $user->id);
        })
        ->where('status', '!=', 'done')
        ->whereNotNull('reminder_at')
        ->where('reminder_at', '<=', now())
        ->whereNull('reminder_sent_at');
    }
}
