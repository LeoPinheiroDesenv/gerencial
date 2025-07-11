@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>

				<div class="card card-custom gutter-b example example-compact">

					<h3 class="m-5">Receber Conta</h3>

					@csrf

					<input type="hidden" value="{{$valor - $conta->valor_integral}}" id="diferenca" name="">
					<input type="hidden" value="{{$conta->valor_integral}}" id="valor_padrao" name="">
					<input type="hidden" value="{{$tipo_pagamento}}" name="tipo_pagamento">

					<div class="row m-2">
						<div class="col-md-6">

							@if($conta->venda_id != null)
							<h5>Fornecedor: <strong>{{$conta->venda->cliente->razao_social}}</strong></h5>
							@endif

							<h5>Data de registro: <strong>{{ \Carbon\Carbon::parse($conta->data_registro)->format('d/m/Y')}}</strong></h5>
							<h5>Data de vencimento original: <strong>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y')}}</strong></h5>
							<h5>Valor Integral: <strong class="text-primary">{{ number_format($conta->valor_integral, 2, ',', '.') }}</strong></h5>
							<h5>Valor informado do cliente: <strong class="text-info">{{ number_format($valor, 2, ',', '.') }}</strong></h5>

							
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

						@if(sizeof($contasParaReceber) > 0)
						<p class="text-danger">* Cliente sobre as contas para selecionar e agrupar o recebimento</p>

						<h3>Somatório: <strong id="somatorio">{{$conta->valor_integral}}</strong></h3>
						@endif

						<div class="col-12">

							<form id="form" class="row" method="post" action="/contasReceber/receberComDivergencia">
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
										<span class="">Receber incluindo uma nova conta</span>
									</button>
								</div>
								<div class="col-md-4">
									<br>
									<button id="btn-finalizar-conta" type="button" class="btn btn-dark w-100 mt-1">
										<i class="la la-money-check-alt"></i>
										<span class="">Efetuar recebimento sem lançar diferença</span>
									</button>
								</div>
							</form>
						</div>

						<div class="row">

							@foreach($contasParaReceber as $c)
							<div class="col-lg-6 col-xl-6 col-sm-6">
								<div onclick="adicionarConta('{{$c->id}}', '{{$c->valor_integral}}')" id="div_{{$c->id}}" class="card card-custom gutter-b example example-compact">
									<div class="card-body">

										<h3 class="card-title">Vencimento: <strong>{{ \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y')}}</strong></h3><br>
										<h3 class="card-title">Valor: <strong>{{ number_format($c->valor_integral, 2, ',', '.') }}</strong></h3>
									</div>
								</div>
							</div>
							@endforeach
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
							<a style="width: 100%" class="btn btn-danger" href="/contasReceber">
								<i class="la la-close"></i>
								<span class="">Cancelar</span>
							</a>
						</div>

						@if($conta->valor_integral > $valor)
						<form method="post" action="/contasReceber/receberComDivergencia">
							@csrf
							<input type="hidden" name="id" value="{{$conta->id}}">
							<input type="hidden" name="valor" value="{{$valor}}">
							<input type="hidden" value="{{$tipo_pagamento}}" name="tipo_pagamento">

							<!-- <button style="width: 100%;" type="submit" class="btn btn-success">
								<i class="la la-check"></i>
								<span class="">Receber incluindo uma nova conta</span>
							</button> -->

						</form>
						@endif

						@if($conta->valor_integral > $valor)
						<form method="post" action="/contasReceber/receberSomente">
							@csrf
							<input type="hidden" name="id" value="{{$conta->id}}">
							<input type="hidden" name="valor" value="{{$valor}}">
							<input type="hidden" value="{{$tipo_pagamento}}" name="tipo_pagamento">

								<!-- <button style="width: 100%;margin-left: 10px;" type="submit" class="btn btn-info">
									<i class="la la-check"></i>
									<span class="">Somente receber</span>
								</button> -->

							</form>

							@else

							<form method="post" action="/contasReceber/receberComOutros">
								@csrf
								<input type="hidden" name="id" value="{{$conta->id}}">
								<input type="hidden" name="valor" value="{{$valor}}">
								<input type="hidden" value="{{$tipo_pagamento}}" name="tipo_pagamento">
								<input type="hidden" id="contas" name="contas" value="">

								<button style="width: 100%;margin-left: 10px;" type="submit" class="btn btn-success">
									<i class="la la-check"></i>
									<span class="">Receber conta(s)</span>
								</button>

							</form>

							@endif


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
