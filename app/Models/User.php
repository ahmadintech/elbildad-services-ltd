<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends AbstractAuthenticatable
{
    use SoftDeletes;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** Assignments where this user is the agent */
    public function rfqAssignments(): HasMany
    {
        return $this->hasMany(AgentRfqAssignment::class, 'agent_id');
    }

    /** RFQs submitted by this user as a customer */
    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'customer_id');
    }

    /**
     * Send the password reset notification.
     * Force instant delivery by bypassing the queue using sendNow.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        \Illuminate\Support\Facades\Notification::sendNow($this, new \Illuminate\Auth\Notifications\ResetPassword($token));
    }
}
