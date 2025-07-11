@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Etiquetas de Pedidos Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.labels.create') }}" class="btn btn-primary btn-lg">
                <i class="fa fa-plus"></i> Nova Etiqueta
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

        @if(isset($error))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ $error }}
            </div>
        @endif

        <!-- Botões de Ação -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('plug4market.orders.index') }}" class="btn btn-info btn-lg">
                    <i class="fas fa-shopping-cart"></i> Ver Pedidos
                </a>
                <a href="{{ route('plug4market.settings.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </div>
        </div>

        <!-- Tabela de Etiquetas -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Pedido</th>
                        <th>Transportadora</th>
                        <th>Serviço</th>
                        <th>Código de Rastreio</th>
                        <th>Data de Envio</th>
                        <th>Previsão de Entrega</th>
                        <th>Custo</th>
                        <th>Status</th>
                        <th width="200">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labels as $label)
                        <tr>
                            <td>
                                <strong>{{ $label['id'] ?? '-' }}</strong>
                            </td>
                            <td>
                                <strong>#{{ $label['orderId'] ?? '-' }}</strong>
                                @if(isset($label['order']))
                                    <br><small class="text-muted">{{ $label['order']['customerName'] ?? 'Cliente não informado' }}</small>
                                @endif
                            </td>
                            <td>
                                @if(isset($label['shippingCompany']))
                                    <span class="badge badge-info">{{ $label['shippingCompany'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($label['shippingService']))
                                    <span class="badge badge-light">{{ $label['shippingService'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($label['trackingCode']) && !empty($label['trackingCode']))
                                    <code>{{ $label['trackingCode'] }}</code>
                                    @if(isset($label['shippingCompany']) && $label['shippingCompany'] == 'correios')
                                        <br><a href="https://rastreamento.correios.com.br/app/index.php?objeto={{ $label['trackingCode'] }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Rastrear
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($label['shippingDate']))
                                    {{ \Carbon\Carbon::parse($label['shippingDate'])->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($label['estimatedDelivery']))
                                    {{ \Carbon\Carbon::parse($label['estimatedDelivery'])->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($label['shippingCost']) && $label['shippingCost'] > 0)
                                    <strong>R$ {{ number_format($label['shippingCost'], 2, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
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
                            <td>
                                <a href="{{ route('plug4market.labels.show', $label['id']) }}" class="btn btn-sm btn-info" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('plug4market.labels.edit', $label['id']) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('plug4market.labels.destroy', $label['id']) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta etiqueta?')" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Nenhuma etiqueta encontrada
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Estatísticas -->
        @if(count($labels) > 0)
            <div class="row mt-4">
                <div class="col-md-2">
                    <div class="bg-light-primary rounded p-3 text-center">
                        <div class="font-weight-bold text-primary font-size-h4">{{ count($labels) }}</div>
                        <div class="text-muted">Total</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="bg-light-success rounded p-3 text-center">
                        <div class="font-weight-bold text-success font-size-h4">
                            {{ collect($labels)->where('status', 'delivered')->count() }}
                        </div>
                        <div class="text-muted">Entregues</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="bg-light-info rounded p-3 text-center">
                        <div class="font-weight-bold text-info font-size-h4">
                            {{ collect($labels)->where('status', 'shipped')->count() }}
                        </div>
                        <div class="text-muted">Enviadas</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="bg-light-primary rounded p-3 text-center">
                        <div class="font-weight-bold text-primary font-size-h4">
                            {{ collect($labels)->where('status', 'in_transit')->count() }}
                        </div>
                        <div class="text-muted">Em Trânsito</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="bg-light-warning rounded p-3 text-center">
                        <div class="font-weight-bold text-warning font-size-h4">
                            {{ collect($labels)->where('status', 'pending')->count() }}
                        </div>
                        <div class="text-muted">Pendentes</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="bg-light-danger rounded p-3 text-center">
                        <div class="font-weight-bold text-danger font-size-h4">
                            {{ collect($labels)->where('status', 'cancelled')->count() }}
                        </div>
                        <div class="text-muted">Canceladas</div>
                    </div>
                </div>
            </div>

            <!-- Resumo de Custos -->
            @php
                $totalCost = collect($labels)->sum('shippingCost');
                $avgCost = count($labels) > 0 ? $totalCost / count($labels) : 0;
            @endphp
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="bg-light-success rounded p-3">
                        <div class="font-weight-bold text-success font-size-h5">
                            R$ {{ number_format($totalCost, 2, ',', '.') }}
                        </div>
                        <div class="text-muted">Custo Total de Envio</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light-info rounded p-3">
                        <div class="font-weight-bold text-info font-size-h5">
                            R$ {{ number_format($avgCost, 2, ',', '.') }}
                        </div>
                        <div class="text-muted">Custo Médio por Envio</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection 