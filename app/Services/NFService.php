<?php
namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\Venda;
use App\Models\Difal;
use App\Models\Contigencia;
use App\Models\RemessaNfe;
use App\Models\ConfigNota;
use App\Models\Certificado;
use App\Models\ConfigSystem;
use NFePHP\NFe\Complements;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Tributacao;
use App\Models\NFeReferecia;
use App\Models\IBPT;
use App\Models\Filial;
use NFePHP\NFe\Factories\Contingency;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFService{

	private $config; 
	private $tools;
	protected $empresa_id = null;
	protected $timeout = 8;

	public function __construct($config, $empresa_id = null){

		if($empresa_id == null){
			$value = session('user_logged');
			$this->empresa_id = $value['empresa'];
		}else{
			$this->empresa_id = $empresa_id;
		}
		if(isset($config['is_filial']) && $config['is_filial']){
			$certificado = Filial::findOrFail($config['is_filial']);

			$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificado->arquivo_certificado, $certificado->senha_certificado));
		}else{
			$certificado = Certificado::
			where('empresa_id', $this->empresa_id)
			->first();

			$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificado->arquivo, $certificado->senha));
		}
		$soapCurl = new SoapCurl();
		$soapCurl->httpVersion('1.1');
		$this->tools->loadSoapClass($soapCurl);

		$contigencia = $this->getContigencia();

		if($contigencia != null){
			$contingency = new Contingency($contigencia->status_retorno);
			$this->tools->contingency = $contingency;
		}
		$this->config = $config;
		$this->tools->model(55);

		$config = ConfigSystem::first();
		if($config){
			if($config->timeout_nfe){
				$this->timeout = $config->timeout_nfe;
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

	public function gerarNFe($idVenda){
		$venda = Venda::
		where('id', $idVenda)
		->first();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first(); // iniciando os dados do emitente NF

		if($venda->filial_id != null){
			$casas_decimais = $config->casas_decimais;
			$config = Filial::findOrFail($venda->filial_id);
			$config->casas_decimais = $casas_decimais;
		}
		
		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first(); // iniciando tributos

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00'; 
		$stdInNFe->Id = null; 
		$stdInNFe->pk_nItem = ''; 

		$infNFe = $nfe->taginfNFe($stdInNFe);

		$vendaLast = Venda::lastNF($this->empresa_id);
		$nNfeRemessa = RemessaNfe::lastNFe($this->empresa_id);

		if($nNfeRemessa > $vendaLast){
			$vendaLast = $nNfeRemessa;
		}

		if($venda->filial_id != null){
			$vendaLast = $config->ultimo_numero_nfe;
		}
		$lastNumero = $vendaLast;
		
		$stdIde = new \stdClass();
		$stdIde->cUF = $config->cUF;
		$stdIde->cNF = rand(11111,99999);
		// $stdIde->natOp = $venda->natureza->natureza;
		$stdIde->natOp = $venda->natureza->natureza;

		// $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

		$stdIde->mod = 55;
		$stdIde->serie = $config->numero_serie_nfe;
		// $stdIde->nNF = (int)$lastNumero;
		$stdIde->nNF = (int)$lastNumero+1;
		if($venda->data_retroativa){
			$stdIde->dhEmi = $venda->data_retroativa.date("\TH:i:sP");
		}else{
			$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		}

		if($venda->data_saida){
			$stdIde->dhSaiEnt = $venda->data_saida.date("\TH:i:sP");
		}else{
			$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		}
		// $stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = 1;

		if($venda->cliente->cod_pais == 1058){
			$stdIde->idDest = $config->UF != $venda->cliente->cidade->uf ? 2 : 1;
		}else{
			$stdIde->idDest = 3;
		}

		$stdIde->cMunFG = $config->codMun;
		$stdIde->tpImp = isset($config->tipo_impressao_danfe) ? $config->tipo_impressao_danfe : 1;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = $config->ambiente;
		$stdIde->finNFe = $venda->natureza->finNFe;
		if($venda->pedido_nuvemshop_id > 0){
			$stdIde->indFinal = 1;
		}else{
			$stdIde->indFinal = $venda->cliente->consumidor_final;
		}
		$stdIde->indPres = 1;

		if($config->ambiente == 2){
			if($venda->pedido_ecommerce_id > 0){
				$stdIde->indIntermed = 1;
			}else{
				$stdIde->indIntermed = 0;
			}
		}
		$stdIde->procEmi = '0';
		$stdIde->verProc = '3.10.31';

		$tagide = $nfe->tagide($stdIde);

		$stdEmit = new \stdClass();
		$stdEmit->xNome = $config->razao_social;
		$stdEmit->xFant = $config->nome_fantasia;

		$ie = preg_replace('/[^0-9]/', '', $config->ie);

		$stdEmit->IE = $ie;
		// $stdEmit->CRT = $tributacao->regime == 0 ? 1 : 3;
		// $stdEmit->CRT = ($tributacao->regime == 0 || $tributacao->regime == 2) ? 1 : 3;
		$stdEmit->CRT = $tributacao->getCRT();

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		if(strlen($cnpj) == 14){
			$stdEmit->CNPJ = $cnpj;
		}else{
			$stdEmit->CPF = $cnpj;
		}
		// $stdEmit->IM = $ie;

		$emit = $nfe->tagemit($stdEmit);

		// ENDERECO EMITENTE
		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $this->retiraAcentos($config->logradouro);
		$stdEnderEmit->nro = $config->numero;
		$stdEnderEmit->xCpl = $this->retiraAcentos($config->complemento);
		
		$stdEnderEmit->xBairro = $this->retiraAcentos($config->bairro);
		$stdEnderEmit->cMun = $config->codMun;
		$stdEnderEmit->xMun = $this->retiraAcentos($config->municipio);
		$stdEnderEmit->UF = $config->UF;

		$telefone = $config->fone;
		if(substr($telefone, 0, 3) == '+55'){
			$telefone = substr($telefone, 3, strlen($telefone));
		}
		$telefone = preg_replace('/[^0-9]/', '', $telefone);

		$stdEnderEmit->fone = $telefone;

		$cep = preg_replace('/[^0-9]/', '', $config->cep);

		$stdEnderEmit->CEP = $cep;
		$stdEnderEmit->cPais = '1058';
		$stdEnderEmit->xPais = 'BRASIL';

		$enderEmit = $nfe->tagenderEmit($stdEnderEmit);

		// DESTINATARIO
		$stdDest = new \stdClass();
		$pFisica = false;
		$stdDest->xNome = $this->retiraAcentos($venda->cliente->razao_social);

		if($venda->cliente->cod_pais != 1058){
			$stdDest->indIEDest = "9";
			$stdDest->idEstrangeiro = $venda->cliente->id_estrangeiro;
		}else{
			if($venda->cliente->contribuinte){
				if($venda->cliente->ie_rg == 'ISENTO'){
					$stdDest->indIEDest = "2";
				}else{
					$stdDest->indIEDest = "1";
				}

			}else{
				$stdDest->indIEDest = "9";
			}

			$cnpj_cpf = preg_replace('/[^0-9]/', '', $venda->cliente->cpf_cnpj);

			if(strlen($cnpj_cpf) == 14){
				$stdDest->CNPJ = $cnpj_cpf;
				$ie = preg_replace('/[^0-9]/', '', $venda->cliente->ie_rg);
				$stdDest->IE = $ie;
			}
			else{
			// $stdDest->CPF = $cnpj_cpf;
				$stdDest->CPF = $cnpj_cpf;
				$ie = preg_replace('/[^0-9]/', '', $venda->cliente->ie_rg);

				if(strtolower($ie) != "isento" && $venda->cliente->contribuinte)
					$stdDest->IE = $ie;
				$pFisica = true;

			} 
		}

		$dest = $nfe->tagdest($stdDest);

		$stdEnderDest = new \stdClass();
		$stdEnderDest->xLgr = $this->retiraAcentos($venda->cliente->rua);
		$stdEnderDest->nro = $this->retiraAcentos($venda->cliente->numero);
		$stdEnderDest->xCpl = $this->retiraAcentos($venda->cliente->complemento);
		$stdEnderDest->xBairro = $this->retiraAcentos($venda->cliente->bairro);

		$telefone = $venda->cliente->telefone;
		$telefone = preg_replace('/[^0-9]/', '', $telefone);

		if(substr($telefone, 0, 3) == '+55'){
			$telefone = substr($telefone, 3, strlen($telefone));
		}
		$stdEnderDest->fone = $telefone;

		if($venda->cliente->cod_pais == 1058){

			$stdEnderDest->cMun = $venda->cliente->cidade->codigo;
			$stdEnderDest->xMun = strtoupper($this->retiraAcentos($venda->cliente->cidade->nome));
			$stdEnderDest->UF = $venda->cliente->cidade->uf;

			$cep = preg_replace('/[^0-9]/', '', $venda->cliente->cep);

			$stdEnderDest->CEP = $cep;
			$stdEnderDest->cPais = "1058";
			$stdEnderDest->xPais = "BRASIL";
		}else{
			$stdEnderDest->cMun = 9999999;
			$stdEnderDest->xMun = "EXTERIOR";
			$stdEnderDest->UF = "EX";
			$stdEnderDest->cPais = $venda->cliente->cod_pais;
			$stdEnderDest->xPais = $venda->cliente->getPais();	
		}

		$enderDest = $nfe->tagenderDest($stdEnderDest);

		if($venda->cliente->rua_entrega != ""){
			$stdEnderDestEntrega = new \stdClass();

			$stdEnderDestEntrega->xLgr = $this->retiraAcentos($venda->cliente->rua_entrega);
			$stdEnderDestEntrega->nro = $this->retiraAcentos($venda->cliente->numero_entrega);
			$stdEnderDestEntrega->xBairro = $this->retiraAcentos($venda->cliente->bairro_entrega);

			$stdEnderDestEntrega->cMun = $venda->cliente->cidadeEntrega->codigo;
			$stdEnderDestEntrega->xMun = strtoupper($this->retiraAcentos($venda->cliente->cidadeEntrega->nome));
			$stdEnderDestEntrega->UF = $venda->cliente->cidadeEntrega->uf;

			$cep = preg_replace('/[^0-9]/', '', $venda->cliente->cep_entrega);

			$stdEnderDestEntrega->CEP = $cep;
			$stdEnderDestEntrega->cPais = "1058";
			$stdEnderDestEntrega->xPais = "BRASIL";

			$cnpj_cpf = preg_replace('/[^0-9]/', '', $venda->cliente->cpf_cnpj_entrega);
			$stdEnderDestEntrega->xNome = $venda->cliente->nome_entrega;

			if(strlen($cnpj_cpf) == 14){
				$stdEnderDestEntrega->CNPJ = $cnpj_cpf;
			}
			else{
				$stdEnderDestEntrega->CPF = $cnpj_cpf;
			} 

			$enderDestEntrega = $nfe->tagentrega($stdEnderDestEntrega);

		}

		$somaProdutos = 0;
		$somaICMS = 0;
		$somaIPI = 0;
		//PRODUTOS
		$itemCont = 0;

		$totalItens = count($venda->itens);
		$somaFrete = 0;
		$somaDesconto = 0;
		$somaAcrescimo = 0;
		$somaISS = 0;
		$somaServico = 0;

		$VBC = 0;
		$somaFederal = 0;
		$somaEstadual = 0;
		$somaMunicipal = 0;

		$p = null;
		$anp = false;

		$nfesRef = "";
		foreach($venda->referencias as $r){
			$std = new \stdClass();
			$std->refNFe = $r->chave;
			$nfe->tagrefNFe($std);

			$nfesRef .= " $r->chave ";
		}

		$somaApCredito = 0;
		$somaVICMSST = 0;

		$obsIbpt = "";
		foreach($venda->itens as $i){

			$p = $i;
			$ncm = $i->produto->NCM;
			$ncm = str_replace(".", "", $ncm);

			$ibpt = IBPT::getIBPT($config->UF, $ncm);

			$itemCont++;

			$stdProd = new \stdClass();
			$stdProd->item = $itemCont;

			$cod = $this->validate_EAN13Barcode($i->produto->codBarras);

			$stdProd->cEAN = $cod ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cEANTrib = $cod ? $i->produto->codBarras : 'SEM GTIN';
			// $stdProd->cEAN = $i->produto->codBarras;
			// $stdProd->cEANTrib = $i->produto->codBarras;
			$stdProd->cProd = $i->produto->id;
			if($i->produto->referencia != ''){
				$stdProd->cProd = $i->produto->referencia;
			}

			if($i->produto_nome != null){
				$nomeProduto = $i->produto_nome;
			}else{
				$nomeProduto = $i->produto->nome;
				if($i->produto->grade){
					$nomeProduto .= " ". $i->produto->str_grade;
				}
			}

			if($i->produto->lote){
				$nomeProduto .= " | LOTE: ". $i->produto->lote;
			}
			if($i->produto->vencimento){
				$nomeProduto .= ", VENCIMENTO: ". $i->produto->vencimento;
			}
			$stdProd->xProd = $this->retiraAcentos($nomeProduto);
			
			// if($i->produto->CST_CSOSN == '500' || $i->produto->CST_CSOSN == '60'){
			// 	$stdProd->cBenef = 'SEM CBENEF';
			// }

			if($i->produto->cBenef){
				$stdProd->cBenef = $i->produto->cBenef;
			}

			$i->produto->perc_iss = 0;
			if($i->produto->perc_iss > 0){
				$stdProd->NCM = '00';
			}else{
				$stdProd->NCM = $ncm;
			}

			if($venda->natureza->sobrescreve_cfop == 0){
				$stdProd->CFOP = $config->UF != $venda->cliente->cidade->uf ?
				$i->produto->CFOP_saida_inter_estadual : $i->produto->CFOP_saida_estadual;
			}else{
				$stdProd->CFOP = $config->UF != $venda->cliente->cidade->uf ?
				$venda->natureza->CFOP_saida_inter_estadual : $venda->natureza->CFOP_saida_estadual;
			}
			$stdProd->uCom = $i->produto->unidade_venda;
			$stdProd->qCom = $i->quantidade;
			$stdProd->vUnCom = $this->format($i->valor, $config->casas_decimais);

			if($i->produto->unidade_tributavel == ''){
				$stdProd->uTrib = $i->produto->unidade_venda;
			}else{
				$stdProd->uTrib = $i->produto->unidade_tributavel;
			}

			// $stdProd->qTrib = $i->quantidade;
			if($i->produto->quantidade_tributavel == 0){
				$stdProd->qTrib = $i->quantidade;
			}else{
				$stdProd->qTrib = $i->produto->quantidade_tributavel * $i->quantidade;
			}

			$stdProd->vProd = $this->format(($i->quantidade * $i->valor), $config->casas_decimais);

			$stdProd->vUnTrib = $this->format($i->valor, $config->casas_decimais);
			if($i->produto->quantidade_tributavel > 0){
				$stdProd->vUnTrib = $stdProd->vProd/$stdProd->qTrib;
			}
			$stdProd->indTot = $i->produto->perc_iss > 0 ? 0 : 1;
			$somaProdutos += $stdProd->vProd;

			$vDesc = 0;
			if($venda->desconto > 0.01 && $somaDesconto < $venda->desconto){

				if($itemCont < sizeof($venda->itens)){
					$totalVenda = $venda->valor_total;

					$media = (((($stdProd->vProd - $totalVenda)/$totalVenda))*100);
					$media = 100 - ($media * -1);

					$tempDesc = ($venda->desconto*$media)/100;
					$tempDesc -= 0.01;
					if($tempDesc > 0.01){
						$somaDesconto += $this->format($tempDesc);
						$stdProd->vDesc = $this->format($tempDesc);
					}else{
						if(sizeof($venda->itens) > 1){
							$somaDesconto += 0.01;
							$stdProd->vDesc = $this->format(0.01);
						}else{
							$somaDesconto = $venda->desconto;
							$stdProd->vDesc = $this->format($somaDesconto);
						}
					}

				}else{
					if(($venda->desconto - $somaDesconto) > 0.01){
						$stdProd->vDesc = $this->format($venda->desconto - $somaDesconto, $config->casas_decimais);
					}
				}

			}
			if($venda->acrescimo > 0.01 && $somaAcrescimo < $venda->acrescimo){

				if($itemCont < sizeof($venda->itens)){
					$totalVenda = $venda->valor_total;

					$media = (((($stdProd->vProd - $totalVenda)/$totalVenda))*100);
					$media = 100 - ($media * -1);

					$tempDesc = ($venda->acrescimo*$media)/100;
					$tempDesc -= 0.01;
					if($tempDesc > 0.01){
						$somaAcrescimo += $this->format($tempDesc);
						$stdProd->vOutro = $this->format($tempDesc);
					}else{
						if(sizeof($venda->itens) > 1){
							$somaAcrescimo += 0.01;
							$stdProd->vOutro = $this->format(0.01);
						}else{
							$somaAcrescimo = $venda->acrescimo;
							$stdProd->vOutro = $this->format($somaAcrescimo);
						}
					}

				}else{
					if(($venda->acrescimo - $somaAcrescimo) > 0.01){
						$stdProd->vOutro = $this->format($venda->acrescimo - $somaAcrescimo, $config->casas_decimais);
					}
				}
			}

			// if($venda->frete){
			// 	if($venda->frete->valor > 0){
			// 		$somaFrete += $vFt = $venda->frete->valor/$totalItens;
			// 		$stdProd->vFrete = $this->format($vFt);
			// 	}
			// }
			if($venda->frete){
				if($venda->frete->valor > 0){
					if($itemCont < sizeof($venda->itens)){
						$somaFrete += $vFt = 
						$this->format($venda->frete->valor/$totalItens, 2);
						$stdProd->vFrete = $this->format($vFt);
					}else{
						$stdProd->vFrete = $this->format(($venda->frete->valor-$somaFrete), 2);
					}
				}
			}

			if($i->x_pedido != ""){
				$stdProd->xPed = substr($i->x_pedido, 0, 15);
			}
			if($i->num_item_pedido != ""){
				$stdProd->nItemPed = substr($i->num_item_pedido, 0, 15);
			}
			$prod = $nfe->tagprod($stdProd);

			if(strlen(trim($i->produto->info_adicional_item)) > 1){
				$std = new \stdClass();
				$std->item = $itemCont;
				$std->infAdProd = $i->produto->info_adicional_item;
				$nfe->taginfAdProd($std);
			}

		//TAG IMPOSTO

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;
			if($i->produto->perc_iss > 0){
				$stdImposto->vTotTrib = 0.00;
			}

			// if($ibpt != null){
			// 	// $vProd = $stdProd->vProd;
			// 	// $somaFederal = ($vProd*($ibpt->nacional_federal/100));
			// 	// $somaEstadual += ($vProd*($ibpt->estadual/100));
			// 	// $somaMunicipal += ($vProd*($ibpt->municipal/100));
			// 	// $soma = $somaFederal + $somaEstadual + $somaMunicipal;
			// 	// $stdImposto->vTotTrib = $soma;

			// 	$vProd = $stdProd->vProd;

			// 	$federal = ($vProd*($ibpt->nacional_federal/100));
			// 	$somaFederal += $federal;

			// 	$estadual = ($vProd*($ibpt->estadual/100));
			// 	$somaEstadual += $estadual;

			// 	$municipal = ($vProd*($ibpt->municipal/100));
			// 	$somaMunicipal += $municipal;
			// 	$soma = $federal + $estadual + $municipal;

			// 	$stdImposto->vTotTrib = $soma;
			// }
			if($stdProd->CFOP != '6909' && $stdProd->CFOP != '5909'){
				if($i->produto->ibpt){
					$vProd = $stdProd->vProd;
					if($i->produto->origem == 1 || $i->produto->origem == 2){
						$federal = $this->format(($vProd*($i->produto->ibpt->importado/100)), 2);
					}else{
						$federal = $this->format(($vProd*($i->produto->ibpt->nacional/100)), 2);
					}
					$somaFederal += $federal;

					$estadual = $this->format(($vProd*($i->produto->ibpt->estadual/100)), 2);
					$somaEstadual += $estadual;

					$municipal = $this->format(($vProd*($i->produto->ibpt->municipal/100)), 2);
					$somaMunicipal += $municipal;

					$soma = $federal + $estadual + $municipal;
					$stdImposto->vTotTrib = $soma;

					$obsIbpt = " FONTE: " . $i->produto->ibpt->fonte ?? '';
					$obsIbpt .= " VERSAO: " . $i->produto->ibpt->versao ?? '';
					$obsIbpt .= " | ";

				}else{
					if($ibpt != null){

						$vProd = $stdProd->vProd;

						if($i->produto->origem == 1 || $i->produto->origem == 2){
							$federal = $this->format(($vProd*($ibpt->importado_federal/100)), 2);

						}else{
							$federal = $this->format(($vProd*($ibpt->nacional_federal/100)), 2);
						}
						$somaFederal += $federal;

						$estadual = $this->format(($vProd*($ibpt->estadual/100)), 2);
						$somaEstadual += $estadual;

						$municipal = $this->format(($vProd*($ibpt->municipal/100)), 2);
						$somaMunicipal += $municipal;

						$soma = $federal + $estadual + $municipal;
						$stdImposto->vTotTrib = $soma;

						$obsIbpt = " FONTE: " . $ibpt->versao ?? '';
						$obsIbpt .= " | ";
					}
				}
			}

			$imposto = $nfe->tagimposto($stdImposto);

			if($venda->natureza->CST_CSOSN){
				$i->produto->CST_CSOSN = $venda->natureza->CST_CSOSN;
			}

			if($i->produto->CST_CSOSN == '00'){
				$i->produto->pRedBC = 0;
			}

			// ICMS
			if($i->produto->perc_iss == 0){
				// regime normal
				if($tributacao->regime == 1){ 
				//$venda->produto->CST  CST

					$percentualUf = $i->percentualUf($venda->cliente->cidade->uf);

					$stdICMS = new \stdClass();
					if($stdIde->idDest == 2 && $venda->cliente->contribuinte == 0 && $tributacao->regime == 1){
						$difal = Difal::where('cfop', $stdProd->CFOP)
						->where('empresa_id', $this->empresa_id)
						->where('uf', $stdEnderDest->UF)->first();

						if($difal){
							$i->produto->perc_icms = $difal->pICMSInter;
						}
					}
					if($percentualUf == null){
						$stdICMS->pICMS = $this->format($i->produto->perc_icms);
					}else{
						//aqui se tem percentual do estado do cliente
						$stdICMS->pICMS = $this->format($percentualUf->percentual_icms);
						if($percentualUf->percentual_red_bc > 0){
							$i->produto->pRedBC = $percentualUf->percentual_red_bc;
						}
					}

					$stdICMS->item = $itemCont; 
					$stdICMS->orig = $i->produto->origem;
					if($i->produto->CST_CSOSN == '10'){
						$stdICMS->modBCST = $i->produto->modBCST;
						$stdICMS->vBCST = $stdProd->vProd;
						$stdICMS->pICMSST = $this->format($i->produto->pICMSST);
						$somaVICMSST += $stdICMS->vICMSST = $stdICMS->vBCST * ($stdICMS->pICMSST/100);
					}

					if($venda->cliente->consumidor_final){
						if($venda->cliente->cod_pais == 1058){
							if($config->sobrescrita_csonn_consumidor_final != ""){
								$stdICMS->CST = $config->sobrescrita_csonn_consumidor_final;
							}else{
								$stdICMS->CST = $i->produto->CST_CSOSN;
							}
						}else{
							$stdICMS->CST = $i->produto->CST_CSOSN_EXP;
						}
					}else{
						if($venda->cliente->cod_pais == 1058){
							$stdICMS->CST = $i->produto->CST_CSOSN;
						}else{
							$stdICMS->CST = $i->produto->CST_CSOSN_EXP;
						}
					}
					// $stdICMS->modBC = 0;
					$stdICMS->modBC = $i->produto->modBC;
					$stdICMS->vBC = $stdProd->vProd + $stdProd->vFrete + $stdProd->vOutro - $stdProd->vDesc;
					$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS/100);

					if($i->produto->pRedBC == 0){
						if($i->produto->CST_CSOSN == '500'){
							$stdICMS->pRedBCEfet = 0.00;
							$stdICMS->vBCEfet = 0.00;
							$stdICMS->pICMSEfet = 0.00;
							$stdICMS->vICMSEfet = 0.00;
						}else if($i->produto->CST_CSOSN == '60'){
							$stdICMS->vBCSTRet = 0.00;
							$stdICMS->vICMSSTRet = 0.00;
							$stdICMS->vBCSTDest = 0.00;
							$stdICMS->vICMSSTDest = 0.00;

						} else if ($i->produto->CST_CSOSN == '40' || $i->produto->CST_CSOSN == '41') {
                          $stdICMS->vICMS = 0;
                          $stdICMS->vBC = 0;
} else if ($i->produto->CST_CSOSN == '51') {
    // Garantindo que a base de cálculo está correta
    $stdICMS->vBC = $this->format($stdProd->vProd + $stdProd->vFrete);
    $stdICMS->pICMS = $this->format($i->produto->perc_icms);

    // Cálculo do ICMS da operação
    $stdICMS->vICMSOp = $this->format($stdICMS->vBC * ($stdICMS->pICMS / 100));

    // Percentual de diferimento (Se não informado, assume 100%)
    $stdICMS->pDif = $this->format($i->produto->pDif ?? 100.00);

    // Valor do ICMS Diferido
    $stdICMS->vICMSDif = $this->format($stdICMS->vICMSOp * ($stdICMS->pDif / 100));

    // Valor do ICMS devido (após diferimento)
    $stdICMS->vICMS = $this->format($stdICMS->vICMSOp - $stdICMS->vICMSDif);

    // 🔥 Correção: Ajustando a soma correta da Base de Cálculo e ICMS
    $VBC += $stdICMS->vBC; // ✅ Base de cálculo não deve ser reduzida para CST 51
    $somaICMS += $stdICMS->vICMS;

    // Ajustando tags obrigatórias no XML
    $stdICMS->modBC = 3; // Modalidade da Base de Cálculo (3 = Valor da operação)
    $stdICMS->orig = $i->produto->origem;
    $stdICMS->CST = '51';

    // Adicionando `vBCFCPUFDest` caso necessário
    $stdICMS->vBCFCPUFDest = $stdICMS->vBC;
}

 else {

							if($i->produto->CST_CSOSN != '61'){

								if($stdICMS->pICMS > 0){
									$VBC += $stdICMS->vBC;
									$somaICMS += $this->format($stdICMS->vICMS);
								}else{
									$stdICMS->vBC = 0;
								}
							}
						}
					}else{

						$tempB = 100-$i->produto->pRedBC;
						// $tempB = $i->produto->pRedBC;

						$v = $stdProd->vProd * ($tempB/100);
						$v += $stdProd->vFrete;
						if($i->produto->CST_CSOSN != '61'){
							// echo $this->format($v);
							// die;
							$stdICMS->vBC = $this->format($v);
							$stdICMS->pICMS = $this->format($i->produto->perc_icms);
							$stdICMS->vICMS = ($stdProd->vProd * ($tempB/100)) * ($stdICMS->pICMS/100);
							$stdICMS->pRedBC = $this->format($i->produto->pRedBC);
							if($i->produto->CST_CSOSN != '40' && $i->produto->CST_CSOSN != '41' && $i->produto->CST_CSOSN != '51'){
								$somaICMS += $this->format($stdICMS->vICMS);
								$VBC += $stdICMS->vBC;
							}
						}
					}

					if($i->produto->CST_CSOSN == '61'){
						$stdICMS->qBCMonoRet = $this->format($stdProd->qTrib);
						$stdICMS->adRemICMSRet = $this->format($i->produto->adRemICMSRet, 4);
						$stdICMS->vICMSMonoRet = $this->format($i->produto->adRemICMSRet*$stdProd->qTrib, 4);
					}
					if($i->produto->CST_CSOSN == '60'){
						$ICMS = $nfe->tagICMSST($stdICMS);
					}else{
						$ICMS = $nfe->tagICMS($stdICMS);
					}
					// regime simples
				}else{ 
				//$venda->produto->CST CSOSN
					$stdICMS = new \stdClass();

					$stdICMS->item = $itemCont; 
					$stdICMS->orig = $i->produto->origem;
					// $stdICMS->CSOSN = $i->produto->CST_CSOSN;
					if($venda->cliente->consumidor_final){
						if($venda->cliente->cod_pais == 1058){
							if($config->sobrescrita_csonn_consumidor_final != ""){
								$stdICMS->CSOSN = $config->sobrescrita_csonn_consumidor_final;
							}else{
								$stdICMS->CSOSN = $i->produto->CST_CSOSN;
							}
						}else{
							$stdICMS->CSOSN = $i->produto->CST_CSOSN_EXP;
						}
					}else{
						if($venda->cliente->cod_pais == 1058){
							$stdICMS->CSOSN = $i->produto->CST_CSOSN;
						}else{
							$stdICMS->CSOSN = $i->produto->CST_CSOSN_EXP;
						}
					}

					if($i->produto->CST_CSOSN == '500'){
						$stdICMS->vBCSTRet = 0.00;
						$stdICMS->pST = 0.00;
						$stdICMS->vICMSSTRet = 0.00;
					}

					$stdICMS->modBC = $i->produto->modBC;

					if($i->produto->perc_icms > 0){
						$stdICMS->vBC = $stdProd->vProd + $stdProd->vFrete + $stdProd->vOutro - $stdProd->vDesc;
					}else{
						$stdICMS->vBC = 0;
					}

					if($i->produto->CST_CSOSN == '201'){
						if($i->produto->perc_mva > 0){
							$stdICMS->pMVAST= $this->format($i->produto->perc_mva);
						}
						$stdICMS->modBCST = $i->produto->modBCST;
						$stdICMS->vBCST = $stdICMS->vBC * ($i->produto->perc_mva/100);
						$stdICMS->pICMSST = $this->format($i->produto->pICMSST);
						$somaVICMSST += $stdICMS->vICMSST = $stdICMS->vBCST * ($stdICMS->pICMSST/100);
					}

					$stdICMS->pICMS = $this->format($i->produto->perc_icms);
					$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS/100);

					if($tributacao->perc_ap_cred > 0 && $stdICMS->CSOSN == 101){
						$stdICMS->pCredSN = $this->format($tributacao->perc_ap_cred);
						$somaApCredito += $stdICMS->vCredICMSSN = $this->format($stdProd->vProd*($tributacao->perc_ap_cred/100));
					}else{
						$stdICMS->pCredSN = 0;
						$stdICMS->vCredICMSSN = 0;
					}

					if($i->produto->CST_CSOSN == '61'){
						$stdICMS->CST = $i->produto->CST_CSOSN;
						$stdICMS->qBCMonoRet = $this->format($stdProd->qTrib);
						$stdICMS->adRemICMSRet = $this->format($i->produto->adRemICMSRet, 4);
						$stdICMS->vICMSMonoRet = $this->format($i->produto->adRemICMSRet*$stdProd->qTrib, 4);
						$ICMS = $nfe->tagICMS($stdICMS);
					}else{
						$ICMS = $nfe->tagICMSSN($stdICMS);
					}

					if($i->produto->perc_icms > 0 && $stdICMS->CSOSN == 900){
						$VBC += $stdProd->vProd + $stdProd->vFrete;
						$somaICMS += $stdICMS->vBC * ($stdICMS->pICMS/100);

					}

					// $VBC = 0;
					// $somaICMS = 0;
				}
			} else {

				$valorIss = ($i->valor * $i->quantidade * $i->quantidade_dimensao) - $vDesc;
				$somaServico += $valorIss;
				$valorIss = $valorIss * ($i->produto->perc_iss/100);
				$somaISS += $valorIss;

				$std = new \stdClass();
				$std->item = $itemCont; 
				$std->vBC = $stdProd->vProd;
				$std->vAliq = $i->produto->perc_iss;
				$std->vISSQN = $this->format($valorIss);
				$std->cMunFG = $config->codMun;
				$std->cListServ = $i->produto->cListServ;
				$std->indISS = 1;
				$std->indIncentivo = 1;

				$nfe->tagISSQN($std);
			}

				//PIS
			$vbcPis = $stdICMS->vBC;
			if($tributacao->exclusao_icms_pis_cofins){
				$vbcPis -= $stdICMS->vICMS;
			}
			$stdPIS = new \stdClass();
			$stdPIS->item = $itemCont; 
			$stdPIS->CST = $i->produto->CST_PIS;
			$stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $vbcPis : 0.00;
			$stdPIS->pPIS = $this->format($i->produto->perc_pis);
			$stdPIS->vPIS = $this->format(($vbcPis) * 
				($i->produto->perc_pis/100));
			$PIS = $nfe->tagPIS($stdPIS);

				//COFINS
			$vbcCofins = $stdICMS->vBC;
			if($tributacao->exclusao_icms_pis_cofins){
				$vbcCofins -= $stdICMS->vICMS;
			}
			$stdCOFINS = new \stdClass();
			$stdCOFINS->item = $itemCont; 
			$stdCOFINS->CST = $i->produto->CST_COFINS;
			$stdCOFINS->vBC = $this->format($i->produto->perc_cofins) > 0 ? $vbcCofins : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->produto->perc_cofins);
			$stdCOFINS->vCOFINS = $this->format(($vbcCofins) * 
				($i->produto->perc_cofins/100));

			$COFINS = $nfe->tagCOFINS($stdCOFINS);

			//IPI
			$std = new \stdClass();
			$std->item = $itemCont; 
				//999 – para tributação normal IPI
			$std->cEnq = $i->produto->cenq_ipi ?? '999'; 
			$std->CST = $i->produto->CST_IPI;
			$std->vBC = $this->format($i->produto->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
			$std->pIPI = $this->format($i->produto->perc_ipi);
			if($std->CST != '51' && $std->CST != '53'){
				$somaIPI += $std->vIPI = $this->format($std->vBC * ($std->pIPI/100));
			}

			$nfe->tagIPI($std);



			//TAG ANP

			// if(strlen($i->produto->descricao_anp) > 5){
			// 	$stdComb = new \stdClass();
			// 	$stdComb->item = $itemCont; 
			// 	$stdComb->cProdANP = $i->produto->codigo_anp;
			// 	$stdComb->descANP = $i->produto->descricao_anp; 
			// 	$stdComb->UFCons = $venda->cliente->cidade->uf;

			// 	$nfe->tagcomb($stdComb);
			// }


			if(strlen($i->produto->codigo_anp) > 2){
				$anp = true;
				$stdComb = new \stdClass();
				$stdComb->item = $itemCont; 
				$stdComb->cProdANP = $i->produto->codigo_anp;
				$stdComb->descANP = $i->produto->getDescricaoAnp(); 

				if($i->produto->perc_glp > 0){
					$stdComb->pGLP = $this->format($i->produto->perc_glp);
				}

				if($i->produto->perc_gnn > 0){
					$stdComb->pGNn = $this->format($i->produto->perc_gnn);
				}

				if($i->produto->perc_gni > 0){
					$stdComb->pGNi = $this->format($i->produto->perc_gni);
				}

				$stdComb->vPart = $this->format($i->produto->valor_partida);
				$stdComb->UFCons = $venda->cliente ? $venda->cliente->cidade->uf : $config->UF;
				if($i->produto->pBio > 0){
					$stdComb->pBio = $i->produto->pBio;
				}
				$nfe->tagcomb($stdComb);
			}

			if($stdIde->indFinal == 0 && strlen($i->produto->codigo_anp) > 2){
				$stdOrigComb = new \stdClass();

				$stdOrigComb->item = $itemCont; 
				$stdOrigComb->indImport = $i->produto->indImport;
				$stdOrigComb->cUFOrig = $i->produto->cUFOrig;
				$stdOrigComb->pOrig = $i->produto->pOrig;
				$nfe->tagorigComb($stdOrigComb);
			}

			$cest = $i->produto->CEST;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			if(strlen($cest) > 0){
				$std = new \stdClass();
				$std->item = $itemCont; 
				$std->CEST = $cest;
				$nfe->tagCEST($std);
			}

			if($stdIde->idDest == 2 && $venda->cliente->contribuinte == 0 && $tributacao->regime == 1){
				$difal = Difal::where('cfop', $stdProd->CFOP)
				->where('empresa_id', $this->empresa_id)
				->where('uf', $stdEnderDest->UF)->first();

				if($difal){

					$std = new \stdClass();
					$std->item = $itemCont; 
					$std->vBCUFDest = $stdICMS->vBC;
					// $std->vBCUFDest = $stdICMS->vBC;
					$std->vBCFCPUFDest = $stdICMS->vBC;
					// $std->vBCFCPUFDest = $stdICMS->vBC;
					$std->pFCPUFDest = $this->format($difal->pFCPUFDest);
					$std->pICMSUFDest = $this->format($difal->pICMSUFDest);

					$std->pICMSInter = $this->format($difal->pICMSInter);
					$std->pICMSInterPart = $this->format($difal->pICMSInterPart);
					// $std->vFCPUFDest = $this->format($stdProd->vProd * ($i->produto->perc_fcp_interestadual/100));
					$std->vFCPUFDest = $this->format($std->vBCUFDest * ($std->pFCPUFDest/100));
					// $std->vICMSUFDest = $this->format($stdProd->vProd * ($i->produto->perc_icms_interestadual/100));

					$vICMSUFDest = $std->vBCFCPUFDest * ($std->pICMSInter/100);
					$vICMSUFDestAux = $stdICMS->vBC * ($std->pICMSUFDest/100);
					$std->vICMSUFDest = $this->format($vICMSUFDestAux-$vICMSUFDest);
					// $std->vICMSUFDest = $this->format($vICMSUFDestAux-$vICMSUFDest);
					// $std->vICMSUFDest = $this->format($stdICMS->vBC * ($i->produto->perc_icms_interestadual/100));
					$std->vICMSUFRemet = $this->format($vICMSUFDestAux-$vICMSUFDest) - $std->vICMSUFDest;

					$nfe->tagICMSUFDest($std);
				}
			}

		}

$stdICMSTot = new \stdClass();
$stdICMSTot->vProd = $this->format($somaProdutos, $config->casas_decimais);
$stdICMSTot->vBC = $this->format($VBC); // ✅ Agora `vBC` corresponde ao total da base de cálculo dos itens
$stdICMSTot->vICMS = $this->format($somaICMS);
$stdICMSTot->vICMSDeson = 0.00;
$stdICMSTot->vBCST = 0.00;
$stdICMSTot->vST = 0.00;

if ($venda->frete) {
    $stdICMSTot->vFrete = $this->format($venda->frete->valor);
} else {
    $stdICMSTot->vFrete = 0.00;
}

$stdICMSTot->vSeg = 0.00;
$stdICMSTot->vDesc = $this->format($venda->desconto);
$stdICMSTot->vII = 0.00;
$stdICMSTot->vIPI = 0.00;
$stdICMSTot->vPIS = 0.00;
$stdICMSTot->vCOFINS = 0.00;
$stdICMSTot->vOutro = $this->format($venda->acrescimo);

$stdICMSTot->vNF = $this->format(
    $somaProdutos +
    $stdICMSTot->vFrete +
    $somaIPI -
    $venda->desconto +
    $venda->acrescimo +
    $somaVICMSST
);

$stdICMSTot->vTotTrib = 0.00;
$ICMSTot = $nfe->tagICMSTot($stdICMSTot);


		//inicio totalizao issqn

		if($somaISS > 0){
			$std = new \stdClass();
			$std->vServ = $this->format($somaServico + $venda->desconto);
			$std->vBC = $this->format($somaServico);
			$std->vISS = $this->format($somaISS);
			$std->dCompet = date('Y-m-d');

			$std->cRegTrib = 6;

			$nfe->tagISSQNTot($std);
		}

		//fim totalizao issqn

		$stdTransp = new \stdClass();
		$stdTransp->modFrete = $venda->frete->tipo ?? '9';

		$transp = $nfe->tagtransp($stdTransp);

		if($venda->transportadora){
			$std = new \stdClass();
			$std->xNome = $venda->transportadora->razao_social;

			$std->xEnder = $venda->transportadora->logradouro;
			$std->xMun = $this->retiraAcentos($venda->transportadora->cidade->nome);
			$std->UF = $venda->transportadora->cidade->uf;

			$cnpj_cpf = preg_replace('/[^0-9]/', '', $venda->transportadora->cnpj_cpf);

			if(strlen($cnpj_cpf) == 14) $std->CNPJ = $cnpj_cpf;
			else $std->CPF = $cnpj_cpf;

			$nfe->tagtransporta($std);
		}

		if($venda->frete != null){

			$std = new \stdClass();

			$placa = str_replace("-", "", $venda->frete->placa);
			$std->placa = strtoupper($placa);
			$std->UF = $venda->frete->uf;

			// if($config->UF == $venda->cliente->cidade->uf){
			if($venda->frete->placa != "" && $venda->frete->uf){
				$nfe->tagveicTransp($std);
			}

			if($venda->frete->qtdVolumes > 0 || $venda->frete->peso_liquido > 0
				|| $venda->frete->peso_bruto > 0){
				$stdVol = new \stdClass();
				$stdVol->item = 1;
				$stdVol->qVol = $venda->frete->qtdVolumes;
				$stdVol->esp = $venda->frete->especie;

				$stdVol->nVol = $venda->frete->numeracaoVolumes;
				$stdVol->pesoL = $venda->frete->peso_liquido;
				$stdVol->pesoB = $venda->frete->peso_bruto;
				$vol = $nfe->tagvol($stdVol);
			}
		}

		if($venda->cliente->cod_pais != 1058){
			$std = new \stdClass();
			$std->UFSaidaPais = $config->UF;
			$std->xLocExporta = $config->municipio;
			// $std->xLocDespacho = 'Informação do Recinto Alfandegado';

			$nfe->tagexporta($std);
		}

	//Fatura
		if($somaISS == 0 && $venda->natureza->CFOP_saida_estadual != '5915' && $venda->natureza->CFOP_saida_inter_estadual != '6915'){
			$stdFat = new \stdClass();
			$stdFat->nFat = (int)$lastNumero+1;
			$stdFat->vOrig = $this->format($somaProdutos+$venda->acrescimo+$stdICMSTot->vFrete);
			$stdFat->vDesc = $this->format($venda->desconto);
			// $stdFat->vOutro = $this->format($venda->acrescimo);
			// $stdFat->vLiq = $this->format($somaProdutos-$venda->desconto+$venda->acrescimo);
			$stdFat->vLiq = $this->format($somaProdutos-$venda->desconto+$venda->acrescimo+$stdICMSTot->vFrete);
			// $stdFat->vLiq = $this->format($somaProdutos-$venda->desconto);
			if($venda->tipo_pagamento != '90'){
				$fatura = $nfe->tagfat($stdFat);
			}
		}

	//Duplicata
		if($venda->tipo_pagamento != '90'){
			if($somaISS == 0 && $venda->natureza->CFOP_saida_estadual != '5915' && $venda->natureza->CFOP_saida_inter_estadual != '6915'){
				if(count($venda->duplicatas) > 0){
					$contFatura = 1;
					foreach($venda->duplicatas as $ft){
						$stdDup = new \stdClass();
						if($contFatura < 10){
							$stdDup->nDup = "00".$contFatura;
						}else{
							$stdDup->nDup = "0".$contFatura;
						}
						$stdDup->dVenc = substr($ft->data_vencimento, 0, 10);
						$stdDup->vDup = $this->format($ft->valor_integral);

						$nfe->tagdup($stdDup);
						$contFatura++;
					}
				}else{

					if($venda->forma_pagamento != 'a_vista'){
						$stdDup = new \stdClass();
						$stdDup->nDup = '001';
						$stdDup->dVenc = Date('Y-m-d');
						$stdDup->vDup = $this->format($venda->valor_total-$venda->desconto+$venda->acrescimo, 4);

						$nfe->tagdup($stdDup);
					}
				}
			}
		}

		$stdPag = new \stdClass();
		$pag = $nfe->tagpag($stdPag);

		if(sizeof($venda->duplicatas) > 0){
			foreach($venda->duplicatas as $d){

				$tipo_pagamento = Venda::getTipoPagamentoNFe($d->tipo_pagamento);

				$stdDetPag = new \stdClass();

				$stdDetPag->tPag = $tipo_pagamento; 

				if($tipo_pagamento == '06'){
					$stdDetPag->tPag = '05'; 
				}

				$stdDetPag->vPag = $tipo_pagamento != '90' ? $this->format($d->valor_integral, $config->casas_decimais) : 
				0.00;
				if($tipo_pagamento == '03' || $tipo_pagamento == '04' || $tipo_pagamento == '17'){

					if($venda->cAut_cartao != ""){
						$stdDetPag->cAut = $venda->cAut_cartao;
					}
					if($venda->cnpj_cartao != ""){
						$cnpj = preg_replace('/[^0-9]/', '', $venda->cnpj_cartao);

						$stdDetPag->CNPJ = $cnpj;
					}
					$stdDetPag->tBand = $venda->bandeira_cartao;

					$stdDetPag->tpIntegra = 2;
				}

				$stdDetPag->indPag = $venda->forma_pagamento == 'a_vista' ?  0 : 1; 

				$detPag = $nfe->tagdetPag($stdDetPag);				
			}
		}else{

			$stdDetPag = new \stdClass();

			$stdDetPag->tPag = $venda->tipo_pagamento;
			$stdDetPag->vPag = $venda->tipo_pagamento != '90' ? $this->format($venda->valor_total - 
				$venda->desconto + $venda->acrescimo, $config->casas_decimais) : 0.00; 

			if($venda->descricao_pag_outros != "" && $venda->tipo_pagamento == '90'){
				$stdDetPag->xPag = $venda->descricao_pag_outros;
			}

			if($venda->tipo_pagamento == '03' || $venda->tipo_pagamento == '04' || $venda->tipo_pagamento == '17'){
				if($venda->cAut_cartao != ""){
					$stdDetPag->cAut = $venda->cAut_cartao;
				}
				if($venda->cnpj_cartao != ""){
					$cnpj = preg_replace('/[^0-9]/', '', $venda->cnpj_cartao);

					$stdDetPag->CNPJ = $cnpj;
				}
				$stdDetPag->tBand = $venda->bandeira_cartao;

				$stdDetPag->tpIntegra = 2;
			}

			$stdDetPag->indPag = $venda->forma_pagamento == 'a_vista' ?  0 : 1; 

			$detPag = $nfe->tagdetPag($stdDetPag);
		}


		if($config->ambiente == 2){
			if($venda->pedido_ecommerce_id > 0){
				$stdPag = new \stdClass();
				$stdPag->CNPJ = env("RESP_CNPJ");
				$stdPag->idCadIntTran = env("RESP_NOME");
				$detInf = $nfe->tagIntermed($stdPag);
			}
		}

		$stdInfoAdic = new \stdClass();

		$obs = " " . $venda->observacao;

		if($nfesRef != ""){
			$obs .= " Chaves referênciadas: " . $nfesRef;
		}

		if($somaEstadual > 0 || $somaFederal > 0 || $somaMunicipal > 0){
			$obs .= " Trib. aprox. ";
			if($somaFederal > 0){
				$obs .= "R$ " . number_format($somaFederal, 2, ',', '.') ." Federal"; 
			}
			if($somaEstadual > 0){
				$obs .= ", R$ ".number_format($somaEstadual, 2, ',', '.')." Estadual"; 
			}
			if($somaMunicipal > 0){
				$obs .= ", R$ ".number_format($somaMunicipal, 2, ',', '.')." Municipal"; 
			}
			// $ibpt = IBPT::where('uf', $config->UF)->first();

			$obs .= $obsIbpt;
		}
		// $stdInfoAdic->infCpl = $obs;
		if($p->produto->renavam != ''){
			$veiCpl = ' | RENAVAM ' . $p->produto->renavam;
			if($p->produto->placa != '') $veiCpl .= ', PLACA ' . $p->produto->placa;
			if($p->produto->chassi != '') $veiCpl .= ', CHASSI ' . $p->produto->chassi;
			if($p->produto->combustivel != '') $veiCpl .= ', COMBUSTÍVEL ' . $p->produto->combustivel;
			if($p->produto->ano_modelo != '') $veiCpl .= ', ANO/MODELO ' . $p->produto->ano_modelo;
			if($p->produto->cor_veiculo != '') $veiCpl .= ', COR ' . $p->produto->cor_veiculo;

			$obs .= $veiCpl;
		}

		if($venda->vendedor_setado && $venda->vendedor_setado->funcionario){
			$obs .= " | vendedor " . $venda->vendedor_setado->funcionario->nome . " ";
		}

		if($somaApCredito > 0){
			if($config->campo_obs_nfe != ""){
				$msg = $config->campo_obs_nfe;
				$msg = str_replace("%", number_format($tributacao->perc_ap_cred, 2, ",",  ".") . "%", $msg);
				$msg = str_replace('R$', 'R$ ' . number_format($somaApCredito, 2, ",",  "."), $msg);
				$obs .= $msg;
			}
		}elseif($config->campo_obs_nfe != ""){
			$obs .= " ".$config->campo_obs_nfe;
		}

		if($venda->getFormaPagamento($venda->empresa_id) != null){
			$obs .= " Inf. adicional de pagamento: " . $venda->getFormaPagamento($venda->empresa_id)->infos;
		}

		$stdInfoAdic->infCpl = $this->retiraAcentos($obs);

		$infoAdic = $nfe->taginfAdic($stdInfoAdic);

		if($config->aut_xml != ''){
			$std = new \stdClass();
			$cnpj = preg_replace('/[^0-9]/', '', $config->aut_xml);
			$std->CNPJ = $cnpj;
			$aut = $nfe->tagautXML($std);
		}

		$std = new \stdClass();
		$std->CNPJ = env('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato= env('RESP_NOME'); //Nome da pessoa a ser contatada
		$std->email = env('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
		$std->fone = env('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
		$nfe->taginfRespTec($std);
		
		try{
			$nfe->montaNFe();
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'nNf' => $stdIde->nNF
			];
			return $arr;
		}catch(\Exception $e){
			return [
				'erros_xml' => $nfe->getErrors()
			];
		}
	}

	private function validate_EAN13Barcode($ean)
	{

		$sumEvenIndexes = 0;
		$sumOddIndexes  = 0;

		$eanAsArray = array_map('intval', str_split($ean));

		if(strlen($ean) == 14){
			return true;
		}

		if (!$this->has13Numbers($eanAsArray) ) {
			return false;
		};

		for ($i = 0; $i < count($eanAsArray)-1; $i++) {
			if ($i % 2 === 0) {
				$sumOddIndexes  += $eanAsArray[$i];
			} else {
				$sumEvenIndexes += $eanAsArray[$i];
			}
		}

		$rest = ($sumOddIndexes + (3 * $sumEvenIndexes)) % 10;

		if ($rest !== 0) {
			$rest = 10 - $rest;
		}

		return $rest === $eanAsArray[12];
	}

	private function has13Numbers(array $ean)
	{
		return count($ean) === 13 || count($ean) === 14;
	}

	private function retiraAcentos($texto){
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç)/"),explode(" ","a A e E i I o O u U n N c"),$texto);
	}

	public function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	public function consultaCadastro($cnpj, $uf){
		try {

			$iest = '';
			$cpf = '';
			$response = $this->tools->sefazCadastro($uf, $cnpj, $iest, $cpf);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			return [
				'erro' => false,
				'json' => $json
			];

		} catch (\Exception $e) {
			return [
				'erro' => true,
				'json' => $e->getMessage()
			];

		}
	}

	public function consultaChave($chave){
		$response = $this->tools->sefazConsultaChave($chave);

		$stdCl = new Standardize($response);
		$arr = $stdCl->toArray();
		return $arr;
	}

	public function inutilizar($config, $nInicio, $nFinal, $justificativa){
		try{

			$nSerie = $config->numero_serie_nfe;
			$nIni = $nInicio;
			$nFin = $nFinal;
			$xJust = $justificativa;
			$response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			return $arr;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function cancelar($vendaId, $justificativa){
		try {
			$venda = Venda::
			where('id', $vendaId)
			->first();

			$chave = $venda->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			sleep(4);
				// return $arr;
			$xJust = $justificativa;

			if(!isset($arr['protNFe'])){
				return ['erro' => true, 'data' => $arr['cStat'] . ' - ' . $arr['xMotivo'], 'status' => 402];	
			}
			$nProt = $arr['protNFe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);
			sleep(1);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				// $public = env('SERVIDOR_WEB') ? 'public/' : '';
				if ($cStat == '101' || $cStat == '135' || $cStat == '155' ) {
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_cancelada/').$chave.'.xml',$xml);

					return $json;
				} else {
					
					return ['erro' => true, 'data' => $arr, 'status' => 402];	
				}
			}    
		} catch (\Exception $e) {
			// echo $e->getMessage();
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];	
    //TRATAR
		}
	}

	public function cartaCorrecao($id, $correcao){
		try {

			$venda = Venda::
			where('id', $id)
			->first();

			$chave = $venda->chave;
			$xCorrecao = $correcao;
			$nSeqEvento = $venda->sequencia_cce+1;
			$response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
			sleep(2);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '135' || $cStat == '136') {
					// $public = env('SERVIDOR_WEB') ? 'public/' : '';
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_correcao/').$chave.'.xml',$xml);

					$venda->sequencia_cce = $venda->sequencia_cce + 1;
					$venda->save();
					return $json;

				} else {
            //houve alguma falha no evento 
					return ['erro' => true, 'data' => $arr, 'status' => 402];	
            //TRATAR
				}
			}    
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function sign($xml){
		try{
			return $this->tools->signNFe($xml);
		}catch(\Exception $e){
			echo $e->getMessage() . "<br>";
			echo $e->getLine();
			die;
		}
	}

	public function consultaStatus($tpAmb, $uf){
		try{
			$response = $this->tools->sefazStatus($uf, $tpAmb);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			return $arr;
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function transmitir($signXml, $chave, $venda_id = null){
		try{
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);

			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);

			$st = new Standardize();
			$std = $st->toStd($resp);
			sleep($this->timeout);
			if ($std->cStat != 103) {

				return "Erro: [$std->cStat] - $std->xMotivo";
			}
			$recibo = $std->infRec->nRec;
			if($venda_id != null){
				$venda = Venda::where('id', $venda_id)->first();
				if($venda != null && $venda->recibo == null){
					$venda->recibo = $recibo;
					$venda->save();
				}
			}

			$protocolo = $this->tools->sefazConsultaRecibo($recibo);

			sleep(1);
			//return $protocolo;
			// $public = env('SERVIDOR_WEB') ? 'public/' : '';
			try {
				$xml = Complements::toAuthorize($signXml, $protocolo);
				file_put_contents(public_path('xml_nfe/').$chave.'.xml',$xml);
				return $recibo;
				// $this->printDanfe($xml);
			} catch (\Exception $e) {
				return "Erro: " . $st->toJson($protocolo);
			}

		} catch(\Exception $e){
			return "Erro: ".$e->getMessage() ;
		}

	}

	public function consultar($vendaId){
		try {
			$venda = Venda::
			where('id', $vendaId)
			->first();
			$this->tools->model('55');

			$chave = $venda->chave;

			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			if($arr['xMotivo'] == 'Autorizado o uso da NF-e'){
				if($venda->estado != 'APROVADO'){

					$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

					// $nRec = $arr['protNFe']['infProt']['nProt'];
					$chave = $arr['protNFe']['infProt']['chNFe'];
					$nRec = $venda->recibo;
					$protocolo = $this->tools->sefazConsultaRecibo($nRec);
					sleep(3);
					$st = new Standardize();
					$std = $st->toStd($protocolo);
					// return $std;
					if($std->protNFe->infProt->cStat == 100){
						// $venda->chave = $chave;
						$venda->estado = 'APROVADO';
						$venda->NfNumero = $config->ultimo_numero_nfe+1;
						$venda->save();

						$config->ultimo_numero_nfe = $config->ultimo_numero_nfe+1;
						$config->save();
						$xml = Complements::toAuthorize($venda->signed_xml, $protocolo);
						file_put_contents(public_path('xml_nfe/').$chave.'.xml',$xml);
						// return $xml;
					}
				}
			}

			return json_encode($arr);

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}


}
