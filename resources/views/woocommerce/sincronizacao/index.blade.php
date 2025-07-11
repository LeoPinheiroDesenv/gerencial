@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="col-12">
            <h3 class="card-title">Sincronização com WooCommerce</h3>
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

        <!-- Componente de Progresso -->
        <div id="sync-progress" class="d-none">
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <h3 class="card-title">Progresso da Sincronização</h3>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 0%" 
                             id="progress-bar">0%</div>
                    </div>
                    <div class="sync-status">
                        <p class="mb-2">Status: <span id="sync-status-text">Aguardando início...</span></p>
                        <p class="mb-2">Itens processados: <span id="sync-items-count">0</span></p>
                        <p class="mb-2">Total de itens: <span id="sync-total-items">0</span></p>
                    </div>
                    <div class="sync-log mt-3">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Log de Sincronização</h3>
                            </div>
                            <div class="card-body">
                                <div id="sync-log-content" style="height: 200px; overflow-y: auto;">
                                    <!-- Logs serão inseridos aqui -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Opções de Sincronização</h3>
                </div>

                <form method="post" action="{{ route('woocommerce-sincronizacao.executar') }}" id="sync-form">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Sincronização</label>
                                    <select name="tipo" class="form-control">
                                        <option value="produtos">Apenas Produtos</option>
                                        <option value="pedidos">Apenas Pedidos</option>
                                        <option value="tudo">Produtos e Pedidos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Direção da Sincronização</label>
                                    <select name="direcao" class="form-control">
                                        <option value="export">Exportar para WooCommerce</option>
                                        <option value="import">Importar do WooCommerce</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="sync-button">
                                    <i class="fa fa-sync"></i> Iniciar Sincronização
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sync-form');
    const progressDiv = document.getElementById('sync-progress');
    const progressBar = document.getElementById('progress-bar');
    const statusText = document.getElementById('sync-status-text');
    const itemsCount = document.getElementById('sync-items-count');
    const totalItems = document.getElementById('sync-total-items');
    const logContent = document.getElementById('sync-log-content');
    const syncButton = document.getElementById('sync-button');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostra o componente de progresso
        progressDiv.classList.remove('d-none');
        
        // Desabilita o botão
        syncButton.disabled = true;
        
        // Limpa o log anterior
        logContent.innerHTML = '';
        
        // Obtém os dados do formulário
        const formData = new FormData(form);
        
        // Inicia a sincronização
        startSync(formData);
    });

    function startSync(formData) {
        // Adiciona o primeiro log
        addLog('Iniciando sincronização...');
        
        // Faz a requisição AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Inicia o polling para atualizar o progresso
                pollProgress();
            } else {
                addLog('Erro: ' + data.message, 'error');
                syncButton.disabled = false;
            }
        })
        .catch(error => {
            addLog('Erro na requisição: ' + error.message, 'error');
            syncButton.disabled = false;
        });
    }

    function pollProgress() {
        fetch('/woocommerce/sincronizacao/progresso')
            .then(response => response.json())
            .then(data => {
                updateProgress(data);
                
                if (data.status === 'completed') {
                    addLog('Sincronização concluída com sucesso!', 'success');
                    syncButton.disabled = false;
                } else if (data.status === 'error') {
                    addLog('Erro na sincronização: ' + data.message, 'error');
                    syncButton.disabled = false;
                } else {
                    // Continua o polling
                    setTimeout(pollProgress, 1000);
                }
            })
            .catch(error => {
                addLog('Erro ao verificar progresso: ' + error.message, 'error');
                syncButton.disabled = false;
            });
    }

    function updateProgress(data) {
        // Atualiza a barra de progresso
        const progress = data.progress || 0;
        progressBar.style.width = progress + '%';
        progressBar.textContent = progress + '%';
        
        // Atualiza os contadores
        statusText.textContent = data.status_text || 'Processando...';
        itemsCount.textContent = data.items_processed || 0;
        totalItems.textContent = data.total_items || 0;
        
        // Adiciona novos logs
        if (data.logs && data.logs.length > 0) {
            data.logs.forEach(log => {
                addLog(log.message, log.type);
            });
        }
    }

    function addLog(message, type = 'info') {
        const logEntry = document.createElement('div');
        logEntry.className = 'log-entry ' + type;
        logEntry.textContent = new Date().toLocaleTimeString() + ' - ' + message;
        logContent.appendChild(logEntry);
        logContent.scrollTop = logContent.scrollHeight;
    }
});
</script>

<style>
.log-entry {
    padding: 5px;
    border-bottom: 1px solid #eee;
}

.log-entry.error {
    color: #dc3545;
}

.log-entry.success {
    color: #28a745;
}

.log-entry.info {
    color: #17a2b8;
}

#sync-log-content {
    font-family: monospace;
    font-size: 12px;
    background-color: #f8f9fa;
    padding: 10px;
}
</style>
@endsection
