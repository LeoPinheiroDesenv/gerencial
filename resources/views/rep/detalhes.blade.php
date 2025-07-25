@extends('default.layout')
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<!--begin::Portlet-->

				<form method="post" action="/rep/update">

					<input type="hidden" name="id" value="{{$empresa->id}}">
					<br>
					
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<h3 class="card-title">Dados da Empresa</h3>
						</div>
					</div>
					@csrf
					@if($empresa->contador)
					<h5>Contador: <strong>{{ $empresa->contador->razao_social }}</strong></h5>
					@endif
					
					<div class="row">
						<div class="col-xl-12">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row">
										<div class="form-group validated col-12 col-lg-6">
											<label class="col-form-label">Nome/Razão Social</label>
											<div class="">
												<input id="nome" type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" name="nome" value="{{$empresa->nome}}">
												@if($errors->has('nome'))
												<div class="invalid-feedback">
													{{ $errors->first('nome') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-12 col-lg-6">
											<label class="col-form-label">Nome Fantasia</label>
											<div class="">
												<input id="nome_fantasia" type="text" class="form-control @if($errors->has('nome_fantasia')) is-invalid @endif" name="nome_fantasia" value="{{$empresa->nome_fantasia}}">
												@if($errors->has('nome_fantasia'))
												<div class="invalid-feedback">
													{{ $errors->first('nome_fantasia') }}
												</div>
												@endif
											</div>
										</div>
										
										<div class="form-group validated col-sm-6 col-lg-4">
											<label class="col-form-label" id="lbl_ie_rg">Rua</label>
											<div class="">
												<input type="text" id="rua" class="form-control @if($errors->has('rua')) is-invalid @endif" name="rua" value="{{$empresa->rua}}">
												@if($errors->has('rua'))
												<div class="invalid-feedback">
													{{ $errors->first('rua') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label" id="lbl_ie_rg">Nº</label>
											<div class="">
												<input type="text" id="numero" class="form-control @if($errors->has('numero')) is-invalid @endif" name="numero" value="{{$empresa->numero}}">
												@if($errors->has('numero'))
												<div class="invalid-feedback">
													{{ $errors->first('numero') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label" id="lbl_ie_rg">Bairro</label>
											<div class="">
												<input type="text" id="bairro" class="form-control @if($errors->has('bairro')) is-invalid @endif" name="bairro" value="{{$empresa->bairro}}">
												@if($errors->has('bairro'))
												<div class="invalid-feedback">
													{{ $errors->first('bairro') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label" id="lbl_ie_rg">Cidade</label>
											<div class="">
												<input type="text" id="cidade" class="form-control @if($errors->has('cidade')) is-invalid @endif" name="cidade" value="{{$empresa->cidade}}">
												@if($errors->has('cidade'))
												<div class="invalid-feedback">
													{{ $errors->first('cidade') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-lg-2 col-md-2 col-sm-6">
											<label class="col-form-label">UF</label>
											<select class="custom-select form-control" id="uf2" name="uf">
												@foreach(App\Models\Cidade::estados() as $e)
												<option value="{{$e}}" @if($empresa->uf == $e) selected @endif>
													{{$e}}
												</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label" id="lbl_ie_rg">
												
												@if(strlen($empresa->cnpj) == 14)
												CPF
												@else
												CNPJ
												@endif
											</label>
											<div class="">
												<input type="text" @if(strlen($empresa->cnpj) == 14) id="cpf" @else d="cnpj" @endif class="form-control @if($errors->has('cnpj')) is-invalid @endif" name="cnpj" value="{{$empresa->cnpj}}">
												@if($errors->has('cnpj'))
												<div class="invalid-feedback">
													{{ $errors->first('cnpj') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label" id="lbl_ie_rg">Email</label>
											<div class="">
												<input type="text" id="email" class="form-control @if($errors->has('email')) is-invalid @endif" name="email" value="{{$empresa->email}}">
												@if($errors->has('email'))
												<div class="invalid-feedback">
													{{ $errors->first('email') }}
												</div>
												@endif
											</div>
										</div>
										
										<div class="form-group validated col-sm-4 col-lg-2">
											<label class="col-form-label" id="lbl_ie_rg">CEP</label>
											<div class="">
												<input type="text" id="cep" class="form-control @if($errors->has('cep')) is-invalid @endif" name="cep" value="{{ $empresa->cep }}">
												@if($errors->has('cep'))
												<div class="invalid-feedback">
													{{ $errors->first('cep') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label" id="lbl_ie_rg">Telefone</label>
											<div class="">
												<input type="text" id="telefone" class="form-control @if($errors->has('telefone')) is-invalid @endif" name="telefone" value="{{$empresa->telefone}}">
												@if($errors->has('telefone'))
												<div class="invalid-feedback">
													{{ $errors->first('telefone') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-4">
											<label class="col-form-label">Representante legal</label>
											<div class="">
												<input type="text" id="representante_legal" class="form-control @if($errors->has('representante_legal')) is-invalid @endif" name="representante_legal" value="{{ $empresa->representante_legal }}">

											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-3">
											<label class="col-form-label">CPF Representante legal</label>
											<div class="">
												<input type="text" id="cpf_representante_legal" class="form-control @if($errors->has('cpf_representante_legal')) is-invalid @endif cpfp" name="cpf_representante_legal" value="{{ $empresa->cpf_representante_legal }}">

											</div>
										</div>
										
										@if($bloquear_empresa)
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="col-form-label text-left col-lg-12 col-sm-12">Status</label>
											<div class="col-6">
												<span class="switch switch-outline switch-success">
													<label>
														<input value="true" @if($empresa->status) checked @endisset type="checkbox" name="status" id="status">
														<span></span>
													</label>
												</span>
											</div>
										</div>
										@endif

										
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
								<a style="width: 100%" class="btn btn-danger" href="/rep">
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




<div class=" d-flex flex-column flex-column-fluid" id="kt_content" style="margin-top: -23px;">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12 mt-2">

				<div class="col-xl-12">

					<div class="row">

						<div class="col-sm-4 col-lg-4 col-md-4 col-12">

							<div class="card card-custom gutter-b">
								<div class="card-header">
									<h3 class="card-title">
										Total de Cadastros
									</h3>
								</div>
								<div class="card-body" style="height: 200px;">

									<h4>Clientes: <strong class="text-info">{{sizeof($empresa->clientes)}}</strong></h4>
									<h4>Fornecedores: <strong class="text-info">{{sizeof($empresa->fornecedores)}}</strong></h4>
									<h4>Produtos: <strong class="text-info">{{sizeof($empresa->produtos)}}</strong></h4>
									<h4>Usuários: <strong class="text-info">{{sizeof($empresa->usuarios)}}</strong></h4>
									<h4>Veiculos: <strong class="text-info">{{sizeof($empresa->veiculos)}}</strong></h4>
								</div>
							</div>
						</div>

						<div class="col-sm-4 col-lg-4 col-md-4 col-12">

							<div class="card card-custom gutter-b">
								<div class="card-header">
									<h3 class="card-title">
										Total de Documentos
									</h3>
								</div>
								<div class="card-body" style="height: 200px;">

									<h4>NFe: <strong class="text-info">{{$empresa->nfes()}}</strong></h4>
									<h4>NFCe: <strong class="text-info">{{$empresa->nfces()}}</strong></h4>
									<h4>CTe: <strong class="text-info">{{$empresa->ctes()}}</strong></h4>
									<h4>MDFe: <strong class="text-info">{{$empresa->mdfes()}}</strong></h4>
									<h4>NFSe: <strong class="text-info">{{$empresa->nfses()}}</strong></h4>
								</div>
							</div>
						</div>

						<div class="col-sm-4 col-lg-4 col-md-4 col-12">

							<div class="card card-custom gutter-b">
								<div class="card-header">
									<h3 class="card-title">
										Registros
									</h3>
								</div>
								<div class="card-body" style="height: 200px;">

									<h4>Vendas: <strong class="text-info">{{sizeof($empresa->vendas)}}</strong></h4>
									<h4>Vendas PDV: <strong class="text-info">{{sizeof($empresa->vendasCaixa)}}</strong></h4>

								</div>
							</div>
						</div>

					</div>
					<div class="row">
						<div class="form-group validated col-sm-6 col-lg-6">
							<div class="">

								<a target="_blank" href="/rep/alterarSenha/{{$empresa->id}}" class="btn btn-danger">
									<i class="la la-key"></i> Alterar Senhas de usuários
								</a>
							</div>
						</div>

						<div class="col-sm-6 col-lg-6">
							<h3 class="text-success">Data de cadastro: {{ \Carbon\Carbon::parse($empresa->created_at)->format('d/m/Y H:i:s')}}</h3>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-12 col-lg-12">
							<h3 class="">Plano atual: 

								@if(!$empresa->planoEmpresa)
								<a target="_blank" href="/rep/setarPlano/{{$empresa->id}}" class="btn btn-info">
									Atribuir plano
								</a>
								@else
								<span class="text-info">{{$empresa->planoEmpresa->plano->nome}} R${{moeda($empresa->planoEmpresa->plano->valor)}}</span>
								- Data de expiração: <span class="@if($planoExpirado) text-danger @else text-info @endif">
									@if($empresa->planoEmpresa->expiracao != '0000-00-00')
									{{ \Carbon\Carbon::parse($empresa->planoEmpresa->expiracao)->format('d/m/Y')}}
									@else
									Indeterminado
									@endif
								</span>
								@endif

								@if($planoExpirado)
								<a target="_blank" href="/rep/setarPlano/{{$empresa->id}}" class="btn btn-danger">
									Atribuir plano
								</a>
								@else
								@if($empresa->planoEmpresa)
								<a target="_blank" href="/rep/setarPlano/{{$empresa->id}}" class="btn btn-warning">
									Alterar plano
								</a>
								@endif
								@endif
							</h3>
						</div>
					</div>

					@if(!$empresa->configNota)
					<p class="text-danger">>>Esta empresa não possui os dados do emitente cadastrados</p>
					@endif

					<div class="row">
						<div class="col-sm-12">
							
							<a class="btn btn-info" href="/rep/download/{{$empresa->id}}">
								<i class="la la-download"></i>
								Download Certificado
							</a>	
							
							@if($empresa->configNota)
							<a class="btn btn-primary" href="/rep/arquivosXml/{{$empresa->id}}">
								<i class="la la-file-code"></i>
								Arquivos Xml
							</a>
							@endif

							<a class="btn btn-warning" href="/rep/configEmitente/{{$empresa->id}}">
								<i class="la la-id-card"></i>
								Configuração do Emitente
							</a>
						</div>
					</div>

					<br>

				</div>
			</div>
		</div>
	</div>
</div>

@endsection