@extends('default.layout')
@section('content')

<style type="text/css">
	.btn-file {
		position: relative;
		overflow: hidden;
	}

	.btn-file input[type=file] {
		position: absolute;
		top: 0;
		right: 0;
		min-width: 100%;
		min-height: 100%;
		font-size: 100px;
		text-align: right;
		filter: alpha(opacity=0);
		opacity: 0;
		outline: none;
		background: white;
		cursor: inherit;
		display: block;
	}
</style>
<div class="card card-custom gutter-b">


	<div class="card-body">
		<div class="content d-flex flex-column flex-column-fluid" id="kt_content" >

			<div class="row" id="anime" style="display: none">
				<div class="col s8 offset-s2">
					<lottie-player src="/anime/{{\App\Models\Venda::randSuccess()}}" background="transparent" speed="0.8" style="width: 100%; height: 300px;" autoplay >
					</lottie-player>
				</div>
			</div>

			<div class="col-lg-12" id="content">
				<h1 class="text-success">EMISSÃO DE CTe</h1>
				<h3 class="card-title">DADOS INICIAIS</h3>

				<input type="hidden" id="clientes" value="{{json_encode($clientes)}}" name="">
				<input type="hidden" id="_token" value="{{csrf_token()}}" name="">
				<div class="row">
					<div class="col-xl-12">

						<div class="kt-section kt-section--first">
							<div class="kt-section__body">
								@if(!empresaComFilial())
								<div class="row">
									<div class="col-lg-4 col-md-4 col-sm-6">

										<h6>Ultima CTe: <strong>{{$lastCte}}</strong></h6>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-6">

										@if($config->ambiente == 2)
										<h6>Ambiente: <strong class="text-primary">Homologação</strong></h6>
										@else
										<h6>Ambiente: <strong class="text-success">Produção</strong></h6>
										@endif
									</div>
								</div>
								@endif

								<div class="row">

									{!! __view_locais_select() !!}
									
									<div class="form-group col-lg-4 col-md-4 col-sm-6">
										<label class="col-form-label">Natureza de Operação</label>
										<div class="">
											<div class="input-group date">
												<select class="custom-select form-control" id="natureza" name="natureza">
													@foreach($naturezas as $n)
													<option 
													@if($config->nat_op_padrao == $n->id)
													selected
													@endif
													value="{{$n->id}}">{{$n->natureza}}</option>
													@endforeach
												</select>
											</div>
										</div>
									</div>

									<div class="form-group validated col-lg-3 col-12">
										<label class="col-form-label" id="">Tipo de serviço</label>
										<select class="custom-select form-control" id="tipo_servico" name="tipo_servico">
											@foreach(App\Models\Cte::tiposServico() as $key => $t)
											<option @isset($cte) @if($cte->tipo_servico == $key) selected @endif @endif value="{{ $key }}">{{ $t }}</option>
											@endforeach
										</select>
									</div>

									<div class="form-group validated col-sm-3 col-lg-2 col-12">
										<label class="col-form-label" id="">Tipo globalzado</label>
										<select class="custom-select form-control" id="globalizado" name="globalizado">
											<option @if(isset($cte))
											@if($cte->globalizado == 0)
											selected
											@endif
											@endif value="0">Não</option>
											<option @if(isset($cte))
											@if($cte->globalizado == 1)
											selected
											@endif
											@endif value="1">Sim</option>
										</select>
									</div>

									@if($tributacao->regime == 1)
									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<label class="col-form-label">CST</label>
										<select class="custom-select form-control" id="cst" name="cst">
											@foreach(App\Models\Cte::getCsts() as $key => $c)
											<option @if(isset($cte)) @if($key == $cte->cst) selected @endif @endif value="{{$key}}">{{$c}}</option>
											@endforeach
										</select>
									</div>
									@else
									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<label class="col-form-label">Contribuinte</label>
										<select class="custom-select form-control" id="cst" name="cst">
											@foreach(App\Models\Cte::getCsosn() as $key => $c)
											<option @if(isset($cte)) @if($key == $cte->cst) selected @endif @endif value="{{$key}}">{{$c}}</option>
											@endforeach
										</select>
									</div>
									@endif

									<div class="form-group col-sm-3 col-lg-2 col-12">
										<label class="col-form-label">%ICMS</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="perc_icms" class="form-control type-ref money-p" value="@if(isset($cte)) {{$cte->perc_icms}} @else 0 @endif" id="perc_icms"/>
											</div>
										</div>
									</div>

									<div class="form-group col-sm-3 col-lg-2 col-12">
										<label class="col-form-label">%Red. BC</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="pRedBC" class="form-control type-ref money-p" value="@if(isset($cte)) {{$cte->pRedBC}} @else 0 @endif" id="pRedBC"/>
											</div>
										</div>
									</div>
									
								</div>

								<div class="row">
									<div class="form-group validated col-sm-6 col-lg-6 col-12">
										<label class="col-form-label" id="">Remetente</label><br>
										<select class="form-control select2" style="width: 100%" id="kt_select2_1" name="cliente">
											<option value="null">Selecione o Remetente</option>
											@foreach($clientes as $c)
											<option @if($dadosDaNFe['remetente'] == $c->id) selected @endif value="{{$c->id}}">{{$c->id}} - {{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
											@endforeach
										</select>

										<hr>
										<div class="row" id="info-remetente" style="display: block">
											<div class="col-xl-12">

												<div class="card card-custom gutter-b">
													<div class="card-body">

														<h4 class="center-align">REMENTE SELECIONADO</h4>



														<h6>Razao Social: <strong id="nome-remetente" class="text-info"></strong></h6>

														<h6>CNPJ: <strong id="cnpj-remetente" class="text-info"></strong>
														</h6>

														<h6>IE: <strong id="ie-remetente" class="text-info"></strong>
														</h6>

														<h6>Rua: <strong id="rua-remetente" class="text-info"></strong>
														</h6>
														<h6>Nro: <strong id="nro-remetente" class="text-info"></strong>
														</h6>
														<h6>Bairro: <strong id="bairro-remetente" class="text-info"></strong>
														</h6>
														<h6>Cidade: <strong id="cidade-remetente" class="text-info"></strong>
														</h6>
													</div>

												</div>
											</div>

										</div>
									</div>

									<div class="form-group validated col-sm-6 col-lg-6 col-12">
										<label class="col-form-label" id="">Destinatário</label><br>
										<select class="form-control select2" style="width: 100%" id="kt_select2_2" name="cliente">
											<option value="null">Selecione o Destinatário</option>
											@foreach($clientes as $c)
											<option @if($dadosDaNFe['destinatario'] == $c->id) selected @endif value="{{$c->id}}">{{$c->id}} - {{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
											@endforeach
										</select>
										<hr>
										<div class="row" id="info-destinatario" style="display: block">
											<div class="col-xl-12">

												<div class="card card-custom gutter-b">
													<div class="card-body">

														<h4 class="center-align">DESTINÁTARIO SELECIONADO</h4>



														<h6>Razao Social: <strong id="nome-destinatario" class="text-danger"></strong></h6>

														<h6>CNPJ: <strong id="cnpj-destinatario" class="text-danger"></strong>
														</h6>

														<h6>IE: <strong id="ie-destinatario" class="text-danger"></strong>
														</h6>

														<h6>Rua: <strong id="rua-destinatario" class="text-danger"></strong>
														</h6>
														<h6>Nro: <strong id="nro-destinatario" class="text-danger"></strong>
														</h6>
														<h6>Bairro: <strong id="bairro-destinatario" class="text-danger"></strong>
														</h6>
														<h6>Cidade: <strong id="cidade-destinatario" class="text-danger"></strong>
														</h6>
													</div>

												</div>
											</div>

										</div>
									</div>

									<div class="form-group validated col-sm-6 col-lg-6 col-12">
										
										<label class="col-form-label" id="">Expedidor</label>
										<div class="input-group">
											<div class="input-group-prepend w-100">
												<select class="form-control select2" style="width: 100%" id="kt_select2_10" name="cliente">
													<option value="null">Selecione o Expedidor</option>
													@foreach($clientes as $c)
													<option @if(isset($cte)) @if($cte->expedidor_id == $c->id) selected @endif @endif value="{{$c->id}}">{{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
													@endforeach
												</select>
												<button type="button" onclick="novoExpedidor()" class="btn btn-warning btn-sm">
													<i class="la la-plus-circle icon-add"></i>
												</button>
											</div>
										</div>

										<hr>
										<div class="row" id="info-expedidor" style="display: none">
											<div class="col-xl-12">

												<div class="card card-custom gutter-b">
													<div class="card-body">

														<h4 class="center-align">EXPEDIDOR SELECIONADO</h4>
														<h6>Razao Social: <strong id="nome-expedidor" class="text-info"></strong></h6>

														<h6>CNPJ: <strong id="cnpj-expedidor" class="text-info"></strong>
														</h6>

														<h6>IE: <strong id="ie-expedidor" class="text-info"></strong>
														</h6>

														<h6>Rua: <strong id="rua-expedidor" class="text-info"></strong>
														</h6>
														<h6>Nro: <strong id="nro-expedidor" class="text-info"></strong>
														</h6>
														<h6>Bairro: <strong id="bairro-expedidor" class="text-info"></strong>
														</h6>
														<h6>Cidade: <strong id="cidade-expedidor" class="text-info"></strong>
														</h6>
													</div>

												</div>
											</div>

										</div>
									</div>

									<div class="form-group validated col-sm-6 col-lg-6 col-12">
										
										<label class="col-form-label" id="">Recebedor</label>
										<div class="input-group">
											<div class="input-group-prepend w-100">
												<select class="form-control select2" style="width: 100%" id="kt_select2_11" name="cliente">
													<option value="null">Selecione o Recebedor</option>
													@foreach($clientes as $c)
													<option @if(isset($cte)) @if($cte->recebedor_id == $c->id) selected @endif @endif value="{{$c->id}}">{{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
													@endforeach
												</select>
												<button type="button" onclick="novoRecebedor()" class="btn btn-dark btn-sm">
													<i class="la la-plus-circle icon-add"></i>
												</button>
											</div>
										</div>

										<hr>
										<div class="row" id="info-recebedor" style="display: none">
											<div class="col-xl-12">

												<div class="card card-custom gutter-b">
													<div class="card-body">

														<h4 class="center-align">RECEBEDOR SELECIONADO</h4>
														<h6>Razao Social: <strong id="nome-recebedor" class="text-info"></strong></h6>

														<h6>CNPJ: <strong id="cnpj-recebedor" class="text-info"></strong>
														</h6>

														<h6>IE: <strong id="ie-recebedor" class="text-info"></strong>
														</h6>

														<h6>Rua: <strong id="rua-recebedor" class="text-info"></strong>
														</h6>
														<h6>Nro: <strong id="nro-recebedor" class="text-info"></strong>
														</h6>
														<h6>Bairro: <strong id="bairro-recebedor" class="text-info"></strong>
														</h6>
														<h6>Cidade: <strong id="cidade-recebedor" class="text-info"></strong>
														</h6>
													</div>

												</div>
											</div>

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="card card-custom gutter-b">

					<div class="card-body">

						<div class="row">
							<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

								<div class="wizard wizard-3" id="kt_wizard_v3" data-wizard-state="between" data-wizard-clickable="true">
									<!--begin: Wizard Nav-->

									<div class="wizard-nav">
										<h1>Referência de Documento para CTe</h1>
										<div class="wizard-steps px-8 py-8 px-lg-15 py-lg-3">
											<!--begin::Wizard Step 1 Nav-->
											<div class="wizard-step" data-wizard-type="step" data-wizard-state="done">
												<div class="wizard-label">
													<h3 class="wizard-title">
														<span>
															NF-e
														</span>
													</h3>
													<div class="wizard-bar"></div>
												</div>
											</div>
											<!--end::Wizard Step 1 Nav-->
											<!--begin::Wizard Step 2 Nav-->
											<div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
												<div class="wizard-label">
													<h3 class="wizard-title">
														<span>
															Outros
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
											<div class="container">
												<div class="pb-5" data-wizard-type="step-content">

													<!-- Inicio da tabela -->
													<input type="hidden" id="chave_import" value="{{$dadosDaNFe['chave']}}" name="">
													<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
														<div class="row">
															<div class="col-xl-12">
																<div class="row align-items-center">
																	<div class="form-group col-lg-10 col-md-12 col-sm-10 col-10">
																		<label class="col-form-label">Chave da NF-e</label>
																		<div class="">
																			<div class="input-group">
																				<input type="text" name="chave_nfe" class="form-control" value="@isset($cte) $cte->chave_nfe @endisset" id="chave_nfe"/>
																			</div>
																		</div>
																	</div>

																	<div class="form-group col-2">
																		<br>
																		<a onclick="addNfeRef()" style="margin-top: 12px;" class="btn btn-info">
																			<i class="la la-plus"></i>
																		</a>
																	</div>
																</div>
															</div>
														</div>
														<!-- chaves referencia -->
														<div class="row" id="chaves_nfe">
															
														</div>
													</div>

													
												</div>


												<!--begin: Wizard Step 1-->
												<div class="pb-5" data-wizard-type="step-content">

													<!-- Inicio da tabela -->

													<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
														<div class="row">
															<div class="col-xl-12">
																<div class="row align-items-center">

																	<div class="form-group validated col-sm-3 col-lg-3 col-12">
																		<label class="col-form-label" id="">Tipo</label>
																		<select class="custom-select form-control" id="tpDoc" name="tpDoc">
																			<option value="00">Declaração</option>
																			<option value="10">Dutoviário</option>
																			<option value="59">CF-e SAT</option>
																			<option value="65">NFC-e</option>
																			<option value="99">Outros</option>
																		</select>
																	</div>

																	<div class="form-group col-sm-3 col-lg-3 col-12">
																		<label class="col-form-label">Descrição doc.</label>
																		<div class="">
																			<div class="input-group">
																				<input type="text" name="descOutros" class="form-control type-ref" value="" id="descOutros"/>
																			</div>
																		</div>
																	</div>
																	<div class="form-group col-sm-2 col-lg-2 col-12">
																		<label class="col-form-label">Número documento</label>
																		<div class="">
																			<div class="input-group">
																				<input type="text" name="nDoc" class="form-control type-ref" value="" id="nDoc"/>
																			</div>
																		</div>
																	</div>
																	<div class="form-group col-sm-3 col-lg-3 col-12">
																		<label class="col-form-label">Valor documento</label>
																		<div class="">
																			<div class="input-group">
																				<input type="text" name="vDocFisc" class="form-control type-ref money" value="" id="vDocFisc"/>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</form>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="card card-custom gutter-b">

					<div class="card-body">

						<div class="row">
							<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

								<h1>Informações da Carga</h1>

								<div class="row">

									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<label class="col-form-label" id="">Veiculo</label>
										<select class="custom-select form-control" id="veiculo_id" name="veiculo_id">
											@foreach($veiculos as $v)
											<option value="{{$v->id}}">{{$v->modelo}} {{$v->placa}}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 col-lg-4 col-12">
										<label class="col-form-label">Produto predominante</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="prod_predominante" class="form-control type-ref" value="{{$dadosDaNFe['produto_predominante']}}"  id="prod_predominante"/>
											</div>
										</div>
									</div>
									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<label class="col-form-label" id="">Tomador</label>
										<select class="custom-select form-control" id="tomador" name="tomador">
											@foreach($tiposTomador as $key => $t)
											<option value="{{$key}}">{{$key ."-".$t}}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="row align-items-center">
									<div class="form-group col-sm-3 col-lg-3 col-12">
										<label class="col-form-label">Valor da carga</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="valor_carga" class="form-control type-ref" value="{{$dadosDaNFe['valor_carga']}}" id="valor_carga"/>
											</div>
										</div>
									</div>
									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<label class="col-form-label" id="">Modelo de transporte</label>
										<select class="custom-select form-control" id="modal-transp" name="modal-transp">
											@foreach($modals as $key => $t)
											<option value="{{$key}}">{{$key ."-".$t}}</option>
											@endforeach
										</select>
									</div>

								</div>

								<hr>
								<h4 class="text-info">Informações de Quantidade</h4>
								<div class="row align-items-center">

									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<label class="col-form-label" id="">Unidade de medida</label>
										<select class="custom-select form-control" id="unidade_medida" name="unidade_medida">
											@foreach($unidadesMedida as $key => $u)
											<option @if($key == $dadosDaNFe['unidade']) selected @endif value="{{$key}}-{{$u}}">{{$key}}-{{$u}}</option>
											@endforeach
										</select>
									</div>

									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<label class="col-form-label" id="">Tipo de medida</label>
										<select class="custom-select form-control" id="tipo_medida" name="tipo_medida">
											@foreach($tiposMedida as $u)
											<option value="{{$u}}">{{$u}}</option>
											@endforeach
										</select>
									</div>
									<input type="hidden" value="{{csrf_token()}}" id="_token">

									<div class="form-group col-sm-2 col-lg-2 col-12">
										<label class="col-form-label">Quantidade</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="quantidade_carga" class="form-control type-ref" value="{{$dadosDaNFe['quantidade']}}" id="quantidade_carga"/>
											</div>
										</div>
									</div>
									<div class="col-lg-1 col-md-4 col-sm-6 col-6">
										<a href="#!" style="margin-top: 10px;" id="addMedida" class="btn btn-light-success px-6 font-weight-bold">
											<i class="la la-plus"></i>
										</a>

									</div>

								</div>

								<div class="container">
									<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

										<table class="datatable-table" style="max-width: 100%; overflow: scroll" id="prod">
											<thead class="datatable-head">
												<tr class="datatable-row" style="left: 0px;">
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Item</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Código unidade</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Tipo de medida</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Quantidade</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Ação</span></th>
												</tr>
											</thead>
											<tbody id="body" class="datatable-body">
												<tr class="datatable-row">
												</tr>
											</tbody>
										</table>
									</div>
								</div>

								<hr>
								<h4 class="text-info">Componentes da carga</h4>
								<p class="text-danger">*A soma dos valores dos componentes deve ser igual ao valor a receber</p>
								<div class="row align-items-center">


									<div class="form-group col-sm-4 col-lg-4 col-12">
										<label class="col-form-label">Nome do componente</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="nome_componente" class="form-control type-ref" value="{{$dadosDaNFe['componente']}}" id="nome_componente"/>
											</div>
										</div>
									</div>
									<div class="form-group col-sm-2 col-lg-2 col-12">
										<label class="col-form-label">Valor</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="valor_componente" class="form-control type-ref money" value="{{$dadosDaNFe['valor_frete']}}" id="valor_componente"/>
											</div>
										</div>
									</div>
									<div class="col-lg-1 col-md-4 col-sm-6 col-6">
										<a href="#!" style="margin-top: 10px;" id="addComponente" class="btn btn-light-success px-6 font-weight-bold">
											<i class="la la-plus"></i>
										</a>

									</div>

								</div>

								<div class="container">
									<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

										<table class="datatable-table" style="max-width: 100%; overflow: scroll" id="componentes">
											<thead class="datatable-head">
												<tr class="datatable-row" style="left: 0px;">
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Item</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Nome</span></th>

													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Valor</span></th>
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Ação</span></th>
												</tr>
											</thead>
											<tbody id="body" class="datatable-body">
												<tr class="datatable-row">
												</tr>
											</tbody>
										</table>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

				<div class="card card-custom gutter-b">

					<div class="card-body">

						<div class="row">
							<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

								<h1>Informações da Entrega</h1>
								<h4 class="text-info">Endereço do tomador</h4>
								<div class="row">

									<div class="form-group validated col-sm-3 col-lg-3 col-12">
										<p>
											<input type="checkbox" id="endereco-destinatario" />
											<label for="endereco-destinatario">Endereço do Destinatário</label>
										</p>

										<p>
											<input type="checkbox" id="endereco-remetente" />
											<label for="endereco-remetente">Endereço do Rementente</label>
										</p>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-6 col-lg-6 col-12">
										<label class="col-form-label">Rua</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="rua_tomador" class="form-control type-ref" value="" id="rua_tomador"/>
											</div>
										</div>
									</div>

									<div class="form-group col-sm-2 col-lg-2 col-6">
										<label class="col-form-label">Número</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="numero_tomador" class="form-control type-ref" value="" id="numero_tomador"/>
											</div>
										</div>
									</div>

									<div class="form-group col-sm-2 col-lg-2 col-6">
										<label class="col-form-label">CEP</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="cep_tomador" class="form-control type-ref" value="" id="cep_tomador"/>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 col-lg-4 col-12">
										<label class="col-form-label">Bairro</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="bairro_tomador" class="form-control type-ref" value="" id="bairro_tomador"/>
											</div>
										</div>
									</div>
									<div class="form-group col-sm-4 col-lg-4 col-12">

										<label class="col-form-label" id="">Cidade</label><br>
										<select class="form-control select2 cidade_tomador" style="width: 100%" id="kt_select2_4" name="cidade_tomador">
											<option value="null">Selecione a cidade</option>
											@foreach($cidades as $c)
											<option value="{{$c->id}}">{{$c->nome}} ({{$c->uf}})</option>
											@endforeach
										</select>

									</div>
								</div>

								<div class="row">
									<div class="form-group col-lg-3 col-md-4 col-sm-6">
										<label class="col-form-label">Data prevista de entrega</label>
										<div class="">
											<div class="input-group date">
												<input type="text" name="data_inicial" class="form-control data_prevista_entrega" value="{{$dadosDaNFe['data_entrega']}}" readonly id="kt_datepicker_3" />
												<div class="input-group-append">
													<span class="input-group-text">
														<i class="la la-calendar"></i>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group col-sm-4 col-lg-3 col-12">
										<label class="col-form-label">Valor da prestação de serviço</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="valor_transporte" class="form-control type-ref" value="{{$dadosDaNFe['valor_frete']}}" id="valor_transporte"/>
											</div>
										</div>
									</div>
									<div class="form-group col-sm-4 col-lg-3 col-12">
										<label class="col-form-label">Valor a receber</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="valor_receber" class="form-control type-ref" value="{{$dadosDaNFe['valor_frete']}}" id="valor_receber"/>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 col-lg-4 col-12">

										<label class="col-form-label" id="">Municipio de envio</label><br>
										<select class="form-control select2 select-mun" style="width: 100%" id="kt_select2_5">
											<option value="null">Selecione a cidade</option>
											@foreach($cidades as $c)
											<option value="{{$c->id}}">{{$c->nome}} ({{$c->uf}})</option>
											@endforeach
										</select>
									</div>

									<div class="form-group col-sm-4 col-lg-4 col-12">

										<label class="col-form-label" id="">Municipio de Inicio</label><br>
										<select class="form-control select2 select-mun" style="width: 100%" id="kt_select2_8" >
											<option value="null">Selecione a cidade</option>
											@foreach($cidades as $c)
											<option value="{{$c->id}}">{{$c->nome}} ({{$c->uf}})</option>
											@endforeach
										</select>
									</div>
									<div class="form-group col-sm-4 col-lg-4 col-12">

										<label class="col-form-label" id="">Municipio final</label><br>
										<select class="form-control select2 select-mun" style="width: 100%" id="kt_select2_7">
											<option value="null">Selecione a cidade</option>
											@foreach($cidades as $c)
											<option value="{{$c->id}}">{{$c->nome}} ({{$c->uf}})</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="row">
									<div class="form-group validated col-sm-2 col-lg-2 col-4">
										<label class="col-form-label" id="">Retira</label>
										<select class="custom-select form-control" id="retira" name="retira">
											<option value="1">Sim</option>
											<option value="0">Não</option>

										</select>
									</div>
									<div class="form-group col-sm-10 col-lg-10 col-12">
										<label class="col-form-label">Detalhes (Opcional)</label>
										<div class="">
											<div class="input-group">
												<input type="text" name="detalhes_retira" class="form-control type-ref" value="" id="detalhes_retira"/>
											</div>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

				<div class="row align-items-center">
					<div class="form-group col-sm-6 col-lg-6 col-12">
						<label class="col-form-label">Informação Adicional</label>
						<div class="">
							<div class="input-group">
								<input type="text" name="obs" class="form-control type-ref" value="" id="obs"/>
							</div>
						</div>
					</div>
					<div class="col-sm-3 col-lg-3">
					</div>
					<div class="col-sm-3 col-lg-3 col-md-3 col-xl-3 col-12">
						<a id="finalizar" style="width: 100%; margin-top: 15px;" href="#" onclick="salvarCTe()" class="btn btn-success disabled">Salvar</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


@endsection