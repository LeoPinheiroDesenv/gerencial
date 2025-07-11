@extends('default.layout')

@section('title', $title)

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.settings.logs') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar aos Logs
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Informações Básicas -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Informações Básicas</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>{{ $log->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Data/Hora:</strong></td>
                                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ação:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $log->status_color }}">{{ ucfirst($log->status) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tempo de Execução:</strong></td>
                                <td>{{ $log->formatted_execution_time }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Informações da Requisição</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>IP Address:</strong></td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>User Agent:</strong></td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($log->user_agent, 50) ?? 'N/A' }}</small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Atualizado em:</strong></td>
                                <td>{{ $log->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensagem -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Mensagem</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-{{ $log->status == 'error' ? 'danger' : ($log->status == 'warning' ? 'warning' : 'info') }}">
                            {{ $log->message }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalhes -->
        @if($log->details)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Detalhes</h3>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-primary" onclick="copyToClipboard()">
                                <i class="fas fa-copy"></i> Copiar JSON
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <pre id="jsonDetails" class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;">{{ $log->formatted_details }}</pre>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- User Agent Completo -->
        @if($log->user_agent)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">User Agent Completo</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <code class="bg-light p-2 rounded d-block">{{ $log->user_agent }}</code>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Ações -->
        <div class="row">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-body text-center">
                        <a href="{{ route('plug4market.settings.logs') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar aos Logs
                        </a>
                        <a href="{{ route('plug4market.settings.index') }}" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Configurações
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
function copyToClipboard() {
    const jsonText = document.getElementById('jsonDetails').textContent;
    
    navigator.clipboard.writeText(jsonText).then(function() {
        // Show success message
        toastr.success('JSON copiado para a área de transferência!');
    }).catch(function(err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = jsonText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        toastr.success('JSON copiado para a área de transferência!');
    });
}
</script>
@endsection 