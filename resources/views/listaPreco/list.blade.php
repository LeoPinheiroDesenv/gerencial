@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">


	<div class="card-body">
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
				<div class="row">

					<a href="/listaDePrecos/new" class="btn btn-lg btn-success">
						<i class="fa fa-plus"></i>Nova Lista de Preço
					</a>

					<a style="margin-left: 10px;" href="/listaDePrecos/pesquisa" class="btn btn-lg btn-info">
						<i class="fa fa-search"></i>Consultar Preços
					</a>
				</div>
			</div>
		</div>
		<br>
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
			<br>

			<div class="row">

				@foreach($lista as $l)


				<div class="col-sm-12 col-lg-6 col-md-6 col-xl-4">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-body">

							<h3 class="">{{$l->nome}} => {{$l->percentual_alteracao}} % | @if($l->tipo == 1) Compra @else Venda @endif | @if($l->tipo_inc_red == 1) Incremento @else Redução @endif
							</h3>
							<div class="card-toolbar">

								<a href="/listaDePrecos/edit/{{$l->id}}" class="btn btn-icon btn-circle btn-sm btn-warning mr-1"><i class="la la-pencil"></i></a>
								<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/listaDePrecos/delete/{{ $l->id }}" }else{return false} })' href="#!" class="btn btn-icon btn-circle btn-sm btn-danger mr-1"><i class="la la-trash"></i></a>
								<a href="/listaDePrecos/ver/{{$l->id}}" class="btn btn-icon btn-circle btn-sm btn-info mr-1"><i class="la la-stream"></i></a>

							</div>
						</div>

					</div>

				</div>

				@endforeach

			</div>
		</div>
	</div>
</div>

@endsection	