@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-12">

				<div class="row">
					<a style="margin-left: 5px; margin-top: 5px;" href="/produtos/new" class="btn btn-lg btn-success">
						<i class="fa fa-plus"></i>Novo Produto
					</a>

					<a style="margin-left: 5px; margin-top: 5px;" href="/produtos/importacao" class="btn btn-lg btn-danger">
						<i class="fa fa-arrow-up"></i>Importação
					</a>

					<a style="margin-left: 5px; margin-top: 5px;" href="/divisaoGrade" class="btn btn-lg btn-info">
						<i class="fa fa-th"></i>Divisao de Grade
					</a>

					@if(sizeof($produtos) > 0)
					<a style="margin-left: 5px; margin-top: 5px;" href="/percentualuf" class="btn btn-lg btn-warning">
						<i class="fa fa-percent"></i>Tributação por estado
					</a>
					@endif

					<a style="margin-left: 5px; margin-top: 5px;" href="/produtos/exportacao" class="btn btn-lg btn-primary">
						<i class="fa fa-arrow-down"></i>Exportar Excel
					</a>

					<a style="margin-left: 5px; margin-top: 5px;" href="/produtos/exportacaoBalanca" class="btn btn-lg btn-dark">
						<i class="fa fa-arrow-down"></i>Exportar Balança
					</a>

					<a style="margin-left: 5px; margin-top: 5px;" href="/produtos/alterar-tributacao" class="btn btn-lg btn-light">
						<i class="fa fa-bars"></i>Alterar tributação
					</a>

				</div>
			</div>
		</div>
		<br>

		@isset($paraImprimir)
		<form method="get" action="/produtos/relatorio">
			<input type="hidden" name="pesquisa" value="{{{ isset($pesquisa) ? $pesquisa : '' }}}">
			<input type="hidden" name="categoria" value="{{$categoria}}">
			<input type="hidden" name="tipo" value="{{$tipo}}">
			<input type="hidden" name="marca" value="{{$marca}}">
			
			<input type="hidden" name="estoque" value="{{ $estoque }}">
			<input type="hidden" name="filial_id" value="{{ $filial_id }}">
			<button class="btn btn-lg btn-info ml-1 mt-2">
				<i class="fa fa-print"></i>Imprimir relatório
			</button>
		</form>
		@endisset

		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

			<form method="get" action="/produtos/filtroCategoria">
				<div class="row align-items-center">
					<div class="form-group col-lg-3 col-xl-2">
						<label class="col-form-label">Tipo de pesquisa</label>
						<div>
							<div class="input-group">
								<select class="form-control" name="tipo">
									<option @if(isset($tipo)) @if($tipo == 'nome')
									selected
									@endif
									@endif value="nome">Nome</option>
									<option @if(isset($tipo)) @if($tipo == 'referencia')
									selected
									@endif
									@endif value="referencia">Referência</option>
									<option @if(isset($tipo)) @if($tipo == 'cod_barras')
									selected
									@endif
									@endif value="cod_barras">Código de barras</option>
								</select>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-4 col-xl-4">
						<label class="col-form-label">Produto</label>
						<div>
							<div class="input-group">
								<input type="text" name="pesquisa" class="form-control" value="{{{isset($pesquisa) ? $pesquisa : ''}}}"
								placeholder="Produto...">
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-xl-2">

						<label class="col-form-label">Categoria</label>
						<div>
							<div class="input-group">
								<select class="form-control select2" id="kt_select2_1" name="categoria">
									<option value="-">Todas</option>
									@foreach($categorias as $c)
									<option @if(isset($categoria)) @if($c->id == $categoria)
										selected
										@endif
										@endif
										value="{{$c->id}}">{{$c->nome}}
									</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<div class="form-group col-lg-3 col-xl-2">

						<label class="col-form-label">Marca</label>
						<div>
							<div class="input-group">
								<select class="form-control select2" id="kt_select2_2" name="marca">
									<option value="-">Todas</option>
									@foreach($marcas as $c)
									<option @if(isset($marca)) @if($c->id == $marca)
										selected
										@endif
										@endif
										value="{{$c->id}}">{{$c->nome}}
									</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-2 col-xl-2">
						<label class="col-form-label">Estoque</label>
						<div>
							<div class="input-group">
								<select class="form-control custom-select" name="estoque">
									<option value="--">--</option>
									<option @if(isset($estoque) && $estoque == 1) selected @endif value="1">Positivo</option>
									<option @if(isset($estoque) && $estoque == -1) selected @endif value="-1">Negativo</option>
								</select>
							</div>
						</div>
					</div>

					@if(empresaComFilial())
					{!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
					@endif

					<div class="col-lg-2 col-xl-2 mt-4">
						<button type="submit" class="btn btn-light-primary font-weight-bold">Filtrar</button>
					</div>
				</div>

			</form>

			<br>
			<h4>Lista de Produtos</h4>

			@if(!isset($categoria))
			<p>Média % de lucro todos os produtos: <strong>{{App\Models\Produto::mediaLucro()}}</strong></p>

			<!-- <label>Total de produtos cadastrados: <strong class="text-info">{{($produtos->total())}}</strong></label> -->
			@endif
			<p class="text-danger">Produtos em vermelho inativos</p>

			<div class="row">
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

						<!-- inicio grid -->
						<div class="pb-5" data-wizard-type="step-content">
							<div class="row">
								<div class="col-xl-12">

									<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

										<table class="datatable-table" style="max-width: 100%; overflow: scroll">
											<thead class="datatable-head">
												<tr class="datatable-row" style="left: 0px;">
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 300px;">AÇÕES</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 250px;">NOME</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">VALOR DE VENDA</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">VALOR DE COMPRA</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">UN. COMPRA/VENDA</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">DATA DE CADASTRO</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">GERENCIAR ESTOQUE</span></th>
													@if(empresaComFilial())
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">DISPONIBILIDADE</span></th>
													@endif
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">ESTOQUE</span></th>
													
												</tr>
											</thead>

											<tbody id="body" class="datatable-body">
												@foreach($produtos as $p)
												<tr class="datatable-row" @if($p->inativo) style="background: #ffcdd2;" @endif>

													<td class="datatable-cell">
														<span class="codigo" style="width: 300px;" id="id">
															<a title="Editar" class="btn btn-sm btn-warning" onclick='swal("Atenção!", "Deseja editar este registro?", "warning").then((sim) => {if(sim){ location.href="/produtos/edit/{{ $p->id }}" }else{return false} })' href="#!">
																<i class="la la-edit"></i>	
															</a>
															<a title="Remover" class="btn btn-sm btn-danger" onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/produtos/delete/{{ $p->id }}" }else{return false} })' href="#!">
																<i class="la la-trash"></i>	
															</a>
															@if($p->composto)
															<a title="Receita/Composição" class="btn btn-sm btn-primary" href="/produtos/receita/{{ $p->id }}">
																<i class="la la-list"></i>	
															</a>
															@endif

															@if($p->grade)
															<a title="Grade" class="btn btn-sm btn-primary" href="/produtos/grade/{{ $p->id }}">
																<i class="la la-th"></i>	
															</a>
															@endif

															<a title="Movimentação" class="btn btn-sm btn-info" href="/produtos/movimentacao/{{ $p->id }}">
																<i class="las la-tasks"></i>
															</a>

															<a title="Duplicar" class="btn btn-sm btn-primary" onclick='swal("Atenção!", "Deseja duplicar este registro?", "warning").then((sim) => {if(sim){ location.href="/produtos/duplicar/{{ $p->id }}" }else{return false} })' href="#!">
																<i class="la la-copy"></i>	
															</a>

															@if($p->ecommerce)
															<a title="Ecommerce" title="Ecommerce" class="btn btn-sm btn-info" href="/produtoEcommerce/edit/{{ $p->ecommerce->id }}">
																<i class="la la-shopping-cart"></i>
															</a>
															@endif

															<a title="Gerar etiqueta(s)" class="btn btn-sm btn-dark" href="/produtos/etiqueta/{{ $p->id }}">
																<i class="la la-barcode"></i>
															</a>
														</span>
													</td>
													<td class="datatable-cell"><span class="codigo" style="width: 250px;" id="id">{{$p->nome}} {{ $p->referencia != "" ? ' - #'.$p->referencia : ''}}</span>
													</td>

													@if($p->grade)
													<td class="datatable-cell">
														<span class="codigo" style="width: 100px;" id="id">
															--
														</span>
													</td>
													@else
													<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">{{number_format($p->valor_venda, $casasDecimais, ',', '.')}}</span>
													</td>
													@endif
													<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">{{number_format($p->valor_compra, $casasDecimais, ',', '.')}}</span>
													</td>
													<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">{{$p->unidade_compra}}/{{$p->unidade_venda}}</span>
													</td>
													<td class="datatable-cell"><span class="codigo" style="width: 120px;">{{\Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i')}}</span>
													</td>
													<td class="datatable-cell">
														<span class="codigo" style="width: 100px;" id="id">
															@if($p->gerenciar_estoque)
															<span class="label label-xl label-inline label-success">Sim</span>
															@else
															<span class="label label-xl label-inline label-warning">Não</span>
															@endif
														</span>
													</td>

													@if(empresaComFilial())
													<td class="datatable-cell">
														<span class="codigo" style="width: 200px;">
															{!! $p->locais_produto() !!}
														</span>
													</td>
													@endif

													<td class="datatable-cell">
														<span class="codigo" style="width: 200px;" id="id">
															{{ $p->estoquePorLocal($filial_id ?? null) }}
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

								@foreach($produtos as $p)

								<div class="col-sm-12 col-lg-6 col-md-6 col-xl-4">
									<div class="card card-custom gutter-b example example-compact">
										<div class="card-header">
											<div class="card-title">
												<div class="flex-shrink-0 mr-4 mt-lg-0 mt-3">
													<div class="symbol symbol-circle symbol-lg-75">
														@if($p->imagem != '')
														<img src="/imgs_produtos/{{$p->imagem}}" alt="image">
														@else
														<img src="/imgs/no_image.png" alt="image">
														@endif

													</div>
												</div>
												<h3 style="width: 230px; font-size: 12px; height: 10px;" class="card-title">{{substr($p->nome, 0, 30)}}
												</h3>

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
																<a href="/produtos/edit/{{$p->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-primary">Editar</span>
																	</span>
																</a>
															</li>
															<li class="navi-item">
																<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/produtos/delete/{{ $p->id }}" }else{return false} })' href="#!" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-danger">Excluir</span>
																	</span>
																</a>
															</li>

															@if($p->composto)
															<li class="navi-item">
																<a href="/produtos/receita/{{$p->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-warning">Receita/Composição</span>
																	</span>
																</a>
															</li>
															@endif

															@if($p->grade)
															<li class="navi-item">
																<a href="/produtos/grade/{{$p->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-warning">Grade</span>
																	</span>
																</a>
															</li>
															@endif

															<li class="navi-item">
																<a href="/produtos/movimentacao/{{$p->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-info">Movimentacao</span>
																	</span>
																</a>
															</li>

															<li class="navi-item">
																<a href="/produtos/etiqueta/{{$p->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-dark">Etiqueta</span>
																	</span>
																</a>
															</li>

															@if($p->ecommerce)

															<li class="navi-item">
																<a href="/produtoEcommerce/edit/{{$p->ecommerce->id}}" class="navi-link">
																	<span class="navi-text">
																		<span class="label label-xl label-inline label-light-dark">Ecommerce</span>
																	</span>
																</a>
															</li>
															@endif

														</ul>

													</div>
												</div>


											</div>
										</div>

										<div class="card-body">

											<div class="kt-widget__info">
												<span class="kt-widget__label">Categoria:</span>
												<a target="_blank" href="/categorias/edit/{{ $p->categoria->id }}" class="kt-widget__data text-success">{{ $p->categoria->nome }}</a>
											</div>

											<div class="kt-widget__info">
												<span class="kt-widget__label">Referência:</span>
												<a target="_blank" href="/categorias/edit/{{ $p->categoria->id }}" class="kt-widget__data text-info">#{{ $p->referencia }}</a>
											</div>
											<div class="kt-widget__info">
												<span class="kt-widget__label">Valor de venda:</span>
												<a class="kt-widget__data text-success">{{ number_format($p->valor_venda, $casasDecimais, ',', '.') }}</a>
											</div>
											<div class="kt-widget__info">
												<span class="kt-widget__label">Valor de compra:</span>
												<a class="kt-widget__data text-success">{{ number_format($p->valor_compra, $casasDecimais, ',', '.') }}</a>
											</div>
											<div class="kt-widget__info">
												<span class="kt-widget__label">% Lucro:</span>
												<a class="kt-widget__data text-success">{{ $p->percentual_lucro }}%</a>
											</div>
											<div class="kt-widget__info">
												<span class="kt-widget__label">Unidade:</span>
												<a class="kt-widget__data text-success">{{$p->unidade_compra}}/{{$p->unidade_venda}}</a>
											</div>
											<div class="kt-widget__info">
												<span class="kt-widget__label">Data de cadastro:</span>
												<a class="kt-widget__data text-success">{{\Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i')}}</a>
											</div>
											<div class="kt-widget__info">
												<span class="kt-widget__label">Gerenciar estoque:</span>
												@if($p->gerenciar_estoque)
												<span class="label label-xl label-inline label-light-success">Sim</span>
												@else
												<span class="label label-xl label-inline label-light-warning">Não</span>
												@endif
											</div>
											<div class="kt-widget__info">
												<span class="kt-widget__label">Tipo grade:</span>
												@if($p->grade)
												<span class="label label-xl label-inline label-light-success">Sim</span>
												@else
												<span class="label label-xl label-inline label-light-warning">Não</span>
												@endif
											</div>

											@if(empresaComFilial())
											<div class="kt-widget__info">
												<span class="kt-widget__label">Disponibilidade:</span>
												<strong>{!! $p->locais_produto() !!}</strong>
											</div>
											@endif

											<div class="kt-widget__info">
												<span class="kt-widget__label">Estoque:</span>
												<a class="kt-widget__data text-success">

													{{ $p->estoquePorLocal($filial_id ?? null) }}
												</a>
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
					{{$produtos->links()}}
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
@section('javascript')
<script type="text/javascript">
	$('.btn-ibpt').click(() => {
		$('.btn-ibpt').addClass('spinner')
	})
</script>
@endsection