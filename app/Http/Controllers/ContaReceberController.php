<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContaReceber;
use App\Models\CategoriaConta;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\Cidade;
use Dompdf\Dompdf;
use PDF;
use App\Imports\ProdutoImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Utils\ContaEmpresaUtil;
use App\Models\ContaEmpresa;
use App\Models\ItemContaEmpresa;
use Illuminate\Support\Str;
use App\Helpers\NumberHelper;
use App\Models\ReciboReceber;
use App\Models\EstornoContaReceber;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class ContaReceberController extends Controller
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

	public function index(){
		__saveRedirect($this->empresa_id, '', 'contas_receber');
		$permissaoAcesso = __getLocaisUsarioLogado();
		$local_padrao = __get_local_padrao();
		if($local_padrao == -1){
			$local_padrao = null;
		}
		$contas = ContaReceber::
		where('empresa_id', $this->empresa_id)
		->whereBetween('data_vencimento', [date("Y-m-d"), 
			date('Y-m-d', strtotime('+1 month'))])
		->orderBy('data_vencimento', 'desc')
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

		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'receber')
		->get();

		$somaContas = $this->somaCategoriaDeContas($contas);

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->get();

		return view('contaReceber/list')
		->with('contas', $contas)
		->with('graficoJs', true)
		->with('categorias', $categorias)
		->with('clientes', $clientes)
		->with('somaContas', $somaContas)
		->with('infoDados', "Dos próximos 30 dias")
		->with('title', 'Contas a Receber');
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
		->where('tipo', 'receber')
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
		$clienteId = $request->clienteId;
		$status = $request->status;
		$filial_id = $request->filial_id;
		$numero_pedido = $request->numero_pedido;
		$contas = [];

		if($request->tipo_pagamento && $request->tipo_pagamento == 'Pix'){
			$request->tipo_pagamento = 'Pagamento Instantâneo (PIX)';
		}

		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		__saveRedirect($this->empresa_id, $url, 'contas_receber');
		$permissaoAcesso = __getLocaisUsarioLogado();

		$c = ContaReceber::
		select('conta_recebers.*')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('conta_recebers.filial_id', $value);
				}
			}
		})
		->when($filial_id, function ($query) use ($filial_id) {
			$filial_id = $filial_id == -1 ? null : $filial_id;
			return $query->where('conta_recebers.filial_id', $filial_id);
		});
		
		if($clienteId != 'null'){
			// $contas->join('clientes', 'clientes.id' , '=', 'conta_recebers.cliente_id');
			$c->where('conta_recebers.cliente_id', $clienteId);
		}

		if($dataInicial && $dataFinal){

			if($request->tipo_filtro_data == 1){
				$c->whereBetween('conta_recebers.data_vencimento', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal)
					]
				);
			}elseif($request->tipo_filtro_data == 2){
				$c->whereBetween('conta_recebers.date_register', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal, true)
					]
				);
			}else{

				$d1 = str_replace("/", "-", $dataInicial);
				$d2 = str_replace("/", "-", $dataFinal);

				$c->whereBetween('conta_recebers.data_recebimento', 
					[
						\Carbon\Carbon::parse($d1)->format('Y-m-d') . " 00:00:00",
						\Carbon\Carbon::parse($d2)->format('Y-m-d') . " 23:59:59"
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

		if($request->numero_nota_fiscal){
			$c->where('conta_recebers.numero_nota_fiscal', $request->numero_nota_fiscal);
		}

		if($request->categoria != 'todos'){
			$c->where('conta_recebers.categoria_id', $request->categoria);
		}

		if($request->tipo_pagamento){
			$c->where('conta_recebers.tipo_pagamento', $request->tipo_pagamento);
		}

		if($numero_pedido){
			$c->join('vendas', 'vendas.id', '=', 'conta_recebers.venda_id')
			->where('vendas.id', $numero_pedido);
		}
		$c->where('conta_recebers.empresa_id', $this->empresa_id);
		
		$temp = $c->get();
		foreach($temp as $t){
			array_push($contas, $t);
		}

		$c = ContaReceber::
		select('conta_recebers.*')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('conta_recebers.filial_id', $value);
				}
			}
		})
		->when($filial_id, function ($query) use ($filial_id) {
			$filial_id = $filial_id == -1 ? null : $filial_id;
			return $query->where('conta_recebers.filial_id', $filial_id);
		});
		
		if($clienteId != 'null'){

			$c->join('vendas', 'vendas.id' , '=', 'conta_recebers.venda_id')
			->where('vendas.cliente_id', $clienteId);
		}

		if($dataInicial && $dataFinal){
			if($request->tipo_filtro_data == 1){
				$c->whereBetween('conta_recebers.data_vencimento', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal)
					]
				);
			}elseif($request->tipo_filtro_data == 2){
				$c->whereBetween('conta_recebers.date_register', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal, true)
					]
				);
			}else{
				$d1 = str_replace("/", "-", $dataInicial);
				$d2 = str_replace("/", "-", $dataFinal);

				$c->whereBetween('conta_recebers.data_recebimento', 
					[
						\Carbon\Carbon::parse($d1)->format('Y-m-d') . " 00:00:00",
						\Carbon\Carbon::parse($d2)->format('Y-m-d') . " 23:59:59"
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

		if($request->numero_nota_fiscal){
			$c->where('conta_recebers.numero_nota_fiscal', $request->numero_nota_fiscal);
		}
		if($request->categoria != 'todos'){
			$c->where('conta_recebers.categoria_id', $request->categoria);
		}

		if($request->tipo_pagamento){
			$c->where('conta_recebers.tipo_pagamento', $request->tipo_pagamento);
		}

		if($numero_pedido){
			$c->join('vendas', 'vendas.id', '=', 'conta_recebers.venda_id')
			->where('vendas.id', $numero_pedido);
		}
		$c->where('conta_recebers.empresa_id', $this->empresa_id);
		
		$temp = $c->get();
		foreach($temp as $t){
			if(!$this->validaInArray($t, $contas)){
				array_push($contas, $t);
			}
		}

		$somaContas = $this->somaCategoriaDeContas($contas);

		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'receber')
		->get();

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->get();

		return view('contaReceber/list')
		->with('contas', $contas)
		->with('clienteId', $clienteId)
		->with('clientes', $clientes)
		->with('categorias', $categorias)
		->with('tipoPesquisa', $request->tipo_pesquisa)
		->with('tipo_filtro_data', $request->tipo_filtro_data)
		->with('categoria', $request->categoria)
		->with('tipo_pagamento', $request->tipo_pagamento)
		->with('dataInicial', $dataInicial)
		->with('dataFinal', $dataFinal)
		->with('status', $status)
		->with('filial_id', $filial_id)
		->with('numero_pedido', $numero_pedido)
		->with('somaContas', $somaContas)
		->with('graficoJs', true)
		->with('numero_nota_fiscal', $request->numero_nota_fiscal)
		->with('paraImprimir', true)
		->with('infoDados', "Contas filtradas")
		->with('title', 'Filtro Contas a Receber');
	}

	private function validaInArray($ct, $contas){
		foreach($contas as $c){
			if($c->id == $ct->id) return true;
		}
		return false;
	}

	public function salvarParcela(Request $request){
		$parcela = $request->parcela;

		$valorParcela = str_replace(".", "", $parcela['valor_parcela']);
		$valorParcela = str_replace(",", ".", $valorParcela);

		$categoria = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'receber')
		->first();

		$result = ContaReceber::create([
			'venda_id' => $parcela['compra_id'],
			'data_vencimento' => $this->parseDate($parcela['vencimento']),
			'data_recebimento' => $this->parseDate($parcela['vencimento']),
			'valor_integral' => $valorParcela,
			'valor_recebido' => 0,
			'status' => false,
			'referencia' => $parcela['referencia'],
			'categoria_id' => $categoria->id,
			'empresa_id' => $this->empresa_id
		]);
		echo json_encode($parcela);
	}

	public function save(Request $request){
		
		if(strlen($request->recorrencia) == 5){
			echo $request->recorrencia;
			$valid = $this->validaRecorrencia($request->recorrencia);
			if(!$valid){
				session()->flash('mensagem_erro', 'Valor recorrente inválido!');
				return redirect('/contasReceber/new');
			}
		}
		$clienteId = NULL;
		if($request->cliente_id != ""){
			$clienteId = $request->cliente_id;
		}

		$request->merge([
			'filial_id' => $request->filial_id == -1 ? null : $request->filial_id
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

		$result = ContaReceber::create([
			'venda_id' => null,
			'data_register'    => $this->parseDate($request->date_register) . ' ' . date('H:i:s'),
			'data_vencimento' => $this->parseDate($request->vencimento),
			'data_recebimento'   => $this->parseDate($request->data_recebimento) . ' ' . date('H:i:s'),
			'valor_integral' => str_replace(",", ".", $request->valor),
			'valor_recebido' => $request->status ? str_replace(",", ".", $request->valor_recebido) : 0,
			'status' => $request->status ? true : false,
			'referencia' => $request->referencia . (sizeof($parcelas) > 0 ? " - parcela 1" . "/".(sizeof($parcelas)+1) : ""),
			'tipo_pagamento' => $request->tipo_pagamento ?? '',
			'observacao' => $request->observacao ?? '',
			'categoria_id' => $request->categoria_id,
			'empresa_id' => $this->empresa_id,
			'cliente_id' => $clienteId,
			'filial_id' => $request->filial_id,
			'numero_nota_fiscal' => $request->numero_nota_fiscal ?? 0,
			'arquivo' => $fileName
		]);
		
		// $loopRecorrencia = $this->calculaRecorrencia($request->recorrencia);
		// if($loopRecorrencia > 0){
		// 	$diaVencimento = substr($request->vencimento, 0, 2);
		// 	$proximoMes = substr($request->vencimento, 3, 2);
		// 	$ano = substr($request->vencimento, 6, 4);

		// 	while($loopRecorrencia > 0){
		// 		$proximoMes = $proximoMes == 12 ? 1 : $proximoMes+1;
		// 		$proximoMes = $proximoMes < 10 ? "0".$proximoMes : $proximoMes;
		// 		if($proximoMes == 1)  $ano++;
		// 		$d = $diaVencimento . "/".$proximoMes . "/" . $ano;

		// 		$result = ContaReceber::create([
		// 			'venda_id' => null,
		// 			'data_vencimento' => $this->parseDate($d),
		// 			'data_recebimento' => $this->parseDate($d),
		// 			'valor_integral' => str_replace(",", ".", $request->valor),
		// 			'valor_recebido' => 0,
		// 			'status' => false,
		// 			'referencia' => $request->referencia,
		// 			'categoria_id' => $request->categoria_id,
		// 			'empresa_id' => $this->empresa_id,
		// 			'cliente_id' => $clienteId
		// 		]);
		// 		$loopRecorrencia--;
		// 	}
		// }

		if(sizeof($parcelas) > 0){
			foreach($parcelas as $key => $p){
				$result = ContaReceber::create([
					'venda_id' => null,
					'data_vencimento' => $p->vencimento,
					'data_recebimento' => $p->vencimento,
					'valor_integral' => str_replace(",", ".", $p->valor),
					'valor_recebido' => $request->status ? str_replace(",", ".", $request->valor_recebido) : 0,
					'status' => $request->status ? true : false,
					'tipo_pagamento' => $request->tipo_pagamento ?? '',
					'cliente_id' => $clienteId,
					'observacao' => $request->observacao ?? '',
					'numero_nota_fiscal' => $request->numero_nota_fiscal ?? 0,
					'referencia' => $request->referencia . " - parcela " .($key+2) . "/".(sizeof($parcelas)+1),
					'categoria_id' => $request->categoria_id,
					'empresa_id' => $this->empresa_id
				]);
			}
		}


		session()->flash('mensagem_sucesso', 'Registro inserido!');

		return redirect('/contasReceber');
	}

	public function update(Request $request){
		$this->_validate($request);
		$conta = ContaReceber::
		where('id', $request->id)
		->first();

		$request->merge([
			'filial_id' => $request->filial_id == -1 ? null : $request->filial_id
		]);

		$conta->date_register    = $this->parseDate($request->date_register);
		$conta->data_vencimento = $this->parseDate($request->vencimento);
		$conta->referencia = $request->referencia;
		$conta->tipo_pagamento = $request->tipo_pagamento ?? '';
		$conta->observacao = $request->observacao ?? '';
		$conta->valor_integral = str_replace(",", ".", $request->valor);
		$conta->categoria_id = $request->categoria_id;
		$conta->filial_id = $request->filial_id;
		$conta->numero_nota_fiscal = $request->numero_nota_fiscal ?? 0;
		if(isset($request->cliente_id)){
			$conta->cliente_id = $request->cliente_id;
		}

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
			session()->flash('mensagem_sucesso', 'Registro atualizado!');
		}else{

			session()->flash('mensagem_erro', 'Ocorreu um erro!');
		}

		$rota = __getRedirect($this->empresa_id, 'contas_receber');
		if($rota != ""){
			return redirect($rota);
		}

		return redirect('/contasReceber');

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
			'cliente_id' => $request->id == 0 ? 'required' : '',
			'referencia' => 'required',
			'valor' => 'required',
			'observacao' => 'max:100',
			'categoria_id' => 'required',
			'vencimento' => 'required',
		];

		$messages = [
			'cliente_id.required' => 'O campo cliente é obrigatório.',
			'referencia.required' => 'O campo referencia é obrigatório.',
			'valor.required' => 'O campo valor é obrigatório.',
			'observacao.max' => 'Máximo de 100 caracteres.',
			'categoria_id.required' => 'O campo categoria é obrigatório.',
			'vencimento.required' => 'O campo vencimento é obrigatório.'
		];
		$this->validate($request, $rules, $messages);
	}

	public function new(){
		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'receber')
		->orderBy('nome')
		->get();

		if(sizeof($categorias) == 0){
			session()->flash('mensagem_alerta', 'Cadastre uma categoria com o tipo receber!');
			return redirect('/categoriasConta');
		}

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->get();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		if($config == null){
			session()->flash('mensagem_alerta', 'Informe a configuração do emitente!');
			return redirect('/configNF');
		}
		
		return view('contaReceber/register')
		->with('categorias', $categorias)
		->with('clientes', $clientes)
		->with('config', $config)
		->with('title', 'Cadastrar Contas a Receber');
	}

	public function detalhes($id){
		$conta = ContaReceber::findOrFail($id);
		return view('contaReceber/detalhes')
		->with('conta', $conta)
		->with('title', 'Detalhes da Conta');
	}

	public function edit($id){
		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'receber')
		->orderBy('nome')
		->get();

		$conta = ContaReceber::
		where('id', $id)
		->first();

		if($conta->venda_caixa_id != null){
			$conta->cliente_id = $conta->vendaCaixa->cliente_id;
			$conta->save();
		}
		if($conta->venda_id != null){
			$conta->cliente_id = $conta->venda->cliente_id;
			$conta->save();
		}

		$conta = ContaReceber::
		where('id', $id)
		->first();

		if($conta->boleto){
			session()->flash('mensagem_erro', 'Conta já possui boleto emitido!');
			return redirect('/contasReceber');
		}

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->get();

		if(valida_objeto($conta)){
			return view('contaReceber/register')
			->with('conta', $conta)
			->with('categorias', $categorias)
			->with('clientes', $clientes)
			->with('title', 'Editar Contas a Receber');
		}else{
			return redirect('/403');
		}
	}

	public function estorno($id)
	{
		$conta = ContaReceber::findOrFail($id);
	
		// usuário logado
		$sess        = session('user_logged');
		$currentUser = Usuario::find($sess['id']);
	
		// quantos já fez desta conta?
		$feito = EstornoContaReceber::where([
			'empresa_id'        => $this->empresa_id,
			'conta_receber_id'  => $conta->id,
			'usuario_id'        => $currentUser->id,
		])->value('quantidade') ?? 0;
	
		// limite configurado no usuário
		$limite = $currentUser->max_estornos_conta_receber;
	
		// precisa de admin se NÃO for admin E (não tiver a flag OU já atingiu o limite)
		$requiresAdminAuth = ! $currentUser->adm
						   && (
								! $currentUser->estorna_conta_receber
							 || $feito >= $limite
							  );
	
		return view('contaReceber.estorno', compact('conta','requiresAdminAuth'))
			   ->with('title','Estornar Conta');
	}

	public function arquivo($id){
		$item = ContaReceber::findOrFail($id);
		if(file_exists(public_path('arquivos_conta/').$item->arquivo)){
			return redirect('/arquivos_conta/'.$item->arquivo);
		}
	}

	public function estornoConta(Request $request)
	{
		$conta       = ContaReceber::findOrFail($request->id);
		$sess        = session('user_logged');
		$currentUser = Usuario::find($sess['id']);
	
		// quantos já fez desta conta
		$feito = EstornoContaReceber::where([
			'empresa_id'        => $this->empresa_id,
			'conta_receber_id'  => $conta->id,
			'usuario_id'        => $currentUser->id,
		])->value('quantidade') ?? 0;
	
		$limite = $currentUser->max_estornos_conta_receber;
	
		// precisa de senha de admin?
		$needsAdminPassword = ! $currentUser->adm
							&& (
								 ! $currentUser->estorna_conta_receber
							  || $feito >= $limite
							   );
	
		// valida senha de admin se necessário
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
	
		// se executor não for admin, reforça permissão e limite
		if (! $executor->adm) {
			if (! $executor->estorna_conta_receber) {
				session()->flash('mensagem_erro', 'Você não tem permissão para estornar contas a receber.');
				return redirect()->back();
			}
			// se for o próprio usuário e já bateu o limite
			if ($executor->id === $currentUser->id && $feito >= $limite) {
				session()->flash('mensagem_erro', "Você já atingiu o máximo de {$limite} estornos para esta conta.");
				return redirect()->back();
			}
		}
	
		// faz o estorno e registra na tabela de controle
		try {
			DB::transaction(function() use ($conta, $request, $executor) {
				$conta->update([
					'status'          => false,
					'estorno'         => true,
					'motivo_estorno'  => $request->motivo,
				]);
	
				$registro = EstornoContaReceber::firstOrNew([
					'empresa_id'        => $conta->empresa_id,
					'conta_receber_id'  => $conta->id,
					'usuario_id'        => $executor->id,
				]);
				$registro->quantidade = ($registro->quantidade ?? 0) + 1;
				$registro->save();
			});
	
			session()->flash('mensagem_sucesso', 'Conta estornada!');
		} catch (\Exception $e) {
			session()->flash('mensagem_erro', 'Algo deu errado: ' . $e->getMessage());
		}
	
		$rota = __getRedirect($this->empresa_id, 'contas_receber');
		return $rota ? redirect($rota) : redirect('/contasReceber');
	}

	public function receber($id){
		$categorias = CategoriaConta::
		where('empresa_id', $this->empresa_id)
		->where('tipo', 'receber')
		->get();
		$conta = ContaReceber::findOrFail($id);

		if(valida_objeto($conta)){

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			$contasEmpresa = ContaEmpresa::where('empresa_id', $this->empresa_id)
			->where('status', 1)->get();

			$dataHoje = strtotime(date('Y-m-d'));
			$dataVencimento = strtotime($conta->data_vencimento);

			$dif = ($dataHoje - $dataVencimento)/86400;

			$multa = 0;
			$juros = 0;
			if($dif > 0){
				if($config->multa_padrao > 0){
					$multa = $conta->valor_integral*($config->multa_padrao/100);
				}
				if($config->juro_padrao > 0){
					$juros = ($conta->valor_integral*($config->juro_padrao/100)) * $dif;
				}
			}

			return view('contaReceber/receber')
			->with('conta', $conta)
			->with('multa', $multa)
			->with('juros', $juros)
			->with('contasEmpresa', $contasEmpresa)
			->with('categorias', $categorias)
			->with('title', 'Receber Conta');
		}else{
			return redirect('/403');
		}
	}

	public function receberConta(Request $request)
	{
		// Recupera a conta a receber
		$conta = ContaReceber::where('id', $request->id)->first();
		if (!$conta) {
			return back()->withErrors('Conta a receber não encontrada!');
		}
	
		// Atualiza os valores de multa, juros e observação
		$conta->multa = $request->multa ? __replace($request->multa) : 0;
		$conta->juros = $request->juros ? __replace($request->juros) : 0;
		$conta->observacao_baixa = $request->observacao_baixa ?? "";
		
		// Salva as alterações iniciais (se houver)
		$conta->save();
		
		if (valida_objeto($conta)) {
			// Calcula valores para verificação de divergência (se necessário)
			$vIntegral = number_format($conta->valor_integral + $conta->juros + $conta->multa, 2);
			$vReq = number_format(str_replace(",", ".", $request->valor), 2);
	
			if ((float)$vIntegral > (float)$vReq) {
				// Caso haja divergência de valores, segue a lógica existente...
				$valor = __replace($request->valor);
	
				if (isset($request->conta_id)) {
					$tipoPagamento = \App\Models\Venda::getTipoPagamentoNFe($request->tipo_pagamento);
					$data = [
						'conta_id' => $request->conta_id,
						'descricao' => "Recebimento de conta " . $conta->referencia,
						'tipo_pagamento' => $tipoPagamento,
						'valor' => $valor,
						'tipo' => 'entrada'
					];
					$itemContaEmpresa = ItemContaEmpresa::create($data);
					$this->util->atualizaSaldo($itemContaEmpresa);
				}
				$valor += $conta->juros + $conta->multa;
	
				if ($conta->venda_id != null) {
					$contasParaReceber = ContaReceber::select('conta_recebers.*')
						->join('vendas', 'vendas.id', '=', 'conta_recebers.venda_id')
						->where('conta_recebers.status', false)
						->where('conta_recebers.id', '!=', $conta->id)
						->where('vendas.cliente_id', $conta->venda->cliente_id)
						->get();
					if ($conta->valor_integral > $request->valor) {
						$contasParaReceber = [];
					}
					return view('contaReceber/valorDivergente')
						->with('conta', $conta)
						->with('valor', $valor)
						->with('tipo_pagamento', $request->tipo_pagamento)
						->with('receberConta', true)
						->with('contasParaReceber', $contasParaReceber)
						->with('title', 'Receber Conta');
				} else {
					$contasParaReceber = [];
					return view('contaReceber/valorDivergente')
						->with('conta', $conta)
						->with('valor', $valor)
						->with('tipo_pagamento', $request->tipo_pagamento)
						->with('receberConta', true)
						->with('contasParaReceber', $contasParaReceber)
						->with('title', 'Receber Conta');
				}
			} else {
				// Se não houver divergência, atualiza os dados de recebimento
				$dtReceb = \Carbon\Carbon::parse(str_replace("/", "-", $request->data_pagamento))->format('Y-m-d') . " " . date("H:i:s");
				$conta->status = true;
				$conta->valor_recebido = __replace($request->valor);
				$conta->data_recebimento = $dtReceb;
				$conta->tipo_pagamento = $request->tipo_pagamento;
	
				$result = $conta->save();
	
				if (isset($request->conta_id)) {
					$tipoPagamento = \App\Models\Venda::getTipoPagamentoNFe($request->tipo_pagamento);
					$data = [
						'conta_id' => $request->conta_id,
						'descricao' => "Recebimento de conta " . $conta->referencia,
						'tipo_pagamento' => $tipoPagamento,
						'valor' => $conta->valor_recebido,
						'tipo' => 'entrada'
					];
					$itemContaEmpresa = ItemContaEmpresa::create($data);
					$this->util->atualizaSaldo($itemContaEmpresa);
				}
	
				if ($result) {
					session()->flash('mensagem_sucesso', 'Conta recebida!');
				} else {
					session()->flash('mensagem_erro', 'Erro ao receber!');
				}
	
				// Verifica o parâmetro do modal: se o usuário optou por gerar recibo, redireciona para a tela do recibo
				if ($request->input('gerar_recibo') == '1') {
					// Redireciona para a rota de recibo passando o ID da conta
					return redirect()->route('contasReceber.recibo', $conta->id);
				}
	
				$rota = __getRedirect($this->empresa_id, 'contas_receber');
				if ($rota != "") {
					return redirect($rota);
				}
				return redirect('/contasReceber');
			}
		} else {
			return redirect('/403');
		}
	}	

	public function delete($id){
		$conta = ContaReceber
		::where('id', $id)
		->first();
		if($conta->venda_id != null){
			session()->flash('mensagem_erro', 'Esta conta esta vinculada a uma venda!');
			return redirect('/contasReceber');
		}
		
		if($conta->boleto){
			session()->flash('mensagem_erro', 'Conta já possui boleto emitido!');
			return redirect('/contasReceber');
		}
		
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
		$cliente = $request->cliente;
		$status = $request->status;
		$filial_id = $request->filial_id;
		$tipo_pagamento = $request->tipo_pagamento;

		$contas = null;

		$permissaoAcesso = __getLocaisUsarioLogado();

		$contas = ContaReceber::
		select('conta_recebers.*')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('conta_recebers.filial_id', $value);
				}
			}
		})
		->when($filial_id, function ($query) use ($filial_id) {
			$filial_id = $filial_id == -1 ? null : $filial_id;
			return $query->where('conta_recebers.filial_id', $filial_id);
		});

		if($cliente != 'null'){
			$contas->join('clientes', 'clientes.id' , '=', 'conta_recebers.cliente_id');
			$contas->where('conta_recebers.cliente_id', $cliente);
		}

		if($tipo_pagamento){
			$contas->where('conta_recebers.tipo_pagamento', $tipo_pagamento);
		}
		
		if($dataInicial && $dataFinal){

			if($request->tipo_filtro_data == 1){
				$contas->whereBetween('conta_recebers.data_vencimento', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal)
					]
				);
			}elseif($request->tipo_filtro_data == 2){
				$contas->whereBetween('conta_recebers.date_register', 
					[
						$this->parseDate($dataInicial),
						$this->parseDate($dataFinal, true)
					]
				);
			}else{
				
				$d1 = str_replace("/", "-", $dataInicial);
				$d2 = str_replace("/", "-", $dataFinal);

				$contas->whereBetween('conta_recebers.data_recebimento', 
					[
						\Carbon\Carbon::parse($d1)->format('Y-m-d') . "",
						\Carbon\Carbon::parse($d2)->format('Y-m-d') . ""
					]
				);
			}
		}
		
		if($status != 'todos'){
			if($status == 'pago'){
				$contas->where('status', true);
			} else if($status == 'pendente'){
				$contas->where('status', false);
			}else if($status == 'vencido'){
				$contas->where('status', false)
				->whereDate('data_vencimento', '<=', date('Y-m-d'));
			}
		}

		if($request->tipo_filtro_data == 3){
			$contas->where('status', true);
		}
		$contas->where('conta_recebers.empresa_id', $this->empresa_id);
		// $contas->groupBy('conta_recebers.data_vencimento');
		$contas->orderBy('conta_recebers.data_vencimento', 'asc');

		if($request->categoria != 'todos'){
			$contas->where('categoria_id', $request->categoria);
		}

		if($request->numero_nota_fiscal){
			$contas->where('conta_recebers.numero_nota_fiscal', $request->numero_nota_fiscal);
		}

		$contas = $contas->get();

		// echo $contas;


		$p = view('relatorios/relatorio_contas_receber')
		->with('data_inicial', $request->data_inicial)
		->with('data_final', $request->data_final)
		->with('contas', $contas);

		// return $p;

		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);

		$pdf = ob_get_clean();

		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatorio de Contas a Receber.pdf", array("Attachment" => false));

	}

	public function receberSomente(Request $request){
		$conta = ContaReceber::find($request->id);
		$valor = __replace($request->valor);

		$conta->status = true;
		$conta->valor_recebido = $request->valor;
		$conta->data_recebimento = date("Y-m-d") . " " . date('H:i:s');
		$conta->tipo_pagamento = $request->tipo_pagamento;

		$result = $conta->save();
		if($result){

			session()->flash('mensagem_sucesso', 'Conta recebida!');
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/contasReceber');
	}

	public function receberComDivergencia(Request $request){
		$conta = ContaReceber::find($request->id);
		$valor = __replace($request->valor);
		$nova_data = $request->nova_data;
		if($request->somente_finalizar == 0){
			$res = ContaReceber::create([
				'venda_id' => $conta->venda_id,
				'venda_caixa_id' => $conta->venda_caixa_id,
				'cliente_id' => $conta->cliente_id,
				'data_vencimento' => $nova_data,
				'data_recebimento' => $conta->data_recebimento,
				'valor_integral' => $conta->valor_integral - $valor,
				'valor_recebido' => 0,
				'status' => false,
				'referencia' => $conta->referencia,
				'categoria_id' => $conta->categoria_id,
				'empresa_id' => $this->empresa_id,
			]);
		}

		$conta->status = true;
		$conta->valor_recebido = $request->valor;
		// $conta->valor_integral = $request->valor;
		$conta->tipo_pagamento = $request->tipo_pagamento;
		$conta->data_recebimento = date("Y-m-d") . " " . date('H:i:s');

		$result = $conta->save();
		if($result){
			if($request->somente_finalizar == 0){
				$id = $res->id;
				session()->flash('mensagem_sucesso', 'Conta recebida parcialmente, uma nova foi criada com ID: ' . $id);
			}else{
				session()->flash('mensagem_sucesso', 'Conta recebida!');
			}
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/contasReceber');
	}

	public function receberComOutros(Request $request){
		$conta = ContaReceber::find($request->id);
		$valor = $request->valor;
		$temp = "";
		$somaParaTroco = $conta->valor_integral;
		try{
			if(isset($request->contas)){
				$contasMais = explode(",", $request->contas);
				// print_r($contasMais);
				foreach($contasMais as $key => $c){
					$ctemp = ContaReceber::find($c);
					$ctemp->status = true;
					$ctemp->valor_recebido = $ctemp->valor_integral;
					$ctemp->data_recebimento = date("Y-m-d") . " " . date('H:i:s');
					$ctemp->save();

					$temp .= " $c" . (sizeof($contasMais)-1 > $key ? "," : "");

					$somaParaTroco += $ctemp->valor_integral;
				}
			}

			$conta->status = true;
			$conta->valor_recebido = $conta->valor_integral;
			$conta->data_recebimento = date("Y-m-d") . " " . date('H:i:s');
			$conta->save();

			$troco = $valor - $somaParaTroco;

			$msg = "Sucesso conta(s) com ID: $conta->id, " . $temp . " recebida(s)";

			if($troco > 0){
				$msg .= " , valor de troco: R$ " . number_format($troco, 2);
			}
			session()->flash('mensagem_sucesso', $msg);

			return redirect('/contasReceber');

		}catch(\Exception $e){
			session()->flash('mensagem_erro', 'Ocorreu um erro ao receber: ' . $e->getMessage());

		}
	}

	public function detalhesVenda($contaId){
		$conta = ContaReceber::find($contaId);

		if(valida_objeto($conta)){

			if($conta->venda_id != null){
				// venda nfe
				return redirect('/vendas/detalhar/'.$conta->venda_id);
			}else{
				// venda pdv
				return redirect('/nfce/detalhes/'.$conta->venda_caixa_id);
			}
		}else{
			return redirect('/403');
		}
	}

	public function pendentes(){
		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->get();

		$title = 'Contas pendentes';
		return view('contaReceber/pendentes', compact('clientes', 'title'));
	}

	public function filtroPendente(Request $request){

		if(!$request->clienteId){
			session()->flash('mensagem_erro', 'Informe o cliente');
			return redirect()->back();
		}

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->get();

		$title = 'Contas pendentes';
		$dataInicial = $request->data_inicial;
		$DataFinal = $request->data_final;
		$clienteId = $request->clienteId;
		$tipo_pagamento = $request->tipo_pagamento;

		$contas = ContaReceber::
		where('empresa_id', $this->empresa_id)
		->where('cliente_id', $clienteId)
		->orderBy('data_vencimento', 'desc')
		->where('status', 0);

		if($tipo_pagamento){
			$contas->where('tipo_pagamento', $request->tipo_pagamento);
		}
		$contas = $contas->get();

		return view('contaReceber/pendentes', 
			compact('clientes', 'title', 'contas', 'dataInicial', 'DataFinal', 'tipo_pagamento', 'clienteId')
		);
	}

	public function receberMultiplos($ids){
		$temp = explode(",", $ids);
		$contas = [];

		$somaTotal = 0;

		foreach($temp as $i){
			$conta = ContaReceber::find($i);
			if($conta->empresa_id != $this->empresa_id){
				session()->flash('mensagem_erro', "Erro inesperado!");
				return redirect()->back();
			}
			$somaTotal += $conta->valor_integral;

			array_push($contas, $conta);
		}

		if(sizeof($contas) <= 1){
			session()->flash('mensagem_erro', "É necessário selecionar mais de uma conta!");
			return redirect()->back();
		}
		$title = 'Receber contas';

		$contasEmpresa = ContaEmpresa::where('empresa_id', $this->empresa_id)
		->where('status', 1)->get();

		return view('contaReceber/receber_multi', 
			compact('somaTotal', 'title', 'contas', 'ids', 'contasEmpresa')
		);
	}

	public function receberMulti(Request $request)
	{
		// Converte a data de recebimento (substitui barras por hífens)
		$dtReceb = \Carbon\Carbon::parse(str_replace("/", "-", $request->data_pagamento))
					->format('Y-m-d') . " " . date("H:i:s");
	
		// Separa os IDs das contas a receber (passados como string separada por vírgulas)
		$ids = explode(",", $request->ids);
		// Valor pago informado no formulário (convertido para número)
		$valorRecebido = __replace($request->valor);
		// Soma total (geralmente já calculada no formulário)
		$somaTotal = $request->somaTotal;
		$tipo_pagamento = $request->tipo_pagamento;
	
		$somaPagamento = 0;
		$diferenca = 0;
		$contasRecebidas = [];
	
		// Processa cada conta selecionada
		foreach ($ids as $idConta) {
			$conta = \App\Models\ContaReceber::find($idConta);
			if (!$conta) {
				continue;
			}
			// Verifica se a conta pertence à mesma empresa
			if ($conta->empresa_id != $this->empresa_id) {
				session()->flash('mensagem_erro', "Conta ID: $idConta não pertence a esta empresa!");
				return redirect()->back();
			}
			$somaPagamento += $conta->valor_integral;
			$conta->status = 1;
			$conta->valor_recebido = $conta->valor_integral;
			$conta->tipo_pagamento = $tipo_pagamento;
			$conta->data_recebimento = $dtReceb;
	
			// Se houver vínculo com conta de caixa, registra o item (mantém sua lógica)
			if (isset($request->conta_id)) {
				$tipoPagamentoNFe = \App\Models\Venda::getTipoPagamentoNFe($request->tipo_pagamento);
				$dataItem = [
					'conta_id'       => $request->conta_id,
					'descricao'      => "Recebimento de conta " . $conta->referencia,
					'tipo_pagamento' => $tipoPagamentoNFe,
					'valor'          => $conta->valor_integral,
					'tipo'           => 'entrada'
				];
				$itemContaEmpresa = \App\Models\ItemContaEmpresa::create($dataItem);
				$this->util->atualizaSaldo($itemContaEmpresa);
			}
	
			// Se o valor acumulado for menor ou igual ao valor recebido, salva normalmente;
			// se ultrapassar, ajusta a última conta e cria uma nova com a diferença.
			if ($somaPagamento <= $valorRecebido) {
				$conta->save();
			} else {
				if ($diferenca == 0) {
					$diferenca = $somaPagamento - $valorRecebido;
					$novoValor = $conta->valor_integral - $diferenca;
					$conta->valor_integral = $novoValor;
					$conta->save();
	
					// Cria uma nova conta com a diferença
					$novaConta = $conta->replicate(['created_at', 'updated_at', 'id']);
					$novaConta->status = 0;
					$novaConta->valor_integral = $diferenca;
					$novaConta->save();
				}
			}
			$contasRecebidas[] = $conta;
		}
	
		// Se o usuário optou por gerar recibo agrupado...
		if ($request->has('gerar_recibo') && $request->input('gerar_recibo') == '1') {
			// Usa a primeira conta para obter os dados do cliente, etc.
			$primeiraConta = $contasRecebidas[0];
	
			// Prepara os dados agregados para o recibo agrupado
			$dadosRecibo = [
				'empresa_id'     => $primeiraConta->empresa_id,
				'filial_id'      => $primeiraConta->filial_id,
				'data_pagamento' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_pagamento)
										->format('Y-m-d') . " " . date("H:i:s"),
				'cliente'        => $primeiraConta->cliente->razao_social,
				'documento'      => $primeiraConta->cliente->cpf_cnpj,
				'endereco'       => $primeiraConta->cliente->endereco,
				'telefone'       => $primeiraConta->cliente->telefone,
				'valor_pago'     => $valorRecebido, // Aqui você pode optar por usar o valor total pago
				'valor_extenso'  => \valorPorExtenso($valorRecebido),
				'forma_pagamento'=> $tipo_pagamento,
				'observacao'     => $request->observacao_baixa ?? '',
				'referencia'     => 'Recibo Agrupado das contas: ' . $request->ids,
			];
	
			// Cria o registro do recibo agrupado na tabela recibo_conta_rec
			$reciboAgrupado = \App\Models\ReciboReceber::create($dadosRecibo);
	
			// Agora, associa cada conta (do array $contasRecebidas) ao recibo agrupado na tabela pivot
			foreach ($contasRecebidas as $conta) {
				$reciboAgrupado->contasReceber()->attach($conta->id);
			}
	
			// Armazena o ID do recibo agrupado na sessão para uso na visualização
			session()->flash('recibo_id', $reciboAgrupado->id);
	
			// Redireciona para a tela de recibo agrupado (rota que espera o ID do recibo agrupado)
			return redirect()->route('contasReceber.reciboMulti', $reciboAgrupado->id);
		}
	
		// Caso não haja geração de recibo agrupado, finaliza o recebimento
		if ($somaPagamento == $valorRecebido) {
			session()->flash('mensagem_sucesso', "Contas recebidas!");
		} else {
			session()->flash('mensagem_sucesso',
				"As contas foram recebidas, porém com saldo insuficiente, " .
				"uma nova conta com a diferença de R$ " . number_format($diferenca, 2, ',', '.') . " foi criada!"
			);
		}
	
		return redirect('/contasReceber');
	}	

	public function importacao(){
		$zip_loaded = extension_loaded('zip') ? true : false;
		if ($zip_loaded === false) {
			session()->flash('mensagem_erro', "Por favor instale/habilite o PHP zip para importar");
			return redirect()->back();
		}


		return view('contaReceber/importacao')
		->with('title', 'Importação de conta receber');
	}

	public function downloadModelo(){
		try{
			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			return response()->download(public_path('files/') . 'import_conta_receber_csv_template.xlsx');
		}catch(\Exception $e){
			echo $e->getMessage();
		}
	}

	public function importacaoStore(Request $request){
		if ($request->hasFile('file')) {
			ini_set('max_execution_time', 0);
			ini_set('memory_limit', -1);

			$filial_id = $request->filial_id;

			$rows = Excel::toArray(new ProdutoImport, $request->file);
			$retornoErro = $this->validaArquivo($rows);

			if($retornoErro == ""){
				$cont = 0;
				foreach($rows as $row){
					foreach($row as $key => $r){
						if($key > 0){

							try{
								$objeto = $this->preparaObjeto($r, $filial_id);
								if($objeto != null){
									ContaReceber::create($objeto);
									$cont++;
								}
							}catch(\Exception $e){
								echo $e->getMessage() . ", linha: " . $e->getLine();
								die;
								session()->flash('mensagem_erro', $e->getMessage());
								return redirect()->back();
							}

							session()->flash('mensagem_sucesso', "Contas inseridas: $cont");
							return redirect('/contasReceber');
						}
					}
				}
			}else{
				session()->flash('mensagem_erro', $retornoErro);
				return redirect()->back();
			}

		}
	}

	private function preparaObjeto($r, $filial_id){
		if(trim($r[1]) == ""){
			return null;
		}

		$documento = $r[1];

		$documento = trim(preg_replace('/[^0-9]/', '', $documento));
		$cliente = Cliente::where('cpf_cnpj', $documento)->first();
		if($cliente == null){
			$mask = "###.###.###-##";
			if(strlen($documento) == 14){
				$mask = "##.###.###/####-##";
			}

			$documento = $this->__mask($documento, $mask);
			$cliente = Cliente::where('cpf_cnpj', $documento)->first();

		}

		if($cliente == null){
			$cliente = $this->cadastrarCliente($r);
		}

		$valor = $r[11];
		$vencimento = $r[12];
		$referencia = $r[13];
		$status = $r[14] != '' ? $r[14] : 0;

		$v = str_replace("/", "-", $vencimento);

		$v = \Carbon\Carbon::parse($v)->format('Y-m-d') . " " . date('H:i:s');

		$data = [
			'venda_id' => null,
			'data_vencimento' => $v,
			'data_recebimento' => $v,
			'valor_integral' => __replace($valor),

			'valor_recebido' => $status ? __replace($valor) : 0,
			'referencia' => $referencia != '' ? $referencia : '',
			'categoria_id' => CategoriaConta::where('empresa_id', $this->empresa_id)->where('tipo', 'receber')->first()->id,
			'status' => $status,
			'empresa_id' => $this->empresa_id,

			'cliente_id' => $cliente->id,
			'juros' => 0,
			'multa' => 0,
			'venda_caixa_id' => null,
			'observacao' => '',
			'tipo_pagamento' => '',

			'filial_id' => $filial_id == -1 ? null : $filial_id,
			'entrada' => 0
		];
		return $data;
	}

	private function cadastrarCliente($r){
		$nome = $r[0];
		$documento = $r[1];
		$documento = trim(preg_replace('/[^0-9]/', '', $documento));

		$ie = $r[2];
		$rua = $r[4];
		$numero = $r[5];
		$bairro = $r[6];
		$cidade = $r[7];
		$uf = $r[8];
		$cep = $r[9];
		$email = $r[10];
		$cidade = Cidade::where('nome', $cidade)->where('uf', $uf)->first();
		return Cliente::create([
			'razao_social' => $nome,
			'cpf_cnpj' => $documento,
			'ie_rg' => $ie != '' ? $ie : '',
			'rua' => $rua,
			'numero' => $numero,
			'bairro' => $bairro,
			'cep' => $cep,
			'email' => $email != '' ? $email : '',
			'cidade_id' => $cidade ? $cidade->id : 1,
			'consumidor_final' => 1,
			'limite_venda' => 0,
			'contribuinte' => $ie != '' ? 1 : 0,
			'empresa_id' => $this->empresa_id
		]);
	}

	private function validaArquivo($rows){
		$cont = 0;
		$msgErro = "";
		foreach($rows as $row){
			foreach($row as $key => $r){
				if($key > 0){
					$nome = $r[0];
					$documento = $r[1];
					$ie = $r[2];

					$documento = trim(preg_replace('/[^0-9]/', '', $documento));
					$cliente = Cliente::where('cpf_cnpj', $documento)
					->where('empresa_id', $this->empresa_id)
					->first();
					if($cliente == null){
						$mask = "###.###.###-##";
						if(strlen($documento) == 14){
							$mask = "##.###.###/####-##";
						}

						$documento = $this->__mask($documento, $mask);
						$cliente = Cliente::where('cpf_cnpj', $documento)
						->where('empresa_id', $this->empresa_id)
						->first();

					}
					$rua = $r[4];
					$numero = $r[5];
					$bairro = $r[6];
					$cidade = $r[7];
					$uf = $r[8];
					$cep = $r[9];
					$email = $r[10];
					$valor = $r[11];
					$vencimento = $r[12];

					if($cliente == null){
						if(strlen($nome) == 0){
							$msgErro .= "Coluna nome em branco na linha: $cont | "; 
						}

						if(strlen($rua) == 0){
							$msgErro .= "Coluna rua em branco na linha: $cont | "; 
						}

						if(strlen($numero) == 0){
							$msgErro .= "Coluna numero em branco na linha: $cont"; 
						}

						if(strlen($numero) == 0){
							$msgErro .= "Coluna numero em branco na linha: $cont"; 
						}
						if(strlen($bairro) == 0){
							$msgErro .= "Coluna bairro em branco na linha: $cont"; 
						}
						if(strlen($cidade) == 0){
							$msgErro .= "Coluna cidade em branco na linha: $cont"; 
						}
						if(strlen($uf) == 0){
							$msgErro .= "Coluna uf em branco na linha: $cont"; 
						}
					}

					if(strlen($valor) == 0){
						$msgErro .= "Coluna valor em branco na linha: $cont"; 
					}
					if(strlen($vencimento) == 0){
						$msgErro .= "Coluna vencimento em branco na linha: $cont"; 
					}

					if($msgErro != ""){
						return $msgErro;
					}
					$cont++;
				}
			}
		}

		return $msgErro;
	}

	private function __mask($val, $mask){
		$maskared = '';
		$k = 0;
		for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
			if ($mask[$i] == '#') {
				if (isset($val[$k])) {
					$maskared .= $val[$k++];
				}
			} else {
				if (isset($mask[$i])) {
					$maskared .= $mask[$i];
				}
			}
		}

		return $maskared;
	}

	public function recibo($id)
	{
		// Recupera a conta a receber pelo ID passado na rota (nesse caso, o ID é da conta)
		$conta = \App\Models\ContaReceber::findOrFail($id);
		$cliente = $conta->cliente;
		// Dados da empresa (ajuste conforme sua lógica)
		$empresa = (object)[
			'nome'     => config('app.nome_empresa', 'Minha Empresa'),
			'cnpj'     => '00.000.000/0001-00',
			'endereco' => 'Rua Exemplo, 123, Cidade, Estado',
			'telefone' => '(00) 0000-0000'
		];
		$title = 'Recibo de Recebimento';
		
		$recibo = \App\Models\ReciboReceber::whereHas('contasReceber', function($q) use ($conta) {
			$q->where('conta_recebers.id', $conta->id);
		})->first();		
		
		if ($recibo) {
			$valor = $recibo->valor_pago;
		} else {
			// Se não existir recibo, usa o valor da conta
			$valor = floatval(str_replace(',', '.', $conta->valor_recebido));
		}
		
		// Calcula o número do recibo: se for recibo individual, pode usar o id do recibo; caso agrupado, pode usar o id da conta
		$numeroRecibo = $recibo 
			? 'REC-' . str_pad($recibo->id, 5, '0', STR_PAD_LEFT) 
			: 'REC-' . str_pad($conta->id, 5, '0', STR_PAD_LEFT);
		
		// Converte o valor por extenso utilizando seu helper
		$valorPorExtenso = \valorPorExtenso($valor);
		
		return view('contaReceber.recibo', compact(
			'title',
			'conta',
			'cliente',
			'empresa',
			'numeroRecibo',
			'valorPorExtenso',
			'recibo'
		));
	}
	
	public function gerarRecibo(Request $request)
	{
		$data = $request->all();
		
		// Ajusta 'conta_id' para $contaId
		if (isset($data['conta_id'])) {
			$contaId = $data['conta_id'];
			unset($data['conta_id']);
		} else {
			return back()->withErrors('Conta a receber não informada!');
		}
		
		// Recupera a conta a receber
		$conta = \App\Models\ContaReceber::find($contaId);
		if (!$conta) {
			return back()->withErrors('Conta a receber não encontrada!');
		}
		
		// Converte valor_pago, se necessário
		if (isset($data['valor_pago'])) {
			$data['valor_pago'] = floatval(str_replace(',', '.', $data['valor_pago']));
		}
		
		// Remove campos que não serão usados
		unset($data['numero_recibo'], $data['data_venda'], $data['produtos_venda'], $data['incluir_venda']);
		
		// Procura se já existe um recibo associado a essa conta via relacionamento many-to-many
		$recibo = \App\Models\ReciboReceber::whereHas('contasReceber', function ($q) use ($conta) {
			$q->where('conta_recebers.id', $conta->id);
		})->first();		
		
		if ($recibo) {
			$recibo->update($data);
		} else {
			$recibo = \App\Models\ReciboReceber::create($data);
			// Para recibo individual, associa a conta através do relacionamento many-to-many (tabela pivot)
			$recibo->contasReceber()->attach($conta->id);
		}
		
		session()->flash('recibo_id', $recibo->id);
		
		// Redireciona para a tela de pré-visualização do recibo usando o ID da conta
		return redirect()->route('contasReceber.recibo', $conta->id);
	}
	
public function pdfRecibo($id)
{
    // Recupera o recibo
    $recibo = \App\Models\ReciboReceber::findOrFail($id);
    $numeroRecibo = 'REC-' . str_pad($recibo->id, 5, '0', STR_PAD_LEFT);

    if ($recibo->contasReceber()->exists()) {
        $conta = $recibo->contasReceber()->first();
        $empresa = \App\Models\Empresa::find($conta->empresa_id);
    } else {
        $conta = null;
        $empresa = $recibo->empresa;
    }

    $html = view('contaReceber.pdf_recibo', [
        'recibo'       => $recibo,
        'numeroRecibo' => $numeroRecibo,
        'empresa'      => $empresa,
        'conta'        => $conta,
    ])->render();

    $dompdf = new \Dompdf\Dompdf(["enable_remote" => true]);
    $dompdf->loadHtml($html);
    $dompdf->setPaper("A4");
    $dompdf->render();

    // Captura a saída do PDF
    $output = $dompdf->output();
    return response($output, 200)
           ->header('Content-Type', 'application/pdf')
           ->header('Content-Disposition', 'inline; filename="recibo.pdf"');
}
	
public function pdfReciboTermica($id)
{
    $recibo = \App\Models\ReciboReceber::findOrFail($id);
    $numeroRecibo = 'REC-' . str_pad($recibo->id, 5, '0', STR_PAD_LEFT);

    if ($recibo->contasReceber()->exists()) {
        $conta = $recibo->contasReceber()->first();
        $empresa = \App\Models\Empresa::find($conta->empresa_id);
    } else {
        $conta = null;
        $empresa = $recibo->empresa;
    }

    $html = view('contaReceber.pdf_recibo_termica', [
        'recibo'       => $recibo,
        'numeroRecibo' => $numeroRecibo,
        'empresa'      => $empresa,
        'conta'        => $conta,
    ])->render();

    $dompdf = new \Dompdf\Dompdf(["enable_remote" => true]);
    $dompdf->loadHtml($html);
    // O CSS da view define o tamanho da página via @page
    $dompdf->render();

    // Captura a saída do PDF
    $output = $dompdf->output();
    return response($output, 200)
           ->header('Content-Type', 'application/pdf')
           ->header('Content-Disposition', 'inline; filename="recibo_termica.pdf"');
}
	
	public function reciboMulti($id)
	{
		// Carrega o recibo
		$recibo = \App\Models\ReciboReceber::findOrFail($id);
	
		// Carrega as contas relacionadas (via pivot)
		$contas = $recibo->contasReceber; // Relação many-to-many
	
		// Se quiser exibir alguma informação de "uma conta", pode pegar a primeira
		// (ou pode não definir $conta se não for necessário)
		$conta = $contas->first(); 
	
		// Se não houver nenhuma conta, você pode tratar
		if (!$conta) {
			// Retorna com alguma mensagem de erro ou redireciona
			return "Nenhuma conta associada ao recibo!";
		}
	
		// Defina a soma (se desejar exibir um total)
		$soma = $contas->sum('valor_integral');
	
		// Pega dados de empresa da primeira conta (por exemplo)
		$empresa = \App\Models\Empresa::find($conta->empresa_id);
	
		// Título da página (opcional)
		$title = 'Recibo Agrupado';
	
		// Retorna a view 'contaReceber.reciboMulti' passando as variáveis necessárias
		return view('contaReceber.reciboMulti', compact(
			'title',
			'recibo',
			'contas',
			'conta',
			'soma',
			'empresa'
		));
	}
	
	public function atualizarReciboMulti($id, Request $request)
{
    // Carrega o recibo (agrupado) que queremos atualizar
    $recibo = \App\Models\ReciboReceber::findOrFail($id);

    // Converte/ajusta os campos do Request, se necessário
    // Por exemplo, data_pagamento (de "Y-m-d" para Carbon, se quiser),
    // ou valor_pago (remover pontuação, converter para float, etc.)

    // Exemplo simples de conversão de valor_pago
    if ($request->has('valor_pago')) {
        $valorConvertido = str_replace(',', '.', str_replace('.', '', $request->valor_pago));
        $request->merge(['valor_pago' => floatval($valorConvertido)]);
    }

    // Atualiza os campos do recibo
    // Observando que 'recibo_conta_rec' não tem 'conta_receber_id',
    // pois é many-to-many.
    $recibo->update([
        'data_pagamento'  => $request->data_pagamento, 
        'cliente'         => $request->cliente,
        'documento'       => $request->documento,
        'endereco'        => $request->endereco,
        'telefone'        => $request->telefone,
        'forma_pagamento' => $request->forma_pagamento,
        'valor_pago'      => $request->valor_pago, 
        'valor_extenso'   => $request->valor_extenso,
        'referencia'      => $request->referencia,
        'observacao'      => $request->observacao,
    ]);

    // Se precisar mexer nas contas associadas (ex: remover/adição), você faria aqui.
    // Ex: $recibo->contasReceber()->sync($arrayDeIdsDasContas);

    // Redireciona de volta para a view de reciboMulti (ou para onde preferir)
    return redirect()->route('contasReceber.reciboMulti', $recibo->id)
                     ->with('mensagem_sucesso', 'Recibo atualizado com sucesso!');
}

public function listarRecibos()
{
    // Busque os recibos da empresa logada (ajuste a lógica conforme necessário)
    $recibos = \App\Models\ReciboReceber::orderBy('created_at', 'desc')->get();
    
    $title = "Lista de Recibos de Recebimento";

	return view('contaReceber.list_recibos', compact('recibos', 'title'));

}

public function excluirRecibo($id)
{
    // Encontra o recibo ou lança 404
    $recibo = \App\Models\ReciboReceber::findOrFail($id);

    // Se houver relacionamento na tabela pivot, desassocia
    $recibo->contasReceber()->detach();

    // Exclui o recibo
    $recibo->delete();

    // Redireciona para a lista de recibos com uma mensagem de sucesso
	return redirect()->route('contasReceber.recibos')
	->with('mensagem_sucesso', 'Recibo excluído com sucesso!');
}

public function getContasDoRecibo($id)
{
    // Carrega o recibo e suas contas associadas (relação many-to-many)
    $recibo = \App\Models\ReciboReceber::with('contasReceber')->findOrFail($id);

    // Se quiser retornar apenas JSON:
    return response()->json($recibo->contasReceber);

}

public function novoReciboAvulso()
{
    // Apenas retorna uma view com um form para criar um recibo "avulso".
    // Esse form não tem "contas" associadas, e sim dados livres:
    // (cliente, documento, endereço, telefone, valor_pago, etc.)

	$title = "Novo Recibo Avulso";
    return view('contaReceber.novo_recibo_avulso', compact('title')); 
}

public function storeReciboAvulso(Request $request)
{
    // Valida os dados do formulário
    $dados = $request->validate([
        'data_pagamento'  => 'required|date',
        'cliente'         => 'required|string|max:255',
        'documento'       => 'nullable|string|max:50',
        'endereco'        => 'nullable|string|max:255',
        'telefone'        => 'nullable|string|max:50',
        'valor_pago'      => 'required',
        'forma_pagamento' => 'nullable|string|max:50',
        'referencia'      => 'nullable|string|max:255',
        'observacao'      => 'nullable|string|max:255',
    ]);

    // Converte o valor_pago para float (removendo pontos e trocando vírgulas por ponto)
    $valorRecebido = floatval(str_replace(',', '.', str_replace('.', '', $dados['valor_pago'])));
    $dados['valor_pago'] = $valorRecebido;

    // Define o campo valor_extenso automaticamente usando o helper
    $dados['valor_extenso'] = \valorPorExtenso($valorRecebido);

    // Define o empresa_id (ajuste conforme sua lógica, por exemplo, via auth ou sessão)
    $empresaId = auth()->user() ? auth()->user()->empresa_id : session('empresa_id', config('app.empresa_id', 1));
    $dados['empresa_id'] = $empresaId;

    // Cria um recibo avulso (sem associação com contas a receber)
    $recibo = \App\Models\ReciboReceber::create($dados);

    return redirect()->route('contasReceber.recibos')
                     ->with('mensagem_sucesso', 'Recibo avulso criado com sucesso!');
}

public function editarRecibo($id)
{
    // Aqui $id é o ID do recibo (na tabela recibo_conta_rec)
    $recibo = \App\Models\ReciboReceber::with('contasReceber')->findOrFail($id);
    
    // Se o recibo estiver associado a contas (via pivot), pegue a primeira conta para obter dados adicionais, se necessário
    $conta = $recibo->contasReceber()->first();
    
    // Se necessário, obtenha o cliente da conta ou use os próprios dados do recibo
    $cliente = $conta ? $conta->cliente : null;
    
    // Carrega os dados da empresa – ajuste conforme sua lógica (pode ser via sessão, auth, etc.)
    $empresa = \App\Models\Empresa::find($recibo->empresa_id);
    
    $title = "Editar Recibo de Recebimento";
    
    // Retorne a view de edição de recibo (por exemplo, resources/views/contaReceber/editar_recibo.blade.php)
    return view('contaReceber.editar_recibo', compact(
        'recibo',
        'conta',
        'cliente',
        'empresa',
        'title'
    ));
}

public function atualizarRecibo($id, Request $request)
{
    $recibo = \App\Models\ReciboReceber::findOrFail($id);
    
    // Valide e converta os campos conforme necessário
    $dados = $request->validate([
        'data_pagamento'  => 'required|date_format:Y-m-d',
        'cliente'         => 'required|string|max:255',
        'documento'       => 'nullable|string|max:50',
        'endereco'        => 'nullable|string|max:255',
        'telefone'        => 'nullable|string|max:50',
        'valor_pago'      => 'required',
        'valor_extenso'   => 'nullable|string',
        'forma_pagamento' => 'nullable|string|max:50',
        'referencia'      => 'nullable|string|max:255',
        'observacao'      => 'nullable|string|max:255',
    ]);
    
    // Converte o valor_pago, se necessário
    $dados['valor_pago'] = floatval(str_replace(',', '.', str_replace('.', '', $dados['valor_pago'])));
    
    // Atualiza o recibo
    $recibo->update($dados);
    
    return redirect()->route('contasReceber.recibos')->with('mensagem_sucesso', 'Recibo atualizado com sucesso!');
}

public function novoVinculado(Request $request)
{
// Obtém o usuário logado corretamente
$usuario = auth()->user() ?? session('user_logged');

// Se não houver usuário na sessão, redireciona para login
if (!$usuario) {
    return redirect()->route('login')->withErrors("Usuário não autenticado!");
}

// Obtém empresa e filial associadas ao usuário logado
$empresaId = $usuario->empresa_id ?? session('empresa_id', config('app.empresa_id', 1));
$filialId = $usuario->filial_id ?? null;


    // Recupera os filtros enviados via GET
    $nome = $request->input('nome');
    $documento = $request->input('documento');
    $dataPagamento = $request->input('data_pagamento');

    // Recupera os clientes vinculados à empresa e filial logada
    $clientes = Cliente::where('empresa_id', $empresaId)
        ->when($filialId, function ($query) use ($filialId) {
            $query->where('filial_id', $filialId);
        })
        ->where('inativo', false)
        ->orderBy('razao_social', 'asc')
        ->get();

    // Consulta as contas a receber SEM recibo vinculado
    $query = ContaReceber::whereDoesntHave('recibos')
        ->where('empresa_id', $empresaId)
        ->whereNotNull('cliente_id') // Exclui contas sem cliente associado
        ->whereHas('cliente'); // Confirma que apenas contas com cliente são carregadas

    // Aplica o filtro de filial (se houver)
    if ($filialId) {
        $query->where('filial_id', $filialId);
    }

    // Aplica filtro de nome do cliente ou CPF/CNPJ
    if ($nome) {
        $query->whereHas('cliente', function ($q) use ($nome) {
            $q->where('razao_social', 'like', '%' . $nome . '%')
              ->orWhere('cpf_cnpj', 'like', '%' . $nome . '%');
        });
    }

    // Aplica filtro de documento (se informado)
    if ($documento) {
        $query->where('documento', 'like', '%' . $documento . '%');
    }

    // Aplica filtro de data de pagamento (se informado)
    if ($dataPagamento) {
        $query->whereDate('data_recebimento', $dataPagamento);
    }

    // Obtém os resultados
    $contas = $query->get();

    // Define o título para a view
    $title = 'Gerar Recibo Vinculado';

    return view('contaReceber.novo_vinculado', compact(
        'contas', 'clientes', 'nome', 'documento', 'dataPagamento', 'title'
    ));
}

public function vincular($id)
{
    // Recupera a conta a receber
    $conta = \App\Models\ContaReceber::findOrFail($id);
    
    // Aqui você pode, por exemplo, exibir uma view para confirmar a vinculação
    // ou processar a criação do recibo vinculado.
    return view('contaReceber.vincular', compact('conta'));
}

public function gerarReciboMultiAutomatico(Request $request)
{
    $ids = $request->input('contasSelecionadas');
    if (empty($ids) || !is_array($ids)) {
        return redirect()->back()->with('erro', 'Nenhuma conta selecionada.');
    }

    $contas = \App\Models\ContaReceber::whereIn('id', $ids)->get();
    if ($contas->isEmpty()) {
        return redirect()->back()->with('erro', 'Nenhuma conta encontrada.');
    }
    $somaTotal = $contas->sum('valor_integral');

    $primeiraConta = $contas->first();
    if (!$primeiraConta || !$primeiraConta->cliente) {
        return redirect()->back()->with('erro', 'Não foi possível obter os dados do cliente.');
    }
    $clienteNome = $primeiraConta->cliente->razao_social;
    $documento   = $primeiraConta->cliente->cpf_cnpj;

    $usuario = auth()->user() ?? session('user_logged');
    if (!$usuario) {
        return redirect()->route('login')->withErrors('Usuário não autenticado!');
    }
    $empresaId = is_array($usuario) ? ($usuario['empresa_id'] ?? null) : $usuario->empresa_id;
    if (!$empresaId) {
        return redirect()->back()->withErrors('Empresa não identificada.');
    }

    try {
        $recibo = \App\Models\ReciboReceber::create([
            'valor_pago'    => $somaTotal,
            'data_pagamento'=> now(),
            'empresa_id'    => $empresaId,
            'cliente'       => $clienteNome,
            'documento'     => $documento,
            // Se tiver mais campos, inclua-os aqui
        ]);
        \Log::info('Recibo criado:', $recibo->toArray());
    } catch (\Exception $e) {
        \Log::error('Erro ao criar recibo: ' . $e->getMessage());
        return redirect()->back()->with('erro', 'Erro ao criar recibo: ' . $e->getMessage());
    }

    try {
        $recibo->contasReceber()->attach($ids);
        \Log::info('IDs anexados ao recibo:', $ids);
    } catch (\Exception $e) {
        \Log::error('Erro ao associar contas ao recibo: ' . $e->getMessage());
        return redirect()->back()->with('erro', 'Erro ao associar contas ao recibo: ' . $e->getMessage());
    }

    return redirect()->route('contasReceber.recibos')
                     ->with('mensagem_sucesso', 'Recibo agrupado criado com sucesso!');
}

}
