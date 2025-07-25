@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-sm-12 col-lg-4 col-md-6 col-xl-4">

				<a href="/impressoras/new" class="btn btn-lg btn-success">
					<i class="fa fa-plus"></i>Nova Impressora
				</a>
			</div>
		</div>
		<br>
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

			<br>
			<h4>Lista de Impressoras</h4>
			<label>Total de registros: {{count($data)}}</label>
			<div class="row">

				<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

					<div class="wizard wizard-3" id="kt_wizard_v3" data-wizard-state="between" data-wizard-clickable="true">
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

						<div class="pb-5" data-wizard-type="step-content">
							<div class="row">
								<div class="col-xl-12">

									<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

										<table class="datatable-table" style="max-width: 100%; overflow: scroll">
											<thead class="datatable-head">
												<tr class="datatable-row" style="left: 0px;">
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 250px;">DESCRIÇÃO</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">PORTA</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">STATUS</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">PADRÃO</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">AÇÕES</span></th>
												</tr>
											</thead>
											<tbody id="body" class="datatable-body">
												@foreach($data as $d)
												<tr class="datatable-row">
													<td class="datatable-cell"><span class="codigo" style="width: 250px;" id="id">{{$d->descricao}}</span>
													</td>
													<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">{{$d->porta}}</span>
													</td>
													<td class="datatable-cell">
														<span class="codigo" style="width: 100px;" id="id">
															@if($d->status)
															<span class="label label-xl label-inline label-light-success">Ativo</span>
															@else
															<span class="label label-xl label-inline label-light-danger">Inátivo</span>

															@endif
														</span>
													</td>
													<td class="datatable-cell">
														<span class="codigo" style="width: 100px;" id="id">
															@if($d->padrao)
															<span class="label label-xl label-inline label-light-success">Sim</span>
															@else
															--
															@endif
														</span>
													</td>

													<td class="datatable-cell">
														<span class="codigo" style="width: 200px;" id="id">
															<a class="btn btn-warning" onclick='swal("Atenção!", "Deseja editar este registro?", "warning").then((sim) => {if(sim){ location.href="/impressoras/edit/{{ $d->id }}" }else{return false} })' href="#!">
																<i class="la la-edit"></i>	
															</a>
															<a class="btn btn-danger" onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/impressoras/delete/{{ $d->id }}" }else{return false} })' href="#!">
																<i class="la la-trash"></i>	
															</a>
															
														</span>
													</td>
												</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>

						<div class="pb-5" data-wizard-type="step-content">
							<div class="row">
								@foreach($data as $d)


								<div class="col-sm-12 col-lg-6 col-md-6 col-xl-4">
									<div class="card card-custom gutter-b example example-compact">
										<div class="card-header">
											<div class="card-title">
												<h3 style="width: 230px; font-size: 12px; height: 10px;" class="card-title">{{substr($d->descricao, 0, 40)}}
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
															<li class="navi-item">
																<a href="/impressoras/edit/{{$d->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-primary">Editar</span>
																	</span>
																</a>
															</li>
															<li class="navi-item">
																<a onclick = "if (! confirm('Deseja excluir este registro?')) { return false; }" href="/impressoras/delete/{{$d->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-danger">Excluir</span>
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
												<span class="kt-widget__label">Porta:</span>
												<a target="_blank" class="kt-widget__data text-success">{{ $d->porta }}</a>
											</div>

										</div>

									</div>

								</div>

								@endforeach
							</div>
						</div>
					</div>
				</div>

			</div>

			<div class="d-flex justify-content-between align-items-center flex-wrap">
				<div class="d-flex flex-wrap py-2 mr-3">
					@if(isset($links))
					{{$servicos->links()}}
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

@endsection