@extends('default.layout')
@section('content')

<div class="row" id="anime" style="display: none">
	<div class="col s8 offset-s2">
		<lottie-player src="/anime/{{\App\Models\Venda::randSuccess()}}" background="transparent" speed="0.8" style="width: 100%; height: 300px;" autoplay >
		</lottie-player>
	</div>
</div>

<div class="card card-custom gutter-b" id="content">

	<div class="card-body @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">
		<div class="content d-flex flex-column flex-column-fluid" id="kt_content" >
			<div class="card card-custom gutter-b example example-compact">
				<div class="card card-custom gutter-b example example-compact">
					<div class="card-header">
						<h3 class="card-title">Editando Devolução: <strong class="text-info">{{$devolucao->id}}</strong></h3>
					</div>
				</div>
				<div class="col-lg-12">
					<!--begin::Portlet-->

					<input type="hidden" value="{{$devolucao->id}}" id="dev_id" name="dev_id">
					<input type="hidden" name="id" value="{{{ isset($cliente) ? $cliente->id : 0 }}}">
					
					<input type="hidden" value="{{csrf_token()}}" id="_token">
					

					<div class="row">

						<div class="col-xl-12">
							

							<div class="col-xl-12">
								<div class="row">

									<div class="col-xl-12 col-sm-12 col-lg-12">
										<h4 class="center-align">Nota Fiscal: <strong class="text-primary">{{$dadosNf['nNf']}}</strong></h4>
										<h4 class="center-align">Chave: <strong class="text-primary">{{$dadosNf['chave']}}</strong></h4>
									</div>

									<div class="col-xl-6 col-sm-6 col-lg-6">
										<h5>Fornecedor: <strong>{{$dadosEmitente['razaoSocial']}}</strong></h5>
										<h5>Nome Fantasia: <strong>{{$dadosEmitente['nomeFantasia']}}</strong></h5>
										<h5>CNPJ: <strong>{{$dadosEmitente['cnpj']}}</strong></h5>
										<h5>IE: <strong>{{$dadosEmitente['ie']}}</strong></h5>
										<h5>Cidade: <strong>{{$cidade->nome}} ({{$cidade->uf}})</strong></h5>
										
									</div>

									<div class="col-xl-6 col-sm-6 col-lg-6">
										<h5>Logradouro: <strong>{{$dadosEmitente['logradouro']}}</strong></h5>
										<h5>Numero: <strong>{{$dadosEmitente['numero']}}</strong></h5>
										<h5>Bairro: <strong>{{$dadosEmitente['bairro']}}</strong></h5>
										<h5>CEP: <strong>{{$dadosEmitente['cep']}}</strong></h5>
										<h5>Fone: <strong>{{$dadosEmitente['fone']}}</strong></h5>
									</div>
								</div>
							</div>

							<input type="hidden" id="idFornecedor" value="{{$idFornecedor}}">
							<input type="hidden" id="nNf" value="{{$dadosNf['nNf']}}">
							<input type="hidden" id="vFrete" value="{{$dadosNf['vFrete']}}">
							<input type="hidden" id="chave" value="{{$dadosNf['chave']}}">
							<input type="hidden" id="totalNF" value="{{$dadosNf['vProd']}}">
							<input type="hidden" id="transportadora" value="{{json_encode($transportadora)}}">

						</div>
						<div class="col-xl-12">
							<div class="row">
								<div class="col-xl-12">

									<h4>Itens da NFe</h4>
									<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">
										<table class="datatable-table" style="max-width: 100%;overflow: scroll" id="tbl">
											<thead class="datatable-head">
												<tr class="datatable-row" style="left: 0px;">
													<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Código</span></th>
													<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Produto</span></th>
													<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">NCM</span></th>
													<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CFOP</span></th>
													<th data-field="Status" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Cod Barra</span></th>
													<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Un. Compra</span></th>
													<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Valor</span></th>
													<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Qtd</span></th>
													<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Subtotal</span></th>
													<th data-field="Actions" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Ações</span></th>
												</tr>
											</thead>
											<input type="hidden" id="itens_nf" value="{{json_encode($itens)}}">

											<tbody id="tbody" class="datatable-body">
											</tbody>


										</table>
										<div class="row">
											<div class="col-xl-12">
												<h5 style="margin-left: 10px; margin-top: 30px;">Soma dos Itens: <strong id="soma-itens" class="text-danger"></strong></h5>
											</div>
										</div>

									</div>

								</div>
							</div>
						</div>
						

						<div class="col-xl-12">
							<div class="col-xl-12">
								<div class="row">

									<div class="form-group validated col-lg-4 col-md-6 col-sm-4">
										<label class="col-form-label">Natureza de Operação</label>

										<select class="custom-select form-control" id="natureza" name="natureza">
											@foreach($naturezas as $n)
											<option @if($devolucao->natureza_id == $n->id) selected @endif value="{{$n->id}}">{{$n->natureza}}</option>
											@endforeach
										</select>

									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Tipo</label>

										<select class="custom-select form-control" id="tipo" name="tipo">
											<option @if($devolucao->tipo == 1) selected @endif value="1">Saida</option>
											<option @if($devolucao->tipo == 0) selected @endif value="0">Entrada</option>
										</select>

									</div>

									<div class="form-group validated col-lg-4 col-md-4 col-sm-4">
										<label class="col-form-label">Transportadora</label>

										<select class="custom-select form-control" id="transportadora_id" name="transportadora_id">
											<option value="0">--</option>
											@foreach($transportadoras as $t)
											<option
											@if($devolucao->transportadora_id != null)
											@if($devolucao->transportadora_id == $t->id)
											selected
											@endif
											@endif
											value="{{$t->id}}"
											>{{$t->razao_social}}</option>
											@endforeach
										</select>

									</div>

									
								</div>
							</div>

							<hr>
							<div class="col-sm-12">
								<div class="row">
									<div class="form-group validated col-12">
										<h3>Frete</h3>
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Valor do frete</label>
										<input class="form-control money" type="text" value="{{number_format($devolucao->vFrete, 2, ',', '.')}}" id="valor_frete" name="valor_frete">
									</div>


									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label" id="">Tipo</label>
										<select class="custom-select form-control" id="tipo_frete" name="tipo_frete">
											<option @if($devolucao->frete_tipo == 0) selected @endif value="0">0 - Emitente</option>
											<option @if($devolucao->frete_tipo == 1) selected @endif  value="1">1 - Destinatário</option>
											<option @if($devolucao->frete_tipo == 2) selected @endif  value="2">2 - Terceiros</option>
											<option @if($devolucao->frete_tipo == 9) selected @endif  value="9">9 - Sem Frete</option>
										</select>
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Placa</label>
										<input class="form-control" data-mask="AAA-AAAA" type="text" value="{{$devolucao->veiculo_placa}}" id="placa" name="placa">
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">UF</label>
										<select class="custom-select form-control" id="uf_placa">
											@foreach(App\Models\Cidade::estados() as $u)
											<option @if($u == $devolucao->veiculo_uf) selected=" @endif" value="{{$u}}">{{$u}}</option>
											@endforeach
										</select>
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Quantidade</label>
										<input class="form-control" type="text" value="{{$devolucao->frete_quantidade}}" id="qtd" name="quantidade">
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Espécie</label>
										<input class="form-control" type="text" value="{{$devolucao->frete_especie}}" id="especie" name="especie">
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Peso bruto</label>
										<input class="form-control" type="text" value="{{$devolucao->frete_peso_bruto}}" id="peso_bruto" name="peso_bruto">
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Peso liquído</label>
										<input class="form-control" type="text" value="{{$devolucao->frete_peso_liquido}}" id="peso_liquido" name="peso_liquido">
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Outras despesas</label>
										<input class="form-control money" type="text" value="{{$devolucao->despesa_acessorias}}" id="valor_outros" name="valor_outros">
									</div>

									<div class="form-group validated col-lg-2 col-md-4 col-sm-2">
										<label class="col-form-label">Desconto</label>
										<input class="form-control money" type="text" value="{{$devolucao->vDesc}}" id="vDesc" name="vDesc">
									</div>
								</div>
							</div>
							<hr>

							<div class="row">

								<div class="form-group validated col-lg-6 col-md-8 col-sm-12">
									<label class="col-form-label">Motivo</label>
									<textarea class="form-control" id="motivo" placeholder="Motivo" rows="3">{{$devolucao->motivo}}</textarea>

								</div>
								
								<div class="form-group validated col-lg-6 col-md-8 col-sm-12">
									<label class="col-form-label">Observação</label>
									<textarea class="form-control" id="obs" placeholder="Observação" rows="3">{{$devolucao->observacao}}</textarea>

								</div>
							</div>
						</div>

						<div class="col-xl-12">
							<div class="row">
								<div class="col-xl-12">

									<div class="col-xl-6">
										<h4>Valor Integral da Nota: <strong id="valorDaNF" class="text-danger">R$ {{number_format((float)$dadosNf['vProd'], 2, ',', '.')}}</strong></h4>

									</div>
									<div class="col-xl-3">
									</div>
									<div class="col-xl-3">
										<button id="update-devolucao" style="width: 100%" type="submit" class="btn btn-success">
											<i class="la la-check"></i>
											<span class="">Atualizar</span>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal2" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="data"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<input id="idEdit" type="hidden" value="">

				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12 col-12">
						<label class="col-form-label" id="">Nome do Item</label>
						<input type="text" placeholder="Nome" id="nomeEdit" name="nomeEdit" class="form-control" value="">
					</div>

					<div class="form-group validated col-sm-4 col-lg-3 col-6">
						<label class="col-form-label" id="">Quantidade</label>
						<input type="text" placeholder="Valor" id="quantidadeEdit" name="quantidadeEdit" class="form-control qCom2" value="">
					</div>

					<div class="form-group validated col-sm-4 col-lg-3 col-6">
						<label class="col-form-label" id="">Valor unitário</label>
						<input type="text" placeholder="Valor" id="valorEdit" name="valorEdit" class="form-control valor_pizza" value="">
					</div>

					<div class="form-group validated col-sm-4 col-lg-3 col-6">
						<label class="col-form-label" id="">Valor do frete</label>
						<input type="text" placeholder="Valor do frete" id="valorFreteEdit" name="valorFreteEdit" class="form-control" value="">
					</div>

					<div class="form-group validated col-sm-4 col-lg-3 col-6">
						<label class="col-form-label" id="">% Redução BC</label>
						<input type="text" placeholder="% Redução BC" id="pRedBC" name="pRedBC" class="form-control" value="">
					</div>

					<div class="form-group validated col-sm-4 col-lg-3 col-6">
						<label class="col-form-label" id="">Cod. benefício</label>
						<input type="text" placeholder="Cod. benefício" id="cBenef" name="cBenef" class="form-control" value="">
					</div>
				</div>

				<div class="row">
					<div class="form-group validated col-lg-8 col-md-10 col-sm-8">
						<label class="col-form-label">CST/CSOSN</label>

						<select class="custom-select form-control" id="CST_CSOSN" name="CST_CSOSN">
							@foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
							<option value="{{$key}}">{{$key}} - {{$c}}
							</option>
							@endforeach
						</select>

					</div>

					<div class="form-group validated col-sm-6 col-lg-4 col-4">
						<label class="col-form-label" id="">%ICMS</label>
						<input type="text" placeholder="" id="icms" name="icms" class="form-control " value="">
					</div>
				</div>

				<div class="row">
					<div class="form-group validated col-lg-8 col-md-10 col-sm-8">
						<label class="col-form-label">CST/PIS</label>

						<select class="custom-select form-control" id="CST_PIS" name="CST_CSOSN">
							@foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
							<option value="{{$key}}">{{$key}} - {{$c}}
							</option>
							@endforeach
						</select>

					</div>
					<div class="form-group validated col-sm-6 col-lg-4 col-4">
						<label class="col-form-label" id="">%PIS</label>
						<input type="text" placeholder="" id="pis" name="pis" class="form-control " value="">
					</div>
				</div>

				<div class="row">
					<div class="form-group validated col-lg-8 col-md-10 col-sm-8">
						<label class="col-form-label">CST/COFINS</label>

						<select class="custom-select form-control" id="CST_COFINS" name="CST_COFINS">
							@foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
							<option value="{{$key}}">{{$key}} - {{$c}}
							</option>
							@endforeach
						</select>

					</div>
					<div class="form-group validated col-sm-6 col-lg-4 col-4">
						<label class="col-form-label" id="">%COFINS</label>
						<input type="text" placeholder="" id="cofins" name="cofins" class="form-control " value="">
					</div>
				</div>

				<div class="row">
					<div class="form-group validated col-lg-8 col-md-10 col-sm-8">
						<label class="col-form-label">CST/IPI</label>

						<select class="custom-select form-control" id="CST_IPI" name="CST_IPI">
							@foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
							<option value="{{$key}}">{{$key}} - {{$c}}
							</option>
							@endforeach
						</select>

					</div>
					<div class="form-group validated col-sm-6 col-lg-4 col-4">
						<label class="col-form-label" id="">%IPI</label>
						<input type="text" placeholder="" id="ipi" name="ipi" class="form-control " value="">
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="salvarEdit" class="btn btn-success font-weight-bold">OK</button>
			</div>
		</div>
	</div>
</div>

@endsection	