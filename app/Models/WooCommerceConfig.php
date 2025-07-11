<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceConfig extends Model
{

    protected $table = 'woocommerce_configs';


    protected $fillable = [
        'empresa_id',
        'store_url',
        'consumer_key',
        'consumer_secret',
        'is_active',
        'sync_products',
        'sync_orders',
        'sync_stock',
        'default_category_id',
        'default_status',
        'price_markup',
        'shipping_methods',
        'payment_methods',
        'last_sync',
        'auto_sync',
        'sync_interval'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sync_products' => 'boolean',
        'sync_orders' => 'boolean',
        'sync_stock' => 'boolean',
        'auto_sync' => 'boolean',
        'shipping_methods' => 'array',
        'payment_methods' => 'array',
        'last_sync' => 'datetime'
    ];
}
