{{-- resources/views/contaReceber/list_recibos.blade.php --}}
@extends('default.layout')

@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="card card-custom gutter-b example example-compact">
    <div class="container">
      <div class="col-lg-12">
        <br>
        <h2 class="mb-4">Lista de Recibos de Recebimento</h2>

        {{-- Botões de "Incluir Recibo" --}}
        <div class="mb-4">
          <a href="{{ route('recibos.novoAvulso') }}" class="btn btn-info">
            <i class="la la-plus"></i> Recibo Avulso
          </a>
          <a href="{{ route('recibos.novoVinculado') }}" class="btn btn-primary ml-2">
            <i class="la la-plus"></i> Recibo Vinculado
          </a>
        </div>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Nº Recibo</th>
              <th>Data de Pagamento</th>
              <th>Cliente</th>
              <th>Documento</th>
              <th>Valor Pago</th>
              <th>Forma de Pagamento</th>
              <th style="width: 250px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recibos as $recibo)
              @php
                $contas = $recibo->contasReceber;
              @endphp

              <tr style="cursor: pointer;" onclick="linhaClicada(event, {{ $recibo->id }})">
                <!-- Ao clicar na linha, chamamos linhaClicada(event, id) -->

                <td>
                  {{-- Exemplo: REC-00028 --}}
                  REC-{{ str_pad($recibo->id, 5, '0', STR_PAD_LEFT) }}
                </td>
                <td>
                  {{ \Carbon\Carbon::parse($recibo->data_pagamento)->format('d/m/Y') }}
                </td>
                <td>
                  {{ $recibo->cliente }}
                </td>
                <td>
                  {{ $recibo->documento }}
                </td>
                <td>
                  R$ {{ number_format($recibo->valor_pago, 2, ',', '.') }}
                </td>
                <td>
                  {{ $recibo->forma_pagamento }}
                </td>

                {{-- Coluna de Ações (impedir que clique aqui abra o painel de contas) --}}
                <td style="white-space: nowrap;" onclick="event.stopPropagation();">
                  {{-- Botões de impressão (A4 / Térmica) --}}
                  <a title="Imprimir recibo A4"
                     href="{{ route('recibo.pdf', $recibo->id) }}"
                     target="_blank"
                     class="btn btn-primary btn-sm">
                    <i class="la la-print"></i>
                  </a>
                  <a title="Imprimir recibo Térmico"
                     href="{{ route('recibo.pdf.termica', $recibo->id) }}"
                     target="_blank"
                     class="btn btn-success btn-sm">
                    <i class="la la-print"></i>
                  </a>

                  {{-- Edição: se tem mais de 1 conta => "reciboMulti", senão => "editarRecibo" --}}
                  @if($contas->count() > 1)
                    <a title="Editar recibo"
                       href="{{ route('contasReceber.reciboMulti', $recibo->id) }}"
                       class="btn btn-warning btn-sm">
                      <i class="la la-edit"></i>
                    </a>
                  @else
                    <a title="Editar recibo"
                       href="{{ route('contasReceber.editarRecibo', $recibo->id) }}"
                       class="btn btn-warning btn-sm">
                      <i class="la la-edit"></i>
                    </a>
                  @endif

                  {{-- Botão Excluir --}}
                  <form action="{{ route('contasReceber.excluirRecibo', $recibo->id) }}"
                        method="POST"
                        style="display:inline-block;"
                        onsubmit="return confirm('Tem certeza que deseja excluir este recibo?');">
                    @csrf
                    @method('DELETE')
                    <button title="Excluir recibo" type="submit" class="btn btn-danger btn-sm">
                      <i class="la la-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        {{-- Div para exibir as contas relacionadas ao recibo selecionado --}}
        <div id="detalhesRecibo"
             class="mt-4 p-3"
             style="display: none; border: 1px solid #ccc; background-color: #fff;">
          {{-- O conteúdo será injetado via JavaScript --}}
        </div>

        <a href="{{ url('/contasReceber') }}" class="btn btn-secondary">
          <i class="la la-arrow-left"></i> Voltar
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script>
/**
 * Ao clicar em qualquer célula (exceto na coluna de ações), chama esta função.
 * event.stopPropagation() é usado na coluna de ações para não disparar esta função.
 */
function linhaClicada(evt, reciboId) {
  const divDetalhes = document.getElementById('detalhesRecibo');
  // Mostra o div e exibe um "Carregando..."
  divDetalhes.style.display = 'block';
  divDetalhes.innerHTML = '<p>Carregando contas relacionadas...</p>';

  // Monta a URL para buscar as contas do recibo (AJAX)
  let url = "{{ route('contaReceber.recibo.contas', ':id') }}";
  url = url.replace(':id', reciboId);

  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error('Erro ao buscar as contas do recibo');
      }
      return response.json();
    })
    .then(contas => {
      if (!contas || !contas.length) {
        divDetalhes.innerHTML = `
          <h5>Recibo #${reciboId}</h5>
          <p>Nenhuma conta associada a este recibo.</p>
        `;
        return;
      }

      // Monta o HTML de tabela com as contas
      let html = `
        <h5>Contas relacionadas ao Recibo #${reciboId}</h5>
        <table class="table table-bordered mt-3">
          <thead>
            <tr>
              <th>ID da Conta</th>
              <th>Data de Vencimento</th>
              <th>Valor Integral</th>
              <th>Data de Recebimento</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
      `;
      contas.forEach(conta => {
        html += `
          <tr>
            <td>${conta.id}</td>
            <td>${formataData(conta.data_vencimento)}</td>
            <td>R$ ${parseFloat(conta.valor_integral)
                    .toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            <td>
              ${conta.status
                ? formataData(conta.data_recebimento)
                : '--'
              }
            </td>
            <td>
              ${conta.status
                ? '<span class="text-success">Recebido</span>'
                : '<span class="text-danger">Pendente</span>'
              }
            </td>
          </tr>
        `;
      });
      html += `
          </tbody>
        </table>
      `;

      divDetalhes.innerHTML = html;
    })
    .catch(error => {
      divDetalhes.innerHTML = `<p style="color:red;">${error.message}</p>`;
    });
}

/**
 * Formata datas no estilo dd/mm/yyyy.
 * dataISO é algo como "2025-03-14" ou "2025-03-14T00:00:00".
 */
function formataData(dataISO) {
  if (!dataISO) return '--';
  let soData = dataISO.split('T')[0]; // "2025-03-14"
  let partes = soData.split('-');     // [YYYY, MM, DD]
  if (partes.length < 3) return dataISO;
  return partes[2] + '/' + partes[1] + '/' + partes[0];
}

// Se quiser abrir o primeiro recibo automaticamente ao carregar a página:
document.addEventListener('DOMContentLoaded', function() {
  @if($recibos->count() > 0)
    // Chama linhaClicada para o primeiro registro
    linhaClicada(null, {{ $recibos->first()->id }});
  @endif
});
</script>
@endsection
