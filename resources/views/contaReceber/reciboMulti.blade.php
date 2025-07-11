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
        <h2 class="mb-4">{{ $title ?? 'Recibo Agrupado' }}</h2>

        @if(session('mensagem_sucesso'))
          <div class="alert alert-success">
            {{ session('mensagem_sucesso') }}
          </div>
        @endif

        @if(!$recibo)
          <p>Recibo não encontrado.</p>
        @else
          <!-- Formulário para atualizar o recibo múltiplo -->
          <form method="POST" action="{{ route('reciboMulti.update', $recibo->id) }}">
            @csrf
            <!-- Data de Pagamento -->
            <div class="row">
              <div class="form-group col-md-4">
                <label>Data de Pagamento:</label>
                <input type="date" name="data_pagamento" class="form-control"
                       value="{{ \Carbon\Carbon::parse($recibo->data_pagamento)->format('Y-m-d') }}">
              </div>
            </div>

            <!-- Cliente, Documento, Telefone -->
            <div class="row">
              <div class="form-group col-md-4">
                <label>Cliente:</label>
                <input type="text" name="cliente" class="form-control"
                       value="{{ $recibo->cliente }}">
              </div>
              <div class="form-group col-md-4">
                <label>Documento (CPF/CNPJ):</label>
                <input type="text" name="documento" class="form-control"
                       value="{{ $recibo->documento }}">
              </div>
              <div class="form-group col-md-4">
                <label>Telefone:</label>
                <input type="text" name="telefone" class="form-control"
                       value="{{ $recibo->telefone }}">
              </div>
            </div>

            <!-- Endereço -->
            <div class="row">
              <div class="form-group col-md-12">
                <label>Endereço:</label>
                <input type="text" name="endereco" class="form-control"
                       value="{{ $recibo->endereco }}">
              </div>
            </div>

            <!-- Valor Pago, Valor Extenso, Forma Pagamento -->
            <div class="row">
              <div class="form-group col-md-4">
                <label>Valor Pago:</label>
                <input type="text" name="valor_pago" class="form-control"
                       value="{{ number_format($recibo->valor_pago, 2, ',', '.') }}">
              </div>
              <div class="form-group col-md-4">
                <label>Valor por Extenso:</label>
                <input type="text" name="valor_extenso" class="form-control"
                       value="{{ $recibo->valor_extenso }}">
              </div>
              <div class="form-group col-md-4">
                <label>Forma de Pagamento:</label>
                <input type="text" name="forma_pagamento" class="form-control"
                       value="{{ $recibo->forma_pagamento }}">
              </div>
            </div>

            <!-- Referência e Observação -->
            <div class="row">
              <div class="form-group col-md-12">
                <label>Referência (Conta/Fatura):</label>
                <input type="text" name="referencia" class="form-control"
                       value="{{ $recibo->referencia }}">
              </div>
            </div>
            <div class="row">
              <div class="form-group col-md-12">
                <label>Observação:</label>
                <input type="text" name="observacao" class="form-control"
                       value="{{ $recibo->observacao }}">
              </div>
            </div>

            <!-- Botão Salvar -->
            <div class="row mt-3">
              <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
              </div>
            </div>
          </form>

          <hr>

          <!-- Lista de contas associadas -->
          <h5>Contas Associadas ao Recibo:</h5>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>ID Conta</th>
                <th>Data Vencimento</th>
                <th>Valor Integral</th>
              </tr>
            </thead>
            <tbody>
              @foreach($contas as $c)
                <tr>
                  <td>{{ $c->id }}</td>
                  <td>{{ \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') }}</td>
                  <td>R$ {{ number_format($c->valor_integral, 2, ',', '.') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if(isset($soma))
            <p><strong>Total das contas:</strong> R$ {{ number_format($soma, 2, ',', '.') }}</p>
          @endif

          <!-- Botões para impressão -->
          <div style="margin-top:20px;">
            <a href="{{ route('recibo.pdf', $recibo->id) }}" target="_blank" class="btn btn-primary">
              Imprimir A4
            </a>
            <a href="{{ route('recibo.pdf.termica', $recibo->id) }}" target="_blank" class="btn btn-success">
              Imprimir Térmica
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
