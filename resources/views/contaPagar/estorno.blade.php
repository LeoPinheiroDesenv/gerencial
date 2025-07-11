@extends('default.layout')

@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
      <div class="col-lg-12">
        <br>

        <form id="estornoForm"
              method="post"
              action="/contasPagar/estorno"
              enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" value="{{ $conta->id }}">

          <div class="card card-custom gutter-b example example-compact">
            <div class="card-header">
              <h3 class="card-title">Estornar Conta</h3>
            </div>
          </div>

          <div class="row">
            <div class="col-xl-12">
              {{-- detalhes da conta --}}
              <div class="row">
                <div class="col s12">
                  @if($conta->compra_id)
                    <h5>Fornecedor: <strong>{{ $conta->compra->fornecedor->razao_social }}</strong></h5>
                  @endif
                  <h5>Data de registro: <strong>{{ \Carbon\Carbon::parse($conta->date_register)->format('d/m/Y') }}</strong></h5>
                  <h5>Data de vencimento: <strong>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</strong></h5>
                  <h5>Valor: <strong>{{ number_format($conta->valor_integral, 2, ',', '.') }}</strong></h5>
                  <h5>Categoria: <strong>{{ $conta->categoria->nome }}</strong></h5>
                  <h5>Referência: <strong>{{ $conta->referencia }}</strong></h5>
                  <h5>Observação: <strong>{{ $conta->observacao }}</strong></h5>
                </div>
              </div>

              {{-- motivo --}}
              <div class="kt-section kt-section--first">
                <div class="kt-section__body">
                  <div class="row">
                    <div class="form-group validated col-sm-6 col-lg-8">
                      <label class="col-form-label">Motivo Estorno</label>
                      <div>
                        <input required
                               type="text"
                               name="motivo"
                               class="form-control @if($errors->has('motivo')) is-invalid @endif"
                               value="{{ old('motivo') }}"
                        >
                        @if($errors->has('motivo'))
                          <div class="invalid-feedback">{{ $errors->first('motivo') }}</div>
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
              <div class="col-lg-3 col-sm-6 col-md-4">
                <a style="width:100%" class="btn btn-danger" href="/contasPagar">
                  <i class="la la-close"></i> Cancelar
                </a>
              </div>
              <div class="col-lg-3 col-sm-6 col-md-4">
                <button id="btnSalvarEstorno" style="width:100%" type="submit" class="btn btn-success">
                  <i class="la la-check"></i> Salvar
                </button>
              </div>
            </div>
          </div>
        </form>

        {{-- Modal de senha de administrador --}}
        <div class="modal fade" id="adminAuthModal" tabindex="-1" role="dialog"
             aria-labelledby="adminAuthLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="adminAuthLabel">Permissão Administrador</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="form-group">
                  <label for="admin_password">Digite a senha do administrador</label>
                  <input type="password"
                         id="admin_password"
                         class="form-control"
                         placeholder="Senha de admin">
                  <div id="adminPasswordError" class="invalid-feedback" style="display:none;">
                    Senha inválida.
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button id="btnConfirmAdmin" type="button" class="btn btn-primary">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script>
  $(function(){
    // se true, precisa do modal; se false, envia direto
    const userCanEstornar = {{ $requiresAdminAuth ? 'false' : 'true' }};

    $('#estornoForm').on('submit', function(e){
      if (userCanEstornar) return;
      e.preventDefault();
      $('#adminAuthModal').modal({backdrop:'static',keyboard:false}).modal('show');
    });

    $('#btnConfirmAdmin').on('click', function(){
      const senha = $('#admin_password').val().trim();
      $('#admin_password').removeClass('is-invalid');
      $('#adminPasswordError').hide();

      if (!senha) {
        $('#admin_password').addClass('is-invalid');
        $('#adminPasswordError').text('A senha é obrigatória.').show();
        return;
      }
      // anexa senha e reenviar
      $('#estornoForm input[name="admin_password"]').remove();
      $('#estornoForm').append(
        $('<input>').attr({type:'hidden', name:'admin_password', value:senha})
      );
      $('#adminAuthModal').modal('hide');
      $('#estornoForm').off('submit').submit();
    });
  });
</script>
@endsection
