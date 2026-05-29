<?php

namespace App\Models;

class ZohoToken extends AbstractModel
{
    protected $table = 'zoho_tokens';

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
