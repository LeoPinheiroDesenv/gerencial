<!DOCTYPE html>
<html>
<head>
	<title></title>
	<!--  -->

	<style type="text/css">

		
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

		*{
			font-family: "Lucida Console", "Courier New", monospace;
			font-size: 12px;
			margin: 1.2;
			padding: 1.2;
		}
		.sub-titulo{
			font-size: 20px;
			font-weight: bold;
		}
		.bold{
			font-weight: bold;
		}
	</style>

</head>
<body>
	<div class="content">
		<table class="div-first" style="border: 1px solid #555; border-radius: 10px;">
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

				<td class="" style="width: 450px;">
					<h5 style="margin-top: -1px">{{ $config->razao_social }}</h5>
					<label style="margin-top: -10px">{{ $config->nome_fantasia }}</label><br>
					<label style="margin-top: -10px">
						{{ $config->logradouro }}, {{ $config->numero }} - {{ $config->bairro }} -  {{ $config->cep }}
					</label><br>
					<label style="margin-top: -10px">
						{{ $config->municipio }} ({{ $config->UF }})
					</label><br>
					<label style="margin-top: -10px">
						{{ $config->email }}
					</label>

				</td>

				<td class="" style="width: 150px;">
					<label>{{ __setMask($config->cnpj) }}</label><br>
					<label>{{ $config->fone }}</label><br>
				</td>
			</tr>
		</table>

		<table class="div-first" style="border: 1px solid #555; border-radius: 10px;">
			<tr>
				<td class="" style="width: 760px;">
					<center>
						<label class="sub-titulo">
							PEDIDO DE VENDA Nº: {{ $venda->numero_sequencial }} - EMISSÃO {{ date('d/m/Y') }}
							@if($venda->data_entrega)
							{{ \Carbon\Carbon::parse($venda->data_entrega)->format('d/m/Y') }}
							@endif
						</label>
					</center>
				</td>
			</tr>
		</table>

		<table class="div-first" style="border: 1px solid #555; border-radius: 10px;">
			<tr>
				<td colspan="2" style="font-size: 15px; text-align: center;">
					CLIENTE
				</td>
			</tr>

			<tr>
				<td class="bold" style="width: 555px;">
					{{ $venda->cliente->razao_social }}
				</td>
				<td class="bold" style="width: 200px;">
					{{ $venda->cliente->cpf_cnpj }}
				</td>
			</tr>
			<tr>
				<td class="bold" style="width: 555px;">
					{{ $venda->cliente->rua }}, {{ $venda->cliente->numero }} {{ $venda->cliente->bairro }} - {{ $venda->cliente->cep }}
				</td>
				<td class="bold" style="width: 200px;">
					{{ $venda->cliente->cidade->info }}
				</td>
			</tr>
			@if($venda->cliente->complemento)
			<tr>
				<td class="bold" style="width: 555px;">
					{{ $venda->cliente->complemento }}
				</td>
			</tr>
			@endif
			<tr>
				<td class="bold" style="width: 555px;">
					{{ $venda->cliente->email }}
				</td>
				@if($venda->cliente->telefone || $venda->cliente->celular)
				<td class="bold" style="width: 200px;">
					{{ $venda->cliente->telefone }} 
					@if($venda->cliente->celular)
					/{{ $venda->cliente->celular }}
					@endif
				</td>
				@endif
			</tr>

		</table>

		<table class="div-first" style="border: 1px solid #555; border-radius: 10px;">
			<tr>
				<td colspan="6" style="font-size: 15px; text-align: center;">
					PRODUTOS
				</td>
			</tr>
			<tr>
				<th style="width: 50px;">CÓDIGO</th>
				<th style="width: 325px; text-align: left;">DESCRIÇÃO</th>
				<th style="width: 90px; text-align: left;">UNID.</th>
				<th style="width: 90px; text-align: left;">QTD.</th>
				<th style="width: 90px; text-align: left;">VL. UNIT.</th>
				<th style="width: 90px; text-align: left;">SUBTOTAL</th>
			</tr>
			@php
			$somaItens = 0;
			$somaTotalItens = 0;
			$tipoDimensao = false;
			$tipoReceita = false;
			@endphp
			@foreach($venda->itens as $i)
			<tr>
				<td class="bold">{{$i->produto->id}} {{$i->produto->referencia != "" ? "/ " . $i->produto->referencia : "" }}</td>
				<td class="bold">
					{{$i->produto->nome}}
					{{$i->produto->grade ? " (" . $i->produto->str_grade . ")" : ""}}
					@if($i->produto->lote != "")
					| Lote: {{$i->produto->lote}}, 
					Vencimento: {{$i->produto->vencimento}}
					@endif
				</td>
				<td class="bold">{{$i->produto->unidade_venda}}</td>
				<td class="bold">{{number_format($i->quantidade, $casasDecimaisQtd, ',', '.')}}</td>
				<td class="bold">{{number_format($i->valor, $casasDecimais, ',', '.')}}</td>
				<td class="bold">{{number_format($i->quantidade * $i->quantidade_dimensao * $i->valor, $casasDecimais, ',', '.')}}</td>
			</tr>

			@php
			$somaItens += $i->quantidade;
			$somaTotalItens += $i->quantidade * $i->valor * $i->quantidade_dimensao;
			if($i->altura > 0 || $i->esquerda > 0){
				$tipoDimensao = true;
			}

			if($i->produto->receita){
				$tipoReceita = true;
			}
			@endphp

			@endforeach
		</table>

		<table class="div-first" style="border: 1px solid #555; border-radius: 10px;">

			<tr>
				<td class="" style="width: 270px;">
					Forma de pagamento: <strong> 
						{{$venda->forma_pagamento == 'a_vista' ? 'À vista' : ($venda->forma_pagamento == 'personalizado' ? 'Parcelado' : $venda->forma_pagamento) }}
						@if($venda->getFormaPagamento($venda->empresa_id) != null)
						<span style="color: #8950FC">{{ $venda->getFormaPagamento($venda->empresa_id)->infos }}</span>
						@endif
					</strong>
				</td>
				<td class="" style="width: 240px;">
					Vendedor: 
					<strong>
						@if($venda->vendedor_setado && $venda->vendedor_setado->funcionario)
						{{ $venda->vendedor_setado->funcionario->nome }}
						@else
						{{ $venda->usuario->nome }}
						@endif
					</strong>
				</td>
				<td class="" style="width: 240px;">
					Frete por conta: <strong>
						@if($venda->frete)
						@if($venda->frete->tipo == 0)
						Emitente
						@elseif($venda->frete->tipo == 1)
						Destinatário
						@elseif($venda->frete->tipo == 2)
						Terceiros
						@else
						Outros
						@endif
						@else
						sem frete
						@endif
					</strong>
				</td>
			</tr>
			<tr>
				<td>
					Desconto (-):
					<strong> 
						{{number_format($venda->desconto, 2, ',', '.')}}
					</strong>
				</td>

				<td>
					Acrescimo (+):
					<strong> 
						{{number_format($venda->acrescimo, 2, ',', '.')}}
					</strong>
				</td>

				<td>
					Frete (+):
					<strong> 
						@if($venda->frete)
						{{number_format($venda->frete->valor, 2, ',', '.')}}
						@else
						0,00
						@endif
					</strong>
				</td>
			</tr>
			<tr>
				<td>
					Data da venda: <strong>{{\Carbon\Carbon::parse($venda->created_at)->format('d/m/Y H:i')}}</strong>
				</td>

				<td>
					@if($venda->data_entrega != null)
					Data da entrega: <strong>{{\Carbon\Carbon::parse($venda->data_entrega)->format('d/m/Y')}}</strong>
					@endif
				</td>

				<td style="font-size: 16px;">
					VALOR LIQUIDO: <strong style="color: red; font-size: 16px; margin-left: -20px;"> 
						R${{ number_format($venda->valor_total - $venda->desconto + $venda->acrescimo, $casasDecimais, ',', '.') }}
					</strong>
				</td>
			</tr>
			@if($venda->observacao != "" || $config->campo_obs_pedido != "")
			<tr>
				<td colspan="3">
					Observação:<strong>{{$config->campo_obs_pedido}}
						{{$venda->observacao}}
					</strong>
				</td>
			</tr>
			@endif
			
		</table>

		@if($venda->duplicatas()->exists())
		<table class="div-first" style="border: 1px solid #555; border-radius: 10px;">
			<tr>
				<td colspan="2" style="font-weight: bold">
					Fatura
				</td>
			</tr>

			<tr>
				<td class="b-bottom" style="width: 150px;">
					Vencimento
				</td>
				<td class="b-bottom" style="width: 150px;">
					Valor
				</td>
				<td class="b-bottom" style="width: 150px;">
					Valor
				</td>
			</tr>
			@foreach($venda->duplicatas as $key => $d)
			<tr>
				<td>
					<strong>{{ \Carbon\Carbon::parse($d->data_vencimento)->format('d/m/Y')}}</strong>
				</td>
				<td>
					<strong>{{number_format($d->valor_integral, $casasDecimais, ',', '.')}}</strong>
				</td>
				<td>
					<strong>{{ $d->tipo_pagamento }}</strong>
				</td>
			</tr>
			@endforeach
		</table>

		@endif

		<br><br><br>
		<table>
			<tr>
				<td class="" style="width: 450px;">
					<strong>
						________________________________________
					</strong><br>
					<span style="font-size: 11px;">{{$config->razao_social}}</span>

				</td>

				<td class="" style="width: 370px;">
					<strong>
						________________________________________
					</strong><br>
					<span style="font-size: 11px;">{{$venda->cliente->razao_social}}</span>
				</td>
			</tr>
		</table>

	</div>
</body>