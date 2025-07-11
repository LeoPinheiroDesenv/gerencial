<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plug4MarketToken extends Model
{
    protected $table = 'plug4market_tokens';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_at',
        'token_type',
        'scope'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function needsRefresh()
    {
        return $this->expires_at && $this->expires_at->subMinutes(5)->isPast();
    }
} 