<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompraManual;
use App\Models\ItemCompra;
use App\Models\Compra;
use App\Models\Produto;
use App\Models\ContaPagar;
use App\Models\ConfigNota;
use App\Models\Fornecedor;
use App\Helpers\StockMove;
use Carbon\Carbon;
use App\Models\Transportadora;
use App\Models\CategoriaConta;
use App\Models\Categoria;
use App\Models\Tributacao;
use Illuminate\Support\Facades\DB;

class CompraManualController extends Controller
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

	public function custoMedio(Request $request){
		$item = Produto::findOrFail($request->id);

		$valorCompraAtual = __replace($request->valor);
		$valorCompraProduto = $item->valor_compra;
		$estoque = 0;
		if($item->estoque){
			$estoque = $item->estoque->quantidade;
		}
		$quantidadeDaCompra = __replace($request->quantidade);

		$vl = ($valorCompraAtual*$quantidadeDaCompra) + ($valorCompraProduto*$estoque);
		$vcm = moeda($vl/($estoque+$quantidadeDaCompra));
		return response()->json($vcm, 200);
	}

	public function numeroSequencial(){
		$verify = Compra::where('empresa_id', $this->empresa_id)
		->where('numero_sequencial', 0)
		->first();
		if($verify){
			$vendas = Compra::where('empresa_id', $this->empresa_id)
			->get();

			$n = 1;
			foreach($vendas as $v){
				$v->numero_sequencial = $n;
				$n++;
				$v->save();
			}
		}
	}

	public function index(){
		$this->numeroSequencial();
		$countProdutos = Produto::
		where('empresa_id', $this->empresa_id)
		// ->where('inativo', false)
		->count();

		if($countProdutos > env("ASSINCRONO_PRODUTOS")){
			$view = $this->compraAssincrona();
			return $view;
		}else{
			$fornecedores = Fornecedor::
			where('empresa_id', $this->empresa_id)
			->orderBy('razao_social')->get();

			if(sizeof($fornecedores) == 0){
				session()->flash("mensagem_erro", "Cadastre um fornecedor!");
				return redirect('/fornecedores');
			}

			$produtos = Produto::
			where('empresa_id', $this->empresa_id)
			// ->where('inativo', false)
			->orderBy('nome')
			->get();

			foreach($produtos as $p){
				if($p->grade){
					$p->nome .= " $p->str_grade";
				}
			}

			$transportadoras = Transportadora::
			where('empresa_id', $this->empresa_id)
			->get();

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			$categorias = Categoria::
			where('empresa_id', $this->empresa_id)
			->get();

			$unidadesDeMedida = Produto::unidadesMedida();

			$tributacao = Tributacao::
			where('empresa_id', $this->empresa_id)
			->first();
			$anps = Produto::lista_ANP();

			if($tributacao->regime == 1){
				$listaCSTCSOSN = Produto::listaCST();
			}else{
				$listaCSTCSOSN = Produto::listaCSOSN();
			}

			$listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
			$listaCST_IPI = Produto::listaCST_IPI();

			$natureza = Produto::
			firstNatureza($this->empresa_id);

			return view('compraManual/register')
			->with('compraManual', true)
			->with('fornecedores', $fornecedores)
			->with('config', $config)
			->with('transportadoras', $transportadoras)
			->with('produtos', $produtos)
			->with('categorias', $categorias)
			->with('tributacao', $tributacao)
			->with('anps', $anps)
			->with('listaCSTCSOSN', $listaCSTCSOSN)
			->with('unidadesDeMedida', $unidadesDeMedida)
			->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
			->with('listaCST_IPI', $listaCST_IPI)
			->with('natureza', $natureza)
			->with('title', 'Nova Compra Manual');
		}
	}

	protected function compraAssincrona(){
		$fornecedores = Fornecedor::
		where('empresa_id', $this->empresa_id)
		->orderBy('razao_social')->get();

		if(sizeof($fornecedores) == 0){
			session()->flash("mensagem_erro", "Cadastre um fornecedor!");
			return redirect('/fornecedores');
		}

		$transportadoras = Transportadora::
		where('empresa_id', $this->empresa_id)
		->get();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$categorias = Categoria::
		where('empresa_id', $this->empresa_id)
		->get();

		$unidadesDeMedida = Produto::unidadesMedida();

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first();
		$anps = Produto::lista_ANP();

		if($tributacao->regime == 1){
			$listaCSTCSOSN = Produto::listaCST();
		}else{
			$listaCSTCSOSN = Produto::listaCSOSN();
		}

		$listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
		$listaCST_IPI = Produto::listaCST_IPI();

		$natureza = Produto::
		firstNatureza($this->empresa_id);

		$categoriasDeConta = CategoriaConta::where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->orderBy('nome', 'asc')->get();

		$p = view('compraManual/register_assincrono')
		->with('compraManualAssincrono', true)
		->with('fornecedores', $fornecedores)
		->with('transportadoras', $transportadoras)
		->with('config', $config)
		->with('categoriasDeConta', $categoriasDeConta)
		->with('categorias', $categorias)
		->with('tributacao', $tributacao)
		->with('anps', $anps)
		->with('listaCSTCSOSN', $listaCSTCSOSN)
		->with('unidadesDeMedida', $unidadesDeMedida)
		->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
		->with('listaCST_IPI', $listaCST_IPI)
		->with('natureza', $natureza)
		->with('title', 'Compra Manual');

		return $p;
	}

	public function salvar(Request $request){
		try{
			$result = DB::transaction(function () use ($request) {
				$compra = $request->compra;

				if($compra['qtdVol']){
					$qtdVol = str_replace(",", ".", $compra['qtdVol']);
				}else{
					$qtdVol = 0;
				}

				if($compra['pesoL']){
					$pesoLiquido = str_replace(",", ".", $compra['pesoL']);
				}else{
					$pesoLiquido = 0;
				}

				if($compra['pesoB']){
					$pesoBruto = str_replace(",", ".", $compra['pesoB']);
				}else{
					$pesoBruto = 0;
				}

				$valorFrete = str_replace(",", ".", $compra['valorFrete']);

				$result = Compra::create([
					'fornecedor_id' => $compra['fornecedor'],
					'usuario_id' => get_id_user(),
					'nf' => '0',
					'observacao' => $compra['observacao'] != null ? $compra['observacao'] : '',
					'lote' => $compra['lote'] != null ? $compra['lote'] : '',
					'valor' => str_replace(",", ".", $compra['total']),
					'desconto' => $compra['desconto'] != null ? 
					str_replace(",", ".", $compra['desconto']) : 0,
					'acrescimo' => $compra['acrescimo'] != null ? 
					str_replace(",", ".", $compra['acrescimo']) : 0,
					'xml_path' => '',
					'estado' => 'NOVO',
					'chave' => '',
					'numero_emissao' => 0,
					'empresa_id' => $this->empresa_id,
					'categoria_conta_id' => $compra['categoria_conta_id'] ? $compra['categoria_conta_id'] : null,
					'valor_frete' => $valorFrete ?? 0,
					'placa' => $compra['placaVeiculo'] ?? '',
					'tipo' => (int)$compra['frete'],
					'uf' => $compra['ufPlaca'] ?? '',
					'numeracaoVolumes' => $compra['numeracaoVol'] ?? '0',
					'peso_liquido' => $pesoLiquido ?? 0,
					'peso_bruto' => $pesoBruto ?? 0,
					'especie' => $compra['especie'] ?? '*',
					'qtdVolumes' => $qtdVol ?? 0,
					'transportadora_id' => $compra['transportadora'],
					'filial_id' => $compra['filial_id'] != -1 ? $compra['filial_id'] : null

				]);


				$this->salvarItens($result->id, $compra['itens']);
				if($compra['formaPagamento'] != 'a_vista'){
					$this->salvarParcela($result->id, $compra['fatura'], $compra['fornecedor'], $compra['categoria_conta_id']);
				}
				return $result;
			});

			echo json_encode($result);
		}catch(\Exception $e){
			__saveError($e, $this->empresa_id);
			return response()->json($e->getMessage(), 400);
		}
	}

	public function update(Request $request){
		try{
			$result = DB::transaction(function () use ($request) {
				$compra = $request->compra;

				if($compra['qtdVol']){
					$qtdVol = str_replace(",", ".", $compra['qtdVol']);
				}else{
					$qtdVol = 0;
				}

				if($compra['pesoL']){
					$pesoLiquido = str_replace(",", ".", $compra['pesoL']);
				}else{
					$pesoLiquido = 0;
				}

				if($compra['pesoB']){
					$pesoBruto = str_replace(",", ".", $compra['pesoB']);
				}else{
					$pesoBruto = 0;
				}

				$valorFrete = str_replace(",", ".", $compra['valorFrete']);

				$res = Compra::findOrFail($compra['id']);
				$res->fornecedor_id = $compra['fornecedor_id'];
				$res->nf = '0';
				$res->observacao = $compra['observacao'] != null ? $compra['observacao'] : '';
				$res->valor = str_replace(",", ".", $compra['total']);
				$res->desconto = $compra['desconto'] != null ? 
				str_replace(",", ".", $compra['desconto']) : 0;
				$res->desconto = $compra['acrescimo'] != null ? 
				str_replace(",", ".", $compra['acrescimo']) : 0;
				$res->valor_frete = $valorFrete ?? 0;
				$res->placa = $compra['placaVeiculo'] ?? '';
				$res->tipo = (int)$compra['frete'];
				$res->uf = $compra['ufPlaca'] ?? '';
				$res->numeracaoVolumes = $compra['numeracaoVol'] ?? '0';
				$res->peso_liquido = $pesoLiquido ?? 0;
				$res->peso_bruto = $pesoBruto ?? 0;
				$res->especie = $compra['especie'] ?? '*';
				$res->qtdVolumes = $qtdVol ?? 0;
				$res->transportadora_id = $compra['transportadora'];
				$res->categoria_conta_id = $compra['categoria_conta_id'] ? $compra['categoria_conta_id'] : null;

				$res->itens()->delete();
				$res->fatura()->delete();
				$res->save();

				$this->salvarItens($res->id, $compra['itens']);
				if($compra['formaPagamento'] != 'a_vista'){
					$this->salvarParcela($res->id, $compra['fatura'], $compra['fornecedor_id'], $compra['categoria_conta_id']);
				}
				session()->flash("mensagem_sucesso", "Compra atualizada!");
			});
			echo json_encode($result);
		}catch(\Exception $e){
			__saveError($e, $this->empresa_id);
			return response()->json($e->getMessage(), 400);
		}
	}

	private function salvarItens($id, $itens){
		$stockMove = new StockMove();
		$compra = Compra::findOrFail($id);
	
		foreach($itens as $i){
			// Busca o produto pelo id e pela empresa atual
			$prod = Produto::where('id', (int) $i['codigo'])
					->where('empresa_id', $this->empresa_id)
					->first();
	
					$result = ItemCompra::create([
						'compra_id'        => $id,
						'produto_id'       => (int) $i['codigo'],
						'quantidade'       => str_replace(",", ".", $i['quantidade']),
						'valor_unitario'   => str_replace(",", ".", $i['valor']),
						'unidade_compra'   => $prod['unidade_compra'],
						'percentual_venda' => isset($i['percentual_venda']) ? __replace($i['percentual_venda']) : 0,
						'preco_venda'      => isset($i['preco_venda']) ? __replace($i['preco_venda']) : 0,
					]);					
	
			// Se o item enviar um valor_custo (valor original do custo) atualiza o campo de compra
			if(isset($i['valor_custo'])){
				$vc = __replace($i['valor_custo']);
				if($vc > 0){
					$prod->valor_compra = $vc;
				}
			}
	
			// Se o produto está em reajuste automático, ele calcula o valor_venda a partir do valor_compra e do percentual atual
			if($prod->reajuste_automatico){
				$prod->valor_venda = $prod->valor_compra + (($prod->valor_compra * $prod->percentual_lucro) / 100);
			}
			
			// Agora, caso os dados enviados contenham os novos valores para venda, atualize-os:
			if(isset($i['preco_venda']) && floatval(__replace($i['preco_venda'])) > 0) {
				$prod->valor_venda = __replace($i['preco_venda']);
			}
			if(isset($i['percentual_venda'])) {
				$prod->percentual_lucro = __replace($i['percentual_venda']);
			}
	
			$prod->save();
	
			if($prod->gerenciar_estoque){
				$stockMove->pluStock(
					(int) $i['codigo'], 
					__replace($i['quantidade']) * $prod->conversao_unitaria,
					__replace($i['valor']),
					$compra->filial_id
				);
			}
		}
		return true;
	}
	

	public function salvarParcela($id, $fatura, $fornecedor_id, $categoria_conta_id){
		$cont = 0;
		$valor = 0;
		foreach($fatura as $parcela){
			$cont = $cont+1;
			$valorParcela = str_replace(".", "", $parcela['valor']);
			$valorParcela = str_replace(",", ".", $valorParcela);

			$categoria = CategoriaConta::where('empresa_id', $this->empresa_id)->first();
			if($categoria_conta_id){
				$categoria = CategoriaConta::findOrFail($categoria_conta_id);
			}

			$result = ContaPagar::create([
				'compra_id' => $id,
				'fornecedor_id' => $fornecedor_id,
				'data_vencimento' => $this->parseDate($parcela['data']),
				'data_pagamento' => $this->parseDate($parcela['data']),
				'valor_integral' => $valorParcela,
				'valor_pago' => 0,
				'status' => false,
				'referencia' => "Parcela $cont/" . sizeof($fatura) . " da Compra $id",
				'categoria_id' => $categoria->id,
				'empresa_id' => $this->empresa_id
			]);
		}
		return true;
	}

	private function parseDate($date){
		return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
	}

	public function ultimaCompra($produtoId){
		$item = ItemCompra::
		where('produto_id', $produtoId)
		->orderBy('id', 'desc')
		->get();

		if(count($item) > 0){
			$last = $item[0];
			$r = [
				'fornecedor' => $last->compra->fornecedor->razao_social,
				'valor' => $last->valor_unitario,
				'quantidade' => $last->quantidade,
				'data' => Carbon::parse($last->compra->created_at)->format('d/m/Y H:i:s')
			];
			echo json_encode($r);
		}else{
			echo json_encode(null);
		}
	}

	public function editar($id){

		$compra = Compra::findOrFail($id);
		$countProdutos = Produto::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->count();

		$fornecedores = Fornecedor::
		where('empresa_id', $this->empresa_id)
		->orderBy('razao_social')->get();

		if(sizeof($fornecedores) == 0){
			session()->flash("mensagem_erro", "Cadastre um fornecedor!");
			return redirect('/fornecedores');
		}

		$produtos = Produto::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->orderBy('nome')
		->get();

		foreach($produtos as $p){
			if($p->grade){
				$p->nome .= " $p->str_grade";
			}
		}

		$transportadoras = Transportadora::
		where('empresa_id', $this->empresa_id)
		->get();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$fatura = null;
		if(sizeof($compra->fatura) == 0){
			$fatura = [];
			$temp = [
				'data_vencimento' => \Carbon\Carbon::parse($compra->created_at)->format('Y-m-d'),
				'valor_integral' => $compra->valor
			];

			array_push($fatura, $temp);
		}else{
			$fatura = $compra->fatura;
		}

		$categorias = Categoria::
		where('empresa_id', $this->empresa_id)
		->get();

		$unidadesDeMedida = Produto::unidadesMedida();

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first();
		$anps = Produto::lista_ANP();

		if($tributacao->regime == 1){
			$listaCSTCSOSN = Produto::listaCST();
		}else{
			$listaCSTCSOSN = Produto::listaCSOSN();
		}

		$listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
		$listaCST_IPI = Produto::listaCST_IPI();

		$natureza = Produto::
		firstNatureza($this->empresa_id);

		$categoriasDeConta = CategoriaConta::where('empresa_id', $this->empresa_id)
		->where('tipo', 'pagar')
		->orderBy('nome', 'asc')->get();
		
		return view('compraManual/edit')
		->with('compraManual', true)
		->with('fornecedores', $fornecedores)
		->with('unidadesDeMedida', $unidadesDeMedida)
		->with('tributacao', $tributacao)
		->with('categoriasDeConta', $categoriasDeConta)
		->with('config', $config)
		->with('listaCSTCSOSN', $listaCSTCSOSN)
		->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
		->with('listaCST_IPI', $listaCST_IPI)
		->with('natureza', $natureza)
		->with('anps', $anps)
		->with('fatura', $fatura)
		->with('compra', $compra)
		->with('categorias', $categorias)
		->with('transportadoras', $transportadoras)
		->with('produtos', $produtos)
		->with('title', 'Editar Compra Manual');
		
	}

}
