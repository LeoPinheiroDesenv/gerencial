@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="row @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">
			<h4>Relatórios de OS</h4>
		</div>
		<br>

		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

			<form class="row" method="get">
				<div class="col-md-2">
					<label>Data Inicial</label>
					<input type="date" class="form-control" name="start_date" value="{{ $start_date }}">
				</div>
				<div class="col-md-2">
					<label>Data Final</label>
					<input type="date" class="form-control" name="end_date" value="{{ $end_date }}">
				</div>
				<div class="col-lg-2 col-xl-2 mt-2 mt-lg-0">
					<br>
					<button style="margin-top: 7px;" class="btn btn-light-primary px-6 font-weight-bold">Filtrar</button>
				</div>
			</form>
		</div>

		<div class="row">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Cliente</th>
							<th>Data de cadastro</th>
							<th>Valor</th>
							<th>Data de entrega</th>
						</tr>
					</thead>
					<tbody>
						@forelse($data as $item)
						<tr>
							<td>{{ $item->numero_sequencial }}</td>
							<td>{{ $item->cliente->razao_social }}</td>
							<td>{{ __date($item->created_at) }}</td>
							<td>{{ moeda($item->total_os()) }}</td>
							<td>{{ __date($item->data_entrega) }}</td>
						</tr>
						@empty
						<tr>
							<td colspan="5">Filtre para buscar os registros</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
			@if(sizeof($data) > 0)
			<form target="_blank" class="col-md-12" method="get" action="/ordemServico/print-relatorio">
				<input type="hidden" class="form-control" name="start_date" value="{{ $start_date }}">
				<input type="hidden" class="form-control" name="end_date" value="{{ $end_date }}">

				<button class="btn btn-dark">
					<i class="la la-print"></i> Imprimir
				</button>
			</form>
			@endif
		</div>
	</div>
</div>

@endsection	