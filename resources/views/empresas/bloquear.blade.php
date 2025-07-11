@extends('default.layout', ['title' => 'Bloquear Empresas'])
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
			<input type="hidden" id="_token" value="{{ csrf_token() }}">


			<h4 class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight mt-2">
				Bloquear empresas
			</h4>
			<hr>
			<form class="row">
				<div class="col-md-4">
					<label>Razão social/Nome fantasia</label>

					<select name="emp_id" class="custom-select w-100" id="inp-empresa_id">
						
					</select>
				</div>
				<div class="col-md-3">
					<label>Plano</label>
					<select class="custom-select" name="plano_id" id="plano_id">
						<option value="">Selecione</option>
						@foreach($planos as $p)
						<option @if($plano_id == $p->id) selected @endif value="{{ $p->id }}">{{ $p->nome }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label>Pagamento</label>
					<select class="custom-select" name="pagamento" id="pagamento">
						<option value="">Selecione</option>
						<option @if($pagamento == 'pago') selected @endif value="pago">Pago</option>
						<option @if($pagamento == 'pendente') selected @endif value="pendente">Pendente</option>
					</select>
				</div>
				<div class="col-md-3">
					<br>
					<button type="button" class="btn btn-light-danger btn-filtro mt-1">Filtrar</button>
					<a href="/empresas/bloqueio-empresa" class="btn btn-light-dark mt-1">Limpar</a>
				</div>
			</form>

			<form method="post" action="/empresas/bloquear-empresas">
				@csrf
				<div class="col-xl-12 @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">

					<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

						<table class="table">
							<thead>
								<tr>
									<th>
										<input type="checkbox" class="check-all">
									</th>
									<th>ID</th>
									<th>Razão social</th>
									<th>Nome fantasia</th>
									<th>Nome</th>
									<th>CPF/CNPJ</th>
									<th>Plano</th>
								</tr>
							</thead>
							<tbody class="tbody">
								<!-- @forelse($data as $item)
								<tr>
									<td>
										<input type="checkbox" name="empresa_check[]" value="{{ $item->id }}">
									</td>
									<td>{{ $item->id }}</td>
									<td>{{ $item->nome }}</td>
									<td>{{ $item->nome_fantasia }}</td>
									<td>{{ $item->cnpj }}</td>
									<td>{{ $item->planoEmpresa ? $item->planoEmpresa->plano->nome : '--' }}</td>
								</tr>
								@empty
								<tr>
									<td colspan="5">Filtre para buscar</td>
								</tr>
								@endforelse -->

								<tr>
									<td colspan="5">Filtre para buscar</td>
								</tr>
							</tbody>
						</table>
					</div>


					<div class="row div-bloqueio d-none">
						<div class="col-md-8">
							<label>Mensagem de bloqueio</label>
							<input type="text" name="mensagem" class="form-control">
						</div>
						<div class="col-md-2">
							<br>
							<button type="submit" class="btn btn-danger mt-1 w-100">Bloquear</button>
						</div>
					</div>

				</div>
			</form>
		</div>
	</div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
	var empresaSelecionadas = []
	$(document).on("click", ".btn-filtro", function () {
		console.log(empresaSelecionadas)
		let empresa_id = $('#inp-empresa_id').val()
		let plano_id = $('#plano_id').val()
		let pagamento = $('#pagamento').val()

		$.get(path + 'empresas/para-bloqueio', 
		{
			empresa_id: empresa_id,
			plano_id: plano_id,
			pagamento: pagamento,
			empresaSelecionadas: empresaSelecionadas
		}).done((res) => {
			console.log(res)
			$('.tbody').html(res)
		})
		.fail((err) => {
			console.log(err)
		})

	});

	$(document).on("click", ".checked", function () {
		percorreAdicionados()
	});

	$(document).on("click", ".check-all", function () {
		if($(this).is(':checked')){
			$('input[type=checkbox]').prop("checked", true)
		}else{
			$('input[type=checkbox]').prop('checked', false)
		}

		setTimeout(() => {
			percorreAdicionados()
		}, 10)
	})

	function percorreAdicionados(){
		empresaSelecionadas = []
		$('.div-bloqueio').addClass('d-none')
		$('.checked').each(function () {
			if($(this).is(":checked")){
				empresaSelecionadas.push($(this).val())
				$('.div-bloqueio').removeClass('d-none')
			}
		})
		setTimeout(() => {
			console.log(empresaSelecionadas)
		}, 10)
	}
</script>
@endsection
