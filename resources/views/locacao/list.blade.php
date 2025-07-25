@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<br>
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

			<form method="get" action="/locacao/pesquisa">
				<div class="row align-items-center">

					<div class="form-group col-lg-3 col-md-4 col-sm-6">
						<label class="col-form-label">Cliente</label>
						<div class="">
							<div class="input-group">
								<input type="text" name="cliente" class="form-control" value="{{{isset($cliente) ? $cliente : ''}}}" />
							</div>
						</div>
					</div>

					<div class="form-group col-sm-6 col-lg-4 col-12 col-sm-12 add-prod">
						<label class="col-form-label">Produto</label>

						<div class="input-group">

							<div class="input-group-prepend">
								<span class="input-group-text" id="focus-codigo">
									<li class="la la-search"></li>
								</span>
							</div>

							<select class="form-control select2 produto-search select-search" style="width: 90%" id="kt_select2_1" name="produto">
								<option value="">Digite para buscar o produto</option>
								@if(isset($produto) && $produto != null)
								<option selected value="{{ $produto->id }}">{{ $produto->nome }}</option>
								@endif
							</select>

						</div>
					</div>

					<div class="form-group col-lg-2 col-md-4 col-sm-6">
						<label class="col-form-label">Data inicial</label>
						<div class="">
							<div class="input-group date">
								<input type="text" name="data_inicial" class="form-control date-out" readonly value="{{{isset($dataInicial) ? $dataInicial : ''}}}" id="kt_datepicker_3" />
								<div class="input-group-append">
									<span class="input-group-text">
										<i class="la la-calendar"></i>
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-2 col-md-4 col-sm-6">
						<label class="col-form-label">Data final</label>
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
									<option @if(isset($estado) && $estado == '') selected @endif value="">TODOS</option>
									<option @if(isset($estado) && $estado == '0') selected @endif value="0">NOVO</option>
									<option @if(isset($estado) && $estado == '1') selected @endif value="1">FINALIZADO</option>
								</select>
							</div>
						</div>
					</div>

					<div class="col-lg-2 col-xl-2 mt-2 mt-lg-0">
						<button style="margin-top: 13px;" class="btn btn-light-primary px-6 font-weight-bold">Pesquisa</button>
					</div>
				</div>

			</form>
			<br>
			<h4>Lista de Locações</h4>
			<label>Total de registros: {{sizeof($locacoes)}}</label>

			<div class="row @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">
				<div class="col-12">
					<div class="row">
						<a href="/locacao/novo" class="btn btn-success ml-3 mb-3">
							<i class="la la-plus"></i>
							Nova Locação
						</a>
					</div>
					@isset($pesquisa)
					<div class="row float-right">
						<form target="_blank" method="get" action="/locacao/relatorio">
							<input type="hidden" name="cliente" value="{{{isset($cliente) ? $cliente : ''}}}" />
							<input type="hidden" name="data_inicial" value="{{{isset($dataInicial) ? $dataInicial : ''}}}"/>
							<input type="hidden" name="data_final" value="{{{isset($dataFinal) ? $dataFinal : ''}}}"/>
							<input type="hidden" name="estado" value="{{{isset($estado) ? $estado : ''}}}"/>
							<input type="hidden" name="produto" value="{{{isset($produto) ? $produto->id : ''}}}"/>

							<button class="btn btn-info mr-3">
								<i class="la la-print"></i>
								Imprimir
							</button>
						</form>
					</div>
					@endisset

				</div>

			</div>
			<div class="row">

				@foreach($locacoes as $e)

				<div class="col-sm-12 col-lg-6 col-md-6 col-xl-6">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<div class="card-title">
								<h3 style="width: 230px; font-size: 12px; height: 10px;" class="card-title">
									R$ {{number_format($e->total, 2, ',', '.')}}
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
												<a href="/locacao/edit/{{$e->id}}" class="navi-link">
													<span class="navi-text">
														<span class="label label-xl label-inline label-light-warning">Editar</span>
													</span>
												</a>
											</li>
											<li class="navi-item">
												<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/locacao/delete/{{ $e->id }}" }else{return false} })' href="#!" class="navi-link">
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
								<span class="kt-widget__label">Cliente:</span>
								<a target="_blank" class="kt-widget__data text-success">
									{{$e->cliente->razao_social}}
								</a>
							</div>
							<div class="kt-widget__info">
								<span class="kt-widget__label">Status:</span>
								<a target="_blank" class="kt-widget__data text-success">
									@if($e->status)
									<span class="label label-xl label-inline label-light-success">FINALIZADO</span>
									@else
									<span class="label label-xl label-inline label-light-primary">NOVO</span>
									@endif
								</a>
							</div>
							<div class="kt-widget__info">
								<span class="kt-widget__label">Início:</span>
								<a target="_blank" class="kt-widget__data text-success">
									{{ \Carbon\Carbon::parse($e->inicio)->format('d/m/Y')}}
								</a>
							</div>
							<div class="kt-widget__info">
								<span class="kt-widget__label">Fim:</span>
								<a target="_blank" class="kt-widget__data text-danger">
									@if($e->fim != '1969-12-31')
									{{ \Carbon\Carbon::parse($e->fim)->format('d/m/Y')}}
									@else
									--
									@endif
								</a>
							</div>

						</div>

						<div class="card-footer">
							<a style="width: 100%;" href="/locacao/itens/{{$e->id}}" class="btn btn-light-primary">
								Detalhes
							</a>
						</div>
					</div>
				</div>

				@endforeach

			</div>

			<div class="d-flex justify-content-between align-items-center flex-wrap">
				<div class="d-flex flex-wrap py-2 mr-3">
					@if(isset($links))
					{{$locacoes->links()}}
					@endif
				</div>
			</div>
		</div>

	</div>
</div>

@section('javascript')
<script type="text/javascript">
	$(function(){
		setTimeout(() => {
			$("#kt_select2_1").select2({
				minimumInputLength: 2,
				language: "pt-BR",
				placeholder: "Digite para buscar o produto",
				width: "90%",
				ajax: {
					cache: true,
					url: path + 'produtos/autocomplete',
					dataType: "json",
					data: function(params) {
						console.clear()
						let filial = $('#filial').val()
						let lista_id = $('#lista_id').val()


						var query = {
							pesquisa: params.term,
							filial_id: filial,
							lista_id: lista_id
						};
						return query;
					},
					processResults: function(response) {

						var results = [];

						$.each(response, function(i, v) {
							var o = {};
							o.id = v.id;

							o.text = v.nome + (v.grade ? " "+v.str_grade : "") + " | R$ " + parseFloat(v.valor_venda).toFixed(2).replace(".", ",")
							+ (v.referencia != "" ? " - Ref: " + v.referencia: "") + (parseFloat(v.estoqueAtual) > 0 ? " | Estoque: " + v.estoqueAtual : "");
							o.value = v.id;
							results.push(o);
						});
						return {
							results: results
						};
					}
				}
			});

			$('.select2-selection__arrow').addClass('select2-selection__arroww')
			$('.select2-selection__arrow').removeClass('select2-selection__arrow')
		}, 10)
	})
</script>
@endsection
@endsection