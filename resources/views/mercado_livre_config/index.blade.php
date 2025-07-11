@extends('default.layout', ['title' => 'Configuração Mercado Livre'])
@section('content')

<style type="text/css">
	.img-template img{
		width: 300px;
		border: 1px solid #999;
		border-radius: 10px;
	}

	.img-template-active img{
		width: 300px;
		border: 3px solid green;
		border-radius: 10px;
	}

	.template:hover{
		cursor: pointer;
	}

	#btn_token:hover{
		cursor: pointer;
	}
</style>
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="post" action="{{ route('config-mercado-livre.store') }}">

					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<h3 class="card-title">Configuração Mercado Livre</h3>
						</div>
					</div>
					@csrf
					<div class="row">
						<div class="col-xl-12">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">
									<div class="row">
										<div class="form-group validated col-md-3 col-12">
											<label class="col-form-label">Client ID</label>
											<div class="">
												<input required id="client_id" type="text" class="form-control @if($errors->has('client_id')) is-invalid @endif" name="client_id" value="{{{ $item != null ? $item->client_id : old('client_id') }}}">
												@if($errors->has('client_id'))
												<div class="invalid-feedback">
													{{ $errors->first('client_id') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-md-4 col-12">
											<label class="col-form-label">Client Secret</label>
											<div class="">
												<input required id="client_secret" type="text" class="form-control @if($errors->has('client_secret')) is-invalid @endif" name="client_secret" value="{{{ $item != null ? $item->client_secret : old('client_secret') }}}">
												@if($errors->has('client_secret'))
												<div class="invalid-feedback">
													{{ $errors->first('client_secret') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-md-5 col-12">
											<label class="col-form-label">Url redirecionamento</label>
											<div class="">
												<input required id="url" type="text" class="form-control @if($errors->has('url')) is-invalid @endif" name="url" value="{{{ $item != null ? $item->url : old('url') }}}">
												@if($errors->has('url'))
												<div class="invalid-feedback">
													{{ $errors->first('url') }}
												</div>
												@endif

											</div>
										</div>

									</div>

									<div class="row">
										@if($item)
										<div class="col-12">
											Access Token: <strong>{{ $item->access_token }}</strong>
										</div>
										<div class="col-12">
											Refresh Token: <strong>{{ $item->refresh_token }}</strong>
										</div>
										<div class="col-6">
											User ID: <strong>{{ $item->user_id }}</strong>
										</div>
										<div class="col-6">
											Code: <strong>{{ $item->code }}</strong>
										</div>
										@endif
										<div class="col-12">
											@if($item != null)
											<a href="{{ route('mercado-livre.get-code') }}">Solicitar novo token</a>
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
								<a style="width: 100%" class="btn btn-danger" href="{{ route('config-mercado-livre.index') }}">
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
<script type="text/javascript">

</script>
@endsection
@endsection