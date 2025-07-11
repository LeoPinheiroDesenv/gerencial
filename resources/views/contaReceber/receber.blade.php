@extends('default.layout')
@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
      <div class="col-lg-12">
        <br>
        <!-- Formulário de recebimento -->
        <form id="formReceber" method="post" action="/contasReceber/receber" enctype="multipart/form-data">
          @csrf
          <!-- Campo oculto com o ID da conta a receber -->
          <input type="hidden" name="id" value="{{ $conta->id }}">
          <!-- Campo oculto para indicar se deve gerar recibo (0 = não, 1 = sim) -->
          <input type="hidden" id="gerar_recibo" name="gerar_recibo" value="0">
          
          <!-- Exibição dos dados da conta (exemplo) -->
          <div class="card card-custom gutter-b example example-compact">
            <div class="card-header">
              <h3 class="card-title">Receber Conta</h3>
            </div>
          </div>
          <!-- Aqui você exibe os dados da conta, como data de registro, vencimento, valor, categoria, referência e observação -->
          <div class="row">
            <div class="col s12">
              @if($conta->compra_id != null)
                <h5>Fornecedor: <strong>{{$conta->compra->fornecedor->razao_social}}</strong></h5>
              @endif
              <h5>Data de registro: <strong>{{ \Carbon\Carbon::parse($conta->data_registro)->format('d/m/Y')}}</strong></h5>
              <h5>Data de vencimento: <strong>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y')}}</strong></h5>
              <h5>Valor: <strong>{{ number_format($conta->valor_integral, 2, ',', '.') }}</strong></h5>
              <h5>Categoria: <strong>{{$conta->categoria->nome}}</strong></h5>
              <h5>Referência: <strong>{{$conta->referencia}}</strong></h5>
              <h5>Observação: <strong>{{$conta->observacao}}</strong></h5>
            </div>
          </div>
          
          <!-- Seção de recebimento -->
          <div class="kt-section kt-section--first">
            <div class="kt-section__body">
              <div class="row">
                <div class="form-group validated col-sm-6 col-lg-2">
                  <label class="col-form-label">Valor Recebido</label>
                  <input required type="text" class="form-control money @if($errors->has('valor')) is-invalid @endif" name="valor" value="{{ moeda($conta->valor_integral + $juros + $multa) }}">
                  @if($errors->has('valor'))
                    <div class="invalid-feedback">
                      {{ $errors->first('valor') }}
                    </div>
                  @endif
                </div>

                <div class="form-group validated col-sm-6 col-lg-2">
                  <label class="col-form-label">Data de recebimento</label>
                  <input required type="text" name="data_pagamento" class="form-control date-input @if($errors->has('data_pagamento')) is-invalid @endif" value="{{ date('d/m/Y') }}" id="kt_datepicker_3" />
                  @if($errors->has('data_pagamento'))
                    <div class="invalid-feedback">
                      {{ $errors->first('data_pagamento') }}
                    </div>
                  @endif
                </div>

                <div class="form-group validated col-sm-12 col-lg-4">
                  <label class="col-form-label">Tipo de Pagamento</label>
                  <select required class="custom-select form-control" id="forma" name="tipo_pagamento">
                    <option value="">Selecione o tipo de pagamento</option>
                    @foreach(App\Models\ContaReceber::tiposPagamento() as $c)
                      <option @if($conta->tipo_pagamento == $c) selected @endif value="{{$c}}">{{$c}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group validated col-lg-2 col-md-4 col-sm-6">
                  <label class="col-form-label">Multa</label>
                  <input type="tel" id="multa" class="form-control money" name="multa" value="{{ moeda($multa) }}">
                </div>

                <div class="form-group validated col-lg-2 col-md-4 col-sm-6">
                  <label class="col-form-label">Juros</label>
                  <input type="tel" id="juros" class="form-control money" name="juros" value="{{ moeda($juros) }}">
                </div>

                @if(sizeof($contasEmpresa) > 0)
                  <div class="form-group validated col-sm-12 col-lg-4">
                    <label class="col-form-label">Conta</label>
                    <select required name="conta_id" class="select2-custom custom-select">
                      <option value=""></option>
                      @foreach($contasEmpresa as $c)
                        <option value="{{ $c->id }}">{{ $c->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                @endif

                <div class="form-group validated col-lg-12">
                  <label class="col-form-label">Observação</label>
                  <input type="text" class="form-control" name="observacao_baixa">
                </div>
              </div>
            </div>
          </div>

          <!-- Botão Único: Receber -->
          <div class="card-footer">
            <div class="row">
              <div class="col-xl-2"></div>
              <div class="col-lg-3 col-sm-6 col-md-4">
                <a style="width: 100%" class="btn btn-danger" href="/contasReceber">
                  <i class="la la-close"></i>
                  <span>Cancelar</span>
                </a>
              </div>
              <div class="col-lg-3 col-sm-6 col-md-4">
                <!-- Botão Receber (ao clicar, abre o modal) -->
                <button type="button" id="btnReceber" style="width: 100%" class="btn btn-success">
                  <i class="la la-check"></i>
                  <span>Receber</span>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal para escolha de gerar recibo -->
<div class="modal fade" id="modalGerarRecibo" tabindex="-1" role="dialog" aria-labelledby="modalGerarReciboLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalGerarReciboLabel">Deseja gerar recibo?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Ao receber a conta, deseja também gerar o recibo?</p>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnNao" class="btn btn-secondary" data-dismiss="modal">Não</button>
        <button type="button" id="btnSim" class="btn btn-primary">Sim</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script>
  $(document).ready(function(){
    // Quando clicar no botão "Receber", abre o modal
    $('#btnReceber').on('click', function(){
      $('#modalGerarRecibo').modal('show');
    });
    
    // Se clicar em "Sim", define gerar_recibo=1 e submete o formulário
    $('#btnSim').on('click', function(){
      $('#gerar_recibo').val('1');
      $('#formReceber').submit();
    });
    
    // Se clicar em "Não", define gerar_recibo=0 e submete o formulário
    $('#btnNao').on('click', function(){
      $('#gerar_recibo').val('0');
      $('#formReceber').submit();
    });
  });
</script>
@endsection
