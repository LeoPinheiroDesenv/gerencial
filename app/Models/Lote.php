<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $fillable = [
        'produto_id',
        'fabricante_id',
        'numero_lote',
        'quantidade_inicial',
        'saldo',
        'data_fabricacao',
        'data_validade',
        'safra',
        'codigo_barras',
        'preco_compra',
        'preco_venda',
        'empresa_id',
        'filial_id'
    ];

    // Relação com Produto
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Relação com Fabricante
    public function fabricante()
    {
        return $this->belongsTo(Fabricante::class);
    }

    // Relação com Empresa (opcional)
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    // Relação com Filial (opcional)
    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }
}
