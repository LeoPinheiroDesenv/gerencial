<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaReceber extends Model
{
	protected $fillable = [
		'venda_id', 'date_register', 'data_vencimento', 'data_recebimento', 'valor_integral', 
		'valor_recebido', 'referencia', 'categoria_id', 'status', 'empresa_id', 
		'cliente_id', 'juros', 'multa', 'venda_caixa_id', 'observacao', 'tipo_pagamento',
		'filial_id', 'entrada', 'numero_nota_fiscal', 'arquivo'
	];

	public function filial(){
        return $this->belongsTo(Filial::class, 'filial_id');
    }

	public function venda(){
		return $this->belongsTo(Venda::class, 'venda_id');
	}
	
	public function vendaCaixa(){
		return $this->belongsTo(VendaCaixa::class, 'venda_caixa_id');
	}

	public function cliente(){
		return $this->belongsTo(Cliente::class, 'cliente_id');
	}

	public function categoria(){
		return $this->belongsTo(CategoriaConta::class, 'categoria_id');
	}

	public function boleto(){
		return $this->hasOne('App\Models\Boleto', 'conta_id', 'id');
	}

	public function diasAtraso(){
		$d = date('Y-m-d');
		$d2 = $this->data_vencimento;
		$dif = strtotime($d2) - strtotime($d);
		$dias = floor($dif / (60 * 60 * 24));
		if($dias == 0){
			return "conta vence hoje";
		}

		if($dias > 0){
			return "$dias dia(s) para o vencimento";
		}else{
			return "conta vencida à " . ($dias*-1) . " dia(s)";
		}
	} 

	public static function filtroData($dataInicial, $dataFinal, $status){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = ContaReceber::
		orderBy('conta_recebers.data_vencimento', 'asc')
		->where('empresa_id', $empresa_id)
		->whereBetween('conta_recebers.data_vencimento', [$dataInicial, 
			$dataFinal]);

		if($status == 'pago'){
			$c->where('status', true);
		} else if($status == 'pendente'){
			$c->where('status', false);
		}
		return $c->get();
	}
	public static function filtroDataFornecedor($cliente, $dataInicial, $dataFinal, $status){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = ContaReceber::
		select('conta_recebers.*')
		->orderBy('conta_recebers.data_vencimento', 'asc')
		->join('vendas', 'vendas.id' , '=', 'conta_recebers.venda_id')
		->join('clientes', 'clientes.id' , '=', 'vendas.cliente_id')
		->where('clientes.razao_social', 'LIKE', "%$cliente%")
		->where('vendas.empresa_id', $empresa_id)
		->whereBetween('conta_recebers.data_vencimento', [$dataInicial, 
			$dataFinal]);

		if($status == 'pago'){
			$c->where('status', true);
		} else if($status == 'pendente'){
			$c->where('status', false);
		}
		$c1 = $c->get();

		$c = ContaReceber::
		select('conta_recebers.*')
		->orderBy('conta_recebers.data_vencimento', 'asc')
		->join('clientes', 'clientes.id' , '=', 'conta_recebers.cliente_id')
		->where('clientes.razao_social', 'LIKE', "%$cliente%")
		->where('conta_recebers.empresa_id', $empresa_id)
		->whereBetween('conta_recebers.data_vencimento', [$dataInicial, 
			$dataFinal]);

		if($status == 'pago'){
			$c->where('status', true);
		} else if($status == 'pendente'){
			$c->where('status', false);
		}
		$c2 = $c->get();

		$temp = [];
		foreach($c1 as $c){
			array_push($temp, $c);
		}
		foreach($c2 as $c){
			array_push($temp, $c);
		}
		return $temp;
	}

	public static function filtroFornecedor($cliente, $status){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = ContaReceber::
		select('conta_recebers.*')
		->orderBy('conta_recebers.data_vencimento', 'asc')
		->join('vendas', 'vendas.id' , '=', 'conta_recebers.venda_id')
		->join('clientes', 'clientes.id' , '=', 'vendas.cliente_id')
		->where('conta_recebers.empresa_id', $empresa_id)
		->where('razao_social', 'LIKE', "%$cliente%");

		if($status == 'pago'){
			$c->where('status', true);
		} else if($status == 'pendente'){
			$c->where('status', false);
		}
		
		$c1 = $c->get();

		$c = ContaReceber::
		select('conta_recebers.*')
		->orderBy('conta_recebers.data_vencimento', 'asc')
		->join('clientes', 'clientes.id' , '=', 'conta_recebers.cliente_id')
		->where('conta_recebers.empresa_id', $empresa_id)
		->where('razao_social', 'LIKE', "%$cliente%");

		if($status == 'pago'){
			$c->where('status', true);
		} else if($status == 'pendente'){
			$c->where('status', false);
		}
		
		$c2 = $c->get();
		$temp = [];
		foreach($c1 as $c){
			array_push($temp, $c);
		}
		foreach($c2 as $c){
			array_push($temp, $c);
		}
		return $temp;
	}

	public static function filtroStatus($status){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];

		$c = ContaReceber::
		where('empresa_id', $empresa_id)
		->orderBy('conta_recebers.data_vencimento', 'asc');
		if($status == 'pago'){
			$c->where('status', true);
		} else if($status == 'pendente'){
			$c->where('status', false);
		}
		
		return $c->get();
	}

	public function getCliente(){

		if($this->venda_id != null){
			return $this->venda->cliente;
		}else if($this->cliente_id != null){
			return $this->cliente;
		}
	}

	public static function tiposPagamento(){
		return [
			'Dinheiro',
			'Cheque',
			'Boleto',
			'Cartão de Crédito',
			'Cartão de Débito',
			'Vale Alimentação',
			'Vale Refeição',
			'Vale Presente',
			'Vale Combustível',
			'Depósito Bancário',
			'Pagamento Instantâneo (PIX)',
			'Outros'
		];
	}

	public function recibos()
{
    return $this->belongsToMany(
        \App\Models\ReciboReceber::class,
        'recibo_conta_rec_conta_recebers', // nome da tabela pivot
        'conta_receber_id',               // coluna da tabela pivot que referencia a ContaReceber
        'recibo_receber_id'               // coluna da tabela pivot que referencia o ReciboReceber
    );
}
}
