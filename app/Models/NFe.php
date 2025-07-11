<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NFe extends Model
{
    protected $table = 'nfe';

    protected $fillable = [
        'empresa_id',
        'pedido_id',
        'numero',
        'chave',
        'status',
        'xml',
        'erro'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
} 