@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="post" action="{{{ isset($autor) ? '/autorPost/update': '/autorPost/save' }}}" enctype="multipart/form-data">
					<input type="hidden" name="id" value="{{{ isset($autor) ? $autor->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<h3 class="card-title">{{isset($autor) ? 'Editar' : 'Novo'}} Autor</h3>
						</div>
					</div>
					@csrf
					<div class="row">
						<div class="col-xl-12">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row">
										<div class="form-group validated col-sm-6 col-lg-6">
											<label class="col-form-label">Nome</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" name="nome" value="{{{ isset($autor) ? $autor->nome : old('nome') }}}">
												@if($errors->has('nome'))
												<div class="invalid-feedback">
													{{ $errors->first('nome') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-4">
											<label class="col-form-label">Tipo</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('tipo')) is-invalid @endif" name="tipo" value="{{{ isset($autor) ? $autor->tipo : old('tipo') }}}">
												@if($errors->has('tipo'))
												<div class="invalid-feedback">
													{{ $errors->first('tipo') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div>
										<div style="background-image: url(/imgs/no_image.png)">
											
										</div>

										<div class="form-group row">
											<label class="col-xl-12 col-lg-12 col-form-label text-left">Imagem</label>
											<div class="col-lg-10 col-xl-6">

												<div class="image-input image-input-outline" id="kt_image_1">
													<div class="image-input-wrapper" @if(isset($autor) && file_exists(public_path('ecommerce/autores/').$autor->img)) style="background-image: url(/ecommerce/autores/{{$autor->img}})" @else style="background-image: url(/imgs/no_image.png)" @endif></div>
													<label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
														<i class="fa fa-pencil icon-sm text-muted"></i>
														<input type="file" name="file" accept=".png, .jpg, .jpeg">
														<input type="hidden" name="profile_avatar_remove">
													</label>
													<span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="cancel" data-toggle="tooltip" title="" data-original-title="Cancel avatar">
														<i class="fa fa-close icon-xs text-muted"></i>
													</span>
												</div>


												<span class="form-text text-muted">.png, .jpg, .jpeg</span>
												@if($errors->has('file'))
												<div class="text-danger">
													{{ $errors->first('file') }}
												</div>
												@endif
												<span class="text-danger">*Recomendado 300x300</span>
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
								<a style="width: 100%" class="btn btn-danger" href="/categoriaEcommerce">
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