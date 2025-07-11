<!DOCTYPE html>
<html>
<head>
	<title></title>
	<!--  -->

	<style type="text/css">

		.content{
			margin-top: -30px;
		}

	</style>

</head>
<body>
	<div class="content">
		@foreach($data as $item)
		<div style="width: 700px; height: 250px; border: 1px solid #000; margin-top: 10px">
			<table style="margin: 10px">
				<tr>
					<td style="width: 80px; height: 30px; border: 1px solid #000; text-align: right;">
						<span style="margin-right: 10px">{{ $item->numero_sequencial }}</span>
					</td>

					<td style="width: 440px; border: 1px solid #000; text-align: center;">
						<span style="font-size: 12px">
							@foreach($item->produtos as $p)
							{{ $p->produto->nome }} @if(!$loop->last), @endif
							@endforeach
						</span>
					</td>
					<td style="width: 150px; border: 1px solid #000; text-align: center;">
						<span>
							{{ $item->vendedor ? $item->vendedor->nome : '' }}
						</span>
					</td>
				</tr>
			</table>
			<table style="margin-left: 10px; margin-top: -10px;">
				<tr>
					<td style="width: 525px; height: 30px; border: 1px solid #000; text-align: left;">
						<span style="margin-right: 10px">{{ $item->cliente->razao_social }}</span>
					</td>

					<td style="width: 150px; border: 1px solid #000; text-align: center;">
						<span>
							{{ __date($item->created_at) }}
						</span>
					</td>
					
				</tr>
			</table>

			<table style="margin-left: 10px; margin-top: 0px;">
				<tr>
					<td style="width: 680px; height: 30px; border: 1px solid #000; text-align: left;">
						<span style="margin-right: 10px">
							{{ $item->rua_servico }}, {{ $item->numero_servico }} - {{ $item->bairro_servico }}
							{{ $item->cidade ? ' - ' . $item->cidade->info : '' }} {{ $item->complemento_servico }} {{ $item->cep_servico }}
						</span>
					</td>
				</tr>
			</table>

			<table style="margin-left: 10px; margin-top: 0px;">
				<tr>
					<td style="width: 222px; height: 30px; border: 1px solid #000; text-align: left;">
						<span style="margin-right: 10px">
							Data de início: <strong> {{ __date($item->data_inicio, 0) }}</strong>
						</span>
					</td>

					<td style="width: 222px; height: 30px; border: 1px solid #000; text-align: left;">
						<span style="margin-right: 10px">
							Data de entrega: <strong> {{ __date($item->data_entrega, 0) }}</strong>
						</span>
					</td>

					<td style="width: 223px; height: 30px; border: 1px solid #000; text-align: left;">
						<span style="margin-right: 10px">
							Valor: <strong> {{ moeda($item->total_os()) }}</strong>
						</span>
					</td>
					
				</tr>
			</table>

			<table style="margin-left: 10px; margin-top: 0px;">
				<tr>
					<td style="width: 680px; height: 70px; border: 1px solid #000; text-align: left;">
						
					</td>
				</tr>
			</table>
		</div>
		@endforeach
	</div>
</body>
</html>