@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>

				<div class="card card-custom gutter-b example example-compact">

					<h3 class="m-5">Pagar Conta</h3>

					@csrf

					<input type="hidden" value="{{$valor - $conta->valor_integral}}" id="diferenca" name="">
					<input type="hidden" value="{{$conta->valor_integral}}" id="valor_padrao" name="">
					<input type="hidden" value="{{$tipo_pagamento}}" name="tipo_pagamento">

					<div class="row m-2">
						<div class="col-md-6">

							@if($conta->compra_id != null)
							<h5>Fornecedor: <strong>{{$conta->compra->fornecedor->razao_social}}</strong></h5>
							@endif

							<h5>Data de registro: <strong>{{ \Carbon\Carbon::parse($conta->data_registro)->format('d/m/Y')}}</strong></h5>
							<h5>Data de vencimento original: <strong>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y')}}</strong></h5>
							<h5>Valor Integral: <strong class="text-primary">{{ number_format($conta->valor_integral, 2, ',', '.') }}</strong></h5>
							<h5>Valor informado: <strong class="text-info">{{ number_format($valor, 2, ',', '.') }}</strong></h5>

							
						</div>
						<div class="col-md-6">
							@if($conta->valor_integral > $valor)
							<h5>Diferença: <strong class="text-danger">{{ number_format($conta->valor_integral - $valor, 2, ',', '.') }}</strong></h5>
							@else
							<h5>Diferença: <strong class="text-success">{{ number_format($valor - $conta->valor_integral, 2, ',', '.') }}</strong></h5>
							@endif
							<h5>Categoria: <strong>{{$conta->categoria->nome}}</strong></h5>
							<h5>Referencia: <strong>{{$conta->referencia}}</strong></h5>
						</div>
						<br>

						<div class="col-12">
							<form id="form" class="row" method="post" action="/contasPagar/pagarComDivergencia">
								@csrf
								<input type="hidden" name="id" value="{{$conta->id}}">
								<input type="hidden" name="valor" value="{{$valor}}">
								<input type="hidden" id="somente_finalizar" name="somente_finalizar" value="0">
								<input type="hidden" value="{{$tipo_pagamento}}" name="tipo_pagamento">
								<div class="col-md-3">
									<label>Novo vencimento</label>
									<input required type="date" name="nova_data" class="form-control">
								</div>
								<div class="col-md-3">
									<br>
									<button type="submit" class="btn btn-success w-100 mt-1">
										<i class="la la-check"></i>
										<span class="">Pagar incluindo uma nova conta</span>
									</button>
								</div>

								<div class="col-md-4">
									<br>
									<button id="btn-finalizar-conta" type="button" class="btn btn-dark w-100 mt-1">
										<i class="la la-money-check-alt"></i>
										<span class="">Efetuar pagamento sem lançar diferença</span>
									</button>
								</div>
							</form>
						</div>

						<div class="kt-section kt-section--first">
							<div class="kt-section__body">


							</div>
						</div>
					</div>
				</div>
				<div class="card-footer">

					<div class="row">

						<div class="col-lg-3 col-sm-6 col-md-4">
							<a style="width: 100%" class="btn btn-danger" href="/contasPagar">
								<i class="la la-close"></i>
								<span class="">Cancelar</span>
							</a>
						</div>
						
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

@endsection
@section('javascript')
<script type="text/javascript">
	$('#btn-finalizar-conta').click(() => {
		$('#somente_finalizar').val('1')
		$('#form').submit()
	})
</script>
@endsection
