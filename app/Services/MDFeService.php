<?php

namespace App\Services;

use NFePHP\MDFe\Make;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\ConfigNota;
use App\Models\Mdfe;
use App\Models\Filial;
use App\Models\ConfigSystem;
use NFePHP\MDFe\Complements;
use App\Models\Certificado;
use NFePHP\Common\Certificate;
use NFePHP\MDFe\Common\Standardize;
use NFePHP\MDFe\Tools;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class MDFeService{

	private $config; 
	protected $empresa_id = null;
	protected $timeout = 8;

	public function __construct($config){

		$value = session('user_logged');
		$this->empresa_id = $value['empresa'];

		$this->config = json_encode($config);

		$certificado = Certificado::
		where('empresa_id', $this->empresa_id)
		->first();

		if($config['is_filial'] > 0){
			$certificado = Filial::findOrFail($config['is_filial']);
			try{
				$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificado->arquivo_certificado, $certificado->senha_certificado));
			}catch(\Exception $e){
				$certificado = Certificado::
				where('empresa_id', $this->empresa_id)
				->first();
				$this->tools = new Tools(json_encode($config), 
					Certificate::readPfx($certificado->arquivo, $certificado->senha));
			}
		}else{
			$this->tools = new Tools(json_encode($config), 
				Certificate::readPfx($certificado->arquivo, $certificado->senha));
		}

		$config = ConfigSystem::first();
		if($config){
			if($config->timeout_mdfe){
				$this->timeout = $config->timeout_mdfe;
			}
		}
	}

	public function teste($nProt){
		$resp = $this->tools->sefazConsultaChave($nProt);
		dd($resp);
	}

	public function gerar($mdfe){
		$mdfex = new Make();
		$mdfex->setOnlyAscii(true);

		$emitente = ConfigNota::
		where('empresa_id', $this->empresa_id)
		->first();

		if($mdfe->filial_id != null){
			$emitente = Filial::findOrFail($mdfe->filial_id);
		}

		$std = new \stdClass();
		$std->cUF = $emitente->cUF;
		$std->tpAmb = (int)$emitente->ambiente;
		$std->tpEmit = $mdfe->tp_emit; 
		// $std->tpTransp = $mdfe->tp_transp; 

		$cnpj = preg_replace('/[^0-9]/', '', $emitente->cnpj);

		$cnpjEmitente = $cnpj;

		$doc = preg_replace('/[^0-9]/', '', $mdfe->veiculoTracao->proprietario_documento);

		if($mdfe->tp_transp){
			$std->tpTransp = $mdfe->tp_transp; 
		}
		
		$std->mod = '58';
		$std->serie = $emitente->numero_serie_mdfe;

		$mdfeLast = Mdfe::lastMdfe();
		if($mdfe->filial_id != null){
			$mdfeLast = $emitente->ultimo_numero_mdfe;
		}

		$std->nMDF = $mdfeLast+1; // ver aqui
		$std->cMDF = rand(11111111, 99999999);
		$std->cDV = '0';
		$std->modal = '1';
		$std->dhEmi = date("Y-m-d\TH:i:sP");
		$std->tpEmis = '1';
		$std->procEmi = '0';
		$std->verProc = '3.0';
		$std->UFIni = $mdfe->uf_inicio;
		$std->UFFim = $mdfe->uf_fim;
		$std->dhIniViagem = $mdfe->data_inicio_viagem . 'T06:00:48-03:00';
		// $std->indCanalVerde = '1';
		// $std->indCarregaPosterior = $mdfe->carga_posterior;
		$mdfex->tagide($std);


		foreach($mdfe->municipiosCarregamento as $m){
			$infMunCarrega = new \stdClass();
			$infMunCarrega->cMunCarrega = $m->cidade->codigo;
			$infMunCarrega->xMunCarrega = $m->cidade->nome;
			$mdfex->taginfMunCarrega($infMunCarrega);
		}

		foreach($mdfe->percurso as $p){

			$infPercurso = new \stdClass();
			$infPercurso->UFPer = $p->uf;
			$mdfex->taginfPercurso($infPercurso);
		}

		$std = new \stdClass();

		$cnpj = $emitente->cnpj;
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace(".", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$cnpjEmitente = $cnpj;

		if(strlen($cnpj) == 14){
			$std->CNPJ = $cnpj;
		}else{
			$std->CPF = $cnpj;
		}

		$ie = str_replace(" ", "", $emitente->ie);
		$std->IE = $ie;
		$std->xNome = $emitente->razao_social;
		$std->xFant = $emitente->nome_fantasia;
		$mdfex->tagemit($std);

		$std = new \stdClass();
		$std->xLgr = $emitente->logradouro;
		$std->nro = $emitente->numero;
		$std->xBairro = $emitente->bairro;
		$std->cMun = $emitente->codMun;
		$std->xMun = $emitente->municipio;
		$cep = str_replace("-", "", $emitente->cep);
		$cep = str_replace(".", "", $cep);
		$std->CEP = $cep;
		$std->UF = $emitente->UF;
		$std->fone = '';
		$std->email = '';
		$mdfex->tagenderEmit($std);

		/* Grupo infANTT */
		$infANTT = new \stdClass();
		if($mdfe->veiculoTracao->rntrc != ''){
			$infANTT->RNTRC = $mdfe->veiculoTracao->rntrc; 
			// pega antt do veiculo de tracao
			$mdfex->taginfANTT($infANTT);
		}

		foreach($mdfe->ciots as $c){
			$infCIOT = new \stdClass();
			$infCIOT->CIOT = $c->codigo;

			$doc = str_replace("-", "", $c->cpf_cnpj);
			$doc = str_replace(".", "", $doc);
			$doc = str_replace("/", "", $doc);
			if(strlen($doc) == 11) $infCIOT->CPF = $doc;
			else $infCIOT->CNPJ = $doc;


			$mdfex->taginfCIOT($infCIOT);

		}

		foreach($mdfe->valesPedagio as $v){
			$valePed = new \stdClass();
			$valePed->CNPJForn = $v->cnpj_fornecedor;
			$doc = str_replace("-", "", $v->cnpj_fornecedor_pagador);
			$doc = str_replace(".", "", $doc);
			$doc = str_replace("/", "", $doc);
			if(strlen($doc) == 11) $valePed->CPFPg = $doc;
			else $valePed->CNPJPg = $doc;

			$valePed->nCompra = $v->numero_compra;
			$valePed->vValePed = $this->format($v->valor);
			$mdfex->tagdisp($valePed);
		}

		$infContratante = new \stdClass();
		$doc = str_replace("-", "", $mdfe->cnpj_contratante);
		$doc = str_replace(".", "", $doc);
		$doc = str_replace("/", "", $doc);

		if(strlen($doc) == 11){
			$infContratante->CPF = $doc;
		}else{
			$infContratante->CNPJ = $doc;
		}
		$mdfex->taginfContratante($infContratante);

		/* Grupo veicTracao */
		$veicTracao = new \stdClass();
		$veicTracao->cInt = '01';
		$placa = str_replace("-", "", $mdfe->veiculoTracao->placa);
		$veicTracao->placa = strtoupper($placa);
		$veicTracao->tara = $mdfe->veiculoTracao->tara;
		$veicTracao->capKG = $mdfe->veiculoTracao->capacidade;
		$veicTracao->tpRod = $mdfe->veiculoTracao->tipo_rodado;
		$veicTracao->tpCar = $mdfe->veiculoTracao->tipo_carroceira;
		$veicTracao->UF = $mdfe->veiculoTracao->uf;

		$condutor = new \stdClass();
		$condutor->xNome = $mdfe->condutor_nome; // banco
		$condutor->CPF = $mdfe->condutor_cpf; // banco
		$veicTracao->condutor = [$condutor];

		$prop = new \stdClass();

		$doc = str_replace("-", "", $mdfe->veiculoTracao->proprietario_documento);
		$doc = str_replace(".", "", $doc);
		$doc = str_replace("/", "", $doc);
		if(strlen($doc) == 11) $prop->CPF = $doc;
		else $prop->CNPJ = $doc;
		
		if($mdfe->veiculoTracao->rntrc != ''){
			$prop->RNTRC = $mdfe->veiculoTracao->rntrc;
		}
		$prop->xNome = $mdfe->veiculoTracao->proprietario_nome;

		$ie = str_replace(" ", "", $mdfe->veiculoTracao->proprietario_ie);
		$prop->IE = $ie;

		$prop->UF = $mdfe->veiculoTracao->uf;
		$prop->tpProp = $mdfe->veiculoTracao->proprietario_tp;
		// $veicTracao->prop = $prop;

		if($cnpjEmitente != $doc && $mdfe->veiculoTracao->rntrc != ''){
			$veicTracao->prop = $prop;
		}

		$mdfex->tagveicTracao($veicTracao);

		/* fim veicTracao */

		/* Grupo veicReboque */
		if($mdfe->veiculo_reboque_id != NULL){
			$veicReboque = new \stdClass();
			$veicReboque->cInt = '02';
			$placa = str_replace("-", "", $mdfe->veiculoReboque->placa);

			$veicReboque->placa = strtoupper($placa);
			$veicReboque->tara = $mdfe->veiculoReboque->tara;
			$veicReboque->capKG = $mdfe->veiculoReboque->capacidade;
			$veicReboque->tpCar = $mdfe->veiculoReboque->tipo_carroceira;
			$veicReboque->UF = $mdfe->veiculoReboque->uf;

			$prop = new \stdClass();
			$doc = str_replace("-", "", $mdfe->veiculoReboque->proprietario_documento);
			$doc = str_replace(".", "", $doc);
			$doc = str_replace("/", "", $doc);
			if(strlen($doc) == 11) $prop->CPF = $doc;
			else $prop->CNPJ = $doc;

			$prop->RNTRC = $mdfe->veiculoReboque->rntrc;
			$prop->xNome = $mdfe->veiculoReboque->proprietario_nome;

			$ie = str_replace(" ", "", $mdfe->veiculoReboque->proprietario_ie);
			$prop->IE = $ie;

			$prop->UF = $mdfe->veiculoReboque->uf;
			$prop->tpProp = $mdfe->veiculoReboque->proprietario_tp;

			if($cnpjEmitente != $doc){
				$veicReboque->prop = $prop;
			}

			$mdfex->tagveicReboque($veicReboque);
		}

		if($mdfe->veiculo_reboque2_id != NULL){
			$veicReboque = new \stdClass();
			$veicReboque->cInt = '03';
			$placa = str_replace("-", "", $mdfe->veiculoReboque2->placa);

			$veicReboque->placa = strtoupper($placa);
			$veicReboque->tara = $mdfe->veiculoReboque2->tara;
			$veicReboque->capKG = $mdfe->veiculoReboque2->capacidade;
			$veicReboque->tpCar = $mdfe->veiculoReboque2->tipo_carroceira;
			$veicReboque->UF = $mdfe->veiculoReboque2->uf;

			$prop = new \stdClass();
			$doc = str_replace("-", "", $mdfe->veiculoReboque2->proprietario_documento);
			$doc = str_replace(".", "", $doc);
			$doc = str_replace("/", "", $doc);
			if(strlen($doc) == 11) $prop->CPF = $doc;
			else $prop->CNPJ = $doc;

			$prop->RNTRC = $mdfe->veiculoReboque2->rntrc;
			$prop->xNome = $mdfe->veiculoReboque2->proprietario_nome;
			
			$ie = str_replace(" ", "", $mdfe->veiculoReboque2->proprietario_ie);
			$prop->IE = $ie;

			$prop->UF = $mdfe->veiculoReboque2->uf;
			$prop->tpProp = $mdfe->veiculoReboque2->proprietario_tp;

			if($cnpjEmitente != $doc){
				$veicReboque->prop = $prop;
			}

			$mdfex->tagveicReboque($veicReboque);
		}

		if($mdfe->veiculo_reboque3_id != NULL){

			$veicReboque = new \stdClass();
			$veicReboque->cInt = '04';
			$placa = str_replace("-", "", $mdfe->veiculoReboque3->placa);

			$veicReboque->placa = strtoupper($placa);
			$veicReboque->tara = $mdfe->veiculoReboque3->tara;
			$veicReboque->capKG = $mdfe->veiculoReboque3->capacidade;
			$veicReboque->tpCar = $mdfe->veiculoReboque3->tipo_carroceira;
			$veicReboque->UF = $mdfe->veiculoReboque3->uf;

			$prop = new \stdClass();
			$doc = str_replace("-", "", $mdfe->veiculoReboque3->proprietario_documento);
			$doc = str_replace(".", "", $doc);
			$doc = str_replace("/", "", $doc);
			if(strlen($doc) == 11) $prop->CPF = $doc;
			else $prop->CNPJ = $doc;

			$prop->RNTRC = $mdfe->veiculoReboque3->rntrc;
			$prop->xNome = $mdfe->veiculoReboque3->proprietario_nome;
			
			$ie = str_replace(" ", "", $mdfe->veiculoReboque3->proprietario_ie);
			$prop->IE = $ie;

			$prop->UF = $mdfe->veiculoReboque3->uf;
			$prop->tpProp = $mdfe->veiculoReboque3->proprietario_tp;

			if($cnpjEmitente != $doc){
				$veicReboque->prop = $prop;
			}

			$mdfex->tagveicReboque($veicReboque);
		}

		$lacRodo = new \stdClass();
		$lacRodo->nLacre = $mdfe->lac_rodo == "" ? "0" : $mdfe->lac_rodo;//ver no banco
		$mdfex->taglacRodo($lacRodo);


		/*
		 * Grupo infDoc ( Documentos fiscais )
		 */
		$cont = 0;
		$contNFe = 0; 
		$contCTe = 0; 

		
		$infos = $this->unirDescarregamentoCidade($mdfe->infoDescarga);
		foreach($infos as $key => $info) {
			$infMunDescarga = new \stdClass();
			$infMunDescarga->cMunDescarga = $info['codigo_cidade'];
			$infMunDescarga->xMunDescarga = $info['nome_cidade'];
			$infMunDescarga->nItem = $key;
			$mdfex->taginfMunDescarga($infMunDescarga);

			/* infCTe */
			// $std = new \stdClass();
			// $std->chCTe = $info->cte->chave;
			// $std->SegCodBarra = '';
			// $std->indReentrega = '1';
			// $std->nItem = $cont;

			$chavesNfe = isset($info['chave_nfe']) ? explode(";", $info['chave_nfe']) : [];
			$chavesCte = isset($info['chave_cte']) ? explode(";", $info['chave_cte']) : [];


			if(sizeof($chavesNfe) > 1 || sizeof($chavesCte) > 1){
				foreach($chavesNfe as $ch){
					if($ch){

						$std = new \stdClass();
						$std->chNFe = $ch;
						$std->SegCodBarra = '';
						$std->indReentrega = '1';
						$std->nItem = $cont;
						$contNFe++;

						$mdfex->taginfNFe($std);
					}
				}

				foreach($chavesCte as $ch){
					if($ch){
						$std = new \stdClass();
						$std->chCTe = $ch;
						$std->SegCodBarra = '';
						$std->indReentrega = '1';
						$std->nItem = $cont;
						$contCTe++;
						$mdfex->taginfCTe($std);
					}
				}

			}else{
				
				if($info['chave_nfe'] != ""){

					$std = new \stdClass();
					$std->chNFe = $info['chave_nfe'];
					$std->SegCodBarra = '';
					$std->indReentrega = '1';
					$std->nItem = $cont;
					$contNFe++;

					$mdfex->taginfNFe($std);

				}else{

					/* infCTe */
					$std = new \stdClass();
					$std->chCTe = $info['chave_cte'];
					$std->SegCodBarra = '';
					$std->indReentrega = '1';
					$std->nItem = $cont;
					$contCTe++;
					$mdfex->taginfCTe($std);

				}
			}

			/* Informações das Unidades de Transporte (Carreta/Reboque/Vagão) */
			$stdinfUnidTransp = new \stdClass();
			$stdinfUnidTransp->tpUnidTransp = $info['tp_unid_transp'];

			$stdinfUnidTransp->idUnidTransp = strtoupper($info['id_unid_transp']);

			/* Lacres das Unidades de Transporte */

			$lacres = [];
			$lacresTemp = $info['lacresTransp'];
			array_push($lacres, $lacresTemp);

			
			$stdlacUnidTransp = new \stdClass();
			$stdlacUnidTransp->nLacre = $lacres;

			$stdinfUnidTransp->lacUnidTransp = $stdlacUnidTransp;

			/* Informações das Unidades de Carga (Containeres/ULD/Outros) */
			$stdinfUnidCarga = new \stdClass();
			$stdinfUnidCarga->tpUnidCarga = '1';

			$unidades = explode(";", $info['id_unidade_carga']);

			if(sizeof($unidades) > 1){
				$temp = [];
				foreach($unidades as $u){
					array_push($temp, $u);
				}
				$stdinfUnidCarga->idUnidCarga = $temp;

			}else{
				$stdinfUnidCarga->idUnidCarga = $info['id_unidade_carga'];
			}


			/* Lacres das Unidades de Carga */
			$lacres = [];
			$lacres = $info['lacresUnidCarga'];


			$stdlacUnidCarga = new \stdClass();
			$stdlacUnidCarga->nLacre = $lacres;


			$stdinfUnidCarga->lacUnidCarga = $stdlacUnidCarga;
			$stdinfUnidCarga->qtdRat = $info['quantidade_rateio_carga'];

			$stdinfUnidTransp->infUnidCarga = [$stdinfUnidCarga];
			$stdinfUnidTransp->qtdRat = $info['quantidade_rateio'];

			$std->infUnidTransp = [$stdinfUnidTransp];


			$cont++;

		}

		

		/* Grupo do Seguro */
		if($mdfe->seguradora_cnpj != null){
			$std = new \stdClass();
			$std->respSeg = '1';

			$cnpj = $mdfe->seguradora_cnpj;
			$cnpj = str_replace("/", "", $cnpj);
			$cnpj = str_replace(".", "", $cnpj);
			$cnpj = str_replace(" ", "", $cnpj);
			$cnpj = str_replace("-", "", $cnpj);
			/* Informações da seguradora */
			$stdinfSeg = new \stdClass();
			$stdinfSeg->xSeg = $mdfe->seguradora_nome;
			$stdinfSeg->CNPJ = $cnpj;

			$std->infSeg = $stdinfSeg;
			$std->nApol = $mdfe->numero_apolice;
			$std->nAver = [$mdfe->numero_averbacao];
			$mdfex->tagseg($std);
			/* fim grupo Seguro */
			// print_r($std);
			// die();
		}

		if($mdfe->produto_pred_nome != ''){
			$prodPred = new \stdClass();
			$prodPred->tpCarga = $mdfe->tp_carga;
			$prodPred->xProd = $mdfe->produto_pred_nome;

			if($mdfe->produto_pred_cod_barras != '' && $mdfe->produto_pred_cod_barras > 0){
				$prodPred->cEAN = $mdfe->produto_pred_cod_barras;
			}else{
				$prodPred->cEAN = null;
			}
			if($mdfe->produto_pred_ncm != '' && $mdfe->produto_pred_ncm > 0){
				$prodPred->NCM = $mdfe->produto_pred_ncm;
			}else{
				$prodPred->NCM = null;
			}

			$localCarrega = new \stdClass();
			if($mdfe->cep_carrega != ''){
				$localCarrega->CEP = $mdfe->cep_carrega;
			}
			if($mdfe->latitude_carregamento != ''){
				$localCarrega->latitude = $this->preparaCordenada($mdfe->latitude_carregamento);
				$localCarrega->longitude = $this->preparaCordenada($mdfe->longitude_carregamento);
			}

			$localDescarrega = new \stdClass();
			$localDescarrega->CEP = $mdfe->cep_descarrega;
			if($mdfe->latitude_descarregamento != ''){
				$localDescarrega->latitude = $this->preparaCordenada($mdfe->latitude_descarregamento);
				$localDescarrega->longitude = $this->preparaCordenada($mdfe->longitude_descarregamento);
			}

			$lotacao = new \stdClass();

			$lotacao->infLocalCarrega = $localCarrega;
			$lotacao->infLocalDescarrega = $localDescarrega;

			if(isset($lotacao->infLocalCarrega)){
				$prodPred->infLotacao = $lotacao;
			}
			// print_r($prodPred);
			// die();
			$mdfex->tagprodPred($prodPred);
		}


		/* grupo de totais */
		$std = new \stdClass();
		$std->vCarga = $this->format($mdfe->valor_carga);
		$std->cUnid = '02';
		if($contNFe > 0){
			$std->qNFe = $contNFe;
		}
		$std->qCTe = $contCTe;
		$std->qCarga = $mdfe->quantidade_carga;
		$mdfex->tagtot($std);
		/* fim grupo de totais */

		

		if($emitente->aut_xml != ""){
			$std = new \stdClass();
			$cnpj = $emitente->aut_xml;
			$cnpj = str_replace(".", "", $cnpj);
			$cnpj = str_replace("-", "", $cnpj);
			$cnpj = str_replace("/", "", $cnpj);
			$cnpj = str_replace(" ", "", $cnpj);
			$std->CNPJ = $cnpj;
			$mdfex->tagautXML($std);
		}

		try{
			$xml = $mdfex->getXML();
			header("Content-type: text/xml");

			return [
				'xml' => $xml,
				'numero' => $mdfeLast+1
			];
		}catch(\Exception $e){
			return ['erros_xml' => $mdfex->getErrors()];
		}

	}

	private function preparaCordenada($cordenada){
		if(strlen($cordenada) == 10) return $cordenada;

		$dif = 10 - strlen($cordenada);
		$temp = $cordenada;
		for($i=0; $i<$dif; $i++){
			$temp .= "0";
		} 
		return $temp;
	}

	private function unirDescarregamentoCidade($infos){
		$arrInit = [];

		foreach($infos as $i){
			$temp = [
				'codigo_cidade' => $i->cidade->codigo,
				'nome_cidade' => $i->cidade->nome,
				
				'chave_cte' => $i->cte ? $i->cte->chave : '',
				'chave_nfe' => $i->nfe ? $i->nfe->chave : '',
				'tp_unid_transp' => $i->tp_unid_transp,
				'id_unid_transp' => $i->id_unid_transp,
				'lacresTransp' => $i->lacresTransp,
				'id_unidade_carga' => $i->unidadeCarga->id_unidade_carga ?? 0,
				'lacresUnidCarga' => $i->lacresUnidCarga,
				'quantidade_rateio_carga' => $i->unidadeCarga->quantidade_rateio ?? 0,
				'quantidade_rateio' => $i->quantidade_rateio ?? 0
			];
			array_push($arrInit, $temp);
		}

		// print_r($arrInit);


		$retorno = [];
		for($i = 0; $i < sizeof($arrInit); $i++){

			$indice = $this->verificaDuplicado($retorno, $arrInit[$i]['codigo_cidade']);
			if($indice == -1){

				array_push($retorno, $arrInit[$i]);

			}else{
				// $chavesNfe = isset($info['chave_nfe']) ? explode(";", $info['chave_nfe']) : [];
				// $chavesCte = isset($info['chave_cte']) ? explode(";", $info['chave_cte']) : [];
				if(isset($arrInit[$i]['chave_nfe'])){
					$retorno[$indice]['chave_nfe'] .= ";" . $arrInit[$i]['chave_nfe'];
				}
				if(isset($arrInit[$i]['chave_cte'])){
					$retorno[$indice]['chave_cte'] .= ";" . $arrInit[$i]['chave_cte'];
				}


				$temp = $retorno[$indice]['lacresTransp'];
				$temp2 = $arrInit[$i]['lacresTransp'];
				$lacres = [];


				foreach($temp2 as $t){
					array_push($lacres, $t->numero);
				}
				$retorno[$indice]['lacresTransp'] = $lacres;
				$retorno[$indice]['id_unidade_carga'] .= ";" . $arrInit[$i]['id_unidade_carga'];


				$temp = $retorno[$indice]['lacresUnidCarga'];
				$temp2 = $arrInit[$i]['lacresUnidCarga'];
				$lacres = [];

				// foreach($temp as $t){
				// 	array_push($lacres, $t->numero);
				// }

				foreach($temp2 as $t){
					array_push($lacres, $t->numero);
				}

				$retorno[$indice]['lacresUnidCarga'] = $lacres;

				$retorno[$indice]['quantidade_rateio_carga'] +=  $arrInit[$i]['quantidade_rateio_carga'];
				$retorno[$indice]['quantidade_rateio'] +=  $arrInit[$i]['quantidade_rateio'];

			}

		}

		// echo "<pre>";
		// print_r($retorno);
		// echo "</pre>";

		// die();


		return $retorno;
	}

	private function verificaDuplicado($arrInit, $codMun){
		$retorno = -1;
		for($i = 0; $i < sizeof($arrInit); $i++){
			if($arrInit[$i]['codigo_cidade'] && $arrInit[$i]['codigo_cidade'] == $codMun) $retorno = $i;
		}
		return $retorno;
	}

	public function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	public function sign($xml){
		return $this->tools->signMDFe($xml);
	}

	public function consultaRecibo($nProt){
		$resp = $this->tools->sefazConsultaRecibo($nProt);
		return $resp;
	}

	public function transmitir($signXml){
		try{
			$resp = $this->tools->sefazEnviaLote([$signXml], rand(1, 10000), 1);

			$st = new Standardize();
			$std = $st->toStd($resp);

			sleep($this->timeout);

			if ($std->cStat != 100) {
				
				return [
					'erro' => true, 
					'message' => $std->xMotivo, 
					'cStat' => $std->cStat
				];
			}

			// return [
			// 	'erro' => true, 
			// 	'message' => $std, 
			// 	'cStat' => '999'
			// ];
			// else{
			// 	return [
			// 		'erro' => true, 
			// 		'message' => $std, 
			// 		'cStat' => $std->cStat
			// 	];
			// }



			// $resp = $this->tools->sefazConsultaRecibo($std->infRec->nRec);
			
			// $std = $st->toStd($resp);
			// sleep(2);

			if(!isset($std->protMDFe)){
				return [
					'erro' => true, 
					'message' => 'Tente enviar novamente em minutos!', 
					'cStat' => '999'
				];
			}

			$chave = $std->protMDFe->infProt->chMDFe;
			$cStat = $std->protMDFe->infProt->cStat;

			if($cStat == '100'){

				$xml = Complements::toAuthorize($signXml, $resp);
				file_put_contents(public_path('xml_mdfe/').$chave.'.xml', $xml);
				return [
					'chave' => $chave, 
					'protocolo' => $std->protMDFe->infProt->nProt, 
					'cStat' => $cStat
				];
			}else{
				return [
					'erro' => true, 
					'message' => $std->protMDFe->infProt->xMotivo, 
					'cStat' => $cStat
				];
			}
			return $std->protMDFe->infProt->chMDFe;

		} catch(\Exception $e){
			return [
				'erro' => true, 
				'message' => $e->getMessage(),
				'cStat' => ''
			];
		}

	}	


	public function naoEncerrados(){
		try {

			$resp = $this->tools->sefazConsultaNaoEncerrados();

			$st = new Standardize();
			$std = $st->toArray($resp);

			return $std;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function encerrar($chave, $protocolo){
		try {
			$emitente = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();
			$chave = $chave;
			$nProt = $protocolo;
			$cUF = $emitente->cUF;
			$cMun = $emitente->codMun;
			$dtEnc = date('Y-m-d'); // Opcional, caso nao seja preenchido pegara HOJE
			$resp = $this->tools->sefazEncerra($chave, $nProt, $cUF, $cMun, $dtEnc);

			$st = new Standardize();
			$std = $st->toStd($resp);

			return $std;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function consultar($chave){
		try {
			
			$chave = $chave;
			$resp = $this->tools->sefazConsultaChave($chave);

			$st = new Standardize();
			$std = $st->toStd($resp);

			return $std;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function cancelar($chave, $protocolo, $justificativa){
		try {
			$xJust = $justificativa;
			$nProt = $protocolo;
			
			$chave = $chave;
			$resp = $this->tools->sefazCancela($chave, $xJust, $nProt);
			sleep(5);
			$st = new Standardize();
			$std = $st->toStd($resp);

			$stdCl = new Standardize($resp);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();
			// return $json;
			$cStat = $std->infEvento->cStat;

			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
				$xml = Complements::toAuthorize($this->tools->lastRequest, $resp);
				file_put_contents(public_path('xml_mdfe_cancelada/').$chave.'.xml',$xml);
			}
			return $std;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}


}
