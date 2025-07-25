@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">

	<div class="card-body">
		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
			<div class="col-lg-12" id="content">
				<!--begin::Portlet-->
				@if(sizeof($usuarios) > 0)
				<form class="row" method="get" id="form-filtro" action="/caixa/filtroUsuario">
					<div class="form-group validated col-lg-3 col-md-2 col-sm-6">
						<label class="col-form-label text-left">Usuário</label>
						<select class="custom-select form-control" id="sel_usuario" name="usuario">
							@foreach($usuarios as $u)
							<option @if($usuario_id == $u->id) selected @endif value="{{$u->id}}">{{$u->nome}}</option>
							@endforeach
						</select>
					</div>
				</form>
				@endif
				@php
				$estado = $abertura == null ? false : true;
				@endphp
				<h2 class="card-title">Estado:
					@if($estado)
					<span class="label label-xl label-inline label-light-success">
						Caixa aberto
						<strong class="ml-1">{{\Carbon\Carbon::parse($abertura->created_at)->format('d/m/Y H:i')}}</strong>
					</span>
					@else
					<span class="label label-xl label-inline label-light-danger">Caixa fechado</span>
					@endif
				</h2>

				<br>

				@if($estado)
				@if(sizeof($caixa) > 0)
				<h2 class="card-title">Total de vendas: <strong class="text-info">{{sizeof($caixa['vendas'])}}</strong></h2>
				@endif

				@if($abertura != null)
				<h2 class="card-title text-success">Valor de abertura: <strong class="">{{number_format($abertura->valor, 2, ',', '.')}}</strong></h2>
				@endif
				@endif

				@php
				$somaDinheiro = 0;
				@endphp
				@if(sizeof($caixa) > 0)
				<div class="row">
					<div class="col-xl-12">
						<h3 class="text-info">Total por tipo de pagamento:</h3>
						<div class="kt-section kt-section--first">
							<div class="kt-section__body">
								<div class="row">

									@foreach($caixa['somaTiposPagamento'] as $key => $tp)
									@if($tp > 0)
									<div class="col-sm-4 col-lg-4 col-md-6">
										<div class="card card-custom gutter-b">
											<div class="card-header">
												<h3 class="card-title">
													{{App\Models\VendaCaixa::getTipoPagamento($key)}}
												</h3>
											</div>

											@php
											if($key == '01') $somaDinheiro = $tp;
											@endphp
											<div class="card-body">
												<h4 class="text-success">R$ {{number_format($tp, 2, ',', '.')}}</h4>
											</div>

										</div>
									</div>
									@endif
									@endforeach

								</div>
							</div>
						</div>
					</div>
				</div>
				@endif


				<div class="row">
					<div class="col-xl-12">

						<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

							<table class="datatable-table" style="max-width: 100%; overflow: scroll">
								<thead class="datatable-head">
									<tr class="datatable-row" style="left: 0px;">
										
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
										<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
										<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Tipo de pagamento</span></th>

										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Estado</span></th>

										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">NFCe/NFe</span></th>

										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Tipo</span></th>

										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
									</tr>
								</thead>

								<tbody class="datatable-body">
									@php
									$soma = 0;
									
									@endphp

									@if(sizeof($caixa) > 0)

									@foreach($caixa['vendas'] as $v)

									<tr class="datatable-row" >
										
										<td class="datatable-cell"><span class="codigo" style="width: 150px;">{{ $v->cliente->razao_social ?? 'NAO IDENTIFCADO' }}</span>
										</td>
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i:s')}}</span>
										</td>
										<td class="datatable-cell">
											<span class="codigo" style="width: 100px;">

												@if($v->tipo_pagamento == '99')

												Outros
												@else
												{{$v->getTipoPagamento($v->tipo_pagamento)}}
												@endif

											</span>
										</td>
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ $v->estado }}</span>
										</td>
										@if($v->tipo == 'PDV')
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ $v->NFcNumero > 0 ? $v->NFcNumero : '--' }}</span>
										</td>
										@else
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ $v->NfNumero > 0 ? $v->NfNumero : '--' }}</span>
										</td>
										@endif

										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ $v->tipo }}</span>
										</td>

										@if(!isset($v->cpf))
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ number_format($v->valor_total-$v->desconto+$v->acrescimo, 2, ',', '.') }}</span>
										</td>
										@else
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ number_format($v->valor_total, 2, ',', '.') }}</span>
										</td>
										@endif

									</tr>
									
									@if($v->estado != 'CANCELADO')
									@php
									if(!isset($v->cpf))
									$soma += $v->valor_total-$v->desconto+$v->acrescimo;
									else
									$soma += $v->valor_total;

									@endphp

									@endif
									
									@endforeach
									@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<br>

				@php
				$somaSuprimento = 0;
				$somaSangria = 0;
				@endphp

				@if($abertura != null)
				<div class="row">
					<div class="col-12 col-xl-6">
						<div class="card card-custom gutter-b bg-light-info">

							<div class="card-body">
								<h2 class="card-title">Suprimentos:</h2>

								@if(sizeof($caixa['suprimentos']) > 0)
								@foreach($caixa['suprimentos'] as $s)
								<h4>Valor: R$ {{number_format($s->valor, 2, ',', '.')}}</h4>
								@php
								$somaSuprimento += $s->valor;
								@endphp
								@endforeach
								@else
								<h4>R$ 0,00</h4>
								@endif
							</div>			
						</div>			
					</div>	

					<div class="col-12 col-xl-6">
						<div class="card card-custom gutter-b bg-light-danger">

							<div class="card-body">
								<h2 class="card-title">Sangrias:</h2>

								@if(sizeof($caixa['sangrias']) > 0)
								@foreach($caixa['sangrias'] as $s)
								<h4>Valor: R$ {{number_format($s->valor, 2, ',', '.')}}</h4>
								@php
								$somaSangria += $s->valor;
								@endphp
								@endforeach
								@else
								<h4>R$ 0,00</h4>
								@endif
							</div>			
						</div>			
					</div>		
				</div>
				@endif

				@if($abertura == null)
				<h2 class="text-danger text-center">NÃO É POSSÍVEL FECHAR SEM NENHUMA VENDA</h2>
				@else

				@if($abertura != null)
				<h2 class="text-warning">Soma de vendas: 
					<strong>{{number_format($soma, 2, ',', '.')}}</strong>
				</h2>

				<h2 class="text-info">Total caixa dinheiro: 

					<strong>
						{{number_format(($somaDinheiro + $somaSuprimento + $abertura->valor) - $somaSangria, 2, ',', '.')}}
					</strong>
				</h2>

				<h2 class="text-success">Total Geral: 
					<strong>
						{{number_format(($soma + $somaSuprimento + $abertura->valor) - $somaSangria, 2, ',', '.')}}
					</strong>
				</h2>
				@endif
				@endif

				@if($abertura != null)
				<div class="row">
					<div class="col-12">
						<form method="post" id="form-caixa" action="/frenteCaixa/fechar">
							@csrf
							<input type="hidden" name="valor_dinheiro_caixa" id="valor_dinheiro_caixa">
							<input type="hidden" name="abertura_id" value="{{$abertura != null ? $abertura->id : 0}}">
							<input type="hidden" name="redirect" value="/caixa">
							<button id="btn-fechar-caixa" type="button" @if(sizeof($caixa) == 0) disabled @endif class="btn btn-lg btn-danger">
								<i class="la la-times"></i>
								Fechar Caixa
							</button>
						</form>
					</div>
				</div>
				@endif

			</div>
		</div>
	</div>
</div>

<input type="hidden" id="_token" value="{{csrf_token()}}" name="">

<div class="modal fade" id="modal-abrir-caixa" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Abertura de caixa</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Valor</label>
						<div class="">
							<input type="text" id="valor" name="valor" class="form-control money" value="">
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" onclick="abrirCaixa()" class="btn btn-light-success font-weight-bold">Abrir</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-supri" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">SUPRIMENTO DE CAIXA</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group validated col-sm-6 col-lg-6 col-6">
						<label class="col-form-label" id="">Valor</label>
						<input type="text" placeholder="Valor" id="valor_suprimento" name="valor_sangria" class="form-control money" value="">
					</div>

					<div class="form-group validated col-lg-4 col-6">
						<label class="col-form-label" id="">Tipo</label>
						<select id="tipo_suprimento" class="custom-select">
							<option value="">Selecione</option>
							@foreach(\App\Models\SuprimentoCaixa::tiposPagamento() as $key => $t)
							<option value="{{ $key }}">{{ $t }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12 col-12">
						<label class="col-form-label" id="">Observação</label>
						<input type="text" placeholder="Observação" id="obs_suprimento" name="obs" class="form-control" value="">
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button style="width: 100%" type="button" onclick="suprimentoCaixa()" class="btn btn-success font-weight-bold">OK</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-sangria" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">SANGRIA DE CAIXA</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group validated col-sm-6 col-lg-6 col-12">
						<label class="col-form-label" id="">Valor</label>
						<input type="text" placeholder="Valor" id="valor_sangria" name="valor_sangria" class="form-control" value="">
					</div>
				</div>

				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12 col-12">
						<label class="col-form-label" id="">Observação</label>
						<input type="text" placeholder="Observação" id="obs_sangria" name="obs" class="form-control" value="">
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button style="width: 100%" type="button" onclick="sangriaCaixa()" class="btn btn-success font-weight-bold">OK</button>
			</div>
		</div>
	</div>
</div>

<input type="hidden" id="_token" value="{{csrf_token()}}" name="">

<div class="modal fade" id="modal-fechar-caixa" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Fechamento de Caixa</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<h4 class="ml-3">Deseja fechar o caixa?</h4>
					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Valor em dinheiro gaveta (opcional)</label>
						<div class="">
							<input type="text" id="valor_dinheiro" name="valor_dinheiro" class="form-control money" value="">
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" onclick="fecharCaixa()" class="btn btn-light-success font-weight-bold">Fechar Caixa</button>
			</div>
		</div>
	</div>
</div>

@section('javascript')
<script type="text/javascript">
	$('#sel_usuario').change(() => {
		$('#form-filtro').submit()
	})

	$("#btn-fechar-caixa").click(() => {
		$('#modal-fechar-caixa').modal('show')
	})

	function fecharCaixa(){
		let valor = $('#valor_dinheiro').val()
		$('#valor_dinheiro_caixa').val(valor)
		$('#form-caixa').submit()
	}
</script>
@endsection
@endsection
