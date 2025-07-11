<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\AberturaCaixa;
use App\Models\VendaCaixa;
use App\Models\Venda;
use App\Models\ConfigNota;
use App\Models\ItemContaEmpresa;
use App\Models\Usuario;
use NFePHP\DA\NFe\ComprovanteCaixa;
use App\Utils\ContaEmpresaUtil;

class SangriaCaixaController extends Controller
{
	protected $util;
	protected $empresa_id = null;
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

	public function save(Request $request){
		$valor = __replace($request->valor);

		if($valor <= $this->somaTotalEmCaixa()){
			$result = SangriaCaixa::create([
				'usuario_id' => get_id_user(),
				'valor' => $valor,
				'observacao' => $request->observacao ?? '',
				'empresa_id' => $this->empresa_id
			]);

			$abertura = AberturaCaixa::
			where('empresa_id', $this->empresa_id)
			->where('status', 0)
			->orderBy('id', 'desc')
			->first();

			if($abertura->conta_id){
				$data = [
					'conta_id' => $abertura->conta_id,
					'descricao' => "Sangria de caixa",
					'tipo_pagamento' => '01',
					'valor' => $valor,
					'caixa_id' => $abertura->id,
					'tipo' => 'saida'
				];
				$itemContaEmpresa = ItemContaEmpresa::create($data);
				$this->util->atualizaSaldo($itemContaEmpresa);

				$data = [
					'conta_id' => $request->conta_id,
					'descricao' => "Sangria de caixa",
					'tipo_pagamento' => '01',
					'valor' => $valor,
					'caixa_id' => $abertura->id,
					'tipo' => 'entrada'
				];
				$itemContaEmpresa = ItemContaEmpresa::create($data);
				$this->util->atualizaSaldo($itemContaEmpresa);
			}
			// echo json_encode($result);
			return response()->json($result, 200);
		}else{
			return response()->json("Valor de sangria ultrapassa valor em caixa!!", 401);
		}
	}

	public function teste(){
		$soma = $this->somaTotalEmCaixa();
		echo $soma;
	}

	private function somaTotalEmCaixa(){
		$abertura = AberturaCaixa::
		where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->orderBy('id', 'desc')
		->first();

		if($abertura == null) return 0;

		$soma = 0;

		$soma += $abertura->valor;

		$vendasPdv = VendaCaixa
		::whereBetween('id', [
			$abertura->primeira_venda_nfce, 
			($abertura->primeira_venda_nfce > 0 ? $abertura->primeira_venda_nfce : 1) * 10000
		])
		->selectRaw('sum(valor_total) as valor')
		->where('empresa_id', $this->empresa_id)
		->first();

		if($vendasPdv != null)
			$soma += $vendasPdv->valor;
		
		$vendas = Venda
		::whereBetween('id', [
			$abertura ? $abertura->primeira_venda_nfe : 0, 
			($abertura->primeira_venda_nfe > 0 ? $abertura->primeira_venda_nfe : 1) * 10000
		])
		->selectRaw('sum(valor_total) as valor')
		->where('empresa_id', $this->empresa_id)
		->first();

		if($vendas != null)
			$soma += $vendas->valor;


		// if($vendas != null)
		// 	$soma += $vendas->valor;
		// return $soma;

		$amanha = date('Y-m-d', strtotime('+1 days')). " 00:00:00";

		$sangriasSoma = SuprimentoCaixa::
		selectRaw('sum(valor) as valor')
		->whereBetween('created_at', [
			$abertura->created_at, 
			$amanha
		])
		->where('empresa_id', $this->empresa_id)
		->first();

		if($sangriasSoma != null)
			$soma += $sangriasSoma->valor;

		$sangriasSoma = SangriaCaixa::
		selectRaw('sum(valor) as valor')
		->whereBetween('created_at', [
			$abertura->created_at, 
			$amanha
		])
		->where('empresa_id', $this->empresa_id)
		->first();

		if($sangriasSoma != null){
			$soma -= $sangriasSoma->valor;
		}

		return $soma;
	}

public function diaria(){
    $ab = AberturaCaixa::
    where('empresa_id', $this->empresa_id)
    ->where('ultima_venda_nfe', 0)
    ->where('ultima_venda_nfce', 0)
    ->orderBy('id', 'desc')
    ->first();

    date_default_timezone_set('America/Sao_Paulo');
    $hoje = date("Y-m-d") . " 00:00:00";
    $amanha = date('Y-m-d', strtotime('+1 days')). " 00:00:00";

    $usuario_id = get_id_user(); // Pegando o ID do usuário logado corretamente

    \Log::info("🔍 Debug Laravel - Sangrias", [
        "Usuário logado" => $usuario_id,
        "Empresa ID" => $this->empresa_id,
        "Abertura Caixa" => $ab ? $ab->toArray() : "Nenhuma abertura encontrada"
    ]);

    // Filtrando corretamente pelas sangrias do usuário logado
$usuario_id = get_id_user(); // Obtém o usuário logado

$sangrias = SangriaCaixa::
whereBetween('created_at', [$ab->created_at, $amanha])
->where('empresa_id', $this->empresa_id)
->where('usuario_id', $usuario_id) // ✅ Filtrando apenas pelo usuário logado
->get();


    \Log::info("🔍 Debug Laravel - Sangrias", [
    "Usuário logado" => $usuario_id,
    "Empresa ID" => $this->empresa_id,
    "Abertura Caixa" => $ab ? $ab->toArray() : "Nenhuma abertura encontrada",
    "Sangrias encontradas" => $sangrias->toArray()
]);

    return response()->json([
        "sangrias" => $this->setUsuario($sangrias),
        "logs" => [
            "Usuário logado" => $usuario_id,
            "Empresa ID" => $this->empresa_id,
            "Abertura Caixa" => $ab ? $ab->toArray() : "Nenhuma abertura encontrada",
            "Sangrias encontradas" => $sangrias->toArray()
        ],
    ]);
}



	private function setUsuario($sangrias){
		for($aux = 0; $aux < count($sangrias); $aux++){
			$sangrias[$aux]['nome_usuario'] = $sangrias[$aux]->usuario->nome;
		}
		return $sangrias;
	}

	public function imprimir($id){
		$sangria = SangriaCaixa::find($id);
		if(valida_objeto($sangria)){

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			$pathLogo = $public.'logos/' . $config->logo;
			$usuario = Usuario::find(get_id_user());

			$cupom = new ComprovanteCaixa("Comprovante de sangria de caixa", $sangria, $pathLogo, $config, $usuario->config ? $usuario->config->impressora_modelo : 80, $usuario->nome);
			$cupom->monta();
			$pdf = $cupom->render();

		// header('Content-Type: application/pdf');
		// echo $pdf;
			return response($pdf)
			->header('Content-Type', 'application/pdf');
		}else{
			return redirect('/403');
		}
	}

}
