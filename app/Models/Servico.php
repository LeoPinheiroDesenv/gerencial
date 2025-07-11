<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $fillable = [
        'nome', 'unidade_cobranca', 'valor', 'categoria_id', 'empresa_id', 'tempo_servico',
        'tempo_adicional', 'valor_adicional', 'tempo_tolerancia', 'codigo_servico',  'cnae_servico', 'comissao',
        'aliquota_iss', 'valor_aliquota', 'aliquota_pis', 'aliquota_cofins', 'aliquota_inss'
    ];

    public function categoria(){
        return $this->belongsTo(CategoriaServico::class, 'categoria_id');
    }
}
