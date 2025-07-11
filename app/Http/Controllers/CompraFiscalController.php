<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\ItemCompra;
use App\Models\Fornecedor;
use App\Models\Compra;
use App\Helpers\StockMove;
use App\Models\Cidade;
use App\Models\ConfigNota;
use App\Models\ManifestaDfe;
use App\Models\NaturezaOperacao;
use App\Services\DFeService;
use App\Models\Marca;
use App\Models\SubCategoria;
use App\Models\ProdutoMapeamento;
use Illuminate\Support\Facades\Log;


class CompraFiscalController extends Controller
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
		$natureza = Produto::firstNatureza($this->empresa_id);
		if($natureza == null){
			session()->flash('mensagem_erro', 'Cadastre uma natureza de operação!');
			return redirect('/naturezaOperacao');
		}

		$categoria = Categoria::
		where('empresa_id', $this->empresa_id)
		->first();
		if($categoria == null){
			session()->flash('mensagem_erro', 'Cadastre uma categoria de produto!');
			return redirect('/categorias');
		}

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		if($config == null){
			session()->flash('mensagem_erro', 'Configure o emitente!');
			return redirect('/configNF');
		}
		return view('compraFiscal/new')
		->with('title', 'Compra Fiscal');
	}

	private function validaChave($chave){
		$msg = "";
		$chave = substr($chave, 3, 44);

		$cp = Compra::
		where('chave', $chave)
		->where('empresa_id', $this->empresa_id)
		->first();

		$manifesto = ManifestaDfe::
		where('chave', $chave)
		->where('empresa_id', $this->empresa_id)
		->first();

		// if($cp != null) $msg = "XML já importado na compra fiscal";
		if($cp != null) $msg = "Atenção: O XML selecionado já foi importado na compra fiscal. A duplicação não é permitida. Verifique os registros antes de tentar novamente.";
		// if($manifesto != null) $msg .= "XML já importado através do manifesto fiscal";
		return $msg;
	}

	public function new(Request $request){
		if ($request->hasFile('file')){
			$arquivo = $request->hasFile('file');
			$xml = simplexml_load_file($request->file);


			if($xml->NFe->infNFe == null){
				session()->flash('mensagem_erro', 'Este XML parece inválido!');
				return redirect("/compraFiscal");
			}

			$msgImport = $this->validaChave($xml->NFe->infNFe->attributes()->Id);

			if($msgImport == ""){
				$cidade = Cidade::getCidadeCod($xml->NFe->infNFe->emit->enderEmit->cMun);
				$dadosEmitente = [
					'cpf' => $xml->NFe->infNFe->emit->CPF,
					'cnpj' => $xml->NFe->infNFe->emit->CNPJ,  				
					'razaoSocial' => $xml->NFe->infNFe->emit->xNome, 				
					'nomeFantasia' => $xml->NFe->infNFe->emit->xFant,
					'logradouro' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
					'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
					'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
					'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
					'fone' => $xml->NFe->infNFe->emit->enderEmit->fone,
					'ie' => $xml->NFe->infNFe->emit->IE,
					'cidade_id' => $cidade->id
				];

				$vDesc = $xml->NFe->infNFe->total->ICMSTot->vDesc;

				$idFornecedor = 0;
				$fornecedorEncontrado = $this->verificaFornecedor($dadosEmitente['cnpj']);
				$dadosAtualizados = [];
				if($fornecedorEncontrado){
					$idFornecedor = $fornecedorEncontrado->id;
					$dadosAtualizados = $this->verificaAtualizacao($fornecedorEncontrado, $dadosEmitente);
				}else{
					array_push($dadosAtualizados, "Fornecedor cadastrado com sucesso");
					$idFornecedor = $this->cadastrarFornecedor($dadosEmitente);
				}

			//Produtos
			//itens

		// Função auxiliar para realizar o mapeamento ou vinculação
function vincularOuMapearProduto($xmlItem, $idFornecedor, $empresa_id)
{
    // Extrai os valores do XML
    $id_xml = trim((string)$xmlItem->prod->cProd);
    $codBarras_xml = trim((string)$xmlItem->prod->cEAN);

    // Se o código de barras for "SEM GTIN", não busca no sistema, apenas na tabela de mapeamento
    if (strtoupper($codBarras_xml) === 'SEM GTIN') {
        $mapping = \App\Models\ProdutoMapeamento::where('id_fornecedor', $idFornecedor)
                    ->where('id_xml', $id_xml)
                    ->where('codBarras_xml', $codBarras_xml)
                    ->first();
        return $mapping ? $mapping->id_produto : null;
    }

    // Se o código de barras for diferente de "SEM GTIN":
    // Primeiro, procura na tabela de mapeamento
    $mapping = \App\Models\ProdutoMapeamento::where('id_fornecedor', $idFornecedor)
                ->where('id_xml', $id_xml)
                ->where('codBarras_xml', $codBarras_xml)
                ->first();
    if ($mapping) {
        return $mapping->id_produto;
    } else {
        // Se não encontrar, procura o produto na tabela de produtos pelo código de barras
        $produto = \App\Models\Produto::where('codBarras', $codBarras_xml)
                    ->where('empresa_id', $empresa_id)
                    ->first();
        if ($produto) {
            // Registra o mapeamento para futuras importações
            \App\Models\ProdutoMapeamento::create([
                'id_xml'           => $id_xml,
                'codBarras_xml'    => $codBarras_xml,
                'id_fornecedor'    => $idFornecedor,
                'id_produto'       => $produto->id,
                'codBarras_produto'=> $produto->codBarras, // ou outro valor que desejar
                'empresa_id'       => $empresa_id,
                'filial_id'        => null, // ajuste se necessário
            ]);
            return $produto->id;
        } else {
            // Produto não encontrado; deverá ser vinculado ou cadastrado manualmente
            return null;
        }
    }
}


				$seq = 0;
				$itens = [];
				$contSemRegistro = 0;
                $totalDesconto = 0;
                $totalSubstituicaoTributaria = 0;
				foreach($xml->NFe->infNFe->det as $item) {
					
					// --- Mapeamento do Produto ---
					$produtoId = vincularOuMapearProduto($item, $idFornecedor, $this->empresa_id);
					$produtoNovo = false;
					if ($produtoId) {
						// Produto encontrado (vinculado ou mapeado) – obtém o registro do produto
						$produto = \App\Models\Produto::find($produtoId);
					} else {
						// Produto não encontrado; o item ficará para vinculação manual
						$produtoNovo = true;
						$contSemRegistro++;
					}
				
					// Caso o produto já exista, pode-se buscar, por exemplo, o código SIAD associado
					$codSiad = 0;
					if (!$produtoNovo) {
						$i = \App\Models\ItemCompra::where('produto_id', $produto->id)->first();
						if ($i != null) {
							$codSiad = $i->codigo_siad ?? 0;
						}
					}
				
					// --- Processamento do Código do Produto ---
					// Remove ou substitui caracteres indesejados (você pode adaptar conforme sua necessidade)
                    $codigo = str_replace(
                       ["/", "'", "-", "(", ")", " ", ":", "[", "]"],
                       ["_", "_", "_", "", "", "", "", "", ""],
                       (string)$item->prod->cProd
                    );
				
					// --- Cálculo dos Impostos e demais valores ---
    // --- Cálculo dos Impostos e demais valores ---
    // IPI
    $vIpi = 0;
    if (isset($item->imposto->IPI) && isset($item->imposto->IPI->IPITrib->vIPI)) {
        $valorIPI = (float)$item->imposto->IPI->IPITrib->vIPI;
        if ($valorIPI > 0 && (float)$item->prod->qCom > 0) {
            $vIpi = $valorIPI / (float)$item->prod->qCom;
        }
    }

    // ICMS Substituição Tributária
    $vICMSST = 0;
    if (isset($item->imposto->ICMS)) {
        $arr = array_values((array)$item->imposto->ICMS);
        $valorICMSST = (float)(isset($arr[0]->vICMSST) ? $arr[0]->vICMSST : 0);
        if ($valorICMSST > 0 && (float)$item->prod->qCom > 0) {
            $vICMSST = $valorICMSST / (float)$item->prod->qCom;
        }
    }

// Outras despesas
$outrasDespesas = isset($item->prod->vOutro) ? (float)$item->prod->vOutro : 0;

// Quantidade original do XML
$qComXml = (float)$item->prod->qCom;

// Conversão do produto: se não for novo e existir uma conversão válida, senão 1
$conversao = (!$produtoNovo && isset($produto->conversao_unitaria) && (float)$produto->conversao_unitaria > 0)
    ? (float)$produto->conversao_unitaria
    : 1;

// Quantidade final (convertida)
$qComFinal = $qComXml * $conversao;

// Agora, calcula o valor unitário de outras despesas usando a quantidade convertida
$outrasDespesas_unit = ($qComFinal > 0) ? $outrasDespesas / $qComFinal : 0;

// Substituição tributária e FCP
    $substituicaoTributaria_total = 0;
    $fcp = 0;
    // Verifica se existe o grupo ICMS no produto
    if (isset($item->imposto->ICMS)) {
        $arr = (array)$item->imposto->ICMS;
        $primeiroICMS = reset($arr); // Pega o primeiro ICMS dentro do ICMS

        if (isset($primeiroICMS->vICMSST)) {
             $substituicaoTributaria_total += (float) $primeiroICMS->vICMSST;
        }
        if (isset($primeiroICMS->vFCPST)) {
            $fcp += (float) $primeiroICMS->vFCPST;
        }
    }
// Dividir o total de FCP pela quantidade (qCom) para obter o valor unitário
$conversao = (!$produtoNovo && isset($produto->conversao_unitaria) && (float)$produto->conversao_unitaria > 0)
    ? (float)$produto->conversao_unitaria
    : 1;
$quantidade = (float)$item->prod->qCom * $conversao;

$fcp_unit = ($quantidade > 0) ? $fcp / $quantidade : 0;

// Soma o FCP ao total da substituição tributária
$substituicaoTributaria_total += $fcp;

// Definir a variável $totalSubstituicaoTributaria para formatação
$totalSubstituicaoTributaria = $substituicaoTributaria_total;

// Calcula o valor unitário da substituição tributária (dividido pela quantidade)
$substituicaoTributaria_unit = ($quantidade > 0) ? $substituicaoTributaria_total / $quantidade : 0;


// Seguro
$valorSeguro = 0;
if (isset($item->prod->vSeg)) {
    $valorSeguro = (float)$item->prod->vSeg;
}

// Total Seguro: valor total do XML para o seguro
$totalSeguro = isset($xml->NFe->infNFe->total->ICMSTot->vSeg)
               ? (float)$xml->NFe->infNFe->total->ICMSTot->vSeg
               : 0;

// Formata os valores para exibição
$valorSeguro_format = number_format((float)$valorSeguro, 6, ',', '');
$totalSeguro_format = number_format((float)$totalSeguro, 6, ',', '');


    // Nome do produto
    $nomeProduto = str_replace("'", "", (string)$item->prod->xProd);

    // Totais gerais do XML
    $totalIPI = isset($xml->NFe->infNFe->total->ICMSTot->vIPI) ? (float)$xml->NFe->infNFe->total->ICMSTot->vIPI : 0;
    $totalOutrasDespesas = isset($xml->NFe->infNFe->total->ICMSTot->vOutro) ? (float)$xml->NFe->infNFe->total->ICMSTot->vOutro : 0;

    // Adicione essa linha para capturar o desconto individual, se existir
    $descontoItem = isset($item->prod->vDesc) ? (float) str_replace(',', '.', $item->prod->vDesc) : 0;
    $descontoUnitario = (float)$descontoItem / ((float)$item->prod->qCom ?: 1);

    // Captura o valor do subtotal do item direto do XML
    $vProdItem = isset($item->prod->vProd)? (float) str_replace(',', '.', $item->prod->vProd): 0;

    // Cálculo do valor unitário do item
    $vUnComCalculado = (float)$item->prod->vUnCom;

    $custoLiquido = $vUnComCalculado + $vIpi + $vICMSST + $fcp_unit + $outrasDespesas_unit - $descontoUnitario;

    $subtotal = $custoLiquido * $quantidade;

    // Formatação dos valores
    $vIpi_format = number_format($vIpi, 6, ',', '');
    $outrasDespesas_format = number_format($outrasDespesas_unit, 6, ',', '');
    $substituicaoTributaria_format = number_format($substituicaoTributaria_unit, 6, ',', '');

     // Extração e conversão do desconto individual:
    $descontoItem = isset($item->prod->vDesc)
    ? (float) str_replace(',', '.', $item->prod->vDesc)
    : 0;

    // Quantidade original do XML
$qComXml = (float) $item->prod->qCom;

// Conversão do produto (se existir), senão 1
$conversao = (!$produtoNovo && isset($produto->conversao_unitaria) && (float)$produto->conversao_unitaria > 0)
    ? (float)$produto->conversao_unitaria
    : 1;

// Quantidade final (convertida)
$qComFinal = $qComXml * $conversao;
    
    // Calcule o desconto unitário se necessário:
    $quantidade = isset($item->prod->qCom) ? (float)$item->prod->qCom : 1;
    $descontoUnitario = $quantidade > 0 ? $descontoItem / $quantidade : 0;

    // Acumule o desconto total:
    $totalDesconto += $descontoItem;

    // Monta o array do item para exibição
    $novoItem = [
        'id'                           => !$produtoNovo ? $produto->id : 0,
        'codigo'                       => $codigo,
        'xProd'                        => $produtoNovo ? $nomeProduto : $produto->nome,
        'NCM'                          => $item->prod->NCM,
        'CEST'                         => $item->prod->CEST,
        'CFOP'                         => $item->prod->CFOP,
        'CFOP_entrada'                 => $this->getCfopEntrada($item->prod->CFOP),
        'uCom'                         => $item->prod->uCom,
        'custo_bruto'                  => number_format($vUnComCalculado, 6, '.', ''),
        'vUnCom'                       => number_format($custoLiquido, 6, '.', ''),
        'qCom'                         => str_replace(',', '.', $item->prod->qCom),
        'qCom_xml'                     => $qComXml,       // quantidade original
        'qCom_final'                   => $qComFinal,     // quantidade convertida
        'conversao_unitaria'           => $conversao, // guarda a conversão se quiser
        'vProd_item'                   => $vProdItem,
        'codBarras'                    => $item->prod->cEAN,
        'produtoNovo'                  => $produtoNovo,
        'codSiad'                      => $produtoNovo ? 0 : $codSiad,
        'produtoId'                    => $produtoNovo ? '0' : $produto->id,
        'valor_venda'                  => $produtoNovo ? 0 : $produto->valor_venda,
        'valor_compra'                 => $produtoNovo ? 0 : $produto->valor_compra,
        'percentual_lucro'             => $produtoNovo ? 0 : $produto->percentual_lucro,
        'valor_ipi'                    => $vIpi_format,
        'outras_despesas'              => $outrasDespesas_format,
        'total_ipi'                    => number_format($totalIPI, 6, ',', ''),
        'total_outras_despesas'        => number_format($totalOutrasDespesas, 6, ',', ''),
        'substituicao_tributaria'      => $substituicaoTributaria_format,
        'valor_seguro'                 => $valorSeguro_format,
        'total_substituicao_tributaria'=> number_format((float)$totalSubstituicaoTributaria, 2, ',', ''),
        'total_seguro'                 => number_format((float)$totalSeguro, 2, ',', ''),
        'desconto_unitario'            => number_format($descontoUnitario, 2, '.', ''),
        'desconto_item'                => (float)$descontoItem,
    ];

    array_push($itens, $novoItem);
}

$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
$dadosNf = [
    'chave' => $chave,
    'vBrut' => $xml->NFe->infNFe->total->ICMSTot->vProd,
    'vProd' => $xml->NFe->infNFe->total->ICMSTot->vNF,
    'indPag' => $xml->NFe->infNFe->ide->indPag,
    'nNf' => $xml->NFe->infNFe->ide->nNF,
    'vDesc' => $vDesc,
    'contSemRegistro' => $contSemRegistro,
    'data_emissao' => substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 16),
    'total_ipi' => $totalIPI,
    'total_outras_despesas' => $totalOutrasDespesas,
    'total_substituicao_tributaria' => number_format($totalSubstituicaoTributaria, 2, ',', '.'),
    'total_seguro' => $totalSeguro,
    'total_desconto' => $totalDesconto
];

			//Pagamento
				$fatura = [];
				if (!empty($xml->NFe->infNFe->cobr->dup))
				{
					foreach($xml->NFe->infNFe->cobr->dup as $dup) {
						$titulo = $dup->nDup;
						$vencimento = $dup->dVenc;
						$vencimento = explode('-', $vencimento);
						$vencimento = $vencimento[2]."/".$vencimento[1]."/".$vencimento[0];
						$vlr_parcela = number_format((double) $dup->vDup, 2, ".", "");	
						$parcela = [
							'numero' => (int)$titulo,
							'vencimento' => $vencimento,
							'valor_parcela' => $vlr_parcela,
							'categoria_conta_id' => 1,
							'rand' => rand(0, 10000)
						];
						array_push($fatura, $parcela);
					}
				}else{
					$vencimento = explode('-', substr($xml->NFe->infNFe->ide->dhEmi[0], 0,10));
					$vencimento = $vencimento[2]."/".$vencimento[1]."/".$vencimento[0];
					$parcela = [
						'numero' => 1,
						'vencimento' => $vencimento,
						'valor_parcela' => (float)$xml->NFe->infNFe->total->ICMSTot->vProd,
						'categoria_conta_id' => 1,
						'rand' => rand(0, 10000)
					];
					array_push($fatura, $parcela);
				}

			//upload
				$file = $request->file;
				$nameArchive = $chave . ".xml" ;

				$pathXml = $file->move(public_path('xml_entrada'), $nameArchive);

            //fim upload

				$categorias = Categoria::
				where('empresa_id', $this->empresa_id)
				->get();
				$unidadesDeMedida = Produto::unidadesMedida();

				$listaCSTCSOSN = Produto::listaCSTCSOSN();
				$listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
				$listaCST_IPI = Produto::listaCST_IPI();
				
				$config = ConfigNota::
				where('empresa_id', $this->empresa_id)
				->first();

				$anps = Produto::lista_ANP();
				$marcas = Marca::
				where('empresa_id', $this->empresa_id)
				->get();

				$subs = SubCategoria::
				select('sub_categorias.*')
				->join('categorias', 'categorias.id', '=', 'sub_categorias.categoria_id')
				->where('empresa_id', $request->empresa_id)
				->get();

			

				return view('compraFiscal/visualizaNota')
				->with('title', 'Nota Fiscal')
				->with('itens', $itens)
				->with('subs', $subs)
				->with('marcas', $marcas)
				->with('fatura', $fatura)
				->with('anps', $anps)
				->with('pathXml', $nameArchive)
				->with('compraFiscalJs', true)
				->with('idFornecedor', $idFornecedor)
				->with('dadosNf', $dadosNf)
				->with('listaCSTCSOSN', $listaCSTCSOSN)
				->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
				->with('listaCST_IPI', $listaCST_IPI)
				->with('config', $config)
				->with('unidadesDeMedida', $unidadesDeMedida)
				->with('categorias', $categorias)
				->with('dadosEmitente', $dadosEmitente)
				->with('dadosAtualizados', $dadosAtualizados);
			}else{
				session()->flash('mensagem_erro', $msgImport);
				return redirect("/compraFiscal");
			}

		}else{
			session()->flash('mensagem_erro', 'XML inválido!');
			return redirect("/compraFiscal");
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

	private function verificaFornecedor($cnpj){
		$forn = Fornecedor::verificaCadastrado($this->formataCnpj($cnpj));
		return $forn;
	}

	private function verificaAtualizacao($fornecedorEncontrado, $dadosEmitente){
		$dadosAtualizados = [];

		$verifica = $this->dadosAtualizados('Razao Social', $fornecedorEncontrado->razao_social,
			$dadosEmitente['razaoSocial']);
		if($verifica) array_push($dadosAtualizados, $verifica);

		$verifica = $this->dadosAtualizados('Nome Fantasia', $fornecedorEncontrado->nome_fantasia,
			$dadosEmitente['nomeFantasia']);
		if($verifica) array_push($dadosAtualizados, $verifica);

		$verifica = $this->dadosAtualizados('Rua', $fornecedorEncontrado->rua,
			$dadosEmitente['logradouro']);
		if($verifica) array_push($dadosAtualizados, $verifica);

		$verifica = $this->dadosAtualizados('Numero', $fornecedorEncontrado->numero,
			$dadosEmitente['numero']);
		if($verifica) array_push($dadosAtualizados, $verifica);

		$verifica = $this->dadosAtualizados('Bairro', $fornecedorEncontrado->bairro,
			$dadosEmitente['bairro']);
		if($verifica) array_push($dadosAtualizados, $verifica);

		$verifica = $this->dadosAtualizados('IE', $fornecedorEncontrado->ie_rg,
			$dadosEmitente['ie']);
		if($verifica) array_push($dadosAtualizados, $verifica);

		$this->atualizar($fornecedorEncontrado, $dadosEmitente);
		return $dadosAtualizados;
	}

	private function atualizar($fornecedor, $dadosEmitente){
		$fornecedor->razao_social = $dadosEmitente['razaoSocial'];
		$fornecedor->nome_fantasia = $dadosEmitente['nomeFantasia'];
		$fornecedor->rua = $dadosEmitente['logradouro'];
		$fornecedor->ie_rg = $dadosEmitente['ie'];
		$fornecedor->bairro = $dadosEmitente['bairro'];
		$fornecedor->numero = $dadosEmitente['numero'];
		$fornecedor->save();
	}

	private function dadosAtualizados($campo, $anterior, $atual){
		if($anterior != $atual){
			return $campo . " atualizado";
		} 
		return false;
	}


	private function cadastrarFornecedor($fornecedor){
		
		$result = Fornecedor::create([
			'razao_social' => $fornecedor['razaoSocial'],
			'nome_fantasia' => $fornecedor['nomeFantasia'],
			'rua' => $fornecedor['logradouro'],
			'numero' => $fornecedor['numero'],
			'bairro' => $fornecedor['bairro'],
			'cep' => $this->formataCep($fornecedor['cep']),
			'cpf_cnpj' => $this->formataCnpj($fornecedor['cnpj']),
			'ie_rg' => $fornecedor['ie'],
			'celular' => '*',
			'telefone' => $this->formataTelefone($fornecedor['fone']),
			'email' => '*',
			'cidade_id' => $fornecedor['cidade_id'],
			'empresa_id' => $this->empresa_id
		]);
		return $result->id;
	}

	private function formataCnpj($cnpj){
		$temp = substr($cnpj, 0, 2);
		$temp .= ".".substr($cnpj, 2, 3);
		$temp .= ".".substr($cnpj, 5, 3);
		$temp .= "/".substr($cnpj, 8, 4);
		$temp .= "-".substr($cnpj, 12, 2);
		return $temp;
	}

	private function formataCep($cep){
		$temp = substr($cep, 0, 5);
		$temp .= "-".substr($cep, 5, 3);
		return $temp;
	}

	private function formataTelefone($fone){
		$temp = substr($fone, 0, 2);
		$temp .= " ".substr($fone, 2, 4);
		$temp .= "-".substr($fone, 4, 4);
		return $temp;
	}

	public function salvarNfFiscal(Request $request){
		$nf = $request->nf;
		$result = Compra::create([
			'fornecedor_id' => $nf['fornecedor_id'],
			'usuario_id' => get_id_user(),
			'nf' => $nf['nNf'],
			'data_emissao' => $nf['data_emissao'],
			'observacao' => '',
			'lote' => $nf['lote'] != null ? $nf['lote'] : '',
			'valor' => str_replace(",", ".", $nf['valor_nf']),
			'desconto' => str_replace(",", ".", $nf['desconto']),
			'xml_path' => $nf['xml_path'],
			'estado' => 'NOVO',
			'numero_emissao' => 0,
			'categoria_id' => 1,
			'categoria_conta_id' => 1,
			'chave' => $nf['chave'],
			'empresa_id' => $this->empresa_id,
			'filial_id' => $nf['filial_id'] != -1 ? $nf['filial_id'] : null,
			'valor_ipi' => str_replace(',', '.', $nf['total_ipi']),
			'outras_despesas' => str_replace(',', '.', $nf['total_outras_despesas']),
			'substituicao_tributaria' => str_replace(',', '.', $nf['total_substituicao_tributaria']),
			'valor_seguro' => str_replace(',', '.', $nf['total_seguro']),
		]);
		echo json_encode($result);
	}

	public function salvarItem(Request $request){
		$prod = $request->produto;

		$produtoBD = Produto::
		where('id', (int) $prod['produto_id'])
		->where('empresa_id', $this->empresa_id)
		->first();

		$result = ItemCompra::create([
			'compra_id' => (int) $prod['compra_id'],
			'produto_id' => (int) $prod['produto_id'],
			'quantidade' =>  str_replace(",", ".", $prod['quantidade']),
			'valor_unitario' => str_replace(",", ".", $prod['valor']),
			'unidade_compra' => $prod['unidade'],
			'cfop_entrada' => $prod['cfop_entrada'],
			'codigo_siad' => $prod['said'] ?? '',
			'valor_ipi' => str_replace(',', '.', $prod['valor_ipi']),
			'outras_despesas' => str_replace(',', '.', $prod['outras_despesas']),
			'substituicao_tributaria' => str_replace(',', '.', $prod['substituicao_tributaria']),
			'valor_seguro' => str_replace(',', '.', $prod['valor_seguro']),
		]);

		$produtoBD->nome = $prod['nome'];
		$produtoBD->conversao_unitaria = $prod['conversao_unitaria'] ?? 1;
		if($prod['valor_venda'] > 0){
			$produtoBD->valor_venda = str_replace(",", ".", $prod['valor_venda']);
		}

		if($prod['valor_venda'] > 0){
			$produtoBD->valor_compra = str_replace(",", ".", $prod['valor_venda']);
		}

		$produtoBD->save();

		$valor = $produtoBD->valor_venda > 0 ? $produtoBD->valor_venda : $prod['valor'];
		$stockMove = new StockMove();
		$stockMove->pluStock((int) $prod['produto_id'], 
          __replace($prod['quantidade']), // 🔥 Removida a multiplicação pela conversão_unitaria
          __replace($valor), $prod['filial_id']);

		echo json_encode($result);
	}

	private function vincularOuMapearProduto($xmlItem, $idFornecedor)
{
    // Supondo que:
    // - O código do produto no XML será usado como 'id_xml' (por exemplo, $xmlItem->prod->cProd)
    // - O código de barras do XML será usado como 'codBarras_xml' (por exemplo, $xmlItem->prod->cEAN)
    
    $id_xml = trim((string)$xmlItem->prod->cProd);
    $codBarras_xml = trim((string)$xmlItem->prod->cEAN);

    // Se o código de barras for "SEM GTIN", faça somente a busca na tabela de mapeamento:
    if (strtoupper($codBarras_xml) == 'SEM GTIN') {
        $mapping = ProdutoMapeamento::where('id_fornecedor', $idFornecedor)
                    ->where('id_xml', $id_xml)
                    ->where('codBarras_xml', $codBarras_xml)
                    ->first();

        if ($mapping) {
            // Já existe mapeamento; retorna o id do produto vinculado
            return $mapping->id_produto;
        } else {
            // Não existe mapeamento; retorna null para indicar que o produto deverá ser vinculado manualmente
            return null;
        }
    }

    // Caso o código de barras seja diferente de "SEM GTIN"
    // Primeiro, verifica na tabela produto_mapeamento:
    $mapping = ProdutoMapeamento::where('id_fornecedor', $idFornecedor)
                ->where('id_xml', $id_xml)
                ->where('codBarras_xml', $codBarras_xml)
                ->first();

    if ($mapping) {
        // Produto já está mapeado, retorna o id do produto
        return $mapping->id_produto;
    } else {
        // Não encontrou na tabela de mapeamento: procurar o produto pelo código de barras no sistema
        $produto = Produto::where('codBarras', $codBarras_xml)
                    ->where('empresa_id', $this->empresa_id)
                    ->first();

        if ($produto) {
            // Produto encontrado no sistema; cria registro na tabela produto_mapeamento
            ProdutoMapeamento::create([
                'id_xml' => $id_xml,
                'codBarras_xml' => $codBarras_xml,
                'id_fornecedor' => $idFornecedor,
                'id_produto' => $produto->id,
                'codBarras_produto' => $produto->codBarras, // ou outro valor conforme sua necessidade
                'empresa_id' => $this->empresa_id,
                'filial_id' => null, // ou defina conforme sua lógica
            ]);

            // Retorna o id do produto encontrado
            return $produto->id;
        } else {
            // Produto não encontrado nem na tabela de mapeamento nem no sistema; 
            // retorna null para que o item seja tratado (exibido em vermelho ou fique disponível para cadastro/vinculação manual)
            return null;
        }
    }
}

public function salvarProdutoDaNota(Request $request)
{
    Log::info("📥 Dados Recebidos no Controller", $request->all());

    $idFornecedor = (int) $request->input('id_fornecedor', 0);
    Log::info("🛠️ ID Fornecedor Convertido:", ['id_fornecedor' => $idFornecedor]);

    $produto = $request->produto;

    // Captura 'id_xml' e 'codBarras_xml' (valor original do XML)
    $id_xml = isset($produto['referencia_xml']) ? preg_replace('/\s+/', '', (string)$produto['referencia_xml']) : null;
    $codBarras_xml = isset($produto['codBarras_xml']) ? trim((string)$produto['codBarras_xml']) : '';

    // Se o campo codBarras_xml estiver vazio, retorne um erro
    if (empty($codBarras_xml)) {
        Log::error("❌ O campo codBarras_xml (valor original do XML) não foi preenchido.");
        return response()->json(['message' => 'O campo codBarras_xml (valor original do XML) é obrigatório.'], 400);
    }

    Log::info("🔹 ID XML Capturado:", ['id_xml' => $id_xml]);
    Log::info("🔹 Código de Barras XML Capturado:", ['codBarras_xml' => $codBarras_xml]);

    // Se for SEM GTIN e não houver um ID_XML, cria um identificador manual
    if ($codBarras_xml === "SEM GTIN" && empty($id_xml)) {
        $id_xml = "MANUAL-" . time();
    }
    
    // Se o valor recebido para subCategoriaId for '0' ou vazio, definir como NULL
    if (empty($subCategoriaId) || $subCategoriaId == 0) {
        $subCategoriaId = null;
    }
    
    $marcaId = $produto['marca_id'] ?? null;
    // Se o valor recebido para marcaId for '0', vazio ou inexistente na tabela, definir como NULL
    if (empty($marcaId) || !DB::table('marcas')->where('id', $marcaId)->exists()) {
        $marcaId = null;
    }

    $natureza = Produto::firstNatureza($this->empresa_id);

    // Conversão e formatação dos valores monetários
    $valorVenda = str_replace(",", ".", str_replace(".", "", $produto['valorVenda']));
    $valorCompra = isset($produto['valorCompra']) ? (float) __replace($produto['valorCompra']) : 0;

    // Processamento do CFOP
    $cfop = $produto['cfop'] ?? '';
    $digito = substr($cfop, 0, 1);
    $cfopEstadual = ($digito == '5') ? $cfop : '5' . substr($cfop, 1);
    $cfopInterEstadual = ($digito == '6') ? $cfop : '6' . substr($cfop, 1);

    $conversaoUnitaria = (int) $produto['conversao_unitaria'];

    // Se o usuário digitou um código de barras (campo visível), usa-o; caso contrário, usa o original
    $codBarras_produto = !empty($produto['codBarras']) ? trim((string)$produto['codBarras']) : $codBarras_xml;

    DB::beginTransaction();
    try {
        // Criando o produto
        $result = Produto::create([
            'nome'                     => $produto['nome'] ?? '',
            'NCM'                      => $produto['ncm'] ?? '',
            'valor_venda'              => $valorVenda,
            'valor_compra'             => $valorCompra,
            'valor_livre'              => false,
            'percentual_lucro'         => $produto['percentual_lucro'] ?? 0,
            'custo_assessor'           => $produto['custo_assessor'] ?? 0,
            'conversao_unitaria'       => $conversaoUnitaria,
            'categoria_id'             => $produto['categoria_id'] ?? 0,
            'marca_id'                 => $marcaId,
            'sub_categoria_id'         => $subCategoriaId,
            'unidade_compra'           => $produto['unidadeCompra'] ?? '',
            'unidade_venda'            => $produto['unidadeVenda'] ?? '',
            // Aqui usamos o valor digitado pelo usuário se existir; caso contrário, o original
            'codBarras'                => $codBarras_produto,
            'composto'                 => false,
            'CST_CSOSN'                => $produto['CST_CSOSN'] ?? '',
            'CST_PIS'                  => $produto['CST_PIS'] ?? '',
            'CST_COFINS'               => $produto['CST_COFINS'] ?? '',
            'CST_IPI'                  => $produto['CST_IPI'] ?? '',
            'perc_icms'                => __replace($produto['perc_icms'] ?? '0'),
            'perc_pis'                 => __replace($produto['perc_pis'] ?? '0'),
            'perc_cofins'              => __replace($produto['perc_cofins'] ?? '0'),
            'perc_ipi'                 => __replace($produto['perc_ipi'] ?? '0'),
            'CFOP_saida_estadual'      => $cfopEstadual,
            'CFOP_saida_inter_estadual'=> $cfopInterEstadual,
            'referencia'               => $produto['referencia'] ?? '',
            'empresa_id'               => $this->empresa_id,
            'gerenciar_estoque'        => $produto['gerenciar_estoque'] ?? 0,
            'reajuste_automatico'      => 0,
            'estoque_minimo'           => $produto['estoque_minimo'] ?? 0,
            'inativo'                  => $produto['inativo'] ?? 0,
            'CEST'                     => $produto['CEST'] ?? '',
            'codigo_anp'               => $produto['anp'] ?? '',
            'perc_glp'                 => $produto['perc_glp'] ?? 0,
            'perc_gnn'                 => $produto['perc_gnn'] ?? 0,
            'perc_gni'                 => $produto['perc_gni'] ?? 0,
            'valor_partida'            => __replace($produto['valor_partida'] ?? '0'),
            'unidade_tributavel'       => $produto['unidade_tributavel'] ?? '',
            'quantidade_tributavel'    => $produto['quantidade_tributavel'] ?? 1,
            'largura'                  => __replace($produto['largura'] ?? '0'),
            'altura'                   => __replace($produto['altura'] ?? '0'),
            'comprimento'              => __replace($produto['comprimento'] ?? '0'),
            'peso_liquido'             => __replace($produto['peso_liquido'] ?? '0'),
            'peso_bruto'               => __replace($produto['peso_bruto'] ?? '0'),
            'locais'                   => isset($produto['filial_id']) && $produto['filial_id'] 
                                          ? '["' . $produto['filial_id'] . '"]' : '["-1"]',
            'referencia_xml'           => $id_xml,
        ]);

        Log::info("✅ Produto Criado com ID:", ['produto_id' => $result->id]);

        // Definindo filial corretamente
        $filialId = isset($produto['filial_id']) && $produto['filial_id'] != -1 ? $produto['filial_id'] : null;

        Log::info("🛠️ Tentando salvar ProdutoMapeamento", [
            'id_xml' => $id_xml,
            'codBarras_xml' => $codBarras_xml,
            'id_fornecedor' => $idFornecedor,
            'id_produto' => $result->id,
            'codBarras_produto' => $codBarras_produto,
            'empresa_id' => $this->empresa_id,
            'filial_id' => $filialId
        ]);

        // Verifica se o mapeamento já existe considerando id_xml, codBarras_xml e id_fornecedor
        $existingMapping = ProdutoMapeamento::where('id_xml', $id_xml)
            ->where('codBarras_xml', $codBarras_xml)
            ->where('id_fornecedor', $idFornecedor)
            ->first();

        if (!$existingMapping) {
            ProdutoMapeamento::create([
                'id_xml'            => $id_xml,
                'codBarras_xml'     => $codBarras_xml,          // Valor original do XML
                'id_fornecedor'     => $idFornecedor,
                'id_produto'        => $result->id,
                'codBarras_produto' => $codBarras_produto,      // Valor digitado/alterado pelo usuário
                'empresa_id'        => $this->empresa_id,
                'filial_id'         => $filialId,
            ]);
            Log::info("✅ ProdutoMapeamento salvo com sucesso!");
        } else {
            Log::info("⚠️ ProdutoMapeamento já existente, não foi criado.");
        }

        DB::commit();
        return response()->json($result);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("❌ Erro ao salvar ProdutoMapeamento", ['erro' => $e->getMessage()]);
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

public function vincularProdutoMapeamento(Request $request)
{
    try {
        Log::info("📥 Dados Recebidos para Vincular Produto", $request->all());

        // Captura os dados da requisição
        $idProduto = (int) $request->input('id_produto');
        $idXml = trim((string) $request->input('id_xml'));
        $idFornecedor = (int) $request->input('id_fornecedor') ?? 0;
        $codBarrasXml = trim((string) $request->input('codBarras_xml')) ?? 'SEM GTIN';
        $codBarrasProduto = $request->input('codBarras_produto');

        if (empty($codBarrasProduto) || trim($codBarrasProduto) == '') {
          $codBarrasProduto = 'SEM GTIN';
        }

        $empresaId = (int) $request->input('empresa_id');
        $filialId = $request->input('filial_id');

        // Se a filial vier como "-1", definir como NULL
        if ($filialId == -1) {
            $filialId = null;
        }

        // Remove "_SEM GTIN" do id_xml, se existir
        $idXml = str_replace("_SEM GTIN", "", $idXml);

        Log::info("📌 ID XML após processamento: " . $idXml);

        // 🔎 Verifica se já existe um vínculo com os mesmos ID_XML, CodBarras_XML e ID_FORNECEDOR
        $existe = \App\Models\ProdutoMapeamento::where('id_xml', $idXml)
            ->where('codBarras_xml', $codBarrasXml)
            ->where('id_fornecedor', $idFornecedor)
            ->first();

        if ($existe) {
            Log::warning("⚠️ Produto já está vinculado! ID_XML: {$idXml}, CodBarras_XML: {$codBarrasXml}, Fornecedor: {$idFornecedor}");
            return response()->json([
                'success' => false,
                'message' => 'Este produto já está vinculado a este fornecedor e código de barras!'
            ], 400);
        }

        // 🔹 Criar novo vínculo
        $novoProduto = \App\Models\ProdutoMapeamento::create([
            'id_xml'            => $idXml,
            'codBarras_xml'     => $codBarrasXml,
            'id_fornecedor'     => $idFornecedor,
            'id_produto'        => $idProduto,
            'codBarras_produto' => $codBarrasProduto,
            'empresa_id'        => $empresaId,
            'filial_id'         => $filialId,
        ]);

        Log::info("✅ Novo vínculo criado com sucesso!", ['id' => $novoProduto->id]);

        return response()->json([
            'success' => true,
            'message' => 'Produto vinculado com sucesso!'
        ]);

    } catch (\Exception $e) {
        Log::error("❌ Erro ao vincular produto: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao vincular o produto! ' . $e->getMessage()
        ], 500);
    }
}

public function getProdutoPeloXml(Request $request)
{
    // Logando os parâmetros recebidos
    Log::info('🔍 Buscando produto mapeado com:', [
        'id_xml' => $request->id_xml,
        'codBarras_xml' => $request->codBarras_xml,
        'id_fornecedor' => $request->id_fornecedor
    ]);

    // Verifica se todos os parâmetros foram enviados
    if (!$request->has(['id_xml', 'codBarras_xml', 'id_fornecedor'])) {
        Log::error('❌ Parâmetros incompletos na requisição');
        return response()->json(['error' => 'Parâmetros incompletos'], 400);
    }

    try {
        // Buscar o produto na tabela produto_mapeamento
        $produtoMapeado = DB::table('produto_mapeamento')
            ->where('id_xml', trim($request->id_xml))
            ->where('codBarras_xml', trim($request->codBarras_xml))
            ->where('id_fornecedor', $request->id_fornecedor)
            ->first();

        // Se o produto for encontrado, retorna o ID do produto no sistema
        if ($produtoMapeado) {
            Log::info('✅ Produto encontrado:', ['id_produto' => $produtoMapeado->id_produto]);
            return response()->json(['id_produto' => $produtoMapeado->id_produto]);
        } else {
            Log::warning('⚠️ Produto não encontrado para os critérios informados.');
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }
    } catch (\Exception $e) {
        Log::error('❌ Erro ao buscar produto:', ['message' => $e->getMessage()]);
        return response()->json(['error' => 'Erro interno no servidor'], 500);
    }
}

public function atualizarPrecoVenda(Request $request)
{
    // Recebe o id do produto vindo do AJAX
    $produtoId = $request->input('produto_id');
    $novoPercentual = $request->input('porcentagem_venda');
    $novoPrecoVenda = $request->input('preco_venda');
    // O valor de base_calculo é recebido mas não será salvo no banco
    // $baseCalculo = $request->input('base_calculo');

    // Busca o produto para a empresa atual
    $produto = \App\Models\Produto::where('id', $produtoId)
                ->where('empresa_id', $this->empresa_id)
                ->first();

    if (!$produto) {
        return response()->json(['message' => 'Produto não encontrado'], 404);
    }

    // Atualiza apenas as colunas existentes
    $produto->percentual_lucro = $novoPercentual;
    $produto->valor_venda = str_replace(',', '.', $novoPrecoVenda);
    // NÃO atualiza base_calculo, pois ela só serve para cálculo na tela
    // $produto->base_calculo = $baseCalculo; // Remova ou comente essa linha

    $produto->save();

    return response()->json(['status' => 'sucesso']);
}

public function atualizarPrecoVendaEmMassa(Request $request)
{
    $produtosAtualizados = $request->input('produtos'); // Espera um array de produtos
    $empresa_id = $request->input('empresa_id'); // Opcional: para filtrar por empresa

    if (!$produtosAtualizados || !is_array($produtosAtualizados)) {
        return response()->json(['message' => 'Nenhum produto enviado'], 400);
    }

    foreach ($produtosAtualizados as $p) {
        // Aqui supomos que o campo "produto_id", "porcentagem_venda" e "preco_venda" estão presentes
        \App\Models\Produto::where('id', $p['produto_id'])
            ->where('empresa_id', $empresa_id) // se necessário
            ->update([
                'percentual_lucro' => $p['porcentagem_venda'],
                'valor_venda'      => $p['preco_venda']
            ]);
    }

    return response()->json(['status' => 'sucesso', 'atualizados' => count($produtosAtualizados)]);
}

}