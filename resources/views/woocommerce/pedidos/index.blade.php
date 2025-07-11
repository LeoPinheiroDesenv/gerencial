@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="col-12">
            <h3 class="card-title">Pedidos - WooCommerce</h3>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-12">
                @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
                @endif
                @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
                @endif
            </div>
        </div>

        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
            <div class="row">
                <div class="col-xl-12">
                    <div class="kt-section kt-section--first">
                        <div class="kt-section__body">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-5 col-lg-5 col-xl-5">
                                    <form method="get" action="{{ route('woocommerce-pedidos.index') }}">
                                        <div class="input-icon ml-10">
                                            <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar..." value="{{ isset($pesquisa) ? $pesquisa : '' }}">
                                            <span>
                                                <i class="fa fa-search"></i>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">
                                        <table class="datatable-table" style="max-width: 100%; overflow: scroll;">
                                            <thead class="datatable-head">
                                                <tr class="datatable-row" style="left: 0px;">
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Pedido #</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 180px;">Cliente</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Total</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 140px;">Status</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Data</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Ações</span></th>
                                                </tr>
                                            </thead>

                                            <tbody class="datatable-body">
                                                @foreach($pedidos as $p)
                                                <tr class="datatable-row">
                                                    <td class="datatable-cell"><span style="width: 100px;">#{{ $p->woocommerce_id }}</span></td>
                                                    <td class="datatable-cell"><span style="width: 180px;">{{ $p->cliente->razao_social ?? $p->cliente_nome }}</span></td>
                                                    <td class="datatable-cell"><span style="width: 120px;">R$ {{ number_format($p->total, 2, ',', '.') }}</span></td>
                                                    <td class="datatable-cell">
                                                        <span style="width: 140px;">
                                                            @if($p->status == 'processing')
                                                            <span class="label label-xl label-inline label-light-primary">Processando</span>
                                                            @elseif($p->status == 'pending')
                                                            <span class="label label-xl label-inline label-light-warning">Pendente</span>
                                                            @elseif($p->status == 'completed')
                                                            <span class="label label-xl label-inline label-light-success">Completo</span>
                                                            @elseif($p->status == 'cancelled')
                                                            <span class="label label-xl label-inline label-light-danger">Cancelado</span>
                                                            @else
                                                            <span class="label label-xl label-inline label-light-info">{{ $p->status }}</span>
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td class="datatable-cell"><span style="width: 150px;">{{ \Carbon\Carbon::parse($p->data_pedido)->format('d/m/Y H:i') }}</span></td>
                                                    <td class="datatable-cell">
                                                        <span style="width: 120px;">
                                                            <a href="{{ route('woocommerce-pedidos.show', $p->id) }}" class="btn btn-sm btn-light-primary">
                                                                <i class="la la-eye"></i>
                                                            </a>
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex flex-wrap py-2 mr-3">
                                    {{ $pedidos->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
