<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\UsuarioAcesso;
use App\Models\ConfigCaixa;
use App\Helpers\Menu;

class UsuarioController extends Controller
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

	public function setLocation(Request $request){
		try{
			$usuario = Usuario::find(get_id_user());
			$usuario->local_padrao = $request->filial_id;
			$usuario->save();
			return response()->json($usuario, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function lista(){
		$usuarios = Usuario::
		where('empresa_id', $this->empresa_id)
		->get();
		return view('usuarios/list')
		->with('usuarios', $usuarios)
		->with('title', 'Lista de Usuários');
	}

	public function new(){
		$value = session('user_logged');
		$usuario = Usuario::find($value['id']);
		$permissoesAtivas = $usuario->empresa->permissao;
		$permissoesDoUsuario = [];
		$permissoesAtivas = json_decode($permissoesAtivas);
		$permissoesUsuario = [];

		if($value['super']){
			$permissoesAtivas = $this->detalhesMaster();
		}

		$menu = new Menu();
		$menu = $menu->getMenu();

		for($i=0; $i < sizeof($menu); $i++){
			$temp = false;
			foreach($menu[$i]['subs'] as $s){
				if(in_array($s['rota'], $permissoesAtivas)){
					$temp = true;
				}
			}
			$menu[$i]['ativo'] = $temp;
		}

		return view('usuarios/register')
		->with('usuarioJs', true)
		->with('permissoesAtivas', $permissoesAtivas)
		->with('permissoesUsuario', $permissoesUsuario)
		->with('menuAux', $menu)
		->with('permissoesDoUsuario', $permissoesDoUsuario)
		->with('title', 'Cadastrar Usuário');
	}

	private function detalhesMaster(){
		$menu = new Menu();
		$menu = $menu->getMenu();
		$temp = [];
		foreach($menu as $m){
			foreach($m['subs'] as $s){
				array_push($temp, $s['rota']);
			}
		}
		return $temp;
	}

	public function edit($id){
		$value = session('user_logged');

		$usuario = Usuario::
		where('id', $id)
		->first();
		if(valida_objeto($usuario)){

			$permissoesAtivas = $usuario->empresa->permissao;
			$permissoesUsuario = $usuario->permissao;
			$permissoesDoUsuario = [];
			$permissoesAtivas = json_decode($permissoesAtivas);
			$permissoesUsuario = json_decode($permissoesUsuario);

			if($value['super']){
				$permissoesAtivas = $this->detalhesMaster();
			}

			$menu = new Menu();
			$menu = $menu->getMenu();


			for($i=0; $i < sizeof($menu); $i++){
				$temp = false;
				foreach($menu[$i]['subs'] as $s){
					if(in_array($s['rota'], $permissoesAtivas)){
						$temp = true;
					}
				}
				$menu[$i]['ativo'] = $temp;
			}

			return view('usuarios/register')
			->with('usuarioJs', true)
			->with('usuario', $usuario)
			->with('permissoesAtivas', $permissoesAtivas)
			->with('permissoesUsuario', $permissoesUsuario)
			->with('menuAux', $menu)
			->with('title', 'Editar Usuários');
		}else{
			return redirect('/403');
		}
	}

	private function validaPermissao($request){
		$menu = new Menu();
		$arr = $request->all();
		$arr = (array) ($arr);
		$menu = $menu->getMenu();
		$temp = [];
		foreach($menu as $m){
			foreach($m['subs'] as $s){
				if(isset($arr[$s['rota']])){
					array_push($temp, $s['rota']);
				}
			}
		}

		return $temp;

	}

	public function save(Request $request){

		$this->_validate($request);

		$permissao = $this->validaPermissao($request);

		$locais = $request->local ? json_encode($request->local) : NULL;
		if($request->locais == null){
			$locais = "[-1]";
		}

		$request->merge([
			'local_padrao' => $request->local_padrao > 0 ? $request->local_padrao : null
		]);
		// dd($locais);
		$result = Usuario::create([
			'nome' => $request->nome,
			'login' => $request->login,
			'senha' => md5($request->senha),
			'adm' => $request->adm ? true : false,
			'somente_fiscal' => $request->somente_fiscal ? true : false,
			'caixa_livre' => $request->caixa_livre ? true : false,
			'ativo' => $request->ativo ? true : false,
			'email' => $request->email,
			'local_padrao' => $request->local_padrao,
			'locais' => $locais,
			'menu_representante' => isset($request->menu_representante) ? ($request->menu_representante ? true : false) : false,
			'rota_acesso' => $request->rota_acesso ?? '',
			'permissao' => json_encode($permissao),
			'empresa_id' => $this->empresa_id,
			'estorna_conta_pagar'   => $request->has('estorna_conta_pagar'),
			'max_estornos_conta_pagar'=> (int) $request->input('max_estornos_conta_pagar', 0),
			'estorna_conta_receber'   => $request->has('estorna_conta_receber'),
			'max_estornos_conta_receber'  => $request->input('max_estornos_conta_receber', 0),
		]);

		$this->criaConfigCaixa($result);

		if($result){
			session()->flash("mensagem_sucesso", "Usuário salvo!");
		}else{
			session()->flash('mensagem_erro', 'Erro ao criar usuário!');
		}

		return redirect('/usuarios');
	}

	private function criaConfigCaixa($usuario){
		$data = [
			'finalizar' => '',
			'reiniciar' => '',
			'editar_desconto' => '',
			'editar_acrescimo' => '',
			'editar_observacao' => '', 
			'setar_valor_recebido' => '',
			'forma_pagamento_dinheiro' => '',
			'forma_pagamento_debito' => '',
			'forma_pagamento_credito' => '',
			'setar_quantidade' => '',
			'forma_pagamento_pix' => '',
			'setar_leitor' => '',
			'finalizar_fiscal' => '',
			'finalizar_nao_fiscal' => '',
			'valor_recebido_automatico' => 0,
			'modelo_pdv' => 2,
			'balanca_valor_peso' => 0,
			'balanca_digito_verificador' => 5,
			'valor_recebido_automatico' => 0,
			'impressora_modelo' => 80,
			'cupom_modelo' => 2,
			'usuario_id' => $usuario->id,
			'mercadopago_public_key' => '',
			'mercadopago_access_token' => '',
			'tipos_pagamento' => '["01","02","03","04","05","06","10","11","12","13","14","15","16","17","90","99"]',
			'tipo_pagamento_padrao' => '01'
		];
		ConfigCaixa::create($data);
	}

	public function update(Request $request){

		$this->_validate($request, true);
		$permissao = $this->validaPermissao($request);

		$usr = Usuario::
		where('id', $request->id)
		->first();

		$locais = $request->local ? json_encode($request->local) : null;
		
		// $request->merge([
		// 	'local_padrao' => $request->local_padrao > 0 ? $request->local_padrao : null
		// ]);
		
		$usr->nome = $request->nome;
		$usr->login = $request->login;
		$usr->locais = $locais;
		$usr->email = $request->email;
		$usr->local_padrao = $request->local_padrao;
		$usr->rota_acesso = $request->rota_acesso ?? '';
		if($request->senha){
			$usr->senha = md5($request->senha);
		}
		
		$usr->adm = $request->adm ? true : false;
		$usr->somente_fiscal = $request->somente_fiscal ? true : false;
		$usr->caixa_livre = $request->caixa_livre ? true : false;
		$usr->permite_desconto = $request->permite_desconto ? true : false;
		$usr->estorna_conta_pagar = $request->estorna_conta_pagar ? true : false;
		$usr->max_estornos_conta_pagar = (int) $request->input('max_estornos_conta_pagar', 0);
		$usr->estorna_conta_receber = $request->has('estorna_conta_receber');
		$usr->max_estornos_conta_receber = $request->input('max_estornos_conta_receber', 0);
		if(isset($request->menu_representante)){
			$usr->menu_representante = $request->menu_representante ? true : false;
		}
		$usr->ativo = $request->ativo ? true : false;
		$usr->permissao = json_encode($permissao);

		// echo $usr->local_padrao;
		// die;
		$result = $usr->save();
		if($result){
			session()->flash("mensagem_sucesso", "Usuário atualizado!");
		}else{
			session()->flash('mensagem_erro', 'Erro ao atualizar usuário!');
		}

		return redirect('/usuarios');
	}

	public function delete($id){
		$usuario = Usuario::
		where('id', $id)
		->first();

		$usuarios = Usuario::
		where('empresa_id', $this->empresa_id)
		->get();

		if(sizeof($usuarios) == 1){
			session()->flash('mensagem_erro', 'Não é possivel remover o ultimo usuário!');
			return redirect()->back();
		}
		try{
			if(valida_objeto($usuario)){

				$usuario->config()->delete();
				if($usuario->delete()){
					session()->flash("mensagem_sucesso", "Usuário removido!");
				}else{
					session()->flash('mensagem_erro', 'Erro ao remover usuário!');
				}

				return redirect('/usuarios');
			}else{
				return redirect('/403');
			}
		}catch(\Exception $e){
			session()->flash('mensagem_erro', 'Algo deu errado ' . $e->getMessage());
		}
	}


	private function _validate(Request $request, $update = false){
		$rules = [
			'nome' => 'required',
			'email' => 'required|email',
			'login' => ['required', \Illuminate\Validation\Rule::unique('usuarios')->ignore($request->id)],
			'senha' => !$update ? 'required' : '',
			'max_estornos_conta_pagar' => 'required|integer|min:0',
			'max_estornos_conta_receber' => 'required|integer|min:0',
		];

		$messages = [
			'nome.required' => 'O campo nome é obrigatório.',
			'email.required' => 'O campo email é obrigatório.',
			'email.email' => 'Email inválido',
			'login.required' => 'O campo login é obrigatório.',
			'senha.required' => 'O campo senha é obrigatório',
			'login.unique' => 'Usuário já cadastrado no sistema.'
		];

		$this->validate($request, $rules, $messages);
	}

	public function setTema(Request $request){
		$tema = $request->tema;
		$tema_menu = $request->tema_menu;
		$tipo_menu = $request->tipo_menu;

		$id = $value = session('user_logged')['id'];
		$usuario = Usuario::find($id);
		$usuario->tema = $tema;
		$usuario->tema_menu = $tema_menu;
		$usuario->tipo_menu = $tipo_menu;
		$usuario->save();
		session()->flash("mensagem_sucesso", "Tema salvo!");
		return redirect()->back();

	}

	public function historico($id){
		$usuario = Usuario::find($id);

		if(valida_objeto($usuario)){

			$acessos = UsuarioAcesso::
			where('usuario_id', $id)
			->paginate(50);
			
			return view('usuarios/historico')
			->with('usuario', $usuario)
			->with('acessos', $acessos)
			->with('title', 'Histórico de Usuário');
		}else{
			return redirect('/403');
		}
	}
}
