<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $fillable = [
        'razao_social', 'nome_fantasia', 'bairro', 'numero', 'rua', 'cpf_cnpj', 'telefone', 'celular', 'email', 'cep', 'ie_rg', 
        'cidade_id', 'empresa_id', 'contribuinte', 'pix', 'tipo_pix', 'complemento', 'cod_pais', 'id_estrangeiro'
    ];

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public static function verificaCadastrado($cnpj){
    	$value = session('user_logged');
        $empresa_id = $value['empresa'];
        $forn = Fornecedor::where('cpf_cnpj', $cnpj)
        ->where('empresa_id', $empresa_id)
        ->first();

        return $forn;
    }

    public static function tiposDePix(){
        return [
            'cpf',
            'cnpj',
            'email',
            'telefone',
            'chave aleatória'
        ];
    }

    public function getPais(){
        $pais = Pais::where('codigo', $this->cod_pais)->first();
        return $pais->nome;
    }
}
