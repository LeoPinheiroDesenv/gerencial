@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
	<div class="card-body">

		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

			@if(isset($config))
			<input type="hidden" id="pass" value="{{ $config->senha_remover }}">
			@endif

			<input type="hidden" id="_token" value="{{ csrf_token() }}">
			<form class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft" method="get" action="/orcamentoVenda/filtro" id="form-pesquisa">
				<div class="row align-items-center">
					<div class="form-group col-lg-2 col-sm-6">
						<label class="col-form-label">Tipo pesquisa</label>
						<div class="">
							<select name="tipo_pesquisa" class="custom-select">
								@foreach(App\Models\Cliente::tiposPesquisa() as $key => $t)
								<option @isset($tipoPesquisa) @if($tipoPesquisa == $key) selected @endif @endif value="{{$key}}">{{$t}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-group col-lg-2 col-md-4 col-sm-6">
						<label class="col-form-label">Cliente</label>
						<div class="">
							<div class="input-group">
								<input type="text" name="cliente" class="form-control" value="{{{isset($cliente) ? $cliente : ''}}}" />
							</div>
						</div>
					</div>

					<div class="form-group col-lg-2 col-md-4 col-sm-6">
						<label class="col-form-label">Data Inicial</label>
						<div class="">
							<div class="input-group date">
								<input type="text" name="data_inicial" class="form-control" readonly value="{{{isset($dataInicial) ? $dataInicial : ''}}}" id="kt_datepicker_3" />
								<div class="input-group-append">
									<span class="input-group-text">
										<i class="la la-calendar"></i>
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-2 col-md-4 col-sm-6">
						<label class="col-form-label">Data Final</label>
						<div class="">
							<div class="input-group date">
								<input type="text" name="data_final" class="form-control" readonly value="{{{isset($dataFinal) ? $dataFinal : ''}}}" id="kt_datepicker_3" />
								<div class="input-group-append">
									<span class="input-group-text">
										<i class="la la-calendar"></i>
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-2 col-md-4 col-sm-6">
						<label class="col-form-label">Estado</label>
						<div class="">
							<div class="input-group date">
								<select class="custom-select form-control" id="estado" name="estado">
									<option @if(isset($estado) && $estado == 'TODOS') selected @endif value="TODOS">TODOS</option>
									<option @if(isset($estado) && $estado == 'NOVO') selected @endif value="NOVO">NOVO</option>
									<option @if(isset($estado) && $estado == 'APROVADO') selected @endif value="APROVADO">APROVADO</option>
									<option @if(isset($estado) && $estado == 'REPROVADO') selected @endif value="REPROVADO">REPROVADO</option>
								</select>
							</div>
						</div>
					</div>

					@if(empresaComFilial())
					{!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
					@endif

					<input type="hidden" name="imprimir" id="imprimir">

					<div class="col-lg-2 col-xl-2 mt-2 mt-lg-0">
						<button style="margin-top: 15px;" class="btn btn-light-primary px-6 font-weight-bold w-100">
							Filtrar <i class="la la-search"></i>
						</button>
					</div>

					@isset($imprimir)	
					<div class="col-lg-2 col-xl-2 mt-2 mt-lg-0">		
						<button id="btn-imprimir-relatorio" style="margin-top: 15px;" type="button" class="btn btn-light-info px-6 font-weight-bold w-100">
							Imprimir <i class="la la-print"></i>
						</button>
					</div>
					@endif
				</div>
			</form>
			<br>

			<h4 class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight">Lista de Orçamentos</h4>
			<label class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight">Numero de registros: {{sizeof($orcamentos)}}</label>
			<div class="row @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">
				<div class="form-group col-lg-3 col-md-4 col-sm-6">
					<a href="/orcamentoVenda/create" class="btn btn-success">
						<i class="la la-plus"></i>
						Novo Orçamento
					</a>
				</div>
			</div>

		</div>

		<div class="row @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">
			<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

				<div class="wizard wizard-3" id="kt_wizard_v3" data-wizard-state="between" data-wizard-clickable="true">
					<!--begin: Wizard Nav-->

					<div class="wizard-nav">

						<div class="wizard-steps px-8 py-8 px-lg-15 py-lg-3">
							<!--begin::Wizard Step 1 Nav-->
							<div class="wizard-step" data-wizard-type="step" data-wizard-state="done">
								<div class="wizard-label">
									<h3 class="wizard-title">
										<span>
											<i style="font-size: 40px" class="la la-table"></i>
											Tabela
										</span>
									</h3>
									<div class="wizard-bar"></div>
								</div>
							</div>
							<!--end::Wizard Step 1 Nav-->
							<!--begin::Wizard Step 2 Nav-->
							<div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
								<div class="wizard-label" id="grade">
									<h3 class="wizard-title">
										<span>
											<i style="font-size: 40px" class="la la-tablet"></i>
											Grade
										</span>
									</h3>
									<div class="wizard-bar"></div>
								</div>
							</div>

						</div>
					</div>


					<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

						<!--begin: Wizard Form-->
						<form class="form fv-plugins-bootstrap fv-plugins-framework" id="kt_form">
							<!--begin: Wizard Step 1-->
							<div class="pb-5" data-wizard-type="step-content">

								<!-- Inicio da tabela -->

								<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
									<div class="row">
										<div class="col-xl-12">

											<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

												<table class="datatable-table" style="max-width: 100%; overflow: scroll">
													<thead class="datatable-head">
														<tr class="datatable-row" style="left: 0px;">
															<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">#</span></th>
															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Ações</span></th>
															<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">Código</span></th>
															<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Cliente</span></th>
															<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Usuário</span></th>

															@if(empresaComFilial())
															<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Local</span></th>
															@endif
															<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor integral</span></th>

															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Desconto</span></th>
															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Acréscimo</span></th>

															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Total</span></th>

															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Estado</span></th>
															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>
															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Forma de Pagamento</span></th>
															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Tipo de Pagamento</span></th>
															
														</tr>
													</thead>

													<tbody id="body" class="datatable-body">
														<?php $total = 0; ?>
														@foreach($orcamentos as $v)

														<tr class="datatable-row">
															<td id="checkbox">

																@if(!$v->status)
																<p style="width: 90px;">
																	<input type="checkbox" class="check" id="test_{{$v->id}}" />
																	<label for="test_{{$v->id}}"></label>
																</p>
																@endif

															</td>

															<td>
																<div class="row">
																	<span style="width: 180px;">

																		@if($v->estado == 'NOVO')
																		<a class="btn btn-danger btn-sm" onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ removerOrcamento("{{$v->id}}") }else{return false} })' href="#!" href="/orcamentoVenda/delete/{{ $v->id }}">
																			<i class="la la-trash"></i>
																		</a>

																		<a class="btn btn-warning btn-sm" href="/orcamentoVenda/edit/{{ $v->id }}">
																			<i class="la la-edit"></i>
																		</a>

																		@endif

																		@if(!$v->venda_id)
																		<a onclick='swal("Atenção!", "Deseja gerar a venda desse orçamento?", "warning").then((sim) => {if(sim){ location.href="/orcamentoVenda/gerarVenda/{{ $v->id }}" }else{return false} })' href="#!" title="Gerar venda" class="btn btn-dark btn-sm">
																			<i class="la la-file"></i>
																		</a>
																		@endif

																	</span>
																</div>
															</td>
															<td class="datatable-cell">
																<span class="codigo_" style="width: 70px;" id="seq">{{$v->numero_sequencial}}</span>

																<span class="codigo d-none" style="width: 70px;" id="id">{{$v->id}}</span>
															</td>
															<td class="datatable-cell"><span class="codigo" style="width: 200px;">{{ $v->cliente->razao_social ?? 'NAO IDENTIFCADO' }}</span>
															</td>
															<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{$v->usuario->nome}}</span>
															</td>
															@if(empresaComFilial())
															<td class="datatable-cell">
																<span class="codigo" style="width: 150px;">
																	{{ $v->filial_id ? $v->filial->descricao : 'Matriz' }}
																</span>
															</td>
															@endif
															<td class="datatable-cell">
																<span class="codigo" style="width: 100px;">
																	{{ number_format($v->valor_total, $casasDecimais, ',', '.') }}
																</span>
															</td>
															<td class="datatable-cell">
																<span class="codigo" style="width: 100px;">
																	{{ number_format($v->desconto, 2, ',', '.') }}
																</span>
															</td>
															<td class="datatable-cell">
																<span class="codigo" style="width: 100px;">
																	{{ number_format($v->acrescimo, 2, ',', '.') }}
																</span>
															</td>
															<td class="datatable-cell">
																<span class="codigo" style="width: 100px;">
																	{{ number_format($v->valor_total - $v->desconto + $v->acrescimo, $casasDecimais, ',', '.') }}
																</span>
															</td>

															<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="estado_{{$v->id}}">{{ $v->estado }}</span>
															</td>
															

															<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i:s')}}</span>
															</td>
															<td class="datatable-cell">
																<span class="codigo" style="width: 100px;">
																	@if($v->forma_pagamento == '30_dias')
																	30 Dias
																	@elseif($v->forma_pagamento == 'a_vista')
																	A vista
																	@else
																	{{ $v->forma_pagamento }}
																	@endif
																</span>
															</td>
															<td class="datatable-cell">
																<span class="codigo" style="width: 120px;">
																	@if($v->tipo_pagamento == '99')
																	@if($v->ecommerce)
																	orçamento ecommerce
																	@else
																	--
																	@endif
																	@else
																	{{$v->getTipoPagamento($v->tipo_pagamento)}}
																	@endif
																</span>
															</td>

														</tr>
														<?php 
														$total += $v->valor_total;
														?>
														@endforeach

													</tbody>
												</table>
											</div>
										</div>

										<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
											<div class="row">

												<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
													<a id="btn-imprimir" onclick="imprimir()" style="width: 100%" class="btn btn-primary" href="#!">Imprimir</a>
												</div>

												<!-- <div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
													<a id="btn-danfe" style="width: 100%" class="btn btn-info" href="#!">Simular Danfe</a>
												</div> -->

												<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
													<a id="btn-correcao" onclick="setaEmail()" style="width: 100%" class="btn btn-secondary">Enviar Email</a>
												</div>

												<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
													<a onclick="modalWhatsApp()" style="width: 100%" class="btn btn-success" >WhatsApp</a>
												</div>

												<?php  
												$dataInicial = str_replace("/", "-", $dataInicial);
												$dataFinal = str_replace("/", "-", $dataFinal);
												?>
												<div class="col-sm-4 col-lg-4 col-md-4 col-xl-3 col-6">
													<a target="_blank" href="/orcamentoVenda/relatorioItens/{{$dataInicial}}/{{$dataFinal}}" style="width: 100%" class="btn btn-danger" >Relatório de compras</a>
												</div>



											</div>
										</div>

									</div>
								</div>
								<!-- Fim da tabela -->
							</div>

							<!--end: Wizard Step 1-->
							<!--begin: Wizard Step 2-->
							<div class="pb-5" data-wizard-type="step-content">

								<!-- Inicio do card -->

								<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
									<div class="row">

										@foreach($orcamentos as $v)
										<div class="col-sm-6 col-lg-6 col-md-6 col-xl-6">

											<div class="card card-custom gutter-b example example-compact">
												<div class="card-header">
													<div class="card-title">
														<h3 style="width: 230px; font-size: 15px; height: 10px;" class="card-title">
															{{$v->cliente->razao_social}}
														</h3>

													</div>
													<div class="card-toolbar">
														<div class="dropdown dropdown-inline" data-toggle="tooltip" title="" data-placement="left" data-original-title="Ações">
															<a href="#" class="btn btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																<i class="fa fa-ellipsis-h"></i>
															</a>
															<div class="dropdown-menu p-0 m-0 dropdown-menu-md dropdown-menu-right">
																<!--begin::Navigation-->
																<ul class="navi navi-hover">
																	<li class="navi-header font-weight-bold py-4">
																		<span class="font-size-lg">Ações:</span>
																	</li>
																	<li class="navi-separator mb-3 opacity-70"></li>

																	@if($v->estado == 'NOVO')
																	<li class="navi-item">
																		<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/orcamentoVenda/delete/{{ $v->id }}" }else{return false} })' href="#!" 
																			class="navi-link">
																			<span class="navi-text">
																				<span class="label label-xl label-inline label-light-danger">Remover</span>
																			</span>
																		</a>

																		<a href="/orcamentoVenda/edit/{{ $v->id }}" 
																			class="navi-link">
																			<span class="navi-text">
																				<span class="label label-xl label-inline label-light-warning">Editar</span>
																			</span>
																		</a>
																	</li>
																	@endif

																	<li class="navi-item">
																		<a href="/orcamentoVenda/detalhar/{{ $v->id }}" 
																			class="navi-link">
																			<span class="navi-text">
																				<span class="label label-xl label-inline label-light-info">Detalhar</span>
																			</span>
																		</a>
																	</li>

																</ul>
																<!--end::Navigation-->
															</div>
														</div>

													</div>
												</div>

												<div class="card-body">

													<div class="kt-widget__info">
														<span class="kt-widget__label">Valor:</span>
														<a target="_blank" class="kt-widget__data text-success">
															R$ {{ number_format($v->valor_total, $casasDecimais, ',', '.') }}
														</a>
													</div>

													<div class="kt-widget__info">
														<span class="kt-widget__label">Data:</span>
														<a target="_blank" class="kt-widget__data text-success">
															{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i:s')}}
														</a>
													</div>

													<div class="kt-widget__info">
														<span class="kt-widget__label">NFe:</span>
														<a target="_blank" class="kt-widget__data text-success">
															{{ $v->NfNumero > 0 ? $v->NfNumero : '--' }}
														</a>
													</div>

													<div class="kt-widget__info">
														<span class="kt-widget__label">Usuário:</span>
														<a target="_blank" class="kt-widget__data text-success">
															{{ $v->usuario->nome }}
														</a>
													</div>

													@if(empresaComFilial())
													<div class="kt-widget__info">
														<span class="kt-widget__label">Local:</span>
														<a target="_blank" class="kt-widget__data text-success">
															{{ $v->filial_id ? $v->filial->descricao : 'Matriz' }}
														</a>
													</div>
													@endif

													<hr>

													<div class="row">



														<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
															<a style="width: 100%; margin-top: 5px;" target="_blank" href="/orcamentoVenda/imprimir/{{$v->id}}" class="btn btn-success">
																<i class="la la-print"></i>
																Imprimir 
															</a>
														</div>


														<!-- <div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
															<a style="width: 100%; margin-top: 5px;" target="_blank" href="/orcamentoVenda/rederizarDanfe/{{$v->id}}" class="btn btn-info">
																<i class="la la-print"></i>
																Simular Danfe
															</a>
														</div> -->

														<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
															<a style="width: 100%; margin-top: 5px;" href="#!" onclick="enviarEmailGrid('{{$v->id}}')" class="btn btn-danger">
																<i class="la la-envelope"></i>
																Enviar Email
															</a>
														</div>

													</div>
												</div>
											</div>

										</div>
										@endforeach

									</div>
								</div>
							</div>
							<!--end: Wizard Step 2-->
							<div class="d-flex justify-content-between align-items-center flex-wrap">
								<div class="d-flex flex-wrap py-2 mr-3">
									@if(isset($links))
									{{$orcamentos->links()}}
									@endif
								</div>
							</div>
						</form>

					</div>
				</div>
			</div>
		</div>



	</div>
</div>


<div class="modal fade" id="modal-whatsApp" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">WHATSAPP</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group validated col-sm-6 col-lg-6">
						<label class="col-form-label" id="">WhatsApp</label>
						<input type="hidden" name="">
						<div class="">
							<input type="text" id="celular" placeholder="WhatsApp" name="email" class="form-control" value="">
						</div>
					</div>

					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Texto</label>
						<input type="hidden" name="">
						<div class="">
							<input type="text" id="texto" placeholder="texto" name="texto" class="form-control" value="">
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-send-whats" onclick="enviarWhatsApp()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Enviar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal5" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">ENVIAR EMAIL</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Email</label>
						<input type="hidden" id="id_correcao" name="">
						<div class="">
							<input type="text" id="email" placeholder="Email" name="email" class="form-control" value="">
						</div>
						<p id="info-email" class="text-danger"></p>

					</div>
				</div>

				<input type="hidden" id="venda_id">


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-send-email" onclick="enviarEmail()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Enviar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal5-grid" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">ENVIAR EMAIL</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Email</label>
						<input type="hidden" id="id_correcao" name="">
						<div class="">
							<input type="text" id="email-grid" placeholder="Email" name="email" class="form-control" value="">
						</div>
						<p id="info-email-grid" class="text-danger"></p>
					</div>

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-send-email2" onclick="enviarEmail2()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Enviar</button>
			</div>
		</div>
	</div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
	$('#btn-imprimir-relatorio').click(() => {
		$('#imprimir').val('1')
		$('#form-pesquisa').submit()
		$('#imprimir').val('0')

	})
</script>
@endsection