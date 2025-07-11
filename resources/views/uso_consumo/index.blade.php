@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">

			<a href="{{ route('uso-consumo.create') }}" class="btn btn-success float-right">
				<i class="fa fa-plus"></i>Novo Registro
			</a>
		</div>
		<br>
		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
			<input type="hidden" id="_token" value="{{ csrf_token() }}">

			<form method="get" class="row">
				<div class="col-md-4">
					<label>Funcionário</label>
					<select class="select2-custom form-control" name="funcionario_id">
						<option value="">Selecione uma opção</option>
						@foreach($funcionarios as $f)
						<option @if($funcionario_id == $f->id) selected @endif value="{{ $f->id }}">{{ $f->nome }}</option>
						@endforeach
					</select>
				</div>

				<div class="col-md-2">
					<label>Data início</label>
					<input type="date" value="{{ $start_date }}" class="form-control" name="start_date">
				</div>

				<div class="col-md-2">
					<label>Data Fim</label>
					<input type="date" value="{{ $end_date }}" class="form-control" name="end_date">
				</div>
				
				<div class="col-md-3">
					<br>
					<button type="submit" class="btn btn-light-danger mt-1">Filtrar</button>
					<a href="{{ route('uso-consumo.index') }}" class="btn btn-light-dark mt-1">Limpar</a>
				</div>
			</form>

			<div class="col-xl-12 @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">

				<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

					<table class="table">
						<thead>
							<tr>

								<th>Funcionário</th>
								<th>Valor Total</th>
								<th>Data</th>
								<th>Desconto</th>
								<th>Acréscimo</th>
								<th>Ações</th>
							</tr>
						</thead>
						<tbody>
							@forelse($data as $item)
							<tr>

								<td>{{ $item->funcionario ? $item->funcionario->nome : '--' }}</td>
								<td>{{ moeda($item->valor_total) }}</td>
								<td>{{ __date($item->created_at) }}</td>
								<td>{{ moeda($item->desconto) }}</td>
								<td>{{ moeda($item->acrescimo) }}</td>
								<td>
									<form action="{{ route('uso-consumo.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 200px">
										@method('delete')
										@csrf
										<a href="{{ route('uso-consumo.edit', [$item->id]) }}" class="btn btn-sm btn-warning">
											<i class="la la-edit"></i>
										</a>

										<button class="btn btn-sm btn-danger btn-delete">
											<i class="la la-trash"></i>
										</button>

										<a target="_blank" href="{{ route('uso-consumo.print', [$item->id]) }}" class="btn btn-sm btn-dark">
											<i class="la la-print"></i>
										</a>

									</form>
								</td>

							</tr>
							@empty
							<tr>
								<td colspan="6">Nada encontrado!</td>
							</tr>
							@endforelse
						</tbody>
					</table>
				</div>

			</div>
		</div>
	</div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
	
</script>
@endsection
