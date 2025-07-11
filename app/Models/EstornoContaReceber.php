<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstornoContaReceber extends Model
{
    protected $table = 'estorno_conta_receber';

    protected $fillable = [
        'empresa_id',
        'conta_receber_id',
        'usuario_id',
        'quantidade',
    ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function contaReceber()
    {
        return $this->belongsTo(ContaReceber::class, 'conta_receber_id');
    }
}
