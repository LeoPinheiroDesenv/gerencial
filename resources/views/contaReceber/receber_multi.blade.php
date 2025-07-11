@extends('default.layout')
@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container @if(env('ANIMACAO')) animate__animated animate__backInLeft @endif">
      <div class="col-lg-12">
        <br>
        <form method="post" action="/contasReceber/receberMulti" enctype="multipart/form-data" id="formReceberMulti">
          @csrf
          <!-- IDs das contas recebidas e o valor total -->
          <input type="hidden" name="ids" value="{{ $ids }}">
          <input type="hidden" name="somaTotal" value="{{ $somaTotal }}">
          <!-- Campo oculto para indicar se deve gerar recibo agrupado (0 = não, 1 = sim) -->
          <input type="hidden" name="gerar_recibo" id="gerar_recibo" value="0">

          <div class="card card-custom gutter-b example example-compact">
            <div class="card-header">
              <h3 class="card-title">Receber Contas</h3>
            </div>
          </div>

          @php
            // Utiliza a primeira conta para exibir algumas informações
            $primeiraConta = $contas[0];
          @endphp

          <div class="row">
            <div class="col-xl-12">
              <div class="row">
                <div class="col s12">
                  @if($primeiraConta->compra_id != null)
                    <h5>Fornecedor: <strong>{{ $primeiraConta->compra->fornecedor->razao_social }}</strong></h5>
                  @endif
                  <h5>Valor Total: <strong>{{ number_format($somaTotal, 2, ',', '.') }}</strong></h5>
                  <br>
                  <table class="table col-12 col-lg-8">
                    <thead>
                      <tr>
                        <th>Vencimento</th>
                        <th>Valor</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($contas as $c)
                        <tr>
                          <td>{{ \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') }}</td>
                          <td>R$ {{ number_format($c->valor_integral, 2, ',', '.') }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <th class="text-info" colspan="2">Contas selecionadas</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <div class="kt-section kt-section--first">
                <div class="kt-section__body">
                  <div class="row">
                    <div class="form-group validated col-sm-6 col-lg-2">
                      <label class="col-form-label">Valor Recebido</label>
                      <div class="">
                        <input required type="text" class="form-control money @if($errors->has('valor')) is-invalid @endif" name="valor" value="{{ number_format($somaTotal, 2, ',', '') }}">
                        @if($errors->has('valor'))
                          <div class="invalid-feedback">
                            {{ $errors->first('valor') }}
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="form-group validated col-sm-6 col-lg-2">
                      <label class="col-form-label">Data de recebimento</label>
                      <div class="">
                        <input required type="text" name="data_pagamento" class="form-control date-input @if($errors->has('data_pagamento')) is-invalid @endif" value="{{ date('d/m/Y') }}" id="kt_datepicker_3" />
                        @if($errors->has('data_pagamento'))
                          <div class="invalid-feedback">
                            {{ $errors->first('data_pagamento') }}
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="form-group validated col-sm-12 col-lg-3">
                      <label class="col-form-label">Tipo de Pagamento</label>
                      <select required class="custom-select form-control" id="forma" name="tipo_pagamento">
                        <option value="">Selecione o tipo de pagamento</option>
                        @foreach(App\Models\ContaReceber::tiposPagamento() as $c)
                          <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                      </select>
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

                  </div>
                </div>
              </div>
            </div>
          </div>

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
                <!-- Botão Receber: ao clicar, abre o modal -->
                <button type="button" id="btnReceberMulti" style="width: 100%" class="btn btn-success">
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

<!-- Modal para perguntar se deseja gerar recibo agrupado -->
<div class="modal fade" id="modalGerarReciboMulti" tabindex="-1" role="dialog" aria-labelledby="modalGerarReciboMultiLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalGerarReciboMultiLabel">Gerar Recibo Agrupado?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Deseja gerar um recibo agrupado para as contas selecionadas?</p>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnNaoMulti" class="btn btn-secondary" data-dismiss="modal">Não</button>
        <button type="button" id="btnSimMulti" class="btn btn-primary">Sim</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('javascript')
<script>
  $(document).ready(function(){
    // Ao clicar no botão Receber, abre o modal de confirmação
    $('#btnReceberMulti').on('click', function(){
      $('#modalGerarReciboMulti').modal('show');
    });

    // Se o usuário escolher "Sim", define gerar_recibo = 1 e submete o formulário
    $('#btnSimMulti').on('click', function(){
      $('#gerar_recibo').val('1');
      $('#formReceberMulti').submit();
    });

    // Se o usuário escolher "Não", define gerar_recibo = 0 e submete o formulário
    $('#btnNaoMulti').on('click', function(){
      $('#gerar_recibo').val('0');
      $('#formReceberMulti').submit();
    });
  });
</script>
@endsection
