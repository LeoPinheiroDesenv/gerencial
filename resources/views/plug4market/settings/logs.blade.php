@extends('default.layout')

@section('title', $title)

@section('content')
<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.settings.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="bg-light-primary rounded p-4 text-center">
                    <div class="font-weight-bold text-primary font-size-h2">{{ $stats['total'] }}</div>
                    <div class="text-muted">Total de Logs</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light-success rounded p-4 text-center">
                    <div class="font-weight-bold text-success font-size-h2">{{ $stats['today'] }}</div>
                    <div class="text-muted">Logs Hoje</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light-danger rounded p-4 text-center">
                    <div class="font-weight-bold text-danger font-size-h2">{{ $stats['errors'] }}</div>
                    <div class="text-muted">Erros</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light-info rounded p-4 text-center">
                    <div class="font-weight-bold text-info font-size-h2">{{ $stats['success'] }}</div>
                    <div class="text-muted">Sucessos</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light-warning rounded p-4 text-center">
                    <div class="font-weight-bold text-warning font-size-h2">{{ $stats['products'] }}</div>
                    <div class="text-muted">Produtos</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light-dark rounded p-4 text-center">
                    <div class="font-weight-bold text-dark font-size-h2">{{ $stats['categories'] }}</div>
                    <div class="text-muted">Categorias</div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="bg-light-secondary rounded p-4 text-center">
                    <div class="font-weight-bold text-secondary font-size-h2">{{ $stats['orders'] }}</div>
                    <div class="text-muted">Pedidos</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light-danger rounded p-3 text-center">
                    <div class="font-weight-bold text-danger font-size-h4">{{ $stats['sync_errors'] }}</div>
                    <div class="text-muted">Erros de Sincronização</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light-warning rounded p-3 text-center">
                    <div class="font-weight-bold text-warning font-size-h4">{{ $stats['recent_errors'] }}</div>
                    <div class="text-muted">Erros Últimos 7 Dias</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-light-info rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="font-weight-bold text-info">Status do Sistema</div>
                            <div class="text-muted">
                                @if($stats['recent_errors'] > 0)
                                    <span class="text-danger">⚠️ {{ $stats['recent_errors'] }} erros recentes</span>
                                @else
                                    <span class="text-success">✅ Sistema funcionando normalmente</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('plug4market.settings.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-cog"></i> Configurações
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Filtros -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Filtros</h3>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('plug4market.settings.logs') }}">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="type">Tipo</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Todos os tipos</option>
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="action">Ação</label>
                                <select name="action" id="action" class="form-control">
                                    <option value="">Todas as ações</option>
                                    @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Todos os status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_from">Data Inicial</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_to">Data Final</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('plug4market.settings.logs') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search">Buscar no texto</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Buscar em mensagens, ações ou detalhes..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('plug4market.settings.logs') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Limpar busca
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Tabela de Logs -->
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Logs ({{ $logs->total() }} registros)</h3>
                </div>
                <div class="card-toolbar">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportLogs()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshLogs()">
                            <i class="fa fa-refresh"></i> Atualizar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Ação</th>
                                    <th>Status</th>
                                    <th>Mensagem</th>
                                    <th>Tempo</th>
                                    <th>IP</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr class="{{ $log->status === 'error' ? 'table-danger' : ($log->status === 'success' ? 'table-success' : '') }}">
                                    <td>
                                        <div class="font-weight-bold">{{ $log->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $typeIcon = '';
                                            $typeClass = '';
                                            if (str_contains($log->action, 'product')) {
                                                $typeIcon = 'fas fa-box';
                                                $typeClass = 'text-warning';
                                            } elseif (str_contains($log->action, 'category')) {
                                                $typeIcon = 'fas fa-folder';
                                                $typeClass = 'text-info';
                                            } elseif (str_contains($log->action, 'order')) {
                                                $typeIcon = 'fas fa-shopping-cart';
                                                $typeClass = 'text-secondary';
                                            } elseif (str_contains($log->action, 'label')) {
                                                $typeIcon = 'fas fa-tag';
                                                $typeClass = 'text-success';
                                            } elseif (str_contains($log->action, 'sync')) {
                                                $typeIcon = 'fas fa-sync-alt';
                                                $typeClass = 'text-primary';
                                            } elseif (str_contains($log->action, 'token')) {
                                                $typeIcon = 'fas fa-key';
                                                $typeClass = 'text-success';
                                            } else {
                                                $typeIcon = 'fas fa-cog';
                                                $typeClass = 'text-secondary';
                                            }
                                        @endphp
                                        <i class="{{ $typeIcon }} {{ $typeClass }}" title="{{ ucfirst(str_replace('_', ' ', $log->action)) }}"></i>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $log->status_color }}">{{ ucfirst($log->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">{{ Str::limit($log->message, 50) }}</div>
                                        @if(strlen($log->message) > 50)
                                            <small class="text-muted">{{ Str::limit($log->message, 100) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $log->formatted_execution_time }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $log->ip_address ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('plug4market.settings.log-details', $log->id) }}" class="btn btn-sm btn-info" title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($log->status === 'error')
                                                <button type="button" class="btn btn-sm btn-warning" onclick="copyErrorDetails('{{ $log->id }}')" title="Copiar detalhes do erro">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Nenhum log encontrado</h4>
                        <p class="text-muted">
                            @if(request()->hasAny(['action', 'status', 'type', 'search', 'date_from', 'date_to']))
                                Não há logs que correspondam aos filtros aplicados.
                                <br><a href="{{ route('plug4market.settings.logs') }}" class="btn btn-sm btn-primary mt-2">Limpar filtros</a>
                            @else
                                Ainda não há logs registrados no sistema.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#action, #status, #type').change(function() {
        $(this).closest('form').submit();
    });
});
function exportLogs() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', '1');
    window.open(currentUrl.toString(), '_blank');
}
function refreshLogs() {
    window.location.reload();
}
function copyErrorDetails(logId) {
    alert('Funcionalidade de copiar detalhes será implementada em breve.');
}
</script>
@endsection 