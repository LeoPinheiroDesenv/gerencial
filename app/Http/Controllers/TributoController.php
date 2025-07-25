<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tributacao;
use App\Models\Produto;
class TributoController extends Controller
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

	public function index(Request $request){

		$regimes = Tributacao::regimes();
		$tributo = Tributacao::
		where('empresa_id', $request->empresa_id)
		->first();
		return view('tributos/index')
		->with('tributo', $tributo)
		->with('regimes', $regimes)
		->with('title', 'Configurar Tributação');
	}


	public function save(Request $request){
		
		$this->_validate($request);
		if($request->id == 0){
			$result = Tributacao::create([
				'icms' => __replace($request->icms),
				'pis' => __replace($request->pis),
				'cofins' => __replace($request->cofins),
				'ipi' => __replace($request->ipi),
				'perc_ap_cred' => $request->perc_ap_cred ? __replace($request->perc_ap_cred) : 0,
				'regime' => $request->regime,
				'exclusao_icms_pis_cofins' => $request->exclusao_icms_pis_cofins,
				'ncm_padrao' => $request->ncm_padrao ?? '',
				'link_nfse' => $request->link_nfse ?? '',
				'empresa_id' => $request->empresa_id
			]);
		}else{
			$trib = Tributacao::
			where('empresa_id', $request->empresa_id)
			->first();

			if($trib->regime != $request->regime){
				$this->alteraProdutos($request->regime);
			}

			$trib->icms = __replace($request->icms);
			$trib->pis = __replace($request->pis);
			$trib->cofins = __replace($request->cofins);
			$trib->ipi = __replace($request->ipi);
			$trib->perc_ap_cred = __replace($request->perc_ap_cred);
			$trib->regime = $request->regime;
			$trib->exclusao_icms_pis_cofins = $request->exclusao_icms_pis_cofins;
			$trib->ncm_padrao = $request->ncm_padrao;
			$trib->link_nfse = $request->link_nfse ?? '';

			$result = $trib->save();
		}

		if($result){
			session()->flash("mensagem_sucesso", "Configurado com sucesso!");
		}else{
			session()->flash('mensagem_erro', 'Erro ao configurar!');
		}

		return redirect('/tributos');
	}

	private function alteraProdutos($regime){
		$produtos = Produto::where('empresa_id', $this->empresa_id)->get();
		if($regime == 1){
			foreach($produtos as $p){
				if($p->CST_CSOSN == '102'){
					$p->CST_CSOSN = '00';
				}

				if($p->CST_CSOSN == '500'){
					$p->CST_CSOSN = '60';
				}
				$p->save();
			}
		}else{
			foreach($produtos as $p){
				if($p->CST_CSOSN == '00'){
					$p->CST_CSOSN = '102';
				}

				if($p->CST_CSOSN == '60'){
					$p->CST_CSOSN = '500';
				}
				$p->save();
			}
		}
	}

	private function _validate(Request $request){
		$rules = [
			'icms' => 'required',
			'pis' => 'required',
			'cofins' => 'required',
			'ipi' => 'required'
		];

		$messages = [
			'icms.required' => 'O campo ICMS é obrigatório.',
			'pis.required' => 'O campo PIS é obrigatório.',
			'cofins.required' => 'O campo COFINS é obrigatório.',
			'ipi.required' => 'O campo IPI é obrigatório.'
		];
		$this->validate($request, $rules, $messages);
	}
}
