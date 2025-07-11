@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">

	<div class="card-body @if(env('ANIMACAO')) animate__animated @endif animate__backInRight">
		<div class="content d-flex flex-column flex-column-fluid" id="kt_content" >
			
			<div class="row" id="anime" style="display: none">
				<div class="col s8 offset-s2">
					<lottie-player src="/anime/{{\App\Models\Venda::randSuccess()}}" background="transparent" speed="0.8" style="width: 100%; height: 300px;" autoplay >
					</lottie-player>
				</div>
			</div>

			<div class="col-lg-12" id="content">

				<div class="row" id="div-cliente">
					<div class="col-xl-12">

						<div class="card card-custom gutter-b">
							<div class="card-body">

								<h4 class="center-align">CLIENTE</h4>
								<div class="row">

									<div class="col-sm-6 col-lg-6 col-12">
										<h5>Razão Social: <strong id="razao_social" class="text-success">{{$cliente->razao_social}}</strong></h5>
										<h5>Nome Fantasia: <strong id="nome_fantasia" class="text-success">{{$cliente->nome_fantasia}}</strong></h5>
										<h5>Logradouro: <strong id="logradouro" class="text-success">{{$cliente->rua}}</strong></h5>
										<h5>Numero: <strong id="numero" class="text-success">{{$cliente->numero}}</strong></h5>
										
									</div>
									<div class="col-sm-6 col-lg-6 col-12">
										<h5>CPF/CNPJ: <strong id="cnpj" class="text-success">{{$cliente->cpf_cnpj}}</strong></h5>
										<h5>RG/IE: <strong id="ie" class="text-success">{{$cliente->ie_rg}}</strong></h5>
										<h5>Fone: <strong id="fone" class="text-success">{{$cliente->telefone}}</strong></h5>
										<h5>Cidade: <strong id="cidade" class="text-success">{{$cliente->cidade->nome}} ({{$cliente->cidade->uf}})</strong></h5>

									</div>
								</div>
							</div>
						</div>
					</div>

				</div>

				<form class="row" method="post" action="/boleto/gerarStore">
					<input type="hidden" value="{{$contaReceber->id}}" name="conta_id">

					@csrf
					<div class="col-xl-12">

						<div class="card card-custom gutter-b">
							<div class="card-body">
								<h4 class="center-align">DADOS DO BOLETO</h4>

								<div class="row">
									<div class="col-lg-12">

										<span class="text-danger"><i class="la la-exclamation-circle text-danger"></i> Após gerar o boleto não será possível editar os dados da conta a receber</span>
										<hr>
									</div>
								</div>

								<div class="row">
									<div class="col-lg-4">
										<h5>Valor: <strong id="cnpj" class="text-info">{{number_format($contaReceber->valor_integral, $casasDecimais, ',', '.')}}</strong></h5>
									</div>

									<div class="col-lg-4">
										<h5>Vencimento: <strong class="text-info">{{ \Carbon\Carbon::parse($contaReceber->data_vencimento)->format('d/m/Y')}}</strong></h5>
									</div>
								</div>
								<div class="row">

									<div class="form-group col-lg-4 col-md-4 col-sm-6 col-6">
										<label class="col-form-label">Banco</label>
										<div class="">
											<div class="input-group">
												<select id="banco" name="banco_id" class="custom-select">
													<option value="">Selecione</option>
													@foreach($contasBancarias as $c)
													<option @if($contaPadrao != null && $contaPadrao->id == $c->id) selected @endif value="{{$c->id}}" @if(old('banco_id') == $c->id) selected @endif >{{$c->banco}} | AG:{{$c->agencia}} - C:{{$c->conta}}</option>
													@endforeach
												</select>
												@if($errors->has('banco_id'))
												<div class="invalid-feedback">
													{{ $errors->first('banco_id') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<input type="hidden" value="{{json_encode($contasBancarias)}}" id="contas_json">

									<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
										<label class="col-form-label">Nº do boleto</label>
										<div class="">
											<div class="input-group">
											<input name="numero" type="text" readonly class="form-control bg-light @if($errors->has('numero')) is-invalid @endif" value="{{ old('numero', isset($contaPadrao) ? $contaPadrao->numero_boleto : '') }}"/>
												@if($errors->has('numero'))
												<div class="invalid-feedback">
													{{ $errors->first('numero') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									@php
                                    $numeroDocumento = $contaReceber->numero_nota_fiscal;
                                        if ($numeroDocumento == 0) {
                                            if ($contaReceber->vendaCaixa && $contaReceber->vendaCaixa->NFcNumero != 0) {
                                            // Se numero_nota_fiscal é 0, tenta pegar o NFcNumero da venda de caixa
                                            $numeroDocumento = $contaReceber->vendaCaixa->NFcNumero;
                                           } else {
                                             // Se NFcNumero também for 0, verifica se venda_id existe
                                             if (!is_null($contaReceber->venda_id)) {
                                               $numeroDocumento = $contaReceber->venda_id;
                                            } else {
                                             // Se venda_id for NULL, usa venda_caixa_id
                                             $numeroDocumento = $contaReceber->venda_caixa_id;
                                            }
                                          }
                                        }
                                        @endphp

										<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
                                            <label class="col-form-label">Nº do documento</label>
                                            <div class="">
                                                <div class="input-group">
                                                    <input name="numero_documento" value="{{ old('numero_documento', $numeroDocumento) }}" type="text" class="form-control @if($errors->has('numero_documento')) is-invalid @endif"/>
                                                    @if($errors->has('numero_documento'))
                                                     <div class="invalid-feedback">
                                                        {{ $errors->first('numero_documento') }}
                                                     </div>
                                                     @endif
                                                </div>
                                            </div>
                                        </div>

									<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
										<label class="col-form-label">Carteira</label>
										<div class="">
											<div class="input-group">
												<input id="carteira" name="carteira" value="@if($contaPadrao != null) {{ $contaPadrao->carteira}} @else {{old('carteira')}} @endif" type="text" readonly class="form-control bg-light @if($errors->has('carteira')) is-invalid @endif"/>
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
												<input id="convenio" name="convenio" value="@if($contaPadrao != null) {{ $contaPadrao->convenio}} @else {{old('convenio')}} @endif" type="text" readonly class="form-control bg-light @if($errors->has('convenio')) is-invalid @endif"/>
												@if($errors->has('convenio'))
												<div class="invalid-feedback">
													{{ $errors->first('convenio') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
										<label class="col-form-label">Juros</label>
										<div class="">
											<div class="input-group">
												<input id="juros" value="{{ isset($contaPadrao) ? number_format($contaPadrao->juros, 2, '.', '') : number_format(old('juros', 0), 2, '.', '') }}" name="juros" type="text" class="form-control money-p"/>
											</div>
										</div>
									</div>

									<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
										<label class="col-form-label">Multa</label>
										<div class="">
											<div class="input-group">
												 <input id="multa" value="{{ isset($contaPadrao) ? number_format($contaPadrao->multa, 2, '.', '') : number_format(old('multa', 0), 2, '.', '') }}" name="multa" type="text" class="form-control money-p"/>
											</div>
										</div>
									</div>

									<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
										<label class="col-form-label">Juros após (dias)</label>
										<div class="">
											<div class="input-group">
												<input id="juros_apos" value="@if($contaPadrao != null) {{ $contaPadrao->juros_apos}} @else {{old('juros_apos')}} @endif" name="juros_apos" type="text" class="form-control"/>
											</div>
										</div>
									</div>


									<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
										<label class="col-form-label">Tipo</label>
										<div class="">
											<div class="input-group">
												<select name="tipo" class="custom-select">
													<option @if($contaPadrao != null) @if($contaPadrao->tipo == 'Cnab400') selected @endif @endif value="Cnab400">Cnab400</option>
													<option @if($contaPadrao != null) @if($contaPadrao->tipo == 'Cnab240') selected @endif @endif value="Cnab240">Cnab240</option>
												</select>
											</div>
										</div>
									</div>

									<div class="form-group validated col-sm-4 col-lg-4">
										<label class="col-form-label">Usar logo</label>

										<div class="switch switch-outline switch-success">
											<label class="">
												<input value="true" @if($contaPadrao != null && $contaPadrao->usar_logo) checked @endif name="logo" class="red-text" type="checkbox">
												<span class="lever"></span>
											</label>
										</div>
									</div>

									<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6 div-aux" style="display: none">
										<label class="col-form-label">Posto</label>
										<div class="">
											<div class="input-group">
											    <input id="posto" name="posto" type="text" readonly 
                                                class="form-control bg-light @if($errors->has('posto')) is-invalid @endif" 
                                                value="{{ old('posto', isset($contaPadrao) ? $contaPadrao->numero_posto : '') }}"/>
												@if($errors->has('posto'))
												<span class="text-danger">
													{{ $errors->first('posto') }}
												</span>
												@endif
											</div>
										</div>
									</div>

									<!--<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6 div-aux" style="display: none">
										<label class="col-form-label">Código do cliente</label>
										<div class="">
											<div class="input-group">
												<input id="codigo_cliente" name="codigo_cliente" type="text" class="form-control @if($errors->has('codigo_cliente')) is-invalid @endif"/>
												@if($errors->has('codigo_cliente'))
												<span class="text-danger">
													{{ $errors->first('codigo_cliente') }}
												</span>
												@endif
											</div>
										</div>
									</div>-->


									<div class="form-group col-12">
										<label class="col-form-label">Instruções</label>
										<div class="">
											<div class="input-group">
												<input name="instrucoes" type="text" class="form-control"/>
											</div>
										</div>
									</div>

								</div>
							</div>
						</div>
						<div class="card-footer">
							<div class="row">
								<div class="col-lg-3 col-sm-6 col-md-4">
									<button style="width: 100%" type="submit" class="btn btn-success">
										<i class="la la-check"></i>
										<span class="">Gerar boleto</span>
									</button>
								</div>
							</div>
						</div>
					</div>

				</form>
			</div>
		</div>
	</div>
</div>

@section('javascript')
<!-- Se o Metronic não carrega o maskMoney, inclua-o aqui -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    // Inicializa o maskMoney para os campos com a classe money-p
    $('.money-p').maskMoney({
            prefix: '',
            allowNegative: false,
            thousands: ',',
            decimal: '.',
            affixesStay: false,
            precision: 2
    });
    
    // Força a reformatação com um pequeno atraso e dispara o blur para aplicar o formato
    setTimeout(function(){
        $('.money-p').each(function(){
            var val = $(this).val();
            // Reatribui o valor e chama o método de formatação
            $(this).val(val).maskMoney('mask').trigger('blur');
        });
    }, 200);

    // 2) Declara e inicializa o array de contas
    var CONTAS = JSON.parse($('#contas_json').val() || '[]');

    // 3) Cria as funções de manipulação
    function verificaBanco(){
      $('#codigo_cliente').val('')
      $('.div-aux').css('display', 'none')
      let banco = $('#banco').val();
      CONTAS.map(function(c){
        if(banco == c.id){
          console.log('Banco selecionado:', c.banco);
          if(c.banco == 'Sicredi' || c.banco == 'Cresol' || c.banco == 'Caixa Econônica Federal' || c.banco == 'Santander'){
            $('.div-aux').css('display', 'block')
            $('#posto').val(c.numero_posto);
          } else {
            $('#posto').val('');
          }
        }
      });
    }

    function getDados(call){
      let banco = $('#banco').val();
      if(banco){
        $.get(path + 'contaBancaria/find/' + banco)
        .done(function(res){
          call(res);
        })
        .fail(function(err){
          call(err);
        });
      } else {
        call(false);
      }
    }

    function updateNumeroBoleto(){
      let banco = $('#banco').val();
      if(banco){
        let conta = CONTAS.find(function(c){
          return c.id == banco;
        });
        if(conta && conta.numero_boleto){
          $('input[name="numero"]').val(conta.numero_boleto);
        }
      }
    }

    // 4) Chama as funções necessárias no carregamento inicial
    verificaBanco();

    // 5) Ao mudar o select de banco, atualiza tudo
    $('#banco').change(function(){
      verificaBanco();
      updateNumeroBoleto();
      getDados(function(res){
        if(res){
          console.log(res);
          $('#carteira').val(res.carteira);
          $('#convenio').val(res.convenio);
          $('#juros').val(res.juros).maskMoney('mask');  // reaplica a formatação
          $('#juros_apos').val(res.juros_apos);
          $('#multa').val(res.multa).maskMoney('mask');  // reaplica a formatação
        }
      });
    });
  });
</script>
@endsection


@endsection
