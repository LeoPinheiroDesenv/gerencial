@foreach($empresas as $item)

<tr>
	<td>
		<input checked class="checked" type="checkbox" name="empresa_check[]" value="{{ $item->id }}">
	</td>
	<td>{{ $item->id }}</td>
	<td>{{ $item->nome }}</td>
	<td>{{ $item->nome_fantasia }}</td>
	<td>{{ $item->cnpj }}</td>
	<td>{{ $item->planoEmpresa ? $item->planoEmpresa->plano->nome : '--' }}</td>
</tr>

@endforeach
@foreach($data as $item)
@if(!in_array($item->id, $empresaSelecionadas))
<tr>
	<td>
		<input class="checked" type="checkbox" name="empresa_check[]" value="{{ $item->id }}">
	</td>
	<td>{{ $item->id }}</td>
	<td>{{ $item->nome }}</td>
	<td>{{ $item->nome_fantasia }}</td>
	<td>{{ $item->cnpj }}</td>
	<td>{{ $item->planoEmpresa ? $item->planoEmpresa->plano->nome : '--' }}</td>
</tr>
@endif
@endforeach