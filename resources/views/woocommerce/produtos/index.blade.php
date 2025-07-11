@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="col-12">
            <h3 class="card-title">Produtos - WooCommerce</h3>
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
                                    <form method="get" action="{{ route('woocommerce-produtos.index') }}">
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
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 250px;">Produto</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Valor</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Estoque</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 140px;">ID WooCommerce</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 140px;">Status</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 180px;">Ações</span></th>
                                                </tr>
                                            </thead>

                                            <tbody class="datatable-body">
                                                @foreach($produtos as $p)
                                                <tr class="datatable-row">
                                                    <td class="datatable-cell" style="display: flex; align-items: center;">
                                                        @if($p->imagem != "")
                                                        <span style="width: 250px;">
                                                            <div class="d-flex align-items-center">
                                                                <div class="symbol symbol-40 symbol-sm flex-shrink-0">
                                                                    <img class="symbol-label" src="/imgs_produtos/{{$p->imagem}}" alt="{{$p->nome}}">
                                                                </div>
                                                                <div class="ml-4">
                                                                    <div class="text-dark-75 font-weight-bolder font-size-lg mb-0">{{$p->nome}}</div>
                                                                    <a class="text-muted font-weight-bold text-hover-primary">Ref: {{$p->referencia}}</a>
                                                                </div>
                                                            </div>
                                                        </span>
                                                        @else
                                                        <span style="width: 250px;">
                                                            <div class="d-flex align-items-center">
                                                                <div class="symbol symbol-40 symbol-light-info flex-shrink-0">
                                                                    <span class="symbol-label font-size-h4 font-weight-bold">{{$p->nome[0]}}</span>
                                                                </div>
                                                                <div class="ml-4">
                                                                    <div class="text-dark-75 font-weight-bolder font-size-lg mb-0">{{$p->nome}}</div>
                                                                    <a class="text-muted font-weight-bold text-hover-primary">Ref: {{$p->referencia}}</a>
                                                                </div>
                                                            </div>
                                                        </span>
                                                        @endif
                                                    </td>
                                                    <td class="datatable-cell"><span style="width: 120px;">R$ {{ number_format($p->valor_venda, 2, ',', '.') }}</span></td>
                                                    <td class="datatable-cell"><span style="width: 120px;">{{ $p->estoque ? $p->estoque->quantidade : 0 }}</span></td>
                                                    <td class="datatable-cell"><span style="width: 140px;">{{ $p->woocommerce ? $p->woocommerce->woocommerce_id : 'Não sincronizado' }}</span></td>
                                                    <td class="datatable-cell">
                                                        <span style="width: 140px;">
                                                            @if($p->woocommerce && $p->woocommerce->woocommerce_status == 'publish')
                                                            <span class="label label-xl label-inline label-light-success">Publicado</span>
                                                            @elseif($p->woocommerce && $p->woocommerce->woocommerce_status == 'draft')
                                                            <span class="label label-xl label-inline label-light-warning">Rascunho</span>
                                                            @else
                                                            <span class="label label-xl label-inline label-light-danger">Não sincronizado</span>
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td class="datatable-cell">
                                                        <span style="width: 180px;">
                                                            <form action="{{ route('woocommerce-produtos.sincronizar', $p->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-light-primary">
                                                                    @if($p->woocommerce)
                                                                    <i class="la la-sync"></i> Atualizar
                                                                    @else
                                                                    <i class="la la-cloud-upload-alt"></i> Sincronizar
                                                                    @endif
                                                                </button>
                                                            </form>
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
                                    {{ $produtos->links() }}
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
