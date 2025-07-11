<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
	protected $fillable = [
		'fornecedor_id', 'usuario_id', 'nf', 'desconto', 'valor', 'observacao', 'xml_path',
		'chave', 'estado', 'numero_emissao', 'empresa_id', 'sequencia_cce', 'valor_frete', 'placa', 
		'tipo', 'uf', 'numeracaoVolumes', 'peso_liquido', 'peso_bruto', 'especie', 'qtdVolumes', 
		'transportadora_id', 'data_emissao', 'filial_id', 'lote', 'valor_ipi', 'outras_despesas', 
		'substituicao_tributaria', 'valor_seguro', 'acrescimo', 'xml_importado', 'categoria_conta_id', 
		'importado_manifesto'
	];

	public static function lastNumero($empresa_id){
		$numero_sequencial = 0;
		$last = Compra::where('empresa_id', $empresa_id)
		->orderBy('id', 'desc')
		->first();

		$numero_sequencial = $last != null ? ($last->numero_sequencial + 1) : 1;
		return $numero_sequencial;
	}

	public function filial(){
		return $this->belongsTo(Filial::class, 'filial_id');
	}

	public function fornecedor(){
		return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
	}

	public function usuario(){
		return $this->belongsTo(Usuario::class, 'usuario_id');
	}

	public function transportadora(){
		return $this->belongsTo(Transportadora::class, 'transportadora_id');
	}

	public function itens(){
		return $this->hasMany('App\Models\ItemCompra', 'compra_id', 'id');
	}

	public function chaves(){
		return $this->hasMany('App\Models\CompraReferencia', 'compra_id', 'id');
	}

	public function fatura(){
		return $this->hasMany('App\Models\ContaPagar', 'compra_id', 'id');
	}

	public function somaItems(){
		if(count($this->itens) > 0){
			$total = 0;
			foreach($this->itens as $t){
				$total += $t->quantidade * $t->valor_unitario;
			}
			return $total;
		}else{
			return 0;
		}
	}

	public static function filtroData($dataInicial, $dataFinal){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = Compra::
		select('compras.*')
		->whereBetween('compras.crated_at', [$dataInicial, 
			$dataFinal])
		->where('compras.empresa_id', $empresa_id);
		return $c->get();
	}
	
	public static function filtroDataFornecedor($fornecedor, $dataInicial, $dataFinal){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = Compra::
		select('compras.*')
		->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id')
		->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%")
		->whereBetween('compras.created_at', [$dataInicial, 
			$dataFinal])
		->where('compras.empresa_id', $empresa_id);

		return $c->get();
	}

	public static function filtroFornecedor($fornecedor){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = Compra::
		select('compras.*')
		->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id')
		->where('razao_social', 'LIKE', "%$fornecedor%")
		->where('compras.empresa_id', $empresa_id);

		return $c->get();
	}


	public static function pesquisaProduto($pesquisa){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		return Compra::
		select('compras.*')
		->join('item_compras', 'compras.id' , '=', 'item_compras.compra_id')
		->join('produtos', 'produtos.id' , '=', 'item_compras.produto_id')
		->where('produtos.nome', 'LIKE', "%$pesquisa%")
		->where('compras.empresa_id', $empresa_id)
		->get();
	}

	public static function tiposPagamento(){
		return [
			'01' => 'Dinheiro',
			'02' => 'Cheque',
			'03' => 'Cartão de Crédito',
			'04' => 'Cartão de Débito',
			'05' => 'Crédito Loja',
			'10' => 'Vale Alimentação',
			'11' => 'Vale Refeição',
			'12' => 'Vale Presente',
			'13' => 'Vale Combustível',
			'14' => 'Duplicata Mercantil',
			'15' => 'Boleto Bancário',
			'16' => 'Depósito Bancário',
			'17' => 'Pagamento Instantâneo (PIX) - Dinâmico',
			'18' => 'Transferência bancária, Carteira Digital',
			'19' => 'Programa de fidelidade, Cashback, Crédito Virtual',
			'20' => 'Pagamento Instantâneo (PIX) – Estático',
			'21' => 'Crédito em Loja',
			'22' => 'Pagamento Eletrônico não Informado - falha de hardware do sistema emissor',
			'90' => 'Sem pagamento',
			// '99' => 'Outros',
		];
	}

	public function verificaValidade(){
		foreach($this->itens as $i){
			if($i->validade == null && $i->produto->alerta_vencimento > 0){
				return 1;
			}
		}
		return 0;
	}

	public static function getTiposViaTransp(){
		return [
			'1' => 'Marítima',
			'2' => 'Fluvial',
			'3' => 'Lacustre',
			'4' => 'Aérea',
			'5' => 'Postal',
			'6' => 'Ferroviária',
			'7' => 'Rodoviária',
			'8' => 'Conduto/Rede transmissão',
			'9' => 'Meios próprios',
			'10' => 'Entrada/Saída ficta',
			'11' => 'Courier',
			'12' => 'Em mãos',
			'13' => 'Por reboque'
		];
	}

	public static function getTiposIntermedio(){
		return [
			'1' => 'Importação por conta própria',
			'2' => 'Importação por conta e ordem',
			'3' => 'Importação por encomenda'
		];
	}

}
