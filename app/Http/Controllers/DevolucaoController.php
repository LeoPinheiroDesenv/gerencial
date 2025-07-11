<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Devolucao;
use App\Models\ItemDevolucao;
use App\Models\Fornecedor;
use App\Models\Cidade;
use App\Models\Produto;
use App\Models\Tributacao;
use App\Models\Transportadora;
use App\Models\NaturezaOperacao;
use App\Models\ConfigNota;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\Daevento;
use App\Services\DevolucaoService;
use App\Helpers\StockMove;
use App\Models\EscritorioContabil;
use App\Models\EmailConfig;
use Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;

class DevolucaoController extends Controller
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
		$devolucoes = Devolucao::
		where('empresa_id', $this->empresa_id)
		->orderBy('id', 'desc')
		->paginate(20);

		return view('devolucao/list')
		->with('devolucoes', $devolucoes)
		->with('devolucaoNF', true)
		->with('links', true)
		->with('title', 'Lista de Devoluções');
	}

	public function new(){
		return view('devolucao/new')
		->with('title', 'Nova Devolução');
	}

	private function validaChave($chave){
		$chave = substr($chave, 3, 44);
		$cp = Devolucao::
		where('empresa_id', $this->empresa_id)
		->where('chave_nf_entrada', $chave)
		->where('estado', 1)
		->first();
		return $cp == null ? true : false;
	}

	public function renderizarXml(Request $request){
		if ($request->hasFile('file')){
			$arquivo = $request->hasFile('file');
			$xml = simplexml_load_file($request->file);

			if(!isset($xml->NFe->infNFe)){
				session()->flash('mensagem_erro', 'Este xml não é uma NFe');
				return redirect("/devolucao/nova");
			}
			if(!$this->validaChave($xml->NFe->infNFe->attributes()->Id)){
				session()->flash('mensagem_erro', 'Este XML de devolução já esta incluido no sistema com estado aprovado!');
				// return redirect("/devolucao/nova");
			}

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

			$transportadora = null;
			$transportadoraDoc = null;

			if($xml->NFe->infNFe->transp->transporta){

				$transp = $xml->NFe->infNFe->transp->transporta;
				$transportadoraDoc = (int)$transp->CNPJ;

				$vol = $xml->NFe->infNFe->transp->vol;
				$modFrete = $xml->NFe->infNFe->transp->modFrete;
				$veicTransp = $xml->NFe->infNFe->transp->veicTransp;

				$transportadora = [
					'transportadora_nome' => (string)$transp->xNome,
					'transportadora_cidade' => (string)$transp->xMun,
					'transportadora_uf' => (string)$transp->UF,
					'transportadora_cpf_cnpj' => (string)$transp->CNPJ,
					'transportadora_ie' => (int)$transp->IE,
					'transportadora_endereco' => (string)$transp->xEnder,
					'frete_quantidade' => (float)$vol->qVol,
					'frete_especie' => (string)$vol->esp,
					'frete_marca' => '',
					'frete_numero' => 0,
					'frete_tipo' => (int)$modFrete,
					'veiculo_placa' => $veicTransp->placa,
					'veiculo_uf' => $veicTransp->UF,
					'frete_peso_bruto' => (float)$vol->pesoB, 
					'frete_peso_liquido' => (float)$vol->pesoL,
					'despesa_acessorias' => (float)$xml->NFe->infNFe->total->ICMSTot->vOutro
				];
			}

			$vFrete = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vFrete, 
				2, ",", ".");

			$vDesc = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");

			$idFornecedor = 0;
			$fornecedorEncontrado = $this->verificaFornecedor($dadosEmitente['cnpj'] == '' ? $dadosEmitente['cpf'] : $dadosEmitente['cnpj']);
			$dadosAtualizados = [];
			if($fornecedorEncontrado){
				$idFornecedor = $fornecedorEncontrado->id;
				$dadosAtualizados = $this->verificaAtualizacao($fornecedorEncontrado, $dadosEmitente);
			}else{

				array_push($dadosAtualizados, "Fornecedor cadastrado com sucesso");
				$idFornecedor = $this->cadastrarFornecedor($dadosEmitente);
			}

			$idTransportadora = 0;

			if($transportadoraDoc != null){

				$transportadoraEncontrada = $this->verificaTransportadora($transportadoraDoc);
				if($transportadoraEncontrada){
					$idTransportadora = $transportadoraEncontrada->id;
				}else{
					array_push($dadosAtualizados, 
						"Transportadora cadastrada com sucesso");
					$idTransportadora = $this->cadastrarTransportadora($transportadora);
				}
			}

			$seq = 0;
			$itens = [];
			$contSemRegistro = 0;

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			$tributacao = Tributacao::
			where('empresa_id', $this->empresa_id)
			->first();

			foreach($xml->NFe->infNFe->det as $item) {
					// var_dump($item);
					// $item = [
					// 	'codigo' => $item->prod->cProd,
					// 	'xProd' => $item->prod->xProd,
					// 	'NCM' => $item->prod->NCM,
					// 	'CFOP' => $item->prod->CFOP,
					// 	'uCom' => $item->prod->uCom,
					// 	'vUnCom' => $item->prod->vUnCom,
					// 	'qCom' => $item->prod->qCom,
					// 	'codBarras' => $item->prod->cEAN,
					// 	'cst_csosn' => $config->CST_CSOSN_padrao,
					// 	'cst_pis' => $config->CST_PIS_padrao,
					// 	'cst_cofins' => $config->CST_COFINS_padrao,
					// 	'cst_ipi' => $config->CST_IPI_padrao,
					// 	'perc_icms' => $tributacao->icms,
					// 	'perc_pis' => $tributacao->pis,
					// 	'perc_cofins' => $tributacao->cofins,
					// 	'perc_ipi' => $tributacao->ipi

					// ];

				$trib = Devolucao::getTrib($item->imposto);
				$tagComb = null;
				if($item->prod->comb){
					$tagComb = $item->prod->comb;
				}

				$item = [
					'codigo' => $item->prod->cProd,
					'randDelete' => rand(10000, 99999),
					'xProd' => $item->prod->xProd,
					'NCM' => $item->prod->NCM,
					'cBenef' => (string)$item->prod->cBenef,
					'vFrete' => $item->prod->vFrete ?? 0,
					'CFOP' => $item->prod->CFOP,
					'uCom' => $item->prod->uCom,
					'unidade_tributavel' => (string)$item->prod->uTrib,
					'quantidade_tributavel' => (float)$item->prod->qTrib,
					'vUnCom' => $item->prod->vUnCom,
					'sub_total' => (float)$item->prod->vProd,
					'qCom' => $item->prod->qCom,
					'codBarras' => $item->prod->cEAN ?? '',
					'cst_csosn' => $trib['cst_csosn'],
					'cst_pis' => $trib['cst_pis'],
					'cst_cofins' => $trib['cst_cofins'],
					'cst_ipi' => $trib['cst_ipi'],
					'perc_icms' => $trib['pICMS'],
					'perc_pis' => $trib['pPIS'],
					'perc_cofins' => $trib['pCOFINS'],
					'perc_ipi' => $trib['pIPI'],
					'pRedBC' => $trib['pRedBC'],
					'modBCST' => $trib['modBCST'],
					'vBCST' => $trib['vBCST'],
					'pICMSST' => $trib['pICMSST'],
					'vICMSST' => $trib['vICMSST'],
					'vBCSTRet' => $trib['vBCSTRet'],
					'pMVAST' => $trib['pMVAST'],
					'pST' => $trib['pST'],
					'vICMSSubstituto' => $trib['vICMSSubstituto'],
					'vICMSSTRet' => $trib['vICMSSTRet'],
					'orig' => $trib['orig'],
					'codigo_anp' => $tagComb != null ? (string)$tagComb->cProdANP : '',
					'descricao_anp' => $tagComb != null ? (string)$tagComb->descANP : '',
					'uf_cons' => $tagComb != null ? (string)$tagComb->UFCons : '',
					'perc_glp' => $tagComb != null ? (float)$tagComb->pGLP : 0,
					'perc_gnn' => $tagComb != null ? (float)$tagComb->pGNn : 0,
					'perc_gni' => $tagComb != null ? (float)$tagComb->pGNi : 0,
					'valor_partida' => $tagComb != null ? (float)$tagComb->vPart : 0,
					'cest' => isset($item->prod->CEST) ? (string)$item->prod->CEST : '',
					'qBCMonoRet' => $trib['qBCMonoRet'],
					'adRemICMSRet' => $trib['adRemICMSRet'],
					'vICMSMonoRet' => $trib['vICMSMonoRet']
				];
				array_push($itens, $item);
			}

			// print_r($itens);die;
			$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
			$dadosNf = [
				'chave' => $chave,
				'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
				'indPag' => $xml->NFe->infNFe->ide->indPag,
				'nNf' => $xml->NFe->infNFe->ide->nNF,
				'vFrete' => $vFrete,
				'vDesc' => $vDesc,
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
					$vlr_parcela = number_format((double) $dup->vDup, 2, ",", ".");	

					$parcela = [
						'numero' => $titulo,
						'vencimento' => $vencimento,
						'valor_parcela' => $vlr_parcela
					];
					array_push($fatura, $parcela);
				}
			}

			//upload
			$file = $request->file;
			$nameArchive = $chave . ".xml" ;

			$pathXml = $file->move(public_path('xml_devolucao_entrada'), $nameArchive);

            //fim upload

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			$naturezas = NaturezaOperacao::
			where('empresa_id', $this->empresa_id)
			->get();

			$transportadoras = Transportadora::
			where('empresa_id', $this->empresa_id)
			->get();

			$tipoFrete = 0;
			if($transportadora != null){
				$tipoFrete = $transportadora['frete_tipo'];
			}

			return view('devolucao/visualizaNota')
			->with('title', 'Devolução')
			->with('itens', $itens)
			->with('fatura', $fatura)
			->with('tipoFrete', $tipoFrete)
			->with('devolucaoJs', true)
			->with('pathXml', $nameArchive)
			->with('idFornecedor', $idFornecedor)
			->with('dadosNf', $dadosNf)
			->with('naturezas', $naturezas)
			->with('config', $config)
			->with('cidade', $cidade)
			->with('transportadora', $transportadora)
			->with('dadosEmitente', $dadosEmitente)
			->with('transportadoras', $transportadoras)
			->with('idTransportadora', $idTransportadora)
			->with('dadosAtualizados', $dadosAtualizados);
			
		}else{
			session()->flash('mensagem_erro', 'XML inválido!');
			return redirect("/devolucao/nova");
		}
	}

	private function verificaFornecedor($doc){
		if(strlen($doc) == 14){
			$doc = $this->formataCnpj($doc);
		}else{
			$doc = $this->formataCpf($doc);
		}
		$forn = Fornecedor::verificaCadastrado($doc);
		return $forn;
	}

	private function verificaTransportadora($cnpj){
		$transp = Transportadora::verificaCadastrado($cnpj);
		return $transp;
	}

	private function cadastrarFornecedor($fornecedor){

		$doc = $fornecedor['cnpj'] == '' ? $fornecedor['cpf'] : $fornecedor['cnpj'];
		if(strlen($doc) == 14){
			$doc = $this->formataCnpj($doc);
		}else{
			$doc = $this->formataCpf($doc);
		}

		$result = Fornecedor::create([
			'razao_social' => $fornecedor['razaoSocial'],
			'nome_fantasia' => $fornecedor['nomeFantasia'],
			'rua' => $fornecedor['logradouro'],
			'numero' => $fornecedor['numero'],
			'bairro' => $fornecedor['bairro'],
			'cep' => $this->formataCep($fornecedor['cep']),
			'cpf_cnpj' => $doc,
			'ie_rg' => $fornecedor['ie'],
			'celular' => '*',
			'telefone' => $this->formataTelefone($fornecedor['fone']),
			'email' => '*',
			'cidade_id' => $fornecedor['cidade_id'],
			'empresa_id' => $this->empresa_id
		]);
		return $result->id;
	}

	private function cadastrarTransportadora($transp){
		
		$cidade = Cidade::
		where('nome', $transp['transportadora_cidade'])
		->first();

		if($cidade == null){
			$cidade = Cidade::
			where('uf', $transp['transportadora_uf'])
			->first();
		}

		$result = Transportadora::create([
			'razao_social' => $transp['transportadora_nome'],
			'cnpj_cpf' => $transp['transportadora_cpf_cnpj'],
			'logradouro' => $transp['transportadora_endereco'],
			'cidade_id' => $cidade == null ? 1 : $cidade->id,
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

	private function formataCpf($cnpj){
		$temp = substr($cnpj, 0, 3);
		$temp .= ".".substr($cnpj, 3, 3);
		$temp .= ".".substr($cnpj, 6, 3);
		$temp .= "-".substr($cnpj, 9, 2);
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

	private function dadosAtualizados($campo, $anterior, $atual){
		if($anterior != $atual){
			return $campo . " atualizado";
		} 
		return false;
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

	public function salvar(Request $request){
		try{
			$result = DB::transaction(function () use ($request) {
				$data = $request->data;
				$transportadora = $data['transportadora'];

				$devolucao = Devolucao::create([
					'fornecedor_id' => $data['fornecedorId'],
					'usuario_id' => get_id_user(),
					'natureza_id' => $data['natureza'],
					'valor_integral' => str_replace(",", ".", $data['valor_integral']),
					'valor_devolvido' => str_replace(",", ".", $data['valor_devolvido']),
					'motivo' => $data['motivo'] ?? '',
					'observacao' => $data['obs'] ?? '',
					'estado' => 0,
					'devolucao_parcial' => $data['devolucao_parcial'] == true ? 1 : 0,
					'chave_nf_entrada' => $data['xmlEntrada'],
					'nNf' => $data['nNf'],
					'vFrete' => str_replace(",", ".", $data['vFrete']),
					'vDesc' => str_replace(",", ".", $data['vDesc']),
					'chave_gerada' => '',
					'numero_gerado' => 0,
					'tipo' => $data['tipo'],
					'empresa_id' => $this->empresa_id,
					'transportadora_nome' => $transportadora['transportadora_nome'] ?? '',
					'transportadora_cidade' => $transportadora['transportadora_cidade'] ?? '',
					'transportadora_uf' => $transportadora['transportadora_uf'] ?? '',
					'transportadora_cpf_cnpj' => $transportadora['transportadora_cpf_cnpj'] ?? '',
					'transportadora_ie' => $transportadora['transportadora_ie'] ?? '',
					'transportadora_endereco' => $transportadora['transportadora_endereco'] ?? '',
					'frete_quantidade' => $data['qtd'] ? __replace($data['qtd']) : 0,
					'frete_especie' => $data['especie'] ?? '',
					'frete_marca' => $transportadora['frete_marca'] ?? '',
					'frete_numero' => $transportadora['frete_numero'] ?? 0,
					'frete_tipo' => $data['tipoFrete'] ?? 0,
					'veiculo_placa' => $data['placa'] ?? '',
					'veiculo_uf' => $data['ufPlaca'] ?? '',
					'frete_peso_bruto' => $data['pBruto'] ? __replace($data['pBruto']) : 0, 
					'frete_peso_liquido' => $data['pLiquido'] ? __replace($data['pLiquido']) : 0,
					'despesa_acessorias' => $data['vOutros'] ? __replace($data['vOutros']) : 0,
					'transportadora_id' => $data['transportadora_id'] > 0 ? $data['transportadora_id'] : NULL
				]);

				$stockMove = new StockMove();
				foreach($data['itens'] as $i){
					$item = ItemDevolucao::create([
						'cod' => $i['codigo'],
						'nome' => $i['xProd'],
						'ncm' => $i['NCM'],
						'cBenef' => $i['cBenef'] ?? '',
						'cfop' => $i['CFOP'],
						'valor_unit' => $i['vUnCom'],
						'sub_total' => $i['sub_total'],
						'vFrete' => $i['vFrete'] ?? 0,
						'quantidade' => $i['qCom'],
						'item_parcial' => $i['parcial'],
						'unidade_medida' => $i['uCom'],
						'codBarras' => $i['codBarras'] ?? '',
						'devolucao_id' => $devolucao->id,
						'cst_csosn' => $i['cst_csosn'],
						'cst_pis' => $i['cst_pis'],
						'cst_cofins' => $i['cst_cofins'],
						'cst_ipi' => $i['cst_ipi'] ?? '99',
						'perc_icms' => $i['perc_icms'],
						'perc_pis' => $i['perc_pis'],
						'perc_cofins' => $i['perc_cofins'],
						'perc_ipi' => $i['perc_ipi'],
						'pRedBC' => $i['pRedBC'] ?? 0,
						'modBCST' => $i['modBCST'] ?? 0,
						'vBCST' => $i['vBCST'] ?? 0,
						'pICMSST' => $i['pICMSST'] ?? 0,
						'vICMSST' => $i['vICMSST'] ?? 0,
						'pMVAST' => $i['pMVAST'] ?? 0,
						'vBCSTRet' => $i['vBCSTRet'] ?? 0,

						'pST' => $i['pST'] ?? 0,
						'vICMSSubstituto' => $i['vICMSSubstituto'] ?? 0,
						'vICMSSTRet' => $i['vICMSSTRet'] ?? 0,
						'orig' => $i['orig'] ?? 0,

						'codigo_anp' => $i['codigo_anp'] ?? '',
						'descricao_anp' => $i['descricao_anp'] ?? '',
						'uf_cons' => $i['uf_cons'] ?? '',
						'valor_partida' => $i['valor_partida'] ?? 0,
						'perc_glp' => $i['perc_glp'] ?? 0,
						'perc_gnn' => $i['perc_gnn'] ?? 0,
						'perc_gni' => $i['perc_gni'] ?? 0,

						'unidade_tributavel' => $i['unidade_tributavel'] ?? '',
						'quantidade_tributavel' => $i['quantidade_tributavel'] ?? 0,
						'cest' => $i['cest'] ?? '',

						'qBCMonoRet' => $i['qBCMonoRet'] ?? 0,
						'adRemICMSRet' => $i['adRemICMSRet'] ?? 0,
						'vICMSMonoRet' => $i['vICMSMonoRet'] ?? 0,
						

					]);

					if(env("DEVOLUCAO_ALTERA_ESTOQUE") == 1){
						$produto = Produto::where('nome', $i['xProd'])->first();
						if($produto != null){
							$stockMove->downStock(
								(int) $produto->id, (float) str_replace(",", ".", $i['qCom']));
						}
					}
				}
				return $data['itens'];
			});
return response()->json($result);
}catch(\Exception $e){
	__saveError($e, $this->empresa_id);
	return response()->json($e->getMessage(), 400);
}
}

public function ver($id){
	$devolucao = Devolucao::
	where('id', $id)
	->first();

	if(valida_objeto($devolucao)){
		// $xml = file_get_contents('xml_devolucao/'.$devolucao->chave_gerada.'.xml');
		if(!file_exists(public_path('xml_devolucao/').$devolucao->chave_gerada.'.xml')){
			session()->flash("mensagem_erro", "XML não encontrado!");
			return redirect()->back();
		}
		$xml = simplexml_load_file(public_path('xml_devolucao/').$devolucao->chave_gerada.'.xml');

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

		$vFrete = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vFrete, 
			2, ",", ".");
		$vDesc = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");

		$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
		$dadosNf = [
			'chave' => $chave,
			'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
			'indPag' => $xml->NFe->infNFe->ide->indPag,
			'nNf' => $xml->NFe->infNFe->ide->nNF,
			'vFrete' => $vFrete,
			'vDesc' => $vDesc,
		];

		return view('devolucao/ver')
		->with('dadosNf', $dadosNf)
		->with('dadosEmitente', $dadosEmitente)
		->with('devolucao', $devolucao)
		->with('title', 'Ver Devolução');
	}else{
		return redirect('/403');
	}

}

public function downloadXmlEntrada($id){
	$devolucao = Devolucao::
	where('id', $id)
	->first();
	if(valida_objeto($devolucao)){
		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		return response()->download(public_path('xml_devolucao_entrada/').$devolucao->chave_nf_entrada.'.xml');
	}else{
		return redirect('/403');
	}

}

public function downloadXmlDevolucao($id){
	$devolucao = Devolucao::
	where('id', $id)
	->first();
	if(valida_objeto($devolucao)){
		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		return response()->download(public_path('xml_devolucao/').$devolucao->chave_gerada.'.xml');
	}else{
		return redirect('/403');
	}

}

public function delete($id){
	$devolucao = Devolucao::
	where('id', $id)
	->first();
	if(valida_objeto($devolucao)){
		$stockMove = new StockMove();

		foreach($devolucao->itens as $i){

			if(env("DEVOLUCAO_ALTERA_ESTOQUE") == 1){
				$produto = Produto::where('nome', $i->nome)->first();
				if($produto != null){
					$stockMove->pluStock(
						(int) $produto->id, (float) str_replace(",", ".", $i->quantidade));
				}
			}
		}

		$devolucao->delete();

		session()->flash("mensagem_sucesso", "Deletado com sucesso!");
		return redirect('/devolucao');
	}else{
		return redirect('/403');
	}
}

public function imprimir($id){
	$devolucao = Devolucao::
	where('id', $id)
	->first();
	if(valida_objeto($devolucao)){
		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		if($devolucao->estado == 1){
			if(file_exists(public_path('xml_devolucao/').$devolucao->chave_gerada.'.xml')){
				$xml = file_get_contents(public_path('xml_devolucao/').$devolucao->chave_gerada.'.xml');

				$config = ConfigNota::
				where('empresa_id', $this->empresa_id)
				->first();

				if($config->logo){
					$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
				}else{
					$logo = null;
				}

				try {
					$danfe = new Danfe($xml);
						// $id = $danfe->monta($logo);
					$pdf = $danfe->render($logo);
					header('Content-Type: application/pdf');

					return response($pdf)
					->header('Content-Type', 'application/pdf');
				} catch (InvalidArgumentException $e) {
					echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
				}  
			}else{
				echo "arquivo XML não encontrado!!";
			}
		}else if($devolucao->estado == 3){
			$xml = file_get_contents(public_path('xml_devolucao_cancelada/').$devolucao->chave_gerada.'.xml');

			// $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public .'imgs/logo.jpg'));
			$logo = null;
			$dadosEmitente = $this->getEmitente();
			try {
				$danfe = new Daevento($xml, $dadosEmitente);
					// $id = $danfe->monta($logo);
				$pdf = $danfe->render($logo);
				header('Content-Type: application/pdf');

				return response($pdf)
				->header('Content-Type', 'application/pdf');
			} catch (InvalidArgumentException $e) {
				echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
			} 
		}
	}else{
		return redirect('/403');
	}
}

private function getEmitente(){
	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();
	return [
		'razao' => $config->razao_social,
		'logradouro' => $config->logradouro,
		'numero' => $config->numero,
		'complemento' => '',
		'bairro' => $config->bairro,
		'CEP' => $config->cep,
		'municipio' => $config->municipio,
		'UF' => $config->UF,
		'telefone' => $config->telefone,
		'email' => ''
	];
}

	//envio sefaz

public function enviarSefaz(Request $request){
	$devolucao = Devolucao::
	where('id', $request->devolucao_id)
	->where('empresa_id', $this->empresa_id)
	->first();

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	$cnpj = str_replace(".", "", $config->cnpj);
	$cnpj = str_replace("/", "", $cnpj);
	$cnpj = str_replace("-", "", $cnpj);
	$cnpj = str_replace(" ", "", $cnpj);

	$nfe_dev = new DevolucaoService([
		"atualizacao" => date('Y-m-d h:i:s'),
		"tpAmb" => (int)$config->ambiente,
		"razaosocial" => $config->razao_social,
		"siglaUF" => $config->UF,
		"cnpj" => $cnpj,
		"schemes" => "PL_009_V4",
		"versao" => "4.00",
		"tokenIBPT" => "AAAAAAA",
		"CSC" => $config->csc,
		"CSCid" => $config->csc_id
	], 55);

	if($devolucao->estado == 0 || $devolucao->estado == 2){
		header('Content-type: text/html; charset=UTF-8');

		$dev = $nfe_dev->gerarDevolucao($devolucao);
		if(!isset($dev['erros_xml'])){
			// file_put_contents('xml/teste2.xml', $nfe['xml']);

			$signed = $nfe_dev->sign($dev['xml']);
			$resultado = $nfe_dev->transmitir($signed, $dev['chave']);

			if(substr($resultado, 0, 4) != 'Erro'){
				$devolucao->chave_gerada = $dev['chave'];
				$devolucao->estado = 1;

				$devolucao->numero_gerado = $dev['nNf'];
				$this->enviarEmailAutomatico($devolucao);

				$file = file_get_contents(public_path('xml_devolucao/'.$devolucao->chave_gerada.'.xml'));
				importaXmlSieg($file, $this->empresa_id);

				$devolucao->save();
			}else{
				$devolucao->estado = 2;
				$devolucao->save();
			}
			echo json_encode($resultado);
		}else{
			return response()->json($dev['erros_xml'][0], 401);
		}

	}else{
		echo json_encode(false);
	}
}

public function consultar(Request $request){
	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	$cnpj = str_replace(".", "", $config->cnpj);
	$cnpj = str_replace("/", "", $cnpj);
	$cnpj = str_replace("-", "", $cnpj);
	$cnpj = str_replace(" ", "", $cnpj);
	$nfe_dev = new DevolucaoService([
		"atualizacao" => date('Y-m-d h:i:s'),
		"tpAmb" => (int)$config->ambiente,
		"razaosocial" => $config->razao_social,
		"siglaUF" => $config->UF,
		"cnpj" => $cnpj,
		"schemes" => "PL_009_V4",
		"versao" => "4.00",
		"tokenIBPT" => "AAAAAAA",
		"CSC" => $config->csc,
		"CSCid" => $config->csc_id
	], 55);

	$devolucao = Devolucao::find($request->id);
	$c = $nfe_dev->consultar($devolucao);
	echo json_encode($c);
}

public function cancelar(Request $request){
	$devolucao = Devolucao::
	where('id', $request->devolucao_id)
	->where('empresa_id', $this->empresa_id)
	->first();

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	$cnpj = str_replace(".", "", $config->cnpj);
	$cnpj = str_replace("/", "", $cnpj);
	$cnpj = str_replace("-", "", $cnpj);
	$cnpj = str_replace(" ", "", $cnpj);

	$nfe_dev = new DevolucaoService([
		"atualizacao" => date('Y-m-d h:i:s'),
		"tpAmb" => (int)$config->ambiente,
		"razaosocial" => $config->razao_social,
		"siglaUF" => $config->UF,
		"cnpj" => $cnpj,
		"schemes" => "PL_009_V4",
		"versao" => "4.00",
		"tokenIBPT" => "AAAAAAA",
		"CSC" => $config->csc,
		"CSCid" => $config->csc_id
	], 55);


	$resultado = $nfe_dev->cancelar($devolucao, $request->justificativa);
	if($this->isJson($resultado)){

		$devolucao->estado = 3;
		$devolucao->save();

		$file = file_get_contents(public_path('xml_devolucao_cancelada/'.$devolucao->chave_gerada.'.xml'));
		importaXmlSieg($file, $this->empresa_id);

		return response()->json($resultado, 200);

	}

	return response()->json($resultado, 401);
}

private function isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

public function filtro(Request $request){
	$dataInicial = $request->data_inicial;
	$dataFinal = $request->data_final;
	$fornecedor = $request->fornecedor;

	if($dataInicial && !$dataFinal || !$dataInicial && $dataFinal){
		session()->flash("mensagem_erro", "Informe as duas datas para filtrar, não somente uma!");
		return redirect('/devolucao');
	}

	$devolucoes = Devolucao::
	select('devolucaos.*');

	if($dataInicial && $dataFinal){
		$devolucoes->whereBetween('devolucaos.created_at', [
			$this->parseDate($dataInicial),
			$this->parseDate($dataFinal, true)
		]);
	}
	if($fornecedor){
		$devolucoes->join('fornecedors', 'fornecedors.id' , '=', 'devolucaos.fornecedor_id')
		->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%");

	}

	$devolucoes = $devolucoes->where('devolucaos.empresa_id', $this->empresa_id)
	->get();

	return view('devolucao/list')
	->with('devolucoes', $devolucoes)
	->with('devolucaoNF', true)
	->with('title', 'Lista de Devoluções');

}

private function parseDate($date, $plusDay = false){
	if($plusDay == false)
		return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
	else
		return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
}

public function xmltemp($id){
	$devolucao = Devolucao::
	where('id', $id)
	->first();

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	if($config == null){
		session()->flash('mensagem_erro', 'Configure o emitente!!');
		return redirect('/configNF');

	}

	$cnpj = str_replace(".", "", $config->cnpj);
	$cnpj = str_replace("/", "", $cnpj);
	$cnpj = str_replace("-", "", $cnpj);
	$cnpj = str_replace(" ", "", $cnpj);

	$nfe_dev = new DevolucaoService([
		"atualizacao" => date('Y-m-d h:i:s'),
		"tpAmb" => (int)$config->ambiente,
		"razaosocial" => $config->razao_social,
		"siglaUF" => $config->UF,
		"cnpj" => $cnpj,
		"schemes" => "PL_009_V4",
		"versao" => "4.00",
		"tokenIBPT" => "AAAAAAA",
		"CSC" => $config->csc,
		"CSCid" => $config->csc_id
	], 55);


	header('Content-type: text/html; charset=UTF-8');

	$dev = $nfe_dev->gerarDevolucao($devolucao);
	if(!isset($dev['erros_xml'])){

		return response($dev['xml'])
		->header('Content-Type', 'application/xml');
	}else{
		foreach($dev['erros_xml'] as $e){
			echo $e;
		}
	}

}

public function danfeTemp($id){
	$devolucao = Devolucao::
	where('id', $id)
	->first();

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	if($config == null){
		session()->flash('mensagem_erro', 'Configure o emitente!!');
		return redirect('/configNF');

	}

	$cnpj = str_replace(".", "", $config->cnpj);
	$cnpj = str_replace("/", "", $cnpj);
	$cnpj = str_replace("-", "", $cnpj);
	$cnpj = str_replace(" ", "", $cnpj);

	$nfe_dev = new DevolucaoService([
		"atualizacao" => date('Y-m-d h:i:s'),
		"tpAmb" => (int)$config->ambiente,
		"razaosocial" => $config->razao_social,
		"siglaUF" => $config->UF,
		"cnpj" => $cnpj,
		"schemes" => "PL_009_V4",
		"versao" => "4.00",
		"tokenIBPT" => "AAAAAAA",
		"CSC" => $config->csc,
		"CSCid" => $config->csc_id
	], 55);


	header('Content-type: text/html; charset=UTF-8');

	$dev = $nfe_dev->gerarDevolucao($devolucao);
	if(!isset($dev['erros_xml'])){

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();
		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		if($config->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
		}else{
			$logo = null;
		}

		try {
			$danfe = new Danfe($dev['xml']);
				// $id = $danfe->monta($logo);
			$pdf = $danfe->render($logo);
			header('Content-Type: application/pdf');

			return response($pdf)
			->header('Content-Type', 'application/pdf');
		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  


	}else{
		foreach($dev['erros_xml'] as $e){
			echo $e;
		}
	}
}

public function cartaCorrecao(Request $request){

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	$cnpj = str_replace(".", "", $config->cnpj);
	$cnpj = str_replace("/", "", $cnpj);
	$cnpj = str_replace("-", "", $cnpj);
	$cnpj = str_replace(" ", "", $cnpj);

	$devolucao = Devolucao::find($request->id);

	$nfe_dev = new DevolucaoService([
		"atualizacao" => date('Y-m-d h:i:s'),
		"tpAmb" => (int)$config->ambiente,
		"razaosocial" => $config->razao_social,
		"siglaUF" => $config->UF,
		"cnpj" => $cnpj,
		"schemes" => "PL_009_V4",
		"versao" => "4.00",
		"tokenIBPT" => "AAAAAAA",
		"CSC" => $config->csc,
		"CSCid" => $config->csc_id
	], 55);

	$devolucao = $nfe_dev->cartaCorrecao($devolucao, $request->correcao);
	echo json_encode($devolucao);
}

public function imprimirCce($id){
	$devolucao = Devolucao::
	where('id', $id)
	->where('empresa_id', $this->empresa_id)
	->first();

	if($devolucao->sequencia_cce > 0){

		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		if(file_exists(public_path('xml_devolucao_correcao/').$devolucao->chave_gerada.'.xml')){
			$xml = file_get_contents(public_path('xml_devolucao_correcao/').$devolucao->chave_gerada.'.xml');

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			if($config->logo){
				$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
			}else{
				$logo = null;
			}

			$dadosEmitente = $this->getEmitente();

			try {
				$daevento = new Daevento($xml, $dadosEmitente);
				$daevento->debugMode(true);
				$pdf = $daevento->render($logo);

				return response($pdf)
				->header('Content-Type', 'application/pdf');
			} catch (InvalidArgumentException $e) {
				echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
			}  
		}else{
			echo "Arquivo XML não encontrado!!";
		}
	}else{
		echo "<center><h1>Este documento não possui evento de correção!<h1></center>";
	}
}

public function imprimirCancela($id){
	$devolucao = Devolucao::
	where('id', $id)
	->where('empresa_id', $this->empresa_id)
	->first();

	if($devolucao->estado == 3){

		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		if(file_exists($public.'xml_devolucao_cancelada/'.$devolucao->chave_gerada.'.xml')){
			$xml = file_get_contents($public.'xml_devolucao_cancelada/'.$devolucao->chave_gerada.'.xml');

			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			if($config->logo){
				$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
			}else{
				$logo = null;
			}

			$dadosEmitente = $this->getEmitente();

			try {
				$daevento = new Daevento($xml, $dadosEmitente);
				$daevento->debugMode(true);
				$pdf = $daevento->render($logo);

				return response($pdf)
				->header('Content-Type', 'application/pdf');
			} catch (InvalidArgumentException $e) {
				echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
			}  
		}else{
			echo "Arquivo XML não encontrado!!";
		}
	}else{
		echo "<center><h1>Este documento não possui evento de correção!<h1></center>";
	}
}

public function edit($id){
	$devolucao = Devolucao::find($id);

	if(valida_objeto($devolucao)){

		$xml = simplexml_load_file(public_path('xml_devolucao_entrada')."/$devolucao->chave_nf_entrada.xml");

		if(!isset($xml->NFe->infNFe)){
			session()->flash('mensagem_erro', 'Este xml não é uma NFe');
			return redirect("/devolucao/nova");
		}
		// if(!$this->validaChave($xml->NFe->infNFe->attributes()->Id)){
		// 	session()->flash('mensagem_erro', 'Este XML de devolução já esta incluido no sistema com estado aprovado!');
		// 		// return redirect("/devolucao/nova");
		// }

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

		$transportadora = null;
		$transportadoraDoc = null;

		if($xml->NFe->infNFe->transp->transporta){

			$transp = $xml->NFe->infNFe->transp->transporta;
			$transportadoraDoc = (int)$transp->CNPJ;

			$vol = $xml->NFe->infNFe->transp->vol;
			$modFrete = $xml->NFe->infNFe->transp->modFrete;

			$transportadora = [
				'transportadora_nome' => (string)$transp->xNome,
				'transportadora_cidade' => (string)$transp->xMun,
				'transportadora_uf' => (string)$transp->UF,
				'transportadora_cpf_cnpj' => (string)$transp->CNPJ,
				'transportadora_ie' => (int)$transp->IE,
				'transportadora_endereco' => (string)$transp->xEnder,
				'frete_quantidade' => (float)$vol->qVol,
				'frete_especie' => (string)$vol->esp,
				'frete_marca' => '',
				'frete_numero' => 0,
				'frete_tipo' => (int)$modFrete,
				'veiculo_placa' => '',
				'veiculo_uf' => '',
				'frete_peso_bruto' => (float)$vol->pesoB, 
				'frete_peso_liquido' => (float)$vol->pesoL,
				'despesa_acessorias' => (float)$xml->NFe->infNFe->total->ICMSTot->vOutro
			];

					// print_r($transportadora);
					// die;

		}

		$vFrete = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vFrete, 
			2, ",", ".");

		$vDesc = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");

		$idFornecedor = 0;
		$fornecedorEncontrado = $this->verificaFornecedor($dadosEmitente['cnpj'] == '' ? $dadosEmitente['cpf'] : $dadosEmitente['cnpj']);
		$dadosAtualizados = [];
		if($fornecedorEncontrado){
			$idFornecedor = $fornecedorEncontrado->id;
			$dadosAtualizados = $this->verificaAtualizacao($fornecedorEncontrado, $dadosEmitente);
		}else{

			array_push($dadosAtualizados, "Fornecedor cadastrado com sucesso");
			$idFornecedor = $this->cadastrarFornecedor($dadosEmitente);
		}

		$idTransportadora = 0;

		if($transportadoraDoc != null){
			$transportadoraEncontrada = $this->verificaTransportadora($transportadoraDoc);

			if($transportadoraEncontrada){
				$idTransportadora = $transportadoraEncontrada->id;
			}else{
				array_push($dadosAtualizados, 
					"Transportadora cadastrada com sucesso");
				$idTransportadora = $this->cadastrarTransportadora($transportadora);
			}
		}

		$seq = 0;
		$itens = [];
		$contSemRegistro = 0;

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first();

		foreach($xml->NFe->infNFe->det as $item) {

			$trib = Devolucao::getTrib($item->imposto);
			$item = [
				'codigo' => $item->prod->cProd,
				'xProd' => $item->prod->xProd,
				'NCM' => $item->prod->NCM,
				'vFrete' => $item->prod->vFrete ?? 0,
				'CFOP' => $item->prod->CFOP,
				'uCom' => $item->prod->uCom,
				'sub_total' => $item->prod->vProd,
				'vUnCom' => $item->prod->vUnCom,
				'qCom' => $item->prod->qCom,
				'codBarras' => $item->prod->cEAN ?? '',
				'cst_csosn' => $trib['cst_csosn'],
				'cst_pis' => $trib['cst_pis'],
				'cst_cofins' => $trib['cst_cofins'],
				'cst_ipi' => $trib['cst_ipi'],
				'perc_icms' => $trib['pICMS'],
				'perc_pis' => $trib['pPIS'],
				'perc_cofins' => $trib['pCOFINS'],
				'perc_ipi' => $trib['pIPI'],
				'pRedBC' => $trib['pRedBC'],
				'modBCST' => $trib['modBCST'],
				'vBCST' => $trib['vBCST'],
				'pICMSST' => $trib['pICMSST'],
				'vICMSST' => $trib['vICMSST'],
				'pMVAST' => $trib['pMVAST'],
			];
			array_push($itens, $item);
		}

			// print_r($itens);die;
		$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
		$dadosNf = [
			'chave' => $chave,
			'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
			'indPag' => $xml->NFe->infNFe->ide->indPag,
			'nNf' => $xml->NFe->infNFe->ide->nNF,
			'vFrete' => $vFrete,
			'vDesc' => $vDesc,
		];


		$fatura = [];
		if (!empty($xml->NFe->infNFe->cobr->dup))
		{
			foreach($xml->NFe->infNFe->cobr->dup as $dup) {
				$titulo = $dup->nDup;
				$vencimento = $dup->dVenc;
				$vencimento = explode('-', $vencimento);
				$vencimento = $vencimento[2]."/".$vencimento[1]."/".$vencimento[0];
				$vlr_parcela = number_format((double) $dup->vDup, 2, ",", ".");	

				$parcela = [
					'numero' => $titulo,
					'vencimento' => $vencimento,
					'valor_parcela' => $vlr_parcela
				];
				array_push($fatura, $parcela);
			}
		}

            //fim upload

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$naturezas = NaturezaOperacao::
		where('empresa_id', $this->empresa_id)
		->get();

		$transportadoras = Transportadora::
		where('empresa_id', $this->empresa_id)
		->get();

		$tipoFrete = 0;
		if($transportadora != null){
			$tipoFrete = $transportadora['frete_tipo'];
		}

		return view('devolucao/edit')
		->with('title', 'Editar devolução')
		->with('itens', $devolucao->itens)
		->with('devolucao', $devolucao)
		->with('fatura', $fatura)
		->with('tipoFrete', $tipoFrete)
		->with('devolucaoJsEdit', true)
		->with('idFornecedor', $idFornecedor)
		->with('dadosNf', $dadosNf)
		->with('naturezas', $naturezas)
		->with('config', $config)
		->with('cidade', $cidade)
		->with('transportadora', $transportadora)
		->with('dadosEmitente', $dadosEmitente)
		->with('transportadoras', $transportadoras)
		->with('idTransportadora', $idTransportadora)
		->with('dadosAtualizados', $dadosAtualizados);

	}else{
		session()->flash('mensagem_erro', 'XML inválido!');
		return redirect("/devolucao/nova");
	}
}


public function editManual($id){
	$devolucao = Devolucao::find($id);

	if(valida_objeto($devolucao)){

		$xml = simplexml_load_file(public_path('xml_devolucao_entrada')."/$devolucao->chave_nf_entrada.xml");

		if(!isset($xml->NFe->infNFe)){
			session()->flash('mensagem_erro', 'Este xml não é uma NFe');
			return redirect("/devolucao/nova");
		}
		// if(!$this->validaChave($xml->NFe->infNFe->attributes()->Id)){
		// 	session()->flash('mensagem_erro', 'Este XML de devolução já esta incluido no sistema com estado aprovado!');
		// 		// return redirect("/devolucao/nova");
		// }

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

		$transportadora = null;
		$transportadoraDoc = null;

		if($xml->NFe->infNFe->transp->transporta){
			$transp = $xml->NFe->infNFe->transp->transporta;
			$transportadoraDoc = (int)$transp->CNPJ;

			$vol = $xml->NFe->infNFe->transp->vol;
			$modFrete = $xml->NFe->infNFe->transp->modFrete;

			$transportadora = [
				'transportadora_nome' => (string)$transp->xNome,
				'transportadora_cidade' => (string)$transp->xMun,
				'transportadora_uf' => (string)$transp->UF,
				'transportadora_cpf_cnpj' => (string)$transp->CNPJ,
				'transportadora_ie' => (int)$transp->IE,
				'transportadora_endereco' => (string)$transp->xEnder,
				'frete_quantidade' => (float)$vol->qVol,
				'frete_especie' => (string)$vol->esp,
				'frete_marca' => '',
				'frete_numero' => 0,
				'frete_tipo' => (int)$modFrete,
				'veiculo_placa' => '',
				'veiculo_uf' => '',
				'frete_peso_bruto' => (float)$vol->pesoB, 
				'frete_peso_liquido' => (float)$vol->pesoL,
				'despesa_acessorias' => (float)$xml->NFe->infNFe->total->ICMSTot->vOutro
			];

					// print_r($transportadora);
					// die;

		}

		$vFrete = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vFrete, 
			2, ",", ".");

		$vDesc = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");

		$idFornecedor = 0;
		$fornecedorEncontrado = $this->verificaFornecedor($dadosEmitente['cnpj'] == '' ? $dadosEmitente['cpf'] : $dadosEmitente['cnpj']);
		$dadosAtualizados = [];
		if($fornecedorEncontrado){
			$idFornecedor = $fornecedorEncontrado->id;
			$dadosAtualizados = $this->verificaAtualizacao($fornecedorEncontrado, $dadosEmitente);
		}else{

			array_push($dadosAtualizados, "Fornecedor cadastrado com sucesso");
			$idFornecedor = $this->cadastrarFornecedor($dadosEmitente);
		}

		$idTransportadora = 0;

		if($transportadoraDoc != null){

			$transportadoraEncontrada = $this->verificaTransportadora($transportadoraDoc);

			if($transportadoraEncontrada){
				$idTransportadora = $transportadoraEncontrada->id;
			}else{
				array_push($dadosAtualizados, 
					"Transportadora cadastrada com sucesso");
				$idTransportadora = $this->cadastrarTransportadora($transportadora);
			}
		}

		$seq = 0;
		$itens = [];
		$contSemRegistro = 0;

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first();

		foreach($xml->NFe->infNFe->det as $item) {

			$trib = Devolucao::getTrib($item->imposto);
			$item = [
				'codigo' => $item->prod->cProd,
				'xProd' => $item->prod->xProd,
				'NCM' => $item->prod->NCM,
				'vFrete' => $item->prod->vFrete ?? 0,
				'CFOP' => $item->prod->CFOP,
				'uCom' => $item->prod->uCom,
				'vUnCom' => $item->prod->vUnCom,
				'qCom' => $item->prod->qCom,
				'codBarras' => $item->prod->cEAN ?? '',
				'cst_csosn' => $trib['cst_csosn'],
				'cst_pis' => $trib['cst_pis'],
				'cst_cofins' => $trib['cst_cofins'],
				'cst_ipi' => $trib['cst_ipi'],
				'perc_icms' => __replace($trib['pICMS']),
				'perc_pis' => __replace($trib['pPIS']),
				'perc_cofins' => __replace($trib['pCOFINS']),
				'perc_ipi' => __replace($trib['pIPI']),
				'pRedBC' => $trib['pRedBC'] ? __replace($trib['pRedBC']) : 0,
				'modBCST' => $trib['modBCST'] ? __replace($trib['modBCST']) : 0,
				'vBCST' => $trib['vBCST'] ? __replace($trib['vBCST']) : 0,
				'pICMSST' => $trib['pICMSST'] ? __replace($trib['pICMSST']) : 0,
				'vICMSST' => $trib['vICMSST'] ? __replace($trib['vICMSST']) : 0,
				'pMVAST' => $trib['pMVAST'] ? __replace($trib['pMVAST']) : 0,
			];

			array_push($itens, $item);
		}

			// print_r($itens);die;
		$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
		$dadosNf = [
			'chave' => $chave,
			'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
			'indPag' => $xml->NFe->infNFe->ide->indPag,
			'nNf' => $xml->NFe->infNFe->ide->nNF,
			'vFrete' => $vFrete,
			'vDesc' => $vDesc,
		];


		$fatura = [];
		if (!empty($xml->NFe->infNFe->cobr->dup))
		{
			foreach($xml->NFe->infNFe->cobr->dup as $dup) {
				$titulo = $dup->nDup;
				$vencimento = $dup->dVenc;
				$vencimento = explode('-', $vencimento);
				$vencimento = $vencimento[2]."/".$vencimento[1]."/".$vencimento[0];
				$vlr_parcela = number_format((double) $dup->vDup, 2, ",", ".");	

				$parcela = [
					'numero' => $titulo,
					'vencimento' => $vencimento,
					'valor_parcela' => $vlr_parcela
				];
				array_push($fatura, $parcela);
			}
		}

            //fim upload

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$naturezas = NaturezaOperacao::
		where('empresa_id', $this->empresa_id)
		->get();

		$transportadoras = Transportadora::
		where('empresa_id', $this->empresa_id)
		->get();

		$tipoFrete = 0;
		if($transportadora != null){
			$tipoFrete = $transportadora['frete_tipo'];
		}

		return view('devolucao/edit_manual')
		->with('title', 'Editar devolução')
		->with('itens', $devolucao->itens)
		->with('devolucao', $devolucao)
		->with('fatura', $fatura)
		->with('cidade', $cidade)
		->with('tipoFrete', $tipoFrete)
		->with('idFornecedor', $idFornecedor)
		->with('dadosNf', $dadosNf)
		->with('naturezas', $naturezas)
		->with('config', $config)
		->with('transportadora', $transportadora)
		->with('dadosEmitente', $dadosEmitente)
		->with('transportadoras', $transportadoras)
		->with('idTransportadora', $idTransportadora)
		->with('dadosAtualizados', $dadosAtualizados);

	}else{
		session()->flash('mensagem_erro', 'XML inválido!');
		return redirect("/devolucao/nova");
	}
}

public function update(Request $request){

	try{
		$result = DB::transaction(function () use ($request) {
			$data = $request->data;

			$devolucao = Devolucao::find($data['id']);

			$devolucao->usuario_id = get_id_user();
			$devolucao->natureza_id = $data['natureza'];
			$devolucao->valor_integral = str_replace(",", ".", $data['valor_integral']);
			$devolucao->valor_devolvido = str_replace(",", ".", $data['valor_devolvido']);
			$devolucao->motivo = $data['motivo'] ?? '';
			$devolucao->observacao = $data['obs'] ?? '';
			$devolucao->vFrete = str_replace(",", ".", $data['vFrete']);
			$devolucao->vDesc = str_replace(",", ".", $data['vDesc']);
			$devolucao->tipo = $data['tipo'];
			$devolucao->frete_quantidade = $data['qtd'] ? __replace($data['qtd']) : 0;
			$devolucao->frete_especie = $data['especie'] ?? '';
			$devolucao->frete_marca = $data['frete_marca'] ?? '';
			$devolucao->frete_numero = $data['frete_numero'] ?? 0;
			$devolucao->frete_tipo = $data['tipoFrete'] ?? 0;
			$devolucao->veiculo_placa = $data['placa'] ?? '';
			$devolucao->veiculo_uf = $data['ufPlaca'] ?? '';
			$devolucao->frete_peso_bruto = $data['pBruto'] ? __replace($data['pBruto']) : 0;
			$devolucao->frete_peso_liquido = $data['pLiquido'] ? 
			__replace($data['pLiquido']) : 0;
			$devolucao->despesa_acessorias = $data['vOutros'] ? 
			__replace($data['vOutros']) : 0;
			$devolucao->transportadora_id = $data['transportadora_id'] > 0 ? $data['transportadora_id'] : NULL;

			$devolucao->save();
			ItemDevolucao::
			where('devolucao_id', $devolucao->id)
			->delete();
			foreach($data['itens'] as $i){

				$item = ItemDevolucao::create([
					'cod' => $i['codigo'],
					'nome' => $i['xProd'],
					'ncm' => $i['NCM'],
					'cfop' => $i['CFOP'],
					'valor_unit' => $i['vUnCom'],
					'vFrete' => $i['vFrete'] ?? 0,
					'quantidade' => $i['qCom'],
					'item_parcial' => $i['parcial'],
					'unidade_medida' => $i['uCom'],
					'codBarras' => $i['codBarras'] ?? '',
					'devolucao_id' => $devolucao->id,
					'cst_csosn' => $i['cst_csosn'],
					'cst_pis' => $i['cst_pis'],
					'cst_cofins' => $i['cst_cofins'],
					'cst_ipi' => $i['cst_ipi'] ?? '99',
					'perc_icms' => __replace($i['perc_icms']),
					'perc_pis' => __replace($i['perc_pis']),
					'perc_cofins' => __replace($i['perc_cofins']),
					'perc_ipi' => __replace($i['perc_ipi']),
					'pRedBC' => $i['pRedBC'] ? __replace($i['pRedBC']) : 0,
					'modBCST' => $i['modBCST'] ? __replace($i['modBCST']) : 0,
					'vBCST' => $i['vBCST'] ? __replace($i['vBCST']) : 0,
					'pICMSST' => $i['pICMSST'] ? __replace($i['pICMSST']) : 0,
					'vICMSST' => $i['vICMSST'] ? __replace($i['vICMSST']) : 0,
					'pMVAST' => $i['pMVAST'] ? __replace($i['pMVAST']) : 0,
					'cBenef' => $i['cBenef'] ?? '',
				]);
			}

			return $data['itens'];
		});
		return response()->json($result, 200);

	}catch(\Exception $e){
		__saveError($e, $this->empresa_id);
		return response()->json($e->getMessage(), 400);
	}
}

public function updateManual(Request $request, $id){

	try{

		$result = DB::transaction(function () use ($request, $id) {

			$devolucao = Devolucao::findOrFail($id);

			$devolucao->usuario_id = get_id_user();
			$devolucao->altera_manual = 1;
			$devolucao->vbc_manual = __replace($request->vbc_manual);
			$devolucao->tipo = $request->tipo;
			$devolucao->transportadora_id = $request->transportadora_id ? $request->transportadora_id : null;
			$devolucao->vFrete = __replace($request->valor_frete);
			$devolucao->frete_tipo = $request->tipo_frete ?? "";
			$devolucao->veiculo_placa = $request->placa ?? "";
			$devolucao->veiculo_uf = $request->uf_placa ?? "";
			$devolucao->frete_especie = $request->especie ?? "";
			$devolucao->frete_quantidade = $request->quantidade ? __replace($request->quantidade) : 0;
			$devolucao->frete_peso_bruto = $request->peso_bruto ? __replace($request->peso_bruto) : 0;
			$devolucao->frete_peso_liquido = $request->peso_liquido ? __replace($request->peso_liquido) : 0;
			$devolucao->despesa_acessorias = $request->valor_outros ? __replace($request->valor_outros) : 0;
			$devolucao->vDesc = $request->vDesc ? __replace($request->vDesc) : 0;

			$devolucao->save();

			for ($i = 0; $i < sizeof($request->item_id); $i++) {
				$item = ItemDevolucao::find($request->item_id[$i]);
				if($item != null){

					$item->valor_unit = $request->valor_unit[$i] ? __replace($request->valor_unit[$i]) : 0;
					$item->quantidade = $request->quantidade[$i] ? __replace($request->quantidade[$i]) : 0;
					$item->vbc_manual = $request->vbc_manual_item[$i] ? __replace($request->vbc_manual_item[$i]) : 0;
					$item->perc_icms = $request->perc_icms[$i] ? __replace($request->perc_icms[$i]) : 0;
					$item->vicms_manual = $request->vicms_manual[$i] ? __replace($request->vicms_manual[$i]) : 0;
					$item->perc_pis = $request->perc_pis[$i] ? __replace($request->perc_pis[$i]) : 0;
					$item->vpis_manual = $request->vpis_manual[$i] ? __replace($request->vpis_manual[$i]) : 0;
					$item->perc_cofins = $request->perc_cofins[$i] ? __replace($request->perc_cofins[$i]) : 0;
					$item->vcofins_manual = $request->vcofins_manual[$i] ? __replace($request->vcofins_manual[$i]) : 0;
					$item->perc_ipi = $request->perc_ipi[$i] ? __replace($request->perc_ipi[$i]) : 0;
					$item->vipi_manual = $request->vipi_manual[$i] ? __replace($request->vipi_manual[$i]) : 0;

					$item->pST = $request->pST[$i] ? __replace($request->pST[$i]) : 0;
					$item->modBCST = $request->modBCST[$i] ? $request->modBCST[$i] : 0;
					$item->vBCST = $request->vBCST[$i] ? __replace($request->vBCST[$i]) : 0;
					$item->pICMSST = $request->pICMSST[$i] ? __replace($request->pICMSST[$i]) : 0;
					$item->vICMSST = $request->vICMSST[$i] ? __replace($request->vICMSST[$i]) : 0;

					$item->cst_csosn = $request->cst_csosn[$i] ?? '';
					$item->cst_pis = $request->cst_pis[$i] ?? '';
					$item->cst_cofins = $request->cst_cofins[$i] ?? '';
					$item->cst_ipi = $request->cst_ipi[$i] ?? '';
					$item->cest = $request->cest[$i] ?? '';
					$item->cBenef = $request->cBenef[$i] ?? '';
					$item->qBCMonoRet = $request->qBCMonoRet[$i] ? __replace($request->qBCMonoRet[$i]) : 0;
					$item->adRemICMSRet = $request->adRemICMSRet[$i] ? __replace($request->adRemICMSRet[$i]) : 0;
					$item->vICMSMonoRet = $request->vICMSMonoRet[$i] ? __replace($request->vICMSMonoRet[$i]) : 0;
					$item->save();

				}
			}
			
		});
		session()->flash("mensagem_sucesso", "Registro atualizado!");

	}catch(\Exception $e){
		__saveError($e, $this->empresa_id);
		session()->flash("mensagem_erro", "Erro: " . $e->getMessage());
	}
	return redirect('/devolucao');
}

private function enviarEmailAutomatico($devolucao){
	$escritorio = EscritorioContabil::
	where('empresa_id', $this->empresa_id)
	->first();

	if($escritorio != null && $escritorio->envio_automatico_xml_contador){
		$email = $escritorio->email;
		Mail::send('mail.xml_automatico', ['descricao' => 'Envio de NFe'], function($m) use ($email, $devolucao){
			$nomeEmpresa = env('MAIL_NAME');
			$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
			$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
			$emailEnvio = env('MAIL_USERNAME');

			$m->from($emailEnvio, $nomeEmpresa);
			$m->subject('Envio de XML Automático');

			$m->attach(public_path('xml_devolucao/'.$devolucao->chave_gerada.'.xml'));
			$m->to($email);
		});
	}
}

public function estadoFiscal($id){

	$devolucao = Devolucao::find($id);
	$value = session('user_logged');
	if($value['adm'] == 0) return redirect()->back();
	if(valida_objeto($devolucao)){

		return view("devolucao/alterar_estado_fiscal")
		->with('devolucao', $devolucao)
		->with('title', "Alterar estado devolucao $id");
	}else{
		return redirect('/403');
	}
}

public function estadoFiscalStore(Request $request){
	try{
		$devolucao = Devolucao::find($request->devolucao_id);
		$estado = $request->estado;

		$devolucao->estado = $estado;
		if ($request->hasFile('file')){

			$xml = simplexml_load_file($request->file);
			$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
			$file = $request->file;
			$file->move(public_path('xml_devolucao'), $chave.'.xml');
			$devolucao->chave_gerada = $chave;
			$devolucao->numero_gerado = (int)$xml->NFe->infNFe->ide->nNF;

		}

		$devolucao->save();
		session()->flash("mensagem_sucesso", "Estado alterado");

	}catch(\Exception $e){
		session()->flash("mensagem_erro", "Erro: " . $e->getMessage());

	}
	return redirect()->back();
}

public function enviarXml(Request $request){
	$email = $request->email;
	$id = $request->id;

	$devolucao = Devolucao::
	where('id', $id)
	->where('empresa_id', $this->empresa_id)
	->first();

	$config = ConfigNota::
	where('empresa_id', $this->empresa_id)
	->first();

	$public = env('SERVIDOR_WEB') ? 'public/' : '';

	$value = session('user_logged');

	if($devolucao->chave_gerada != ""){
		$this->criarPdfParaEnvio($devolucao);
	}

	if($config->usar_email_proprio){

		$fileDir = public_path('xml_devolucao/') . $devolucao->chave_gerada.'.xml';

		$subject = 'XML de devolução';
		$body = '<h1>Envio de XML</h1>';
		$body .= '<h3>Emissão: '. \Carbon\Carbon::parse($devolucao->data_registro)->format('d/m/Y') .'</h3>';
		$body .= '<h3>Valor: R$' . number_format($devolucao->valor_devolvido, 2,',', '.') .'</h3>';

		$send = $this->enviaEmailPHPMailer($fileDir, $subject, $body, $email);
		if(isset($send['erro'])){
			return response()->json($send['erro'], 401);
		}
		return "ok";
	}else{
		Mail::send('mail.xml_send_devolucao', ['emissao' => $devolucao->data_registro, 'nf' => $devolucao->numero_gerado,'valor' => $devolucao->valor_devolvido, 'usuario' => $value['nome'], 'devolucao' => $devolucao, 'config' => $config], function($m) use ($devolucao, $email){

			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			$nomeEmpresa = env('MAIL_NAME');
			$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
			$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
			$emailEnvio = env('MAIL_USERNAME');

			$m->from($emailEnvio, $nomeEmpresa);
			$subject = "Envio de XML #$devolucao->numero_gerado";

			$m->subject($subject);

			if($devolucao->chave_gerada != ""){
				$m->attach(public_path('xml_devolucao/').$devolucao->chave_gerada.'.xml');
			}

			// $m->attach($public.'pdf/DANFE_DEVOLUCAO.pdf');
			$m->attach(public_path('pdf/').'DANFE_DEVOLUCAO.pdf');
			$m->to($email);
		});
		return "ok";
	}
}

private function enviaEmailPHPMailer($fileDir, $subject, $body, $email){
	$emailConfig = EmailConfig::
	where('empresa_id', $this->empresa_id)
	->first();

	if($emailConfig == null){
		return [
			'erro' => 'Primeiramente configure seu email'
		];
	}

	$mail = new PHPMailer(true);

	try {
		if($emailConfig->smtp_debug){
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;   
		}                   
		$mail->isSMTP();                                            
		$mail->Host = $emailConfig->host;                     
		$mail->SMTPAuth = $emailConfig->smtp_auth;                                   
		$mail->Username = $emailConfig->email;                     
		$mail->Password = $emailConfig->senha;                               
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
		$mail->Port = $emailConfig->porta; 

		$mail->setFrom($emailConfig->email, $emailConfig->nome); 
		$mail->addAddress($email); 

		$mail->addAttachment($fileDir); 

		$mail->isHTML(true);
		$mail->CharSet = 'UTF-8';

		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->send();
		return [
			'sucesso' => true
		];
	} catch (Exception $e) {
		return [
			'erro' => $mail->ErrorInfo
		];
			// echo "Message could; not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}

private function criarPdfParaEnvio($devolucao){
	$public = env('SERVIDOR_WEB') ? 'public/' : '';
	$xml = file_get_contents($public.'xml_devolucao/'.$devolucao->chave_gerada.'.xml');

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
		header('Content-Type: application/pdf');
		file_put_contents(public_path('pdf/'). 'DANFE_DEVOLUCAO.pdf',$pdf);
	} catch (InvalidArgumentException $e) {
		echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
	}  
}
}
