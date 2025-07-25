<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CodigoDesconto;
use Comtele\Services\CreditService;
use Comtele\Services\TextMessageService;
use App\Models\ClienteDelivery;

class CodigoDescontoController extends Controller
{
	protected $empresa_id = null;
	public function __construct(){
		$this->middleware(function ($request, $next) {
			$this->empresa_id = $request->empresa_id;
			$value = session('user_logged');
			if(!$value){
				return redirect("/login");
			}
			return $next($request);
		});
	}
	
	public function index(){
		$codigos = CodigoDesconto::
		where('empresa_id', $this->empresa_id)
		->paginate(20);

		return view('codigoDesconto/list')
		->with('title', 'Cupons de desconto')
		->with('codigos', $codigos);
	}

	public function new(){
		$clientes = ClienteDelivery::orderBy('nome')->get();
		return view('codigoDesconto/register')
		->with('title', 'Novo Cupom')
		->with('clientes', $clientes)
		->with('codigoJs', true);
	}


	public function save(Request $request){
		$this->_validate($request);
		$cli = $request->cliente_id;

		$expiracao = null;
		if($request->expiracao){
			$expiracao = $request->expiracao;
			$expiracao = str_replace("/", "-", $expiracao);
			$expiracao = \Carbon\Carbon::parse($expiracao)->format('Y-m-d');
		}
		$res = CodigoDesconto::create([
			'descricao' => $request->descricao,
			'codigo' => $request->codigo,
			'tipo' => $request->tipo,
			'cliente_id' => $request->todos ? NULL : $cli,
			'ativo' => 1,
			'push' => 0,
			'sms' => 0,
			'expiracao' => $expiracao,
			'empresa_id' => $this->empresa_id,
			'valor' => str_replace(",", ".", $request->valor),
			'valor_minimo_pedido' => str_replace(",", ".", $request->valor_minimo_pedido)
		]);

		if($res){
			session()->flash('mensagem_sucesso', 'Cupom adicionado!');
		}else{
			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/codigoDesconto');
	}

	public function edit($id){
		$codigo = CodigoDesconto::
		where('id', $id)
		->first();

		if(valida_objeto($codigo)){
			return view('codigoDesconto/register')
			->with('codigoJs', true)
			->with('codigo', $codigo)
			->with('title', 'Editar Cupom');
		}else{
			return redirect('/403');
		}
	}

	public function update(Request $request){
		$this->_validate($request);
		$codigo = CodigoDesconto::
		where('id', $request->id)
		->first();

		$expiracao = null;
		if($request->expiracao){
			$expiracao = $request->expiracao;
			$expiracao = str_replace("/", "-", $expiracao);
			$expiracao = \Carbon\Carbon::parse($expiracao)->format('Y-m-d');
			
		}

		$codigo->codigo = $request->codigo;
		$codigo->descricao = $request->descricao;
		$codigo->expiracao = $expiracao;
		$codigo->tipo = $request->tipo;
		$codigo->valor = str_replace(",", ".", $request->valor);
		$codigo->valor_minimo_pedido = str_replace(",", ".", $request->valor_minimo_pedido);
		// $codigo->ativo = $request->ativo ? true : false;
		if($codigo->save()){
			session()->flash('mensagem_sucesso', 'Cupom editado!');
		}else{
			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/codigoDesconto');
	}


	private function _validate(Request $request){
		// print_r($request->todos);
		// die;
		$rules = [
			'cliente_id' => ($request->id > 0) ? '' : ($request->todos ? '' : 'required'),
			'valor' => 'required',
			'codigo' => 'required',
			'descricao' => 'required|max:50',
		];

		$messages = [
			'descricao.required' => 'O campo descrição é obrigatório.',
			'descricao.max' => 'máximo de 50 caracteres.',
			'valor.required' => 'O campo valor é obrigatório.',
			'cliente_id.required' => 'O campo cliente é obrigatório.',
			'codigo.required' => 'O campo código é obrigatório.',
		];
		$this->validate($request, $rules, $messages);
	}

	public function delete($id){
		$codigo = CodigoDesconto::
		where('id', $id)
		->first();
		if(valida_objeto($codigo)){
			$codigo->delete();
			if($codigo){
				session()->flash('mensagem_sucesso', 'Codigo removido!');
			}else{

				session()->flash('mensagem_erro', 'Erro!');
			}
			return redirect('/codigoDesconto');
		}else{
			return redirect('/403');
		}
	}

	public function push($id){
		$cupom = CodigoDesconto::
		where('id', $id)
		->first();

		if(valida_objeto($cupom)){

			return view('codigoDesconto/enviaPush')
			->with('cupom', $cupom)
			->with('title', 'Editar Código de desconto');
		}else{
			return redirect('/403');
		}
	}

	public function sms($id){
		$cupom = CodigoDesconto::
		where('id', $id)
		->first();

		if(valida_objeto($cupom)){

			$creditService = new CreditService(env('SMS_KEY'));
			$totalSms = $creditService->get_my_credits();

			return view('codigoDesconto/enviarSms')

			->with('totalSms', $totalSms)
			->with('cupom', $cupom)
			->with('title', 'Editar Código de desconto');
		}else{
			return redirect('/403');
		}
	}

	public function saveSms(Request $request){
		//envia sms
		$mensagem = $request->mensagem;
		$cupomId = $request->cupom_id;

		$cupom = CodigoDesconto::
		where('id', $cupomId)
		->first();

		
		$envios = 0;
		if($cupom->cliente_id == null){
			$clientesAtivos = ClienteDelivery::
			where('ativo', true)
			->get();

			foreach($clientesAtivos as $c){
				$phone = str_replace(" ", "", $c->celular);
				$phone = str_replace("-", "", $phone);
				if(strlen($phone) == 11){
					$res = $this->sendSms($mensagem, $phone);
					if($res) $envios++;
				}

			}

		}else{
			$phone = str_replace(" ", "", $cupom->cliente->celular);
			$phone = str_replace("-", "", $phone);
			$res = $this->sendSms($mensagem, $phone);
			$envios++;
		}

		if($res){
			$cupom->sms = true;
			$cupom->save();

			session()->flash('mensagem_sucesso', "SMS enviado para $envios pessoa(s)!");
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/codigoDesconto');

	}

	public function savePush(Request $request){
		//envia sms
		$mensagem = $request->mensagem;
		$titulo = $request->titulo;
		$cupomId = $request->cupom_id;

		$cupom = CodigoDesconto::
		where('id', $cupomId)
		->first();

		
		$envios = 0;
		$res = false;

		$dataMessage = array(
			'message' => $mensagem,
			'title' => $titulo
		);

		if($cupom->cliente_id == null){
			$clientesAtivos = ClienteDelivery::
			where('ativo', true)
			->get();

			foreach($clientesAtivos as $c){

				$tokens = [];
				foreach($c->tokens as $t){
					if(!in_array($t->token, $tokens)){
						array_push($tokens, $t->token);
					}
				}
				$envios++;

			}

		}else{
			
			$cliente = $cupom->cliente;
			$tokens = [];
			foreach($cliente->tokens as $t){
				if(!in_array($t->token, $tokens)){
					array_push($tokens, $t->token);
				}
			}
			
			$envios++;

		}

		$res = $this->sendPush($tokens, $dataMessage);

		if($res){
			$cupom->push = true;
			$cupom->save();

			session()->flash('mensagem_sucesso', "Push enviado para $envios pessoa(s)!");
		}else{

			session()->flash('mensagem_erro', 'Erro ao enviar Push!');
		}

		return redirect('/codigoDesconto');

	}

	private function sendSms($mensagem, $phone){

		$textMessageService = new TextMessageService(env('SMS_KEY'));
		$res = $textMessageService->send("Sender", $mensagem, [$phone]);
		return $res;
	}

	private function sendPush($to = '', $data = array()){
		$apiKey = env('PUSH_KEY');
		$fields = array('registration_ids' => [$to], 'data' => $data);

		$headers = array('Authorization:key = '.$apiKey, 'Content-Type: application/json');

		$url = 'https://fcm.googleapis.com/fcm/send';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		$result = curl_exec($ch);
		if($result === FALSE){
			die('Curl failed: ' . curl_error($ch));
		}
		curl_close($ch);
		return $result;
	}

	public function alterarStatus($id){
		$codigo = CodigoDesconto::
		where('id', $id)
		->first();
		if(valida_objeto($codigo)){

			$codigo->ativo = !$codigo->ativo; 
			$codigo->save();
			session()->flash('mensagem_sucesso', "$codigo->codigo alterado para ".(!$codigo->ativo ? "desativado" : "ativado"));
			return redirect('/codigoDesconto');
		}else{
			return redirect('/403');
		}
	}
}
