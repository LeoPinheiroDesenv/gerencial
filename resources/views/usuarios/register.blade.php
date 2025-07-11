@extends('default.layout')
@section('content')

<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="post" action="/usuarios/{{{ isset($usuario) ? 'update' : 'save' }}}">

					<input type="hidden" name="id" value="{{{ isset($usuario) ? $usuario->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<h3 class="card-title">{{isset($usuario) ? 'Editar' : 'Novo'}} Usuário</h3>
						</div>
					</div>
					@csrf

					<div class="row">
						<div class="col-xl-12">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">
									<div class="row">
										<div class="form-group validated col-sm-10 col-lg-3">
											<label class="col-form-label">Nome</label>
											<div class="">
												<input id="nome" type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" name="nome" value="{{{ isset($usuario) ? $usuario->nome : old('nome') }}}">
												@if($errors->has('nome'))
												<div class="invalid-feedback">
													{{ $errors->first('nome') }}
												</div>
												@endif
											</div>
										</div>

										@isset($usuario)
										{!! __view_locais_user_edit($usuario->locais) !!}
										@else
										{!! __view_locais_user() !!}
										@endif

										<div class="form-group validated col-sm-10 col-lg-3">
											<label class="col-form-label">Local padrão</label>
											<div class="">
												<select name="local_padrao" class="form-select form-control">
													<option value="0">--</option>
													@foreach(__locaisAtivosAll() as $key => $l)
													<option @isset($usuario) @if($usuario->local_padrao == $key) selected @endif @endif value="{{$key}}">{{$l}}</option>
													@endforeach
												</select>
											</div>
										</div>

										<div class="form-group validated col-sm-10 col-lg-3">
											<label class="col-form-label">Login</label>
											<div class="">
												<input id="login" type="text" class="form-control @if($errors->has('login')) is-invalid @endif" name="login" value="{{{ isset($usuario) ? $usuario->login : old('login') }}}">
												@if($errors->has('login'))
												<div class="invalid-feedback">
													{{ $errors->first('login') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-10 col-lg-3">
											<label class="col-form-label">Senha</label>
											<div class="">
												<input id="senha" type="password" class="form-control @if($errors->has('senha')) is-invalid @endif" name="senha" value="{{{ isset($usuario) ? '' : old('senha') }}}">
												@if($errors->has('senha'))
												<div class="invalid-feedback">
													{{ $errors->first('senha') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-10 col-lg-3">
											<label class="col-form-label">Email</label>
											<div class="">
												<input id="email" type="email" class="form-control @if($errors->has('email')) is-invalid @endif" name="email" value="{{{ isset($usuario) ? $usuario->email : old('email') }}}">
												@if($errors->has('email'))
												<div class="invalid-feedback">
													{{ $errors->first('email') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-10 col-lg-4">
											<label class="col-form-label">Rota padrão ao logar (opcional)</label>
											<button type="button" class="btn btn-light-info btn-sm btn-icon col-lg-6 col-sm-6" data-toggle="popover" data-trigger="click" data-content="Copie o caminho completo da URL, exemplo: http://meusistema.com.br/frenteCaixa"><i class="la la-info"></i></button>
											<div class="">
												<input id="rota_acesso" type="text" class="form-control @if($errors->has('rota_acesso')) is-invalid @endif" name="rota_acesso" value="{{{ isset($usuario) ? $usuario->rota_acesso : old('rota_acesso') }}}">
												@if($errors->has('rota_acesso'))
												<div class="invalid-feedback">
													{{ $errors->first('rota_acesso') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-6 col-lg-2">
											<label class="col-form-label text-left col-lg-12 col-sm-12">Ativo</label>
											<div class="col-6">
												<span class="switch switch-outline switch-warning">
													<label>
														<input id="ativo" @if(isset($usuario->ativo) && $usuario->ativo) checked @endisset
														name="ativo" type="checkbox" >
														<span></span>
													</label>
												</span>

											</div>
										</div>

										<div class="form-group validated col-sm-6 col-lg-2">
											<label class="col-form-label text-left col-lg-12 col-sm-12">ADM</label>
											<div class="col-6">
												<span class="switch switch-outline switch-primary">
													<label>
														<input id="adm" @if(isset($usuario->adm) && $usuario->adm) checked @endisset
														name="adm" type="checkbox" >
														<span></span>
													</label>
												</span>

											</div>
										</div>

										<div class="form-group validated col-sm-6 col-lg-2">
											<label class="col-form-label text-left col-lg-12 col-sm-12">Somente Fiscal</label>
											<div class="col-6">
												<span class="switch switch-outline switch-info">
													<label>
														<input id="somente_fiscal" @if(isset($usuario->somente_fiscal) && $usuario->somente_fiscal) checked @endisset
														name="somente_fiscal" type="checkbox" >
														<span></span>
													</label>
												</span>

											</div>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="col-form-label">Caixa livre</label>
											<button type="button" class="btn btn-light-warning btn-sm btn-icon col-lg-6 col-sm-6" data-toggle="popover" data-trigger="click" data-content="Marcar para selcionar o vendedor/fúncionário no PDV para comissão"><i class="la la-info"></i></button>
											<div class="col-6">
												<span class="switch switch-outline switch-warning">
													<label>
														<input id="caixa_livre" @if(isset($usuario->caixa_livre) && $usuario->caixa_livre) checked @endisset
														name="caixa_livre" type="checkbox" >
														<span></span>
													</label>
												</span>
											</div>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="col-form-label">Permite desconto em vendas</label>
											<button type="button" class="btn btn-light-danger btn-sm btn-icon col-lg-6 col-sm-6" data-toggle="popover" data-trigger="click" data-content="Marcar para usuário conceder desconto na venda PDV e pedido"><i class="la la-info"></i></button>
											<div class="col-6">
												<span class="switch switch-outline switch-danger">
													<label>
														<input id="permite_desconto" @if(isset($usuario->permite_desconto) && $usuario->permite_desconto) checked @endisset
														name="permite_desconto" type="checkbox" >
														<span></span>
													</label>
												</span>
											</div>
										</div>

										{{-- Checkbox Permite estornar --}}
                                        <div class="form-group validated col-sm-6 col-lg-3">
                                            <label class="col-form-label">Permite estornar contas a pagar</label>
                                            <button type="button"
                                                class="btn btn-light-secondary btn-sm btn-icon col-lg-6 col-sm-6"
                                                data-toggle="popover"
                                                data-trigger="click"
                                                data-content="Marcar para usuário poder estornar contas a pagar">
                                                <i class="la la-info"></i>
                                            </button>
                                            <div class="col-6">
                                                <span class="switch switch-outline switch-info">
                                                    <label>
                                                        <input id="estorna_conta_pagar"
                                                            name="estorna_conta_pagar"
                                                            type="checkbox"
                                                            {{ old('estorna_conta_pagar', $usuario->estorna_conta_pagar ?? false) ? 'checked' : '' }}
                                                        >
                                                        <span></span>
                                                    </label>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Container do Máx. Estornos --}}
                                        <div id="grupo_max_estornos"
                                            class="form-group validated col-sm-10 col-lg-3"
                                            style="display: {{ old('estorna_conta_pagar', $usuario->estorna_conta_pagar ?? false) ? 'block' : 'none' }};">
                                            <label class="col-form-label">Máx. Estornos Permitidos</label>
                                            <input
                                                id="max_estornos_conta_pagar"
                                                type="number"
                                                name="max_estornos_conta_pagar"
                                                min="0"
                                                class="form-control @error('max_estornos_conta_pagar') is-invalid @enderror"
                                                value="{{ old('max_estornos_conta_pagar', $usuario->max_estornos_conta_pagar ?? 0) }}"
                                            >
                                            @error('max_estornos_conta_pagar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

										{{-- Permite estornar Contas a Receber --}}
                                        <div class="form-group validated col-sm-6 col-lg-3">
                                            <label class="col-form-label">Permite estornar Contas a Receber</label>
                                            <div class="col-6">
                                                <span class="switch switch-outline switch-info">
                                                    <label>
                                                        <input id="estorna_conta_receber"
                                                            name="estorna_conta_receber"
                                                            type="checkbox"
                                                            {{ old('estorna_conta_receber', $usuario->estorna_conta_receber ?? false) ? 'checked' : '' }}>
                                                        <span></span>
                                                    </label>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Máx. Estornos Contas a Receber --}}
                                        <div id="grupo_max_estornos_receber" class="form-group validated col-sm-10 col-lg-3">
                                            <label class="col-form-label">Máx. Estornos Receber</label>
                                            <input type="number"
                                                name="max_estornos_conta_receber"
                                                min="0"
                                                class="form-control @error('max_estornos_conta_receber') is-invalid @enderror"
                                                value="{{ old('max_estornos_conta_receber', $usuario->max_estornos_conta_receber ?? 0) }}">
                                            @error('max_estornos_conta_receber')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

										@if(session('user_logged')['tipo_representante'] == 1)
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="col-form-label">Acesso representante</label>
											<button type="button" class="btn btn-light-dark btn-sm btn-icon col-lg-6 col-sm-6" data-toggle="popover" data-trigger="click" data-content="Marcar para usuário ter acesso ao menu representante/Contador"><i class="la la-info"></i></button>
											<div class="col-6">
												<span class="switch switch-outline switch-dark">
													<label>
														<input id="menu_representante" @if(isset($usuario->menu_representante) && $usuario->menu_representante) checked @endisset
														name="menu_representante" type="checkbox" >
														<span></span>
													</label>
												</span>
											</div>
										</div>
										@endif
									</div>

									<div class="row">
										<div class="form-group validated col-sm-12">
											
											<label class="col-3 col-form-label">Permissão de Acesso:</label>
											<input type="hidden" id="menus" value="{{json_encode($menu)}}" name="">
											@foreach($menuAux as $m)
											@if($m['ativo'] == 1)
											<div class="col-12 col-form-label">
												<span>
													<label class="checkbox checkbox-info">
														<input id="todos_{{str_replace(' ', '_', $m['titulo'])}}" onclick="marcarTudo('{{$m['titulo']}}')" type="checkbox" >
														<span></span><strong class="text-info" style="margin-left: 5px; font-size: 16px;">{{$m['titulo']}} </strong>
													</label>
												</span>
												<div class="checkbox-inline" style="margin-top: 10px;">
													@foreach($m['subs'] as $s)
													@if(in_array($s['rota'], $permissoesAtivas))
													<label class="checkbox checkbox-info check-sub">
														<input id="sub_{{str_replace('/', 	'', $s['rota'])}}" @if(in_array($s['rota'], $permissoesUsuario)) checked @endif type="checkbox" name="{{$s['rota']}}">
														<span></span>{{$s['nome']}}
													</label>
													@endif
													@endforeach
												</div>

											</div>
											@endif
											@endforeach
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
							<a style="width: 100%" class="btn btn-danger" href="/usuarios">
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

@section('javascript')
<script type="text/javascript">
	$('[data-toggle="popover"]').popover()
</script>
<script>
  $(function(){
    function toggleMaxEstornos() {
      const permit = $('#estorna_conta_pagar').is(':checked');
      $('#grupo_max_estornos').toggle(permit);
    }

    // ao carregar e ao mudar
    toggleMaxEstornos();
    $('#estorna_conta_pagar').on('change', toggleMaxEstornos);

    // popovers
    $('[data-toggle="popover"]').popover();
  });
</script>
<script>
  $(function(){
    function toggleMaxReceber(){
      const permit = $('#estorna_conta_receber').is(':checked');
      $('#grupo_max_estornos_receber').toggle(permit);
    }

    // ao carregar e ao mudar
    toggleMaxReceber();
    $('#estorna_conta_receber').on('change', toggleMaxReceber);
  });
</script>
@endsection

@endsection