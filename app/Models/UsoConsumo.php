<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsoConsumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'funcionario_id', 'observacao', 'valor_total', 'desconto', 'acrescimo'
    ];

    public function funcionario(){
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function itens(){
        return $this->hasMany(ItemUsoConsumo::class, 'uso_consumo_id');
    }

}
