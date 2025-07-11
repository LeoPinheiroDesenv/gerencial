@extends('default.layout')

@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container">
      <div class="col-lg-12">
        <br>
        <h2 class="mb-4">Novo Recibo Avulso</h2>

        <form method="post" action="{{ route('recibos.storeAvulso') }}">
          @csrf

          <div class="row">
            <div class="form-group col-md-4">
              <label>Data de Pagamento:</label>
              <input type="date" name="data_pagamento" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group col-md-4">
              <label>Cliente:</label>
              <input type="text" name="cliente" class="form-control" placeholder="Nome do cliente">
            </div>
            <div class="form-group col-md-4">
              <label>Documento (CPF/CNPJ):</label>
              <input type="text" name="documento" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-12">
              <label>Endereço:</label>
              <input type="text" name="endereco" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-4">
              <label>Telefone:</label>
              <input type="text" name="telefone" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label>Valor Pago:</label>
              <input type="text" name="valor_pago" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label>Valor por Extenso:</label>
              <input type="text" name="valor_extenso" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-4">
              <label>Forma de Pagamento:</label>
              <input type="text" name="forma_pagamento" class="form-control">
            </div>
            <div class="form-group col-md-8">
              <label>Referência (Conta/Fatura) ou descrição:</label>
              <input type="text" name="referencia" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-12">
              <label>Observação:</label>
              <input type="text" name="observacao" class="form-control">
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            Salvar Recibo
          </button>
          <a href="{{ route('contasReceber.recibos') }}" class="btn btn-secondary">
            <i class="la la-arrow-left"></i> Voltar
          </a>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection
