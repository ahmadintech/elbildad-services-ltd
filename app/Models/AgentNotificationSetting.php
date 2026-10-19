<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentNotificationSetting extends AbstractModel
{
    protected $casts = [
        'notify_on_login' => 'boolean',
        'notify_on_logout' => 'boolean',
        'notify_on_inactivity' => 'boolean',
        'inactivity_hours' => 'integer',
        'exclude_weekends' => 'boolean',
        'sound_chime' => 'boolean',
        'monitored_agents' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Retrieve or create default settings for a given admin user.
     */
    public static function forAdmin(string $adminId): self
    {
        return static::firstOrCreate(
            ['admin_id' => $adminId],
            [
                'notify_on_login' => true,
                'notify_on_logout' => true,
                'notify_on_inactivity' => true,
                'inactivity_hours' => 24,
                'exclude_weekends' => true,
                'sound_chime' => true,
                'monitored_agents' => ['all'],
            ]
        );
    }
}
