<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommercePedido extends Model
{
    protected $table = 'woocommerce_pedidos';

    protected $fillable = [
        'empresa_id',
        'woocommerce_id',
        'cliente_id',
        'cliente_nome',
        'cliente_email',
        'cliente_telefone',
        'status',
        'total',
        'forma_pagamento',
        'forma_envio',
        'endereco_entrega',
        'bairro_entrega',
        'cidade_entrega',
        'estado_entrega',
        'cep_entrega',
        'observacao',
        'data_pedido'
    ];

    protected $casts = [
        'data_pedido' => 'datetime',
        'total' => 'decimal:2'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens()
    {
        return $this->hasMany(WooCommerceItemPedido::class, 'pedido_id');
    }
} 