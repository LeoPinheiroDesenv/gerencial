@extends('default.layout', ['title' => 'Pedido Mercado Livre #'.$item->_id])
@section('css')
<style type="text/css">
    @page { size: auto;  margin: 0mm; }

    @media print {
        .print{
            margin: 10px;
        }
    }
</style>
@endsection
@section('content')

<div class="card mt-1 print">
    <div class="card-body">
        <div class="pl-lg-4">

            <div class="ms">

                <div class="mt-3 d-print-none" style="text-align: right;">
                    <a href="{{ route('mercado-livre-pedidos.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                <div class="row mb-2">

                    <div class="col-md-3 col-6">
                        <h5><strong class="text-danger">#{{ $item->_id }}</strong></h5>
                    </div>
                    <div class="col-md-3 col-6">
                        <h5>Data do pedido: <strong class="text-primary">{{ __date($item->data_pedido) }}</strong></h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Data de Cadastro no Sistema: <strong class="text-primary">{{ __date($item->created_at) }}</strong></h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Código de rastreamento: <strong class="text-primary">{{ $item->codigo_rastreamento ? $item->codigo_rastreamento : '--' }}</strong></h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Valor Total: <strong class="text-primary">R$ {{ moeda($item->total) }}</strong> </h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Valor Entrega: <strong class="text-primary">R$ {{ moeda($item->valor_entrega) }}</strong> </h5>
                    </div>

                </div>

                <a class="btn btn-primary btn-sm d-print-none" href="javascript:window.print()" >
                    <i class="la la-print"></i>
                    Imprimir
                </a>
                @if($item->venda_id == 0)
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('mercado-livre-pedidos.gerar-nfe', $item->id) }}">
                    <i class="la la-file"></i>
                    Gerar NFe
                </a>
                @else
                <a class="btn btn-success btn-sm d-print-none" href="/vendas/detalhar/{{$item->venda_id}}">
                    <i class="la la-file-alt"></i>
                    Ver NFe
                </a>

                @endif

                <a class="btn btn-dark btn-sm d-print-none" href="{{ route('mercado-livre-pedidos.chat', [$item->id]) }}">
                    <i class="la la-comment"></i>
                    Chat
                </a>


            </div>

            <div class="row mt-2">
                <div class="col-12">
                    <h4>Itens do pedido</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor unitário</th>
                                    <th>Sub total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->itens as $i)
                                <tr>
                                    <td>{{ $i->produto->nome }}</td>
                                    <td>{{ number_format($i->quantidade, 0) }}</td>
                                    <td>{{ moeda($i->valor_unitario) }}</td>
                                    <td>{{ moeda($i->sub_total) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4">

                <div class="col-md-6 col-12">
                    <h4>
                        Cliente: <strong>{{ $item->cliente_nome }}</strong>
                        @if($item->cliente)
                        <a href="/clientes/edit/{{$item->cliente_id}}" class="btn btn-warning btn-sm d-print-none">
                            <i class="la la-edit"></i>
                        </a>
                        @else
                        <button class="btn btn-dark btn-sm d-print-none" data-bs-toggle="modal" data-bs-target="#modal-cliente">Atribuir cliente</button>
                        @endif
                    </h4>
                    <h4>ID: <strong>{{ $item->seller_id }}</strong></h4>

                </div>
                <div class="col-md-6 col-12">
                    <h4>Documento do Cliente: <strong>{{ $item->cliente_documento }}</strong></h4>
                    <h4>Comentário do Pedido: <strong>{{ $item->comentario ? $item->comentario : '--' }}</strong></h4>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-cliente" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('mercado-livre-pedidos.set-cliente', [$item->id]) }}">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Atribuir cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12">
                            <label>Cliente</label>
                            <select id="inp-cliente_id" name="cliente_id" class="form-select"></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Atribuir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    $("#inp-cliente_id").select2({
        minimumInputLength: 2,
        language: "pt-BR",
        placeholder: "Digite para buscar o cliente",
        theme: "bootstrap4",
        dropdownParent: $('#modal-cliente'),
        ajax: {
            cache: true,
            url: path_url + "api/clientes/pesquisa",
            dataType: "json",
            data: function (params) {
                console.clear();
                var query = {
                    pesquisa: params.term,
                    empresa_id: $("#empresa_id").val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];

                $.each(response, function (i, v) {
                    var o = {};
                    o.id = v.id;

                    o.text = v.razao_social + " - " + v.cpf_cnpj;
                    o.value = v.id;
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    });
</script>

@endsection
