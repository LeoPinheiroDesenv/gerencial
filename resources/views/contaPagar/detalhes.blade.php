@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<a href="/contasPagar" class="btn btn-sm btn-danger float-right mb-1">
					<i class="la la-arrow-left"></i>
					voltar
				</a>

				<div class="card card-custom gutter-b example example-compact w-100">

					<div class="card-header">

						<h3 class="card-title">Detalhes da Conta</h3>

					</div>
					<div class="card-body">
						<div class="col s12">
							@if($conta->compra_id != null)
							<h5>Fornecedor: <strong>{{$conta->compra->fornecedor->razao_social}}</strong></h5>
							@endif

							<h5>Data de registro: <strong>{{ \Carbon\Carbon::parse($conta->data_registro)->format('d/m/Y')}}</strong></h5>
							<h5>Data de vencimento: <strong>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y')}}</strong></h5>
							<h5>Valor: <strong>{{ number_format($conta->valor_integral, 2, ',', '.') }}</strong></h5>
							<h5>Valor Pago: <strong>{{ number_format($conta->valor_pago, 2, ',', '.') }}</strong></h5>
							<h5>Categoria: <strong>{{$conta->categoria->nome}}</strong></h5>
							<h5>Referencia: <strong>{{$conta->referencia}}</strong></h5>
							<h5>Observação: <strong>{{$conta->observacao}}</strong></h5>
							<h5>Observação de baixa: <strong>{{$conta->observacao_baixa}}</strong></h5>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

@endsection
