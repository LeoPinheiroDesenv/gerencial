@extends('default.layout')

@section('title', $title)

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title }}</h3>
        </div>
        <div class="card-toolbar">
            <span class="badge badge-{{ $settings->status_color }} badge-lg">
                {{ $settings->status_text }}
            </span>
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

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('warning') }}
            </div>
        @endif

        <!-- Status da Configuração -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="bg-light-primary rounded p-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50 symbol-light-primary mr-4">
                            <span class="symbol-label">
                                <i class="fas fa-cog fa-2x text-primary"></i>
                            </span>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">Status da Configuração</div>
                            <div class="text-muted">{{ $settings->status_text }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light-info rounded p-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50 symbol-light-info mr-4">
                            <span class="symbol-label">
                                <i class="fas fa-clock fa-2x text-info"></i>
                            </span>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">Último Teste</div>
                            <div class="text-muted">
                                @if($settings->last_test_at)
                                    {{ $settings->last_test_at->format('d/m/Y H:i:s') }}
                                @else
                                    Nunca testado
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light-success rounded p-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50 symbol-light-success mr-4">
                            <span class="symbol-label">
                                <i class="fas fa-list fa-2x text-success"></i>
                            </span>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">Logs Hoje</div>
                            <div class="text-muted">{{ $todayLogs->count() }} registros</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light-danger rounded p-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50 symbol-light-danger mr-4">
                            <span class="symbol-label">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </span>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">Erros Recentes</div>
                            <div class="text-muted">{{ $errorLogs->count() }} erros</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Recentes -->
        @if($recentLogs->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Logs Recentes</h3>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('plug4market.settings.logs') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-list"></i> Ver Todos os Logs
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>Ação</th>
                                        <th>Status</th>
                                        <th>Mensagem</th>
                                        <th>Tempo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLogs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $log->action }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $log->status_color }}">{{ $log->status }}</span>
                                        </td>
                                        <td>{{ Str::limit($log->message, 50) }}</td>
                                        <td>{{ $log->formatted_execution_time }}</td>
                                        <td>
                                            @if($log->id)
                                                <a href="{{ route('plug4market.settings.log-details', $log->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Ver detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Formulário de Configuração -->
        <form action="{{ route('plug4market.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Credenciais do Usuário -->
                

                <!-- Tokens -->
                <div class="col-md-12">
                    <div class="card card-custom">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Tokens de Acesso</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Atenção:</strong> Os tokens são gerados automaticamente usando as credenciais do usuário. Não edite manualmente a menos que seja necessário.
                            </div>
                            
                            <div class="form-group">
                                <label for="access_token">Access Token</label>
                                <textarea
                                    name="access_token"
                                    id="access_token"
                                    class="form-control @error('access_token') is-invalid @enderror"
                                    rows="4"
                                    placeholder="Cole aqui o access token do Plug4Market"
                                >{{ old('access_token', $settings->access_token) }}</textarea>
                                @error('access_token')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="refresh_token">Refresh Token</label>
                                <textarea
                                    name="refresh_token"
                                    id="refresh_token"
                                    class="form-control @error('refresh_token') is-invalid @enderror"
                                    rows="4"
                                    placeholder="Cole aqui o refresh token do Plug4Market"
                                >{{ old('refresh_token', $settings->refresh_token) }}</textarea>
                                @error('refresh_token')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Configurações Gerais -->
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Configurações Gerais</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="base_url">URL da API</label>
                                <input
                                    type="url"
                                    name="base_url"
                                    id="base_url"
                                    class="form-control @error('base_url') is-invalid @enderror"
                                    value="{{ old('base_url', $settings->base_url) }}"
                                    placeholder="https://api.sandbox.plug4market.com.br"
                                >
                                @error('base_url')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="checkbox-inline">
                                    <label class="checkbox">
                                        <input
                                            type="checkbox"
                                            name="sandbox"
                                            value="1"
                                            {{ old('sandbox', $settings->sandbox) ? 'checked' : '' }}
                                        >
                                        <span></span>
                                        Modo Sandbox
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="seller_id">Seller ID</label>
                                <input
                                    type="text"
                                    name="seller_id"
                                    id="seller_id"
                                    class="form-control @error('seller_id') is-invalid @enderror"
                                    value="{{ old('seller_id', $settings->seller_id) }}"
                                    placeholder="7"
                                >
                                @error('seller_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="software_house_cnpj">CNPJ Software House</label>
                                <input
                                    type="text"
                                    name="software_house_cnpj"
                                    id="software_house_cnpj"
                                    class="form-control @error('software_house_cnpj') is-invalid @enderror"
                                    value="{{ old('software_house_cnpj', $settings->software_house_cnpj) }}"
                                    placeholder="04026307000112"
                                >
                                @error('software_house_cnpj')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="store_cnpj">CNPJ da Loja</label>
                                <input
                                    type="text"
                                    name="store_cnpj"
                                    id="store_cnpj"
                                    class="form-control @error('store_cnpj') is-invalid @enderror"
                                    value="{{ old('store_cnpj', $settings->store_cnpj) }}"
                                    placeholder="04026307000112"
                                >
                                @error('store_cnpj')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="user_id">User ID</label>
                                <input
                                    type="text"
                                    name="user_id"
                                    id="user_id"
                                    class="form-control @error('user_id') is-invalid @enderror"
                                    value="{{ old('user_id', $settings->user_id) }}"
                                    placeholder="89579395-cc99-4a2a-8bb9-8e2165d7611d"
                                >
                                @error('user_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Geração de Tokens -->
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Geração Automática de Tokens</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <i class="fas fa-magic"></i>
                                <strong>Automação:</strong> Gere tokens automaticamente usando suas credenciais do Plug4Market.
                            </div>
                            
                            <div class="form-group">
                                <label for="generate_user_login">Email de Login</label>
                                <input
                                    type="email"
                                    id="generate_user_login"
                                    class="form-control"
                                    placeholder="seu-email@exemplo.com"
                                    value="{{ $settings->user_login }}"
                                >
                            </div>

                            <div class="form-group">
                                <label for="generate_user_password">Senha</label>
                                <input
                                    type="password"
                                    id="generate_user_password"
                                    class="form-control"
                                    placeholder="Sua senha do Plug4Market"
                                    value="{{ $settings->user_password }}"
                                >
                            </div>

                            <div class="form-group">
                                <label for="generate_store_cnpj">CNPJ da Loja</label>
                                <input
                                    type="text"
                                    id="generate_store_cnpj"
                                    class="form-control"
                                    placeholder="04026307000112"
                                    value="{{ $settings->store_cnpj }}"
                                >
                            </div>

                            <div class="form-group">
                                <label for="generate_software_house_cnpj">CNPJ Software House</label>
                                <input
                                    type="text"
                                    id="generate_software_house_cnpj"
                                    class="form-control"
                                    placeholder="04026307000112"
                                    value="{{ $settings->software_house_cnpj }}"
                                >
                            </div>

                            <button type="button" class="btn btn-success btn-lg btn-block" onclick="generateTokens()">
                                <i class="fas fa-key"></i> Gerar Tokens Automaticamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Salvar Configurações
                            </button>

                            <a href="{{ route('plug4market.settings.test') }}" class="btn btn-success btn-lg">
                                <i class="fas fa-plug"></i> Testar Conexão (Detalhado)
                            </a>

                            <button type="button" class="btn btn-info btn-lg" onclick="getTokenInfo()">
                                <i class="fas fa-info-circle"></i> Informações do Token
                            </button>

                            <a href="{{ route('plug4market.settings.logs') }}" class="btn btn-warning btn-lg">
                                <i class="fas fa-list"></i> Ver Logs
                            </a>

                            <a href="{{ route('plug4market.products.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Resultado do Último Teste -->
        @if($settings->last_test_at)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Resultado do Último Teste</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Data/Hora:</strong> {{ $settings->last_test_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    <span class="badge badge-{{ $settings->last_test_success ? 'success' : 'danger' }} badge-lg">
                                        {{ $settings->last_test_success ? 'Sucesso' : 'Falha' }}
                                    </span>
                                </div>
                            </div>
                            @if($settings->last_test_message)
                                <div class="mt-2">
                                    <strong>Mensagem:</strong> {{ $settings->last_test_message }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Informações sobre o Teste -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Sobre o Teste de Conexão</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> O que o teste verifica:</h5>
                            <ul class="mb-0">
                                <li><strong>Configurações Básicas:</strong> Verifica se os tokens estão configurados</li>
                                <li><strong>URL da API:</strong> Valida se a URL da API está configurada</li>
                                <li><strong>Conectividade:</strong> Testa se consegue acessar a API do Plug4Market</li>
                                <li><strong>Validação de Tokens:</strong> Verifica se os tokens são válidos e funcionais</li>
                                <li><strong>Busca de Produtos:</strong> Testa se consegue buscar produtos da API</li>
                                <li><strong>Configurações Específicas:</strong> Verifica se todos os campos obrigatórios estão preenchidos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Informações do Token -->
<div class="modal fade" id="tokenInfoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Informações do Token</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="tokenInfoContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Geração de Tokens -->
<div class="modal fade" id="generateTokensModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Geração de Tokens</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="generateTokensContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Gerando tokens...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
function getTokenInfo() {
    $('#tokenInfoModal').modal('show');
    $('#tokenInfoContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');

    $.ajax({
        url: '{{ route("plug4market.settings.token-info") }}',
        method: 'GET',
        success: function(response) {
            let html = '<div class="table-responsive"><table class="table table-bordered">';
            for (let key in response) {
                html += '<tr><td><strong>' + key.replace(/_/g, ' ').toUpperCase() + '</strong></td><td>' + response[key] + '</td></tr>';
            }
            html += '</table></div>';
            $('#tokenInfoContent').html(html);
        },
        error: function(xhr) {
            let message = 'Erro ao obter informações do token';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                message = xhr.responseJSON.error;
            }
            $('#tokenInfoContent').html('<div class="alert alert-danger">' + message + '</div>');
        }
    });
}

function generateTokens() {
    const userLogin = $('#generate_user_login').val();
    const userPassword = $('#generate_user_password').val();
    const storeCnpj = $('#generate_store_cnpj').val();
    const softwareHouseCnpj = $('#generate_software_house_cnpj').val();

    if (!userLogin || !userPassword || !storeCnpj || !softwareHouseCnpj) {
        alert('Por favor, preencha todos os campos necessários para gerar os tokens.');
        return;
    }

    $('#generateTokensModal').modal('show');
    $('#generateTokensContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Gerando tokens...</div>');

    $.ajax({
        url: '{{ route("plug4market.settings.generate-tokens") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            user_login: userLogin,
            user_password: userPassword,
            store_cnpj: storeCnpj,
            software_house_cnpj: softwareHouseCnpj
        },
        success: function(response) {
            if (response.success) {
                let html = '<div class="alert alert-success">';
                html += '<i class="fas fa-check-circle"></i> <strong>Sucesso!</strong><br>';
                html += response.message;
                if (response.user_info) {
                    html += '<br><br><strong>Informações do usuário:</strong><br>';
                    html += 'Nome: ' + (response.user_info.name || 'N/A') + '<br>';
                    html += 'ID: ' + (response.user_info.id || 'N/A');
                }
                html += '</div>';
                html += '<div class="alert alert-info">';
                html += '<i class="fas fa-info-circle"></i> Os tokens foram salvos automaticamente. Você pode agora testar a conexão.';
                html += '</div>';
                $('#generateTokensContent').html(html);
                
                // Recarregar a página após 3 segundos para mostrar os novos tokens
                setTimeout(function() {
                    location.reload();
                }, 3000);
            } else {
                $('#generateTokensContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + response.message + '</div>');
            }
        },
        error: function(xhr) {
            let message = 'Erro ao gerar tokens';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            $('#generateTokensContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + message + '</div>');
        }
    });
}
</script>
@endsection
