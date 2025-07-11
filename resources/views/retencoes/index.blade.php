@extends('default.layout', ['title' => 'Retenções'])
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">

		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

			<form class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft" method="get" action="">
				<div class="row">
					<div class="form-group col-lg-3 col-12">
						<label class="col-form-label">Fornecedor</label>
						<input type="text" name="fornecedor" class="form-control" value="{{ request()->fornecedor }}" />
					</div>

					<div class="form-group col-lg-2 col-12">
						<label class="col-form-label">Data início</label>
						<input type="date" name="data_inicio" class="form-control" value="{{ request()->data_inicio }}" />
					</div>

					<div class="form-group col-lg-2 col-12">
						<label class="col-form-label">Data final</label>
						<input type="date" name="data_final" class="form-control" value="{{ request()->data_final }}" />
					</div>

					<div class="col-lg-2 col-xl-2 mt-2 mt-lg-0">
						<br>
						<button style="margin-top: 17px;" class="btn btn-light-primary px-6 font-weight-bold">Filtrar</button>
						<a href="{{ route('retencoes.index') }}" style="margin-top: 17px;" class="btn btn-light-danger px-6 font-weight-bold">Limpar</a>
					</div>
				</div>
			</form>

			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th>Fornecedor</th>
							<th>Data de cadastro</th>
							<th>Valor à pagar</th>
							<th>INSS</th>
							<th>ISS</th>
							<th>PIS</th>
							<th>COFINS</th>
							<th>IR</th>
							<th>Outras retenções</th>
						</tr>
					</thead>
					<tbody>
						@foreach($data as $item)
						<tr>
							<td>{{ $item->fornecedor->razao_social }}</td>
							<td>{{ __date($item->created_at) }}</td>
							<td>{{ moeda($item->valor_integral) }}</td>
							<td>{{ moeda($item->valor_inss) }}</td>
							<td>{{ moeda($item->valor_iss) }}</td>
							<td>{{ moeda($item->valor_pis) }}</td>
							<td>{{ moeda($item->valor_cofins) }}</td>
							<td>{{ moeda($item->valor_ir) }}</td>
							<td>{{ moeda($item->outras_retencoes) }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="d-flex justify-content-between align-items-center flex-wrap">
				<div class="d-flex flex-wrap py-2 mr-3">
					{!! $data->appends(request()->all())->links() !!}
				</div>
			</div>

			<form method="get" action="{{ route('retencoes.print') }}">
				<input type="hidden" name="data_inicio" value="{{ request()->data_inicio }}">
				<input type="hidden" name="data_final" value="{{ request()->data_final }}">
				<input type="hidden" name="fornecedor" value="{{ request()->fornecedor }}">
				<button class="btn btn-dark">
					<i class="la la-print"></i> Imprimir
				</button>
			</form>
		</div>
	</div>
</div>
@endsection