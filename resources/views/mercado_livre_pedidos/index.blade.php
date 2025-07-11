@extends('default.layout', ['title' => 'Pedidos no Mercado Livre'])
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<form method="get" action="/mercado-livre-pedidos">
				<div class="row align-items-center">

					<div class="form-group col-md-4 col-12">
						<label class="col-form-label">Cliente</label>

						<input type="text" name="cliente_nome" class="form-control" value="{{ isset($cliente_nome) ? $cliente_nome : '' }}" placeholder="Pesquisar por cliente">
					</div>

					<div class="form-group col-md-2 col-12">
						<label class="col-form-label">Data inícial</label>
						<input type="date" name="start_date" class="form-control" value="{{ isset($start_date) ? $start_date : '' }}">
					</div>

					<div class="form-group col-md-2 col-12">
						<label class="col-form-label">Data final</label>
						<input type="date" name="end_date" class="form-control" value="{{ isset($end_date) ? $end_date : '' }}">
					</div>
					<div class="col-lg-2 col-xl-2 mt-4">
						<button type="submit" class="btn btn-light-primary font-weight-bold">Filtrar</button>
					</div>
				</div>

			</form>

			<br>
			<h4>Lista de Pedidos</h4>

			<div class="row">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>#</th>
								<th>Cliente</th>
								<th>Data</th>
								<th>Valor total do pedido</th>
								<th>Valor de entrega</th>
								<th>Total de itens</th>
								<th width="10%">Ações</th>
							</tr>
						</thead>
						<tbody>
							@forelse($data as $item)
							<tr>

								<td>{{ $item->_id }}</td>
								<td>{{ $item->cliente_nome }} {{ $item->cliente_documento }}</td>
								<td>{{ __date($item->data_pedido) }}</td>
								<td>{{ moeda($item->total) }}</td>
								<td>{{ moeda($item->valor_entrega) }}</td>
								<td>{{ sizeof($item->itens) }}</td>
								<td>
									<form action="{{ route('mercado-livre-pedidos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
										@method('delete')
										@csrf

										<a title="Ver pedido" class="btn btn-dark btn-sm text-white" href="{{ route('mercado-livre-pedidos.show', [$item->id]) }}">
											<i class="la la-file"></i>
										</a>
										<button type="button" class="btn btn-delete btn-sm btn-danger">
											<i class="la la-trash"></i>
										</button>
									</form>
								</td>
							</tr>
							@empty
							<tr>
								<td colspan="7" class="text-center">Nada encontrado</td>
							</tr>
							@endforelse
						</tbody>
					</table>
					<br>
				</div>
			</div>
			{!! $data->appends(request()->all())->links() !!}
		</div>
	</div>
</div>
@endsection