@extends('relatorios.default')
@section('content')

<style type="text/css">
	tfoot td{
		border-top: 1px solid #999;
		font-weight: bold;
	}
</style>
<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th class="text-left">Código</th>
		<th class="text-left">Produto</th>
		<th class="text-left">Un</th>
		<th class="text-left">Prec. Médio</th>
		<th class="text-left">Quantidade</th>
		<th class="text-left">Valor unitário</th>
		<th class="text-left">Total</th>
		<th class="text-left">Total Custo</th>
		<th class="text-left">Lucro</th>
		<th class="text-left">Margem</th>
	</tr>
</thead>

<tbody>

	@foreach($data as $item)
	<tr>
		<td>{{ $item['produto_id'] }}</td>
		<td>{{ $item['produto_nome'] }}</td>
		<td>{{ $item['unidade_venda'] }}</td>
		<td>R$ {{ moeda($item['preco_medio']) }}</td>

		<td>{{ $item['quantidade'] }}</td>
		<td>R$ {{ moeda($item['valor_unitario']) }}</td>
		<td>R$ {{ moeda($item['sub_total']) }}</td>
		<td>R$ {{ moeda($item['total_custo']) }}</td>
		<td>R$ {{ moeda($item['sub_total']-$item['total_custo']) }}</td>
		<td>{{ $item['margem'] }}%</td>

	</tr>

	
	@endforeach
</tbody>

<tfoot>
	<tr>
		<td colspan="6">Soma</td>
		<td>R$ {{ moeda($somaTotal) }}</td>
		<td>R$ {{ moeda($somaTotalCusto) }}</td>
		<td colspan="2">R$ {{ moeda($somaTotal-$somaTotalCusto) }}</td>
	</tr>
</tfoot>

</table>

@endsection
