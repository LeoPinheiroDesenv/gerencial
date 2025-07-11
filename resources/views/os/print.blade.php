<!DOCTYPE html>
<html>
<head>
	<title></title>
	<!--  -->

	<style type="text/css">

		.content{
			margin-top: -30px;
		}
		.titulo{
			font-size: 20px;
			margin-bottom: 0px;
			font-weight: bold;
		}

		.b-top{
			border-top: 1px solid #000; 
		}
		.b-bottom{
			border-bottom: 1px solid #000; 
		}
		.page_break { page-break-before: always; }

	</style>

</head>
<body>
	<div class="content">
		<table>
			<tr>

				@if($config->logo != "")
				<td class="" style="width: 150px;">
					<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logos/').$config->logo))}}" width="100px;">
				</td>
				@else
				<td class="" style="width: 150px;">
					<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('imgs/Owner.png')))}}" width="100px;">
				</td>
				@endif

				<td class="" style="width: 400px;">
					<center><label class="titulo">ORDEM DE SERVIÇO</label></center>
				</td>
			</tr>
		</table>

	</div>
	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Dados da empresa</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 480px;">
				Razão social: <strong>{{$config->razao_social}}</strong>
			</td>
			<td class="b-top" style="width: 220px;">
				Documento: <strong>{{ \App\Models\ConfigNota::formataCnpj($config->cnpj) }}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 700px;">
				Endereço: <strong>{{$config->logradouro}}, {{$config->numero}} - {{$config->bairro}} - {{$config->municipio}} ({{$config->UF}})</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 300px;">
				Complemento: <strong>{{$config->complemento}}</strong>
			</td>
			<td class="b-top b-bottom" style="width: 200px;">
				CEP: <strong>{{$config->cep}}</strong>
			</td>
			<td class="b-top b-bottom" style="width: 200px;">
				Telefone: <strong>{{$config->fone}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-bottom" style="width: 700px;">
				Email: <strong>{{$config->email}}</strong>
			</td>
			
		</tr>
	</table>
	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Dados do cliente</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 450px;">
				Nome: <strong>{{$ordem->cliente->razao_social}}</strong>
			</td>
			<td class="b-top" style="width: 247px;">
				CPF/CNPJ: <strong>{{$ordem->cliente->cpf_cnpj}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 500px;">
				Endereço: <strong>{{$ordem->cliente->rua}}, {{$ordem->cliente->numero}} - {{$ordem->cliente->bairro}} - {{$ordem->cliente->cidade->nome}} ({{$ordem->cliente->cidade->uf}})</strong>
			</td>

			<td class="b-top" style="width: 200px;">
				CEP: <strong>{{$ordem->cliente->cep}}</strong>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top" style="width: 300px;">
				Complemento: <strong>{{$ordem->cliente->complemento }}</strong>
			</td>

			<td class="b-top" style="width: 200px;">
				Telefone: <strong>{{$ordem->cliente->telefone}}</strong>
			</td>
			<td class="b-top" style="width: 200px;">
				Celular: <strong>{{$ordem->cliente->celular}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 700px;">
				Email: <strong>{{$ordem->cliente->email}}</strong>
			</td>
			
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top" style="width: 350px;">
				Nº Doc: <strong>{{$ordem->numero_sequencial}}</strong>
			</td>
			<td class="b-top" style="width: 347px;">

			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 700px; height: 50px;">
				<strong>Serviços:</strong>
			</td>
		</tr>
	</table>	

	<table>
		<thead>
			<tr>
				
				<td class="" style="width: 350px;">
					Descrição
				</td>
				<td class="" style="width: 100px;">
					Qtd.
				</td>
				<td class="" style="width: 100px;">
					Vl Unit.
				</td>
				<td class="" style="width: 100px;">
					Subtotal
				</td>
			</tr>
		</thead>

		
		<tbody>

			@foreach($ordem->servicos as $item)
			<tr>
				<td>{{ $item->servico->nome }}</td>
				<td>{{ moeda($item->quantidade) }}</td>
				<td>{{ moeda($item->valor_unitario) }}</td>
				<td>{{ moeda($item->sub_total) }}</td>
			</tr>
			
			@endforeach
		</tbody>
	</table>
	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 350px;">
				<center><strong>Quantidade total: {{ moeda($ordem->servicos->sum('quantidade')) }}</strong></center>
			</td>

			<td class="b-top b-bottom" style="width: 350px;">
				<center><strong>Valor total de serviço: R$ 
					{{ moeda($ordem->servicos->sum('sub_total')) }}
				</strong></center>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-bottom" style="width: 700px; height: 50px;">
				<strong>Produtos:</strong>
			</td>
		</tr>
	</table>	

	<table>
		<thead>
			<tr>
				
				<td class="" style="width: 350px;">
					Descrição
				</td>
				<td class="" style="width: 100px;">
					Qtd.
				</td>
				<td class="" style="width: 100px;">
					Vl Unit.
				</td>
				<td class="" style="width: 100px;">
					Subtotal
				</td>
			</tr>
		</thead>

		<tbody>

			@foreach($ordem->produtos as $item)
			<tr>
				<td>{{ $item->produto->nome }}</td>
				<td>{{ moeda($item->quantidade) }}</td>
				<td>{{ moeda($item->valor_unitario) }}</td>
				<td>{{ moeda($item->sub_total) }}</td>
			</tr>
			
			@endforeach
		</tbody>
	</table>
	<br>

	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 350px;">
				<center><strong>Quantidade total: {{ moeda($ordem->produtos->sum('quantidade')) }}</strong></center>
			</td>

			<td class="b-top b-bottom" style="width: 350px;">
				<center><strong>Valor total de produto: R$ {{ moeda($ordem->produtos->sum('sub_total')) }}<
				</strong></center>
			</td>
		</tr>
	</table>

	
	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				Forma de pagamento: <strong>{{ $ordem->forma_pagamento }}</strong>
			</td>
		</tr>
	</table>
	
</table>
<table>
	<tr>
		<td class="" style="width: 250px;">
			Data: <strong>{{\Carbon\Carbon::parse($ordem->created_at)->format('d/m/Y H:i')}}</strong>
		</td>


	</tr>
</table>

<table>
    <tr>
        <td style="width: 200px;">
            Desconto (-):
            <strong> R$ {{ moeda($ordem->desconto) }} </strong>
        </td>
        <td style="width: 200px;">
            Acréscimo (+):
            <strong> R$ {{ moeda($ordem->acrescimo) }} </strong>
        </td>
        <td style="width: 250px; white-space: nowrap;">
            <strong>Total Produtos/Serviços: R$ {{ moeda($ordem->total_os()) }} </strong>
        </td>
    </tr>
</table>

@if($ordem->descricao != '')
<hr>
<h4 style="margin-left: 5px">Descrição da OS:</h4>
<p style="margin-top: -20px; margin-left: 10px">{{ $ordem->descricao }}</p>
@endif

@if($ordem->observacao != "")
<table>
	<tr>
		<td class="" style="width: 700px;">
			<span>Observação: 
				<strong>
					{{$ordem->observacao}}
				</strong>
			</span>
		</td>
	</tr>
</table>
@endif



<br><br><br>
<table>
	<tr>
		<td class="" style="width: 350px;">
			<strong>
				________________________________________
			</strong><br>
			<span style="font-size: 11px;">{{$config->razao_social}}</span>

		</td>

		<td class="" style="width: 350px;">
			<strong>
				________________________________________
			</strong><br>
			<span style="font-size: 11px;">{{$ordem->cliente->razao_social}}</span>
		</td>
	</tr>
</table>


</body>
</html>