@extends('default.layout')
@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container">
      <div class="col-lg-12">
       <div> 
        {{-- Exemplo de voltar --}}
        <a href="{{ url('/contasReceber/recibos') }}" class="btn btn-secondary">
          <i class="la la-arrow-left"></i> Voltar
        </a>
      </div>
        <br>
        <h2 class="mb-4">Recibo de Recebimento</h2>

        <form method="post" action="{{ route('contaReceber.gerarRecibo') }}">
          @csrf

          <!-- Se já existir recibo, usamos; senão 0 -->
          <input type="hidden" name="receipt_id" value="{{ $recibo->id ?? 0 }}">

          <!-- ID da conta (caso seja conta única) -->
          <input type="hidden" name="conta_id" value="{{ $conta->id }}">

          <!-- Exemplo: Data de pagamento -->
          <div class="row">
            <div class="form-group col-md-4">
              <label>Data de Pagamento:</label>
              <input type="date" name="data_pagamento" class="form-control"
                     value="{{ isset($recibo->data_pagamento) 
                         ? date('Y-m-d', strtotime($recibo->data_pagamento)) 
                         : date('Y-m-d', strtotime($conta->data_recebimento)) }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-4">
              <label>Cliente:</label>
              <input type="text" name="cliente" class="form-control"
                     value="{{ $recibo->cliente ?? $cliente->razao_social }}">
            </div>
            <div class="form-group col-md-4">
              <label>Documento (CPF/CNPJ):</label>
              <input type="text" name="documento" class="form-control"
                     value="{{ $recibo->documento ?? $cliente->cpf_cnpj }}">
            </div>
            <div class="form-group col-md-4">
              <label>Telefone:</label>
              <input type="text" name="telefone" class="form-control"
                     value="{{ $recibo->telefone ?? $cliente->telefone }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-12">
              <label>Endereço:</label>
              <input type="text" name="endereco" class="form-control"
                     value="{{ $recibo->endereco ?? $cliente->endereco }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-4">
              <label>Valor Pago:</label>
              <input type="text" name="valor_pago" class="form-control"
                     value="{{ $recibo 
                         ? number_format($recibo->valor_pago, 2, ',', '.') 
                         : number_format($conta->valor_recebido, 2, ',', '.') }}">
            </div>
            <div class="form-group col-md-4">
              <label>Valor por Extenso:</label>
              <input type="text" name="valor_extenso" class="form-control"
                     value="{{ $valorPorExtenso }}">
            </div>
            <div class="form-group col-md-4">
              <label>Forma de Pagamento:</label>
              <input type="text" name="forma_pagamento" class="form-control"
                     value="{{ $recibo->forma_pagamento ?? $conta->tipo_pagamento }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-12">
              <label>Referência (Conta/Fatura):</label>
              <input type="text" name="referencia" class="form-control"
                     value="{{ $recibo->referencia ?? $conta->referencia }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-12">
              <label>Observação:</label>
              <input type="text" name="observacao" class="form-control"
                     value="{{ $recibo->observacao ?? $conta->observacao }}">
            </div>
          </div>

          <!-- Botão que simplesmente submete o form -->
          <div class="row mt-3">
            <div class="col-md-12">
              <button type="submit" class="btn btn-primary">
                Gerar Recibo
              </button>
            </div>
          </div>
        </form>

        @if(session('recibo_id'))
          <hr>
          <h5>Escolha o modelo de impressão:</h5>
          <a href="{{ route('recibo.pdf', session('recibo_id')) }}" 
             target="_blank" class="btn btn-primary">
            Imprimir A4
          </a>
          <a href="{{ route('recibo.pdf.termica', session('recibo_id')) }}" 
             target="_blank" class="btn btn-success">
            Imprimir Térmica
          </a>
        @endif

      </div>
    </div>
  </div>
</div>
@endsection
