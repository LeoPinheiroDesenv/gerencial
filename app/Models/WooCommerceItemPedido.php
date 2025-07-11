<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceItemPedido extends Model
{
    protected $table = 'woocommerce_item_pedidos';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade',
        'valor_unitario'
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'valor_unitario' => 'decimal:2'
    ];

    public function pedido()
    {
        return $this->belongsTo(WooCommercePedido::class, 'pedido_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
} 