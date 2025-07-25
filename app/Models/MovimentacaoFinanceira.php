<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoFinanceira extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'conta_id', 'tabela', 'status', 'valor'
    ];
}
