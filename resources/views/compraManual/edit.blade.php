@extends('default.layout')
@section('content')
<style type="text/css">

	#focus-codigo:hover{
		cursor: pointer
	}

	.search-prod{
		position: absolute;
		top: 0;
		margin-top: 40px;
		left: 10;
		width: 100%;
		max-height: 200px;
		overflow: auto;
		z-index: 9999;
		border: 1px solid #eeeeee;
		border-radius: 4px;
		background-color: #fff;
		box-shadow: 0px 1px 6px 1px rgba(0, 0, 0, 0.4);
	}

	.search-prod label:hover{
		cursor: pointer;
	}

	.search-prod label{
		margin-left: 10px;
		width: 100%;
		margin-top: 7px;
		font-size: 14px;
		color: #000 !important;
	}
</style>
<div class="row" id="anime" style="display: none">
	<div class="col s8 offset-s2">
		<lottie-player src="/anime/{{\App\Models\Venda::randSuccess()}}" background="transparent" speed="0.8" style="width: 100%; height: 300px;" autoplay>
		</lottie-player>
	</div>
</div>
<div class="row @if(env('ANIMACAO')) animate__animated @endif animate__bounce" id="content" style="display: block">
	<div class="d-flex flex-column flex-column-fluid" id="kt_content">
		<div class="card card-custom gutter-b example example-compact">
			<div class="">
				<div class="col-lg-12">

					<input type="hidden" name="id" value="{{{ isset($cliente) ? $cliente->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact m-3">
						<div class="card-header">

							<h3 class="card-title">DADOS INICIAIS</h3>

							{!! __view_locais_select() !!}
							
						</div>
						<input type="hidden" value="{{ json_encode($produtos) }}" id="produtos">
						<div class="wizard wizard-3" id="kt_wizard_v3" data-wizard-state="between" data-wizard-clickable="true">
							<!--begin: Wizard Nav-->
							<div class="wizard-nav">
								<div class="wizard-steps px-8 py-8 px-lg-15 py-lg-3">
									<!--begin::Wizard Step 1 Nav-->
									<div class="wizard-step" data-wizard-type="step" data-wizard-state="done">
										<div class="wizard-label">
											<h3 class="wizard-title">
												<span>1.</span>ITENS
											</h3>
											<div class="wizard-bar"></div>
										</div>
									</div>
									<!--end::Wizard Step 1 Nav-->
									<div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
										<div class="wizard-label">
											<h3 class="wizard-title">
												<span>2.</span>FRETE
											</h3>
											<div class="wizard-bar"></div>
										</div>
									</div>
									<!--end::Wizard Step 2 Nav-->
									<!--begin::Wizard Step 2 Nav-->
									<div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
										<div class="wizard-label">
											<h3 class="wizard-title">
												<span>3.</span>PAGAMENTO
											</h3>
											<div class="wizard-bar"></div>
										</div>
									</div>
									<!--end::Wizard Step 2 Nav-->
								</div>
							</div>


							<!--end: Wizard Nav-->
							<!--begin: Wizard Body-->
							<div class="row justify-content-center py-10 px-8 py-lg-12 px-lg-10">
								<div class="col-xl-12">
									<!--begin: Wizard Form-->
									<form class="form fv-plugins-bootstrap fv-plugins-framework" id="kt_form">
										<!--begin: Wizard Step 1-->
										<div class="pb-5" data-wizard-type="step-content">
											<h4 class="mb-4 font-weight-bold text-dark">Selecione o Fornecedor</h4>
											<!--begin::Input-->
											<div class="input-group">

												<select class="form-control select2 fornecedor" id="kt_select2_1" name="fornecedor">
													<option value="--">Selecione o fornecedor</option>
													@foreach($fornecedores as $f)
													<option value="{{$f->id}}"
														@if($compra->fornecedor_id == $f->id)
														selected @endif
														>{{$f->razao_social}} - {{$f->nome_fantasia}} ({{$f->cpf_cnpj}})
													</option>
													@endforeach
												</select>
												<button type="button" onclick="novoFornecedor()" class="btn btn-warning btn-sm">
													<i class="la la-plus-circle icon-add"></i>
												</button>
											</div>


											<div class="row" id="fornecedor" style="display: none">

												<br>
												<div class="row col-12">

													<div class="col-sm-6 col-lg-6">
														<h5>Razão Social: <strong id="razao_social" class="text-danger">--</strong></h5>
														<h5>Nome Fantasia: <strong id="nome_fantasia" class="text-danger">--</strong></h5>
														<h5>Logradouro: <strong id="logradouro" class="text-danger">--</strong></h5>
														<h5>Numero: <strong id="numero" class="text-danger">--</strong></h5>

													</div>
													<div class="col-sm-6 col-lg-6">
														<h5>CPF/CNPJ: <strong id="cnpj" class="text-danger">--</strong></h5>
														<h5>RG/IE: <strong id="ie" class="text-danger">--</strong></h5>
														<h5>Fone: <strong id="fone" class="text-danger">--</strong></h5>
														<h5>Cidade: <strong id="cidade" class="text-danger">--</strong></h5>

													</div>
												</div>

											</div>

											<hr>
											<br>
											<h4 class="mb-10 font-weight-bold text-dark">Itens da Compra</h4>
											<div class="row">
												<div class="form-group validated col-sm-4 col-lg-4">
													<label class="col-form-label">Produto</label>
													<div class="input-group">

														<input placeholder="Digite para buscar o produto" type="search" id="produto-search" class="form-control">
														<div class="search-prod" style="display: none">
														</div>
														<button type="button" onclick="novoProduto()" class="btn btn-info btn-sm">
															<i class="la la-plus-circle icon-add"></i>
														</button>
													</div>
												</div>

												<div class="form-group validated col-sm-2 col-lg-2">
													<label class="col-form-label">Quantidade</label>
													<div class="">
														<input type="text" class="form-control" name="quantidade" id="quantidade">
													</div>
												</div>
  
                                                <div class="form-group validated col-sm-12 col-lg-2">
                                                    <label class="col-form-label">Custo Unit.</label>
                                                    <input type="text" class="form-control" name="valor" id="valor" value="0">
                                                </div>
  
                                                <!-- Campo % de Venda com coluna menor -->
                                                <div class="form-group validated col-sm-12 col-lg-1">
                                                    <label class="col-form-label">% Venda</label>
                                                    <input type="text" class="form-control" name="percentual_venda" id="percentual_venda" value="0" style="width:70px;">
                                                </div>
  
                                                <div class="form-group validated col-sm-12 col-lg-2">
                                                    <label class="col-form-label">Preço de Venda</label>
                                                    <input type="text" class="form-control money" name="preco_venda" id="preco_venda" value="0">
                                                </div>
  
                                                <div class="form-group validated col-sm-12 col-lg-1">
                                                    <label class="col-form-label">SubTotal Custo</label>
                                                    <input type="text" class="form-control" id="subtotal" value="0" disabled>
                                                </div>

												<div class="form-group validated col-sm-2 col-lg-2">
													<br>
													<button type="button" style="margin-top: 13px;" id="addProd" class="btn btn-success font-weight-bold text-uppercase px-9 py-4">
														Adicionar
													</button>
												</div>
											</div>

											<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded prod">
												<table class="datatable-table" style="max-width: 100%;overflow: scroll">
													<thead class="datatable-head">
														<tr class="datatable-row" style="left: 0px;">
															<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">#</span></th>
															<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">Código</span></th>
															<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Nome</span></th>
															<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Custo unitario</span></th>
															<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Quantidade</span></th>
															<th data-field="percentuallucro" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">% Venda</span></th>
                                                            <th data-field="PrecoVenda" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Preço de Venda</span></th>
															<th data-field="Status" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Subtotal Custo</span></th>
															<th data-field="Actions" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Ações</span></th>
														</tr>
													</thead>

													<tbody class="datatable-body">
													</tbody>

												</table>

											</div>
										</div>
										<!--end: Wizard Step 1-->


										<!--begin: Wizard Step 2-->
										<div class="pb-5" data-wizard-type="step-content" >
											<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
												<div class="row">
													<div class="col-xl-12">
														<h3>Transportadora</h3>

														<div class="row align-items-center">
															<div class="form-group validated col-sm-6 col-lg-5 col-12">
																<select class="form-control select2" style="width: 100%" id="kt_select2_3" name="transportadora">
																	<option value="null">Selecione a transportadora (opcional)</option>
																	@foreach($transportadoras as $t)
																	<option 
																	@if($compra->transportadora_id == $t->id)
																	selected @endif
																	value="{{$t->id}}">{{$t->id}} - {{$t->razao_social}}</option>
																	@endforeach
																</select>
															</div>
														</div>
													</div>
												</div>
												<hr>

												<div class="row">
													<div class="col-xl-12">
														<h3>Frete</h3>

														<div class="row align-items-center">
															<div class="form-group validated col-sm-4 col-lg-4 col-8">
																<label class="col-form-label" id="">Tipo</label>
																<select class="custom-select form-control" id="frete" name="frete">
																	<option @if($compra->tipo == '0') selected @endif value="0">0 - Emitente</option>
																	<option @if($compra->tipo == '1') selected @endif  value="1">1 - Destinatário</option>
																	<option @if($compra->tipo == '2') selected @endif  value="2">2 - Terceiros</option>
																	<option @if($compra->tipo == '9') selected @endif  value="9">9 - Sem Frete</option>
																</select>
															</div>

															<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
																<label class="col-form-label">Placa Veiculo</label>
																<div class="">
																	<div class="input-group">
																		<input value="{{$compra->placa}}" type="text" name="placa" class="form-control" value="" id="placa"/>
																	</div>
																</div>
															</div>

															<div class="form-group validated col-sm-2 col-lg-2 col-6">
																<label class="col-form-label" id="">UF</label>
																<select class="custom-select form-control" id="uf_placa" name="uf_placa">
																	<option value="--">--</option>
																	@foreach(App\Models\Cidade::estados() as $uf)
																	<option @if($compra->uf == $uf)
																		selected @endif value="{{$uf}}">{{$uf}}</option>
																		@endforeach
																	</select>
																</div>

																<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
																	<label class="col-form-label">Valor</label>
																	<div class="">
																		<div class="input-group">
																			<input type="text" name="valor_frete" class="form-control" value="{{$compra->valor_frete}}" id="valor_frete"/>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<hr>
													<div class="row">
														<div class="col-xl-12">
															<h3>Volume</h3>

															<div class="row align-items-center">

																<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
																	<label class="col-form-label">Espécie</label>
																	<div class="">
																		<div class="input-group">
																			<input type="text" name="especie" class="form-control" value="{{$compra->especie}}" id="especie"/>
																		</div>
																	</div>
																</div>

																<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
																	<label class="col-form-label">Num. de Volumes</label>
																	<div class="">
																		<div class="input-group">
																			<input type="text" name="numeracaoVol" class="form-control" value="{{$compra->numeracaoVolumes}}" id="numeracaoVol"/>
																		</div>
																	</div>
																</div>

																<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
																	<label class="col-form-label">Qtd. de Volumes</label>
																	<div class="">
																		<div class="input-group">
																			<input type="text" name="qtdVol" class="form-control" value="{{$compra->qtdVolumes}}" id="qtdVol"/>
																		</div>
																	</div>
																</div>

																<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
																	<label class="col-form-label">Peso Liquido</label>
																	<div class="">
																		<div class="input-group">
																			<input type="text" name="pesoL" class="form-control" value="{{$compra->peso_liquido}}" id="pesoL"/>
																		</div>
																	</div>
																</div>

																<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
																	<label class="col-form-label">Peso Bruto</label>
																	<div class="">
																		<div class="input-group">
																			<input type="text" name="pesoB" class="form-control" value="{{$compra->peso_bruto}}" id="pesoB"/>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>

												</div>
											</div>
											<div class="pb-5" data-wizard-type="step-content" data-wizard-state="current">
												<h4 class="mb-10 font-weight-bold text-dark">Selecione a forma de pagamento</h4>
												<!--begin::Input-->
												<div class="row">
													<div class="form-group validated col-sm-3 col-lg-3">
														<label class="col-form-label">Forma de pagamento</label>
														<select class="custom-select form-control" id="formaPagamento">
															<option value="--">Selecione a forma de pagamento</option>
															<option value="a_vista">A vista</option>
															<option value="30_dias">30 Dias</option>
															<option value="personalizado">Personalizado</option>
														</select>
													</div>
													<div class="form-group validated col-sm-2 col-lg-2">
														<label class="col-form-label">Qtd de parcelas</label>
														<div class="">
															<input type="text" class="form-control" name="bairro" id="qtdParcelas">

														</div>
													</div>

													<div class="form-group validated col-sm-2 col-lg-2">
														<label class="col-form-label">Data de Vencimento</label>
														<div class="">
															<div class="input-group date">
																<input type="text" class="form-control data-input" id="kt_datepicker_3">
																<div class="input-group-append">
																	<span class="input-group-text">
																		<i class="la la-calendar"></i>
																	</span>
																</div>
															</div>
														</div>
													</div>

													<div class="form-group validated col-sm-2 col-lg-2">
														<label class="col-form-label">Valor da parcela</label>
														<div class="">
															<input type="text" class="form-control" id="valor_parcela">

														</div>
													</div>

													<div class="form-group validated col-sm-2 col-lg-2">
														<br>
														<a style="margin-top: 13px;" id="add-pag" class="btn btn-primary font-weight-bold text-uppercase px-9 py-4">
															Adicionar
														</a>
													</div>
												</div>

												<div class="row">
													<div class="form-group validated col-sm-12 col-lg-12">

														<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded fatura">
															<table class="datatable-table" style="max-width: 100%;overflow: scroll">
																<thead class="datatable-head">
																	<tr class="datatable-row" style="left: 0px;">
																		<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 180px;">Parcela</span></th>
																		<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 220px;">Data</span></th>
																		<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 260px;">Valor</span></th>

																		<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 220px;">Valor</span></th>
																	</tr>
																</thead>

																<tbody class="datatable-body">
																</tbody>

															</table>

														</div>
													</div>
												</div>


											</div>
											<!--end: Wizard Step 2-->

											<!--begin: Wizard Actions-->
											<div class="d-flex justify-content-between border-top mt-5 pt-10">
										<!-- <div class="mr-2">
											<button type="button" class="btn btn-light-primary font-weight-bold text-uppercase px-9 py-4" data-wizard-type="action-prev">Voltar para Itens</button>
										</div> -->
										<div>
											<!-- <button type="button" class="btn btn-success font-weight-bold text-uppercase px-9 py-4" data-wizard-type="action-submit">Salvar Compra</button> -->
											<!-- <button type="button" class="btn btn-primary font-weight-bold text-uppercase px-9 py-4" data-wizard-type="action-next">Ir para pagamento</button> -->
										</div>
									</div>
									<!--end: Wizard Actions-->

								</form>
								<!--end: Wizard Form-->
							</div>
						</div>
						<!--end: Wizard Body-->
					</div>

					<div class="row justify-content-center py-10 px-8 py-lg-12 px-lg-10">
						<div class="col-xl-12">
							<h5>Valor Total R$ <strong id="total" class="cyan-text">0,00</strong></h5>
							<div class="row">

								<div class="form-group validated col-sm-2 col-lg-2">
									<label class="col-form-label">Desconto</label>
									<div class="">
										<input type="text" value="{{$compra->desconto}}" class="form-control" id="desconto">

									</div>
								</div>
								<div class="form-group validated col-sm-2 col-lg-2">
									<label class="col-form-label">Acréscimo</label>
									<div class="">
										<input type="text" value="{{$compra->acrescimo}}" class="form-control money" id="acrescimo">

									</div>
								</div>

								<div class="form-group validated col-sm-8 col-lg-6">
									<label class="col-form-label">Observação</label>
									<div class="">
										<input type="text" value="{{$compra->observacao}}" class="form-control" id="obs">

									</div>
								</div>

								<div class="form-group validated col-lg-2 col-md-4 col-sm-6">
									<label class="col-form-label">Categoria de conta</label>

									<select class="custom-select form-control" id="categoria_conta_id" name="categoria_conta_id">
										<option value="">Selecione</option>
										@foreach($categoriasDeConta as $c)
										<option @if($compra->categoria_conta_id == $c->id) selected @endif value="{{$c->id}}">
											{{$c->nome}}
										</option>
										@endforeach
									</select>

								</div>

								<div class="form-group validated col-12">
									<br>
									<button disabled type="button" class="btn btn-success font-weight-bold text-uppercase px-9 py-4" id="salvar-venda"  style="width: 180px; margin-top: 13px; float: right;" onclick="atualizarCompra()">Atualizar</button>
								</div>

							</div>
						</div>
					</div>

				</div>

			</div>
		</div>
		<input type="hidden" id="_token" value="{{ csrf_token() }}">
		<input type="hidden" id="itens" value="{{json_encode($compra->itens)}}">
		<input type="hidden" id="fatura" value="{{json_encode($fatura)}}">
		<input type="hidden" id="compra_id" value="{{ $compra->id }}">
	</div>
</div>
</div>

<div class="modal fade" id="modal-fornecedor" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Novo Fornecedor</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">

				<div class="row">
					<div class="col-xl-12">

						<div class="row">
							<div class="form-group col-sm-12 col-lg-12">
								<label>Pessoa:</label>
								<div class="radio-inline">
									<label class="radio radio-success">
										<input name="group1" type="radio" id="pessoaFisica"/>
										<span></span>
										FISICA
									</label>
									<label class="radio radio-success">
										<input name="group1" type="radio" id="pessoaJuridica"/>
										<span></span>
										JURIDICA
									</label>

								</div>

							</div>
						</div>
						<div class="row">

							<div class="form-group validated col-sm-3 col-lg-4">
								<label class="col-form-label" id="lbl_cpf_cnpj">CPF</label>
								<div class="">
									<input type="text" id="cpf_cnpj" class="form-control @if($errors->has('cpf_cnpj')) is-invalid @endif" name="cpf_cnpj">
									
								</div>
							</div>
							<div class="form-group validated col-lg-2 col-md-2 col-sm-6">
								<label class="col-form-label text-left col-lg-12 col-sm-12">UF</label>

								<select class="custom-select form-control" id="sigla_uf" name="sigla_uf">
									@foreach(App\Models\Cidade::estados() as $c)
									<option value="{{$c}}">{{$c}}
									</option>
									@endforeach
								</select>

							</div>
							<div class="form-group validated col-lg-2 col-md-2 col-sm-6">
								<br><br>
								<a type="button" id="btn-consulta-cadastro" onclick="consultaCadastro()" class="btn btn-success spinner-white spinner-right">
									<span>
										<i class="fa fa-search"></i>
									</span>
								</a>
							</div>

						</div>

						<div class="row">
							<div class="form-group validated col-sm-6">
								<label class="col-form-label">Razao Social/Nome</label>
								<div class="">
									<input id="razao_social2" type="text" class="form-control @if($errors->has('razao_social')) is-invalid @endif">
									
								</div>
							</div>

							<div class="form-group validated col-sm-6">
								<label class="col-form-label">Nome Fantasia</label>
								<div class="">
									<input id="nome_fantasia2" type="text" class="form-control @if($errors->has('nome_fantasia')) is-invalid @endif">
								</div>
							</div>

							<div class="form-group validated col-sm-3 col-lg-4">
								<label class="col-form-label" id="lbl_ie_rg">RG</label>
								<div class="">
									<input type="text" id="ie_rg" class="form-control @if($errors->has('ie_rg')) is-invalid @endif">
								</div>
							</div>

							<div class="form-group validated col-lg-3 col-md-3 col-sm-10">
								<label class="col-form-label text-left col-lg-12 col-sm-12">Contribuinte</label>

								<select class="custom-select form-control" id="contribuinte">

									<option value="1">SIM</option>
									<option value="0">NAO</option>
								</select>
							</div>

						</div>
						<hr>
						<h5>Endereço</h5>
						<div class="row">
							<div class="form-group validated col-sm-8 col-lg-8">
								<label class="col-form-label">Rua</label>
								<div class="">
									<input id="rua" type="text" class="form-control @if($errors->has('rua')) is-invalid @endif">
									
								</div>
							</div>

							<div class="form-group validated col-sm-2 col-lg-2">
								<label class="col-form-label">Número</label>
								<div class="">
									<input id="numero2" type="text" class="form-control @if($errors->has('numero')) is-invalid @endif">
									
								</div>
							</div>

							<div class="form-group validated col-sm-8 col-lg-5">
								<label class="col-form-label">Bairro</label>
								<div class="">
									<input id="bairro" type="text" class="form-control @if($errors->has('bairro')) is-invalid @endif">
									
								</div>
							</div>

							<div class="form-group validated col-sm-8 col-lg-3">
								<label class="col-form-label">CEP</label>
								<div class="">
									<input id="cep" type="text" class="form-control @if($errors->has('cep')) is-invalid @endif">
									
								</div>
							</div>

							<div class="form-group validated col-sm-8 col-lg-4">
								<label class="col-form-label">Email</label>
								<div class="">
									<input id="email" type="text" class="form-control @if($errors->has('email')) is-invalid @endif">
									
								</div>
							</div>

							@php
							$cidade = App\Models\Cidade::getCidadeCod($config->codMun);
							@endphp
							<div class="form-group validated col-lg-6 col-md-6 col-sm-10">
								<label class="col-form-label text-left col-lg-4 col-sm-12">Cidade</label><br>
								<select style="width: 100%" class="form-control select2" id="kt_select2_4">
									@foreach(App\Models\Cidade::all() as $c)
									<option @if($cidade->id == $c->id) selected @endif value="{{$c->id}}">
										{{$c->nome}} ({{$c->uf}})
									</option>
									@endforeach
								</select>
								
							</div>

							<div class="form-group validated col-sm-8 col-lg-3">
								<label class="col-form-label">Telefone (Opcional)</label>
								<div class="">
									<input id="telefone" type="text" class="form-control @if($errors->has('telefone')) is-invalid @endif">
								</div>
							</div>

							<div class="form-group validated col-sm-8 col-lg-3">
								<label class="col-form-label">Celular (Opcional)</label>
								<div class="">
									<input id="celular" type="text" class="form-control @if($errors->has('celular')) is-invalid @endif">
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" id="btn-frete" class="btn btn-danger font-weight-bold spinner-white spinner-right" data-dismiss="modal" aria-label="Close">Fechar</button>
				<button type="button" onclick="salvarFornecedor()" class="btn btn-success font-weight-bold spinner-white spinner-right">Salvar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-produto" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Novo Produto</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">

				<div class="wizard wizard-3" id="kt_wizard_v4" data-wizard-state="between" data-wizard-clickable="true">
					<!--begin: Wizard Nav-->

					<div class="wizard-nav">

						<div class="wizard-steps px-8 py-8 px-lg-15 py-lg-3">
							<!--begin::Wizard Step 1 Nav-->
							<div class="wizard-step" data-wizard-type="step" data-wizard-state="done">
								<div class="wizard-label">
									<h3 class="wizard-title">
										<span>
											IDENTIFICAÇÃO
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
											ALÍQUOTAS
										</span>
									</h3>
									<div class="wizard-bar"></div>
								</div>
							</div>
						</div>
					</div>

					<div class="card-body">
						<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

							<!--begin: Wizard Form-->
							<form class="form fv-plugins-bootstrap fv-plugins-framework form-prod" id="kt_form">
								<!--begin: Wizard Step 1-->
								<p class="kt-widget__data text-danger">Campos com (*) obrigatório</p>

								<div class="pb-5" data-wizard-type="step-content">
									<div class="row">

										<div class="col-xl-12">
											<div class="row">

												<div class="form-group validated col-sm-9 col-lg-9">
													<label class="col-form-label">Nome*</label>
													<div class="">
														<input type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" id="nome">
													</div>
												</div>

												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">Referência</label>
													<div class="">
														<input type="text" class="form-control @if($errors->has('referencia')) is-invalid @endif" id="referencia">
													</div>
												</div>

												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">Valor de Compra*</label>
													<div class="">
														<input type="text" id="valor_compra" class="form-control @if($errors->has('valor_compra')) is-invalid @endif money">
													</div>
												</div>

												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">% lucro*</label>
													<div class="">
														<input type="text" id="percentual_lucro" class="form-control money" name="percentual_lucro" value="{{$config->percentual_lucro_padrao }}">
													</div>
												</div>

												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">Valor de Venda*</label>
													<div class="">
														<input type="text" id="valor_venda" class="form-control @if($errors->has('valor_venda')) is-invalid @endif money">

													</div>
												</div>


												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">Iniciar com Estoque</label>
													<div class="">
														<input type="text" id="estoque" class="form-control @if($errors->has('estoque')) is-invalid @endif money">

													</div>
												</div>

												<div class="form-group validated col-sm-4 col-lg-4">
													<label class="col-form-label">Código de Barras EAN13</label>
													<div class="">
														<input type="text" class="form-control @if($errors->has('codBarras')) is-invalid @endif" id="codBarras">
													</div>
												</div>


												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">Estoque minimo</label>
													<div class="">
														<input type="text" id="estoque_minimo" class="form-control @if($errors->has('estoque_minimo')) is-invalid @endif">
													</div>
												</div>


												<div class="form-group validated col-sm-6 col-lg-4">
													<label class="col-form-label">Gerenciar estoque</label>
													<div class="col-6">
														<span class="switch switch-outline switch-primary">
															<label>
																<input value="true" type="checkbox" id="gerenciar_estoque">
																<span></span>
															</label>
														</span>
													</div>
												</div>

												<div class="form-group validated col-sm-6 col-lg-2">
													<label class="col-form-label">Inativo</label>
													<div class="col-6">
														<span class="switch switch-outline switch-danger">
															<label>
																<input value="true" type="checkbox" id="inativo">
																<span></span>
															</label>
														</span>
													</div>
												</div>

												<div class="form-group validated col-lg-3 col-md-5 col-sm-10">
													<label class="col-form-label ">Categoria</label>
													<div class="input-group">

														<select id="categoria_id" class="form-control custom-select">
															@foreach($categorias as $cat)
															<option value="{{$cat->id}}">{{$cat->nome}}
															</option>
															@endforeach
														</select>

													</div>
												</div>

												<div class="form-group validated col-sm-4 col-lg-3">
													<label class="col-form-label">Limite maximo desconto %</label>
													<div class="">
														<input type="text" id="limite_maximo_desconto" class="form-control @if($errors->has('limite_maximo_desconto')) is-invalid @endif">
													</div>
												</div>



												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">Alerta de Venc. (Dias)</label>
													<div class="">
														<input type="text" id="alerta_vencimento" class="form-control @if($errors->has('alerta_vencimento')) is-invalid @endif">
													</div>
												</div>


												<div class="form-group validated col-lg-3 col-md-6 col-sm-10">
													<label class="col-form-label">Unidade de compra *</label>

													<select class="custom-select form-control" id="unidade_compra" id="unidade_compra">
														@foreach($unidadesDeMedida as $u)
														<option @if($u == 'UN') selected @endif value="{{$u}}">{{$u}}
														</option>
														@endforeach
													</select>
												</div>


												<div class="form-group validated col-sm-3 col-lg-3" id="conversao" style="display: none">
													<label class="col-form-label">Conversão Unitária</label>
													<div class="">
														<input type="text" id="conversao_unitaria" class="form-control @if($errors->has('conversao_unitaria')) is-invalid @endif">
													</div>
												</div>
												<div class="form-group validated col-lg-3 col-md-6 col-sm-10">
													<label class="col-form-label">Unidade de venda *</label>

													<select class="custom-select form-control" id="unidade_venda">
														@foreach($unidadesDeMedida as $u)
														<option @if($u == 'UN') selected @endif value="{{$u}}">{{$u}}
														</option>
														@endforeach
													</select>

												</div>

												<div class="form-group validated col-sm-3 col-lg-3">
													<label class="col-form-label">NCM *</label>
													<div class="">
														<input data-mask="0000.00.00" type="text" id="NCM" class="form-control @if($errors->has('NCM')) is-invalid @endif" value="{{$tributacao->ncm_padrao}}">
													</div>
												</div>

												<div class="form-group validated col-sm-2 col-lg-3">
													<label class="col-form-label">CEST</label>
													<div class="">
														<input type="text" id="CEST" class="form-control @if($errors->has('CEST')) is-invalid @endif">
													</div>
												</div>
												<hr>

												<div class="form-group validated col-12">
													<h3>Derivado Petróleo</h3>
												</div>

												<div class="form-group validated col-lg-6 col-md-10 col-sm-10">
													<label class="col-form-label">ANP</label>

													<select class="custom-select form-control" id="anp">
														<option value="">--</option>
														@foreach($anps as $key => $a)
														<option value="{{$key}}">[{{$key}}] - {{$a}}
														</option>
														@endforeach
													</select>
												</div>

												<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
													<label class="col-form-label">%GLP</label>

													<input type="text" id="perc_glp" class="form-control @if($errors->has('perc_glp')) is-invalid @endif trib">
												</div>

												<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
													<label class="col-form-label">%GNn</label>

													<input type="text" id="perc_gnn" class="form-control @if($errors->has('perc_gnn')) is-invalid @endif trib">
												</div>

												<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
													<label class="col-form-label">%GNi</label>

													<input type="text" id="perc_gni" class="form-control @if($errors->has('perc_gni')) is-invalid @endif trib">
												</div>

												<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
													<label class="col-form-label">Valor de partida</label>

													<input type="text" id="valor_partida" class="form-control @if($errors->has('valor_partida')) is-invalid @endif money">
												</div>

												<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
													<label class="col-form-label">Un. tributável</label>

													<input type="text" id="unidade_tributavel" class="form-control @if($errors->has('unidade_tributavel')) is-invalid @endif" data-mask="AAAA">
												</div>

												<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
													<label class="col-form-label">Qtd. tributável</label>

													<input type="text" id="quantidade_tributavel" class="form-control @if($errors->has('quantidade_tributavel')) is-invalid @endif" data-mask="00000,00" data-mask-reverse="true">
												</div>


												<hr>
												<div class="form-group validated col-12">
													<h3>Dados de dimensão e peso do produto (Opcional)</h3>
												</div>


												<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
													<label class="col-form-label">Largura (cm)</label>

													<input type="text" id="largura" class="form-control @if($errors->has('largura')) is-invalid @endif">

												</div>

												<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
													<label class="col-form-label">Altura (cm)</label>

													<input type="text" id="altura" class="form-control @if($errors->has('altura')) is-invalid @endif">
												</div>

												<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
													<label class="col-form-label">Comprimento (cm)</label>

													<input type="text" id="comprimento" class="form-control @if($errors->has('comprimento')) is-invalid @endif">
												</div>

												<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
													<label class="col-form-label">Peso liquido</label>

													<input type="text" id="peso_liquido" class="form-control @if($errors->has('peso_liquido')) is-invalid @endif">
												</div>

												<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
													<label class="col-form-label">Peso bruto</label>

													<input type="text" id="peso_bruto" class="form-control @if($errors->has('peso_bruto')) is-invalid @endif">
												</div>

												<div class="col-lg-12 col-xl-12">
													<p class="text-danger">*Se atente a preencher todos os dados para utilizar a Api dos correios.</p>
												</div>

											</div>

										</div>
									</div>

								</div>
							</div>
							<div class="pb-5" data-wizard-type="step-content">

								<div class="row">

									<div class="col-xl-12">

										<div class="row">

											<div class="form-group validated col-lg-12 col-md-10 col-sm-10">
												<label class="col-form-label">
													@if($tributacao->regime == 1)
													CST
													@else
													CSOSN
													@endif
												*</label>

												<select class="custom-select form-control" id="CST_CSOSN">
													@foreach($listaCSTCSOSN as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_CSOSN)
														selected
														@endif
														@else
														@if($key == $config->CST_CSOSN_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>

											</div>

											<div class="form-group validated col-lg-6 col-md-10 col-sm-10">
												<label class="col-form-label">CST PIS *</label>

												<select class="custom-select form-control" id="CST_PIS">
													@foreach($listaCST_PIS_COFINS as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_PIS)
														selected
														@endif
														@else
														@if($key == $config->CST_PIS_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>

											</div>

											<div class="form-group validated col-lg-6 col-md-10 col-sm-10">
												<label class="col-form-label">CST COFINS *</label>

												<select class="custom-select form-control" id="CST_COFINS">
													@foreach($listaCST_PIS_COFINS as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_COFINS)
														selected
														@endif
														@else
														@if($key == $config->CST_COFINS_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>

											</div>

											<div class="form-group validated col-lg-12 col-md-10 col-sm-10">
												<label class="col-form-label">CST IPI *</label>

												<select class="custom-select form-control" id="CST_IPI">
													@foreach($listaCST_IPI as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_IPI)
														selected
														@endif
														@else
														@if($key == $config->CST_IPI_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>
											</div>

											<div class="form-group validated col-lg-12 col-md-10 col-sm-10">
												<label class="col-form-label">
													@if($tributacao->regime == 1)
													CST Exportação
													@else
													CSOSN Exportação
													@endif
												*</label>

												<select class="custom-select form-control" id="CST_CSOSN_EXP">
													<option value="">--</option>
													@foreach($listaCSTCSOSN as $key => $c)
													<option value="{{$key}}" @if(isset($produto)) @if($key==$produto->CST_CSOSN_EXP)
														selected
														@endif
														@endif

														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>

											</div>

											<div class="form-group validated col-sm-4 col-lg-3">
												<label class="col-form-label">CFOP saida interno *</label>
												<div class="">
													<input type="text" id="CFOP_saida_estadual" class="form-control @if($errors->has('CFOP_saida_estadual')) is-invalid @endif" value="{{{ isset($produto->CFOP_saida_estadual) ? $produto->CFOP_saida_estadual : $natureza->CFOP_saida_estadual }}}">
												</div>
											</div>
											<div class="form-group validated col-sm-4 col-lg-3">
												<label class="col-form-label">CFOP saida externo *</label>
												<div class="">
													<input type="text" id="CFOP_saida_inter_estadual" class="form-control @if($errors->has('CFOP_saida_inter_estadual')) is-invalid @endif" value="{{{ isset($produto->CFOP_saida_inter_estadual) ? $produto->CFOP_saida_inter_estadual : $natureza->CFOP_saida_inter_estadual }}}">
												</div>
											</div>

											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%ICMS *</label>
												<div class="">
													<input type="text" id="perc_icms" class="form-control trib @if($errors->has('perc_icms')) is-invalid @endif" value="{{{ isset($produto->perc_icms) ? $produto->perc_icms : $tributacao->icms }}}">
												</div>
											</div>
											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%PIS *</label>
												<div class="">
													<input type="text" id="perc_pis" class="form-control trib @if($errors->has('perc_pis')) is-invalid @endif" value="{{{ isset($produto->perc_pis) ? $produto->perc_pis : $tributacao->pis }}}">
												</div>
											</div>
											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%COFINS *</label>
												<div class="">
													<input type="text" id="perc_cofins" class="form-control trib @if($errors->has('perc_cofins')) is-invalid @endif" value="{{{ isset($produto->perc_cofins) ? $produto->perc_cofins : $tributacao->cofins }}}">
												</div>
											</div>
											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%IPI *</label>
												<div class="">
													<input type="text" id="perc_ipi" class="form-control trib @if($errors->has('perc_ipi')) is-invalid @endif" value="{{{ isset($produto->perc_ipi) ? $produto->perc_ipi : $tributacao->ipi }}}">
												</div>
											</div>

											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%ISS*</label>
												<div class="">
													<input type="text" id="perc_iss" class="form-control trib @if($errors->has('perc_iss')) is-invalid @endif" value="{{{ isset($produto->perc_iss) ? $produto->perc_iss : 0.00 }}}">
												</div>
											</div>

											<div class="form-group validated col-sm-2 col-lg-2">
												<label class="col-form-label">%Redução BC</label>
												<div class="">
													<input type="text" id="pRedBC" class="form-control @if($errors->has('pRedBC')) is-invalid @endif" value="{{{ isset($produto->pRedBC) ? $produto->pRedBC : 0.00 }}}">

												</div>
											</div>

											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">Cod benefício</label>
												<div class="">
													<input type="text" id="cBenef" class="form-control @if($errors->has('cBenef')) is-invalid @endif" value="{{{ isset($produto->cBenef) ? $produto->cBenef : old('cBenef') }}}">

												</div>
											</div>

											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%ICMS interestadual</label>
												<div class="">
													<input type="text" id="perc_icms_interestadual" class="form-control @if($errors->has('perc_icms_interestadual')) is-invalid @endif trib" value="{{{ isset($produto->perc_icms_interestadual) ? $produto->perc_icms_interestadual : old('perc_icms_interestadual') }}}">

												</div>
											</div>

											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%ICMS interno</label>
												<div class="">
													<input type="text" id="perc_icms_interno" class="form-control @if($errors->has('perc_icms_interno')) is-invalid @endif trib" value="{{{ isset($produto->perc_icms_interno) ? $produto->perc_icms_interno : old('perc_icms_interno') }}}">

												</div>
											</div>

											<div class="form-group validated col-sm-3 col-lg-2">
												<label class="col-form-label">%FCP interestadual</label>
												<div class="">
													<input type="text" id="perc_fcp_interestadual" class="form-control @if($errors->has('perc_fcp_interestadual')) is-invalid @endif trib" value="{{{ isset($produto->perc_fcp_interestadual) ? $produto->perc_fcp_interestadual : old('perc_fcp_interestadual') }}}">

												</div>
											</div>

											<div class="form-group validated col-lg-12 col-md-10 col-sm-10">
												<label class="col-form-label">
													@if($tributacao->regime == 1)
													CST entrada
													@else
													CSOSN entrada
												@endif *</label>

												<select class="custom-select form-control" id="CST_CSOSN_entrada" name="CST_CSOSN_entrada">
													@foreach($listaCSTCSOSN as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_CSOSN_entrada)
														selected
														@endif
														@else
														@if($key == $config->CST_CSOSN_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>

											</div>

											<div class="form-group validated col-lg-6 col-md-10 col-sm-10">
												<label class="col-form-label">CST PIS entrada *</label>

												<select class="custom-select form-control" id="CST_PIS_entrada" name="CST_PIS_entrada">
													@foreach(App\Models\Produto::listaCST_PIS_COFINS_Entrada() as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_PIS_entrada)
														selected
														@endif
														@else
														@if($key == $config->CST_PIS_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>

											</div>

											<div class="form-group validated col-lg-6 col-md-10 col-sm-10">
												<label class="col-form-label">CST COFINS entrada *</label>

												<select class="custom-select form-control" id="CST_COFINS_entrada" name="CST_COFINS_entrada">
													@foreach(App\Models\Produto::listaCST_PIS_COFINS_Entrada() as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_COFINS_entrada)
														selected
														@endif
														@else
														@if($key == $config->CST_COFINS_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>

											</div>

											<div class="form-group validated col-lg-6 col-md-10 col-sm-10">
												<label class="col-form-label">CST IPI entrada *</label>

												<select class="custom-select form-control" id="CST_IPI_entrada" name="CST_IPI_entrada">
													@foreach(App\Models\Produto::listaCST_IPI_Entrada() as $key => $c)
													<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_IPI_entrada)
														selected
														@endif
														@else
														@if($key == $config->CST_IPI_padrao)
														selected
														@endif
														@endif

														@endif
														>{{$key}} - {{$c}}
													</option>
													@endforeach
												</select>
											</div>

										</div>
									</div>
								</div>
							</div>

						</div>

					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="btn-frete" class="btn btn-danger font-weight-bold spinner-white spinner-right" data-dismiss="modal" aria-label="Close">Fechar</button>
				<button type="button" onclick="salvarProduto()" class="btn btn-success font-weight-bold spinner-white spinner-right">Salvar</button>
			</div>
		</div>
	</div>
</div>
@endsection