<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReciboReceber extends Model
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'recibo_conta_rec';

    // Campos preenchíveis (mass assignable) – não inclui 'conta_receber_id'
    protected $fillable = [
        'empresa_id',
        'filial_id',
        'data_pagamento',
        'cliente',
        'documento',
        'endereco',
        'telefone',
        'valor_pago',
        'valor_extenso',
        'forma_pagamento',
        'observacao',
        'referencia'
    ];
    
    // Relação many-to-many com ContaReceber usando a tabela pivot
    public function contasReceber()
    {
        return $this->belongsToMany(
            \App\Models\ContaReceber::class,
            'recibo_conta_rec_conta_recebers', // tabela pivot
            'recibo_receber_id',               // coluna da pivot que referencia este model
            'conta_receber_id'                 // coluna da pivot que referencia ContaReceber
        );
    }
    
    // Relação com a Empresa
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id');
    }
    
    // Relação com a Filial
    public function filial()
    {
        return $this->belongsTo(\App\Models\Filial::class, 'filial_id');
    }

    // App\Models\ContaReceber.php
public function recibos()
{
    return $this->belongsToMany(
        \App\Models\ReciboReceber::class,
        'recibo_conta_rec_conta_recebers', // tabela pivot
        'conta_receber_id',                // coluna que referencia ContaReceber
        'recibo_receber_id'                // coluna que referencia ReciboReceber
    );
}

    
}