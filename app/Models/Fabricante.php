<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fabricante extends Model
{
    protected $fillable = [
		'razao_social', 'cnpj_cpf', 'ie_rg', 'empresa_id', 'telefone'
	];

	public static function verificaCadastrado($cnpj){
    	$value = session('user_logged');
        $empresa_id = $value['empresa'];
        $fabri = Fabricante::where('cnpj_cpf', $cnpj)
        ->where('empresa_id', $empresa_id)
        ->first();
        return $fabri;
    }
}
