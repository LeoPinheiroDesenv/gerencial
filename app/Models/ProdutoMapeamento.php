<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoMapeamento extends Model
{
    use HasFactory;

    protected $table = 'produto_mapeamento';

    protected $fillable = [
        'id_xml',
        'codBarras_xml',
        'id_fornecedor',
        'id_produto',
        'codBarras_produto',
        'empresa_id',
        'filial_id'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }
}
