<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IBPT;
use App\Models\Produto;
use App\Models\ConfigNota;
use App\Models\ItemIBTE;
use App\Models\ProdutoIbpt;
use App\Services\IbptService;

class IbptController extends Controller
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
		$ibtes = IBPT::all();
		return view('ibpt/list')
		->with('ibtes', $ibtes)
		->with('title', 'IBPT');
	}

	public function new(){
		$todos = IBPT::estados();
		$estados = [];
		foreach($todos as $uf){
			$res = IBPT::where('uf', $uf)->first();
			if($res == null){
				array_push($estados, $uf);
			}
		}

		return view('ibpt/new')
		->with('estados', $estados)
		->with('title', 'IBPT');
	}

	public function refresh($id){
		$ibpt = IBPT::find($id);
		
		return view('ibpt/new')
		->with('ibpt', $ibpt)
		->with('title', 'IBPT');
	}

	public function importar(Request $request){
		if ($request->hasFile('file')){
			$file = $request->file;
			$handle = fopen($file, "r");
			$row = 0;
			$linhas = [];

			if($request->ibpt_id == 0){
				$result = IBPT::create(
					[
						'uf' => $request->uf,
						'versao' => $request->versao,
					]
				);
			}else{
				$result = IBPT::find($request->ibpt_id);
				$result->versao = $request->versao;
				$result->save();
				ItemIBTE::where('ibte_id', $request->ibpt_id)->delete();
			}

			while ($line = fgetcsv($handle, 1000, ";")) {
				if ($row++ == 0) {
					continue;
				}
				
				$data = [
					'ibte_id' => $result->id,
					'codigo' => $line[0],
					'descricao' => $line[3],
					'nacional_federal' => $line[4],
					'importado_federal' => $line[5],
					'estadual' => $line[6],
					'municipal' => $line[7] 
				];
				ItemIBTE::create($data);

			}
			if($request->ibpt_id > 0){
				session()->flash('mensagem_sucesso', 'Importação atualizada para '.$request->uf);
			}else{
				session()->flash('mensagem_sucesso', 'Importação concluída para '.$request->uf);
			}
			return redirect("/ibpt");


		}else{
			if($request->ibpt_id > 0){
				$result = IBPT::find($request->ibpt_id);
				$result->versao = $request->versao;
				session()->flash('mensagem_sucesso', 'Versão atualizada!');
				$result->save();
			}else{
				session()->flash('mensagem_erro', 'Arquivo inválido!');
			}
			return redirect("/ibpt");
		}
	}

	public function ver($id){
		$ibpt = IBPT::find($id);
		$itens = ItemIBTE::where('ibte_id', $id)->paginate(100);
		return view('ibpt/ver')
		->with('ibpt', $ibpt)
		->with('itens', $itens)
		->with('links', true)
		->with('title', 'IBPT');
	}

	public function atualizaIbpt(){
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$produtos = Produto::where('empresa_id', $this->empresa_id)
		->get();

		$ibptService = new IbptService($config->token_ibpt, preg_replace('/[^0-9]/', '', $config->cnpj));
		$produtosAtualizados = 0;

		foreach($produtos as $p){
			if($p->NCM){
				$data = [
					'ncm' => preg_replace('/[^0-9]/', '', $p->NCM),
					'uf' => $config->UF,
					'extarif' => 0,
					'descricao' => $p->nome,
					'unidadeMedida' => $p->unidade_venda,
					'valor' => number_format(0, $config->casas_decimais),
					'gtin' => $p->codBarras,
					'codigoInterno' => 0
				];	

				$resp = $ibptService->consulta($data);
				if(isset($resp->httpcode)){
					if($resp->httpcode == 403){
						session()->flash('mensagem_erro', $resp->response);
						return redirect("/produtos");
					}
				}
				try{
					if($p->ibpt){
						$ibpt = $p->ibpt;
						$ibpt->codigo = $resp->Codigo;
						$ibpt->uf = $resp->UF;
						$ibpt->descricao = $resp->Descricao;
						$ibpt->nacional = $resp->Nacional;
						$ibpt->estadual = $resp->Estadual;
						$ibpt->importado = $resp->Importado;
						$ibpt->municipal = $resp->Municipal;
						$ibpt->vigencia_inicio = $resp->VigenciaInicio;
						$ibpt->vigencia_fim = $resp->VigenciaFim;
						$ibpt->chave = $resp->Chave;
						$ibpt->versao = $resp->Versao;
						$ibpt->fonte = $resp->Fonte;
						$ibpt->save();
					}else{
						$dataIbpt = [
							'produto_id' => $p->id,
							'codigo' => $resp->Codigo,
							'uf' => $resp->UF, 
							'descricao' => $resp->Descricao,
							'nacional' => $resp->Nacional,
							'estadual' => $resp->Estadual,
							'importado' => $resp->Importado,
							'municipal' => $resp->Municipal,
							'vigencia_inicio' => $resp->VigenciaInicio,
							'vigencia_fim' => $resp->VigenciaFim,
							'chave' => $resp->Chave,
							'versao' => $resp->Versao,
							'fonte' => $resp->Fonte
						];

						ProdutoIbpt::create($dataIbpt);
					}
					$produtosAtualizados++;
				}catch(\Exception $e){
				// echo $e->getMessage();
				// echo "<pre>";
				// print_r($resp);
				// echo "</pre>";

				}
			}
		}

		session()->flash('mensagem_sucesso', 'Produtos atualizados');
		return redirect("/produtos");

	}

	public function atualizaApi(){
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$produtos = Produto::where('empresa_id', $this->empresa_id)
		->get();

		$ibptService = new IbptService($config->token_ibpt, preg_replace('/[^0-9]/', '', $config->cnpj));

		$produtosAtualizados = 0;
		foreach($produtos as $p){
			$data = [
				'ncm' => preg_replace('/[^0-9]/', '', $p->NCM),
				'uf' => $config->UF,
				'extarif' => 0,
				'descricao' => $p->nome,
				'unidadeMedida' => $p->unidade_venda,
				'valor' => number_format(0, $config->casas_decimais),
				'gtin' => $p->codBarras,
				'codigoInterno' => 0
			];	

			$resp = $ibptService->consulta($data);

			if(isset($resp->httpcode)){
				if($resp->httpcode == 403){
					return response()->json($resp->response, 401);
				}
			}

			try{
				if($p->ibpt){
					$ibpt = $p->ibpt;
					$ibpt->codigo = $resp->Codigo;
					$ibpt->uf = $resp->UF;
					$ibpt->descricao = $resp->Descricao;
					$ibpt->nacional = $resp->Nacional;
					$ibpt->estadual = $resp->Estadual;
					$ibpt->importado = $resp->Importado;
					$ibpt->municipal = $resp->Municipal;
					$ibpt->vigencia_inicio = $resp->VigenciaInicio;
					$ibpt->vigencia_fim = $resp->VigenciaFim;
					$ibpt->chave = $resp->Chave;
					$ibpt->versao = $resp->Versao;
					$ibpt->fonte = $resp->Fonte;
					$ibpt->save();
				}else{
					$dataIbpt = [
						'produto_id' => $p->id,
						'codigo' => $resp->Codigo,
						'uf' => $resp->UF, 
						'descricao' => $resp->Descricao,
						'nacional' => $resp->Nacional,
						'estadual' => $resp->Estadual,
						'importado' => $resp->Importado,
						'municipal' => $resp->Municipal,
						'vigencia_inicio' => $resp->VigenciaInicio,
						'vigencia_fim' => $resp->VigenciaFim,
						'chave' => $resp->Chave,
						'versao' => $resp->Versao,
						'fonte' => $resp->Fonte
					];

					ProdutoIbpt::create($dataIbpt);
				}


				$produtosAtualizados++;
			}catch(\Exception $e){
				// echo $e->getMessage();
				// die;

			}
		}
		if($produtosAtualizados == 0){
			return response()->json("Finalizado", 200);
		}
		return response()->json("Produtos atualizados.", 200);
	}

}
