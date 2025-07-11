<?php

namespace App\Helpers;

use App\Models\Estoque;
use App\Models\Produto;
use App\Models\Empresa;
use App\Models\ContaBancaria;
use App\Models\ContaReceber;
use App\Models\Remessa;
use App\Models\RemessaBoleto;
use Illuminate\Support\Str;

class BoletoHelper {
	protected $empresa = null;
	public function __construct($empresa){
		$this->empresa = $empresa;
	}
	
	public function gerar($boleto){

		$contaReceber = $boleto->conta;

		$beneficiario = $this->getBeneficiario($boleto);
		$pagador = $this->getPagador($contaReceber);

		$boletoAux = $boleto;


		if($boletoAux->logo){
			$config = $this->empresa->configNota;
			if($config->logo){
				$logo = public_path('logos'). '/'.$config->logo;
			}else{
				$logo = '';
			}
		}else{
			$logo = '';
		}

		$dataBoleto = [
			'logo' => $logo,
			'dataVencimento' => \Carbon\Carbon::parse($boleto->conta->data_vencimento),
			'valor' => $boleto->conta->valor_integral,
			'numero' => $boleto->numero,
			'numeroDocumento' => $boleto->numero_documento,
			'pagador' => $pagador,
			'beneficiario' => $beneficiario,
			'carteira' => $boleto->carteira,
			'agencia' => $boleto->banco->agencia,
			'convenio' => $boleto->convenio,
			'conta' => $boleto->banco->conta,
			'multa' => $boleto->multa, 
			'juros' => $boleto->juros, 
			'jurosApos' => $boleto->juros_apos,
			'descricaoDemonstrativo' => [],
			'instrucoes' => [$boleto->instrucoes],
		];

		try{
			$boleto = $this->geraBoleto($dataBoleto, $boletoAux);

			$pdf = new \Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
			$pdf->addBoleto($boleto);
			$pdf->showPrint();
			$pdf->hideInstrucoes();

			$fileName = $pdf->setBoleto();

			$boletoAux->nome_arquivo = $fileName;
			$boletoAux->linha_digitavel = $boleto->getCampoCodigoBarras();

			$boletoAux->save();
			return $fileName . ".pdf";

		}catch(\Exception $e){
			return [
				'erro' => true,
				'mensagem' => $e->getMessage()
			];
		}

	}

	private function getBeneficiario($boleto){
		// $config = $this->empresa->configNota;
		$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa([
			'documento' => $boleto->banco->cnpj,
			'nome'      => $boleto->banco->titular,
			'cep'       => $boleto->banco->cep,
			'endereco'  => $boleto->banco->endereco,
			'bairro' 	=> $boleto->banco->bairro,
			'uf'        => $boleto->banco->cidade->uf,
			'cidade'    => $boleto->banco->cidade->nome,
		]);
		return $beneficiario;
	}

	private function getPagador($contaReceber){
		
		$cliente = null;
		if($contaReceber->venda_id != null){
			$cliente = $contaReceber->venda->cliente;
		}else if($contaReceber->cliente_id != null){
			$cliente = $contaReceber->cliente;
		}else{
			return null;
		}

		$pagador = new \Eduardokum\LaravelBoleto\Pessoa([
			'documento' => $cliente->cpf_cnpj,
			'nome'      => $cliente->razao_social,
			'cep'       => $cliente->cep,
			'endereco'  => "$cliente->rua, $cliente->numero",
			'bairro' 	=> $cliente->bairro,
			'uf'        => $cliente->cidade->uf,
			'cidade'    => $cliente->cidade->nome,
		]);

		return $pagador;
	}

	private function geraBoleto($data, $boletoAux){
		$boleto = null;

		if($boletoAux->banco->banco == 'Banco do Brasil'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Bb($data);
		}else if($boletoAux->banco->banco == 'Itau'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Itau($data);
		}else if($boletoAux->banco->banco == 'Bradesco'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Bradesco($data);
		}else if($boletoAux->banco->banco == 'Sicoob'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Bancoob($data);
		}else if($boletoAux->banco->banco == 'Sicredi'){

			$data['posto'] = $boletoAux->posto;
			$data['byte'] = 2;
			$data['codigoCliente'] = $boletoAux->codigo_cliente;
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Sicredi($data);
		}else if($boletoAux->banco->banco == 'Cresol'){

			$data['posto'] = $boletoAux->posto;
			$data['byte'] = 2;
			$data['codigoCliente'] = $boletoAux->codigo_cliente;
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Cresol($data);
		}else if($boletoAux->banco->banco == 'Caixa Econônica Federal'){
			// $data['posto'] = $boletoAux->posto;
			// $data['byte'] = 2;

			$data['codigoCliente'] = $boletoAux->codigo_cliente;
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Caixa($data);
		}

		else if ($boletoAux->banco->banco == 'Santander') {
			// Buscando o código do cliente a partir do pagador
			$pagador = $this->getPagador($boletoAux->conta);
		
			if (!$pagador || empty($pagador->getDocumento())) {
				throw new \Exception("O campo 'codigoCliente' (CPF/CNPJ do pagador) é obrigatório para o banco Santander.");
			}
		
			$data['codigoCliente'] = $pagador->getDocumento(); // Usa CPF/CNPJ do pagador como codigoCliente
		
			$boleto = new \Eduardokum\LaravelBoleto\Boleto\Banco\Santander($data);
		}		

		return $boleto;
	}

	public function simular($boletos){
		$resposta = true;
		foreach($boletos as $b){

			$contaReceber = ContaReceber::find($b['conta_id']);
			$beneficiario = $this->getBeneficiarioSimulacao($b['banco_id']);
			$pagador = $this->getPagador($contaReceber);

			$config = $this->empresa->configNota;
			if($config->logo){
				$logo = public_path('logos'). '/'.$config->logo;
			}else{
				$logo = '';
			}

			$banco = ContaBancaria::find($b['banco_id']);

			$dataBoleto = [
				'logo' => $logo,
				'dataVencimento' => \Carbon\Carbon::parse($contaReceber->data_vencimento),
				'valor' => $contaReceber->valor_integral,
				'numero' => $b['numero'],
				'numeroDocumento' => $b['numero_documento'],
				'pagador' => $pagador,
				'beneficiario' => $beneficiario,
				'carteira' => $b['carteira'],
				'agencia' => $banco->agencia,
				'convenio' => $b['convenio'],
				'conta' => $banco->conta,
				'multa' => $b['multa'], 
				'juros' => $b['juros'], 
				'jurosApos' => $b['juros_apos'],
				'descricaoDemonstrativo' => [],
				'instrucoes' => [],
			];

			try{
				$boleto = $this->geraBoletoSimulacao($dataBoleto, $b);
				$pdf = new \Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
				$pdf->addBoleto($boleto);
				$pdf->showPrint();

				$fileName = $pdf->setBoletoAux();
			}catch(\Exception $e){
				return [
					'erro' => true,
					'mensagem' => $e->getMessage()
				];
			}
			
		}

		return true;

	}

	private function getBeneficiarioSimulacao($bancoId){
		// $config = $this->empresa->configNota;
		$banco = ContaBancaria::find($bancoId);
		$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa([
			'documento' => $banco->cnpj,
			'nome'      => $banco->titular,
			'cep'       => $banco->cep,
			'endereco'  => $banco->endereco,
			'bairro' 	=> $banco->bairro,
			'uf'        => $banco->cidade->uf,
			'cidade'    => $banco->cidade->nome,
		]);
		return $beneficiario;
	}

	private function geraBoletoSimulacao($data, $b){
		$banco = ContaBancaria::find($b['banco_id']);

		$boleto = null;
		if($banco->banco == 'Banco do Brasil'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Bb($data);
		}else if($banco->banco == 'Itau'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Itau($data);
		}else if($banco->banco == 'Bradesco'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Bradesco($data);
		}else if($banco->banco == 'Sicoob'){
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Bancoob($data);
		}else if($banco->banco == 'Sicredi'){
			$data['posto'] = $b['posto'];
			$data['byte'] = 2;
			$data['codigoCliente'] = $b['codigo_cliente'];
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Sicredi($data);
		}else if($banco->banco == 'Cresol'){
			$data['posto'] = $b['posto'];
			$data['byte'] = 2;
			$data['codigoCliente'] = $b['codigo_cliente'];
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Cresol($data);
		}else if($banco->banco == 'Caixa Econônica Federal'){
			// $data['posto'] = $b['posto'];
			// $data['byte'] = 2;
			$data['codigoCliente'] = $b['codigo_cliente'];
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Caixa($data);
		}else if($banco->banco == 'Santander'){
			// $data['posto'] = $b['posto'];
			// $data['byte'] = 2;
			$data['codigoCliente'] = $b['codigo_cliente'];
			$boleto	= new \Eduardokum\LaravelBoleto\Boleto\Banco\Santander($data);
		}

		return $boleto;
	}

	public function gerarMulti($boletos){
		$resposta = true;
		foreach($boletos as $b){

			$contaReceber = ContaReceber::find($b['conta_id']);
			$beneficiario = $this->getBeneficiarioSimulacao($b['banco_id']);
			$pagador = $this->getPagador($contaReceber);

			$config = $this->empresa->configNota;
			if($config->logo){
				$logo = public_path('logos'). '/'.$config->logo;
			}else{
				$logo = '';
			}

			$banco = ContaBancaria::find($b['banco_id']);

			$dataBoleto = [
				'logo' => $logo,
				'dataVencimento' => \Carbon\Carbon::parse($contaReceber->data_vencimento),
				'valor' => $contaReceber->valor_integral,
				'numero' => $b['numero'],
				'numeroDocumento' => $b['numero_documento'],
				'pagador' => $pagador,
				'beneficiario' => $beneficiario,
				'carteira' => $b['carteira'],
				'agencia' => $banco->agencia,
				'convenio' => $b['convenio'],
				'conta' => $banco->conta,
				'multa' => $b['multa'], 
				'juros' => $b['juros'], 
				'jurosApos' => $b['juros_apos'],
				'descricaoDemonstrativo' => [],
				'instrucoes' => [],
			];

			try{
				$boleto = $this->geraBoletoSimulacao($dataBoleto, $b);
			}catch(\Exception $e){
				return [
					'erro' => true,
					'mensagem' => $e->getMessage()
				];
			}
			
		}

		return true;
	}

	public function gerarRemessa($boleto){
		$config = $this->empresa->configNota;

		$boletoRemessa = RemessaBoleto::where('boleto_id', $boleto->id)
		->first();

		if($boletoRemessa != null){
			$nomeArquivo = $boletoRemessa->remessa->nome_arquivo;
			$file = public_path('remessas')."/$nomeArquivo.txt";
			if(file_exists($file)){
				$file = public_path('remessas')."/$nomeArquivo.txt";

				header('Content-Type: application/txt');
				header('Content-Disposition: attachment; filename='.$nomeArquivo.'.txt"');
				readfile($file);

				die();
			}else{
				Remessa::find($boletoRemessa->remessa_id)->delete();
			}

		}

		if($config->logo){
			$logo = public_path('logos'). '/'.$config->logo;
		}else{
			$logo = '';
		}

		$pagador = $this->getPagador($boleto->conta);
		$beneficiario = $this->getBeneficiario($boleto);
		$dataBoleto = [
			'logo' => $logo,
			'dataVencimento' => \Carbon\Carbon::parse($boleto->conta->data_vencimento),
			'valor' => $boleto->conta->valor_integral,
			'numero' => $boleto->numero,
			'numeroDocumento' => $boleto->numero_documento,
			'pagador' => $pagador,
			'beneficiario' => $beneficiario,
			'carteira' => $boleto->carteira,
			'agencia' => $boleto->banco->agencia,
			'convenio' => $boleto->convenio,
			'conta' => $boleto->banco->conta,
			'multa' => $boleto->multa, 
			'juros' => $boleto->juros, 
			'jurosApos' => $boleto->juros_apos,
			'descricaoDemonstrativo' => [],
			'instrucoes' => [],
		];

		$boletoAux = $this->geraBoleto($dataBoleto, $boleto);

		$sendArray = [
			'beneficiario'     => $beneficiario,
			'carteira'         => $boleto->carteira,
			'agencia'          => $boleto->banco->agencia,
			'convenio'         => $boleto->convenio,
			'variacaoCarteira' => '1',
			'conta'            => $boleto->banco->conta
		];
		
		$bancoNome = trim(strtolower($boleto->banco->banco));
		// Debug para ver qual valor está vindo
		// dd($bancoNome);
		if (in_array($bancoNome, ['bradesco', 'sicredi', 'cresol'])) {
			$sendArray['idremessa'] = $boleto->banco->numero_remessa;
		}
		if ($bancoNome == 'caixa econômica federal') {
			$sendArray['idremessa'] = $boleto->banco->numero_remessa;
			$sendArray['codigoCliente'] = $boleto->codigo_cliente;
		}
		if ($bancoNome == 'sicoob') {
			$sendArray['idremessa'] = $boleto->banco->numero_remessa;
		}
		if ($bancoNome == 'santander') {
			$pagador = $this->getPagador($boleto->conta);
			if (!$pagador || empty($pagador->getDocumento())) {
				throw new \Exception("O campo 'codigoCliente' (CPF/CNPJ do pagador) é obrigatório para o banco Santander.");
			}
			$sendArray['codigoCliente'] = $pagador->getDocumento();
		}
		
		// Fallback: se idremessa não foi definida, defina um valor padrão
		if (!isset($sendArray['idremessa'])) {
			$sendArray['idremessa'] = $boleto->banco->numero_remessa ?? '0000000';
		}		

		if ($boleto->banco->banco == 'Sicredi') {
			// Extrai a conta do banco do boleto
			if (!$boleto->banco->conta || empty($boleto->banco->conta)) {
				throw new \Exception("O campo 'conta' é obrigatório para o banco Sicredi.");
			}
		
			// Extrai apenas o número antes do traço
			$numeroConta = explode('-', $boleto->banco->conta)[0];
		
			// Atribui o número da conta ao campo 'codigoCliente'
			$sendArray['codigoCliente'] = $numeroConta;
		}

		if ($boleto->banco->banco == 'Cresol') {
			// Extrai a conta do banco do boleto
			if (!$boleto->banco->conta || empty($boleto->banco->conta)) {
				throw new \Exception("O campo 'conta' é obrigatório para o banco Cresol.");
			}
		
			// Extrai apenas o número antes do traço
			$numeroConta = explode('-', $boleto->banco->conta)[0];
		
			// Atribui o número da conta ao campo 'codigoCliente'
			$sendArray['codigoCliente'] = $numeroConta;
		}
		

		$send = $this->setTipoRemessa($sendArray, $boleto);
		$send->addBoleto($boletoAux);
		$send->gerar();

		if(!is_dir(public_path('remessas'))){
			mkdir(public_path('remessas'), 0777, true);
		}

		try {
			// Gera o nome do arquivo no formato Sicredi
			$numeroConta = explode('-', $boleto->banco->conta)[0]; // Extrai o número da conta antes do traço
			$mesAtual = date('n'); // Número do mês (1-12)
			$diaAtual = date('d'); // Número do dia (01-31)
		
			$tabelaMes = [
				1 => '1',  2 => '2',  3 => '3',  4 => '4',  5 => '5',  6 => '6',
				7 => '7',  8 => '8',  9 => '9', 10 => 'O', 11 => 'N', 12 => 'D'
			];
			$codigoMes = $tabelaMes[$mesAtual]; // Obtém o código do mês de acordo com a tabela
		
			if (strtolower($boleto->banco->banco) === 'cresol') {
				// Se for Cresol, extensão .rem
				$nameFile = "{$numeroConta}{$codigoMes}{$diaAtual}.rem";
			} else {
				// Caso contrário, extensão .txt
				$nameFile = "{$numeroConta}{$codigoMes}{$diaAtual}.txt";
			}	
		
			// Gera o conteúdo da remessa
			$content = $send->gerar();
		
			// Remove qualquer coisa antes do "01REMESSA"
			$content = preg_replace('/^.*?(01REMESSA)/', '$1', $content);
		
			// Salva o conteúdo tratado no arquivo
			$filePath = public_path('remessas') . "/$nameFile";
			file_put_contents($filePath, $content);
		
			// Salva as informações da remessa no banco de dados
			$result = Remessa::create([
				'nome_arquivo' => $nameFile,
				'empresa_id' => $this->empresa->id,
			]);
		
			RemessaBoleto::create([
				'remessa_id' => $result->id,
				'boleto_id' => $boleto->id,
			]);
			
			// **Incrementa o número da remessa para a próxima operação**
			$boleto->banco->increment('numero_remessa');

			// Usa o método `download` do objeto $send com o nome correto do arquivo
			$send->save($filePath); // Salva o arquivo no caminho correto
			return $send->download($nameFile); // Faz o download apenas com o nome do arquivo
		
		} catch (\Exception $e) {
			\Log::error('Erro ao gerar remessa: ' . $e->getMessage());
			return response()->json(['erro' => true, 'mensagem' => $e->getMessage()]);
		}					
	}


	private function setTipoRemessa($sendArray, $boleto){
		$remessa = null;
		$tipo = $boleto->tipo;
		if($boleto->banco->banco == 'Banco do Brasil'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bb($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bb($sendArray);
			}
		}else if($boleto->banco->banco == 'Itau'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Itau($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Itau($sendArray);
			}
		}else if($boleto->banco->banco == 'Bradesco'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bradesco($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bradesco($sendArray);
			}
		}else if($boleto->banco->banco == 'Sicredi'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Sicredi($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Sicredi($sendArray);
			}
		}else if($boleto->banco->banco == 'Cresol'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Cresol($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Cresol($sendArray);
			}
		}
		else if($boleto->banco->banco == 'Caixa Econônica Federal'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Caixa($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Caixa($sendArray);
			}
		}
		else if($boleto->banco->banco == 'Santander'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Santander($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Santander($sendArray);
			}
		}
		else if($boleto->banco->banco == 'Sicoob'){
			if($tipo == 'Cnab400'){	
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bancoob($sendArray);
			}else{
				$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bancoob($sendArray);
			}
		}

		return $remessa;
	}

	public function gerarRemessaMulti($boletos){
		$config = $this->empresa->configNota;
		$multiBoletos = [];

		foreach($boletos as $boleto){

			if($config->logo){
				$logo = public_path('logos'). '/'.$config->logo;
			}else{
				$logo = '';
			}

			$pagador = $this->getPagador($boleto->conta);
			$beneficiario = $this->getBeneficiario($boleto);

			$dataBoleto = [
				'logo' => $logo,
				'dataVencimento' => \Carbon\Carbon::parse($boleto->conta->data_vencimento),
				'valor' => $boleto->conta->valor_integral,
				'numero' => $boleto->numero,
				'numeroDocumento' => $boleto->numero_documento,
				'pagador' => $pagador,
				'beneficiario' => $beneficiario,
				'carteira' => $boleto->carteira,
				'agencia' => $boleto->banco->agencia,
				'convenio' => $boleto->convenio,
				'conta' => $boleto->banco->conta,
				'multa' => $boleto->multa, 
				'juros' => $boleto->juros, 
				'jurosApos' => $boleto->juros_apos,
				'descricaoDemonstrativo' => [],
				'instrucoes' => [],
			];

			$boletoAux = $this->geraBoleto($dataBoleto, $boleto);
			array_push($multiBoletos, $boletoAux);
		}

		$sendArray = [
			'beneficiario' => $beneficiario,
			'carteira' => $boleto->carteira,
			'agencia' => $boleto->banco->agencia,
			'convenio' => $boleto->convenio,
			'`' => '1',
			'conta' => $boleto->banco->conta
		];

		// if($boletos[0]->banco->banco == 'Bradesco'){
		// 	 $sendArray['idremessa'] = $boleto->banco->numero_remessa;
		// }

		if($boletos[0]->banco->banco == 'Bradesco' || $boletos[0]->banco->banco == 'Sicredi' || $boletos[0]->banco->banco == 'Cresol'){
			$sendArray['idremessa'] = $boletos[0]->banco->numero_remessa;
		}

		if($boletos[0]->banco->banco == 'Caixa Econônica Federal'){
			$sendArray['idremessa'] = $boletos[0]->banco->numero_remessa;
			$sendArray['codigoCliente'] = $boletos[0]->codigo_cliente;
		}

		if($boletos[0]->banco->banco == 'Sicoob'){
			 $sendArray['idremessa'] = $boletos[0]->banco->numero_remessa;
		}

		if ($boletos[0]->banco->banco == 'Santander') {
			$pagador = $this->getPagador($boletos[0]->conta);
		
			if (!$pagador || empty($pagador->getDocumento())) {
				throw new \Exception("O campo 'codigoCliente' (CPF/CNPJ do pagador) é obrigatório para a remessa do banco Santander.");
			}
		
			$sendArray['codigoCliente'] = $pagador->getDocumento(); // CPF/CNPJ do pagador
		}
		

		$send = $this->setTipoRemessa($sendArray, $boletos[0]);
		$send->addBoletos($multiBoletos);
		$send->gerar();

		if(!is_dir(public_path('remessas'))){
			mkdir(public_path('remessas'), 0777, true);
		}

		try{
			$nameFile = Str::random(32);

			$result = Remessa::create([
				'nome_arquivo' => $nameFile,
				'empresa_id' => $this->empresa->id
			]);

			foreach($boletos as $boleto){
				RemessaBoleto::create(
					[
						'remessa_id' => $result->id,
						'boleto_id' => $boleto->id,
					]
				);
			}

			// **Incrementa o número da remessa para a próxima operação**
			$boletos[0]->banco->increment('numero_remessa');

			$send->save(public_path('remessas'). "/$nameFile.txt");
			$send->download("$nameFile.txt");
		}catch(\Exception $e){
			echo $e->getMessage();
		}

	}

}