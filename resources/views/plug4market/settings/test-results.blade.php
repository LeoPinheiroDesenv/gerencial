@extends('default.layout')

@section('title', 'Resultados do Teste - Plug4Market')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">Resultados do Teste de Configuração</h3>
        </div>
        <div class="card-toolbar">
            <span class="badge badge-{{ $overallSuccess ? 'success' : 'danger' }} badge-lg">
                {{ $overallSuccess ? 'SUCESSO' : 'FALHA' }}
            </span>
        </div>
    </div>

    <div class="card-body">
        <!-- Resumo do Teste -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="bg-light-{{ $overallSuccess ? 'success' : 'danger' }} rounded p-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50 symbol-light-{{ $overallSuccess ? 'success' : 'danger' }} mr-4">
                            <span class="symbol-label">
                                <i class="fas fa-{{ $overallSuccess ? 'check' : 'times' }}-circle fa-2x text-{{ $overallSuccess ? 'success' : 'danger' }}"></i>
                            </span>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">Status Geral</div>
                            <div class="text-muted">
                                {{ $overallSuccess ? 'Todos os testes passaram com sucesso!' : 'Alguns testes falharam. Verifique os detalhes abaixo.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-light-info rounded p-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50 symbol-light-info mr-4">
                            <span class="symbol-label">
                                <i class="fas fa-clock fa-2x text-info"></i>
                            </span>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">Data/Hora do Teste</div>
                            <div class="text-muted">{{ now()->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalhes dos Testes -->
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Detalhes dos Testes</h3>
                </div>
            </div>
            <div class="card-body">
                @foreach($testResults as $index => $test)
                    <div class="d-flex align-items-center p-3 border-bottom {{ $index === count($testResults) - 1 ? '' : 'border-bottom' }}">
                        <div class="symbol symbol-40 symbol-light-{{ $test['status'] === 'success' ? 'success' : ($test['status'] === 'failed' ? 'danger' : ($test['status'] === 'warning' ? 'warning' : 'info')) }} mr-4">
                            <span class="symbol-label">
                                @if($test['status'] === 'success')
                                    <i class="fas fa-check text-success"></i>
                                @elseif($test['status'] === 'failed')
                                    <i class="fas fa-times text-danger"></i>
                                @elseif($test['status'] === 'warning')
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                @else
                                    <i class="fas fa-spinner fa-spin text-info"></i>
                                @endif
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="font-weight-bold text-dark">{{ $test['test'] }}</div>
                            <div class="text-muted">{{ $test['message'] }}</div>
                        </div>
                        <div class="ml-3">
                            <span class="badge badge-{{ $test['status'] === 'success' ? 'success' : ($test['status'] === 'failed' ? 'danger' : ($test['status'] === 'warning' ? 'warning' : 'info')) }} badge-lg">
                                {{ ucfirst($test['status']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Informações da Configuração -->
        <div class="card card-custom mt-4">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Informações da Configuração</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>URL da API:</strong></td>
                                <td>{{ $settings->base_url ?? 'Não configurado' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Modo Sandbox:</strong></td>
                                <td>{{ $settings->sandbox ? 'Sim' : 'Não' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Seller ID:</strong></td>
                                <td>{{ $settings->seller_id ?? 'Não configurado' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>CNPJ Software House:</strong></td>
                                <td>{{ $settings->software_house_cnpj ?? 'Não configurado' }}</td>
                            </tr>
                            <tr>
                                <td><strong>CNPJ da Loja:</strong></td>
                                <td>{{ $settings->store_cnpj ?? 'Não configurado' }}</td>
                            </tr>
                            <tr>
                                <td><strong>User ID:</strong></td>
                                <td>{{ $settings->user_id ?? 'Não configurado' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-body">
                        <a href="{{ route('plug4market.settings.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-cog"></i> Voltar às Configurações
                        </a>
                        
                        <a href="{{ route('plug4market.settings.test') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-redo"></i> Executar Teste Novamente
                        </a>
                        
                        @if($overallSuccess)
                            <a href="{{ route('plug4market.products.index') }}" class="btn btn-info btn-lg">
                                <i class="fas fa-box"></i> Ir para Produtos
                            </a>
                        @else
                            <button type="button" class="btn btn-warning btn-lg" onclick="showHelp()">
                                <i class="fas fa-question-circle"></i> Precisa de Ajuda?
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Dicas de Solução -->
        @if(!$overallSuccess)
            <div class="card card-custom mt-4">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">Dicas para Resolver os Problemas</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb"></i> Dicas de Solução:</h5>
                        <ul class="mb-0">
                            <li><strong>Tokens não configurados:</strong> Configure os tokens de acesso e refresh token nas configurações</li>
                            <li><strong>URL da API:</strong> Verifique se a URL está correta (sandbox ou produção)</li>
                            <li><strong>Erro de conectividade:</strong> Verifique sua conexão com a internet e se a API está acessível</li>
                            <li><strong>Tokens inválidos:</strong> Gere novos tokens no painel do Plug4Market</li>
                            <li><strong>Configurações específicas:</strong> Preencha todos os campos obrigatórios (Seller ID, CNPJs, etc.)</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@section('js')
<script>
function showHelp() {
    // Aqui você pode implementar um modal ou redirecionamento para uma página de ajuda
    alert('Para obter ajuda, entre em contato com o suporte técnico ou consulte a documentação do Plug4Market.');
}
</script>
@endsection 