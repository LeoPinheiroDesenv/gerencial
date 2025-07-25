@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="post" action="/transportadoras/{{{ isset($transp) ? 'update' : 'save' }}}">

					<input type="hidden" name="id" value="{{{ isset($transp) ? $transp->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<h3 class="card-title">{{isset($transp) ? 'Editar' : 'Nova'}} Transportadora</h3>
						</div>
					</div>
					@csrf

					<div class="row">
						<div class="col-xl-12">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row">
										<div class="form-group col-sm-12 col-lg-12">
											<label>Pessoa:</label>
											<div class="radio-inline">
												<label class="radio radio-success">
													<input name="group1" type="radio" id="pessoaFisica" @if(isset($transp)) @if(strlen($transp->cnpj_cpf)
													< 15) checked @endif @endif />
													<span></span>
													FISICA
												</label>
												<label class="radio radio-success">
													<input name="group1" type="radio" id="pessoaJuridica" @if(isset($transp)) @if(strlen($transp->cnpj_cpf) > 15) checked @endif @endif/>
													<span></span>
													JURIDICA
												</label>

											</div>

										</div>
									</div>
									<div class="row">

										<div class="form-group validated col-sm-3 col-lg-4">
											<label class="col-form-label" id="lbl_cpf_cnpj">CPF</label>
											<div class="">
												<input type="text" id="cpf_cnpj" class="form-control @if($errors->has('cnpj_cpf')) is-invalid @endif" name="cnpj_cpf" value="{{{ isset($transp) ? $transp->cnpj_cpf : old('cnpj_cpf') }}}">
												@if($errors->has('cnpj_cpf'))
												<div class="invalid-feedback">
													{{ $errors->first('cnpj_cpf') }}
												</div>
												@endif
											</div>
										</div>
										<!-- <div class="form-group validated col-lg-2 col-md-2 col-sm-6">
											<label class="col-form-label">UF</label>

											<select class="custom-select form-control" id="sigla_uf" name="sigla_uf">
												@foreach($estados as $c)
												<option @if(isset($transp)) @if($transp->cidade->uf == $c) selected @endif @endif value="{{$c}}">{{$c}}</option>
												@endforeach
											</select>

										</div> -->
										<div class="form-group validated col-lg-2 col-md-2 col-sm-6">
											<br><br>
											<a type="button" id="btn-consulta-cadastro" onclick="consultaCadastro()" class="btn btn-success spinner-white spinner-right">
												<span>
													<i class="fa fa-search"></i>
												</span>
											</a>
										</div>

									</div>

									<div class="row">
										<div class="form-group validated col-sm-10 col-lg-6">
											<label class="col-form-label">Razao Social/Nome</label>
											<div class="">
												<input id="razao_social" type="text" class="form-control @if($errors->has('razao_social')) is-invalid @endif" name="razao_social" value="{{{ isset($transp) ? $transp->razao_social : old('razao_social') }}}">
												@if($errors->has('razao_social'))
												<div class="invalid-feedback">
													{{ $errors->first('razao_social') }}
												</div>
												@endif
											</div>
										</div>
										<div class="form-group validated col-sm-8 col-lg-3">
											<label class="col-form-label">Email</label>
											<div class="">
												<input id="email" type="text" class="form-control @if($errors->has('email')) is-invalid @endif" name="email" value="{{{ isset($transp) ? $transp->email : old('email') }}}">
												@if($errors->has('email'))
												<div class="invalid-feedback">
													{{ $errors->first('email') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<hr>

									<div class="row">
										<div class="form-group validated col-sm-8 col-lg-4">
											<label class="col-form-label">Logradouro</label>
											<div class="">
												<input id="rua" type="text" class="form-control @if($errors->has('logradouro')) is-invalid @endif" name="logradouro" value="{{{ isset($transp) ? $transp->logradouro : old('logradouro') }}}">
												@if($errors->has('logradouro'))
												<div class="invalid-feedback">
													{{ $errors->first('logradouro') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-2 col-lg-2">
											<label class="col-form-label">Número</label>
											<div class="">
												<input id="numero" type="text" class="form-control @if($errors->has('numero')) is-invalid @endif" name="numero" value="{{{ isset($transp) ? $transp->numero : old('numero') }}}">
												@if($errors->has('numero'))
												<div class="invalid-feedback">
													{{ $errors->first('numero') }}
												</div>
												@endif
											</div>
										</div>
										
										<div class="form-group validated col-lg-4 col-md-6 col-sm-10">
											<label class="col-form-label">Cidade</label>

											<select class="form-control select2" id="kt_select2_1" name="cidade">
												@foreach($cidades as $c)
												<option value="{{$c->id}}" @isset($transp) @if($c->id == $transp->cidade_id)
													selected
													@endif
													@endisset >{{$c->nome}} ({{$c->uf}})
												</option>
												@endforeach
											</select>
											@if($errors->has('cidade'))
											<div class="invalid-feedback">
												{{ $errors->first('cidade') }}
											</div>
											@endif
										</div>

										<div class="form-group validated col-sm-8 col-lg-3">
											<label class="col-form-label">Telefone</label>
											<div class="">
												<input id="telefone" type="text" class="form-control @if($errors->has('telefone')) is-invalid @endif" name="telefone" value="{{{ isset($transp) ? $transp->telefone : old('telefone') }}}">
												@if($errors->has('telefone'))
												<div class="invalid-feedback">
													{{ $errors->first('telefone') }}
												</div>
												@endif
											</div>
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
								<a style="width: 100%" class="btn btn-danger" href="/transportadoras">
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