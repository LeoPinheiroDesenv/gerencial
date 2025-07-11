@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Novo Pedido Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.orders.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('plug4market.orders.store') }}" method="POST">
            @csrf
            
            <!-- Informações Básicas do Pedido -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="card-label">Informações Básicas</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="marketplace" class="form-label">Marketplace</label>
                                <input type="number" class="form-control" id="marketplace" name="marketplace" value="7" required>
                            </div>
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1">Pendente</option>
                                    <option value="2" selected>Confirmado</option>
                                    <option value="3">Enviado</option>
                                    <option value="4">Entregue</option>
                                    <option value="5">Cancelado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="type_billing" class="form-label">Tipo de Cobrança</label>
                                <select class="form-control" id="type_billing" name="type_billing" required>
                                    <option value="PF">Pessoa Física</option>
                                    <option value="PJ" selected>Pessoa Jurídica</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shipping_cost" class="form-label">Custo do Frete</label>
                                <input type="number" step="0.01" class="form-control" id="shipping_cost" name="shipping_cost" value="1" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_name" class="form-label">Nome do Frete</label>
                                <input type="text" class="form-control" id="shipping_name" name="shipping_name" value="SEDEX" required>
                            </div>
                            <div class="form-group">
                                <label for="payment_name" class="form-label">Nome do Pagamento</label>
                                <input type="text" class="form-control" id="payment_name" name="payment_name" value="Cartão Crédito" required>
                            </div>
                            <div class="form-group">
                                <label for="interest" class="form-label">Juros</label>
                                <input type="number" step="0.01" class="form-control" id="interest" name="interest" value="0">
                            </div>
                            <div class="form-group">
                                <label for="total_commission" class="form-label">Comissão Total</label>
                                <input type="number" step="0.01" class="form-control" id="total_commission" name="total_commission" value="1000">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seleção de Cliente -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="card-label">Cliente</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="cliente_id" class="form-label">Cliente Existente (Opcional)</label>
                        <select class="form-control" id="cliente_id" name="cliente_id">
                            <option value="">Selecione um cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" 
                                        data-nome="{{ $cliente->razao_social ?? $cliente->nome_fantasia }}"
                                        data-email="{{ $cliente->email }}"
                                        data-telefone="{{ $cliente->telefone }}"
                                        data-documento="{{ $cliente->cpf_cnpj }}"
                                        data-endereco="{{ $cliente->endereco }}"
                                        data-numero="{{ $cliente->numero }}"
                                        data-complemento="{{ $cliente->complemento }}"
                                        data-bairro="{{ $cliente->bairro }}"
                                        data-cidade="{{ $cliente->cidade }}"
                                        data-estado="{{ $cliente->estado }}"
                                        data-cep="{{ $cliente->cep }}">
                                    {{ $cliente->razao_social ?? $cliente->nome_fantasia }} - {{ $cliente->cpf_cnpj }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Dados de Entrega -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="card-label">Dados de Entrega</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shipping_recipient_name" class="form-label">Nome do Destinatário</label>
                                <input type="text" class="form-control" id="shipping_recipient_name" name="shipping_recipient_name" value="João da Silva (PEDIDO TESTE)" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="shipping_phone" name="shipping_phone" value="41999999999" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_street" class="form-label">Rua</label>
                                <input type="text" class="form-control" id="shipping_street" name="shipping_street" value="Rua Doutor Corrêa Coelho" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_street_number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="shipping_street_number" name="shipping_street_number" value="741" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_street_complement" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="shipping_street_complement" name="shipping_street_complement" value="Sala 4A">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shipping_district" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="shipping_district" name="shipping_district" value="Jardim Botânico" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" value="Curitiba" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_state" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state" value="PR" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_zip_code" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="shipping_zip_code" name="shipping_zip_code" value="80210350" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_ibge" class="form-label">Código IBGE</label>
                                <input type="text" class="form-control" id="shipping_ibge" name="shipping_ibge" value="4106902">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados de Cobrança -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="card-label">Dados de Cobrança</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="billing_name" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="billing_name" name="billing_name" value="João da Silva (PEDIDO TESTE)" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="billing_email" name="billing_email" value="537422410963@email.com" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_document_id" class="form-label">CPF/CNPJ</label>
                                <input type="text" class="form-control" id="billing_document_id" name="billing_document_id" value="24075890503" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="billing_phone" name="billing_phone" value="41999999999" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_street" class="form-label">Rua</label>
                                <input type="text" class="form-control" id="billing_street" name="billing_street" value="Rua Loefgren" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_street_number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="billing_street_number" name="billing_street_number" value="656" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_street_complement" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="billing_street_complement" name="billing_street_complement" value="AP 14">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="billing_district" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="billing_district" name="billing_district" value="Vila Clementino" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="billing_city" name="billing_city" value="São Paulo" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_state" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="billing_state" name="billing_state" value="SP" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_zip_code" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="billing_zip_code" name="billing_zip_code" value="04040000" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_ibge" class="form-label">Código IBGE</label>
                                <input type="text" class="form-control" id="billing_ibge" name="billing_ibge" value="3550308">
                            </div>
                            <div class="form-group">
                                <label for="billing_tax_payer" class="form-label">Contribuinte</label>
                                <select class="form-control" id="billing_tax_payer" name="billing_tax_payer">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nota Fiscal -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="card-label">Nota Fiscal</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_number" class="form-label">Número da Nota Fiscal</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" placeholder="Ex: 000001">
                            </div>
                            <div class="form-group">
                                <label for="invoice_key" class="form-label">Chave de Acesso</label>
                                <input type="text" class="form-control" id="invoice_key" name="invoice_key" placeholder="Ex: 35241234567890123456789012345678901234567890">
                            </div>
                            <div class="form-group">
                                <label for="invoice_date" class="form-label">Data da Nota Fiscal</label>
                                <input type="datetime-local" class="form-control" id="invoice_date" name="invoice_date">
                            </div>
                            <div class="form-group">
                                <label for="invoice_file" class="form-label">Anexar Nota Fiscal</label>
                                <input type="file" class="form-control-file" id="invoice_file" name="invoice_file" accept=".pdf,.xml,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Formatos aceitos: PDF, XML, JPG, JPEG, PNG</small>
                            </div>
                            <div class="form-group">
                                <label for="invoice_status" class="form-label">Status da Nota Fiscal</label>
                                <select class="form-control" id="invoice_status" name="invoice_status">
                                    <option value="">Selecione...</option>
                                    <option value="pending">Pendente</option>
                                    <option value="processing">Processando</option>
                                    <option value="approved">Aprovada</option>
                                    <option value="rejected">Rejeitada</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_series" class="form-label">Série da Nota</label>
                                <input type="text" class="form-control" id="invoice_series" name="invoice_series" placeholder="Ex: 1">
                            </div>
                            <div class="form-group">
                                <label for="invoice_model" class="form-label">Modelo da Nota</label>
                                <select class="form-control" id="invoice_model" name="invoice_model">
                                    <option value="">Selecione...</option>
                                    <option value="55">NFe - Nota Fiscal Eletrônica</option>
                                    <option value="65">NFCe - Nota Fiscal de Consumidor Eletrônica</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="invoice_environment" class="form-label">Ambiente</label>
                                <select class="form-control" id="invoice_environment" name="invoice_environment">
                                    <option value="">Selecione...</option>
                                    <option value="1">Produção</option>
                                    <option value="2">Homologação</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="invoice_protocol" class="form-label">Protocolo de Autorização</label>
                                <input type="text" class="form-control" id="invoice_protocol" name="invoice_protocol" placeholder="Ex: 123456789012345">
                            </div>
                            <div class="form-group">
                                <label for="invoice_protocol_date" class="form-label">Data do Protocolo</label>
                                <input type="datetime-local" class="form-control" id="invoice_protocol_date" name="invoice_protocol_date">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Valores da Nota Fiscal -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5>Valores da Nota Fiscal</h5>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="invoice_total_products" class="form-label">Total Produtos</label>
                                <input type="number" step="0.01" class="form-control" id="invoice_total_products" name="invoice_total_products" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="invoice_total_taxes" class="form-label">Total Impostos</label>
                                <input type="number" step="0.01" class="form-control" id="invoice_total_taxes" name="invoice_total_taxes" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="invoice_total_shipping" class="form-label">Total Frete</label>
                                <input type="number" step="0.01" class="form-control" id="invoice_total_shipping" name="invoice_total_shipping" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="invoice_total_discount" class="form-label">Total Desconto</label>
                                <input type="number" step="0.01" class="form-control" id="invoice_total_discount" name="invoice_total_discount" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="invoice_total_final" class="form-label">Total Final</label>
                                <input type="number" step="0.01" class="form-control" id="invoice_total_final" name="invoice_total_final" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produtos -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="card-label">Produtos</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="produtos-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>SKU</th>
                                    <th>Preço</th>
                                    <th>Quantidade</th>
                                    <th>Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="produto-row-1">
                                    <td>
                                        <select class="form-control produto-select" name="produtos[1][id]" onchange="selecionarProduto(this, 1)">
                                            <option value="">Selecione um produto...</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" 
                                                        data-sku="{{ $product->codigo }}"
                                                        data-preco="{{ $product->valor_unitario }}">
                                                    {{ $product->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="produtos[1][sku]" id="sku-1" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" name="produtos[1][preco]" id="preco-1" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="produtos[1][quantidade]" id="quantidade-1" value="1" min="1" onchange="calcularTotal(1)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" id="total-1" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(1)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success btn-sm" onclick="adicionarProduto()">
                            <i class="fas fa-plus"></i> Adicionar Produto
                        </button>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="card card-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Criar Pedido
                        </button>
                        <a href="{{ route('plug4market.orders.index') }}" class="btn btn-secondary btn-lg ml-3">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let produtoCounter = 1;

function adicionarProduto() {
    produtoCounter++;
    const tbody = document.querySelector('#produtos-table tbody');
    const newRow = document.createElement('tr');
    newRow.id = `produto-row-${produtoCounter}`;
    
    newRow.innerHTML = `
        <td>
            <select class="form-control produto-select" name="produtos[${produtoCounter}][id]" onchange="selecionarProduto(this, ${produtoCounter})">
                <option value="">Selecione um produto...</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" 
                            data-sku="{{ $product->codigo }}"
                            data-preco="{{ $product->valor_unitario }}">
                        {{ $product->descricao }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control" name="produtos[${produtoCounter}][sku]" id="sku-${produtoCounter}" readonly>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="produtos[${produtoCounter}][preco]" id="preco-${produtoCounter}" readonly>
        </td>
        <td>
            <input type="number" class="form-control" name="produtos[${produtoCounter}][quantidade]" id="quantidade-${produtoCounter}" value="1" min="1" onchange="calcularTotal(${produtoCounter})">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" id="total-${produtoCounter}" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(${produtoCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
}

function removerProduto(id) {
    const row = document.getElementById(`produto-row-${id}`);
    if (row) {
        row.remove();
    }
}

function selecionarProduto(select, id) {
    const option = select.options[select.selectedIndex];
    const skuInput = document.getElementById(`sku-${id}`);
    const precoInput = document.getElementById(`preco-${id}`);
    
    if (option.value) {
        skuInput.value = option.dataset.sku;
        precoInput.value = option.dataset.preco;
        calcularTotal(id);
    } else {
        skuInput.value = '';
        precoInput.value = '';
        document.getElementById(`total-${id}`).value = '';
    }
}

function calcularTotal(id) {
    const preco = parseFloat(document.getElementById(`preco-${id}`).value) || 0;
    const quantidade = parseInt(document.getElementById(`quantidade-${id}`).value) || 0;
    const total = preco * quantidade;
    document.getElementById(`total-${id}`).value = total.toFixed(2);
}

// Auto-preenchimento de dados do cliente
document.getElementById('cliente_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        // Preencher dados de cobrança
        document.getElementById('billing_name').value = option.dataset.nome;
        document.getElementById('billing_email').value = option.dataset.email;
        document.getElementById('billing_phone').value = option.dataset.telefone;
        document.getElementById('billing_document_id').value = option.dataset.documento;
        document.getElementById('billing_street').value = option.dataset.endereco;
        document.getElementById('billing_street_number').value = option.dataset.numero;
        document.getElementById('billing_street_complement').value = option.dataset.complemento;
        document.getElementById('billing_district').value = option.dataset.bairro;
        document.getElementById('billing_city').value = option.dataset.cidade;
        document.getElementById('billing_state').value = option.dataset.estado;
        document.getElementById('billing_zip_code').value = option.dataset.cep;
        
        // Preencher dados de entrega
        document.getElementById('shipping_recipient_name').value = option.dataset.nome;
        document.getElementById('shipping_phone').value = option.dataset.telefone;
        document.getElementById('shipping_street').value = option.dataset.endereco;
        document.getElementById('shipping_street_number').value = option.dataset.numero;
        document.getElementById('shipping_street_complement').value = option.dataset.complemento;
        document.getElementById('shipping_district').value = option.dataset.bairro;
        document.getElementById('shipping_city').value = option.dataset.cidade;
        document.getElementById('shipping_state').value = option.dataset.estado;
        document.getElementById('shipping_zip_code').value = option.dataset.cep;
    }
});
</script>

@endsection 