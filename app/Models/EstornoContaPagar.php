<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstornoContaPagar extends Model
{
    // Se você usou o nome singular-plural padrão, não precisa desta linha:
    protected $table = 'estorno_conta_pagar';

    // Desativa timestamps automáticos (não temos created_at/updated_at)
    public $timestamps = false;

    // Campos que podemos preencher em massa
    protected $fillable = [
        'empresa_id',
        'conta_pagar_id',
        'usuario_id',
        'quantidade',
    ];

    // Relações (opcionais, caso queira navegar):
    public function contaPagar()
    {
        return $this->belongsTo(ContaPagar::class, 'conta_pagar_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
