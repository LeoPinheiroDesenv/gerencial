<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ListaPromocao extends Model
{
    protected $fillable = [
        'nome', 
        'empresa_id',
        'data_inicio', // Adicionando o campo de data de início
        'data_termino' // Adicionando o campo de data de término
    ];

    // Relacionamento com a tabela ProdutoListaPromocao
    public function produtos()
    {
        return $this->hasMany('App\Models\ProdutoListaPromocao', 'promocao_id', 'id', 'produto_id');
    }
    
}