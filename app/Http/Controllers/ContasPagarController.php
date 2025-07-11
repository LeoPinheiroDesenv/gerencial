<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContaPagar;
use App\Models\CategoriaConta;
use App\Models\Fornecedor;
use App\Models\ConfigNota;
use App\Models\ContaEmpresa;
use App\Models\ItemContaEmpresa;
use Dompdf\Dompdf;
use App\Utils\ContaEmpresaUtil;
use Illuminate\Support\Str;
use App\Models\EstornoContaPagar;
use App\Models\Usuario; 
use Illuminate\Support\Facades\DB;

class ContasPagarController extends Controller
{
	protected $empresa_id = null;
	protected $util;

	public function __construct(ContaEmpresaUtil $util){
		$this->util = $util;

		$this->middleware(function ($request, $next) {
			$this->empresa_id = $request->empresa_id;
			$value = session('user_logged');
			if(!$value){
				return redirect("/login");
			}
			return $next($request);
		});
	}

	private function comRetencoes(){
		return ContaPagar::where('empresa_id', $this->empresa_id)
		->where('valor_inss', '>', 0)
		->orWhere('valor_iss', '>', 0)
		->orWhere('valor_pis', '>', 0)
		->orWhere('valor_cofins', '>', 0)
		->orWhere('valor_ir', '>', 0)
		->orWhere('outras_retencoes', '>', 0)
		->first();
	}

	public function index(){
		__saveRedirect($this->empresa_id, '', 'contas_pagar');

		$comRetencoes = $this->comRetencoes();

		$permissaoAcesso = __getLocaisUsarioLogado();
		$local_padrao = __get_local_padrao();
		if($local_padrao == -1){
			$local_padrao = null;
		}
		$contas = ContaPagar::
		whereBetween('data_vencimento', [date("Y-m-d"), 
			date('Y-m-d', strtotime('+1 month'))])
		->where('empresa_id', $this->empresa_id)
		->orderBy('data_vencimento', 'asc')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('filial_id', $value);
				}
			}
		})
		->when($local_padrao != NULL, function ($query) use ($local_padrao) {
			$query->where('filial_id', $local_padrao);
		})
		->get();

		$somaContas = $this->somaCategoriaDeContas($contas);

		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->get();

		$fornecedores = Fornecedor::where('empresa_id', $this->empresa_id)
		->get();

		return view('contaPagar/list')
		->with('contas', $contas)
		->with('comRetencoes', $comRetencoes)
		->with('categorias', $categorias)
		->with('fornecedores', $fornecedores)
		->with('graficoJs', true)
		->with('somaContas', $somaContas)
		->with('infoDados', "Dos próximos 30 dias")
		->with('title', 'Contas a Pagar');
	}

	private function somaCategoriaDeContas($contas){
		$arrayCategorias = $this->criaArrayDecategoriaDeContas();
		$temp = [];
		foreach($contas as $c){
			foreach($arrayCategorias as $a){
				if($c->categoria->nome == $a){
					if(isset($temp[$a])){
						$temp[$a] = $temp[$a] + $c->valor_integral;
					}else{
						$temp[$a] = $c->valor_integral;
					}
				}
			}
		}

		return $temp;
	}

	private function criaArrayDecategoriaDeContas(){
		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->get();
		
		$temp = [];
		foreach($categorias as $c){
			array_push($temp, $c->nome);
		}

		return $temp;
	}

	public function filtro(Request $request){

		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;
		$fornecedorId = $request->fornecedorId;
		$status = $request->status;
		$filial_id = $request->filial_id;

		$contas = [];
		$comRetencoes = $this->comRetencoes();

		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		__saveRedirect($this->empresa_id, $url, 'contas_pagar');

		$permissaoAcesso = __getLocaisUsarioLogado();

		$c = ContaPagar::
		select('conta_pagars.*')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('conta_pagars.filial_id', $value);
				}
			}
		})
		->when($filial_id, function ($query) use ($filial_id) {
			$filial_id = $filial_id == -1 ? null : $filial_id;
			return $query->where('filial_id', $filial_id);
		});

		if($fornecedorId != "null"){
			// $c->join('fornecedors', 'fornecedors.id' , '=', 'conta_pagars.fornecedor_id')
			// ->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%");

			$c->where('fornecedor_id', $fornecedorId);
		}
		if($dataInicial && $dataFinal){
			if($request->tipo_filtro_data == 1){
				$c->whereBetween('conta_pagars.data_vencimento', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}elseif($request->tipo_filtro_data == 2){
				$c->whereBetween('conta_pagars.date_register', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}else{
				$c->whereBetween('conta_pagars.data_pagamento', 
					[
						$this->parseDate($dataInicial)  . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}
		}
		if($status != 'todos'){
			if($status == 'pago'){
				$c->where('status', true);
			} else if($status == 'pendente'){
				$c->where('status', false);
			}else if($status == 'vencido'){
				$c->where('status', false)
				->whereDate('data_vencimento', '<=', date('Y-m-d'));
			}
		}
		if($request->tipo_filtro_data == 3){
			$c->where('status', true);
		}

		if($request->categoria != 'todos'){
			$c->where('categoria_id', $request->categoria);
		}
		if($request->tipo_pagamento){
			$c->where('conta_pagars.tipo_pagamento', $request->tipo_pagamento);
		}
		$c->where('conta_pagars.empresa_id', $this->empresa_id);

		if($request->tipo_filtro_data == 1){
			$c->orderBy('conta_pagars.data_vencimento', 'asc');
		}

		if($request->numero_nota_fiscal){
			$c->where('conta_pagars.numero_nota_fiscal', $request->numero_nota_fiscal);
		}
		$temp = $c->get();
		foreach($temp as $t){
			array_push($contas, $t);
		}

		$c = ContaPagar::
		select('conta_pagars.*')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('conta_pagars.filial_id', $value);
				}
			}
		})
		->when($filial_id, function ($query) use ($filial_id) {
			$filial_id = $filial_id == -1 ? null : $filial_id;
			return $query->where('conta_pagars.filial_id', $filial_id);
		});

		if($fornecedorId != "null"){
			// $c->join('compras', 'compras.id' , '=', 'conta_pagars.compra_id')
			// ->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id')
			// ->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%");

			$c->join('compras', 'compras.id' , '=', 'conta_pagars.compra_id')
			->where('compras.fornecedor_id', $fornecedorId);
		}
		if($dataInicial && $dataFinal){
			if($request->tipo_filtro_data == 1){
				$c->whereBetween('conta_pagars.data_vencimento', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal)
					]
				);
			}elseif($request->tipo_filtro_data == 2){
				$c->whereBetween('conta_pagars.date_register', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal, true)
					]
				);
			}else{
				$c->whereBetween('conta_pagars.data_pagamento', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal, true)
					]
				);
			}
		}
		if($status != 'todos'){
			if($status == 'pago'){
				$c->where('status', true);
			} else if($status == 'pendente'){
				$c->where('status', false);
			}else if($status == 'vencido'){
				$c->where('status', false)
				->whereDate('data_vencimento', '<=', date('Y-m-d'));
			}
		}
		if($request->tipo_filtro_data == 3){
			$c->where('status', true);
		}
		if($request->categoria != 'todos'){
			$c->where('categoria_id', $request->categoria);
		}
		if($request->tipo_pagamento){
			$c->where('conta_pagars.tipo_pagamento', $request->tipo_pagamento);
		}
		if($request->numero_nota_fiscal){
			$c->where('conta_pagars.numero_nota_fiscal', $request->numero_nota_fiscal);
		}
		$c->where('conta_pagars.empresa_id', $this->empresa_id);
		$temp = $c->get();
		foreach($temp as $t){
			if(!$this->validaInArray($t, $contas)){
				array_push($contas, $t);
			}
		}

		$somaContas = $this->somaCategoriaDeContas($contas);

		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->get();

		$fornecedores = Fornecedor::where('empresa_id', $this->empresa_id)
		->get();

		return view('contaPagar/list')
		->with('contas', $contas)
		->with('comRetencoes', $comRetencoes)
		->with('fornecedorId', $fornecedorId)
		->with('fornecedores', $fornecedores)
		->with('filial_id', $filial_id)
		->with('categorias', $categorias)
		->with('dataInicial', $dataInicial)
		->with('dataFinal', $dataFinal)
		->with('status', $status)
		->with('url', $url)
		->with('tipo_filtro_data', $request->tipo_filtro_data)
		->with('somaContas', $somaContas)
		->with('graficoJs', true)
		->with('paraImprimir', true)
		->with('categoria', $request->categoria)
		->with('tipo_pagamento', $request->tipo_pagamento)
		->with('numero_nota_fiscal', $request->numero_nota_fiscal)
		
		->with('infoDados', "Contas filtradas")
		->with('title', 'Filtro Contas a Pagar');
	}

	private function validaInArray($ct, $contas){
		foreach($contas as $c){
			if($c->id == $ct->id) return true;
		}
		return false;
	}

	public function salvarParcela(Request $request) {
		$parcela = $request->parcela;
	
		$valorParcela = str_replace(",", ".", $parcela['valor_parcela']);
		$valorParcela = str_replace(" ", "", $valorParcela);
	
		// Busca a categoria padrão
		$categoria = CategoriaConta::where('empresa_id', $this->empresa_id)
			->where('tipo', 'pagar')
			->first();
	
		// Se `categoria_conta_id` for enviado, busca a categoria específica
		if (isset($parcela['categoria_conta_id']) && $parcela['categoria_conta_id']) {
			$categoria = CategoriaConta::find($parcela['categoria_conta_id']);
		}
	
		$categoriaId = $categoria ? $categoria->id : 1; // Fallback para 1 caso não exista
	
		// Criação da conta a pagar
		$result = ContaPagar::create([
			'compra_id' => $parcela['compra_id'],
			'data_vencimento' => $this->parseDate($parcela['vencimento']),
			'data_pagamento' => $this->parseDate($parcela['vencimento']),
			'valor_integral' => $valorParcela,
			'valor_pago' => 0,
			'status' => false,
			'referencia' => $parcela['referencia'],
			'categoria_id' => $categoriaId,
			'categoria_conta_id' => $categoriaId, // Categoria de conta com fallback
			'empresa_id' => $this->empresa_id,
			'numero_nota_fiscal' => isset($parcela['numero_nota_fiscal']) ? $parcela['numero_nota_fiscal'] : 0
		]);
	
		echo json_encode($parcela);
	}	

	public function detalhes($id){
		$conta = ContaPagar::findOrFail($id);
		return view('contaPagar/detalhes')
		->with('conta', $conta)
		->with('title', 'Detalhes da Conta');
	}

	public function arquivo($id){
		$item = ContaPagar::findOrFail($id);
		if(file_exists(public_path('arquivos_conta/').$item->arquivo)){
			return redirect('/arquivos_conta/'.$item->arquivo);
		}
	}

	public function save(Request $request){
		// $parcelas = json_decode($request->parcelas);
		// echo "<pre>";
		// print_r($parcelas);
		// echo "</pre>";
		if(strlen($request->recorrencia) == 5){
			$valid = $this->validaRecorrencia($request->recorrencia);
			if(!$valid){
				session()->flash('mensagem_erro', 'Valor recorrente inválido!');
				return redirect('/contasPagar/new');
			}
		}

		$request->merge([
			'filial_id' => $request->filial_id == -1 ? null : $request->filial_id,
			'valor' => $request->valor_final ? __replace($request->valor_final) : __replace($request->valor)
		]);

		$this->_validate($request);
		$parcelas = json_decode($request->parcelas);


		$fileName = "";
		if(!is_dir(public_path('arquivos_conta'))){
			mkdir(public_path('arquivos_conta'), 0777, true);
		}

		if($request->hasFile('file')){
    		//unlink anterior
			$file = $request->file('file');
			$extensao = $file->getClientOriginalExtension();
			$fileName = Str::random(20).".$extensao";
			$file->move(public_path('arquivos_conta'), $fileName);
		}
		
		$result = ContaPagar::create([
			'compra_id' => null,
			'data_register'    => $this->parseDate($request->date_register) . ' ' . date('H:i:s'),
			'data_vencimento' => $this->parseDate($request->vencimento),
			'data_pagamento' => $request->status ? $this->parseDate($request->data_pagamento) . ' ' . date('H:i:s') : null,
			'valor_integral' => $request->valor,
			'valor_pago' => $request->status ? __replace($request->valor_pago) : 0,
			'status' => $request->status ? true : false,
			'referencia' => $request->referencia . (sizeof($parcelas) > 0 ? " - parcela 1" . "/".(sizeof($parcelas)+1) : ""),
			'observacao' => $request->observacao ?? "",
			'tipo_pagamento' => $request->tipo_pagamento ?? '',
			'numero_nota_fiscal' => $request->numero_nota_fiscal ?? 0,
			'fornecedor_id' => $request->fornecedor_id,
			'categoria_id' => $request->categoria_id,
			'categoria_conta_id' => 1,
			'empresa_id' => $this->empresa_id,
			'filial_id' => $request->filial_id,
			'valor_inss' => $request->valor_inss ? __replace($request->valor_inss) : 0,
			'valor_iss' => $request->valor_iss ? __replace($request->valor_iss) : 0,
			'valor_pis' => $request->valor_pis ? __replace($request->valor_pis) : 0,
			'valor_cofins' => $request->valor_cofins ? __replace($request->valor_cofins) : 0,
			'valor_ir' => $request->valor_ir ? __replace($request->valor_ir) : 0,
			'outras_retencoes' => $request->outras_retencoes ? __replace($request->outras_retencoes) : 0,
			'arquivo' => $fileName
		]);
		

		if(sizeof($parcelas) > 0){

			foreach($parcelas as $key => $p){

				$result = ContaPagar::create([
					'compra_id' => null,
					'data_vencimento' => $p->vencimento,
					'data_pagamento' => $request->status ? $p->vencimento : null,
					'valor_integral' => str_replace(",", ".", $p->valor),
					'valor_pago' => $request->valor_pago ? __replace($request->valor_pago) : 0,
					'status' => $request->status ? true : false,
					'tipo_pagamento' => $request->tipo_pagamento ?? '',
					'numero_nota_fiscal' => $request->numero_nota_fiscal ?? 0,
					'fornecedor_id' => $request->fornecedor_id,
					'referencia' => $request->referencia . " - parcela " .($key+2) . "/".(sizeof($parcelas)+1),
					'categoria_id' => $request->categoria_id,
					'categoria_conta_id' => 1,
					'empresa_id' => $this->empresa_id
				]);
			}
		}

		session()->flash('mensagem_sucesso', 'Registro inserido!');

		return redirect('/contasPagar');
	}

	public function update(Request $request){


		$this->_validate($request);
		$conta = ContaPagar::
		where('id', $request->id)
		->where('empresa_id', $this->empresa_id)
		->first();

		$request->merge([
			'filial_id' => $request->filial_id == -1 ? null : $request->filial_id
		]);

		$conta->date_register = $this->parseDate($request->date_register);
		$conta->data_vencimento = $this->parseDate($request->vencimento);
		$conta->referencia = $request->referencia;
		$conta->observacao = $request->observacao ?? "";
		$conta->valor_integral = str_replace(",", ".", $request->valor);
		$conta->categoria_id = $request->categoria_id;
		$conta->tipo_pagamento = $request->tipo_pagamento ?? '';
		$conta->numero_nota_fiscal = $request->numero_nota_fiscal ?? 0;

		$conta->valor_inss = $request->valor_inss ? __replace($request->valor_inss) : 0;
		$conta->valor_iss = $request->valor_iss ? __replace($request->valor_iss) : 0;
		$conta->valor_pis = $request->valor_pis ? __replace($request->valor_pis) : 0;
		$conta->valor_cofins = $request->valor_cofins ? __replace($request->valor_cofins) : 0;
		$conta->valor_ir = $request->valor_ir ? __replace($request->valor_ir) : 0;
		$conta->outras_retencoes = $request->outras_retencoes ? __replace($request->outras_retencoes) : 0;

		// $conta->fornecedor_id = $request->fornecedor_id;
		$conta->filial_id = $request->filial_id;

		if($request->hasFile('file')){
    		//unlink anterior

			if(file_exists(public_path('arquivos_conta/').$conta->arquivo) && $conta->arquivo != ''){
				unlink(public_path('arquivos_conta/').$conta->arquivo);
			}
			$file = $request->file('file');
			$extensao = $file->getClientOriginalExtension();
			$fileName = Str::random(20).".$extensao";
			$file->move(public_path('arquivos_conta'), $fileName);

			$conta->arquivo = $fileName;
		}

		$result = $conta->save();

		if($result){
			session()->flash('mensagem_sucesso', 'Registro editado!');
		}else{
			session()->flash('mensagem_erro', 'Ocorreu um erro!');
		}

		$rota = __getRedirect($this->empresa_id, 'contas_pagar');
		if($rota != ""){
			return redirect($rota);
		}
		return redirect('/contasPagar');

	}

	private function calculaRecorrencia($recorrencia){
		if(strlen($recorrencia) == 5){
			$dataAtual = date("Y-m");
			$dif = strtotime($this->parseRecorrencia($recorrencia)) - strtotime($dataAtual);

			$meses = floor($dif / (60 * 60 * 24 * 30));

			return $meses;
		}
		return 0;
	}

	public function validaRecorrencia($rec){
		$mesAutal = date('m');
		$anoAtual = date('y');
		$temp = explode("/", $rec);
		if($anoAtual > $temp[1]) return false;
		if((int)$temp[0] <= $mesAutal && $anoAtual == $temp[1]) return false;

		return true;
	}

	private function _validate(Request $request){
		$rules = [
			'fornecedor_id' => $request->id == 0 ? 'required' : '',
			'referencia' => 'required',
			'valor' => 'required',
			'observacao' => 'max:100',
			'vencimento' => 'required',
		];

		$messages = [
			'referencia.required' => 'O campo referencia é obrigatório.',
			'observacao.max' => 'Máximo de 100 caracteres.',
			'fornecedor_id.required' => 'O campo fornecedor é obrigatório.',
			'valor.required' => 'O campo valor é obrigatório.',
			'vencimento.required' => 'O campo vencimento é obrigatório.'
		];
		$this->validate($request, $rules, $messages);
	}

	public function new(){
		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->orderBy('nome')
		->get();

		if(sizeof($categorias) == 0){
			session()->flash('mensagem_alerta', 'Cadastre uma categoria com o tipo pagar!');
			return redirect('/categoriasConta');
		}

		$fornecedores = Fornecedor::
		where('empresa_id', $this->empresa_id)
		->get();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		if($config == null){
			session()->flash('mensagem_alerta', 'Informe a configuração do emitente!');
			return redirect('/configNF');
		}

		return view('contaPagar/register')
		->with('categorias', $categorias)
		->with('fornecedores', $fornecedores)
		->with('config', $config)
		->with('title', 'Cadastrar Contas a Pagar');
	}

	public function edit($id){
		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->orderBy('nome')
		->get();

		$conta = ContaPagar::
		where('id', $id)
		->where('empresa_id', $this->empresa_id)
		->first();

		$fornecedores = Fornecedor::
		where('empresa_id', $this->empresa_id)
		->get();

		if($conta->fornecedor_id == null){
			if($conta->compra->fornecedor){
				$conta->fornecedor_id = $conta->compra->fornecedor_id;
				$conta->save();
			}
		}

		if(valida_objeto($conta)){

			return view('contaPagar/register')
			->with('conta', $conta)
			->with('fornecedores', $fornecedores)
			->with('categorias', $categorias)
			->with('title', 'Editar Contas a Pagar');
		}else{
			return redirect('/403');
		}
	}

	public function pagar($id){
		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->get();

		$conta = ContaPagar::findOrFail($id);

		if(valida_objeto($conta)){

			$contasEmpresa = ContaEmpresa::where('empresa_id', $this->empresa_id)
			->where('status', 1)->get();
			return view('contaPagar/pagar')
			->with('conta', $conta)
			->with('contasEmpresa', $contasEmpresa)
			->with('categorias', $categorias)
			->with('title', 'Pagar Conta');
		}else{
			return redirect('/403');
		}
	}
	
	public function estorno($id)
	{
		$conta = ContaPagar::findOrFail($id);
	
		$sess        = session('user_logged');
		$currentUser = Usuario::find($sess['id']);
	
		// quantos já fez
		$feito = EstornoContaPagar::where([
			'empresa_id'     => $this->empresa_id,
			'conta_pagar_id' => $conta->id,
			'usuario_id'     => $currentUser->id,
		])->value('quantidade') ?? 0;
	
		$limite = $currentUser->max_estornos_conta_pagar;
	
		// agora inclui a condição de limite
		$requiresAdminAuth = ! $currentUser->adm
						   && (
								! $currentUser->estorna_conta_pagar 
							 || $feito >= $limite
							  );
	
		return view('contaPagar/estorno', compact('conta','requiresAdminAuth'))
			   ->with('title','Estornar Conta');
	}

	public function estornoConta(Request $request)
	{
		// 1) busca conta e usuário
		$conta       = ContaPagar::findOrFail($request->id);
		$sess        = session('user_logged');
		$currentUser = Usuario::find($sess['id']);
	
		// 2) quantos já fez desta conta
		$feito = EstornoContaPagar::where([
			'empresa_id'     => $this->empresa_id,
			'conta_pagar_id' => $conta->id,
			'usuario_id'     => $currentUser->id,
		])->value('quantidade') ?? 0;
	
		$limite = $currentUser->max_estornos_conta_pagar;
	
		// 3) precisa de senha de admin se:
		//    • não for admin
		//    • E (não tem a flag OU já atingiu o limite)
		$needsAdminPassword = ! $currentUser->adm
							&& (
								 ! $currentUser->estorna_conta_pagar
							  || $feito >= $limite
							   );
	
		// 4) se precisa, valida o campo admin_password
		if ($needsAdminPassword) {
			$senhaAdmin = $request->input('admin_password');
			$admin = Usuario::where('empresa_id', $this->empresa_id)
							->where('adm', true)
							->where('senha', md5($senhaAdmin))
							->first();
			if (! $admin) {
				session()->flash('mensagem_erro', 'Senha de administrador inválida.');
				return redirect()->back()->withInput();
			}
			$executor = $admin;
		} else {
			$executor = $currentUser;
		}
	
		// 5) reforça permissão/limite se executor não for admin
		if (! $executor->adm) {
			if (! $executor->estorna_conta_pagar) {
				session()->flash('mensagem_erro', 'Você não tem permissão para estornar contas a pagar.');
				return redirect()->back();
			}
			if ($executor->id === $currentUser->id && $feito >= $limite) {
				session()->flash('mensagem_erro', "Você já atingiu o máximo de {$limite} estornos para esta conta.");
				return redirect()->back();
			}
		}
	
		// 6) transação de estorno...
		try {
			DB::transaction(function() use ($conta, $request, $executor) {
				$conta->update([
					'status'         => false,
					'estorno'        => true,
					'valor_pago'     => 0,
					'motivo_estorno' => $request->motivo,
				]);
				$registro = EstornoContaPagar::firstOrNew([
					'empresa_id'     => $conta->empresa_id,
					'conta_pagar_id' => $conta->id,
					'usuario_id'     => $executor->id,
				]);
				$registro->quantidade = ($registro->quantidade ?? 0) + 1;
				$registro->save();
			});
			session()->flash('mensagem_sucesso', 'Conta estornada!');
		} catch (\Exception $e) {
			session()->flash('mensagem_erro', 'Algo deu errado: ' . $e->getMessage());
		}
	
		// 7) redireciona
		$rota = __getRedirect($this->empresa_id, 'contas_pagar');
		return $rota ? redirect($rota) : redirect('/contasPagar');
	}

	public function validateAdminPassword(Request $request)
    {
    $senha = md5($request->password);
    $sess = session('user_logged');
    $admin = Usuario::where('empresa_id', $this->empresa_id)
                    ->where('adm', true)
                    ->where('senha', $senha)
                    ->first();
    return response()->json(['success' => (bool) $admin]);
    }

	public function pagarConta(Request $request){
		$conta = ContaPagar::
		where('id', $request->id)
		->first();

		$valor = str_replace(".", "", $request->valor);
		$valor = str_replace(",", ".", $valor);
		$conta->multa = $request->multa ? __replace($request->multa) : 0;
		$conta->juros = $request->juros ? __replace($request->juros) : 0;
		$conta->observacao_baixa = $request->observacao_baixa ?? "";
		$conta->save();
		$vIntegral = $conta->valor_integral + $conta->juros + $conta->multa;

		if((float)$vIntegral > (float)$valor){

			return view('contaPagar/valorDivergente')
			->with('conta', $conta)
			->with('valor', $valor)
			->with('tipo_pagamento', $request->tipo_pagamento)
			->with('title', 'Pagar Conta');
		}

		$conta->status = true;
		$conta->tipo_pagamento = $request->tipo_pagamento;
		
		$conta->valor_pago = $valor + $conta->juros + $conta->multa;
		$dtPag = \Carbon\Carbon::parse(str_replace("/", "-", $request->data_pagamento))->format('Y-m-d');
		$dtPag .= " " . date("H:i:s");
		$conta->data_pagamento = $dtPag;

		$result = $conta->save();

		if(isset($request->conta_id)){
			$tipoPagamento = \App\Models\Venda::getTipoPagamentoNFe($request->tipo_pagamento);

			$data = [
				'conta_id' => $request->conta_id,
				'descricao' => "Pagamento da conta " . $conta->referencia,
				'tipo_pagamento' => $tipoPagamento,
				'valor' => $valor,
				'tipo' => 'saida'
			];
			$itemContaEmpresa = ItemContaEmpresa::create($data);
			$this->util->atualizaSaldo($itemContaEmpresa);
		}
		if($result){
			session()->flash('mensagem_sucesso', 'Conta paga!');
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}

		$rota = __getRedirect($this->empresa_id, 'contas_pagar');
		if($rota != ""){
			return redirect($rota);
		}
		return redirect('/contasPagar');
	}

	public function pagarComDivergencia(Request $request){
		$conta = ContaPagar::find($request->id);
		$valor = __replace($request->valor);
		$nova_data = $request->nova_data;

		if($request->somente_finalizar == 0){
			$res = ContaPagar::create([
				'fornecedor_id' => $conta->fornecedor_id,
				'data_vencimento' => $nova_data,
				'data_pagamento' => $conta->data_pagamento,
				'valor_integral' => $conta->valor_integral - $valor,
				'valor_pago' => 0,
				'status' => false,
				'referencia' => $conta->referencia,
				'categoria_id' => $conta->categoria_id,
				'categoria_conta_id' => 1,
				'empresa_id' => $this->empresa_id,
			]);
		}

		$conta->status = true;
		$conta->valor_pago = $request->valor;
		// $conta->valor_integral = $request->valor;
		$conta->tipo_pagamento = $request->tipo_pagamento;
		$conta->data_pagamento = date("Y-m-d") . " " . date('H:i:s');

		$result = $conta->save();
		if($result){
			if($request->somente_finalizar == 0){
				$id = $res->id;
				session()->flash('mensagem_sucesso', 'Conta paga parcialmente, uma nova foi criada com ID: ' . $id);
			}else{
				session()->flash('mensagem_sucesso', 'Conta paga!');
			}
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/contasPagar');
	}

	public function delete($id){
		$conta = ContaPagar
		::where('id', $id)
		->first();
		if(valida_objeto($conta)){
			if($conta->delete()){

				session()->flash('mensagem_sucesso', 'Registro removido!');
			}else{

				session()->flash('mensagem_erro', 'Erro!');
			}
			return redirect()->back();
		}else{
			return redirect('/403');
		}
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	private function parseRecorrencia($rec){
		$temp = explode("/", $rec);
		$rec = "01/".$temp[0]."/20".$temp[1];
		//echo $rec;
		return date('Y-m', strtotime(str_replace("/", "-", $rec)));
	}

	public function relatorio(Request $request){
		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;
		$fornecedorId = $request->fornecedorId;
		$status = $request->status;
		$filial_id = $request->filial_id;
		$tipo_pagamento = $request->tipo_pagamento;

		$contas = [];
		$permissaoAcesso = __getLocaisUsarioLogado();

		$c = ContaPagar::
		select('conta_pagars.*')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('conta_pagars.filial_id', $value);
				}
			}
		})
		->when($filial_id, function ($query) use ($filial_id) {
			$filial_id = $filial_id == -1 ? null : $filial_id;
			return $query->where('conta_pagars.filial_id', $filial_id);
		});
		if($fornecedorId != "null"){

			// $c->join('fornecedors', 'fornecedors.id' , '=', 'conta_pagars.fornecedor_id')
			// ->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%");

			$c->where('fornecedor_id', $fornecedorId);
		}
		if($dataInicial && $dataFinal){
			if($request->tipo_filtro_data == 1){
				$c->whereBetween('conta_pagars.data_vencimento', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}else if($request->tipo_filtro_data == 2){
				$c->whereBetween('conta_pagars.date_register', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}else{
				$c->whereBetween('conta_pagars.data_pagamento', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}
		}
		if($status != 'todos'){
			if($status == 'pago'){
				$c->where('status', true);
			} else if($status == 'pendente'){
				$c->where('status', false);
			}else if($status == 'vencido'){
				$c->where('status', false)
				->whereDate('data_vencimento', '<=', date('Y-m-d'));
			}
		}
		if($request->tipo_filtro_data == 3){
			$c->where('status', true);
		}
		if($request->categoria != 'todos'){
			$c->where('categoria_id', $request->categoria);
		}
		if($tipo_pagamento){
			$c->where('tipo_pagamento', $tipo_pagamento);
		}
		$c->where('conta_pagars.empresa_id', $this->empresa_id);

		if($request->tipo_filtro_data == 1){
			$c->orderBy('conta_pagars.data_vencimento', 'asc');
		}

		if($request->numero_nota_fiscal){
			$c->where('conta_pagars.numero_nota_fiscal', $request->numero_nota_fiscal);
		}
		$temp = $c->get();

		foreach($temp as $t){
			array_push($contas, $t);
		}

		$c = ContaPagar::
		select('conta_pagars.*')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('conta_pagars.filial_id', $value);
				}
			}
		})
		->when($filial_id, function ($query) use ($filial_id) {
			$filial_id = $filial_id == -1 ? null : $filial_id;
			return $query->where('conta_pagars.filial_id', $filial_id);
		});
		
		if($fornecedorId != "null"){
			// $c->join('compras', 'compras.id' , '=', 'conta_pagars.compra_id')
			// ->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id')
			// ->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%");

			$c->join('compras', 'compras.id' , '=', 'conta_pagars.compra_id')
			->where('compras.fornecedor_id', $fornecedorId);
		}
		if($dataInicial && $dataFinal){
			if($request->tipo_filtro_data == 1){
				$c->whereBetween('conta_pagars.data_vencimento', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}elseif($request->tipo_filtro_data == 2){
				$c->whereBetween('conta_pagars.date_register', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}else{
				$c->whereBetween('conta_pagars.data_pagamento', 
					[
						$this->parseDate($dataInicial) . " 00:00:00",
						$this->parseDate($dataFinal) . " 23:59:59"
					]
				);
			}
		}
		if($status != 'todos'){
			if($status == 'pago'){
				$c->where('status', true);
			} else if($status == 'pendente'){
				$c->where('status', false);
			}else if($status == 'vencido'){
				$c->where('status', false)
				->whereDate('data_vencimento', '<=', date('Y-m-d'));
			}
		}
		if($request->tipo_filtro_data == 3){
			$c->where('status', true);
		}
		if($request->categoria != 'todos'){
			$c->where('categoria_id', $request->categoria);
		}
		if($request->tipo_pagamento){
			$c->where('tipo_pagamento', $request->tipo_pagamento);
		}
		if($request->numero_nota_fiscal){
			$c->where('conta_pagars.numero_nota_fiscal', $request->numero_nota_fiscal);
		}
		$c->where('conta_pagars.empresa_id', $this->empresa_id);
		$temp = $c->get();
		foreach($temp as $t){
			if(!$this->validaInArray($t, $contas)){
				array_push($contas, $t);
			}
		}

		$p = view('relatorios/relatorio_contas_pagar')
		->with('data_inicial', $request->data_inicial)
		->with('data_final', $request->data_final)
		->with('contas', $contas);

		// return $p;

		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);

		$pdf = ob_get_clean();

		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório Contas a Pagar.pdf", array("Attachment" => false));

	}

	public function pagarMultiplos($ids){
		$ids = explode(",", $ids);
		$somaTotal = 0;
		$contas = [];

		foreach($ids as $id){
			$conta = ContaPagar::find($id);
			// if($conta){
				// $conta->status = true;
				// $conta->valor_pago = $conta->valor_integral;
				// $conta->data_pagamento = date('Y-m-d H:i:s');
				// $conta->save();
			// }
			if($conta->empresa_id != $this->empresa_id){
				session()->flash('mensagem_erro', "Erro inesperado!");
				return redirect()->back();
			}
			$somaTotal += $conta->valor_integral;

			array_push($contas, $conta);
		}

		$title = 'Pagar contas';

		$contasEmpresa = ContaEmpresa::where('empresa_id', $this->empresa_id)
		->where('status', 1)->get();

		return view('contaPagar/pagar_multi', 
			compact('somaTotal', 'title', 'contas', 'ids', 'contasEmpresa')
		);
	}

	public function pagarMultiploStore(Request $request){
		$valorRecebido = __replace($request->valor);
		$somaTotal = $request->somaTotal;
		$tipo_pagamento = $request->tipo_pagamento;
		// dd($request->all());
		try{
			for($i=0; $i<sizeof($request->conta_pagar_id); $i++){
				$conta = ContaPagar::findOrFail($request->conta_pagar_id[$i]);

				if($conta->empresa_id == $this->empresa_id){
					$conta->status = true;
					$conta->valor_pago = $conta->valor_integral;
					$conta->tipo_pagamento = $request->tipo_pagamento;
					$conta->data_pagamento = date('Y-m-d H:i:s');
					$conta->save();

					if(isset($request->conta_id)){
						$tipoPagamento = \App\Models\Venda::getTipoPagamentoNFe($request->tipo_pagamento);

						$data = [
							'conta_id' => $request->conta_id,
							'descricao' => "Pagamento da conta " . $conta->referencia,
							'tipo_pagamento' => $tipoPagamento,
							'valor' => $conta->valor_integral,
							'tipo' => 'saida'
						];
						$itemContaEmpresa = ItemContaEmpresa::create($data);
						$this->util->atualizaSaldo($itemContaEmpresa);
					}
				}
			}

			session()->flash('mensagem_sucesso', 'Contas pagas!');
		}catch(\Exception $e){
			session()->flash('mensagem_erro', 'Algo deu errado: '. $e->getMessage());

		}
		return redirect('/contasPagar');
	}

}
