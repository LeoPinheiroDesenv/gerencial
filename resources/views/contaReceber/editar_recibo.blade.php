@extends('default.layout')
@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container">
      <div class="col-lg-12">
        <br>
        <h2 class="mb-4">{{ $title }}</h2>
        
        <form method="post" action="{{ route('contasReceber.atualizarRecibo', $recibo->id) }}">
          @csrf
          @method('PUT')
          
          <div class="row">
            <div class="form-group col-md-4">
              <label>Data de Pagamento:</label>
              <input type="date" name="data_pagamento" class="form-control" value="{{ date('Y-m-d', strtotime($recibo->data_pagamento)) }}">
            </div>
          </div>
          
          <div class="row">
            <div class="form-group col-md-4">
              <label>Cliente:</label>
              <input type="text" name="cliente" class="form-control" value="{{ $recibo->cliente }}">
            </div>
            <div class="form-group col-md-4">
              <label>Documento (CPF/CNPJ):</label>
              <input type="text" name="documento" class="form-control" value="{{ $recibo->documento }}">
            </div>
            <div class="form-group col-md-4">
              <label>Telefone:</label>
              <input type="text" name="telefone" class="form-control" value="{{ $recibo->telefone }}">
            </div>
          </div>
          
          <div class="row">
            <div class="form-group col-md-12">
              <label>Endereço:</label>
              <input type="text" name="endereco" class="form-control" value="{{ $recibo->endereco }}">
            </div>
          </div>
          
          <div class="row">
            <div class="form-group col-md-4">
              <label>Valor Pago:</label>
              <input type="text" name="valor_pago" class="form-control" value="{{ number_format($recibo->valor_pago, 2, ',', '.') }}">
            </div>
            <div class="form-group col-md-4">
              <label>Valor por Extenso:</label>
              <input type="text" name="valor_extenso" class="form-control" value="{{ $recibo->valor_extenso }}">
            </div>
            <div class="form-group col-md-4">
              <label>Forma de Pagamento:</label>
              <input type="text" name="forma_pagamento" class="form-control" value="{{ $recibo->forma_pagamento }}">
            </div>
          </div>
          
          <div class="row">
            <div class="form-group col-md-12">
              <label>Referência (Conta/Fatura):</label>
              <input type="text" name="referencia" class="form-control" value="{{ $recibo->referencia }}">
            </div>
          </div>
          
          <div class="row">
            <div class="form-group col-md-12">
              <label>Observação:</label>
              <input type="text" name="observacao" class="form-control" value="{{ $recibo->observacao }}">
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col-md-12">
              <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
