<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoMesa extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'valor_total', 'forma_pagamento', 'observacao',
        'estado', 'uid', 'nome_cliente', 'telefone_cliente', 'mesa_id'
    ];

    public function mesa(){
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function itens(){
        return $this->hasMany(ItemPedidoMesa::class, 'pedido_id', 'id')->with('produto')->with('itensAdicionais')
        ->with('tamanho');
    }

    public function somaItens(){
        $total = 0;
        foreach($this->itens as $item){
            $total += $item->quantidade * $item->valor;
        }
        return $total;
    }
}
