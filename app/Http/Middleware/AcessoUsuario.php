<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\UsuarioAcesso;

class AcessoUsuario
{
	public function handle($request, Closure $next){

		$response = $next($request);

		$value = session('user_logged');

		if(isset($value['id'])){
			$acesso = UsuarioAcesso::
			where('usuario_id', $value['id'])
			->where('status', 0)
			->first();

			if(!$acesso){
				return $response;
			}
		}else{
			return $response;
		}


		// echo (strtotime(date('Y-m-d')) - strtotime($acesso->created_at))/60/24;
		// die;

		if($value['hash'] != $acesso->hash && $value['ip_address'] != $this->get_client_ip()){
			session()->flash('mensagem_login', 'Já existe uma sessão ativa em outro equipamento.');

			$acesso2 = UsuarioAcesso::
			where('usuario_id', $value['id'])
			->where('status', 0)
			->orderBy('id', 'desc')
			->first();
			$acesso2->delete();

			// $acesso->delete();
			return redirect("/login/logoff?notmessage=1");
		}

		return $response;

	}

	private function get_client_ip() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}

}
