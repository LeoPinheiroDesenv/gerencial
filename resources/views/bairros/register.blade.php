@extends('default.layout')
@section('content')

<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="post" action="{{{ isset($bairro) ? '/bairrosDelivery/update': '/bairrosDelivery/save' }}}">

					<input type="hidden" name="id" value="{{{ isset($bairro->id) ? $bairro->id : 0 }}}">

					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">{{isset($bairro) ? 'Editar' : 'Novo'}} Bairro</h3>
						</div>

					</div>
					@csrf

					<div class="row">

						<div class="col-xl-12">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row">
										<div class="form-group validated col-sm-6 col-lg-4">
											<label class="col-form-label">Nome</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" name="nome" value="{{{ isset($bairro) ? $bairro->nome : old('nome') }}}">
												@if($errors->has('nome'))
												<div class="invalid-feedback">
													{{ $errors->first('nome') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label">Valor de Entrega</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('valor_entrega')) is-invalid @endif money" name="valor_entrega" value="{{{ isset($bairro) ? $bairro->valor_entrega : old('valor_entrega') }}}">
												@if($errors->has('valor_entrega'))
												<div class="invalid-feedback">
													{{ $errors->first('valor_entrega') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-lg-4 col-md-5 col-sm-10">
											<label class="col-form-label text-left">Cidade</label>
											<select class="form-control select2" id="kt_select2_1" name="cidade_id">
												<option value="">Selecione</option>
												@foreach($cidades as $c)
												<option value="{{$c->id}}" @isset($bairro) @if($c->id == $bairro->cidade_id) selected @endif @endisset 
													@if(old('cidade') == $c->id)
													selected
													@endif
													>
													{{$c->nome}} ({{$c->uf}})
												</option>
												@endforeach
											</select>
											@if($errors->has('cidade_id'))
											<div class="invalid-feedback">
												{{ $errors->first('cidade_id') }}
											</div>
											@endif
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>
					<div class="card-footer">

						<div class="row">
							<div class="col-xl-2">

							</div>
							<div class="col-lg-3 col-sm-6 col-md-4">
								<a style="width: 100%" class="btn btn-danger" href="/bairrosDelivery">
									<i class="la la-close"></i>
									<span class="">Cancelar</span>
								</a>
							</div>
							<div class="col-lg-3 col-sm-6 col-md-4">
								<button style="width: 100%" type="submit" class="btn btn-success">
									<i class="la la-check"></i>
									<span class="">Salvar</span>
								</button>
							</div>

						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection