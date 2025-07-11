@extends('default.layout')
@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
      <div class="col-lg-12"><br>

        <form id="estornoForm" method="post" action="/contasReceber/estorno" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" value="{{ $conta->id }}">

          <div class="card-header">
            <h3 class="card-title">Estornar Conta a Receber</h3>
          </div>

          {{-- detalhes da conta --}}
          <div class="row">
            <div class="col-xl-12">
              <h5>Cliente: <strong>{{ $conta->cliente->nome ?? '' }}</strong></h5>
              <h5>Data de registro: <strong>{{ \Carbon\Carbon::parse($conta->date_register)->format('d/m/Y') }}</strong></h5>
              <h5>Data de vencimento: <strong>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</strong></h5>
              <h5>Valor: <strong>{{ number_format($conta->valor_integral,2,',','.') }}</strong></h5>
              <h5>Referência: <strong>{{ $conta->referencia }}</strong></h5>
              <h5>Observação: <strong>{{ $conta->observacao }}</strong></h5>
            </div>
          </div>

          {{-- motivo --}}
          <div class="kt-section kt-section--first">
            <div class="kt-section__body">
              <div class="row">
                <div class="form-group col-lg-8">
                  <label>Motivo Estorno</label>
                  <input required
                         type="text"
                         name="motivo"
                         class="form-control @error('motivo') is-invalid @enderror"
                         value="{{ old('motivo') }}">
                  @error('motivo')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          {{-- botões --}}
          <div class="card-footer">
            <a href="/contasReceber" class="btn btn-danger">Cancelar</a>
            <button id="btnSalvarEstorno" type="submit" class="btn btn-success">Salvar</button>
          </div>
        </form>

        {{-- modal de admin --}}
        <div class="modal fade" id="adminAuthModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Permissão Administrador</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                <label>Senha do administrador</label>
                <input type="password" id="admin_password" class="form-control">
                <div id="adminPasswordError" class="invalid-feedback" style="display:none;">
                  Senha inválida.
                </div>
              </div>
              <div class="modal-footer">
                <button id="btnConfirmAdmin" class="btn btn-primary">Confirmar</button>
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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
    // se true, envia direto; se false, bloqueia e abre modal
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

      // anexa ao form e envia
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
