<?php
namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\VendaCaixa;
use App\Models\ConfigNota;
use App\Models\Certificado;
use NFePHP\NFe\Complements;
use App\Models\ConfigSystem;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Tributacao;
use App\Models\PedidoDelivery;
use App\Models\IBPT;
use App\Models\Filial;
use App\Models\Contigencia;
use NFePHP\NFe\Factories\Contingency;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFCeService{

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
		$contigencia = $this->getContigencia();

		if($contigencia != null){
			$contingency = new Contingency($contigencia->status_retorno);
			$this->tools->contingency = $contingency;
		}
		$this->tools->loadSoapClass($soapCurl);
		$this->tools->model(65);

		$config = ConfigSystem::first();
		if($config){
			if($config->timeout_nfce){
				$this->timeout = $config->timeout_nfce;
			}
		}
		
	}

	private function getContigencia(){
		$active = Contigencia::
		where('empresa_id', $this->empresa_id)
		->where('status', 1)
		->where('documento', 'NFCe')
		->first();
		return $active;
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

	public function gerarNFCe($idVenda){
		$venda = VendaCaixa::
		where('id', $idVenda)
		->first();

		$config = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		$tributacao = Tributacao::
		where('empresa_id', $this->empresa_id)
		->first();

		if($venda->filial_id != null){
			$casas_decimais = $config->casas_decimais;
			$config = Filial::findOrFail($venda->filial_id);
			$config->casas_decimais = $casas_decimais;

		}

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00'; //versão do layout
		$stdInNFe->Id = null; //se o Id de 44 digitos não for passado será gerado automaticamente
		$stdInNFe->pk_nItem = ''; //deixe essa variavel sempre como NULL

		$infNFe = $nfe->taginfNFe($stdInNFe);

		//IDE
		$stdIde = new \stdClass();
		$stdIde->cUF = $config->cUF;
		$stdIde->cNF = rand(11111111, 99999999);
		$stdIde->natOp = $config->natureza->natureza;

		// $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

		$vendaLast = VendaCaixa::lastNFCe($this->empresa_id);
		if($venda->filial_id != null){
			$vendaLast = $config->ultimo_numero_nfce;
		}
		$lastNumero = $vendaLast;

		$stdIde->mod = 65;
		$stdIde->serie = $config->numero_serie_nfce;
		$stdIde->nNF = (int)$lastNumero+1; 
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = 1;
		$stdIde->idDest = 1;
		$stdIde->cMunFG = $config->codMun;
		$stdIde->tpImp = 4;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = (int)$config->ambiente;
		$stdIde->finNFe = 1;
		$stdIde->indFinal = 1;
		$stdIde->indPres = 1;
		if($config->ambiente == 2){
			$stdIde->indIntermed = 0;
		}
		$stdIde->procEmi = '0';
		$stdIde->verProc = '3.10.31';
		//
		$tagide = $nfe->tagide($stdIde);

		$stdEmit = new \stdClass();
		$stdEmit->xNome = $config->razao_social;
		$stdEmit->xFant = $config->nome_fantasia;

		$ie = preg_replace('/[^0-9]/', '', $config->ie);

		$stdEmit->IE = $ie;
		$stdEmit->CRT = ($tributacao->regime == 0 || $tributacao->regime == 2) ? 1 : 3;

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$stdEmit->CNPJ = $cnpj; 

		$emit = $nfe->tagemit($stdEmit);

		// ENDERECO EMITENTE
		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $config->logradouro;
		$stdEnderEmit->nro = $config->numero;
		$stdEnderEmit->xCpl = $config->complemento;
		$stdEnderEmit->xBairro = $config->bairro;
		$stdEnderEmit->cMun = $config->codMun;
		$stdEnderEmit->xMun = $config->municipio;
		$stdEnderEmit->UF = $config->UF;

		$cep = str_replace("-", "", $config->cep);
		$stdEnderEmit->CEP = $cep;
		$stdEnderEmit->cPais = $config->codPais;
		$stdEnderEmit->xPais = $config->pais;

		$fone = str_replace(" ", "", $config->fone);
		$fone = str_replace("-", "", $fone);
		$stdEnderEmit->fone = $fone;

		$enderEmit = $nfe->tagenderEmit($stdEnderEmit);

		// DESTINATARIO

		if($venda->cliente_id != null || $venda->cpf != null){
			$stdDest = new \stdClass();
			if($venda->cliente_id != null){
				$stdDest->xNome = $venda->cliente->razao_social;
				$stdDest->indIEDest = "1";

				$cnpj_cpf = str_replace(".", "", $venda->cliente->cpf_cnpj);
				$cnpj_cpf = str_replace("/", "", $cnpj_cpf);
				$cnpj_cpf = str_replace("-", "", $cnpj_cpf);

				if(strlen($cnpj_cpf) == 14) $stdDest->CNPJ = $cnpj_cpf;
				else $stdDest->CPF = $cnpj_cpf;

				$dest = $nfe->tagdest($stdDest);

				$stdEnderDest = new \stdClass();
				$stdEnderDest->xLgr = $venda->cliente->rua;
				$stdEnderDest->nro = $venda->cliente->numero;
				$stdEnderDest->xCpl = "";
				$stdEnderDest->xBairro = $venda->cliente->bairro;
				$stdEnderDest->cMun = $venda->cliente->cidade->codigo;
				$stdEnderDest->xMun = strtoupper($venda->cliente->cidade->nome);
				$stdEnderDest->UF = $venda->cliente->cidade->uf;

				$cep = str_replace("-", "", $venda->cliente->cep);
				$stdEnderDest->CEP = $cep;
				$stdEnderDest->cPais = "1058";
				$stdEnderDest->xPais = "BRASIL";
				$enderDest = $nfe->tagenderDest($stdEnderDest);

			}
			if($venda->cpf != null){

				$cpf = str_replace(".", "", $venda->cpf);
				$cpf = str_replace("/", "", $cpf);
				$cpf = str_replace("-", "", $cpf);
				$cpf = str_replace(" ", "", $cpf);

				if($venda->nome) $stdDest->xNome = $venda->nome;
				$stdDest->indIEDest = "9";
				// $stdDest->CPF = $cpf;
				if(strlen($cpf) == 14) $stdDest->CNPJ = $cpf;
				else $stdDest->CPF = $cpf;
				$dest = $nfe->tagdest($stdDest);
			}

		}


		$somaProdutos = 0;
		$somaICMS = 0;
		//PRODUTOS
		$itemCont = 0;
		$somaDesconto = 0;
		$totalItens = count($venda->itens);
		$somaAcrescimo = 0;
		$VBC = 0;

		$somaFederal = 0;
		$somaEstadual = 0;
		$somaMunicipal = 0;

		$obsIbpt = "";

		foreach($venda->itens as $i){
			$itemCont++;

			$stdProd = new \stdClass();
			$stdProd->item = $itemCont;

			$cod = $this->validate_EAN13Barcode($i->produto->codBarras);

			$stdProd->cEAN = $cod ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cEANTrib = $cod ? $i->produto->codBarras : 'SEM GTIN';
			$stdProd->cProd = $i->produto->id;
			if($i->produto->referencia != ''){
				$stdProd->cProd = $i->produto->referencia;
			}

			$stdProd->xProd = $i->produto->nome;
			if($i->produto->CST_CSOSN != '60'){

				if($i->produto->cBenef){
					$stdProd->cBenef = $i->produto->cBenef;
				}
			}

			$ncm = $i->produto->NCM;
			$ncm = str_replace(".", "", $ncm);
			$stdProd->NCM = $ncm;
			$ibpt = IBPT::getIBPT($config->UF, $ncm);

			$stdProd->CFOP = $i->produto->CFOP_saida_estadual;

			if($config->natureza->sobrescreve_cfop == 0){
				$stdProd->CFOP = $i->produto->CFOP_saida_estadual;
			}else{
				$stdProd->CFOP = $config->natureza->CFOP_saida_estadual;
			}

			$cest = $i->produto->CEST;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			$stdProd->uCom = $i->produto->unidade_venda;
			$stdProd->qCom = $i->quantidade;
			$stdProd->vUnCom = $this->format($i->valor, $config->casas_decimais);
			$stdProd->vProd = $this->format($i->quantidade * $i->valor, $config->casas_decimais);
			// $stdProd->uTrib = $i->produto->unidade_venda;
			// $stdProd->qTrib = $i->quantidade;
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
			$stdProd->vUnTrib = $this->format($i->valor, $config->casas_decimais);
			if($i->produto->quantidade_tributavel > 0){
				$stdProd->vUnTrib = $stdProd->vProd/$stdProd->qTrib;
			}
			$stdProd->indTot = 1;

			//calculo media prod

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


			if($venda->pedido_delivery_id > 0){
				$pedido = PedidoDelivery::find($venda->pedido_delivery_id);
				$somaItens = $pedido->somaItensSemFrete();
				$totalVenda = $venda->valor_total;
				if($somaItens < $totalVenda){
					$vAcr = $totalVenda - $somaItens;

					if($itemCont < sizeof($venda->itens)){

						$media = (((($stdProd->vProd-$totalVenda)/$totalVenda))*100);
						$media = 100 - ($media * -1);

						$tempAcrescimo = ($vAcr*$media)/100;
						$somaAcrescimo+=$tempAcrescimo;
						if($tempAcrescimo > 0.1)
							$stdProd->vOutro = $this->format($tempAcrescimo);
					}else{
						if($vAcr - $somaAcrescimo > 0.1)
							$stdProd->vOutro = $this->format($vAcr - $somaAcrescimo);
					}

				}
			}
			// fim calculo
			

			// if($venda->desconto > 0){
			// 	$stdProd->vDesc = $this->format($venda->desconto/$totalItens);
			// }

			// if($venda->desconto > 0){
			// 	if($itemCont < sizeof($venda->itens)){
			// 		$totalVenda = $venda->valor_total + $venda->desconto;

			// 		$media = (((($stdProd->vProd - $totalVenda)/$totalVenda))*100);
			// 		$media = 100 - ($media * -1);

			// 		if($venda->desconto > 0.1){
			// 			$tempDesc = ($venda->desconto*$media)/100;
			// 		}else{
			// 			$tempDesc = $venda->desconto;
			// 		}

			// 		if($somaDesconto >= $venda->desconto){
			// 			$tempDesc = 0;
			// 		}

			// 		$somaDesconto += $this->format($tempDesc);
			// 		if($tempDesc > 0.01){
			// 			$stdProd->vDesc = $this->format($tempDesc);
			// 		}
			// 	}else{

			// 		if($venda->desconto - $somaDesconto >= 0.01){
			// 			$stdProd->vDesc = $this->format($venda->desconto - $somaDesconto);
			// 		}
			// 	}
			// }

			if($venda->desconto > 0.01 && $somaDesconto < $venda->desconto){
				if($itemCont < sizeof($venda->itens)){
					$totalVenda = $venda->valor_total + $venda->desconto;

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

			// echo $stdProd->vDesc . "<br>";
			$somaProdutos += $i->quantidade * $i->valor;


			$prod = $nfe->tagprod($stdProd);

			// $tributacao = Tributacao::first();

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;

			// if($ibpt != null){
			// 	$vProd = $stdProd->vProd;
			// 	$somaFederal = ($vProd*($ibpt->nacional_federal/100));
			// 	$somaEstadual += ($vProd*($ibpt->estadual/100));
			// 	$somaMunicipal += ($vProd*($ibpt->municipal/100));
			// 	$soma = $somaFederal + $somaEstadual + $somaMunicipal;
			// 	$stdImposto->vTotTrib = $soma;
			// }

			if($i->produto->ibpt){
				$vProd = $stdProd->vProd;
				if($i->produto->origem == 1 || $i->produto->origem == 2){
					$federal = $this->format(($vProd*($i->produto->ibpt->federal/100)), 2);
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

			$imposto = $nfe->tagimposto($stdImposto);

			if($config->sobrescrita_csonn_consumidor_final != ""){
				$i->produto->CST_CSOSN = $config->sobrescrita_csonn_consumidor_final;
			}

			if($tributacao->regime == 1){ // regime normal

				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CST = $i->produto->CST_CSOSN;
				$stdICMS->modBC = 0;
				$stdICMS->vBC = $this->format($i->valor * $i->quantidade);
				$stdICMS->pICMS = $this->format($i->produto->perc_icms);
				$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS/100);

				if($i->produto->CST_CSOSN == '500' || $i->produto->CST_CSOSN == '60'){
				// if($i->produto->CST_CSOSN == '500' ){
					$stdICMS->pRedBCEfet = 0.00;
					$stdICMS->vBCEfet = 0.00;
					$stdICMS->pICMSEfet = 0.00;
					$stdICMS->vICMSEfet = 0.00;

				}
				if($i->produto->CST_CSOSN == '61'){
					$stdICMS->qBCMonoRet = $this->format($i->produto->valor_venda);
					$stdICMS->vICMSMonoRet = $this->format($i->produto->adRemICMSRet*$i->quantidade);
					$stdICMS->adRemICMSRet = $this->format($i->produto->adRemICMSRet);
				}

				if($i->produto->pRedBC > 0){
					$tempB = 100-$i->produto->pRedBC;

					$v = $stdProd->vProd * ($tempB/100);

					$v += $stdProd->vFrete;
					if($i->produto->CST_CSOSN != '61'){
						$VBC += $stdICMS->vBC = number_format($v,2,'.','');
						$stdICMS->pICMS = $this->format($i->produto->perc_icms);
						$somaICMS += $stdICMS->vICMS = ($stdProd->vProd * ($tempB/100)) * ($stdICMS->pICMS/100);
						$stdICMS->pRedBC = $this->format($i->produto->pRedBC);
					}
				}else{
					if($i->produto->CST_CSOSN != '61' && $i->produto->CST_CSOSN != '40' && $i->produto->CST_CSOSN != '60'){
						$VBC += $stdProd->vProd;
						$somaICMS += $stdICMS->vICMS;
					}
				}

				
				$ICMS = $nfe->tagICMS($stdICMS);

			}else{ // regime simples
				
				$stdICMS = new \stdClass();
				
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CSOSN = $i->produto->CST_CSOSN;
				$stdICMS->pCredSN = $this->format($i->produto->perc_icms);
				$stdICMS->vCredICMSSN = $this->format($i->produto->perc_icms);
				// $ICMS = $nfe->tagICMSSN($stdICMS);

				if($i->produto->CST_CSOSN == '61'){
					$stdICMS->CST = $i->produto->CST_CSOSN;
					$stdICMS->qBCMonoRet = $this->format($stdProd->qTrib);
					$stdICMS->adRemICMSRet = $this->format($i->produto->adRemICMSRet, 4);
					$stdICMS->vICMSMonoRet = $this->format($i->produto->adRemICMSRet*$stdProd->qTrib, 4);
					$ICMS = $nfe->tagICMS($stdICMS);
				}else{
					$ICMS = $nfe->tagICMSSN($stdICMS);
				}
				$somaICMS = 0;
			}


			$vbcPis = $stdProd->vProd;
			if($tributacao->exclusao_icms_pis_cofins){
				$vbcPis -= $stdICMS->vICMS;
			}
			$stdPIS = new \stdClass();
			$stdPIS->item = $itemCont; 
			$stdPIS->CST = $i->produto->CST_PIS;
			$stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $vbcPis : 0.00;
			$stdPIS->pPIS = $this->format($i->produto->perc_pis);
			$stdPIS->vPIS = $this->format(($vbcPis) * ($i->produto->perc_pis/100));
			$PIS = $nfe->tagPIS($stdPIS);

			//COFINS
			$vbcCofins = $stdProd->vProd;
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


				$stdComb->UFCons = $venda->cliente ? $venda->cliente->cidade->uf : $config->UF;
				if($i->produto->pBio > 0){
					$stdComb->pBio = $i->produto->pBio;
				}
				$nfe->tagcomb($stdComb);
			}

			// if($stdIde->indFinal == 0 && strlen($i->produto->codigo_anp) > 2){
			// 	$stdOrigComb = new \stdClass();

			// 	$stdOrigComb->item = $itemCont; 
			// 	$stdOrigComb->indImport = $i->produto->indImport;
			// 	$stdOrigComb->cUFOrig = $i->produto->cUFOrig;
			// 	$stdOrigComb->pOrig = $i->produto->pOrig;
			// 	$nfe->tagorigComb($stdOrigComb);
			// }

			$cest = $i->produto->CEST;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			if(strlen($cest) > 0){
				$std = new \stdClass();
				$std->item = $itemCont; 
				$std->CEST = $cest;
				$nfe->tagCEST($std);
			}
		}
		// die();


		//ICMS TOTAL
		$stdICMSTot = new \stdClass();
		$stdICMSTot->vBC = $this->format($VBC);
		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;
		$stdICMSTot->vProd = $this->format($somaProdutos);
		
		$stdICMSTot->vFrete = 0.00;

		$stdICMSTot->vSeg = 0.00;
		$stdICMSTot->vDesc = $this->format($venda->desconto);
		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = 0.00;
		$stdICMSTot->vCOFINS = 0.00;
		$stdICMSTot->vOutro = $this->format($venda->acrescimo);
		$stdICMSTot->vNF = $this->format($venda->valor_total);

		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);

		//TRANSPORTADORA

		$stdTransp = new \stdClass();
		$stdTransp->modFrete = 9;

		$transp = $nfe->tagtransp($stdTransp);

		
		$stdPag = new \stdClass();

		if ($venda->tipo_pagamento != '99') {
			if($venda->tipo_pagamento == '01'){
				$stdPag->vTroco = $this->format($venda->troco); 
			}

			if($venda->troco == 0 && ($venda->valor_total != $venda->dinheiro_recebido)){
				if($venda->tipo_pagamento == '01'){
					if($venda->dinheiro_recebido - $venda->valor_total > 0)
						$stdPag->vTroco = $this->format($venda->dinheiro_recebido - $venda->valor_total);
				} 
			}
		}

		$pag = $nfe->tagpag($stdPag);

		//Resp Tecnico
		$stdResp = new \stdClass();
		$stdResp->CNPJ = env('RESP_CNPJ'); 
		$stdResp->xContato= env('RESP_NOME');
		$stdResp->email = env('RESP_EMAIL'); 
		$stdResp->fone = env('RESP_FONE'); 

		$nfe->taginfRespTec($stdResp);

		//DETALHE PAGAMENTO

		// $stdDetPag = new \stdClass();
		// $stdDetPag->indPag = 0;

		// $stdDetPag->tPag = $venda->tipo_pagamento; 
		// $stdDetPag->vPag = $this->format($venda->dinheiro_recebido); //Obs: deve ser informado o valor pago pelo cliente

		// if($venda->tipo_pagamento == '03' || $venda->tipo_pagamento == '04'){
		// 	// $stdDetPag->CNPJ = '12345678901234';
		// 	// $stdDetPag->tBand = '01';
		// 	// $stdDetPag->cAut = '3333333';
		// 	$stdDetPag->tpIntegra = 2;
		// }
		
		// // // $std->tpIntegra = 1; //incluso na NT 2015/002
		// // // $std->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo

		// $detPag = $nfe->tagdetPag($stdDetPag);

		if ($venda->tipo_pagamento != '99') {

			$stdDetPag = new \stdClass();
    		//$stdDetPag->indPag = 0;
			$stdDetPag->tPag = $venda->tipo_pagamento; 
			if($venda->tipo_pagamento == '06'){
				$stdDetPag->tPag = '05'; 
			}
    		// $stdDetPag->vPag = $this->format($venda->valor_total); //Obs: deve ser informado o valor pago pelo cliente

			if($venda->tipo_pagamento == '03' || $venda->tipo_pagamento == '04' || $venda->tipo_pagamento == '17'){
				$stdDetPag->tBand = $venda->bandeira_cartao;
				if($venda->cAut_cartao != ""){
					$stdDetPag->cAut = $venda->cAut_cartao;
				}
				if($venda->cnpj_cartao != ""){
					$cnpj = str_replace(".", "", $venda->cnpj_cartao);
					$cnpj = str_replace("/", "", $cnpj);
					$cnpj = str_replace("-", "", $cnpj);
					$stdDetPag->CNPJ = $cnpj;
				}

				$stdDetPag->tpIntegra = 2;
				$stdDetPag->vPag = $this->format($venda->valor_total);

			}else{
				if($venda->tipo_pagamento == '01'){
					$stdDetPag->vPag = $this->format($venda->dinheiro_recebido);
				}else{
					$stdDetPag->vPag = $this->format($venda->valor_total);
				}
			}

    		// $std->tpIntegra = 1; //incluso na NT 2015/002
    		// $std->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo

			$detPag = $nfe->tagdetPag($stdDetPag);
		}
		else {

			if(sizeof($venda->fatura) > 0){
				foreach($venda->fatura as $f){

					$stdDetPag = new \stdClass();
					$stdDetPag->tPag = $f->forma_pagamento; 

					if($f->forma_pagamento == '06'){
						$stdDetPag->tPag = '05'; 
					}

					$stdDetPag->vPag = $this->format($f->valor);
					if($f->forma_pagamento == '03' || $f->forma_pagamento == '04' || $f->forma_pagamento == '17'){
						$stdDetPag->tBand = '99';
						$stdDetPag->tpIntegra = 2;
					}
					if($venda->descricao_pag_outros != "" && $f->forma_pagamento == '99'){
						$stdDetPag->xPag = $venda->descricao_pag_outros;
					}
					$detPag = $nfe->tagdetPag($stdDetPag);				
				}
			}else{
				$stdDetPag = new \stdClass();
				$stdDetPag->tPag = $venda->tipo_pagamento;
				if($venda->descricao_pag_outros != "" && $venda->tipo_pagamento == '99'){
					$stdDetPag->xPag = $venda->descricao_pag_outros;
				}
				$stdDetPag->vPag = $this->format($venda->valor_total);

				$detPag = $nfe->tagdetPag($stdDetPag);

			}

			// if ($venda->valor_pagamento_1 > 0) {

			// 	$stdDetPag1 = new \stdClass();
   //  			//$stdDetPag1->indPag = 0;

			// 	$stdDetPag1->tPag = $venda->tipo_pagamento_1; 
			// 	if($venda->tipo_pagamento_1 == '06'){
			// 		$stdDetPag1->tPag = '05'; 
			// 	}
   //  			$stdDetPag1->vPag = $this->format($venda->valor_pagamento_1); //Obs: deve ser informado o valor pago pelo cliente

   //  			if($venda->tipo_pagamento_1 == '03' || $venda->tipo_pagamento_1 == '04'){
   //  				// $stdDetPag1->CNPJ = '12345678901234';
   //  				// $stdDetPag3->CNPJ = null;

   //  				$stdDetPag1->tBand = '99';
   //  				// $stdDetPag1->cAut = '3333333';
   //  				$stdDetPag1->tpIntegra = 2;
   //  			}

   //  			// $std->tpIntegra = 1; //incluso na NT 2015/002
   //  			// $std->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo

   //  			$detPag = $nfe->tagdetPag($stdDetPag1);

   //  		}else{
   //  			$stdDetPag = new \stdClass();
   //  			$stdDetPag->tPag = $venda->tipo_pagamento; 
   //  			$stdDetPag->vPag = $this->format($venda->valor_total);
   //  			$stdDetPag->xPag = $venda->descricao_pag_outros;
   //  			$detPag = $nfe->tagdetPag($stdDetPag);
   //  		}

   //  		if ($venda->tipo_pagamento_2!=null && $venda->valor_pagamento_2>0) {

   //  			$stdDetPag2 = new \stdClass();
   //  			//$stdDetPag2->indPag = 0;

   //  			$stdDetPag2->tPag = $venda->tipo_pagamento_2;
   //  			if($venda->tipo_pagamento_2 == '06'){
			// 		$stdDetPag2->tPag = '05'; 
			// 	}
   //  			$stdDetPag2->vPag = $this->format($venda->valor_pagamento_2); //Obs: deve ser informado o valor pago pelo cliente

   //  			if($venda->tipo_pagamento_2 == '03' || $venda->tipo_pagamento_2 == '04'){
   //  				// $stdDetPag2->CNPJ = '12345678901234';
   //  				// $stdDetPag3->CNPJ = null;

   //  				$stdDetPag2->tBand = '99';
   //  				// $stdDetPag2->cAut = '3333333';
   //  				$stdDetPag2->tpIntegra = 2;
   //  			}

   //  			// $std->tpIntegra = 1; //incluso na NT 2015/002
   //  			// $std->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo

   //  			$detPag = $nfe->tagdetPag($stdDetPag2);

   //  		}

   //  		if ($venda->tipo_pagamento_3!=null && $venda->valor_pagamento_3>0) {

   //  			$stdDetPag3 = new \stdClass();
   //  			//$stdDetPag1->indPag = 0;

   //  			$stdDetPag3->tPag = $venda->tipo_pagamento_3;
   //  			if($venda->tipo_pagamento_3 == '06'){
			// 		$stdDetPag3->tPag = '05'; 
			// 	}
   //  			$stdDetPag3->vPag = $this->format($venda->valor_pagamento_3); //Obs: deve ser informado o valor pago pelo cliente

   //  			if($venda->tipo_pagamento_3 == '03' || $venda->tipo_pagamento_3 == '04'){
   //  				// $stdDetPag3->CNPJ = null;
   //  				$stdDetPag3->tBand = '99';
   //  				// $stdDetPag3->cAut = '3333333';
   //  				$stdDetPag3->tpIntegra = 1;
   //  			}

   //  			// $std->tpIntegra = 1; //incluso na NT 2015/002
   //  			// $std->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo

   //  			$detPag = $nfe->tagdetPag($stdDetPag3);

   //  		}

		}

		//INFO ADICIONAL
		$stdInfoAdic = new \stdClass();
		// $stdInfoAdic->infAdFisco = 'informacoes para o fisco';
		$obs = $venda->observacao;
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
		$stdInfoAdic->infCpl = $obs;
		$infoAdic = $nfe->taginfAdic($stdInfoAdic);
		// if($nfe->monta()){

		// 	$arr = [
		// 		'chave' => $nfe->getChave(),
		// 		'xml' => $nfe->getXML(),
		// 		'nNf' => $stdIde->nNF,
		// 		'modelo' => $nfe->getModelo()
		// 	];
		// 	return $arr;
		// } else {
		// 	throw new Exception("Erro ao gerar NFce");
		// }


		try{
			$nfe->monta();
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'nNf' => $stdIde->nNF,
				'modelo' => $nfe->getModelo()
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
		
		if (!$this->has13Numbers($eanAsArray)) {
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
		return count($ean) === 13;
	}

	public function sign($xml){

		return $this->tools->signNFe($xml);
	}

	public function transmitirNfce($signXml, $chave, $venda_id = null){
		try{
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
			if($this->tools->contingency->type == 'OFFLINE'){
				
			}else{
				$resp = $this->tools->sefazEnviaLote([$signXml], $idLote, 1);

				sleep($this->timeout);
				$st = new Standardize();
				$std = $st->toStd($resp);

				if ($std->cStat != 103 && $std->cStat != 104) {
					return "Erro: [$std->cStat] - $std->xMotivo";
				}

				if($venda_id != null){
					$venda = VendaCaixa::where('id', $venda_id)->first();
					if($venda != null && $venda->recibo == null){
						$venda->recibo = $resp;
						$venda->save();
					}
				}

				$public = env('SERVIDOR_WEB') ? 'public/' : '';
				try {

					$xml = Complements::toAuthorize($signXml, $resp);
					file_put_contents(public_path('xml_nfce/').$chave.'.xml', $xml);
					return $std->protNFe->infProt->nProt;
				} catch (\Exception $e) {
					return "Erro: " . $st->toJson($resp);
				}
			}
			

		} catch(\Exception $e){
			return "Erro: ".$e->getMessage() ;
		}

	}

	public function consultarNFCe($venda){
		try {

			$this->tools->model('65');

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
					
						// $venda->chave = $chave;
					$venda->estado = 'APROVADO';
					$venda->NFcNumero = $config->ultimo_numero_nfce+1;
					$venda->save();

					$config->ultimo_numero_nfce = $config->ultimo_numero_nfce+1;
					$config->save();
					try{
						$xml = Complements::toAuthorize($venda->signed_xml, $nRec);
						file_put_contents(public_path('xml_nfce/').$chave.'.xml',$xml);
					}catch(\Exception $e){
						
					}

				}
			}

			// $arr = json_decode($json);
			return json_encode($arr);

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

   //  public function transmitirNfce($signXml, $chave){
   //  	try{
   //  		$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
   //  		$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);
   //  		sleep(2);
   //  		$st = new Standardize();
   //  		$std = $st->toStd($resp);

   //  		if ($std->cStat != 103) {

   //  			return "[$std->cStat] - $std->xMotivo";
   //  		}
   //  		sleep(2);
   //  		$recibo = $std->infRec->nRec; 
   //  		$protocolo = $this->tools->sefazConsultaRecibo($recibo);
   //  		sleep(3);
			// // return $protocolo;

   //  		$public = env('SERVIDOR_WEB') ? 'public/' : '';
   //  		try {
   //  			$xml = Complements::toAuthorize($signXml, $protocolo);
   //  			header('Content-type: text/xml; charset=UTF-8');
   //  			file_put_contents($public.'xml_nfce/'.$chave.'.xml',$xml);
   //  			return $recibo;
			// 	// $this->printDanfe($xml);
   //  		} catch (\Exception $e) {
   //  			return "Erro: " . $st->toJson($protocolo);
   //  		}

   //  	} catch(\Exception $e){
   //  		return "Erro: ".$e->getMessage() ;
   //  	}

   //  }	

	public function cancelarNFCe($vendaId, $justificativa){
		try {
			$venda = VendaCaixa::
			where('id', $vendaId)
			->first();

			$chave = $venda->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			sleep(4);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
				// return $arr;
			$xJust = $justificativa;


			$nProt = $arr['protNFe']['infProt']['nProt'];
			sleep(1);

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			if ($std->cStat != 128) {

			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '101' || $cStat == '135' || $cStat == '155' ) {
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfce_cancelada/').$chave.'.xml',$xml);

					return $arr;
				} else {
					return $arr;	
				}
			}   

		} catch (\Exception $e) {
			return 
			[
				'mensagem' => $e->getMessage(),
				'erro' => true
			];
    //TRATAR
		}
	}

	public function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	public function inutilizar($config, $nInicio, $nFinal, $justificativa, $nSerie){
		try{

			// $nSerie = $nSerie;
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

}