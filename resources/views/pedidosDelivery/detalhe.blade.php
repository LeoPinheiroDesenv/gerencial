@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">

		<div class="card card-custom gutter-b">

			<div class="card-body">
				<h2>Pedido Delivery <strong class="text-info">#{{$pedido->id}}</strong></h2>
				<h4>Cliente: <strong class="text-success">{{$pedido->cliente->nome}}</strong></h4>
				<h4>Telefone: <strong class="text-success">{{$pedido->telefone}}</strong></h4>
				<!-- @if($pedido->app == 0)
				<h4><strong class="text-success">Pedido realizado através do WebDelivery</strong></h4>
				@else
				<h4><strong class="text-success">Pedido realizado através do App</strong></h4>
				@endif -->
				<h4>Horario: <strong class="text-success">{{ \Carbon\Carbon::parse($pedido->data_registro)->format('H:i:s')}}</strong></h4>
				<h4>Estado Atual:
					@if($pedido->estado == 'novo')
					<span class="label label-xl label-inline label-light-primary">NOVO</span>
					@elseif($pedido->estado == 'cancelado')
					<span class="label label-xl label-inline label-light-danger">CANCELADO</span>
					<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/pedidosDelivery/delete/{{ $pedido->id }}" }else{return false} })' class="btn btn-danger">
						<i class="la la-trash"></i> Apagar pedido
					</a>
					@elseif($pedido->estado == 'aprovado')
					<span class="label label-xl label-inline label-light-success">APROVADO</span>
					@else
					<span class="label label-xl label-inline label-light-info">FINALIZADO</span>
					@endif 

					<span>
						<a href="#!" onclick='swal("", "{{$pedido->motivoEstado}}", "info")' class="btn btn-light-info
							@if(empty($pedido->motivoEstado))
							disabled
							@endif">
							Motivo do estado
						</a>
					</span>
				</h4>
				@if($pedido->forma_pagamento == 'dinheiro')
				<h4>Troco para: <strong class="tent-danger">R$ {{number_format($pedido->troco_para, 2)}}</strong></h4>
				@endif

				@if($pedido->endereco_id != null)
				<a class="btn btn-info" data-toggle="modal" data-target="#modal-endereco">
					<i class="la la-map"></i> Ver Endereço
				</a>

				@if($pedido->endereco->longitude != "")
				<a class="btn btn-success" target="_blank" href="/clientesDelivery/enderecosMap/{{$pedido->endereco_id }}">
					<i class="la la-map-marked"></i> Ver Mapa
				</a>
				@endif
				@endif

				<a target="_blank" class="btn btn-danger" href="/pedidosDelivery/print/{{$pedido->id}}">
					<i class="la la-print"></i> Imprimir Pedido
				</a>
				@if($pedido->app == 1)
				<a class="btn btn-warning" data-toggle="modal" data-target="#modal-push">
					<i class="la la-bell"></i> Enviar Push
				</a>
				@else

				@if(sizeof($pedido->cliente->tokensWeb) > 0)
				<a class="btn cyan waves-light blue modal-trigger" href="#modal-push-web">
					<i class="material-icons left">notifications</i> Enviar Push Web
				</a>

				@endif
				@endif

				@if($pedido->estado == 'cancelado' || $pedido->estado == 'finalizado')
				<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/pedidosDelivery/delete/{{ $pedido->id }}" }else{return false} })' class="btn btn-danger">
					<i class="la la-trash"></i> Apagar pedido
				</a>
				@endif

				<!-- <a onclick="setaTelefone('{{$pedido->telefone}}')" class="btn btn-dark" href="#modal-sms">
					<i class="la la-send"></i> Enviar SMS
				</a> -->
			</div>
		</div>

		<div class="card card-custom gutter-b">
			<div class="card-body">
				<h4>Valor da Entrega: <strong class="text-danger">{{number_format($pedido->valor_entrega, 2 , ',', '.')}}</strong></h4>
				<h4>Total do Pedido: <strong class="text-danger">{{number_format($pedido->valor_total,2 , ',', '.')}}</strong></h4>

				<h4>Total de Itens: <strong class="text-danger">{{count($pedido->itens)}}</strong></h4>
				<h4>Forma de pagamento: <strong class="text-danger">{{ $pedido->forma_pagamento }}</strong></h4>

				@if($pedido->observacao != '')
				<h4>Observação: <strong class="text-danger">{{$pedido->observacao}}</strong></h4>
				@endif
				@if($pedido->troco_para > 0)
				<h4>Troco Para: <strong class="text-danger">{{$pedido->troco_para}}</strong></h4>
				@endif
			</div>
		</div>

		@if($pedido->forma_pagamento == 'pagseguro')
		<div class="card card-custom gutter-b">
			<div class="card-body">
				<h4 class="">Dados do Pagamento PagSeguro</h4>
				@if($pedido->pagseguro->status == '1' || $pedido->pagseguro->status == '2')
				<h5 class="text-info">CUIDADO, CONSULTE A TRANSAÇÃO, VERIFIQUE SE AUTORIZADA!!</h5>
				@endif
				<h5>Parcelas: <strong class="text-info">{{strtoupper($pedido->pagseguro->parcelas)}}</strong></h5>
				<h5>Referência: <strong class="text-info">{{strtoupper($pedido->pagseguro->referencia)}}</strong></h5>
				<h5>Código da transação: <strong class="text-info">{{strtoupper($pedido->pagseguro->codigo_transacao)}}</strong></h5>
				<h5>Número do Cartão: <strong class="text-info">{{strtoupper($pedido->pagseguro->numero_cartao)}}</strong></h5>
				<h5>CPF: <strong class="text-info">{{strtoupper($pedido->pagseguro->cpf)}}</strong></h5>
				<button id="btn-pgseguro" onclick="consultar('{{$pedido->pagseguro->codigo_transacao}}')" class="btn btn-danger spinner-white spinner-right">Consultar Transação</button>
			</div>
		</div>
		@endif

		@if(!empty($pedido->cupom))
		<div class="card card-custom gutter-b">
			<div class="card-body">
				<h4 class="center-align">Cliente utilizou cupom de desconto</h4>
				<h5>Cupom de: <strong class="text-danger">{{$pedido->cupom->tipo == 'valor' ? 'R$' : ''}} {{number_format($pedido->cupom->valor, 2)}} {{$pedido->cupom->tipo == 'percentual' ? '%' : ''}}</strong></h5>
				<h5>Cupom de Desconto: <strong class="text-danger">{{$pedido->cupom->codigo}}</strong></h5>
				<h5>Valor de Desconto: <strong class="text-danger">R$ {{$pedido->desconto}}</strong></h5>
			</div>
		</div>
		@endif
		

		<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
			<div class="row">
				<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
					<div class="row">
						<div class="col-xl-12">

							<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

								<table class="datatable-table" style="max-width: 100%; overflow: scroll">
									<thead class="datatable-head">
										<tr class="datatable-row" style="left: 0px;">
											
											<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Produto</span></th>
											<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Quantidade</span></th>
											<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Sabores</span></th>
											<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor Unitário</span></th>

											<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Status</span></th>

											<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Adicionais</span></th><th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Observação</span></th>
											<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">SubTotal</span></th>

											<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Ações</span></th>
										</tr>
									</thead>

									<tbody id="body" class="datatable-body">
										@foreach($pedido->itens as $i)
										<tr class="datatable-row">
											
											<td class="datatable-cell">
												<span class="codigo" style="width: 200px;" id="id">
													#{{$i->produto->referencia == "" ? $i->produto->id : $i->produto->referencia}} {{$i->produto->produto->nome}}
												</span>
											</td>
											<td class="datatable-cell">
												<span class="codigo" style="width: 100px;" id="id">
													{{ (int)$i->quantidade }}
												</span>
											</td>
											<td class="datatable-cell">
												<span class="codigo" style="width: 200px;" id="id">
													@if(count($i->sabores) > 0)
													@foreach($i->sabores as $s)
													{{$s->produto->produto->nome}}<br>

													@endforeach
													<label>Tamanho: {{$i->tamanho->nome()}} - {{$i->tamanho->pedacos}} pedaços</label>
													@else
													--
													@endif
												</span>
											</td>

											<td class="datatable-cell">
												<span class="codigo" style="width: 100px;" id="id">
													@if(count($i->sabores) == 0)
													{{ number_format($i->valor, 2, ',', '.') }}
													@else
													--
													@endif
												</span>
											</td>
											<td class="datatable-cell">
												<span class="codigo" style="width: 100px;" id="id">
													@if($i->status)
													<span class="label label-xl label-inline label-light-success">OK</span>
													@else
													<span class="label label-xl label-inline label-light-danger">Pendente</span>
													@endif
												</span>
											</td>
											<td class="datatable-cell">
												<span class="codigo" style="width: 100px;" id="id">
													@if(count($i->itensAdicionais) > 0)

													@foreach($i->itensAdicionais as $key => $ad)
													{{$ad->adicional->nome()}} 
													@if($key < count($i->itensAdicionais)-1)
													|
													@endif
													@endforeach

													@else
													Nenhum 
													@endif
												</span>
											</td>
											<td class="datatable-cell">
												<span class="codigo" style="width: 100px;" id="id">
													{{ $i->obseracao != '' ? $i->obseracao : '--' }}
												</span>
											</td>
											
											<td class="datatable-cell">
												<span class="codigo" style="width: 100px;" id="id">
													{{number_format($i->valor*$i->quantidade, 2, ',', '.')}}
												</span>
											</td>
											
											<td class="datatable-cell">
												<span class="codigo" style="width: 200px;" id="id">


													<a class="btn btn-danger btn-sm" onclick='swal("Atenção!", "Deseja excluir este item do pedido?", "warning").then((sim) => {if(sim){ location.href="/pedidosDelivery/deleteItem/{{ $i->id }}" }else{return false} })' href="#!">
														<i class="la la-trash"></i>				
													</a>

													@if(!$i->status)
													<a class="btn btn-success btn-sm" onclick='swal("Atenção!", "Deseja marcar este item como concluido?", "warning").then((sim) => {if(sim){ location.href="/pedidosDelivery/alterarStatus/{{ $i->id }}" }else{return false} })' href="#!">
														<i class="la la-check"></i>				
													</a>
													@endif


												</span>
											</td>

										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<input type="hidden" id="token" value="{{csrf_token()}}">
						<input type="hidden" id="cliente" value="{{$pedido->cliente->id}}">
					</div>
				</div>
			</div>
		</div>


		<div class="card card-custom gutter-b">
			<div class="card-body">
				<div class="row"> 
					@if($pedido->estado == 'novo' || $pedido->estado == 'aprovado')
					<div class="col-sm-6 col-lg-4 col-md-6 col-xl-3">
						<form action="/pedidosDelivery/alterarPedido" method="get">
							<input type="hidden" name="id" value="{{$pedido->id}}">
							<input type="hidden" name="tipo" value="cancelado">
							<button style="width: 100%" class="btn btn-light-danger" type="submit">Alterar para cancelado</button>
						</form>
					</div>
					@endif
					
					@if($pedido->estado == 'novo')
					<div class="col-sm-6 col-lg-4 col-md-6 col-xl-3">
						<form action="/pedidosDelivery/alterarPedido" method="get">
							<input type="hidden" name="id" value="{{$pedido->id}}">
							<input type="hidden" name="tipo" value="aprovado">
							<button style="width: 100%" class="btn btn-light-success" type="submit">Alterar para aprovado</button>
						</form>
					</div>
					@endif
					@if($pedido->estado == 'aprovado')
					<div class="col-sm-6 col-lg-4 col-md-6 col-xl-3">
						<form action="/pedidosDelivery/alterarPedido" method="get">
							<input type="hidden" name="id" value="{{$pedido->id}}">
							<input type="hidden" name="tipo" value="finalizado">
							<button style="width: 100%" class="btn btn-light-info" type="submit">Alterar para finalizado</button>
						</form>
					</div>
					@endif

					@if($pedido->estado == 'finalizado')
					<div class="col-sm-6 col-lg-4 col-md-6 col-xl-3">
						<a class="btn btn-success" href="/pedidosDelivery/irParaFrenteCaixa/{{$pedido->id}}">Ir para frente de caixa</a>
					</div>
					@endif
				</div>
			</div>

		</div>
	</div>
</div>

<div class="row">
	<div class="col s12">


		
	</div>
</div>

@if($pedido->endereco)
<div class="modal fade" id="modal-endereco" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">ENDEREÇO</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				
				<h5>Rua: <strong id="rua" class="text-danger">{{$pedido->endereco->rua}}, {{$pedido->endereco->numero}}</strong></h5>
				<h5>Bairro: <strong id="bairro" class="text-danger">{{$pedido->endereco->_bairro->nome}}</strong></h5>
				<h5>Referência: <strong id="referencia" class="text-danger">{{$pedido->endereco->referencia}}</strong></h5>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>

			</div>
		</div>
	</div>
</div>
@endif

<div class="modal fade" id="modal-push" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">ENVIAR Push App</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">

				<div class="row">
					<div class="form-group validated col-sm-6 col-lg-4">
						<label class="col-form-label" id="">Titulo Push</label>
						<div class="">
							<input type="text" id="titulo-push" class="form-control" value="">
						</div>
					</div>

					<div class="form-group validated col-sm-6 col-lg-8">
						<label class="col-form-label" id="">Texto Push</label>
						<div class="">
							<input type="text" id="texto-push" class="form-control" value="">
						</div>
					</div>

					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">URL Imagem (opcional)</label>
						<div class="">
							<input type="text" id="imagem-push" class="form-control" value="">
						</div>
					</div>

				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-enviar-push" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Enviar Push</button>
			</div>
		</div>
	</div>
</div>

<div id="modal-push-web" class="modal">
	<div class="row">
		<div class="col s2 offset-s5">
			<i class="material-icons large pink-text">notifications</i>
		</div>
	</div>
	<div class="modal-content">
		<div class="row">
			<div class="col s6 input-field">
				<input type="text" id="titulo-push-web">
				<label>Titulo Push</label>
			</div>
		</div>
		<div class="row">
			<div class="col s12 input-field">
				<input type="text" id="texto-push-web">
				<label>Texto Push</label>
			</div>
		</div>

		<div class="row">
			<div class="col s12 input-field">
				<input type="text" id="imagem-push-web">
				<label>URL Imagem (opcional)</label>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#!" id="btn-enviar-push-web" class="modal-action btn blue">Enviar Push Web</a>
		<a href="#!" class="modal-action modal-close btn grey">Fechar</a>
	</div>
</div>

<div class="modal fade" id="modal-consulta" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">PAGSEGURO</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				
				<h5>Referencia: <strong id="referencia"></strong></h5>
				<h5>Status: <strong id="status"></strong></h5>
				<h5>Total: <strong id="total"></strong></h5>
				<h5>Taxa: <strong id="taxa"></strong></h5>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>

			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-sms" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">ENVIAR SMS</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">

				<div class="row">
					<div class="form-group validated col-sm-6 col-lg-6">
						<label class="col-form-label" id="">Telefone SMS</label>
						<div class="">
							<input type="text" id="telefone-sms" name="telefone-sms" class="form-control" value="">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label" id="">Texto SMS</label>
						<div class="">
							<input type="text" id="texto-sms" name="texto-sms" class="form-control" value="Pedido saiu para entrega">
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="btn-enviar-sms" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Enviar SMS</button>
			</div>
		</div>
	</div>
</div>

<div id="modal-sms" class="modal">
	<div class="row">
		<div class="col s2 offset-s5">
			<i class="material-icons large orange-text">send</i>
		</div>

	</div>
	<div class="modal-content">
		<h4>Enviar SMS</h4>

		<div class="row">
			<div class="col s6 input-field">
				<input type="tel" id="telefone-sms">
				<label>Telefone SMS</label>
			</div>
		</div>
		<div class="row">
			<div class="col s12 input-field">
				<input type="text" value="Pedido saiu para entrega" id="texto-sms">
				<label>Texto SMS</label>
			</div>
		</div>

	</div>
	<div class="modal-footer">
		<a href="#!" class="modal-action modal-close btn grey">Fechar</a>
		
	</div>
</div>


@endsection	