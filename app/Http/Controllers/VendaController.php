<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\Filial;
use App\Models\VendaCaixa;
use App\Models\NaturezaOperacao;
use App\Models\FormaPagamento;
use App\Models\ItemVenda;
use App\Models\ItemVendaCaixa;
use App\Models\Produto;
use App\Models\Pedido;
use App\Models\Categoria;
use App\Models\Tributacao;
use App\Models\ConfigNota;
use App\Models\Certificado;
use App\Models\CreditoVenda;
use App\Models\ContaReceber;
use App\Models\Transportadora;
use App\Models\Frete;
use App\Models\Cotacao;
use App\Models\TrocaVenda;
use App\Models\CategoriaConta;
use App\Models\Contigencia;
use App\Models\Cliente;
use App\Models\ListaPreco;
use App\Helpers\StockMove;
use App\Services\NFService;
use NFePHP\DA\NFe\Danfe;
use Dompdf\Dompdf;
use App\Models\ComissaoVenda;
use App\Models\Usuario;
use App\Models\Cidade;
use App\Models\AberturaCaixa;
use App\Models\ContaBancaria;
use App\Models\Boleto;
use App\Models\Empresa;
use App\Models\NFeReferecia;
use App\Helpers\BoletoHelper;
use File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Utils\WhatsAppUtil;

class VendaController extends Controller
{
	protected $empresa_id = null;
	protected $util;

	public function __construct(WhatsAppUtil $util){
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

	private function verificaAberturaCaixa(){

		$ab = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->orderBy('id', 'desc')->first();

		$ab2 = AberturaCaixa::where('ultima_venda_nfe', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
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

	public function numeroSequencial(){
		$verify = Venda::where('empresa_id', $this->empresa_id)
		->where('numero_sequencial', 0)
		->first();

		if($verify){
			$vendas = Venda::where('empresa_id', $this->empresa_id)
			->get();

			$n = 1;
			foreach($vendas as $v){
				$v->numero_sequencial = $n;
				$n++;
				$v->save();
			}
		}
	}

	private function getContigencia(){
		$active = Contigencia::
		where('empresa_id', $this->empresa_id)
		->where('status', 1)
		->where('documento', 'NFe')
		->first();
		return $active;
	}

	public function index(){

		$permissaoAcesso = __getLocaisUsarioLogado();
		$local_padrao = __get_local_padrao();

		// echo $local_padrao;
		if($local_padrao <= 0){
			$local_padrao = null;
		}
		$vendas = Venda::
		where('estado', 'DISPONIVEL')
		->where('empresa_id', $this->empresa_id)
		->where('forma_pagamento', '!=', 'conta_crediario')
		->where(function($query) use ($permissaoAcesso){
			if($permissaoAcesso != null){
				foreach ($permissaoAcesso as $value) {
					if($value == -1){
						$value = null;	
					} 
					$query->orWhere('filial_id', $value);
				}
			}
		})->where('filial_id', $local_padrao)
		->orderBy('id', 'desc')
		->paginate(30);

		$this->numeroSequencial();

		$menos30 = $this->menos30Dias();
		$date = date('d/m/Y');

		$certificado = Certificado::
		where('empresa_id', $this->empresa_id)
		->first();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		return view("vendas/list")
		->with('vendas', $vendas)
		->with('config', $config)
		->with('nf', true)
		->with('links', true)
		->with('dataInicial', $menos30)
		->with('dataFinal', $date)
		->with('contigencia', $this->getContigencia())
		->with('certificado', $certificado)
		->with('title', "Lista de Vendas");

	}

	public function detalhesPagamento($id){
		$item = Venda::findOrFail($id);
		return view('vendas.detalhes_pagamento', compact('item'));
	}

	public function nova(){

		// $countProdutos = Produto::
		// where('empresa_id', $this->empresa_id)
		// ->where('inativo', false)
		// ->count();

		// if($countProdutos > 1000){
		$view = $this->vendaAssincrona();
		return $view;
		
		if($countProdutos > 1000 || empresaComFilial()){

			$view = $this->vendaAssincrona();
			return $view;
		}else{

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();
			if($config == null){
				return redirect('configNF');
			}
			$lastNF = Venda::lastNF();

			$naturezas = NaturezaOperacao::
			where('empresa_id', $this->empresa_id)
			->get();

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			$categorias = Categoria::
			where('empresa_id', $this->empresa_id)
			->get();

			$produtos = $this->getProdutosParaVenda();

			$tributacao = Tributacao::
			where('empresa_id', $this->empresa_id)
			->first();

			$produtosAll = Produto::
			where('empresa_id', $this->empresa_id)
			->where('inativo', false)
			->get();

			$clientes = Cliente::
			where('empresa_id', $this->empresa_id)
			->where('inativo', 0)
			->get();

			$tiposPagamento = Venda::tiposPagamento();

			$formasPagamento = FormaPagamento::
			where('empresa_id', $this->empresa_id)
			->where('status', true)
			->get();

			if(count($naturezas) == 0 || count($produtos) == 0 || $config == null || count($categorias) == 0 || $tributacao == null || count($clientes) == 0){

				return view("vendas/alerta")
				->with('produtos', count($produtos))
				->with('categorias', count($categorias))
				->with('clientes', count($clientes))
				->with('naturezas', $naturezas)
				->with('formasPagamento', $formasPagamento)
				->with('config', $config)
				->with('tributacao', $tributacao)
				->with('title', "Validação para Emitir");
				
			}else{

				$transportadoras = Transportadora::
				where('empresa_id', $this->empresa_id)
				->get();

				foreach($clientes as $c){
					$c->cidade;
				}

				foreach($produtos as $p){
					$p->listaPreco;
					$p->estoque;
				}

				foreach($produtosAll as $p){
					$p->listaPreco;
					$p->estoque;
				}

				$abertura = $this->verificaAberturaCaixa();
				if($abertura == -1 && env("CAIXA_PARA_NFE") == 1){
					session()->flash("mensagem_erro", "Abra o caixa para vender!");
					return redirect('/caixa');
				}

				$contaPadrao = ContaBancaria::
				where('empresa_id', $this->empresa_id)
				->where('padrao', true)
				->first();

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

				$usuario = Usuario::find(get_id_user());

				$usuarios = Usuario::where('empresa_id', $this->empresa_id)
				->where('ativo', 1)
				->orderBy('nome', 'asc')
				->get();

				$vendedores = [];
				foreach($usuarios as $u){
					if($u->funcionario){
						array_push($vendedores, $u);
					}
				}

				return view("vendas/register")
				->with('naturezas', $naturezas)
				->with('vendaJs', true)
				->with('config', $config)
				->with('usuario', $usuario)
				->with('vendedores', $vendedores)
				->with('formasPagamento', $formasPagamento)
				->with('listaCSTCSOSN', $listaCSTCSOSN)
				->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
				->with('listaCST_IPI', $listaCST_IPI)
				->with('natureza', $natureza)
				->with('contaPadrao', $contaPadrao)
				->with('clientes', $clientes)
				->with('categorias', $categorias)
				->with('anps', $anps)
				->with('unidadesDeMedida', $unidadesDeMedida)
				->with('tributacao', $tributacao)
				->with('produtos', $produtos)
				->with('produtosAll', $produtosAll)
				->with('transportadoras', $transportadoras)
				->with('tiposPagamento', $tiposPagamento)
				->with('lastNF', $lastNF)
				->with('listaPreco', ListaPreco::where('empresa_id', $this->empresa_id)->get())
				->with('title', "Nova Venda");
			}
		}
	}

	protected function vendaAssincrona(){

		$cotacao = null;
		if(isset(request()->cotacao_id)){
			$cotacao = Cotacao::with('itens')->findOrFail(request()->cotacao_id);
		}
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();
		if($config == null){
			return redirect('configNF');
		}
		$lastNF = Venda::lastNF();

		$naturezas = NaturezaOperacao::
		where('empresa_id', $this->empresa_id)
		->get();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$categorias = Categoria::
		where('empresa_id', $this->empresa_id)
		->get();

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first();

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', 0)
		->count();

		$tiposPagamento = Venda::tiposPagamento();

		if(sizeof($naturezas) == 0 || $config == null || sizeof($categorias) == 0 || $tributacao == null || $clientes == 0){

			$p = view("vendas/alerta")
			->with('categorias', count($categorias))
			->with('clientes', $clientes)
			->with('naturezas', $naturezas)
			->with('produtos', 0)
			->with('config', $config)
			->with('tributacao', $tributacao)
			->with('title', "Validação para Emitir");
			return $p;

		}else{

			$transportadoras = Transportadora::
			where('empresa_id', $this->empresa_id)
			->get();

			$abertura = $this->verificaAberturaCaixa();
			if($abertura == -1 && env("CAIXA_PARA_NFE") == 1){
				session()->flash("mensagem_erro", "Abra o caixa para vender!");
				return redirect('/caixa');
			}

			$contaPadrao = ContaBancaria::
			where('empresa_id', $this->empresa_id)
			->where('padrao', true)
			->first();

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

			$formasPagamento = FormaPagamento::
			where('empresa_id', $this->empresa_id)
			->where('status', true)
			->get();

			$usuario = Usuario::find(get_id_user());

			$usuarios = Usuario::where('empresa_id', $this->empresa_id)
			->where('ativo', 1)
			->orderBy('nome', 'asc')
			->get();

			$vendedores = [];
			foreach($usuarios as $u){
				if($u->funcionario){
					array_push($vendedores, $u);
				}
			}

			$p = view("vendas/register_assincrono")
			->with('naturezas', $naturezas)
			->with('formasPagamento', $formasPagamento)
			->with('vendaJsAssincrono', true)
			->with('config', $config)
			->with('usuario', $usuario)
			->with('vendedores', $vendedores)
			->with('listaCSTCSOSN', $listaCSTCSOSN)
			->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
			->with('listaCST_IPI', $listaCST_IPI)
			->with('natureza', $natureza)
			->with('contaPadrao', $contaPadrao)
			->with('categorias', $categorias)
			->with('anps', $anps)
			->with('cotacao', $cotacao)
			->with('unidadesDeMedida', $unidadesDeMedida)
			->with('tributacao', $tributacao)
			->with('transportadoras', $transportadoras)
			->with('tiposPagamento', $tiposPagamento)
			->with('lastNF', $lastNF)
			->with('listaPreco', ListaPreco::where('empresa_id', $this->empresa_id)->get())
			->with('title', "Nova Venda");

			return $p;
		}

	}

	private function getProdutosParaVenda(){
		$produtos = Produto::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->groupBy('referencia_grade')
		->orderBy('nome')
		->get();

		foreach($produtos as $p){
			if($p->grade){
				$p->nome .= " [grade]"; 
			}
		}
		return $produtos;
	}

	public function detalhar($id){
		$venda = Venda::
		where('id', $id)
		->first();
		if(valida_objeto($venda)){

			$menos30 = $this->menos30Dias();
			$date = date('d/m/Y');

			$value = session('user_logged');
			$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

			return view("vendas/detalhe")
			->with('venda', $venda)
			->with('config', $config)
			->with('adm', $value['adm'])
			->with('title', "Detalhe de Venda $id");
		}else{
			return redirect('/403');
		}
	}

	public function delete($id){
		$venda = Venda::
		where('id', $id)
		->first();
		
		if(valida_objeto($venda)){
			$this->criarLog($venda, 'deletar');

			$comissao = ComissaoVenda::
			where('empresa_id', $this->empresa_id)
			->where('tabela', 'vendas')
			->where('venda_id', $id)
			->first();

			if($comissao != null)
				$comissao->delete();

			if($venda->troca()){
				$venda->troca()->delete();
			}

		// $this->removerDuplicadas($venda);
			$this->reverteEstoque($venda->itens);
			$venda->delete();
			session()->flash("mensagem_sucesso", "Venda removida!");

			return redirect('/vendas');
		}else{
			return redirect('/403');
		}
	}

	private function removerDuplicadas($venda){
		foreach($venda->duplicatas as $dp){
			$c = ContaReceber::
			where('id', $dp->id)
			->delete();
		}
	}

	function sanitizeString($str){
		return preg_replace('{\W}', ' ', preg_replace('{ +}', ' ', strtr(
			utf8_decode(html_entity_decode($str)),
			utf8_decode('ÀÁÃÂÉÊÍÓÕÔÚÜÇÑàáãâéêíóõôúüçñ'),
			'AAAAEEIOOOUUCNaaaaeeiooouucn')));
	}

	private function criarLog($objeto, $tipo = 'criar'){
		if(isset(session('user_logged')['log_id'])){
			$record = [
				'tipo' => $tipo,
				'usuario_log_id' => session('user_logged')['log_id'],
				'tabela' => 'vendas',
				'registro_id' => $objeto->id,
				'empresa_id' => $this->empresa_id
			];
			__saveLog($record);
		}
	}

	public function salvar(Request $request){
		try{
			$result = DB::transaction(function () use ($request) {
				$venda = $request->venda;
				$valorFrete = str_replace(".", "", $venda['valorFrete'] ?? 0);
				$valorFrete = str_replace(",", ".", $valorFrete );
				$vol = $venda['volume'];

				if($vol['pesoL']){
					$pesoLiquido = str_replace(",", ".", $vol['pesoL']);
				}else{
					$pesoLiquido = 0;
				}

				if($vol['pesoB']){
					$pesoBruto = str_replace(",", ".", $vol['pesoB']);
				}else{
					$pesoBruto = 0;
				}

				if($vol['qtdVol']){
					$qtdVol = str_replace(",", ".", $vol['qtdVol']);
				}else{
					$qtdVol = 0;
				}

				$frete = null;
				if($venda['frete'] != '9'){
					$frete = Frete::create([
						'placa' => $venda['placaVeiculo'] ?? '',
						'valor' => $valorFrete ?? 0,
						'tipo' => (int)$venda['frete'],
						'qtdVolumes' => $qtdVol?? 0,
						'uf' => $venda['ufPlaca'] ?? '',
						'numeracaoVolumes' => $vol['numeracaoVol'] ?? '0',
						'especie' => $vol['especie'] ?? '*',
						'peso_liquido' => $pesoLiquido ?? 0,
						'peso_bruto' => $pesoBruto ?? 0
					]);
				}

				$totalVenda = str_replace(",", ".", $venda['total']);

				$desconto = 0;
				if($venda['desconto']){
					$desconto = str_replace(".", "", $venda['desconto']);
					$desconto = str_replace(",", ".", $desconto);
				}

				$acrescimo = 0;
				if($venda['acrescimo']){
					$acrescimo = str_replace(".", "", $venda['acrescimo']);
					$acrescimo = str_replace(",", ".", $acrescimo);
				}

				$numero_sequencial = 0;
				$last = Venda::where('empresa_id', $this->empresa_id)
				->orderBy('id', 'desc')
				->first();

				$vendedor_id = $venda['vendedor_id'];
				if(!$vendedor_id){
					$vendedor_id = get_id_user();
				}

				$numero_sequencial = $last != null ? ($last->numero_sequencial + 1) : 1;
				$natureza = NaturezaOperacao::findOrFail($venda['naturezaOp']);
				$result = Venda::create([
					'cliente_id' => $venda['cliente'],
					'transportadora_id' => $venda['transportadora'],
					'forma_pagamento' => $venda['formaPagamento'],
					'tipo_pagamento' => $venda['tipoPagamento'],
					'usuario_id' => get_id_user(),
					'valor_total' => $totalVenda,
					'desconto' => $desconto,
					'acrescimo' => $acrescimo,
					'frete_id' => $frete != null ? $frete->id : null,
					'NfNumero' => 0,
					'natureza_id' => $venda['naturezaOp'],
					'path_xml' => '',
					'chave' => '',
					'sequencia_cce' => 0,
					'observacao' => $venda['observacao'] ?? '',
					'data_entrega' => $venda['data_entrega'] != '' ? $this->parseDate($venda['data_entrega']) : null,
					'data_retroativa' => $venda['data_retroativa'] != '' ? $this->parseDate($venda['data_retroativa']) : null,
					'data_saida' => $venda['data_saida'] != '' ? $this->parseDate($venda['data_saida']) : null,
					'estado' => 'DISPONIVEL',
					'empresa_id' => $this->empresa_id,
					'bandeira_cartao' => $venda['bandeira_cartao'],
					'cAut_cartao' => $venda['cAut_cartao'] ?? '',
					'cnpj_cartao' => $venda['cnpj_cartao'] ?? '',
					'descricao_pag_outros' => $venda['descricao_pag_outros'] ?? '',
					'credito_troca' => $venda['credito_troca'] ? $desconto : 0,
					'vendedor_id' => $vendedor_id,
					'numero_sequencial' => $numero_sequencial,
					'filial_id' => $venda['filial_id'] != -1 ? $venda['filial_id'] : null
				]);

				if($venda['credito_troca']){
					$this->recalcularCredito($desconto, $venda['cliente']);
				}

				if($venda['formaPagamento'] == 'conta_crediario'){ 
					$credito = CreditoVenda::create([
						'venda_id' => $result->id,
						'cliente_id' => $venda['cliente'],
						'status' => false,	
						'empresa_id' => $this->empresa_id
					]);
				}

				$itens = $venda['itens'];
				$referencias = $venda['referencias'] ?? [];
				$stockMove = new StockMove();

				$cliente = Cliente::find($venda['cliente']);

				$config = ConfigNota::
				where('empresa_id', $this->empresa_id)
				->first();

				if($venda['data_venda'] != ''){
					$v = Venda::findOrFail($result->id);
					$dataVenda = $this->parseDate($venda['data_venda']) . " " . date('H:i:s');
					$v->created_at = $dataVenda;
					$v->save();
				}

				foreach ($itens as $i) {
					$produto = Produto::find($i['codigo']);
					$cfop = 0;

					if($natureza->sobrescreve_cfop){
						if($config->UF != $cliente->cidade->uf){
							$cfop = $natureza->CFOP_saida_inter_estadual;
						}else{
							$cfop = $natureza->CFOP_saida_estadual;
						}
					}else{
						if($config->UF != $cliente->cidade->uf){
							$cfop = $produto->CFOP_saida_inter_estadual;
						}else{
							$cfop = $produto->CFOP_saida_estadual;
						}
					}

					ItemVenda::create([
						'venda_id' => $result->id,
						'produto_id' => (int) $i['codigo'],
						'produto_nome' => isset($i['nome']) ? $i['nome'] : null,
						'quantidade' => (float) str_replace(",", ".", $i['quantidade']),
						'quantidade_dimensao' => (float) str_replace(",", ".", $i['quantidade_dim']),
						'valor' => (float) str_replace(",", ".", $i['valor']),
						'cfop' => $cfop ?? 0,
						'altura' => $i['altura'],
						'largura' => $i['largura'],
						'profundidade' => $i['profundidade'],
						'esquerda' => $i['esquerda'],
						'direita' => $i['direita'],
						'superior' => $i['superior'],
						'inferior' => $i['inferior'],
						'atacado' => isset($i['item_atacado']) ? $i['item_atacado'] : 0,
						'x_pedido' => isset($i['x_pedido']) ? $i['x_pedido'] : "",
						'num_item_pedido' => isset($i['num_item_pedido']) ? $i['num_item_pedido'] : "",
						'valor_custo' => $produto->valor_compra
					]);

					$prod = Produto::where('id', $i['codigo'])
					->first();

					if($natureza->nao_movimenta_estoque == false && $prod->gerenciar_estoque){
						if(!empty($prod->receita)){
							$receita = $prod->receita; 
							foreach($receita->itens as $rec){

								if(!empty($rec->produto->receita)){ 
									$receita2 = $rec->produto->receita; 

									foreach($receita2->itens as $rec2){
										$stockMove->downStock(
											$rec2->produto_id, 
											(float) str_replace(",", ".", $i['quantidade']) * 
											($rec2->quantidade/$receita2->rendimento),
											$venda['filial_id']
										);
									}
								}else{

									$stockMove->downStock(
										$rec->produto_id, 
										(float) str_replace(",", ".", $i['quantidade']) * 
										($rec->quantidade/$receita->rendimento),
										$venda['filial_id']
									);
								}
							}
						}else{
							$stockMove->downStock(
								(int) $i['codigo'], (float) str_replace(",", ".", $i['quantidade']), $venda['filial_id']);
						}
					}
				}

				if(sizeof($referencias) > 0){
					foreach($referencias as $r){
						NFeReferecia::create([
							'venda_id' => $result->id,
							'chave' => $r
						]);
					}
				}

				if(isset($venda['receberContas'])){
					$receberContas = $venda['receberContas'];
					foreach($receberContas as $r){ 
						$c = CreditoVenda::where('id', $r)
						->first();
						$c->status = true;
						$c->save();
					}
				}

				$mensagem = [];
				$fatura = $venda['fatura'] ?? [];
				
				if($venda['tipoPagamento'] == '06'){

					if($natureza->categoria_conta_id == null){
						$catCrediario = $this->categoriaCrediario();
					}else{
						$catCrediario = CategoriaConta::findOrFail($natureza->categoria_conta_id)->id;
					}

					foreach ($fatura as $key => $f) {
						$valorParcela = str_replace(",", ".", $f['valor']);
						$resultFatura = ContaReceber::create([
							'venda_id' => $result->id,
							'data_vencimento' => $this->parseDate($f['data']),
							'data_recebimento' => $this->parseDate($f['data']),
							'valor_integral' => $valorParcela,
							'cliente_id' => $venda['cliente'],
							'valor_recebido' => 0,
							'status' => false,
							'entrada' => $f['entrada'],
							'tipo_pagamento' => 'Crediário',
							'referencia' => "Parcela ".$f['numero']."/" . sizeof($fatura) .", da Venda " . $result->id,
							'categoria_id' => $catCrediario,
							'empresa_id' => $this->empresa_id
						]);
					}

				}elseif($venda['formaPagamento'] != 'a_vista' && $venda['formaPagamento'] != 'conta_crediario'){

					$gerarBoleto = isset($venda['gerar_boleto']);
					$contaPadrao = ContaBancaria::
					where('empresa_id', $this->empresa_id)
					->where('padrao', true)
					->first();

					foreach ($fatura as $key => $f) {
						$valorParcela = str_replace(",", ".", $f['valor']);
						if($natureza->categoria_conta_id == null){
							$catVenda = $this->categoriaVenda();
						}else{
							$catVenda = CategoriaConta::findOrFail($natureza->categoria_conta_id)->id;
						}

						$resultFatura = ContaReceber::create([
							'venda_id' => $result->id,
							'data_vencimento' => $this->parseDate($f['data']),
							'data_recebimento' => $this->parseDate($f['data']),
							'valor_integral' => $valorParcela,
							'cliente_id' => $venda['cliente'],
							'valor_recebido' => 0,
							'tipo_pagamento' => $f['tipo'],
							'status' => false,
							'entrada' => $f['entrada'],
							'referencia' => "Parcela ".$f['numero']."/" . sizeof($fatura) .", da Venda " . $result->id,
							'categoria_id' => $catVenda,
							'empresa_id' => $this->empresa_id
						]);

						if ($gerarBoleto) {
							if ($contaPadrao != null) {
								foreach ($resultFaturas as $key => $resultFatura) { // Iterar pelas parcelas
									$data = [
										'banco_id' => $contaPadrao->id,
										'conta_id' => $resultFatura->id, // Associar corretamente cada fatura
										'numero' => $key . $result->id,
										'numero_documento' => $result->id,
										'carteira' => $contaPadrao->carteira,
										'convenio' => $contaPadrao->convenio,
										'linha_digitavel' => '',
										'nome_arquivo' => '',
										'juros' => $contaPadrao->juros,
										'multa' => $contaPadrao->multa,
										'juros_apos' => $contaPadrao->juros_apos,
										'instrucoes' => "",
										'logo' => $contaPadrao->usar_logo ? true : false,
										'tipo' => $contaPadrao->tipo,
										'codigo_cliente' => rand(0, 100),
										'posto' => $request->posto ?? 1,
									];
						
									$boleto = Boleto::create($data);
									$empresa = Empresa::find($this->empresa_id);
						
									$boletoHelper = new BoletoHelper($empresa);
									$resultBoleto = $boletoHelper->gerar($boleto);
						
									if (isset($resultBoleto['erro'])) {
										array_push($mensagem, "Erro ao gerar boleto para parcela {$key}: " . $resultBoleto['mensagem']);
									} else {
										// Registrar o boleto gerado no contas a receber (atualizar as informações da parcela)
										ContaReceber::where('id', $resultFatura->id)->update([
											'boleto_id' => $boleto->id,
											'linha_digitavel' => $boleto->linha_digitavel,
										]);
									}
								}
						
								return response()->json([
									'status' => 'sucesso',
									'id' => $result->id,
									'boletos' => $resultFaturas->map(function ($fatura) use ($boleto) {
										return [
											'boleto_id' => $boleto->id,
											'nome_arquivo' => $boleto->nome_arquivo,
										];
									}), // Retorna os boletos gerados
								]);
							} else {
								array_push($mensagem, "Erro ao gerar boletos: conta padrão não definida.");
							}
						}										
						
					}
				}

				if(isset($venda['cotacao_id']) && $venda['cotacao_id']){
					$cotacao = Cotacao::find($venda['cotacao_id']);
					$cotacao->venda_id = $result->id;
					$cotacao->save();
				}

				$usuario = Usuario::find(get_id_user());
				$vTemp = Venda::find($result->id);

				$this->criarLog($vTemp);
				if($venda['vendedor_id']){
					$usr = Usuario::find($venda['vendedor_id']);
					if($usr->funcionario){
						$percentual_comissao = $usr->funcionario->percentual_comissao;

						// $valorComissao = (($totalVenda-$desconto+$acrescimo) * $percentual_comissao) / 100;
						$valorComissao = $this->calcularComissaoVenda($vTemp, $percentual_comissao);
						if($valorComissao > 0){
							ComissaoVenda::create(
								[
									'funcionario_id' => $usr->funcionario->id,
									'venda_id' => $result->id,
									'tabela' => 'vendas',
									'valor' => $valorComissao,
									'status' => 0,
									'empresa_id' => $this->empresa_id
								]
							);
						}
					}
				}else{
					if($usuario->funcionario){
						$percentual_comissao = $usuario->funcionario->percentual_comissao;

						$valorComissao = $this->calcularComissaoVenda($vTemp, $percentual_comissao);
						if($valorComissao > 0){
							ComissaoVenda::create(
								[
									'funcionario_id' => $usuario->funcionario->id,
									'venda_id' => $result->id,
									'tabela' => 'vendas',
									'valor' => $valorComissao,
									'status' => 0,
									'empresa_id' => $this->empresa_id
								]
							);
						}
					}
				}
				if(sizeof($mensagem) == 0){
					return $result;
				}else{
					return $mensagem;
				}
			});


return response()->json($result, 200);

}catch(\Exception $e){
	// __saveError($e, $this->empresa_id);
	return response()->json($e->getMessage(), 400);
}
}

private function recalcularCredito($valor_utilizado, $cliente_id){
	$creditos = TrocaVenda::
	where('empresa_id', $this->empresa_id)
	->where('cliente_id', $cliente_id)
	->where('status', 0)
	->orderBy('id', 'desc')
	->get();

	$tempSoma = 0;
	foreach($creditos as $c){
		$tempSoma += $c->valor_credito;
		$c->status = 1;
		$c->save();
	}

	if($tempSoma > $valor_utilizado){
		$dif = $tempSoma - $valor_utilizado;
		$cr = $creditos[sizeof($creditos)-1];

		$cr->status = 0;
		$cr->valor_credito = $dif;
		$cr->save();
	}
}

private function categoriaCrediario(){
	$cat = CategoriaConta::
	where('empresa_id', $this->empresa_id)
	->where('nome', 'Crediário')
	->first();
	if($cat != null) return $cat->id;
	$cat = CategoriaConta::create([
		'nome' => 'Crediário',
		'empresa_id' => $this->empresa_id,
		'tipo'=> 'receber'
	]);
	return $cat->id;
}

private function categoriaVenda(){
	$cat = CategoriaConta::
	where('empresa_id', $this->empresa_id)
	->where('nome', 'Vendas')
	->first();
	if($cat != null) return $cat->id;
	$cat = CategoriaConta::create([
		'nome' => 'Vendas',
		'empresa_id' => $this->empresa_id,
		'tipo'=> 'receber'
	]);
	return $cat->id;
}

private function calcularComissaoVenda($venda, $percentual_comissao){
	$valorRetorno = 0;
	foreach($venda->itens as $i){
		if($i->produto->perc_comissao > 0){
			$valorRetorno += (($i->valor*$i->quantidade) * $i->produto->perc_comissao) / 100;
		}
	}
	if($valorRetorno == 0){
		$valorRetorno = (($venda->valor_total-$venda->desconto+$venda->acrescimo) * $percentual_comissao) / 100;
	}
	return $valorRetorno;
}

public function atualizar(Request $request){
	try{
		$result = DB::transaction(function () use ($request) {
			$request = $request->venda;
			$venda_id = $request['venda_id'];
			$venda = $vendaAnterior = Venda::find($venda_id);

			$valorFrete = str_replace(".", "", $request['valorFrete'] ?? 0);
			$valorFrete = str_replace(",", ".", $valorFrete );

			$vol = $request['volume'];

			if($vol['pesoL']){
				$pesoLiquido = $vol['pesoL'];
				$pesoLiquido = str_replace(",", ".", $pesoLiquido);
			}else{
				$pesoLiquido = 0;
			}

			if($vol['pesoB']){
				$pesoBruto = $vol['pesoB'];
				$pesoBruto = str_replace(",", ".", $pesoBruto);
			}else{
				$pesoBruto = 0;
			}

			if($vol['qtdVol']){
				$qtdVol = str_replace(".", "", $vol['qtdVol']);
				$qtdVol = str_replace(",", ".", $qtdVol);
			}else{
				$qtdVol = 0;
			}

			$frete = null;
			if($request['frete'] != '9'){
				$frete = Frete::create([
					'placa' => $request['placaVeiculo'] ?? '',
					'valor' => $valorFrete ?? 0,
					'tipo' => (int)$request['frete'],
					'qtdVolumes' => $qtdVol ?? 0,
					'uf' => $request['ufPlaca'],
					'numeracaoVolumes' => $vol['numeracaoVol'] ?? '0',
					'especie' => $vol['especie'] ?? '*',
					'peso_liquido' => $pesoLiquido ?? 0,
					'peso_bruto' => $pesoBruto ?? 0
				]);
			}

			$totalVenda = str_replace(",", ".", $request['total']);

			$desconto = 0;
			if($request['desconto']){
				$desconto = str_replace(".", "", $request['desconto']);
				$desconto = str_replace(",", ".", $desconto);
			}

			$acrescimo = 0;
			if($request['acrescimo']){
				$acrescimo = str_replace(".", "", $request['acrescimo']);
				$acrescimo = str_replace(",", ".", $acrescimo);
			}

			$fatura = $request['fatura'];

			$venda->transportadora_id = $request['transportadora'];
			$venda->cliente_id = $request['cliente'];
			$venda->forma_pagamento = $request['formaPagamento'];

			$tipo_pagamento = Venda::getTipoPagamentoNFe($fatura[0]['tipo']);
			$venda->tipo_pagamento = $tipo_pagamento;
			$venda->data_entrega = $request['data_entrega'] != '' ? $this->parseDate($request['data_entrega']) : null;
			$venda->data_retroativa = $request['data_retroativa'] != '' ? $this->parseDate($request['data_retroativa']) : null;
			$venda->data_saida = $request['data_saida'] != '' ? $this->parseDate($request['data_saida']) : null;
			$venda->usuario_id = get_id_user();
			$venda->valor_total = $totalVenda;
			$venda->desconto = $desconto;
			$venda->acrescimo = $acrescimo;
			$venda->frete_id = $frete != null ? $frete->id : null;
			$venda->NfNumero = 0;
			$venda->natureza_id = $request['naturezaOp'];
			$venda->observacao = $request['observacao'] ?? '';
			$venda->filial_id = $request['filial_id'] != -1 ? $request['filial_id'] : null;

			$vendedor_id = $request['vendedor_id'];
			if(!$vendedor_id){
				$vendedor_id = get_id_user();
			}
			$venda->vendedor_id = $vendedor_id;

			if($request['data_venda'] != ''){
				$dataVenda = $this->parseDate($request['data_venda']) . " " . date('H:i:s');
				$venda->created_at = $dataVenda;
			}

			$venda->save();
			$itens = $request['itens'];
			$referencias = $request['referencias'] ?? [];
			$this->reverteEstoque($venda->itens);
			$this->deleteItens($venda);
			$stockMove = new StockMove();

			$natureza = NaturezaOperacao::find($request['naturezaOp']);
			$cliente = Cliente::find($vendaAnterior->cliente_id);

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			foreach ($itens as $i) {
				$produto = Produto::find($i['codigo']);
				$cfop = 0;

				if($natureza->sobrescreve_cfop){
					if($config->UF != $cliente->cidade->uf){
						$cfop = $natureza->CFOP_saida_inter_estadual;
					}else{
						$cfop = $natureza->CFOP_saida_estadual;
					}
				}else{
					if($config->UF != $cliente->cidade->uf){
						$cfop = $produto->CFOP_saida_inter_estadual;
					}else{
						$cfop = $produto->CFOP_saida_estadual;
					}
				}

				ItemVenda::create([
					'venda_id' => $venda->id,
					'produto_id' => (int) $i['codigo'],
					'produto_nome' => isset($i['nome']) ? $i['nome'] : null,
					'quantidade' => (float) str_replace(",", ".", $i['quantidade']),
					'valor' => (float) str_replace(",", ".", $i['valor']),
					'cfop' => $cfop,
					'altura' => $i['altura'],
					'largura' =>  $i['largura'],
					'profundidade' => $i['profundidade'],
					'esquerda' =>  $i['esquerda'],
					'direita' =>  $i['direita'],
					'superior' =>  $i['superior'],
					'inferior' =>  $i['inferior'],
					'x_pedido' => isset($i['x_pedido']) ? $i['x_pedido'] : "",
					'num_item_pedido' => isset($i['num_item_pedido']) ? $i['num_item_pedido'] : "",
					'valor_custo' => $produto->valor_compra
				]);

				$prod = Produto
				::where('id', $i['codigo'])
				->first();

				if(!empty($prod->receita)){
				//baixa por receita
					$receita = $prod->receita; 
					foreach($receita->itens as $rec){

						if(!empty($rec->produto->receita)){ // se item da receita for receita
							$receita2 = $rec->produto->receita; 

							foreach($receita2->itens as $rec2){
								$stockMove->downStock(
									$rec2->produto_id, 
									(float) str_replace(",", ".", $i['quantidade']) * 
									($rec2->quantidade/$receita2->rendimento),
									$venda['filial_id']
								);
							}
						}else{

							$stockMove->downStock(
								$rec->produto_id, 
								(float) str_replace(",", ".", $i['quantidade']) * 
								($rec->quantidade/$receita->rendimento),
								$venda['filial_id']
							);
						}
					}
				}else{
					if($prod->gerenciar_estoque){
						$stockMove->downStock(
							(int) $i['codigo'], (float) str_replace(",", ".", $i['quantidade']),
							$venda['filial_id']);
					}
				}
			}

			$this->deleteChaves($venda);

			if(sizeof($referencias) > 0){
				foreach($referencias as $r){
					NFeReferecia::create([
						'venda_id' => $venda->id,
						'chave' => $r
					]);
				}
			}
			$this->criarLog($venda, 'atualizar');
			$this->deleteFatura($venda);
			$resultFatura = null;
			if($request['formaPagamento'] != 'a_vista' && $request['formaPagamento'] != 'conta_crediario'){
				$fatura = $request['fatura'];

				foreach ($fatura as $f) {
					$valorParcela = str_replace(",", ".", $f['valor']);

					if($natureza->categoria_conta_id == null){
						$cat = $this->categoriaVenda();
					}else{
						$cat = CategoriaConta::findOrFail($natureza->categoria_conta_id)->id;
					}
					$resultFatura = ContaReceber::create([
						'venda_id' => $venda->id,
						'data_vencimento' => $this->parseDate($f['data']),
						'data_recebimento' => $this->parseDate($f['data']),
						'valor_integral' => $valorParcela,
						'valor_recebido' => 0,
						'status' => false,
						'cliente_id' => $venda->cliente_id,
						'entrada' => $f['entrada'],
					// 'tipo_pagamento' => Venda::getTipo($request['tipoPagamento']),
						'tipo_pagamento' => $f['tipo'],
						'referencia' => "Parcela ".$f['numero']."/" . sizeof($fatura) .", da Venda " . $venda->id,
						'categoria_id' => $cat,
						'empresa_id' => $this->empresa_id
					]);
				}
			}

			$vTemp = Venda::find($venda->id);

			if($request['vendedor_id']){
				$comissao = ComissaoVenda::where('venda_id', $venda->id)->first();
				if($comissao){
					$comissao->delete();
				}
				$usr = Usuario::find($request['vendedor_id']);
				if($usr->funcionario){
					$percentual_comissao = $usr->funcionario->percentual_comissao;

					// $valorComissao = (($totalVenda-$desconto+$acrescimo) * $percentual_comissao) / 100;
					$valorComissao = $this->calcularComissaoVenda($vTemp, $percentual_comissao);
					if($valorComissao > 0){
						ComissaoVenda::create(
							[
								'funcionario_id' => $usr->funcionario->id,
								'venda_id' => $venda->id,
								'tabela' => 'vendas',
								'valor' => $valorComissao,
								'status' => 0,
								'empresa_id' => $this->empresa_id
							]
						);
					}
				}
			}else{
				$comissao = ComissaoVenda::where('venda_id', $venda->id)->first();
				if($comissao){
					$comissao->delete();
				}
				$usuario = Usuario::find(get_id_user());
				
				if($usuario->funcionario){
					$percentual_comissao = $usuario->funcionario->percentual_comissao;

					$valorComissao = $this->calcularComissaoVenda($vTemp, $percentual_comissao);
					if($valorComissao > 0){
						ComissaoVenda::create(
							[
								'funcionario_id' => $usuario->funcionario->id,
								'venda_id' => $venda->id,
								'tabela' => 'vendas',
								'valor' => $valorComissao,
								'status' => 0,
								'empresa_id' => $this->empresa_id
							]
						);
					}
				}
			}
			

			return json_encode($resultFatura);
		});
return response()->json($result, 200);
}catch(\Exception $e){
	__saveError($e, $this->empresa_id);
	return response()->json($e->getMessage(), 400);
}

}

private function reverteEstoque($itens){
	$stockMove = new StockMove();
	foreach($itens as $i){
		if($i->produto->gerenciar_estoque){
			if(!empty($i->produto->receita)){
				$receita = $i->produto->receita; 
				foreach($receita->itens as $rec){

					if(!empty($rec->produto->receita)){
						$receita2 = $rec->produto->receita; 
						foreach($receita2->itens as $rec2){
							$stockMove->pluStock(
								$rec2->produto_id, 
								(float) str_replace(",", ".", $i->quantidade) * 
								($rec2->quantidade/$receita2->rendimento),
								-1,
								$itens[0]->venda->filial_id
							);
						}
					}else{

						$stockMove->pluStock(
							$rec->produto_id, 
							(float) str_replace(",", ".", $i->quantidade) * 
							($rec->quantidade/$receita->rendimento),
							-1,
							$itens[0]->venda->filial_id
						);
					}
				}
			}else{
				$stockMove->pluStock(
					$i->produto_id, (float) str_replace(",", ".", $i->quantidade),
					-1,
					$itens[0]->venda->filial_id);
			}
		}
	}
}

private function deleteItens($venda){
	ItemVenda::where('venda_id', $venda->id)->delete();
}

private function deleteChaves($venda){
	NFeReferecia::where('venda_id', $venda->id)->delete();
}

private function deleteFatura($venda){
	ContaReceber::where('venda_id', $venda->id)->delete();
}

public function salvarCrediario(Request $request){
	$venda = $request->venda;
	$valorFrete = 0;

	$totalVenda = str_replace(",", ".", $venda['valor_total']);

	$desconto = 0;
	$acrescimo = 0;

	$result = Venda::create([
		'cliente_id' => $venda['cliente'],
		'transportadora_id' => null,
		'forma_pagamento' => 'conta_crediario',
		'tipo_pagamento' => '05',
		'usuario_id' => get_id_user(),
		'valor_total' => $totalVenda,
		'desconto' => $desconto,
		'acrescimo' => $acrescimo,
		'frete_id' => null,
		'NfNumero' => 0,
		'natureza_id' => 1,  
		'path_xml' => '',
		'chave' => '',
		'sequencia_cce' => 0,
		'observacao' => '',
		'estado' => 'DISPONIVEL',
		'empresa_id' => $this->empresa_id,
		'filial_id' => $venda['filial_id'] != -1 ? $venda['filial_id'] : null
	]);


	$credito = CreditoVenda::create([
		'venda_id' => $result->id,
		'cliente_id' => $venda['cliente'],
		'status' => false,	
		'empresa_id' => $this->empresa_id
	]);

	if($venda['codigo_comanda'] > 0){
		$pedido = Pedido::
		where('comanda', $venda['codigo_comanda'])
		->where('status', 0)
		->where('desativado', 0)
		->first();

		$pedido->status = 1;
		$pedido->desativado = 1;
		$pedido->save();
	}


	$itens = $venda['itens'];
	$stockMove = new StockMove();
	foreach ($itens as $i) {
		$pTemp = Produto::find((int) $i['id']);
		ItemVenda::create([
			'venda_id' => $result->id,
			'produto_id' => (int) $i['id'],
			'quantidade' => (float) str_replace(",", ".", $i['quantidade']),
			'valor' => (float) str_replace(",", ".", $i['valor']),
			'valor_custo' => $pTemp->valor_compra
		]);
		$stockMove->downStock(
			(int) $i['id'], (float) str_replace(",", ".", $i['quantidade']),
			$venda['filial_id']);
	}

	echo json_encode($result);
}

private function menos30Dias(){
	return date('d/m/Y', strtotime("-30 days",strtotime(str_replace("/", "-", 
		date('Y-m-d')))));
}

private function parseDate($date, $plusDay = false){
	if($plusDay == false)
		return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
	else
		return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
}

public function filtro(Request $request){

	$dataInicial = $request->data_inicial;
	$dataFinal = $request->data_final;
	$dataEmissao = $request->data_emissao;
	$cliente = $request->cliente;
	$estado = $request->estado;
	$numero_nfe = $request->numero_nfe;
	$numero_doc = $request->numero_doc;
	$filial_id = $request->filial_id;

	$vendas = null;

	$permissaoAcesso = __getLocaisUsarioLogado();

	$vendas = Venda::
	where('vendas.empresa_id', $this->empresa_id)
	->where(function($query) use ($permissaoAcesso){
		if($permissaoAcesso != null){
			foreach ($permissaoAcesso as $value) {
				if($value == -1){
					$value = null;	
				} 
				$query->orWhere('vendas.filial_id', $value);
			}
		}
	})
	->orderBy('vendas.id', 'desc')
	->select('vendas.*');

	if(isset($dataInicial) && isset($dataFinal)){
		$vendas->whereBetween('vendas.'.$request->tipo_pesquisa_data, [
			$this->parseDate($dataInicial), 
			$this->parseDate($dataFinal, true)
		]);
	}

	if(($dataEmissao)){
		$vendas->whereDate('data_emissao', $this->parseDate($dataEmissao));
	}

	if(isset($cliente)){
		$vendas->join('clientes', 'clientes.id' , '=', 'vendas.cliente_id')
		->where('clientes.'.$request->tipo_pesquisa, 'LIKE', "%$cliente%");
	}

	if($numero_nfe != ""){
		$vendas->where('NfNumero', $numero_nfe);
	}
	if($numero_doc != ""){
		$vendas->where('numero_sequencial', $numero_doc);
	}

	if($filial_id){
		if($filial_id == -1){
			$vendas->where('filial_id', null);
		}else{
			$vendas->where('filial_id', $filial_id);
		}
	}

	if($estado != "TODOS"){
		$vendas->where('estado', $estado);
	}

	$vendas = $vendas->get();

	$certificado = Certificado::
	where('empresa_id', $this->empresa_id)
	->first();


	return view("vendas/list")
	->with('vendas', $vendas)
	->with('nf', true)
	->with('contigencia', $this->getContigencia())
	->with('cliente', $cliente)
	->with('tipoPesquisa', $request->tipo_pesquisa)
	->with('tipoPesquisaData', $request->tipo_pesquisa_data)
	->with('certificado', $certificado)
	->with('dataInicial', $dataInicial)
	->with('dataFinal', $dataFinal)
	->with('dataEmissao', $dataEmissao)
	->with('numero_doc', $numero_doc)
	->with('numero_nfe', $numero_nfe)
	->with('filial_id', $filial_id)
	->with('estado', $estado)

	->with('title', "Filtro de Vendas");
}

public function rederizarDanfe($id){
	$venda = Venda::find($id);
	if(valida_objeto($venda)){

		$isFilial = $venda->filial_id;
		if($venda->filial_id == null){
			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();
		}else{
			$config = Filial::findOrFail($venda->filial_id);
			if($config->arquivo_certificado == null){
				echo "Necessário o certificado para realizar esta ação!";
				die;
			}
		}

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		$nfe_service = new NFService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id,
			"is_filial" => $isFilial
		]);
		$nfe = $nfe_service->gerarNFe($id);
		$signed = $nfe_service->sign($nfe['xml']);
		if(!isset($nfe['erros_xml'])){
			$xml = $nfe['xml'];

			$public = env('SERVIDOR_WEB') ? 'public/' : '';

			if($config->logo){
				$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
			}else{
				$logo = null;
			}

			try {
				$danfe = new Danfe($xml);
					// $id = $danfe->monta();
				$danfe->setVUnComCasasDec($config->casas_decimais);

				$pdf = $danfe->render();
				header("Content-Disposition: ; filename=DANFE Temporária.pdf");
				return response($pdf)
				->header('Content-Type', 'application/pdf');
			} catch (InvalidArgumentException $e) {
				echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
			} 
		} else{
			foreach($nfe['erros_xml'] as $e) {
				echo $e;
			}
		}
	}else{
		return redirect('/403');
	}
}

public function find($id){
	$item = Venda::with('cliente')->findOrFail($id);
	return response()->json($item, 200);
}

public function enviarWhats(Request $request){
	$item = Venda::findOrFail($request->venda_id);

	$numero = preg_replace('/[^0-9]/', '', $request->celular);
	$configNota = ConfigNota::where('empresa_id', $this->empresa_id)->first();

	if($numero != '' && $configNota->token_whatsapp != ''){
		$numero = "55" . $numero;
		$files = [];
		if($request->pedido){
			$dir = $this->criaPdfPedido($item);
			$files[] = $dir;
		}

		if($request->danfe){

			$dir = $this->criarPdfDanfe($item);
			$files[] = $dir;
		}

		if($request->xml){
			$dir = $this->criarXml($item);
			$files[] = $dir;
		}


		if(sizeof($files) > 1){
			foreach($files as $key => $file){
				$texto = "";
				if($key == 0){
					$texto = $request->texto;
				}
				$retorno = $this->util->sendMessage($numero, $texto, $this->empresa_id, $file);
			}
		}else{
			$retorno = $this->util->sendMessage($numero, $request->texto, $this->empresa_id, 
				sizeof($files) == 1 ? $files[0] : null);
		}
		$ret = json_decode($retorno);

		$this->removeArquivosVenda($item);
		// dd($retorno);
		if($ret->success){
			session()->flash("mensagem_sucesso", "Mensagem enviada!");
		}else{
			session()->flash("mensagem_erro", "Falha ao enviar mensagem: " . $ret->message);
		}

	}else{
		session()->flash("mensagem_erro", "Dados para envio incorretos");
	}
	return redirect()->back();

}

private function removeArquivosVenda($venda){
	if(file_exists(public_path('vendas_temp/').'PEDIDO_'.$venda->id.'.pdf')){
		unlink(public_path('vendas_temp/').'PEDIDO_'.$venda->id.'.pdf');
	}

	if(file_exists(public_path('vendas_temp/').'DANFE_'.$venda->id.'.pdf')){
		unlink(public_path('vendas_temp/').'DANFE_'.$venda->id.'.pdf');
	}

}

private function criarXml($venda){
	if(file_exists(public_path('xml_nfe/'.$venda->chave.'.xml'))){
		return env("PATH_URL").'/xml_nfe/'.$venda->chave.'.xml';
	}
}

private function criarPdfDanfe($venda){

	if(!is_dir(public_path('vendas_temp'))){
		mkdir(public_path('vendas_temp'), 0777, true);
	}
	if(file_exists(public_path('xml_nfe/'.$venda->chave.'.xml'))){
		$xml = file_get_contents(public_path('xml_nfe/').$venda->chave.'.xml');

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		if($config->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
		}else{
			$logo = null;
		}
		// $docxml = FilesFolders::readFile($xml);

		try {

			$danfe = new Danfe($xml);
			// $id = $danfe->monta($logo);
			$pdf = $danfe->render($logo);

			file_put_contents(public_path('vendas_temp/').'DANFE_'.$venda->id.'.pdf',$pdf);

			return env("PATH_URL").'/vendas_temp/DANFE_'.$venda->id.'.pdf';
		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		} 
	}
}


private function criaPdfPedido($venda){
	if(!is_dir(public_path('vendas_temp'))){
		mkdir(public_path('vendas_temp'), 0777, true);
	}

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	$p = view('vendas/print')
	->with('config', $config)
	->with('venda', $venda);

	$domPdf = new Dompdf(["enable_remote" => true]);
	$domPdf->loadHtml($p);

	$pdf = ob_get_clean();

	$domPdf->setPaper("A4");
	$domPdf->render();

	$public = env('SERVIDOR_WEB') ? 'public/' : '';

	file_put_contents(public_path('vendas_temp/').'PEDIDO_'.$venda->id.'.pdf', $domPdf->output());
	return env("PATH_URL").'/vendas_temp/PEDIDO_'.$venda->id.'.pdf';
}

public function imprimirPedido($id){
	$venda = Venda::find($id);
	if(valida_objeto($venda)){
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		if($venda->filial_id != null){
			$config = $venda->filial;
		}

		if($config->modelo_impressao_pedido == 2){
			$p = view('vendas/print')
			->with('config', $config)
			->with('venda', $venda);
		}else{
			$p = view('vendas/print2')
			->with('config', $config)
			->with('venda', $venda);
		}
		// return $p;

		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);

		$pdf = ob_get_clean();

		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Pedido de Venda $id.pdf", array("Attachment" => false));
	}else{
		return redirect('/403');
	}
}	

public function baixarXml($id){
	$venda = Venda::find($id);
	if(valida_objeto($venda)){
		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		if(file_exists(public_path('xml_nfe/').$venda->chave.'.xml')){

			return response()->download(public_path('xml_nfe/').$venda->chave.'.xml');
		}else{
			echo "Arquivo XML não encontrado!!";
		}
	}else{
		return redirect('/403');
	}

}

public function edit($id){
	$venda = Venda::find($id);

	$countProdutos = Produto::
	where('empresa_id', $this->empresa_id)
	->where('inativo', false)
	->count();

	if($countProdutos > env("ASSINCRONO_PRODUTOS")){
		$view = $this->vendaAssincronaEdit($venda);
		return $view;
	}else{
		if(valida_objeto($venda)){

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();
			if($config == null){
				return redirect('configNF');
			}
			$lastNF = Venda::lastNF();

			$naturezas = NaturezaOperacao::
			where('empresa_id', $this->empresa_id)
			->get();

			$categorias = Categoria::
			where('empresa_id', $this->empresa_id)
			->get();

			$produtos = Produto::
			where('empresa_id', $this->empresa_id)
			->where('inativo', false)
			->get();

			$tributacao = Tributacao::
			where('empresa_id', $this->empresa_id)
			->first();

			$clientes = Cliente::
			where('empresa_id', $this->empresa_id)
			->where('inativo', 0)
			->get();

			$tiposPagamento = Venda::tiposPagamento();

			foreach($venda->itens as $i){
				$i->produto;
			}
			$venda->duplicatas;
			$venda->natureza;
			$venda->cliente;
			$venda->frete;
			$venda->referencias;

			$transportadoras = Transportadora::
			where('empresa_id', $this->empresa_id)
			->get();

			$produtos = $this->getProdutosParaVenda();

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

			foreach($produtos as $p){
				$p->listaPreco;
				$p->estoque;
			}

			$unidadesDeMedida = Produto::unidadesMedida();

			$formasPagamento = FormaPagamento::
			where('empresa_id', $this->empresa_id)
			->where('status', true)
			->get();

			$usuario = Usuario::find(get_id_user());

			$clientes = Cliente::
			where('empresa_id', $this->empresa_id)
			->with('cidade')
			->where('inativo', 0)
			->get();

			return view("vendas/edit")
			->with('naturezas', $naturezas)
			->with('usuario', $usuario)
			->with('clientes', $clientes)
			->with('formasPagamento', $formasPagamento)
			->with('vendaJs', true)
			->with('config', $config)
			->with('tributacao', $tributacao)
			->with('categorias', $categorias)
			->with('transportadoras', $transportadoras)
			->with('produtos', $produtos)
			->with('unidadesDeMedida', $unidadesDeMedida)
			->with('venda', $venda)
			->with('anps', $anps)
			->with('tiposPagamento', $tiposPagamento)
			->with('lastNF', $lastNF)
			->with('listaCSTCSOSN', $listaCSTCSOSN)
			->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
			->with('listaCST_IPI', $listaCST_IPI)
			->with('natureza', $natureza)
			->with('listaPreco', ListaPreco::where('empresa_id', $this->empresa_id)->get())
			->with('title', "Editar Venda");
		}else{
			return redirect('/403');
		}
	}

}

protected function vendaAssincronaEdit($venda){
	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();
	if($config == null){
		return redirect('configNF');
	}
	$lastNF = Venda::lastNF();

	$naturezas = NaturezaOperacao::
	where('empresa_id', $this->empresa_id)
	->get();

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	$categorias = Categoria::
	where('empresa_id', $this->empresa_id)
	->get();

	$tributacao = Tributacao::
	where('empresa_id', $this->empresa_id)
	->first();

	$clientes = Cliente::
	where('empresa_id', $this->empresa_id)
	->where('inativo', 0)
	->get();

	$tiposPagamento = Venda::tiposPagamento();

	if(count($naturezas) == 0 || $config == null || count($categorias) == 0 || $tributacao == null || count($clientes) == 0){

		$p = view("vendas/alerta")
		->with('produtos', count($produtos))
		->with('categorias', count($categorias))
		->with('clientes', count($clientes))
		->with('naturezas', $naturezas)
		->with('config', $config)
		->with('formasPagamento', $formasPagamento)
		->with('tributacao', $tributacao)
		->with('title', "Validação para Emitir");
		return $p;

	}else{

		$transportadoras = Transportadora::
		where('empresa_id', $this->empresa_id)
		->get();

		foreach($clientes as $c){
			$c->cidade;
		}

		$abertura = $this->verificaAberturaCaixa();
		if($abertura == -1 && env("CAIXA_PARA_NFE") == 1){
			session()->flash("mensagem_erro", "Abra o caixa para vender!");
			return redirect('/caixa');
		}

		$contaPadrao = ContaBancaria::
		where('empresa_id', $this->empresa_id)
		->where('padrao', true)
		->first();

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

		$venda->duplicatas;
		$venda->natureza;
		$venda->cliente;
		$venda->frete;
		$venda->referencias;
		foreach($venda->itens as $i){
			$i->produto;
		}

		$formasPagamento = FormaPagamento::
		where('empresa_id', $this->empresa_id)
		->where('status', true)
		->get();

		$usuario = Usuario::find(get_id_user());
		$vendedores = [];
		$usuarios = Usuario::where('empresa_id', $this->empresa_id)
		->where('ativo', 1)
		->orderBy('nome', 'asc')
		->get();
		foreach($usuarios as $u){
			if($u->funcionario){
				array_push($vendedores, $u);
			}
		}
		$p = view("vendas/edit_assincrono")
		->with('naturezas', $naturezas)
		->with('vendaJsAssincrono', true)
		->with('config', $config)
		->with('vendedores', $vendedores)
		->with('usuario', $usuario)
		->with('listaCSTCSOSN', $listaCSTCSOSN)
		->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
		->with('listaCST_IPI', $listaCST_IPI)
		->with('natureza', $natureza)
		->with('contaPadrao', $contaPadrao)
		->with('clientes', $clientes)
		->with('categorias', $categorias)
		->with('venda', $venda)
		->with('formasPagamento', $formasPagamento)
		->with('anps', $anps)
		->with('unidadesDeMedida', $unidadesDeMedida)
		->with('tributacao', $tributacao)
		->with('transportadoras', $transportadoras)
		->with('tiposPagamento', $tiposPagamento)
		->with('lastNF', $lastNF)
		->with('listaPreco', ListaPreco::where('empresa_id', $this->empresa_id)->get())
		->with('title', "Editar Venda");

		return $p;
	}

}

public function clone($id){

	$venda = Venda::find($id);
	if(valida_objeto($venda)){
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$lastNF = Venda::lastNF();
		if($venda->filial_id != null){
			$config = Filial::findOrFail($venda->filial_id);
			$lastNF = $config->ultimo_numero_nfe;
		}

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', 0)
		->get();

		$semEstoque = $this->validaEstoque($venda);

		return view("vendas/clone")
		->with('vendaJs', true)
		->with('config', $config)
		->with('clientes', $clientes)
		->with('venda', $venda)
		->with('semEstoque', $semEstoque)
		->with('lastNF', $lastNF)
		->with('title', "Clonar Venda");
	}else{
		return redirect('/403');
	}
}

private function validaEstoque($venda){
	$semEstoque = [];
	foreach($venda->itens as $item){
		$p = $item->produto;
		$qtdDisponivel = $p->estoquePorLocal($item->filial_id);
		if($item->quantidade > $qtdDisponivel && $p->gerenciar_estoque){
			array_push($semEstoque, $p);
		}
	}

	return $semEstoque;
}

public function salvarClone(Request $request){
	$cliente = $request->cliente;
	$vendaId = $request->venda_id;

	$venda = Venda::find($vendaId);
	if(valida_objeto($venda)){

		$clienteId = (int)explode("-", $cliente)[0];
		if(!$clienteId){
			session()->flash("mensagem_erro", "Informe o cliente!");
			return redirect()->back();
		}


		$freteId = null;
		if($venda->frete_id != NULL){
			$frete = Frete::create([
				'placa' => $venda->frete->placa,
				'valor' => $venda->frete->valor,
				'tipo' => $venda->frete->tipo,
				'qtdVolumes' => $venda->frete->qtdVolumes,
				'uf' => $venda->frete->uf,
				'numeracaoVolumes' => $venda->frete->numeracaoVolumes,
				'especie' => $venda->frete->especie,
				'peso_liquido' => $venda->frete->peso_liquido,
				'peso_bruto' => $venda->frete->peso_bruto
			]);
			$freteId = $frete->id;
		}

		$total = $venda->valor_total;
		if($total == 0){
			foreach($venda->itens as $i){
				$total += $i->quantidade * $i->valor;
			}
		}
		$novaVenda = [ 
			'cliente_id' => $clienteId,
			'usuario_id' => get_id_user(),
			'frete_id' => $freteId,
			'valor_total' => $total,
			'forma_pagamento' => $venda->forma_pagamento,
			'NfNumero' => 0,
			'natureza_id' => $venda->natureza_id,
			'chave' => '',
			'path_xml' => '',
			'estado' => 'DISPONIVEL',
			'observacao' => $venda->observacao,
			'desconto' => $venda->desconto,
			'acrescimo' => $venda->acrescimo,
			'transportadora_id' => $venda->transportadora_id,
			'sequencia_cce' => 0,
			'tipo_pagamento' => $venda->tipo_pagamento,
			'empresa_id' => $this->empresa_id,
			'bandeira_cartao' =>$venda->bandeira_cartao,
			'cAut_cartao' =>$venda->cAut_cartao,
			'cnpj_cartao' =>$venda->cnpj_cartao,
			'descricao_pag_outros' =>$venda->descricao_pag_outros,
			'filial_id' => $venda->filial_id
		];

		$result = Venda::create($novaVenda);

		$itens = $venda->itens;
		$stockMove = new StockMove();
		foreach ($itens as $i) {
			ItemVenda::create([
				'venda_id' => $result->id,
				'produto_id' => $i->produto_id,
				'quantidade' => $i->quantidade,
				'valor' => $i->valor,
				'cfop' => $i->cfop,
				'valor_custo' => $i->produto->valor_compra,
				'x_pedido' => $i->x_pedido,
				'num_item_pedido' => $i->num_item_pedido

			]);

			$prod = Produto
			::where('id', $i->produto_id)
			->first();

			if(!empty($prod->receita)){

				$receita = $prod->receita; 
				foreach($receita->itens as $rec){

						if(!empty($rec->produto->receita)){ // se item da receita for receita
							$receita2 = $rec->produto->receita; 

							foreach($receita2->itens as $rec2){
								$stockMove->downStock(
									$rec2->produto_id, 
									$i->quantidade * 
									($rec2->quantidade/$receita2->rendimento),
									$venda->filial_id
								);
							}
						}else{

							$stockMove->downStock(
								$rec->produto_id, 
								$i->quantidade* 
								($rec->quantidade/$receita->rendimento),
								$venda->filial_id
							);
						}
					}
				}else{
					$stockMove->downStock(
						$i->produto_id, $i->quantidade, $venda->filial_id);
				}
			}

			if($venda->forma_pagamento != 'a_vista' && $venda->forma_pagamento != 'conta_crediario'){
				$fatura = $venda->duplicatas;

				foreach ($fatura as $key => $f) {
					$valorParcela = str_replace(",", ".", $f['valor']);

					$resultFatura = ContaReceber::create([
						'venda_id' => $result->id,
						'data_vencimento' => $f->data_vencimento,
						'data_recebimento' => $f->data_recebimento,
						'valor_integral' => $f->valor_integral,
						'valor_recebido' => 0,
						'tipo_pagamento' => $f->tipo_pagamento,
						'status' => false,
						'entrada' => $f['entrada'],
						'referencia' => "Parcela ". ($key+1) . "/" . sizeof($fatura) .", da Venda " . $result->id,
						'categoria_id' => $f->categoria_id,
						'empresa_id' => $this->empresa_id
					]);
				}
			}

			session()->flash("mensagem_sucesso", "Venda duplicada com sucesso!");
			return redirect('/vendas');
		}else{
			return redirect('/403');
		}

	}

	public function gerarXml($id){
		$certificado = Certificado::
		where('empresa_id', $this->empresa_id)
		->first();

		if($certificado == null){
			echo "Necessário o certificado para realizar esta ação!";
			die;
		}
		$venda = Venda::find($id);

		if(valida_objeto($venda)){

			$isFilial = $venda->filial_id;
			if($venda->filial_id == null){
				$config = ConfigNota::
				where('empresa_id', $this->empresa_id)
				->first();
			}else{
				$config = Filial::findOrFail($venda->filial_id);
				if($config->arquivo_certificado == null){
					echo "Necessário o certificado para realizar esta ação!";
					die;
				}
			}
			$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

			$nfe_service = new NFService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => (int)$config->ambiente,
				"razaosocial" => $config->razao_social,
				"siglaUF" => $config->UF,
				"cnpj" => $cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => $config->csc,
				"CSCid" => $config->csc_id,
				"is_filial" => $isFilial,
			]);
			$nfe = $nfe_service->gerarNFe($id);
			if(!isset($nfe['erros_xml'])){
				$xml = $nfe_service->sign($nfe['xml']);
				
				return response($xml)
				->header('Content-Type', 'application/xml');

			} else{
				foreach($nfe['erros_xml'] as $e) {
					echo $e;
				}
			}
		}else{
			return redirect('/403');
		}
	}

	public function calculaFrete(Request $request){

		$stringUrl = "&sCepOrigem=$request->sCepOrigem&sCepDestino=$request->sCepDestino&nVlPeso=$request->nVlPeso";

		$stringUrl .= "&nVlComprimento=$request->nVlComprimento&nVlAltura=$request->nVlAltura&nVlLargura=$request->nVlLargura&nCdServico=04014";

		$url = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa=&sDsSenha=&sCdAvisoRecebimento=n&sCdMaoPropria=n&nVlValorDeclarado=0&nVlDiametro=0&StrRetorno=xml&nIndicaCalculo=3&nCdFormato=1" . $stringUrl;

		$unparsedResult = file_get_contents($url);
		$parsedResult = simplexml_load_string($unparsedResult);

		$stringUrl = "&sCepOrigem=$request->sCepOrigem&sCepDestino=$request->sCepDestino&nVlPeso=$request->nVlPeso";

		$stringUrl .= "&nVlComprimento=$request->nVlComprimento&nVlAltura=$request->nVlAltura&nVlLargura=$request->nVlLargura&nCdServico=04510";

		$url = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa=&sDsSenha=&sCdAvisoRecebimento=n&sCdMaoPropria=n&nVlValorDeclarado=0&nVlDiametro=0&StrRetorno=xml&nIndicaCalculo=3&nCdFormato=1" . $stringUrl;

		$unparsedResultSedex = file_get_contents($url);
		$parsedResultSedex = simplexml_load_string($unparsedResultSedex);

		$retorno = array(
			'preco_sedex' => strval($parsedResult->cServico->Valor),
			'prazo_sedex' => strval($parsedResult->cServico->PrazoEntrega),

			'preco' => strval($parsedResultSedex->cServico->Valor),
			'prazo' => strval($parsedResultSedex->cServico->PrazoEntrega)
		);

		return response()->json($retorno, 200);
	}

	public function importacao(){
		$zip_loaded = extension_loaded('zip') ? true : false;
		if ($zip_loaded === false) {
			session()->flash('mensagem_erro', "Por favor instale/habilite o PHP zip para importar");
			return redirect()->back();
		}

		$natureza = NaturezaOperacao::
		where('empresa_id', $this->empresa_id)
		->first();

		if($natureza == null){
			session()->flash('mensagem_erro', 'Informe uma natureza de operação!');
			return redirect('/naturezaOperacao/new');
		}

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		if($config == null){
			session()->flash('mensagem_erro', 'Informe a configuração do emitente!');
			return redirect('/configNF');
		}

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first();

		if($tributacao == null){
			session()->flash('mensagem_erro', 'Informe a tributação!');
			return redirect('/tributos');
		}
		
		return view('vendas/importacao')
		->with('title', 'Importação de xml');
	}

	public function importacaoStore(Request $request){
		if ($request->hasFile('file')) {

			$zip = new \ZipArchive();
			$zip->open($request->file);

			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			$destino = $public . 'extract';
			$this->limparPasta($destino);
			if($zip->extractTo($destino) == TRUE){

				$data = $this->preparaXmls($destino);
				
				if(sizeof($data) == 0){
					session()->flash('mensagem_erro', "Algo errado com o arquivo!");
					return redirect()->back();
				}
				return view('vendas/import')
				->with('data', $data)
				->with('title', 'Importação de XML');

			}else {
				session()->flash('mensagem_erro', "Erro ao desconpactar arquivo");
				return redirect()->back();
			}
			$zip->close();
		}else{
			session()->flash('mensagem_erro', 'Nenhum Arquivo!!');
			return redirect()->back();
		}
	}

	private function limparPasta($destino){
		$files = glob($destino."/*");
		foreach($files as $file){ 
			if(is_file($file)) unlink($file); 
		}
	}

	private function preparaXmls($destino){
		$files = glob($destino."/*");
		$data = [];
		foreach($files as $file){
			if(is_file($file)){
				$xml = simplexml_load_file($file);
				$cliente = $this->getCliente($xml);
				$produtos = $this->getProdutos($xml);
				$fatura = $this->getFatura($xml);

				if($produtos != null){

					$temp = [
						'data' => $xml->NFe->infNFe->ide->dhEmi,
						'chave' => substr($xml->NFe->infNFe->attributes()->Id, 3, 44),
						'total' => $xml->NFe->infNFe->total->ICMSTot->vProd,
						'numero_nf' => $xml->NFe->infNFe->ide->nNF,
						'desconto' => $xml->NFe->infNFe->total->ICMSTot->vDesc,
						'cliente' => $cliente,
						'produtos' => $produtos,
						'fatura' => $fatura,
						'file' => $file,
						'natureza' => $xml->NFe->infNFe->ide->natOp[0],
						'observacao' => $xml->NFe->infNFe->infAdic ? $xml->NFe->infNFe->infAdic->infCpl[0] : '',
						'tipo_pagamento' => $xml->NFe->infNFe->pag->detPag->tPag,
						'forma_pagamento' => $xml->NFe->infNFe->pag->detPag->indPag ?? 0
					];
					array_push($data, $temp);
				}
			}
		}

		return $data;
	}

	private function getCliente($xml){
		if(!isset($xml->NFe->infNFe->dest->enderDest->cMun)) return null;
		$cidade = Cidade::getCidadeCod($xml->NFe->infNFe->dest->enderDest->cMun);
		$dadosCliente = [
			'cpf_cnpj' => isset($xml->NFe->infNFe->dest->CNPJ) ? $xml->NFe->infNFe->dest->CNPJ : $xml->NFe->infNFe->dest->CPF,
			'razao_social' => $xml->NFe->infNFe->dest->xNome, 				
			'nome_fantasia' => $xml->NFe->infNFe->dest->xFant,
			'rua' => $xml->NFe->infNFe->dest->enderDest->xLgr,
			'numero' => $xml->NFe->infNFe->dest->enderDest->nro,
			'bairro' => $xml->NFe->infNFe->dest->enderDest->xBairro,
			'cep' => $xml->NFe->infNFe->dest->enderDest->CEP,
			'telefone' => $xml->NFe->infNFe->dest->enderDest->fone,
			'celular' => '',
			'ie_rg' => $xml->NFe->infNFe->dest->IE,
			'cidade_id' => $cidade != null ? $cidade->id : 1,
			'consumidor_final' => 1,
			'limite_venda' => 0,
			'contribuinte' => 1,
			'rua_cobranca' => '',
			'numero_cobranca' => '',
			'bairro_cobranca' => '',
			'cep_cobranca' => '',
			'cidade_cobranca_id' => NULL,
			'empresa_id' => $this->empresa_id
		];

		return $dadosCliente;
	}

	private function getProdutos($xml){
		$itens = [];
		try{
			foreach($xml->NFe->infNFe->det as $item) {

				$produto = Produto::verificaCadastrado($item->prod->cEAN,
					$item->prod->xProd, $item->prod->cProd);

				$produtoNovo = !$produto ? true : false;
				$item = [
					'codigo' => $item->prod->cProd,
					'xProd' => $item->prod->xProd,
					'NCM' => $item->prod->NCM,
					'CFOP' => $item->prod->CFOP,
					'CFOP_entrada' => $this->getCfopEntrada($item->prod->CFOP),
					'uCom' => $item->prod->uCom,
					'vUnCom' => $item->prod->vUnCom,
					'qCom' => $item->prod->qCom,
					'codBarras' => $item->prod->cEAN,
					'produtoNovo' => $produtoNovo,
					'produtoId' => $produtoNovo ? '0' : $produto->id
				];
				array_push($itens, $item);
			}
			return $itens;
		}catch(\Exception $e){
			return null;
		}
	}

	private function getCfopEntrada($cfop){
		$natureza = NaturezaOperacao::
		where('empresa_id', $this->empresa_id)
		->where('CFOP_saida_estadual', $cfop)
		->first();

		if($natureza != null){
			return $natureza->CFOP_entrada_inter_estadual;
		}

		$natureza = NaturezaOperacao::
		where('empresa_id', $this->empresa_id)
		->where('CFOP_saida_inter_estadual', $cfop)
		->first();

		if($natureza != null){
			return $natureza->CFOP_entrada_inter_estadual;
		}

		$digito = substr($cfop, 0, 1);
		if($digito == '5'){
			return '1'. substr($cfop, 1, 4);

		}else{
			return '2'. substr($cfop, 1, 4);
		}
	}

	private function getCfopEstadual($cfop){
		$digito = substr($cfop, 0, 1);
		if($digito == '5'){ 
			return $cfop;
		}else{
			return '5'. substr($cfop, 1, 4);
		}
	}

	private function getCfopInterEstadual($cfop){
		$digito = substr($cfop, 0, 1);
		if($digito == '6'){ 
			return $cfop;
		}else{
			return '6'. substr($cfop, 1, 4);
		}
	}

	private function getCfopEntradaInterEstadual($cfop){
		$digito = substr($cfop, 0, 1);
		return '2'. substr($cfop, 1, 4);
	}

	private function getCfopEntradaEstadual($cfop){
		$digito = substr($cfop, 0, 1);
		return '1'. substr($cfop, 1, 4);
	}

	private function getFatura($xml){
		$fatura = [];

		try{
			if (!empty($xml->NFe->infNFe->cobr->dup))
			{
				foreach($xml->NFe->infNFe->cobr->dup as $dup) {
					$titulo = $dup->nDup;
					$vencimento = $dup->dVenc;
					$vencimento = explode('-', $vencimento);
					$vencimento = $vencimento[2]."/".$vencimento[1]."/".$vencimento[0];
					$vlr_parcela = number_format((double) $dup->vDup, 2, ",", ".");	

					$parcela = [
						'numero' => (int)$titulo,
						'vencimento' => $dup->dVenc,
						'valor_parcela' => $vlr_parcela,
						'rand' => rand(0, 10000)
					];
					array_push($fatura, $parcela);
				}
			}else{

				$vencimento = explode('-', substr($xml->NFe->infNFe->ide->dhEmi[0], 0,10));
				$vencimento = $vencimento[2]."/".$vencimento[1]."/".$vencimento[0];
				$parcela = [
					'numero' => 1,
					'vencimento' => substr($xml->NFe->infNFe->ide->dhEmi[0], 0,10),
					'valor_parcela' => (float)$xml->NFe->infNFe->pag->detPag->vPag[0],
					'rand' => rand(0, 10000)
				];
				array_push($fatura, $parcela);
			}
		}catch(\Exception $e){

		}

		return $fatura;
	}

	public function importStore(Request $request){
		$tabela = $request->tabela;
		$data = json_decode($request->data);
		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		foreach($data as $d){
			if($request->input('ch_'.$d->chave)){
				$cliente = json_decode(json_encode($d->cliente), true);
				if($cliente){
					$cliente = $this->insereCliente($cliente);
				}else{

				}

				$produtos = json_decode(json_encode($d->produtos), true);

				$itens = $this->insereProdutos($produtos);

				if($tabela == 'vendas'){
					if($cliente != null){

						$vendaId = $this->salvarVenda($d, $cliente, $produtos);
						$this->gravarItensVenda($vendaId, $itens);

						$fatura = json_decode(json_encode($d->fatura), true);
						$this->salvarFatura($vendaId, $fatura);

						File::copy($d->file, $public . "xml_nfe/".$d->chave.".xml");
					}
				}else{
					$vendaId = $this->salvarVendaCaixa($d, $produtos);
					$this->gravarItensVendaCaixa($vendaId, $itens);

					File::copy($d->file, $public . "xml_nfce/".$d->chave.".xml");
				}
			}
		}

		session()->flash('mensagem_sucesso', 'Importação concluida!!');
		return redirect('/vendas/importacao');
	}

	private function salvarVenda($venda, $cliente, $produtos){
		$venda = json_decode(json_encode($venda), true);

		$natureza = $this->insereNatureza($venda['natureza'][0], $produtos);
		
		$arrVenda = [
			'cliente_id' => $cliente->id,
			'transportadora_id' => NULL,
			'forma_pagamento' => isset($venda['forma_pagamento'][0]) ? ($venda['forma_pagamento'][0] == 0 ? 'a_vista' : 'personalizado') : 'a_vista',
			'tipo_pagamento' => $venda['tipo_pagamento'][0],
			'usuario_id' => get_id_user(),
			'valor_total' => $venda['total'][0],
			'desconto' => $venda['desconto'][0],
			'acrescimo' => 0,
			'frete_id' => null,
			'NfNumero' => $venda['numero_nf'][0],
			'natureza_id' => $natureza,
			'path_xml' => '',
			'chave' => $venda['chave'],
			'sequencia_cce' => 0,
			'observacao' => $venda['observacao'][0] ?? '',
			'estado' => 'APROVADO',
			'empresa_id' => $this->empresa_id,
		];
		// dd($arrVenda);
		// echo "<pre>";
		// print_r($arrVenda);
		// echo "</pre>";

		$result = Venda::create($arrVenda);
		$data = $venda['data'][0];
		$data = \Carbon\Carbon::parse($data)->format('Y-m-d H:i:s');
		$result->created_at = $data;
		$result->data_emissao = $data;
		$result->save();
		return $result->id;

	}


	private function salvarVendaCaixa($venda, $produtos){
		$venda = json_decode(json_encode($venda), true);

		$natureza = $this->insereNatureza($venda['natureza'][0], $produtos);
		$arrVenda = [
			'cliente_id' => NULL,
			'usuario_id' => get_id_user(),
			'valor_total' => $venda['total'][0],
			'NFcNumero' => $venda['numero_nf'][0],
			'natureza_id' => $natureza,
			'chave' => $venda['chave'],
			'path_xml' => '',
			'estado' => 'APROVADO',
			'tipo_pagamento' => $venda['tipo_pagamento'][0],
			'forma_pagamento' => isset($venda['forma_pagamento'][0]) ? $venda['forma_pagamento'][0] == 0 ? 'a_vista' : 'personalizado' : 'a_vista',
			'dinheiro_recebido' => $venda['total'][0],
			'troco' => 0,
			'nome' => '',
			'cpf' => '',
			'observacao' => $venda['observacao'][0] ?? '',
			'desconto' => $venda['desconto'][0],
			'acrescimo' => 0,
			'pedido_delivery_id' => 0,
			'tipo_pagamento_1' => '',
			'valor_pagamento_1' => 0,
			'tipo_pagamento_2' => '',
			'valor_pagamento_2' => 0,
			'tipo_pagamento_3' => '',
			'valor_pagamento_3' => 0,
			'empresa_id' => $this->empresa_id,
			'numero_sequencial' => VendaCaixa::lastNumero($this->empresa_id),
			'created_at' => $venda['data'][0]
		];
		// echo "<pre>";
		// print_r($arrVenda);
		// echo "</pre>";

		$result = VendaCaixa::create($arrVenda);
		$data = $venda['data'][0];
		$data = \Carbon\Carbon::parse($data)->format('Y-m-d H:i:s');
		$result->created_at = $data;
		$result->data_emissao = $data;
		$result->save();
		return $result->id;

	}

	private function gravarItensVenda($vendaId, $itens){
		foreach($itens as $i){
			$pTemp = Produto::find($i['codigo']);
			ItemVenda::create([
				'venda_id' => $vendaId,
				'produto_id' => $i['codigo'],
				'quantidade' => $i['quantidade'],
				'valor' => $i['valor'],
				'valor_custo' => $pTemp->valor_compra
			]);
		}
	}

	private function gravarItensVendaCaixa($vendaId, $itens){
		foreach($itens as $i){
			$pTemp = Produto::find($i['codigo']);

			ItemVendaCaixa::create([
				'venda_caixa_id' => $vendaId,
				'produto_id' => $i['codigo'],
				'quantidade' => $i['quantidade'],
				'valor' => $i['valor'],
				'item_pedido_id' => NULL,
				'observacao' => '',
				'valor_custo' => $pTemp->valor_compra
			]);
		}
	}

	private function salvarFatura($vendaId, $fatura){
		foreach($fatura as $key => $f){
			try{
				$resultFatura = ContaReceber::create([
					'venda_id' => $vendaId,
					'data_vencimento' => (string)$f['vencimento'][0],
					'data_recebimento' => (string)$f['vencimento'][0],
					'valor_integral' => __replace($f['valor_parcela']),
					'valor_recebido' => 0,
					'status' => false,
					'entrada' => $f['entrada'],
					'referencia' => "Parcela da Venda $vendaId",
					'categoria_id' => CategoriaConta::where('empresa_id', $this->empresa_id)->first()->id,
					'empresa_id' => $this->empresa_id
				]);
			}catch(\Exception $e){

			}
		}
	}

	private function insereNatureza($nome, $produtos){
		$natureza = NaturezaOperacao::where('natureza', $nome)
		->where('empresa_id', $this->empresa_id)
		->first();

		$cfopEstadual = $this->getCfopEstadual($produtos[0]['CFOP'][0]);
		$cfopInterEstadual = $this->getCfopInterEstadual($produtos[0]['CFOP'][0]);
		$cfopEntradaEstadual = $this->getCfopEntradaEstadual(
			$produtos[0]['CFOP'][0]);
		$cfopEntradaInterEstadual = $this->getCfopEntradaInterEstadual(
			$produtos[0]['CFOP'][0]);

		if($natureza != null) return $natureza->id;

		$data = [
			'natureza' => $nome,
			'CFOP_entrada_estadual' => $cfopEntradaEstadual,
			'CFOP_entrada_inter_estadual' => $cfopEntradaInterEstadual,
			'CFOP_saida_estadual' => $cfopEstadual,
			'CFOP_saida_inter_estadual' => $cfopInterEstadual,
			'empresa_id' => $this->empresa_id,
			'sobrescreve_cfop' => 0,
			'finNFe' => 1,
			'nao_movimenta_estoque' => 0
		];
		$res = NaturezaOperacao::create($data);
		return $res->id;
	}

	private function insereCliente($data){

		if(!isset($data['cpf_cnpj'][0])) return null;

		$cadastrado = Cliente::verificaCadastrado($data['cpf_cnpj'][0]);

		if($cadastrado != null) return $cadastrado;

		$cli = [
			'cpf_cnpj' => $data['cpf_cnpj'][0],
			'razao_social' => $data['razao_social'][0], 				
			'nome_fantasia' => $data['nome_fantasia'] ? $data['nome_fantasia'][0] : "",
			'rua' => $data['rua'][0],
			'numero' => $data['numero'][0],
			'bairro' => $data['bairro'][0],
			'cep' => isset($data['cep'][0]) ? $data['cep'][0] : '',
			'telefone' => $data['telefone'] ? $data['telefone'][0] : "",
			'celular' => '',
			'ie_rg' => $data['ie_rg'] ? $data['ie_rg'][0] : "",
			'cidade_id' => $data['cidade_id'],
			'consumidor_final' => 1,
			'limite_venda' => 0,
			'contribuinte' => 1,
			'rua_cobranca' => '',
			'numero_cobranca' => '',
			'bairro_cobranca' => '',
			'cep_cobranca' => '',
			'email' => '',
			'cidade_cobranca_id' => NULL,
			'empresa_id' => $this->empresa_id
		];

		$res = Cliente::create($cli);
		$cliente = Cliente::find($res->id);
		return $cliente;

	}

	private function insereProdutos($data){
		$itens = [];
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		foreach($data as $key => $d){
			$categoria = Categoria::where('empresa_id', $this->empresa_id)->first();
			$produto = Produto::verificaCadastrado($d['codBarras'] ? $d['codBarras'][0] : '',
				$d['xProd'][0], $d['codigo'][0]);

			if($produto == null){
				$prod = [
					'nome' => $d['xProd'][0],
					'categoria_id' => $categoria->id,
					'cor' => '',
					'valor_venda' => $d['vUnCom'][0],
					'NCM' => $d['NCM'][0],
					'CST_CSOSN' => $config->CST_CSOSN_padrao,
					'CST_PIS' => $config->CST_COFINS_padrao,
					'CST_COFINS' => $config->CST_PIS_padrao,
					'CST_IPI' => $config->CST_IPI_padrao,
					'unidade_compra' => 'UN',
					'unidade_venda' => $d['uCom'][0],
					'composto' => 0,
					'codBarras' => $d['codBarras'] ? $d['codBarras'][0] : 'SEM GTIN',
					'conversao_unitaria' => 1,
					'valor_livre' => 0,
					'perc_icms' => 0,
					'perc_pis' => 0,
					'perc_cofins' => 0,
					'perc_ipi' => 0,
					'CFOP_saida_estadual' => $this->getCfopEstadual($d['CFOP'][0]),
					'CFOP_saida_inter_estadual' => $this->getCfopInterEstadual($d['CFOP'][0]),
					'codigo_anp' => '',
					'descricao_anp' => '',
					'perc_iss' => 0,
					'cListServ' => '',
					'imagem' => '',
					'alerta_vencimento' => 0,
					'valor_compra' => 0,
					'gerenciar_estoque' => $config->gerenciar_estoque_produto,
					'estoque_minimo' => 0,
					'referencia' => '',
					'empresa_id' => $this->empresa_id,
					'largura' => 0,
					'comprimento' => 0,
					'altura' => 0,
					'peso_liquido' => 0,
					'peso_bruto' => 0,
					'limite_maximo_desconto' => 0,
					'grade' => 0,
					'referencia_grade' => Str::random(20)
				];

				$res = Produto::create($prod);
				$temp = [
					'codigo' => $res->id,
					'quantidade' => (float)$d['qCom'][0],
					'valor' => (float)$d['vUnCom'][0]
				];
			}else{
				$temp = [
					'codigo' => $produto->id,
					'quantidade' => (float)$d['qCom'][0],
					'valor' => (float)$d['vUnCom'][0]
				];
			}
			array_push($itens, $temp);
		}
		return $itens;
	}

	public function estadoFiscal($id){
		$venda = Venda::
		where('id', $id)
		->first();
		$value = session('user_logged');
		if($value['adm'] == 0) return redirect()->back();
		if(valida_objeto($venda)){

			return view("vendas/alterar_estado_fiscal")
			->with('venda', $venda)
			->with('title', "Alterar estado venda $id");
		}else{
			return redirect('/403');
		}
	}

	public function estadoFiscalStore(Request $request){
		try{
			$venda = Venda::find($request->venda_id);
			$estado = $request->estado;

			$venda->estado = $estado;
			if($estado == 'CANCELADO'){
				$venda->valor_total = 0;
			}
			if ($request->hasFile('file')){
				$public = env('SERVIDOR_WEB') ? 'public/' : '';

				$xml = simplexml_load_file($request->file);
				$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
				$file = $request->file;
				$file->move(public_path('xml_nfe'), $chave.'.xml');
				$venda->chave = $chave;
				$venda->data_emissao = date('Y-m-d H:i:s');
				$venda->NfNumero = (int)$xml->NFe->infNFe->ide->nNF;

				if($venda->filial_id != null){
					$config = Filial::findOrFail($venda->filial_id);
					$config->ultimo_numero_nfe = (int)$xml->NFe->infNFe->ide->nNF;
					$config->save();
				}else{
					$config = ConfigNota::
					where('empresa_id', $this->empresa_id)
					->first();

					$config->ultimo_numero_nfe = (int)$xml->NFe->infNFe->ide->nNF;
					$config->save();
				}

			}

			$venda->save();
			session()->flash("mensagem_sucesso", "Estado alterado");

		}catch(\Exception $e){
			session()->flash("mensagem_erro", "Erro: " . $e->getMessage());

		}
		return redirect()->back();
	}

	public function calcComissao(){

		$vendas = Venda::
		where('empresa_id', $this->empresa_id)
		->get();

		foreach($vendas as $v){
			$comissao = ComissaoVenda::
			where('empresa_id', $this->empresa_id)
			->where('tabela', 'vendas')
			->where('venda_id', $v->id)
			->first();
			if($comissao == null){
				try{
					$usuario = Usuario::find($v->usuario_id);
					if(isset($usuario->funcionario)){
						$percentual_comissao = __replace($usuario->funcionario->percentual_comissao);
						$valorComissao = ($v->valor_total * $percentual_comissao) / 100;
						echo $v->valor_total  . "<br>";
						echo $percentual_comissao  . "<br>";
						echo $valorComissao . "<br>";
						echo "<br><br>";
						ComissaoVenda::create(
							[
								'funcionario_id' => $usuario->funcionario->id,
								'venda_id' => $v->id,
								'tabela' => 'vendas',
								'valor' => $valorComissao,
								'status' => 0,
								'empresa_id' => $this->empresa_id,
								'created_at' => $v->created_at
							]
						);
					}else{
						echo $v->usuario->nome . ' - '. $v->created_at . "<br>";
					}
				}catch(\Exception $e){
					echo "Erro: ". $e->getMessage();
					die;
				}
			}

		}
	}

	public function gerarFormasPagamento(){
		$empresas = Empresa::all();
		foreach($empresas as $e){
			FormaPagamento::create([
				'empresa_id' => $e->id,
				'nome' => 'A vista',
				'chave' => 'a_vista',
				'taxa' => 0,
				'status' => 1,
				'prazo_dias' => 0,
				'tipo_taxa' => 'perc'
			]);
			FormaPagamento::create([
				'empresa_id' => $e->id,
				'nome' => '30 dias',
				'chave' => '30_dias',
				'taxa' => 0,
				'status' => 1,
				'prazo_dias' => 30,
				'tipo_taxa' => 'perc'
			]);
			FormaPagamento::create([
				'empresa_id' => $e->id,
				'nome' => 'Personalizado',
				'chave' => 'personalizado',
				'taxa' => 0,
				'status' => 1,
				'prazo_dias' => 0,
				'tipo_taxa' => 'perc'
			]);
			FormaPagamento::create([
				'empresa_id' => $e->id,
				'nome' => 'Conta crediario',
				'chave' => 'conta_crediario',
				'taxa' => 0,
				'status' => 1,
				'prazo_dias' => 0,
				'tipo_taxa' => 'perc'
			]);
		}
	}

	public function editXml($id){
		$item = Venda::findOrFail($id);

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		$nfe_service = new NFService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => " v8zRciG2x1Y32X8Q_ebzXXHj5yKd6cwJgkdXgeJTak5rwqe4v4yzt0537HmXrY8G",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		]);

		$nfe = $nfe_service->gerarNFe($item->id);

		if(!isset($nfe['erros_xml'])){
			$xml = $nfe['xml'];

			return view('vendas.edit_xml', compact('item', 'xml'))
			->with('title', 'Editando XML');
		}else{
			print_r($nfe['erros_xml']);
		}

	}
}
