<?php

namespace App\Services;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;

use App\Models\ConfigNota;
use App\Models\Certificado;
use App\Models\Venda;
use App\Models\Compra;
use App\Models\IBPT;
use NFePHP\NFe\Complements;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Tributacao;
use App\Models\Filial;
use App\Models\ConfigSystem;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFeEntradaService {

	private $config; 
	private $tools;
	protected $empresa_id = null;
	protected $timeout = 8;

	public function __construct($config, $modelo){
		$value = session('user_logged');
		$this->empresa_id = $value['empresa'];
		$this->config = $config;

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
		$this->tools->model($modelo);
		$config = ConfigSystem::first();
		if($config){
			if($config->timeout_nfe){
				$this->timeout = $config->timeout_nfe;
			}
		}
	}

	public function gerarNFe($compra, $natureza, $tipoPagamento){
		
		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first(); // iniciando os dados do emitente NF

		if($compra->filial_id != null){
			$casas_decimais = $config->casas_decimais;
			$config = Filial::findOrFail($compra->filial_id);
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

		$vendaLast = Venda::lastNF();
		if($compra->filial_id != null){
			$vendaLast = $config->ultimo_numero_nfe;
		}
		$compraLast = $vendaLast;
		
		$stdIde = new \stdClass();
		$stdIde->cUF = $config->cUF;
		$stdIde->cNF = rand(11111,99999);
		// $stdIde->natOp = $venda->natureza->natureza;
		$stdIde->natOp = $natureza->natureza;

		// $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

		$stdIde->mod = 55;
		$stdIde->serie = $config->numero_serie_nfe;
		$stdIde->nNF = (int)$compraLast+1;
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = 0; // 0 Entrada;

		if($compra->fornecedor->cod_pais == 1058){
			$stdIde->idDest = $config->UF != $compra->fornecedor->cidade->uf ? 2 : 1;
		}else{
			$stdIde->idDest = 3;
		}

		$stdIde->cMunFG = $config->codMun;
		// $stdIde->tpImp = 1;
		$stdIde->tpImp = $config->tipo_impressao_danfe;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = $config->ambiente;
		$stdIde->finNFe = $natureza->finNFe;
		$stdIde->indFinal = 1;
		$stdIde->indPres = 1;
		$stdIde->procEmi = '0';
		// $stdIde->verProc = '2.0';

		$stdIde->verProc = '3.10.31';
		if($config->ambiente == 2){
			$stdIde->indIntermed = 0;
		}
		// $stdIde->dhCont = null;
		// $stdIde->xJust = null;


		//
		$tagide = $nfe->tagide($stdIde);

		$stdEmit = new \stdClass();
		$stdEmit->xNome = $config->razao_social;
		$stdEmit->xFant = $config->nome_fantasia;

		$ie = preg_replace('/[^0-9]/', '', $config->ie);

		$stdEmit->IE = $ie;
		// $stdEmit->CRT = $tributacao->regime == 0 ? 1 : 3;
		$stdEmit->CRT = ($tributacao->regime == 0 || $tributacao->regime == 2) ? 1 : 3;

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
		
		if(strlen($cnpj) == 14){
			$stdEmit->CNPJ = $cnpj;
		}else{
			$stdEmit->CPF = $cnpj;
		}

		$emit = $nfe->tagemit($stdEmit);

		// ENDERECO EMITENTE
		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $config->logradouro;
		$stdEnderEmit->nro = $config->numero;
		$stdEnderEmit->xCpl = "";
		$stdEnderEmit->xBairro = $config->bairro;
		$stdEnderEmit->cMun = $config->codMun;
		$stdEnderEmit->xMun = $config->municipio;
		$stdEnderEmit->UF = $config->UF;

		$cep = str_replace("-", "", $config->cep);
		$cep = str_replace(".", "", $cep);
		$stdEnderEmit->CEP = $cep;
		$stdEnderEmit->cPais = $config->codPais;
		$stdEnderEmit->xPais = $config->pais;

		$enderEmit = $nfe->tagenderEmit($stdEnderEmit);

		// DESTINATARIO
		$stdDest = new \stdClass();
		$stdDest->xNome = $compra->fornecedor->razao_social;

		if($compra->fornecedor->cod_pais != 1058){
			$stdDest->indIEDest = "9";
			$stdDest->idEstrangeiro = $compra->fornecedor->id_estrangeiro;
		}else{
			if($compra->fornecedor->contribuinte){
				if($compra->fornecedor->ie_rg == 'ISENTO'){
					$stdDest->indIEDest = "2";
				}else{
					$stdDest->indIEDest = "1";
				}

			}else{
				$stdDest->indIEDest = "9";
			}

			$cnpj_cpf = preg_replace('/[^0-9]/', '', $compra->fornecedor->cpf_cnpj);

			if(strlen($cnpj_cpf) == 14){
				$stdDest->CNPJ = $cnpj_cpf;
				$ie = preg_replace('/[^0-9]/', '', $compra->fornecedor->ie_rg);

				$stdDest->IE = $ie;

			}
			else{

				$stdDest->CPF = $cnpj_cpf;
				$ie = preg_replace('/[^0-9]/', '', $compra->fornecedor->ie_rg);

				if(strtolower($ie) != "isento" && $compra->fornecedor->contribuinte)
					$stdDest->IE = $ie;
				$pFisica = true;

			}
		}

		$dest = $nfe->tagdest($stdDest);

		$stdEnderDest = new \stdClass();
		$stdEnderDest->xLgr = $compra->fornecedor->rua;
		$stdEnderDest->nro = $compra->fornecedor->numero;
		$stdEnderDest->xCpl = $compra->fornecedor->complemento;
		$stdEnderDest->xBairro = $compra->fornecedor->bairro;
		if($compra->fornecedor->cod_pais == 1058){
			$stdEnderDest->cMun = $compra->fornecedor->cidade->codigo;
			$stdEnderDest->xMun = $this->retiraAcentos($compra->fornecedor->cidade->nome);
			$stdEnderDest->UF = $compra->fornecedor->cidade->uf;

			$cep = str_replace("-", "", $compra->fornecedor->cep);
			$cep = str_replace(".", "", $cep);
			$stdEnderDest->CEP = $cep;
			$stdEnderDest->cPais = "1058";
			$stdEnderDest->xPais = "BRASIL";
		}else{
			$stdEnderDest->cMun = 9999999;
			$stdEnderDest->xMun = "EXTERIOR";
			$stdEnderDest->UF = "EX";
			$stdEnderDest->cPais = $compra->fornecedor->cod_pais;
			$stdEnderDest->xPais = $compra->fornecedor->getPais();	
		}

		$enderDest = $nfe->tagenderDest($stdEnderDest);

		$somaProdutos = 0;
		$somaICMS = 0;
		$somaVICMSST = 0;
		//PRODUTOS
		$itemCont = 0;
		$itemContImportacao = 0;

		$totalItens = sizeof($compra->itens);
		$somaFrete = 0;
		$p = null;
		$somaDesconto = 0;
		$somaAcrescimo = 0;

		foreach($compra->chaves as $r){
			$std = new \stdClass();
			$std->refNFe = str_replace(" ", "", $r->chave);
			$nfe->tagrefNFe($std);
		}

		$somaFederal = 0;
		$somaEstadual = 0;
		$somaMunicipal = 0;
		$VBC = 0;

		foreach($compra->itens as $i){
			$itemCont++;
			$p = $i;

			$ncm = $i->produto->NCM;
			$ncm = str_replace(".", "", $ncm);

			$ibpt = IBPT::getIBPT($config->UF, $ncm);

			$stdProd = new \stdClass();
			$stdProd->item = $itemCont;

			$cod = $this->validate_EAN13Barcode($i->produto->codBarras);

			// $stdProd->cEAN = $i->produto->codBarras;
			// $stdProd->cEANTrib = $i->produto->codBarras;
			$stdProd->cEAN = $cod ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cEANTrib = $cod ? $i->produto->codBarras : 'SEM GTIN';

			$stdProd->cProd = $i->produto->id;
			if($i->produto->referencia != ''){
				$stdProd->cProd = $i->produto->referencia;
			}
			$stdProd->xProd = $this->retiraAcentos($i->produto->nome);
			
			$stdProd->NCM = $ncm;

			if($natureza->sobrescreve_cfop == 0){
				$stdProd->CFOP = $config->UF != $compra->fornecedor->cidade->uf ?
				$i->produto->CFOP_entrada_inter_estadual : $i->produto->CFOP_entrada_estadual;
			}else{
				$stdProd->CFOP = $config->UF != $compra->fornecedor->cidade->uf ?
				$natureza->CFOP_entrada_inter_estadual : $natureza->CFOP_entrada_estadual;
			}

			$stdProd->uCom = $i->produto->unidade_compra;
			$stdProd->qCom = $i->quantidade;
			$stdProd->vUnCom = $this->format($i->valor_unitario, $config->casas_decimais);
			$stdProd->vProd = $this->format(($i->quantidade * $i->valor_unitario));

			if($i->produto->unidade_tributavel == ''){
				$stdProd->uTrib = $i->produto->unidade_compra;
			}else{
				$stdProd->uTrib = $i->produto->unidade_tributavel;
			}
			$stdProd->qTrib = $i->quantidade;

			$stdProd->vUnTrib = $this->format($i->valor_unitario, $config->casas_decimais);
			$stdProd->indTot = 1;
			$somaProdutos += ($i->quantidade * $i->valor_unitario);

			// if($compra->desconto > 0){
			// 	if($itemCont < sizeof($compra->itens)){
			// 		$stdProd->vDesc = $this->format($compra->desconto/$totalItens);
			// 		$somaDesconto += $compra->desconto/$totalItens;
			// 	}else{
			// 		$stdProd->vDesc = $compra->desconto - $somaDesconto;
			// 	}
			// }

			$totalCompra = $compra->valor + $compra->desconto;

			if($compra->desconto > 0.01 && $somaDesconto < $compra->desconto){

				if($itemCont < sizeof($compra->itens)){

					$media = (((($stdProd->vProd - $totalCompra)/$totalCompra))*100);
					$media = 100 - ($media * -1);

					$tempDesc = ($compra->desconto*$media)/100;

					if($tempDesc > 0.01){
						$stdProd->vDesc = $this->format($tempDesc);
					}else{
						$stdProd->vDesc = $this->format($somaDesconto);
					}

				}else{
					if(($compra->desconto - $somaDesconto) > 0.01){
						$stdProd->vDesc = $this->format($compra->desconto - $somaDesconto, $config->casas_decimais);
					}
				}
				$somaDesconto += $this->format($stdProd->vDesc);

			}

			if($compra->acrescimo > 0.01 && $somaAcrescimo < $compra->acrescimo){

				if($itemCont < sizeof($compra->itens)){
					$totalCompra = $compra->valor;

					$media = (((($stdProd->vProd - $totalCompra)/$totalCompra))*100);
					$media = 100 - ($media * -1);

					$tempDesc = ($compra->acrescimo*$media)/100;
					$tempDesc -= 0.01;
					if($tempDesc > 0.01){
						$somaAcrescimo += $this->format($tempDesc);
						$stdProd->vOutro = $this->format($tempDesc);
					}else{
						if(sizeof($compra->itens) > 1){
							$somaAcrescimo += 0.01;
							$stdProd->vOutro = $this->format(0.01);
						}else{
							$somaAcrescimo = $compra->acrescimo;
							$stdProd->vOutro = $this->format($somaAcrescimo);
						}
					}

				}else{
					if(($compra->acrescimo - $somaAcrescimo) > 0.01){
						$stdProd->vOutro = $this->format($compra->acrescimo - $somaAcrescimo, $config->casas_decimais);
					}
				}
			}

			if($compra->valor_frete > 0){
				if($itemCont < sizeof($compra->itens)){
					$somaFrete += $vFt = 
					number_format($compra->valor_frete/$totalItens, 2);
					$stdProd->vFrete = $this->format($vFt);
				}else{
					$stdProd->vFrete = $this->format(($compra->valor_frete-$somaFrete), 2);
				}
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

			if($stdProd->CFOP != '6909'){
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

			if($natureza->CST_CSOSN){
				$i->produto->CST_CSOSN_entrada = $natureza->CST_CSOSN;
			}
			// ICMS
			if($tributacao->regime == 1){ // regime normal

				//$venda->produto->CST

				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				if($compra->fornecedor->cod_pais == 1058){
					$stdICMS->CST = $i->produto->CST_CSOSN_entrada;
				}else{
					$stdICMS->CST = $i->produto->CST_CSOSN_EXP;
				}
				$stdICMS->modBC = $i->produto->modBC;
				$stdICMS->vBC = $stdProd->vProd;
				$stdICMS->pICMS = $this->format($i->produto->perc_icms);
				$stdICMS->vICMS = $this->format(($i->valor_unitario * $i->quantidade) 
					* ($stdICMS->pICMS/100));
				// echo $i->produto->CST_CSOSN_entrada;
				// die;
				if($i->produto->CST_CSOSN_entrada != '40' && $i->produto->CST_CSOSN_entrada != '41'){
					$somaICMS += $stdICMS->vICMS;
				}

				if($i->produto->CST_CSOSN_entrada == '60'){
					$stdICMS->vBCSTRet = 0.00;
					$stdICMS->vICMSSTRet = 0.00;
					$stdICMS->vBCSTDest = 0.00;
					$stdICMS->vICMSSTDest = 0.00;

				}

				if($i->produto->CST_CSOSN_entrada == '61'){
					$stdICMS->qBCMonoRet = $this->format($stdProd->qTrib);
					$stdICMS->adRemICMSRet = $this->format($i->produto->adRemICMSRet, 4);
					$stdICMS->vICMSMonoRet = $this->format($i->produto->adRemICMSRet*$stdProd->qTrib, 4);
				}
				
				if($i->produto->CST_CSOSN_entrada != '40' && $i->produto->CST_CSOSN_entrada != '41'){
					if($i->produto->pRedBC > 0){

						$tempB = 100 - $i->produto->pRedBC;

						$v = $stdProd->vProd * ($tempB/100);

						$v += $stdProd->vFrete;
						if($i->produto->CST_CSOSN_entrada != '61'){
							$VBC += $stdICMS->vBC = number_format($v,2,'.','');
							$stdICMS->pICMS = $this->format($i->produto->perc_icms);
							$somaICMS += $stdICMS->vICMS = ($stdProd->vProd * ($tempB/100)) * ($stdICMS->pICMS/100);
							$stdICMS->pRedBC = $this->format($i->produto->pRedBC);
						}
					}
				}

				if($i->produto->CST_CSOSN_entrada == '60'){
					$ICMS = $nfe->tagICMSST($stdICMS);
				}else{
					$ICMS = $nfe->tagICMS($stdICMS);
				}

			}else{ // regime simples
				//$venda->produto->CST CSOSN
				
				$stdICMS = new \stdClass();
				
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				if($compra->fornecedor->cod_pais == 1058){
					$stdICMS->CSOSN = $i->produto->CST_CSOSN_entrada;
				}else{
					$stdICMS->CSOSN = $i->produto->CST_CSOSN_EXP;
				}

				if($stdICMS->CSOSN == 900){
					$stdICMS->modBCST = $i->produto->modBCST;
					$stdICMS->modBC = $i->produto->modBC;
					$stdICMS->vBC = $stdProd->vProd;
					$stdICMS->pICMS = $this->format($i->produto->perc_icms);
					$somaICMS += $stdICMS->vICMS = $this->format(($i->valor_unitario * $i->quantidade) 
						* ($stdICMS->pICMS/100));
					$VBC += $stdProd->vProd + $stdProd->vFrete;

					if($i->produto->perc_mva > 0){
						$stdICMS->pMVAST = $this->format($i->produto->perc_mva);
					}

					$stdICMS->pRedBCST = 0;
					$stdICMS->vBCST = 0;
					$stdICMS->pICMSST = $this->format($i->produto->pICMSST);
					$somaVICMSST += $stdICMS->vICMSST = $stdICMS->vBCST * ($stdICMS->pICMSST/100);
					
				}else{
					$somaICMS = 0;
					$stdICMS->pCredSN = $this->format($i->produto->perc_icms);
					$stdICMS->vCredICMSSN = $this->format($i->produto->perc_icms);
				}

				if($i->produto->CST_CSOSN_entrada == '61'){
					$stdICMS->CST = $i->produto->CST_CSOSN_entrada;
					$stdICMS->qBCMonoRet = $this->format($stdProd->qTrib);
					$stdICMS->adRemICMSRet = $this->format($i->produto->adRemICMSRet, 4);
					$stdICMS->vICMSMonoRet = $this->format($i->produto->adRemICMSRet*$stdProd->qTrib, 4);
					$ICMS = $nfe->tagICMS($stdICMS);
				}else{
					$ICMS = $nfe->tagICMSSN($stdICMS);
				}
				// $ICMS = $nfe->tagICMSSN($stdICMS);

			}

			
			$stdPIS = new \stdClass();//PIS
			$stdPIS->item = $itemCont; 
			$stdPIS->CST = $i->produto->CST_PIS_entrada;
			if($i->produto->CST_PIS_entrada != '71'){
				$stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
				$stdPIS->pPIS = $this->format($i->produto->perc_pis);
				$stdPIS->vPIS = $this->format(($stdProd->vProd * $i->quantidade) * ($i->produto->perc_pis/100));
			}else{
				$stdPIS->vBC = 0;
				$stdPIS->pPIS = 0;
				$stdPIS->vPIS = 0;
			}
			
			$PIS = $nfe->tagPIS($stdPIS);


			$stdCOFINS = new \stdClass();//COFINS
			$stdCOFINS->item = $itemCont; 
			$stdCOFINS->CST = $i->produto->CST_COFINS_entrada;
			$stdCOFINS->vBC = $this->format($i->produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->produto->perc_cofins);
			if($i->produto->CST_COFINS_entrada != '71'){
				$stdCOFINS->vCOFINS = $this->format(($stdProd->vProd * $i->quantidade) * 
					($i->produto->perc_cofins/100));
			}else{
				$stdCOFINS->vCOFINS = 0;
			}
			$COFINS = $nfe->tagCOFINS($stdCOFINS);


			$std = new \stdClass();//IPI
			$std->item = $itemCont; 
			$std->clEnq = null;
			$std->CNPJProd = null;
			$std->cSelo = null;
			$std->qSelo = null;
			// $std->cEnq = '999'; //999 – para tributação normal IPI
			$std->cEnq = $i->produto->cenq_ipi ?? '999';
			$std->IPINT = '55';

			$std->CST = $i->produto->CST_IPI_entrada ? $i->produto->CST_IPI_entrada : '99';
			$std->vBC = $this->format($i->produto->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
			$std->pIPI = $this->format($i->produto->perc_ipi);
			if($i->produto->CST_COFINS_entrada != '03'){
				$std->vIPI = $stdProd->vProd * $this->format(($i->produto->perc_ipi/100));
			}
			$std->qUnid = null;
			$std->vUnid = null;

			$nfe->tagIPI($std);

			if(strlen($i->produto->codigo_anp) > 2){
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


				$stdComb->UFCons = $compra->cliente ? $compra->cliente->cidade->uf : 
				$config->UF;

				$nfe->tagcomb($stdComb);
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

			// dados para importacao
			if($i->nDI != null && $i->dDI != null){
				$std = new \stdClass();
				$std->item = $itemCont;
				$std->nDI = $i->nDI;
				$std->dDI = $i->dDI;
				$std->xLocDesemb = $i->cidadeDesembarque->nome;
				$std->UFDesemb = $i->cidadeDesembarque->uf;
				$std->dDesemb = $i->dDesemb;
				$std->tpViaTransp = $i->tpViaTransp;
				if($i->vAFRMM > 0){
					$std->vAFRMM = $this->format($i->vAFRMM);
				}
				$std->tpIntermedio = $i->tpIntermedio;
				if($i->documento){
					$doc = preg_replace('/[^0-9]/', '', $i->documento);
					if(strlen($doc) == 14){
						$std->CNPJ = $doc;
					}else{
						$std->CPF = $doc;
					}
				}
				if($i->UFTerceiro){
					$std->UFTerceiro = $i->UFTerceiro;
				}

				if($i->cExportador){
					$std->cExportador = $i->cExportador;
				}

				$nfe->tagDI($std);

				if($i->nAdicao){
					$itemContImportacao++;
					$std2 = new \stdClass();
					$std2->item = $itemCont;
					$std2->nDI = $i->nDI;
					$std2->nSeqAdic = $itemContImportacao;
					$std2->nAdicao = $i->nAdicao;
					$std2->cFabricante = $i->cFabricante;

					$nfe->tagadi($std2);
				}
			}


		}

		$stdICMSTot = new \stdClass();
		// $stdICMSTot->vBC = $this->format($VBC);
		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;
		$stdICMSTot->vProd = $this->format($somaProdutos);

		$stdICMSTot->vFrete = 0.00;
		$stdICMSTot->vFrete = $this->format($compra->valor_frete);
		$stdICMSTot->vSeg = 0.00;
		$stdICMSTot->vDesc = $this->format($compra->desconto);
		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = 0.00;
		$stdICMSTot->vCOFINS = 0.00;
		$stdICMSTot->vOutro = 0.00;

		// if($venda->frete){
		// 	$stdICMSTot->vNF = 
		// 	$this->format(($somaProdutos+$venda->frete->valor)-$venda->desconto);
		// } 
		$stdICMSTot->vNF = $this->format($compra->valor+$compra->valor_frete);

		$stdICMSTot->vTotTrib = 0.00;
		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);


		$stdTransp = new \stdClass();
		$stdTransp->modFrete = '9';

		$transp = $nfe->tagtransp($stdTransp);


		$stdTransp = new \stdClass();
		$stdTransp->modFrete = $compra->tipo ?? '9';

		$transp = $nfe->tagtransp($stdTransp);

		if($compra->transportadora){
			$std = new \stdClass();
			$std->xNome = $compra->transportadora->razao_social;

			$std->xEnder = $compra->transportadora->logradouro;
			$std->xMun = $this->retiraAcentos($compra->transportadora->cidade->nome);
			$std->UF = $compra->transportadora->cidade->uf;


			$cnpj_cpf = $compra->transportadora->cnpj_cpf;
			$cnpj_cpf = str_replace(".", "", $compra->transportadora->cnpj_cpf);
			$cnpj_cpf = str_replace("/", "", $cnpj_cpf);
			$cnpj_cpf = str_replace("-", "", $cnpj_cpf);

			if(strlen($cnpj_cpf) == 14) $std->CNPJ = $cnpj_cpf;
			else $std->CPF = $cnpj_cpf;

			$nfe->tagtransporta($std);
		}

		$placa = str_replace("-", "", $compra->placa);
		$std = new \stdClass();
		$std->placa = strtoupper($placa);
		$std->UF = $compra->uf;

			// if($config->UF == $venda->cliente->cidade->uf){
		if($compra->placa != "" && $compra->uf && $stdIde->idDest != 2){
			$nfe->tagveicTransp($std);
		}

		if($compra->qtdVolumes > 0 && $compra->peso_liquido > 0
			&& $compra->peso_bruto > 0){
			$stdVol = new \stdClass();
			$stdVol->item = 1;
			$stdVol->qVol = $compra->qtdVolumes;
			$stdVol->esp = $compra->especie;

			$stdVol->nVol = $compra->numeracaoVolumes;
			$stdVol->pesoL = $compra->peso_liquido;
			$stdVol->pesoB = $compra->peso_bruto;
			$vol = $nfe->tagvol($stdVol);
		}

		// if($compra->fornecedor->cod_pais != 1058){
		// 	$std = new \stdClass();
		// 	$std->UFSaidaPais = $config->UF;
		// 	$std->xLocExporta = $config->municipio;
		// 	// $std->xLocDespacho = 'Informação do Recinto Alfandegado';

		// 	$nfe->tagexporta($std);
		// }

	//Fatura
		if($tipoPagamento != '90'){

			$stdFat = new \stdClass();
			$stdFat->nFat = $stdIde->nNF;
			$stdFat->vOrig = $this->format($compra->valor);
			$stdFat->vDesc = $this->format(0.00);
			$stdFat->vLiq = $this->format($compra->valor);

			$fatura = $nfe->tagfat($stdFat);
		}


	//Duplicata

		if($tipoPagamento != '90'){
			if(sizeof($compra->fatura) > 0){
				$contFatura = 1;
				foreach($compra->fatura as $ft){
					$stdDup = new \stdClass();
					$stdDup->nDup = "00".$contFatura;
					$stdDup->dVenc = substr($ft->data_vencimento, 0, 10);
					$stdDup->vDup = $this->format($ft->valor_integral);

					$nfe->tagdup($stdDup);
					$contFatura++;
				}
			}else{
				$stdDup = new \stdClass();
				$stdDup->nDup = '001';
				$stdDup->dVenc = Date('Y-m-d');
				$stdDup->vDup =  $this->format($compra->valor);

				$nfe->tagdup($stdDup);
			}
		}



		$stdPag = new \stdClass();
		$pag = $nfe->tagpag($stdPag);

		$stdDetPag = new \stdClass();


		$stdDetPag->tPag = $tipoPagamento;
		$stdDetPag->vPag = $tipoPagamento == '90' ? 0.00 : $this->format($compra->valor); 
		$stdDetPag->indPag = '0'; 

		$detPag = $nfe->tagdetPag($stdDetPag);

		$stdInfoAdic = new \stdClass();
		$obs = $this->retiraAcentos($compra->observacao);

		if($p->produto->renavam != ''){
			$veiCpl = 'RENAVAM ' . $p->produto->renavam;
			if($p->produto->placa != '') $veiCpl .= ', PLACA ' . $p->produto->placa;
			if($p->produto->chassi != '') $veiCpl .= ', CHASSI ' . $p->produto->chassi;
			if($p->produto->combustivel != '') $veiCpl .= ', COMBUSTÍVEL ' . $p->produto->combustivel;
			if($p->produto->ano_modelo != '') $veiCpl .= ', ANO/MODELO ' . $p->produto->ano_modelo;
			if($p->produto->cor_veiculo != '') $veiCpl .= ', COR ' . $p->produto->cor_veiculo;

			$obs .= $veiCpl;
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

		$stdInfoAdic->infCpl = $this->retiraAcentos($obs);
		$infoAdic = $nfe->taginfAdic($stdInfoAdic);

		$std = new \stdClass();
		$std->CNPJ = env('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato= env('RESP_NOME'); //Nome da pessoa a ser contatada
		$std->email = env('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
		$std->fone = env('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
		$nfe->taginfRespTec($std);
		
		if($config->aut_xml != ''){

			$std = new \stdClass();
			$cnpj = preg_replace('/[^0-9]/', '', $config->aut_xml);

			$std->CNPJ = $cnpj;

			$aut = $nfe->tagautXML($std);
		}

		// if($nfe->montaNFe()){
		// 	$arr = [
		// 		'chave' => $nfe->getChave(),
		// 		'xml' => $nfe->getXML(),
		// 		'nNf' => $stdIde->nNF
		// 	];
		// 	return $arr;
		// } else {
		// 	throw new Exception("Erro ao gerar NFe");
		// }

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

	private function retiraAcentos($texto){
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç)/"),explode(" ","a A e E i I o O u U n N c"),$texto);
	}

	public function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	public function sign($xml){
		return $this->tools->signNFe($xml);
	}

	public function transmitir($signXml, $chave){
		try{
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);

			$st = new Standardize();
			$std = $st->toStd($resp);
			sleep($this->timeout);
			if ($std->cStat != 103) {

				return "[$std->cStat] - $std->xMotivo";
			}
			$recibo = $std->infRec->nRec; 
			
			$protocolo = $this->tools->sefazConsultaRecibo($recibo);
			sleep(2);
			//return $protocolo;
			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			try {
				$xml = Complements::toAuthorize($signXml, $protocolo);
				header('Content-type: text/xml; charset=UTF-8');
				file_put_contents(public_path('xml_entrada_emitida/').$chave.'.xml',$xml);
				return $recibo;
				// $this->printDanfe($xml);
			} catch (\Exception $e) {
				return "Erro: " . $st->toJson($protocolo);
			}

		} catch(\Exception $e){
			return "Erro: ".$e->getMessage() ;
		}

	}	

	public function cancelar($compra, $justificativa){
		try {
			
			$chave = $compra->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			sleep(1);
				// return $arr;
			$xJust = $justificativa;


			$nProt = $arr['protNFe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);
			sleep(2);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				$public = env('SERVIDOR_WEB') ? 'public/' : '';
				if ($cStat == '101' || $cStat == '135' || $cStat == '155' ) {
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents($public.'xml_nfe_entrada_cancelada/'.$chave.'.xml',$xml);

					return $json;
				} else {

					return $json;	
				}
			}    
		} catch (\Exception $e) {
			echo $e->getMessage();

		}
	}

	public function cartaCorrecao($id, $correcao){
		try {

			$compra = Compra::
			where('id', $id)
			->first();

			$chave = $compra->chave;
			$xCorrecao = $correcao;
			$nSeqEvento = $compra->sequencia_cce+1;
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
					$public = env('SERVIDOR_WEB') ? 'public/' : '';
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents($public.'xml_nfe_entrada_correcao/'.$chave.'.xml',$xml);

					$compra->sequencia_cce = $compra->sequencia_cce + 1;
					$compra->save();
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

	public function consultar($compra){
		try {
			
			$this->tools->model('55');

			$chave = $compra->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			// $arr = json_decode($json);
			return json_encode($arr);

		} catch (\Exception $e) {
			echo $e->getMessage();
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

}
