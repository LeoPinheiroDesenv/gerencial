<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaBancaria extends Model
{
    protected $fillable = [
        'banco', 'agencia', 'conta', 'titular', 'empresa_id', 'padrao', 'cnpj', 'endereco',
        'cidade_id', 'cep', 'bairro', 'carteira', 'convenio', 'juros', 'multa', 'juros_apos', 
        'tipo', 'usar_logo', 'numero_remessa', 'numero_posto', 'numero_boleto'
    ];

    public static function bancos(){
        return [
            '001' => 'Banco do Brasil',
            '341' => 'Itau',
            '237' => 'Bradesco',
            '748' => 'Sicredi',
            '133' => 'Cresol',
            '104' => 'Caixa Econônica Federal',
            '033' => 'Santander',
            '756' => 'Sicoob'
        ];
    }

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}