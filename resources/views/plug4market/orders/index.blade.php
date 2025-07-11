@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Pedidos Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.orders.create') }}" class="btn btn-primary btn-lg">
                <i class="fa fa-plus"></i> Novo Pedido
            </a>
            <a href="{{ route('plug4market.orders.test-api') }}" class="btn btn-info btn-lg" target="_blank">
                <i class="fa fa-wifi"></i> Testar API
            </a>
            <a href="{{ route('plug4market.orders.test-create') }}" class="btn btn-warning btn-lg" target="_blank">
                <i class="fa fa-flask"></i> Testar Criação
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('error') }}
            </div>
        @endif

        <!-- Tabela de Pedidos -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Status</th>
                        <th>Marketplace</th>
                        <th>Tipo</th>
                        <th>Valor Total</th>
                        <th>Frete</th>
                        <th>Data</th>
                        <th width="200">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($localOrders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                <strong>{{ $order->order_number ?? $order->external_id ?? '-' }}</strong>
                                @if($order->external_id)
                                    <br><small class="text-muted">API ID: {{ $order->external_id }}</small>
                                @endif
                            </td>
                            <td>
                                @if($order->cliente)
                                    <strong>{{ $order->cliente->razao_social ?? $order->cliente->nome_fantasia }}</strong>
                                    @if($order->cliente->cpf_cnpj)
                                        <br><small class="text-muted">{{ $order->cliente->cpf_cnpj }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">{{ $order->billing_name ?? 'Cliente não informado' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $order->status_badge }}">
                                    {{ $order->status_text }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-light">{{ $order->marketplace }}</span>
                            </td>
                                <td>
                                <span class="badge badge-{{ $order->type_billing == 'PJ' ? 'info' : 'secondary' }}">
                                    {{ $order->type_billing_text }}
                                    </span>
                                </td>
                            <td>
                                <strong>{{ $order->formatted_total_amount }}</strong>
                            </td>
                            <td>{{ $order->formatted_shipping_cost }}</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('plug4market.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('plug4market.orders.edit', $order->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('plug4market.orders.destroy', $order->id) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este pedido?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Nenhum pedido encontrado
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection 