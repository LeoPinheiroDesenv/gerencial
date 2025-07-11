@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Detalhes da Etiqueta Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.labels.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
            <a href="{{ route('plug4market.labels.edit', $id) }}" class="btn btn-warning btn-lg">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(empty($label))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atenção:</strong> Não foi possível carregar os detalhes da etiqueta.
            </div>
        @else
            <!-- Informações Principais -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-custom card-stretch">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Informações da Etiqueta</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID da Etiqueta:</strong></td>
                                    <td><span class="badge badge-primary">{{ $label['id'] ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Pedido:</strong></td>
                                    <td>
                                        <strong>#{{ $label['orderId'] ?? '-' }}</strong>
                                        @if(isset($label['order']))
                                            <br><small class="text-muted">{{ $label['order']['customerName'] ?? 'Cliente não informado' }}</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Transportadora:</strong></td>
                                    <td>
                                        @if(isset($label['shippingCompany']))
                                            <span class="badge badge-info">{{ $label['shippingCompany'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Serviço:</strong></td>
                                    <td>
                                        @if(isset($label['shippingService']))
                                            <span class="badge badge-light">{{ $label['shippingService'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if(isset($label['status']))
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'shipped' => 'info',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger',
                                                    'in_transit' => 'primary'
                                                ];
                                                $statusTexts = [
                                                    'pending' => 'Pendente',
                                                    'shipped' => 'Enviado',
                                                    'delivered' => 'Entregue',
                                                    'cancelled' => 'Cancelado',
                                                    'in_transit' => 'Em Trânsito'
                                                ];
                                                $color = $statusColors[$label['status']] ?? 'secondary';
                                                $text = $statusTexts[$label['status']] ?? $label['status'];
                                            @endphp
                                            <span class="badge badge-{{ $color }}">{{ $text }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-custom card-stretch">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Informações de Rastreio</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Código de Rastreio:</strong></td>
                                    <td>
                                        @if(isset($label['trackingCode']) && !empty($label['trackingCode']))
                                            <code>{{ $label['trackingCode'] }}</code>
                                            @if(isset($label['shippingCompany']) && $label['shippingCompany'] == 'correios')
                                                <br><a href="https://rastreamento.correios.com.br/app/index.php?objeto={{ $label['trackingCode'] }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                    <i class="fas fa-external-link-alt"></i> Rastrear nos Correios
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Data de Envio:</strong></td>
                                    <td>
                                        @if(isset($label['shippingDate']))
                                            {{ \Carbon\Carbon::parse($label['shippingDate'])->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Previsão de Entrega:</strong></td>
                                    <td>
                                        @if(isset($label['estimatedDelivery']))
                                            {{ \Carbon\Carbon::parse($label['estimatedDelivery'])->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Custo do Envio:</strong></td>
                                    <td>
                                        @if(isset($label['shippingCost']) && $label['shippingCost'] > 0)
                                            <strong class="text-success">R$ {{ number_format($label['shippingCost'], 2, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            @if(isset($label['notes']) && !empty($label['notes']))
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-label">Observações</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $label['notes'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Informações do Pedido -->
            @if(isset($label['order']))
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-label">Informações do Pedido</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>ID do Pedido:</strong></td>
                                                <td>#{{ $label['order']['id'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Cliente:</strong></td>
                                                <td>{{ $label['order']['customerName'] ?? 'Não informado' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>{{ $label['order']['customerEmail'] ?? 'Não informado' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Valor Total:</strong></td>
                                                <td>
                                                    @if(isset($label['order']['totalAmount']))
                                                        <strong class="text-success">R$ {{ number_format($label['order']['totalAmount'], 2, ',', '.') }}</strong>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status do Pedido:</strong></td>
                                                <td>
                                                    @if(isset($label['order']['status']))
                                                        <span class="badge badge-info">{{ $label['order']['status'] }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Data do Pedido:</strong></td>
                                                <td>
                                                    @if(isset($label['order']['createdAt']))
                                                        {{ \Carbon\Carbon::parse($label['order']['createdAt'])->format('d/m/Y H:i') }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Dados da API -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Dados da API</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Informação:</strong> Esta etiqueta está sincronizada com a API Plug4Market.
                            </div>
                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($label, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ações -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-body">
                        <a href="{{ route('plug4market.labels.edit', $id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar Etiqueta
                        </a>
                        <form action="{{ route('plug4market.labels.destroy', $id) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta etiqueta?')">
                                <i class="fas fa-trash"></i> Excluir Etiqueta
                            </button>
                        </form>
                        <a href="{{ route('plug4market.labels.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Voltar à Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 