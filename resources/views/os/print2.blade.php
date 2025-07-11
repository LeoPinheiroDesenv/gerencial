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
		.color-main{
			color: #536dfe;
			font-weight: bold;
		}
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
					<center><label class="titulo">ORDEM DE SERVIÇO <strong style="color: #536dfe">{{ $ordem->numero_sequencial }}</strong></label></center>
				</td>

				<td class="" style="width: 200px;">
					<center><label style="font-size: 12px">{{ date('d/m/Y H:i') }}</label></center>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td class="b-top" style="width: 100px;">
					Cliente:
				</td>
				<td class="b-top" style="width: 600px;">
					<label class="color-main">{{ $ordem->cliente->razao_social }}</label>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td class="b-top" style="width: 100px;">
					CPF/CNPJ:
				</td>
				<td class="b-top" style="width: 150px;">
					<label class="color-main">{{ $ordem->cliente->cpf_cnpj }}</label>
				</td>

				<td class="b-top" style="width: 84px;">
					Telefone:
				</td>
				<td class="b-top" style="width: 100px;">
					<label class="color-main">{{ $ordem->cliente->telefone }}</label>
				</td>
				<td class="b-top" style="width: 100px;">
					Data emissão:
				</td>
				<td class="b-top" style="width: 150px;">
					<label class="color-main">{{ __date($ordem->created_at) }}</label>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td class="b-top" style="width: 100px;">
					Endereço:
				</td>
				<td class="b-top" style="width: 600px;">
					<label class="color-main">
						{{ $ordem->rua_servico }}, {{ $ordem->numero_servico }} - {{ $ordem->bairro_servico }}
						{{ $ordem->cidade ? ' - ' . $ordem->cidade->info : '' }} {{ $ordem->complemento_servico }} {{ $ordem->cep_servico }}

					</label>
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td class="b-top" style="width: 100px;">
					Modelo:
				</td>
				<td class="b-top" style="width: 600px;">
					<label style="font-weight: bold">{{ $ordem->modelo }}</label>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td class="b-top" style="width: 100px;">
					Filtro:
				</td>
				<td class="b-top" style="width: 220px;">
					<label style="font-weight: bold">{{ $ordem->filtro }}</label>
				</td>
				<td class="b-top" style="width: 170px;">
					Registro para cascata:
				</td>
				<td class="b-top" style="width: 200px;">
					<label style="font-weight: bold">{{ $ordem->registro_cascata ? 'SIM' : 'NÃO' }}</label>
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td class="b-top" style="width: 150px;">
					Entrada de àgua:
				</td>
				<td class="b-top" style="width: 100px;">
					<label style="font-weight: bold">{{ $ordem->entrada_agua ? 'SIM' : 'NÃO' }}</label>
				</td>
				<td class="b-top" style="width: 150px;">
					Potência do motor:
				</td>
				<td class="b-top" style="width: 82px;">
					<label style="font-weight: bold">{{ $ordem->potencia_motor }}</label>
				</td>
				<td class="b-top" style="width: 120px;">
					Ligar motor para:
				</td>
				<td class="b-top" style="width: 80px;">
					<label style="font-weight: bold">{{ $ordem->ligar_motor_para }}</label>
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td class="b-top" style="width: 110px;">
					Vendedor:
				</td>
				<td class="b-top" style="width: 170px;">
					<label style="font-weight: bold">{{ $ordem->vendedor ? $ordem->vendedor->nome : '' }}</label>
				</td>
				<td class="b-top" style="width: 100px;">
					Data início:
				</td>
				<td class="b-top" style="width: 100px;">
					<label style="font-weight: bold">{{ $ordem->data_inicio ? \Carbon\Carbon::parse($ordem->data_inicio)->format('d/m/Y') : '' }}</label>
				</td>
				<td class="b-top" style="width: 100px;">
					Data entrega:
				</td>
				<td class="b-top" style="width: 102px;">
					<label style="font-weight: bold">{{ $ordem->data_inicio ? \Carbon\Carbon::parse($ordem->data_entrega)->format('d/m/Y') : '' }}</label>
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td class="b-top" style="width: 200px;">
					Outros registros da CM:
				</td>
				<td class="b-top" style="width: 500px;">
					<label style="font-weight: bold">{{ $ordem->outros_servicos_cm }}</label>
				</td>
			</tr>
		</table>

		<table style="margin-top: 10px;">
			<tr>
				<td style="width: 110px;">
					Observação:
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td style="width: 700px;">
					<div style="border: 1px solid #000; height: 200px; border-radius: 3px;">
						{{ $ordem->observacao }}
					</div>
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td class="b-bottom" style="width: 140px;">
					NÃO ESQUECER:
				</td>
				<td class="b-bottom" style="width: 560px;">
					<label style="font-weight: bold">{{ $ordem->nao_esquecer }}</label>
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td style="width: 80px;">
					Areia:
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px">Cobertura:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px">Capa bolha:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>

			<tr>
				<td style="width: 80px;">
					Cola:
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px; font-size: 13px">Cabo telescópio:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px; font-size: 11px">Cotovelo de 90º 50 mm:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>

			<tr>
				<td style="width: 80px;">
					Lixa:
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px; font-size: 13px">Peneira cata folhas:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px; font-size: 11px">Cotovelo de 45º 50 mm:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>

			<tr>
				<td style="width: 80px;">
					Silicone:
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px;">Aspirador:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px;">Tê de 50mm:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>

			<tr>
				<td style="width: 80px;">
					Manual operação:
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px; font-size: 13px">Luva imples 50mm:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 130px;">
					<span style="margin-left: 20px; font-size: 11px;">Mangueira flexível:</span>
				</td>
				<td style="width: 110px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td style="width: 180px;">
					<span style="margin-left: 0px; font-size: 12px;">Tubo de PVC de 50mm (1 e 1/2"):</span>
				</td>
				<td style="width: 120px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 180px;">
					<span style="margin-left: 20px; font-size: 12px;">Tubo de PVC de 32mm (1"):</span>
				</td>
				<td style="width: 120px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>
			<tr>
				<td style="width: 180px;">
					<span style="margin-left: 0px; font-size: 12px;">Tubo de PVC de 25mm (3/4"):</span>
				</td>
				<td style="width: 120px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 180px;">
					<span style="margin-left: 20px; font-size: 12px;">Tubo de PVC de 30mm (1/2"):</span>
				</td>
				<td style="width: 120px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>
			<tr>
				<td style="width: 180px;">
					<span style="margin-left: 0px; font-size: 12px;">Suspiros da Hidro:</span>
				</td>
				<td style="width: 120px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>

				<td style="width: 180px;">
					<span style="margin-left: 20px; font-size: 12px;">Cascata:</span>
				</td>
				<td style="width: 120px;">
					<div style="border: 1px solid #000; height: 20px;"></div>
				</td>
			</tr>
		</table>

		<table style="margin-top: 30px">
			<tr>
				<td style="width: 300px;">
					<span style="margin-left: 0px; font-size: 12px;">___________________________________________________</span>
					<br>
					<span><center>Assinatura</center></span>
				</td>
				<td style="width: 50px;">
				</td>
				<td style="width: 300px;">
					<span style="margin-left: 0px; font-size: 12px;">___________________________________________________</span>
					<br>
					<span><center>Recebido por</center></span>
				</td>
			</tr>
		</table>


	</div>


</body>
</html>