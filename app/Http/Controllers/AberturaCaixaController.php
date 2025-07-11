<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AberturaCaixa;
use App\Models\VendaCaixa;
use App\Models\Venda;
use App\Models\Usuario;
use App\Models\Nfse;
use App\Models\ConfigNota;
use App\Models\SuprimentoCaixa;
use App\Models\SangriaCaixa;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\ContaEmpresa;
use Dompdf\Dompdf;
use NFePHP\DA\NFe\ComprovanteFechamentoCaixa;

class AberturaCaixaController extends Controller
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

	public function abertos(){
		// $data = AberturaCaixa::
		// where('empresa_id', $this->empresa_id)
		// ->where('status', 0)
		// ->first();
		// $data->status = 1;
		// $data->save();

	}

	public function abrir(Request $request){

		$ultimaVendaNfce = VendaCaixa::
		where('empresa_id', $this->empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfe = Venda::
		where('empresa_id', $this->empresa_id)
		->orderBy('id', 'desc')->first();
		$verify = $this->verificaAberturaCaixa();

		$conta_id = $request->conta_id;
		if($verify == -1){
			$result = AberturaCaixa::create([
				'usuario_id' => get_id_user(),
				'valor' => str_replace(",", ".", $request->valor),
				'empresa_id' => $this->empresa_id,
				'primeira_venda_nfe' => $ultimaVendaNfe != null ? 
				$ultimaVendaNfe->id : 0,
				'primeira_venda_nfce' => $ultimaVendaNfce != null ? 
				$ultimaVendaNfce->id : 0,
				'status' => 0,
				'filial_id' => $request->filial_id,
				'conta_id' => $conta_id
			]);
			echo json_encode($result);
		}else{
			echo json_encode(true);
		}
	}

	public function verificaHoje(){
		echo json_encode($this->verificaAberturaCaixa());
	}

	public function diaria(){
		date_default_timezone_set('America/Sao_Paulo');
		$hoje = date("Y-m-d") . " 00:00:00";
		$amanha = date('Y-m-d', strtotime('+1 days')). " 00:00:00";
		$abertura = AberturaCaixa::
		whereBetween('data_registro', [$hoje, 
			$amanha])
		->where('empresa_id', $this->empresa_id)
		->first();

		echo json_encode($abertura);
	}

	private function setUsuario($sangrias){
		for($aux = 0; $aux < count($sangrias); $aux++){
			$sangrias[$aux]['nome_usuario'] = $sangrias[$aux]->usuario->nome;
		}
		return $sangrias;
	}

	private function verificaAberturaCaixa(){
		$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

		$ab = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$ab2 = AberturaCaixa::where('ultima_venda_nfe', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		if($ab != null && $ab2 == null){
			return $ab->valor;
		}else if($ab == null && $ab2 != null){
			$ab2->valor;
		}else if($ab != null && $ab2 != null){
			if(strtotime($ab->created_at) > strtotime($ab2->created_at)){
				$ab->valor;
			}else{
				$ab2->valor;
			}
		}else{
			return -1;
		}
		if($ab != null) return $ab->valor;
		else return -1;
	}

	//view do caixa

	public function index(){

		$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();
		if($config == null){
			session()->flash('mensagem_erro', 'Configure o emitente');
			return redirect('/configNF');
		}

		$abertura = $this->verificaAberturaCaixa();
		$ultimaFechadaNfce = AberturaCaixa::where('ultima_venda_nfce', '>', 0)
		->where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$ultimaFechadaNfe = AberturaCaixa::where('ultima_venda_nfe', '>', 0)
		->where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfce = VendaCaixa::
		where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfe = Venda::
		where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$vendas = [];
		$somaTiposPagamento = [];

		$caixa = [];
		$nfse = [];

		$contasRecebidas = 0;
		$contasPagas = 0;
		if($abertura != -1){
			$contasRecebidas = $this->getContasRecebidasCaixaAberto($config);
			$contasPagas = $this->getContasPagasCaixaAberto($config);
			$caixa = $this->getCaixaAberto();
			$nfse = $this->getNotasServico($config);

		}

		
		$ab = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('ultima_venda_nfe', 0)
		->where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->where('status', 0)
		->orderBy('id', 'desc')->first();

		$usuarios = [];

		$user = Usuario::find(get_id_user());

		if($user->adm){
			$usuarios = Usuario::
			where('empresa_id', $this->empresa_id)
			->get();
		}
		
		$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();
		$contasEmpresa = ContaEmpresa::where('empresa_id', $this->empresa_id)
		->where('status', 1)->get();

		return view('caixa/index')
		->with('vendas', $vendas)
		->with('nfse', $nfse)
		->with('usuario_id', get_id_user())
		->with('usuarios', $usuarios)
		->with('config', $config)
		->with('abertura', $ab)
		->with('contasRecebidas', $contasRecebidas)
		->with('contasPagas', $contasPagas)
		->with('contasEmpresa', $contasEmpresa)
		->with('caixaJs', true)
		->with('caixa', $caixa)
		->with('title', 'Caixa');
	}

	private function getContasPagasCaixaAberto($config){
		$abertura = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$sumContas = ContaPagar::where('empresa_id', $this->empresa_id)
		// ->whereDate('data_recebimento', $abertura->created_at)
		->whereBetween('data_pagamento',
			[
				$abertura->created_at,
				date('Y-m-d') . " 23:59:59"
			]
		)
		->where('status', 1)
		->sum('valor_pago');

		$contas = ContaPagar::where('empresa_id', $this->empresa_id)
		// ->whereDate('data_recebimento', '>=', $abertura->created_at)
		->whereBetween('data_pagamento',
			[
				$abertura->created_at,
				date('Y-m-d') . " 23:59:59"
			]
		)
		->where('status', 1)->get();
		
		return [
			'soma' => $sumContas,
			'contas' => $contas
		];
	}

	private function getNotasServico($config){
		$abertura = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$sumNotas = Nfse::where('empresa_id', $this->empresa_id)
		->where('estado', '!=', 'rejeitado')
		->where('estado', '!=', 'cancelado')
		->whereBetween('created_at',
			[
				$abertura->created_at,
				date('Y-m-d') . " 23:59:59"
			]
		)
		->sum('valor_total');

		$contas = Nfse::where('empresa_id', $this->empresa_id)
		->where('estado', '!=', 'rejeitado')
		->where('estado', '!=', 'cancelado')
		->whereBetween('created_at',
			[
				$abertura->created_at,
				date('Y-m-d') . " 23:59:59"
			]
		)->get();
		
		return [
			'soma' => $sumNotas,
			'notas' => $contas
		];
	}

	private function getContasRecebidasCaixaAberto($config){
		$abertura = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$sumContas = ContaReceber::where('empresa_id', $this->empresa_id)
		// ->whereDate('data_recebimento', $abertura->created_at)
		->whereBetween('data_recebimento',
			[
				$abertura->created_at,
				date('Y-m-d') . " 23:59:59"
			]
		)
		->where('status', 1)
		->sum('valor_recebido');

		$contas = ContaReceber::where('empresa_id', $this->empresa_id)
		// ->whereDate('data_recebimento', '>=', $abertura->created_at)
		->whereBetween('data_recebimento',
			[
				$abertura->created_at,
				date('Y-m-d') . " 23:59:59"
			]
		)
		->where('status', 1)->get();

		return [
			'soma' => $sumContas,
			'contas' => $contas
		];
	}

	private function getCaixaAberto($usuario = 0){
		$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

		if($usuario == 0){
			$usuario = get_id_user();
		}

		$aberturaNfe = AberturaCaixa::where('ultima_venda_nfe', 0)
		->where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
			return $q->where('usuario_id', $usuario);
		})
		->orderBy('id', 'desc')->first();

		$aberturaNfce = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
			return $q->where('usuario_id', $usuario);
		})
		->orderBy('id', 'desc')->first();

		$ultimaVendaCaixa = VendaCaixa::
		where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
			return $q->where('usuario_id', $usuario);
		})
		->orderBy('id', 'desc')->first();

		$ultimaVenda = Venda::
		where('empresa_id', $this->empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
			return $q->where('usuario_id', $usuario);
		})
		->orderBy('id', 'desc')->first();

		$vendas = [];
		$somaTiposPagamento = [];

		if($ultimaVendaCaixa != null || $ultimaVenda != null){
			$ultimaVendaCaixa = $ultimaVendaCaixa != null ? $ultimaVendaCaixa->id : 0;
			$ultimaVenda = $ultimaVenda != null ? $ultimaVenda->id : 0;

			$vendasPdv = VendaCaixa
			::whereBetween('created_at', [$aberturaNfce->created_at, now()])
			->where('empresa_id', $this->empresa_id)
			->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
				return $q->where('usuario_id', $usuario);
			})
			->where('rascunho', 0)
			->where('consignado', 0)
			->get();

			$vendas = Venda
			::whereBetween('created_at', [$aberturaNfe->created_at, now()])
			->where('empresa_id', $this->empresa_id)
			->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
				return $q->where('usuario_id', $usuario);
			})
			->get();

			$vendas = $this->agrupaVendas($vendas, $vendasPdv);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);

		}

		usort($vendas, function($a, $b){
			return strtotime($a['created_at']) < strtotime($b['created_at']) ? 1 : -1;
		});

		$suprimentos = [];
		$sangrias = [];
		if($aberturaNfe != null){
			$suprimentos = SuprimentoCaixa::
			whereBetween('created_at', [
				$aberturaNfe->created_at, 
				date('Y-m-d H:i:s')
			])
			->where('empresa_id', $this->empresa_id)
			->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
				return $q->where('usuario_id', $usuario);
			})
			->get();

			$sangrias = SangriaCaixa::
			whereBetween('created_at', [$aberturaNfe->created_at, 
				date('Y-m-d H:i:s')])
			->where('empresa_id', $this->empresa_id)
			->when($config->caixa_por_usuario == 1, function ($q) use ($config, $usuario) {
				return $q->where('usuario_id', $usuario);
			})
			->get();
		}

		return [
			'vendas' => $vendas,
			'sangrias' => $sangrias,
			'suprimentos' => $suprimentos,
			'somaTiposPagamento' => $somaTiposPagamento
		];
	}

	public function filtroUsuario(Request $request){

		$usuario = $request->usuario;
		$user = Usuario::find(get_id_user());

		if(!$user->adm){
			session()->flash('mensagem_erro', 'Não permitido o acesso!');
			return redirect('/caixa');
		}

		$abertura = $this->verificaAberturaCaixa();
		$ultimaFechadaNfce = AberturaCaixa::where('ultima_venda_nfce', '>', 0)
		->where('empresa_id', $this->empresa_id)
		->where('usuario_id', $usuario)
		->orderBy('id', 'desc')->first();

		$ultimaFechadaNfe = AberturaCaixa::where('ultima_venda_nfe', '>', 0)
		->where('empresa_id', $this->empresa_id)
		->where('usuario_id', $usuario)
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfce = VendaCaixa::
		where('empresa_id', $this->empresa_id)
		->where('usuario_id', $usuario)
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfe = Venda::
		where('empresa_id', $this->empresa_id)
		->where('usuario_id', $usuario)
		->orderBy('id', 'desc')->first();

		$vendas = [];
		$somaTiposPagamento = [];

		$caixa = [];

		if($abertura != -1){
			$caixa = $this->getCaixaAberto($usuario);
		}else{
			if($usuario){
				$caixa = $this->getCaixaAberto($usuario);
			}
		}

		$ab = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('ultima_venda_nfe', 0)
		->where('empresa_id', $this->empresa_id)
		->where('usuario_id', $usuario)
		->where('status', 0)
		->orderBy('id', 'desc')->first();
		
		$usuarios = Usuario::
		where('empresa_id', $this->empresa_id)
		->get();

		return view('caixa/filtro')
		->with('vendas', $vendas)
		->with('usuario_id', $usuario)
		->with('usuarios', $usuarios)
		->with('abertura', $ab)
		->with('caixaJs', true)
		->with('caixa', $caixa)
		->with('title', 'Caixa');
	}

	private function agrupaVendas($vendas, $vendasPdv){
		$temp = [];
		foreach($vendas as $v){
			$v->tipo = 'VENDA';
			array_push($temp, $v);
		}

		foreach($vendasPdv as $v){
			$v->tipo = 'PDV';
			array_push($temp, $v);
		}

		return $temp;
	}

private function somaTiposPagamento($vendas){
    $tipos = $this->preparaTipos();

    foreach($vendas as $v){
        // ignora vendas canceladas
        if($v->estado === 'CANCELADO') {
            continue;
        }

        // só processa se veio um tipo de pagamento definido
        $tp = $v->tipo_pagamento;
        if(!$tp){
            continue;
        }

        if($tp != 99){

            // NFC-e / PDV
            if(isset($v->NFcNumero)){
                // só soma se não for rascunho nem consignado
                if(!$v->rascunho && !$v->consignado && array_key_exists($tp, $tipos)){
                    $tipos[$tp] += $v->valor_total;
                }
            } else {
                // NFe — com duplicatas
                if(count($v->duplicatas) > 0){
                    foreach($v->duplicatas as $d){
                        $key = Venda::getTipoPagamentoNFe($d->tipo_pagamento);
                        if(array_key_exists($key, $tipos)){
                            $tipos[$key] += $d->valor_integral;
                        }
                    }
                }
                // NFe — sem duplicatas
                else {
                    if(array_key_exists($tp, $tipos)){
                        $tipos[$tp] += ($v->valor_total - $v->desconto);
                    }
                }
            }

        } else {
            // pagamento multiplos (99) via fatura
            if($v->fatura){
                foreach($v->fatura as $f){
                    $key = trim($f->forma_pagamento);
                    if(array_key_exists($key, $tipos)){
                        $tipos[$key] += $f->valor;
                    }
                }
            }
        }
    }

    return $tipos;
}


	private function preparaTipos(){
		$temp = [];
		foreach(Venda::tiposPagamento() as $key => $tp){
			$temp[$key] = 0;
		}
		return $temp;
	}

	public function list(){

		$value = session('user_logged');
		if(!$value['adm']){
			session()->flash("mensagem_erro", "Somente adm podem acessar a lista de caixas!");
			return redirect()->back();
		}
		$aberturas = AberturaCaixa::
		where('empresa_id', $this->empresa_id)
		->where('ultima_venda_nfe', '>', 0)
		->orWhere('ultima_venda_nfce', '>', 0)
		->where('empresa_id', $this->empresa_id)
		->orderBy('id', 'desc')
		->get();

		return view('caixa/list')
		->with('aberturas', $aberturas)
		->with('title', 'Lista de Caixas');
	}

	public function filtro(Request $request){

		$aberturas = AberturaCaixa::
		where('empresa_id', $this->empresa_id)
		->whereBetween('created_at', [
			$this->parseDate($request->data_inicial), 
			$this->parseDate($request->data_final, true)
		])
		->where('ultima_venda_nfe', '>', 0)

		->orWhere('ultima_venda_nfce', '>', 0)
		->whereBetween('created_at', [
			$this->parseDate($request->data_inicial), 
			$this->parseDate($request->data_final, true)
		])
		->where('empresa_id', $this->empresa_id)

		->orderBy('id', 'desc')
		->get();

		return view('caixa/list')
		->with('aberturas', $aberturas)
		->with('dataInicial', $request->data_inicial)
		->with('dataFinal', $request->data_final)
		->with('title', 'Lista de Caixas');
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	private function getContasRecebidas($config, $inicio, $fim){

		$abertura = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$sumContas = ContaReceber::where('empresa_id', $this->empresa_id)
		->whereBetween('data_recebimento',
			[
				$inicio,
				$fim
			]
		)
		->where('status', 1)
		->sum('valor_recebido');

		return $sumContas;
	}

	public function detalhes($id){
		$abertura = AberturaCaixa::find($id);
		$aberturas = AberturaCaixa::
		where('empresa_id', $this->empresa_id)
		->get();
		$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

		if(valida_objeto($abertura)){

			$aberturaAnterior = AberturaCaixa::find($id-1);

			$fim = $abertura->updated_at;
			$inicio = $abertura->created_at;

            //dd($inicio);
			$vendasPdv = VendaCaixa
			::where('empresa_id', $this->empresa_id)
			->where('rascunho', 0)
			->where('consignado', 0)
			->whereBetween('created_at', [$inicio, $fim])
			->get();

			$vendas = Venda
			::where('empresa_id', $this->empresa_id)
			->whereBetween('created_at', [$inicio, $fim])
			->get();

			$vendas = $this->agrupaVendas($vendas, $vendasPdv);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);


			$suprimentos = SuprimentoCaixa::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			$sangrias = SangriaCaixa::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			$contasRecebidas = $this->getContasRecebidas($config, $inicio, $fim);
			$nfse = $suprimentos = Nfse::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			return view('caixa/detalhes')
			->with('abertura', $abertura)
			->with('vendas', $vendas)
			->with('nfse', $nfse)
			->with('suprimentos', $suprimentos)
			->with('contasRecebidas', $contasRecebidas)
			->with('sangrias', $sangrias)
			->with('somaTiposPagamento', $somaTiposPagamento)
			->with('title', 'Detalhes Caixa');
		}else{
			return redirect('/403');
		}
	}

	public function imprimir($id){
		$abertura = AberturaCaixa::find($id);
		$aberturas = AberturaCaixa::
		where('empresa_id', $this->empresa_id)
		->get();

		if(valida_objeto($abertura)){

			$aberturaAnterior = AberturaCaixa::find($id-1);

			// $fim = $abertura->updated_at;
			// $inicio = $aberturaAnterior == null ? '2016-01-01' : $aberturaAnterior->created_at;
			$fim = $abertura->updated_at;
			$inicio = $abertura->created_at;

			$vendasPdv = VendaCaixa
			::where('empresa_id', $this->empresa_id)
			->where('rascunho', 0)
			->where('consignado', 0)
			->whereBetween('created_at', [$inicio, $fim])
			->get();

			$vendas = Venda
			::where('empresa_id', $this->empresa_id)
			->whereBetween('created_at', [$inicio, $fim])
			->get();

			$vendas = $this->agrupaVendas($vendas, $vendasPdv);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);

			$suprimentos = SuprimentoCaixa::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			$sangrias = SangriaCaixa::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			$usuario = Usuario::find(get_id_user());
			$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

			$nfse = $suprimentos = Nfse::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			$p = view('caixa/relatorio')
			->with('abertura', $abertura)
			->with('vendas', $vendas)
			->with('nfse', $nfse)
			->with('suprimentos', $suprimentos)
			->with('sangrias', $sangrias)
			->with('usuario', $usuario)
			->with('config', $config)
			->with('somaTiposPagamento', $somaTiposPagamento)
			->with('title', 'Detalhes Caixa');

			// return $p;

			$domPdf = new Dompdf(["enable_remote" => true]);
			$domPdf->loadHtml($p);

			$pdf = ob_get_clean();

			$domPdf->setPaper("A4");
			$domPdf->render();
			$domPdf->stream("Relatório caixa.pdf", array("Attachment" => false));
		}else{
			return redirect('/403');
		}
	}

	public function imprimir80($id){
		$abertura = AberturaCaixa::find($id);
		$aberturas = AberturaCaixa::
		where('empresa_id', $this->empresa_id)
		->get();

		if(valida_objeto($abertura)){

			$aberturaAnterior = AberturaCaixa::find($id-1);

			$fim = $abertura->updated_at;
			$inicio = $aberturaAnterior == null ? '2016-01-01' : $aberturaAnterior->updated_at;

			$vendasPdv = VendaCaixa
			::where('empresa_id', $this->empresa_id)
			->where('rascunho', 0)
			->where('consignado', 0)
			->whereBetween('created_at', [$inicio, $fim])
			->get();

			$vendas = Venda
			::where('empresa_id', $this->empresa_id)
			->whereBetween('created_at', [$inicio, $fim])
			->get();

			$vendas = $this->agrupaVendas($vendas, $vendasPdv);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);

			$somaVendas = 0;

			foreach($vendas as $v){
				if($v->estado != 'CANCELADO' && !$v->rascunho && !$v->consignado){
					$total = $v->valor_total;
					if(!isset($v->cpf)){
						$total = $total-$v->desconto+$v->acrescimo;
					}

					$somaVendas += $total;
				}
			}
			$suprimentos = SuprimentoCaixa::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			$sangrias = SangriaCaixa::
			whereBetween('created_at', [$inicio, 
				$fim])
			->where('empresa_id', $this->empresa_id)
			->get();

			$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();
			$usuario = Usuario::find(get_id_user());

			$cupom = new ComprovanteFechamentoCaixa($vendas, '', $config, 80, $suprimentos, $sangrias, $somaTiposPagamento, $abertura, $usuario, $somaVendas);
			$cupom->monta();
			$pdf = $cupom->render();

			return response($pdf)
			->header('Content-Type', 'application/pdf');
		}else{
			return redirect('/403');
		}
	}

}
