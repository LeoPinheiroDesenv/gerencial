<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoIfood extends Model
{
    use HasFactory;
    protected $fillable = [
        'status', 'pedido_id', 'data_pedido', 'empresa_id',
        // 'tipo_pedido', 'endereco', 'bairro', 'cep', 'nome_cliente', 'id_cliente',
        // 'telefone_cliente', 'valor_produtos', 'valor_entrega', 'valor_total',
        // 'taxas_adicionais'
    ];

    public function itens(){
        return $this->hasMany(ItemPedidoIfood::class, 'pedido_id');
    }

    public function payments(){
        return $this->hasMany(PagamentoPedidoIfood::class, 'pedido_id');
    }

    public function venda(){
        return $this->hasOne(VendaCaixa::class, 'pedido_ifood_id');
    }
}
