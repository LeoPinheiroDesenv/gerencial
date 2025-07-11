<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plug4MarketOrderItem extends Model
{
    protected $table = 'plug4market_order_items';

    protected $fillable = [
        'order_id',
        'sku',
        'quantity',
        'price',
        'total_price',
        'product_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function order()
    {
        return $this->belongsTo(Plug4MarketOrder::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Plug4MarketProduct::class, 'product_id');
    }

    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    public function getFormattedTotalPriceAttribute()
    {
        return 'R$ ' . number_format($this->total_price, 2, ',', '.');
    }

    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->descricao : 'Produto não encontrado';
    }
} 