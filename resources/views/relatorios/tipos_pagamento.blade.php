@extends('relatorios.default')
@section('content')

@if($data_inicial && $data_final)
<p>Período: {{$data_inicial}} – {{$data_final}}</p>
@endif

<table class="table-sm table-borderless"
       style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
    <thead>
        <tr>
            <th width="45%" class="text-left">Tipo</th>
            <th width="15%" class="text-left">Total</th>
        </tr>
    </thead>
    <tbody>
    @foreach($somaTiposPagamento as $key => $v)
        <tr class="@if($loop->even) pure-table-odd @endif">
            <td>{{ App\Models\VendaCaixa::getTipoPagamento($key) }}</td>
            <td>R$ {{ number_format($v, 2, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr style="border-top:1px solid rgb(206, 206, 206);">
            <td><strong>Total Geral</strong></td>
            <td><strong>
                R$ {{ number_format(array_sum($somaTiposPagamento), 2, ',', '.') }}
            </strong></td>
        </tr>
    </tfoot>
</table>

@endsection
