<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Nfse;
use App\Models\Empresa;

class LimiteNFSe
{

	public function handle($request, Closure $next){

		$value = session('user_logged');
		if($value['super']){
			return $next($request);
		}
		$empresa_id = $value['empresa'];
		$empresa = Empresa::find($empresa_id);
		$dataExp = $empresa->planoEmpresa->expiracao;
		$dataCriacao = substr($empresa->planoEmpresa->created_at, 0, 10);

		$vendas = Nfse::
		whereBetween('created_at', [$dataCriacao, 
            $dataExp])
		->where('empresa_id', $empresa_id)
		->where('numero_nfse', '>', 0)
		->where('estado', 'aprovado')
		->get();

		$cont = sizeof($vendas);

		if($empresa->planoEmpresa->plano->maximo_nfse == -1 || $empresa->planoEmpresa->plano->armazenamento > 0){
			return $next($request);
		}

		if($cont < $empresa->planoEmpresa->plano->maximo_nfse){
			return $next($request);
		} else {
            return response()->json('Limite de emissão de NFSe atingido!!', 407);
		}
		
	}

}