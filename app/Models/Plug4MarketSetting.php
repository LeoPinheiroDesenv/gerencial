<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plug4MarketSetting extends Model
{
    protected $table = 'plug4market_settings';

    protected $fillable = [
        'user_login',
        'user_password',
        'access_token',
        'refresh_token',
        'base_url',
        'sandbox',
        'seller_id',
        'software_house_cnpj',
        'store_cnpj',
        'user_id',
        'last_test_at',
        'last_test_success',
        'last_test_message'
    ];

    protected $casts = [
        'sandbox' => 'boolean',
        'last_test_success' => 'boolean',
        'last_test_at' => 'datetime'
    ];

    public static function getSettings()
    {
        return static::first() ?? static::create();
    }

    public function isConfigured()
    {
        return !empty($this->access_token) && !empty($this->refresh_token);
    }

    public function getStatusTextAttribute()
    {
        if (!$this->isConfigured()) {
            return 'Não Configurado';
        }
        
        if (!$this->last_test_success) {
            return 'Configurado (Teste Falhou)';
        }
        
        return 'Configurado e Funcionando';
    }

    public function getStatusColorAttribute()
    {
        if (!$this->isConfigured()) {
            return 'danger';
        }
        
        if (!$this->last_test_success) {
            return 'warning';
        }
        
        return 'success';
    }
} 