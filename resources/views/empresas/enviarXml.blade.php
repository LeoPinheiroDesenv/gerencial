@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="get" action="/empresas/filtroXml">

					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">
								ARQUIVOS XML DA EMPRESA <strong style="margin-left: 3px;" class="text-info">{{$empresa->nome}}</strong>
							</h3>
						</div>
					</div>
					@if(isset($estado) && $estado == 0)
					<h3 class="text-danger">>>Filtrando os documentos cancelados</h3>

					@endif

					<div class="row">
						<div class="col-xl-2"></div>
						<div class="col-xl-8">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row align-items-center">
										<div class="form-group col-lg-3 col-md-4 col-sm-6">
											<label class="col-form-label">Data Inicial</label>
											<div class="">
												<div class="input-group date">
													<input type="text" name="data_inicial" class="form-control date-input" id="kt_datepicker_3" value="{{isset($dataInicial) ? $dataInicial : ''}}" />
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
													<input type="text" name="data_final" class="form-control date-input" id="kt_datepicker_3" value="{{isset($dataFinal) ? $dataFinal : ''}}" />
													<div class="input-group-append">
														<span class="input-group-text">
															<i class="la la-calendar"></i>
														</span>
													</div>
												</div>
											</div>
										</div>

										<input type="hidden" name="empresa_filtro_id" value="{{$empresa->id}}">

										<div class="form-group col-lg-3 col-md-3 col-sm-6">
											<label class="col-form-label">Estado</label>
											<div class="">
												<div class="input-group">
													<select name="estado" class="form-control custom-select">
														<option @isset($estado) @if($estado == 1) selected @endif @endif value="1">APROVADO</option>
														<option @isset($estado) @if($estado == 0) selected @endif @endif value="0">CANCELADO</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-lg-3 col-md-3 col-sm-6">
											<button style="margin-top: 15px;" class="btn btn-light-primary px-6 font-weight-bold">Buscar Arquivos</button>
										</div>



									</div>
								</div>
							</div>
						</div>
					</div>
				</form>


				@if(isset($xml)&& sizeof($xml) > 0)
				<div class="form-group col-lg-12 col-md-12 col-sm-12">

					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<div class="col-lg-4 col-md-4 col-sm-6">
								<h3 class="card-title">Total de arquivos de NFe: <strong style="margin-left: 5px; color: blue">{{count($xml)}}</strong></h3>
							</div>
							<div class="col-lg-4 col-md-6 col-sm-6">

								<a target="_blank" style="width: 100%" href="/empresas/downloadXml/{{$empresa->id}}" class="btn">
									<span style="width: 100%" class="label label-xl label-inline label-light-danger">
										Baixar Arquivos de XML NFe
									</span>
								</a>
							</div>
						</div>

						<div class="card-body">
							<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

								<table class="datatable-table" style="max-width: 100%; overflow: scroll">
									<thead class="datatable-head">
										<tr class="datatable-row" style="left: 0px;">
											<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Venda ID</span></th>
											<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
											<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
											<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
											<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>

										</tr>
									</thead>
									<tbody class="datatable-body">
										@foreach($xml as $x)
										<tr class="datatable-row" style="left: 0px;">
											<td class="datatable-cell"><span class="codigo" style="width: 60px;">
												{{$x->id}}</span></td>
												<td class="datatable-cell"><span class="codigo" style="width: 150px;">
													{{$x->cliente->razao_social}}
												</span></td>
												<td class="datatable-cell"><span class="codigo" style="width: 100px;">
													{{number_format($x->valor_total, 2)}}
												</span></td>
												<td class="datatable-cell"><span class="codigo" style="width: 150px;">
													{{$x->chave}}
												</span></td>
												<td class="datatable-cell"><span class="codigo" style="width: 100px;">
													{{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
												</span></td>

											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>

						</div>
					</div>

					@endif

					@if(isset($xmlEntrada) && sizeof($xmlEntrada) > 0)
					<div class="form-group col-lg-12 col-md-12 col-sm-12">

						<div class="card card-custom gutter-b example example-compact">
							<div class="card-header">
								<div class="col-lg-4 col-md-4 col-sm-6">
									<h3 class="card-title">Total de arquivos de NFe Entrada: <strong style="margin-left: 5px; color: blue">{{count($xmlEntrada)}}</strong></h3>
								</div>
								<div class="col-lg-4 col-md-6 col-sm-6">

									<a target="_blank" style="width: 100%" href="/empresas/downloadEntrada/{{$empresa->id}}" class="btn">
										<span style="width: 100%" class="label label-xl label-inline label-light-danger">
											Baixar Arquivos de XML NFe Entrada
										</span>
									</a>
								</div>
							</div>

							<div class="card-body">
								<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

									<table class="datatable-table" style="max-width: 100%; overflow: scroll">
										<thead class="datatable-head">
											<tr class="datatable-row" style="left: 0px;">
												<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Compra ID</span></th>
												<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
												<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
												<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
												<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>

											</tr>
										</thead>
										<tbody class="datatable-body">
											@foreach($xmlEntrada as $x)
											<tr class="datatable-row" style="left: 0px;">
												<td class="datatable-cell"><span class="codigo" style="width: 60px;">
													{{$x->id}}</span></td>
													<td class="datatable-cell"><span class="codigo" style="width: 150px;">
														{{$x->fornecedor->razao_social}}
													</span></td>
													<td class="datatable-cell"><span class="codigo" style="width: 100px;">
														{{number_format($x->valor, 2)}}
													</span></td>
													<td class="datatable-cell"><span class="codigo" style="width: 150px;">
														{{$x->chave}}
													</span></td>
													<td class="datatable-cell"><span class="codigo" style="width: 100px;">
														{{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
													</span></td>

												</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div>

							</div>
						</div>

						@endif

						@if(isset($xmlDevolucao) && sizeof($xmlDevolucao) > 0)
						<div class="form-group col-lg-12 col-md-12 col-sm-12">

							<div class="card card-custom gutter-b example example-compact">
								<div class="card-header">
									<div class="col-lg-4 col-md-4 col-sm-6">
										<h3 class="card-title">Total de arquivos de NFe Devolução: <strong style="margin-left: 5px; color: blue">{{count($xmlDevolucao)}}</strong></h3>
									</div>
									<div class="col-lg-4 col-md-6 col-sm-6">

										<a target="_blank" style="width: 100%" href="/empresas/downloadDevolucao/{{$empresa->id}}" class="btn">
											<span style="width: 100%" class="label label-xl label-inline label-light-danger">
												Baixar Arquivos de XML NFe Devolução
											</span>
										</a>
									</div>
									
								</div>

								<div class="card-body">
									<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

										<table class="datatable-table" style="max-width: 100%; overflow: scroll">
											<thead class="datatable-head">
												<tr class="datatable-row" style="left: 0px;">
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Venda ID</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
													<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
													<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
													<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>

												</tr>
											</thead>
											<tbody class="datatable-body">
												@foreach($xmlDevolucao as $x)
												<tr class="datatable-row" style="left: 0px;">
													<td class="datatable-cell"><span class="codigo" style="width: 60px;">
														{{$x->id}}</span></td>
														<td class="datatable-cell"><span class="codigo" style="width: 150px;">
															{{$x->fornecedor->razao_social}}
														</span></td>
														<td class="datatable-cell"><span class="codigo" style="width: 100px;">
															{{number_format($x->valor_devolvido, 2)}}
														</span></td>
														<td class="datatable-cell"><span class="codigo" style="width: 150px;">
															{{$x->chave_gerada}}
														</span></td>
														<td class="datatable-cell"><span class="codigo" style="width: 100px;">
															{{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
														</span></td>

													</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									</div>

								</div>
							</div>

							@endif

							@if(isset($xmlNfce) && count($xmlNfce) > 0)
							<hr>
							<div class="form-group col-lg-12 col-md-12 col-sm-12">

								<div class="card card-custom gutter-b example example-compact">
									<div class="card-header">
										<div class="col-lg-4 col-md-4 col-sm-6">
											<h3 class="card-title">Total de arquivos de NFCe: <strong style="margin-left: 5px; color: blue">{{count($xmlNfce)}}</strong></h3>
										</div>
										<div class="col-lg-4 col-md-6 col-sm-6">

											<a target="_blank" style="width: 100%" href="/empresas/downloadNfce/{{$empresa->id}}" class="btn">
												<span style="width: 100%" class="label label-xl label-inline label-light-danger">
													Baixar Arquivos de XML NFCe
												</span>
											</a>
										</div>
										
									</div>

									<div class="card-body">
										<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

											<table class="datatable-table" style="max-width: 100%; overflow: scroll">
												<thead class="datatable-head">
													<tr class="datatable-row" style="left: 0px;">
														<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Venda ID</span></th>
														<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Cliente</span></th>
														<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
														<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Chave</span></th>
														<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>

													</tr>
												</thead>
												<tbody class="datatable-body">
													@foreach($xmlNfce as $x)
													<tr class="datatable-row" style="left: 0px;">
														<td class="datatable-cell"><span class="codigo" style="width: 60px;">
															{{$x->id}}</span></td>
															<td class="datatable-cell"><span class="codigo" style="width: 150px;">
																@if($x->cliente)
																{{$x->cliente->razao_social}}
																@else
																--
																@endif
															</span></td>
															<td class="datatable-cell"><span class="codigo" style="width: 100px;">
																{{number_format($x->valor_total, 2)}}
															</span></td>
															<td class="datatable-cell"><span class="codigo" style="width: 150px;">
																{{$x->chave}}
															</span></td>
															<td class="datatable-cell"><span class="codigo" style="width: 100px;">
																{{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
															</span></td>

														</tr>
														@endforeach
													</tbody>
												</table>
											</div>
										</div>

									</div>
								</div>

								@endif

								@if(isset($xmlCte) && count($xmlCte) > 0)
								<hr>
								<div class="form-group col-lg-12 col-md-12 col-sm-12">

									<div class="card card-custom gutter-b example example-compact">
										<div class="card-header">
											<div class="col-lg-4 col-md-4 col-sm-6">
												<h3 class="card-title">Total de arquivos de CTe: <strong style="margin-left: 5px; color: blue">{{count($xmlCte)}}</strong></h3>
											</div>
											<div class="col-lg-4 col-md-6 col-sm-6">

												<a target="_blank" style="width: 100%" href="/empresas/downloadCte/{{$empresa->id}}" class="btn">
													<span style="width: 100%" class="label label-xl label-inline label-light-danger">
														Baixar Arquivos de XML CTe
													</span>
												</a>
											</div>
											
										</div>

										<div class="card-body">
											<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

												<table class="datatable-table" style="max-width: 100%; overflow: scroll">
													<thead class="datatable-head">
														<tr class="datatable-row" style="left: 0px;">
															<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">ID</span></th>

															<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 350px;">Chave</span></th>
															<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>

														</tr>
													</thead>
													<tbody class="datatable-body">
														@foreach($xmlCte as $x)
														<tr class="datatable-row" style="left: 0px;">
															<td class="datatable-cell"><span class="codigo" style="width: 60px;">
																{{$x->id}}</span></td>

																<td class="datatable-cell"><span class="codigo" style="width: 350px;">
																	{{$x->chave}}
																</span></td>
																<td class="datatable-cell"><span class="codigo" style="width: 100px;">
																	{{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
																</span></td>

															</tr>
															@endforeach
														</tbody>
													</table>
												</div>
											</div>

										</div>
									</div>

									@endif

									@if(isset($xmlMdfe) && count($xmlMdfe) > 0)
									<hr>
									<div class="form-group col-lg-12 col-md-12 col-sm-12">

										<div class="card card-custom gutter-b example example-compact">
											<div class="card-header">
												<div class="col-lg-4 col-md-4 col-sm-6">
													<h3 class="card-title">Total de arquivos de MDFe: <strong style="margin-left: 5px; color: blue">{{count($xmlMdfe)}}</strong></h3>
												</div>
												<div class="col-lg-4 col-md-6 col-sm-6">

													<a target="_blank" style="width: 100%" href="/empresas/downloadMdfe/{{$empresa->id}}" class="btn">
														<span style="width: 100%" class="label label-xl label-inline label-light-danger">
															Baixar Arquivos de XML MDFe
														</span>
													</a>
												</div>
												
											</div>

											<div class="card-body">
												<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

													<table class="datatable-table" style="max-width: 100%; overflow: scroll">
														<thead class="datatable-head">
															<tr class="datatable-row" style="left: 0px;">
																<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">ID</span></th>

																<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 350px;">Chave</span></th>
																<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data</span></th>

															</tr>
														</thead>
														<tbody class="datatable-body">
															@foreach($xmlMdfe as $x)
															<tr class="datatable-row" style="left: 0px;">
																<td class="datatable-cell"><span class="codigo" style="width: 60px;">
																	{{$x->id}}</span></td>

																	<td class="datatable-cell"><span class="codigo" style="width: 350px;">
																		{{$x->chave}}
																	</span></td>
																	<td class="datatable-cell"><span class="codigo" style="width: 100px;">
																		{{ \Carbon\Carbon::parse($x->created_at)->format('d/m/Y H:i:s')}}
																	</span></td>

																</tr>
																@endforeach
															</tbody>
														</table>
													</div>
												</div>

											</div>
										</div>

										@endif

										@if(isset($xml) && isset($xmlNfce) && isset($xmlCte) && isset($xmlMdfe) && 
										count($xml) == 0 && count($xmlNfce) == 0 && count($xmlCte) == 0 && count($xmlMdfe) == 0)
										<h2 style="font-size: 30px; color: red">Nenhum arquivo encontrado</h2>
										@endif

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		@endsection	