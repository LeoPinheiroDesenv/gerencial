<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\CreditoVenda;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\OrdemServico;
use Dompdf\Dompdf;

class FluxoCaixaController extends Controller
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

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	private function parseViewData($date){
		
		return date('d/m/Y', strtotime(str_replace("/", "-", $date)));
	}


	public function index(){
		$datas = $this->returnDateMesAtual();

		$fluxo = $this->criarArrayDeDatas($datas['start'], $datas['end']);

		return view('fluxoCaixa/list')
		->with('fluxo', $fluxo)
		->with('title', 'Movimentação de caixa');
	}

	public function filtro(Request $request){

		if($request->data_inicial && $request->data_final){
			$fluxo = $this->criarArrayDeDatas($this->parseDate($request->data_inicial), 
				$this->parseDate($request->data_final));
			return view('fluxoCaixa/list')
			->with('fluxo', $fluxo)
			->with('data_inicial', $request->data_inicial)
			->with('data_final', $request->data_final)

			->with('dataInicial', $this->parseDate($request->data_inicial))
			->with('dataFinal', $this->parseDate($request->data_final))
			->with('title', 'Fluxo de Caixa');
		}else{
			session()->flash("mensagem_erro", "Informe data inicial e final!!");
			return redirect()->back();
		}
	}

	private function returnDateMesAtual(){
		$hoje = date('Y-m-d');
		$primeiroDia = substr($hoje, 0, 7) . "-01";

		return ['start' => $primeiroDia, 'end' => $hoje];
	}

	private function getContasReceber($data){
		$valor = 0;

		$contas = ContaReceber::
		selectRaw('data_recebimento as data, sum(valor_recebido) as valor')
		// ->where('updated_at', $data)
		->whereBetween('data_recebimento', [
			$data . " 00:00:00", 
			$data . " 23:59:00"
		])
		->where('status', 1)
		->where('empresa_id', $this->empresa_id)
		// ->groupBy('updated_at')
		->first();

		$valor += $contas->valor ?? 0;

		// $contas = ContaReceber::
		// selectRaw('data_vencimento as data, sum(valor_integral) as valor')
		// // ->where('updated_at', $data)
		// ->whereBetween('data_vencimento', [
		// 	$data . " 00:00:00", 
		// 	$data . " 23:59:00"
		// ])
		// ->where('status', 0)
		// ->where('empresa_id', $this->empresa_id)
		// ->groupBy('updated_at')
		// ->first();

		// $valor += $contas->valor ?? 0;


		return $valor;
	}

	private function getCreditoVenda($data){
		$creditos = CreditoVenda::
		selectRaw('DATE_FORMAT(vendas.data_registro, "%Y-%m-%d") as data, sum(vendas.valor_total) as valor')
		->join('vendas', 'vendas.id' , '=', 'credito_vendas.venda_id')
		->whereRaw("DATE_FORMAT(credito_vendas.updated_at, '%Y-%m-%d') = '$data'")
		->where('credito_vendas.status', true)
		->where('vendas.empresa_id', $this->empresa_id)
		->groupBy('data')
		->first();

		return $creditos;
	}

	private function getOs($data){
		$orders = OrdemServico::
		whereDate("updated_at", $data)
		->where('estado', 'ap')
		->where('empresa_id', $this->empresa_id)
		->get();

		$sum = 0;
		foreach($orders as $os){
			$sum += $os->total_os();
		}

		return $sum;
	}

	private function getContasPagar($data){
		// $contas = ContaPagar::
		// selectRaw('data_vencimento as data, sum(valor_integral) as valor')
		// ->where('data_vencimento', $data)
		// ->where('empresa_id', $this->empresa_id)
		// ->where('status', 1)
		// ->groupBy('data_vencimento')
		// ->first();

		$contas = ContaPagar::
		selectRaw('data_pagamento as data, sum(valor_pago) as valor')
		// ->where('updated_at', $data)
		->whereBetween('data_pagamento', [
			$data . " 00:00:00", 
			$data . " 23:59:00"
		])
		->where('empresa_id', $this->empresa_id)
		->where('status', 1)
		->first();

		return $contas->valor ?? 0;
	}

	private function getVendas($data){
		$venda = Venda::
		selectRaw('DATE_FORMAT(data_registro, "%Y-%m-%d") as data, sum(valor_total) as valor')
		->whereRaw("DATE_FORMAT(data_registro, '%Y-%m-%d') = '$data' AND forma_pagamento = 'a_vista'")
		->where('empresa_id', $this->empresa_id)
		->groupBy('data')
		->first();
		return $venda;
	}

	private function getVendaCaixa($data){
		$venda = VendaCaixa::
		selectRaw('DATE_FORMAT(data_registro, "%Y-%m-%d") as data, sum(valor_total) as valor')
		->whereRaw("DATE_FORMAT(data_registro, '%Y-%m-%d') = '$data'")
		->where('empresa_id', $this->empresa_id)
		->groupBy('data')
		->first();
		return $venda;
	}

	private function criarArrayDeDatas($inicio, $fim){
		$diferenca = strtotime($fim) - strtotime($inicio);
		$dias = floor($diferenca / (60 * 60 * 24));
		$global = [];
		$dataAtual = $inicio;
		for($aux = 0; $aux < $dias+1; $aux++){

			$contaReceber = $this->getContasReceber($dataAtual);

			$contaPagar = $this->getContasPagar($dataAtual);
			$credito = $this->getCreditoVenda($dataAtual);
			$venda = $this->getVendas($dataAtual);
			$vendaCaixa = $this->getVendaCaixa($dataAtual);
			$os = $this->getOs($dataAtual);

			$tst = [
				'data' => $this->parseViewData($dataAtual),
				'conta_receber' => $contaReceber,
				'conta_pagar' => $contaPagar,
				'credito_venda' => $credito->valor ?? 0,
				'venda' => $venda->valor ?? 0,
				'venda_caixa' => $vendaCaixa->valor ?? 0,
				'os' => $os,
			];

			array_push($global, $tst);

			$temp = [];

			$dataAtual = date('Y-m-d', strtotime($dataAtual. '+1day'));
		}

		return $global;
	}

	public function relatorioIndex(){

		$domPdf = new Dompdf();

		// ob_start();
		$datas = $this->returnDateMesAtual();
		
		$fluxo = $this->criarArrayDeDatas($datas['start'], $datas['end']);
		$p = view('fluxoCaixa/relatorio')
		->with('title', 'Movimentação de caixa')
		->with('fluxo', $fluxo);

		// return $p;
		$domPdf->loadHtml($p);

		// $pdf = ob_get_clean();

		$domPdf->setPaper("A4", "landscape");
		$domPdf->render();
		$domPdf->stream("file.pdf", array("Attachment" => false));
	}

	public function relatorioFiltro($data_inicial, $data_final){

		$domPdf = new Dompdf();

		// ob_start();
		
		$fluxo = $this->criarArrayDeDatas($this->parseDate($data_inicial), 
			$this->parseDate($data_final));
		$p = view('fluxoCaixa/relatorio')
		->with('data_inicial', \Carbon\Carbon::parse($data_inicial)->format('d/m/Y'))
		->with('data_final', \Carbon\Carbon::parse($data_final)->format('d/m/Y'))
		->with('title', 'Movimentação de caixa')
		->with('fluxo', $fluxo);
		$domPdf->loadHtml($p);

		// $pdf = ob_get_clean();

		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Movimento de caixa.pdf");
	}

}
