@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="col-12">
            <h3 class="card-title">Detalhes do Pedido #{{ $pedido->woocommerce_id }}</h3>
            <div class="col-md-4 col-sm-6">
                <a href="{{ route('woocommerce-pedidos.index') }}" class="btn btn-sm btn-light-primary">
                    <i class="la la-arrow-left"></i> Voltar
                </a>
            </div>
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

        <div class="row">
            <div class="col-md-6">
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <h3 class="card-title">Informações do Pedido</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Número:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">#{{ $pedido->woocommerce_id }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Data:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Status:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext">
                                    @if($pedido->status == 'processing')
                                    <span class="label label-lg label-inline label-light-primary">Processando</span>
                                    @elseif($pedido->status == 'pending')
                                    <span class="label label-lg label-inline label-light-warning">Pendente</span>
                                    @elseif($pedido->status == 'completed')
                                    <span class="label label-lg label-inline label-light-success">Completo</span>
                                    @elseif($pedido->status == 'cancelled')
                                    <span class="label label-lg label-inline label-light-danger">Cancelado</span>
                                    @else
                                    <span class="label label-lg label-inline label-light-info">{{ $pedido->status }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Forma de Pagamento:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->forma_pagamento }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Forma de Envio:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->forma_envio }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <h3 class="card-title">Informações do Cliente</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Nome:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->cliente->razao_social ?? $pedido->cliente_nome }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Email:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->cliente->email ?? $pedido->cliente_email }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Telefone:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->cliente->telefone ?? $pedido->cliente_telefone }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">CPF/CNPJ:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->cliente->cpf_cnpj ?? 'Não informado' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <h3 class="card-title">Endereço de Entrega</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Logradouro:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->endereco_entrega }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Número:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->numero_entrega }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Bairro:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->bairro_entrega }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Cidade:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->cidade_entrega }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">Estado:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->estado_entrega }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-4 col-form-label">CEP:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $pedido->cep_entrega }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <h3 class="card-title">Observações</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <span class="form-control-plaintext">{{ $pedido->observacao ?? 'Nenhuma observação' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <h3 class="card-title">Itens do Pedido</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th>Produto</th>
                                        <th style="width: 100px;">Quantidade</th>
                                        <th style="width: 120px;">Valor Unit.</th>
                                        <th style="width: 120px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedido->itens as $item)
                                    <tr>
                                        <td>{{ $item->produto->id ?? $item->produto_id }}</td>
                                        <td>{{ $item->produto->nome ?? $item->nome }}</td>
                                        <td>{{ $item->quantidade }}</td>
                                        <td>R$ {{ number_format($item->valor, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item->valor * $item->quantidade, 2, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td><strong>Subtotal</strong></td>
                                        <td>R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
