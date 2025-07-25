@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">

		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

			<input type="hidden" id="_token" value="{{ csrf_token() }}">
			<form class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft" method="get" action="/mdfe/filtro">
				<div class="row align-items-center">

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
									<option @if(isset($estado) && $estado == 'NOVO') selected @endif value="NOVO">DISPONIVEIS</option>
									<option @if(isset($estado) && $estado == 'REJEITADO') selected @endif value="REJEITADO">REJEITADAS</option>
									<option @if(isset($estado) && $estado == 'CANCELADO') selected @endif value="CANCELADO">CANCELADAS</option>
									<option @if(isset($estado) && $estado == 'APROVADO') selected @endif value="APROVADO">APROVADAS</option>
									<option @if(isset($estado) && $estado == 'TODOS') selected @endif value="TODOS">TODOS</option>
								</select>
							</div>
						</div>
					</div>

					@if(empresaComFilial())
					{!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
					@endif

					<div class="col-lg-2 col-xl-2 mt-2 mt-lg-0">
						<button style="margin-top: 15px;" class="btn btn-light-primary px-6 font-weight-bold">Filtrar</button>
					</div>
				</div>
			</form>
			<br>
			<h4 class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">Lista de MDFe</h4>

			<label class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">Registros: <strong class="text-success">{{sizeof($mdfes)}}</strong></label>
			<div class="row @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
				<div class="form-group col-12">
					<a href="/mdfe/nova" class="btn btn-success">
						<i class="la la-plus"></i>
						Nova MDFe
					</a>

					<a href="/mdfeSefaz/naoEncerrados" class="btn btn-danger">
						<i class="la la-list"></i>
						Ver Documentos Não Encerrados
					</a>

					<button class="btn btn-dark" data-toggle="modal" data-target="#modal-nfe">
						<i class="la la-file-invoice"></i>
						Importar NFe(s) emitida(s)
					</button>
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
																<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">#</span></th>
																<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">ID</span></th>
																<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Data Inicio da Viagem</span></th>
																<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">CNPJ Contratante</span></th>
																<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Estado</span></th>

																@if(empresaComFilial())
																<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Local</span></th>
																@endif

																<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Veiculo tração</span></th>

																<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Quantidade carga</span></th>
																
																<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor Carga</span></th>

																<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Ações</span></th>
															</tr>
														</thead>

														<tbody id="body" class="datatable-body">

															@foreach($mdfes as $m)

															<tr class="datatable-row">
																<td id="checkbox">

																	<p style="width: 80px;">
																		<input type="checkbox" class="check" id="test_{{$m->id}}" />
																		<label for="test_{{$m->id}}"></label>
																	</p>

																</td>
																<td style="display: none" id="numero">{{$m->mdfe_numero}}</td>
																<td style="display: none" id="id">{{$m->id}}</td>

																<td style="display: none" id="estado_{{$m->id}}">{{$m->estado}}</td>
																<td class="datatable-cell">
																	<span class="codigo" style="width: 70px;">
																		{{ $m->id }}
																	</span>
																</td>

																<td class="datatable-cell">
																	<span class="codigo" style="width: 150px;">
																		{{ \Carbon\Carbon::parse($m->data_inicio_viagem)->format('d/m/Y')}}
																	</span>
																</td>
																<td class="datatable-cell">
																	<span class="codigo" style="width: 150px;">
																		{{$m->cnpj_contratante}}
																	</span>
																</td>
																<td class="datatable-cell">
																	<span class="codigo" style="width: 100px;">
																		@if($m->estado == 'NOVO')
																		<span class="label label-xl label-inline label-light-primary">Disponível</span>

																		@elseif($m->estado == 'APROVADO')
																		<span class="label label-xl label-inline label-light-success">Aprovado</span>
																		@elseif($m->estado == 'CANCELADO')
																		<span class="label label-xl label-inline label-light-danger">Cancelado</span>
																		@else
																		<span class="label label-xl label-inline label-light-warning">Rejeitado</span>
																		@endif
																	</span>
																</td>

																@if(empresaComFilial())
																<td class="datatable-cell">
																	<span class="codigo" style="width: 150px;">
																		{{ $m->filial_id ? $m->filial->descricao : 'Matriz' }}
																	</span>
																</td>
																@endif

																<td class="datatable-cell">
																	<span class="codigo" style="width: 100px;">
																		{{$m->veiculoTracao->marca}} {{$m->veiculoTracao->placa}}
																	</span>
																</td>
																
																<td class="datatable-cell">
																	<span class="codigo" style="width: 100px;">
																		{{$m->quantidade_carga}}
																	</span>
																</td>
																<td class="datatable-cell">
																	<span class="codigo" style="width: 100px;">
																		{{number_format($m->valor_carga, 2, ',', '.')}}
																	</span>
																</td>
																

																<td>
																	<div class="row">
																		<span style="width: 200px;">

																			@if($m->estado == 'NOVO' || $m->estado == 'REJEITADO')
																			<a class="btn btn-danger btn-sm" onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/mdfe/delete/{{ $m->id }}" }else{return false} })' href="#!">
																				<i class="la la-trash"></i>				
																			</a>

																			<a class="btn btn-warning btn-sm" onclick='swal("Atenção!", "Deseja editar este registro?", "warning").then((sim) => {if(sim){ location.href="/mdfe/edit/{{ $m->id }}" }else{return false} })' href="#!">
																				<i class="la la-edit"></i>	
																			</a>

																			@endif

																			<a title="Alterar estado fiscal" class="btn btn-info btn-sm" href="/mdfe/estadoFiscal/{{ $m->id }}">
																				<i class="la la-file"></i>	
																			</a>

																			<a title="Alterar estado fiscal" class="btn btn-dark btn-sm" href="/mdfe/clone/{{ $m->id }}">
																				<i class="la la-copy"></i>
																			</a>

																			@if($m->estado == 'APROVADO')
																			<a title="Downlaod XML" class="btn btn-dark btn-sm" href="/mdfeSefaz/baixarXml/{{ $m->id }}">
																				<i class="la la-download"></i>	
																			</a>
																			@endif

																		</span>
																	</div>
																</td>

															</tr>
															
															@endforeach

														</tbody>
													</table>
												</div>
											</div>


											<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12 mt-2">
												<div class="row">

													<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
														<a id="btn-enviar" onclick="enviar()" style="width: 100%" class="btn btn-success spinner-white spinner-right" href="#!">Enviar</a>
													</div>

													<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
														<a id="btn-imprimir" onclick="imprimir()" style="width: 100%" class="btn btn-secondary" href="#!">Imprimir</a>
													</div>

													<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
														<a id="btn-consultar" onclick="consultar()" style="width: 100%" class="btn btn-info spinner-white spinner-right" href="#!">Consultar</a>
													</div>

													<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
														<a id="btn-cancelar" data-toggle="modal" data-target="#modal1" onclick="setarNumero()" style="width: 100%" class="btn btn-danger" href="#modal1">Cancelar</a>
													</div>

													<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
														<a id="btn-xml" onclick="setarNumero(true)" style="width: 100%" class="btn btn-warning" data-toggle="modal" data-target="#modal5">Enviar XML</a>
													</div>

													<div class="col-sm-4 col-lg-4 col-md-4 col-xl-2 col-6">
														<a id="btn-xml-temp" style="width: 100%" class="btn btn-warning" data-target="#modal5">XML temporário</a>
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

											@foreach($mdfes as $m)
											<div class="col-sm-6 col-lg-6 col-md-6 col-xl-6">

												<div class="card card-custom gutter-b example example-compact">
													<div class="card-header">
														<div class="card-title">
															<h3 style="width: 230px; font-size: 15px; height: 10px;" class="card-title">
																<strong class="text-success"> </strong>

																{{$m->id}} - {{$m->cnpj_contratante}}

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


																		@if($m->estado == 'NOVO' || $m->estado == 'REJEITADO')

																		<li class="navi-item">
																			<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/mdfe/delete/{{ $m->id }}" }else{return false} })' href="#!" class="navi-link">
																				<span class="navi-text">
																					<span class="label label-xl label-inline label-light-danger">Remover</span>
																				</span>
																			</a>
																		</li>

																		<li class="navi-item">
																			<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/mdfe/edit/{{ $m->id }}" }else{return false} })' href="#!" class="navi-link">
																				<span class="navi-text">
																					<span class="label label-xl label-inline label-light-warning">Editar</span>
																				</span>
																			</a>
																		</li>

																		@endif

																		<li class="navi-item">
																			<a href="/mdfe/estadoFiscal/{{ $m->id }}" class="navi-link">
																				<span class="navi-text">
																					<span class="label label-xl label-inline label-light-info">Alterar estado fiscal</span>
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
															<span class="kt-widget__label">Valor da carga:</span>
															<a class="kt-widget__data text-success">
																{{$m->valor_carga}}
															</a>
														</div>

														<div class="kt-widget__info">
															<span class="kt-widget__label">Quantidade da carga:</span>
															<a class="kt-widget__data text-success">
																{{$m->quantidade_carga}}
															</a>
														</div>

														<div class="kt-widget__info">
															<span class="kt-widget__label">Data de inicio viagem:</span>
															<a class="kt-widget__data text-success">
																{{ \Carbon\Carbon::parse($m->data_inicio_viagem)->format('d/m/Y')}}
															</a>
														</div>

														<div class="kt-widget__info">
															<span class="kt-widget__label">Veiculo tração:</span>
															<a class="kt-widget__data text-success">
																{{$m->veiculoTracao->marca}} {{$m->veiculoTracao->placa}}
															</a>
														</div>

														<div class="kt-widget__info">
															<span class="kt-widget__label">Estado:</span>
															<a class="kt-widget__data text-success">

																@if($m->estado == 'NOVO')
																<span class="label label-xl label-inline label-light-primary">Disponível</span>

																@elseif($m->estado == 'APROVADO')
																<span class="label label-xl label-inline label-light-success">Aprovado</span>
																@elseif($m->estado == 'CANCELADO')
																<span class="label label-xl label-inline label-light-danger">Cancelado</span>
																@else
																<span class="label label-xl label-inline label-light-warning">Rejeitado</span>
																@endif
															</a>
														</div>

														<hr>

														<div class="row">

															@if($m->estado == 'APROVADO')

															<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
																<a style="width: 100%; margin-top: 5px;" href="/mdfeSefaz/imprimir/{{$m->id}}" class="btn btn-success">
																	<i class="la la-print"></i>
																	Imprimir 
																</a>
															</div>

															<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
																<a id="btn_consulta_grid_{{$m->id}}" style="width: 100%; margin-top: 5px;" href="#!" onclick="consultarMDFe('{{$m->id}}')" class="btn btn-info spinner-white spinner-right">
																	<i class="la la-check"></i>
																	Consultar MDFe
																</a>
															</div>

															<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
																<a id="btn_consulta_grid_{{$m->id}}" style="width: 100%; margin-top: 5px;" href="#!" onclick="cancelarMDFe('{{$m->id}}', '{{$m->mdfe_numero}}')" class="btn btn-danger spinner-white spinner-right">
																	<i class="la la-close"></i>
																	Cancelar MDFe
																</a>
															</div>

															<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
																<a style="width: 100%; margin-top: 5px;" href="/mdfeSefaz/baixarXml/{{$m->id}}" class="btn btn-danger">
																	<i class="la la-download"></i>
																	Baixar XML
																</a>
															</div>
															@endif

															@if($m->estado == 'REJEITADO')
															<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
																<a id="btn_transmitir_grid_{{$m->id}}" style="width: 100%; margin-top: 5px;" href="#!" onclick="transmitirMDFe('{{$m->id}}')" class="btn btn-success spinner-white spinner-right">
																	<i class="la la-check"></i>
																	Transmitir MDFe
																</a>
															</div>
															@endif

															@if($m->estado == 'NOVO')
															<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
																<a id="btn_transmitir_grid_{{$m->id}}" style="width: 100%; margin-top: 5px;" href="#!" onclick="transmitirMDFe('{{$m->id}}')" class="btn btn-success spinner-white spinner-right">
																	<i class="la la-check"></i>
																	Transmitir MDFe 
																</a>
															</div>
															@endif

															@if($m->estado == 'CANCELADO')
															<div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">
																<a id="btn_consulta_grid_{{$m->id}}" style="width: 100%; margin-top: 5px;" href="#!" onclick="consultarMDFe('{{$m->id}}')" class="btn btn-info spinner-white spinner-right">
																	<i class="la la-check"></i>
																	Consultar MDFe
																</a>
															</div>
															@endif

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
										{{$mdfes->links()}}
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
</div>


<div class="modal fade" id="modal1" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">CANCELAR MDFe <strong class="text-danger" id="numero_cancelamento"></strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">

					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Justificativa</label>
						<div class="">
							<input type="text" id="justificativa" placeholder="Justificativa minimo de 15 caracteres" name="justificativa" class="form-control" value="">
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-cancelar-2" onclick="cancelar()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Cancelar MDFe</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal1_aux" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">CANCELAR MDFe <strong class="text-danger" id="numero_cancelamento2"></strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<input type="hidden" id="id_cancela" name="">
					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Justificativa</label>
						<div class="">
							<input type="text" id="justificativa2" placeholder="Justificativa minimo de 15 caracteres" name="justificativa" class="form-control" value="">
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-cancelar-3" onclick="cancelar2()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Cancelar MDFe</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal5" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">ENVIAR XML DA MDFe <strong class="text-danger" id="numero_nf"></strong></h5>
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
					</div>
				</div>
				<input type="hidden" id="venda_id">


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-send" onclick="enviarEmailXMl()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Enviar</button>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="modal-nfe" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Importar NFe</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				
				<div class="row">
					<div class="form-group col-lg-3 col-md-4 col-sm-6">
						<label class="col-form-label">Data Inicial</label>
						<div class="">
							<div class="input-group date">
								<input type="text" name="data_inicial" class="form-control date-input" id="kt_datepicker_1" />
								<div class="input-group-append">
									<span class="input-group-text">
										<i class="la la-calendar"></i>
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-4 col-sm-6">
						<label class="col-form-label">Data Final</label>
						<div class="">
							<div class="input-group date">
								<input type="text" name="data_final" class="form-control date-input" id="kt_datepicker_2" />
								<div class="input-group-append">
									<span class="input-group-text">
										<i class="la la-calendar"></i>
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-3 col-xl-2 mt-2 mt-lg-0">
						<br>
						<button id="btn-buscar-nfes" style="margin-top: 17px;" class="btn btn-light-success px-6 font-weight-bold spinner-white spinner-right w-100">
							<i class="la la-search"></i>
							Filtrar
						</button>
					</div>
				</div>

				<div class="row">
					<div class="col-xl-12">
						<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

							<table class="datatable-table" style="max-width: 100%; overflow: scroll">
								<thead class="datatable-head">
									<tr class="datatable-row" style="left: 0px;">
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">#</span></th>
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">ID</span></th>
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 250px;">Cliente</span></th>
										<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Data de registro</span></th>
										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor Total</span></th>
										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 400px;">Chave</span></th>
										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Nº NFe</span></th>
									</tr>
								</thead>
								<tbody id="nfe-list" class="datatable-body">

								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-send" onclick="importarNfe()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Importar</button>
			</div>
		</div>
	</div>
</div>


@endsection	

@section('javascript')
<script type="text/javascript">
	$(function(){
		// $('#modal-nfe').modal('show')
		$('#btn-buscar-nfes').trigger('click')
	})

	function getNfes(){

		let data1 = $('#kt_datepicker_1').val()
		let data2 = $('#kt_datepicker_2').val()
		$.get(path + 'nf/filtro',{data1: data1, data2: data2})
		.done((res) => {
			$('#btn-buscar-nfes').removeClass('spinner')
			// console.log(res)
			let t = ''
			res.map((item) => {
				console.log(item)
				t += '<tr class="datatable-row">'
				t += '<td class="datatable-cell">'
				t += '<span class="codigo" style="width: 60px;">'

				t += '<input class="sl-'+item.id+'" type="checkbox" value="'+item.id+'"/>'
				t += '</span>'
				t += '</td>'
				t += '<td class="datatable-cell">'
				t += '<span class="codigo" style="width: 70px;">'
				t += item.id + '</span>'
				t += '</td>'

				t += '</td>'
				t += '<td class="datatable-cell">'
				t += '<span class="codigo" style="width: 250px;">'
				t += item.cliente.razao_social + '</span>'
				t += '</td>'

				t += '<td class="datatable-cell">'
				t += '<span class="codigo" style="width: 120px;">'
				t += item.data_registro + '</span>'
				t += '</td>'

				t += '<td class="datatable-cell">'
				t += '<span class="codigo" style="width: 100px;">'
				t += formatReal(item.valor_total) + '</span>'
				t += '</td>'

				t += '<td class="datatable-cell">'
				t += '<span class="codigo" style="width: 400px;">'
				t += item.chave + '</span>'
				t += '</td>'

				t += '<td class="datatable-cell">'
				t += '<span class="codigo" style="width: 100px;">'
				t += item.NfNumero + '</span>'
				t += '</td>'

				t += '</tr>'
			})

			$('#nfe-list').html(t)
		})
		.fail((err) => {
			$('#btn-buscar-nfes').removeClass('spinner')
			console.log(err)
		})
	}

	function formatReal(v)
	{
		return parseFloat(v).toFixed(casas_decimais).replace(".", ",")
	}

	$('#btn-buscar-nfes').click(() => {
		$('#btn-buscar-nfes').addClass('spinner')
		getNfes()
	})

	function importarNfe(){
		let ids = []
		$('#nfe-list tr').each(function(){
			if($(this).find('input').is(':checked')){
				let id = $(this).find('input').val()
				console.log(id)
				ids.push(id)
			}
		})

		if(ids.length > 0){
			location.href = path + 'mdfe/createWithNfe/'+ids
		}else{
			swal("Alerta", "Selecione ao menos um documento!", "warning")
		}
	}
</script>
@endsection