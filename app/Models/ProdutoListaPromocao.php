<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoListaPromocao extends Model
{
    protected $fillable = [
        'promocao_id',
        'produto_id',  // Adicionando a coluna produto_id
        'preco_compra',
        'preco_venda',
        'porcentagem_desconto',
        'valor_desconto',
        'valor_final',
    ];

    public function listaPromocao()
    {
        return $this->belongsTo('App\Models\ListaPromocao', 'promocao_id', 'id');
    }

    public function produto()
    {
        return $this->belongsTo('App\Models\Produto', 'produto_id', 'id');  // Relacionamento com o modelo Produto
    }
}