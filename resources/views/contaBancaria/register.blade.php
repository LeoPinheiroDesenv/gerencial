@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="post" action="{{{ isset($conta) ? '/contaBancaria/update': '/contaBancaria/save' }}}" enctype="multipart/form-data">
					<input type="hidden" name="id" value="{{{ isset($conta) ? $conta->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">{{{ isset($conta) ? "Editar": "Cadastrar" }}} Conta Boleto</h3>
						</div>
					</div>
					@csrf

					<div class="row">
						<div class="col-xl-12">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row">

										<div class="form-group validated col-lg-4 col-md-4 col-sm-6">
											<label class="col-form-label">Banco</label>

											<select class="custom-select form-control" id="categoria_id" name="banco">
												@foreach(App\Models\ContaBancaria::bancos() as $key => $b)
												<option value="{{$b}}" @isset($conta)
												@if($b == $conta->banco) selected @endif @endisset >{{$key}} - {{$b}}</option>
												@endforeach

											</select>

										</div>

										<div class="form-group validated col-sm-3 col-lg-2">
											<label class="col-form-label">Agencia</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('agencia')) is-invalid @endif" name="agencia" value="{{{ isset($conta) ? $conta->agencia : old('agencia') }}}">
												@if($errors->has('agencia'))
												<div class="invalid-feedback">
													{{ $errors->first('agencia') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-2">
											<label class="col-form-label">Conta</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('conta')) is-invalid @endif" name="conta" value="{{{ isset($conta) ? $conta->conta : old('conta') }}}">
												@if($errors->has('conta'))
												<div class="invalid-feedback">
													{{ $errors->first('conta') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label">Titular</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('titular')) is-invalid @endif" name="titular" value="{{{ isset($conta) ? $conta->titular : old('titular') }}}">
												@if($errors->has('titular'))
												<div class="invalid-feedback">
													{{ $errors->first('titular') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label">CPF/CNPJ</label>
											<div class="">
												<input id="" type="text" class="form-control @if($errors->has('cnpj')) is-invalid @endif cpf_cnpj" name="cnpj" value="{{{ isset($conta) ? $conta->cnpj : old('cnpj') }}}">
												@if($errors->has('cnpj'))
												<div class="invalid-feedback">
													{{ $errors->first('cnpj') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-lg-4 col-md-5 col-sm-10">
											<label class="col-form-label">Endereço</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('endereco')) is-invalid @endif" name="endereco" value="{{{ isset($conta) ? $conta->endereco : old('endereco') }}}">
												@if($errors->has('endereco'))
												<div class="invalid-feedback">
													{{ $errors->first('endereco') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-lg-3 col-md-5 col-sm-10">
											<label class="col-form-label">Bairro</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('bairro')) is-invalid @endif" name="bairro" value="{{{ isset($conta) ? $conta->bairro : old('bairro') }}}">
												@if($errors->has('bairro'))
												<div class="invalid-feedback">
													{{ $errors->first('bairro') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-lg-2 col-md-5 col-sm-10">
											<label class="col-form-label">CEP</label>
											<div class="">
												<input type="text" id="cep" class="form-control @if($errors->has('cep')) is-invalid @endif" name="cep" value="{{{ isset($conta) ? $conta->cep : old('cep') }}}">
												@if($errors->has('cep'))
												<div class="invalid-feedback">
													{{ $errors->first('cep') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-lg-4 col-md-5 col-sm-10">
											<label class="col-form-label">Cidade</label>
											<select class="form-control select2" id="kt_select2_1" name="cidade_id">
												@foreach($cidades as $c)
												<option value="{{$c->id}}" @isset($conta) @if($c->id == $conta->cidade_id) selected @endif @endisset 
													@if(old('cidade') == $c->id)
													selected
													@endif
													>
													{{$c->nome}} ({{$c->uf}})
												</option>
												@endforeach
											</select>
											@if($errors->has('cidade'))
											<div class="invalid-feedback">
												{{ $errors->first('cidade') }}
											</div>
											@endif
										</div>
									</div>
									<div class="row">
										<div class="col-12">
											<h2>Padrão</h2>
										</div>

										<div class="form-group validated col-sm-3 col-lg-2">
											<label class="col-form-label">Padrão para emissão</label>

											<div class="switch switch-outline switch-info">
												<label class="">
													<input @if(isset($conta) && $conta->padrao) checked @endisset value="true" name="padrao" class="red-text" type="checkbox">
													<span class="lever"></span>
												</label>
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-2">
											<label class="col-form-label">Usar logo</label>

											<div class="switch switch-outline switch-danger">
												<label class="">
													<input @if(isset($conta) && $conta->usar_logo) checked @endisset value="true" name="usar_logo" class="red-text" type="checkbox">
													<span class="lever"></span>
												</label>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">Carteira</label>
											<div class="">
												<div class="input-group">
													<input name="carteira" value="{{{ isset($conta) ? $conta->carteira : old('carteira') }}}" type="text" class="form-control @if($errors->has('carteira')) is-invalid @endif"/>
													@if($errors->has('carteira'))
													<div class="invalid-feedback">
														{{ $errors->first('carteira') }}
													</div>
													@endif
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">Convênio</label>
											<div class="">
												<div class="input-group">
													<input name="convenio" value="{{{ isset($conta) ? $conta->convenio : old('convenio') }}}" type="text" class="form-control @if($errors->has('convenio')) is-invalid @endif"/>
													@if($errors->has('convenio'))
													<div class="invalid-feedback">
														{{ $errors->first('convenio') }}
													</div>
													@endif
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">N° Boleto</label>
											<div class="">
												<div class="input-group">
													<input name="numero_boleto" value="{{{ isset($conta) ? $conta->numero_boleto : old('numero_boleto') }}}" type="text" class="form-control @if($errors->has('numero_boleto')) is-invalid @endif"/>
													@if($errors->has('numero_boleto'))
													<div class="invalid-feedback">
														{{ $errors->first('numero_boleto') }}
													</div>
													@endif
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">N° Posto</label>
											<div class="">
												<div class="input-group">
													<input name="numero_posto" value="{{{ isset($conta) ? $conta->numero_posto : old('numero_posto') }}}" type="text" class="form-control @if($errors->has('numero_posto')) is-invalid @endif"/>
													@if($errors->has('numero_posto'))
													<div class="invalid-feedback">
														{{ $errors->first('numero_posto') }}
													</div>
													@endif
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">Juros</label>
											<div class="">
												<div class="input-group">
													<input value="{{ isset($conta) ? number_format($conta->juros, 2, '.', '') : number_format(old('juros', 0), 2, '.', '') }}" name="juros" type="text" class="form-control money-p"/>
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">Multa</label>
											<div class="">
												<div class="input-group">
													<input value="{{ isset($conta) ? number_format($conta->multa, 2, '.', '') : number_format(old('multa', 0), 2, '.', '') }}" name="multa" type="text" class="form-control money-p"/>
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">Juros após (dias)</label>
											<div class="">
												<div class="input-group">
													<input value="{{{ isset($conta) ? $conta->juros_apos : old('juros_apos') }}}" name="juros_apos" type="text" class="form-control"/>
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">Tipo</label>
											<div class="">
												<div class="input-group">
													<select name="tipo" class="custom-select">
														<option @if(isset($conta)) @if($conta->tipo == 'Cnab400') selected @endif @else @if(old('tipo') == 'Cnab400') selected @endif @endif value="Cnab400">Cnab400</option>
														<option @if(isset($conta)) @if($conta->tipo == 'Cnab240') selected @endif @else @if(old('tipo') == 'Cnab240') selected @endif @endif value="Cnab240">Cnab240</option>
													</select>
												</div>
											</div>
										</div>

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
											<label class="col-form-label">Número da Remessa</label>
											<div class="">
												<div class="input-group">
													<input name="numero_remessa" value="{{{ isset($conta) ? $conta->numero_remessa : old('numero_remessa') }}}" type="text" class="form-control @if($errors->has('numero_remessa')) is-invalid @endif"/>
													@if($errors->has('numero_remessa'))
													<div class="invalid-feedback">
													   {{ $errors->first('numero_remessa') }}
													</div>
													@endif
												</div>
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
								<a style="width: 100%" class="btn btn-danger" href="/contaBancaria">
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
@section('javascript')
  <!-- Se o Metronic não estiver carregando o jQuery automaticamente, inclua-o aqui;
       caso contrário, remova a inclusão duplicada. -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Inclua o jQuery Migrate para restaurar a propriedade jQuery.browser -->
  <script src="https://code.jquery.com/jquery-migrate-3.3.2.min.js"></script>
  
  <!-- Plugin maskMoney: carregue-o após o jQuery e o jQuery Migrate -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
  
  <!-- Plugin select2 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  
  <!-- Plugin timepicker -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
  
  <!-- Seu script de inicialização -->
  <script type="text/javascript">
  $(document).ready(function(){
    $('.money-p').maskMoney({
        prefix: '',
        allowNegative: false,
        thousands: ',',
        decimal: '.',
        affixesStay: false,
        precision: 2
    }).maskMoney('mask');
  });
      
      // Aqui você pode incluir o restante do seu código (por exemplo, para select2, timepicker, etc.)
    });
  </script>
@endsection
@endsection
