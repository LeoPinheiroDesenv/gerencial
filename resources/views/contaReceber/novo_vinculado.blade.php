@extends('default.layout')

@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container">
      <div class="col-lg-12">
        <br>
        <h2 class="mb-4">Gerar Recibo Vinculado</h2>

        {{-- Formulário de Filtro --}}
        <form action="{{ route('recibos.novoVinculado') }}" method="GET" class="mb-4">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="cliente">Cliente:</label>
              <select name="nome" id="cliente" class="form-control select2" style="opacity: 100; width: 100%; display: block;">
                <option value="">Selecione um cliente</option>
                @foreach($clientes as $cliente)
                  <option value="{{ $cliente->razao_social }}"
                    {{ isset($nome) && $nome == $cliente->razao_social ? 'selected' : '' }}>
                    {{ $cliente->razao_social }} - {{ $cliente->cpf_cnpj }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="data_pagamento">Data de Pagamento:</label>
              <input type="date" name="data_pagamento" id="data_pagamento" class="form-control"
                     value="{{ old('data_pagamento', $dataPagamento ?? '') }}">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Filtrar</button>
          <a href="{{ route('contasReceber.recibos') }}" class="btn btn-secondary">
            <i class="la la-arrow-left"></i> Voltar
          </a>
        </form>

        <hr>

        <h3>Contas a Receber Sem Recibo Vinculado</h3>

        @if($contas->count())
        <form action="{{ route('contasReceber.gerarReciboMultiAutomatico') }}" method="POST">
            @csrf
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>
                    <input type="checkbox" id="selecionar_todas">
                  </th>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>CPF/CNPJ</th>
                  <th>Data de Pagamento</th>
                  <th>Valor</th>
                </tr>
              </thead>
              <tbody>
                @foreach($contas as $conta)
                  <tr>
                    <td>
                      <input type="checkbox" name="contasSelecionadas[]" value="{{ $conta->id }}" class="conta_checkbox">
                    </td>
                    <td>{{ $conta->id }}</td>
                    <td>{{ optional($conta->cliente)->razao_social ?? 'Cliente não encontrado' }}</td>
                    <td>{{ optional($conta->cliente)->cpf_cnpj ?? '--' }}</td>
                    <td>
                      @if($conta->data_recebimento)
                        {{ \Carbon\Carbon::parse($conta->data_recebimento)->format('d/m/Y') }}
                      @else
                        --
                      @endif
                    </td>
                    <td>
                      R$ {{ number_format($conta->valor_integral, 2, ',', '.') }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>

            <button type="submit" class="btn btn-success mt-2">Gerar Recibo</button>
          </form>
        @else
          <p>Nenhuma conta a receber encontrada sem recibo vinculado.</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script>
  // Evento para marcar ou desmarcar todos os checkboxes
  document.getElementById('selecionar_todas').addEventListener('change', function() {
    let checkboxes = document.querySelectorAll('.conta_checkbox');
    checkboxes.forEach(function(checkbox) {
      checkbox.checked = document.getElementById('selecionar_todas').checked;
    });
  });

  // Ao carregar a página, limpa os campos de filtro
  document.addEventListener('DOMContentLoaded', function() {
    // Seleciona o formulário de filtro pelo atributo method="GET"
    var filtroForm = document.querySelector('form[method="GET"]');
    if (filtroForm) {
      // Limpa os valores dos inputs do formulário
      filtroForm.reset();
      // Caso o campo esteja utilizando o Select2, atualiza o plugin
      if ($('#cliente').hasClass('select2')) {
        $('#cliente').val(null).trigger('change');
      }
    }
  });
</script>
@endsection
