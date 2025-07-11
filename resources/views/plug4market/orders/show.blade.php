@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Detalhes do Pedido Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.orders.edit', $order->id) }}" class="btn btn-warning btn-lg">
                <i class="fa fa-edit"></i> Editar
            </a>
            <a href="{{ route('plug4market.orders.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <!-- Informações Básicas -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Informações do Pedido</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">ID:</label>
                                    <p class="form-control-static">{{ $order->id }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Número do Pedido:</label>
                                    <p class="form-control-static">
                                        <strong>{{ $order->order_number ?? $order->external_id ?? 'N/A' }}</strong>
                                        @if($order->external_id)
                                            <br><small class="text-muted">API ID: {{ $order->external_id }}</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Marketplace:</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-light">{{ $order->marketplace }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Status:</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-{{ $order->status_badge }}">
                                            {{ $order->status_text }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Tipo de Cobrança:</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-{{ $order->type_billing == 'PJ' ? 'info' : 'secondary' }}">
                                            {{ $order->type_billing_text }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label font-weight-bold">Data de Criação:</label>
                            <p class="form-control-static">{{ $order->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informações Financeiras -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Informações Financeiras</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Valor Total:</label>
                                    <p class="form-control-static">
                                        <strong class="text-success">{{ $order->formatted_total_amount }}</strong>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Custo do Frete:</label>
                                    <p class="form-control-static">{{ $order->formatted_shipping_cost }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Nome do Frete:</label>
                                    <p class="form-control-static">{{ $order->shipping_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Nome do Pagamento:</label>
                                    <p class="form-control-static">{{ $order->payment_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Juros:</label>
                                    <p class="form-control-static">R$ {{ number_format($order->interest, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label font-weight-bold">Comissão Total:</label>
                            <p class="form-control-static">{{ $order->formatted_total_commission }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cliente -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Cliente</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($order->cliente)
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle"></i>
                                <strong>Cliente Vinculado:</strong> {{ $order->cliente->razao_social ?? $order->cliente->nome_fantasia }}
                                @if($order->cliente->cpf_cnpj)
                                    <br><small>CPF/CNPJ: {{ $order->cliente->cpf_cnpj }}</small>
                                @endif
                                @if($order->cliente->email)
                                    <br><small>Email: {{ $order->cliente->email }}</small>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Cliente não vinculado</strong>
                                <br><small>{{ $order->billing_name ?? 'Cliente não informado' }}</small>
                            </div>
                        @endif
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
                                    <label class="form-label font-weight-bold">Destinatário:</label>
                                    <p class="form-control-static">{{ $order->shipping_recipient_name ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Telefone:</label>
                                    <p class="form-control-static">{{ $order->shipping_phone ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Rua:</label>
                                    <p class="form-control-static">{{ $order->shipping_street ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Número:</label>
                                    <p class="form-control-static">{{ $order->shipping_street_number ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Complemento:</label>
                                    <p class="form-control-static">{{ $order->shipping_street_complement ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Bairro:</label>
                                    <p class="form-control-static">{{ $order->shipping_district ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Cidade:</label>
                                    <p class="form-control-static">{{ $order->shipping_city ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Estado:</label>
                                    <p class="form-control-static">{{ $order->shipping_state ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">CEP:</label>
                                    <p class="form-control-static">{{ $order->shipping_zip_code ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Código IBGE:</label>
                                    <p class="form-control-static">{{ $order->shipping_ibge ?? 'N/A' }}</p>
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
                                    <label class="form-label font-weight-bold">Nome:</label>
                                    <p class="form-control-static">{{ $order->billing_name ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Email:</label>
                                    <p class="form-control-static">{{ $order->billing_email ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">CPF/CNPJ:</label>
                                    <p class="form-control-static">{{ $order->billing_document_id ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Telefone:</label>
                                    <p class="form-control-static">{{ $order->billing_phone ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Rua:</label>
                                    <p class="form-control-static">{{ $order->billing_street ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Número:</label>
                                    <p class="form-control-static">{{ $order->billing_street_number ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Complemento:</label>
                                    <p class="form-control-static">{{ $order->billing_street_complement ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Bairro:</label>
                                    <p class="form-control-static">{{ $order->billing_district ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Cidade:</label>
                                    <p class="form-control-static">{{ $order->billing_city ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Estado:</label>
                                    <p class="form-control-static">{{ $order->billing_state ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">CEP:</label>
                                    <p class="form-control-static">{{ $order->billing_zip_code ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Código IBGE:</label>
                                    <p class="form-control-static">{{ $order->billing_ibge ?? 'N/A' }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Contribuinte:</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-{{ $order->billing_tax_payer ? 'success' : 'secondary' }}">
                                            {{ $order->billing_tax_payer ? 'Sim' : 'Não' }}
                                        </span>
                                    </p>
                                </div>
                                @if($order->billing_date_of_birth)
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Data de Nascimento:</label>
                                        <p class="form-control-static">{{ $order->billing_date_of_birth->format('d/m/Y') }}</p>
                                    </div>
                                @endif
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
                        @if($order->hasInvoice())
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Número da Nota:</label>
                                        <p class="form-control-static">{{ $order->invoice_number }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Chave de Acesso:</label>
                                        <p class="form-control-static">{{ $order->invoice_key }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Data da Nota:</label>
                                        <p class="form-control-static">{{ $order->invoice_date ? $order->invoice_date->format('d/m/Y H:i') : 'N/A' }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Status:</label>
                                        <p class="form-control-static">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'cancelled' => 'secondary'
                                                ];
                                                $statusText = [
                                                    'pending' => 'Pendente',
                                                    'processing' => 'Processando',
                                                    'approved' => 'Aprovada',
                                                    'rejected' => 'Rejeitada',
                                                    'cancelled' => 'Cancelada'
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$order->invoice_status] ?? 'secondary' }}">
                                                {{ $statusText[$order->invoice_status] ?? $order->invoice_status }}
                                            </span>
                                        </p>
                                    </div>
                                    @if($order->invoice_url)
                                        <div class="form-group">
                                            <label class="form-label font-weight-bold">URL da Nota:</label>
                                            <p class="form-control-static">
                                                <a href="{{ $order->invoice_url }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-external-link-alt"></i> Abrir Nota
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                    @if($order->hasInvoiceFile())
                                        <div class="form-group">
                                            <label class="form-label font-weight-bold">Arquivo da Nota:</label>
                                            <p class="form-control-static">
                                                <a href="{{ route('plug4market.orders.download-invoice-file', $order->id) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i> {{ $order->invoice_file_name }}
                                                </a>
                                                <br><small class="text-muted">
                                                    Tipo: {{ $order->invoice_file_type_text }} | 
                                                    Tamanho: {{ $order->invoice_file_size_formatted }} | 
                                                    Upload: {{ $order->invoice_file_uploaded_at->format('d/m/Y H:i') }}
                                                </small>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Série:</label>
                                        <p class="form-control-static">{{ $order->invoice_series ?? 'N/A' }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Modelo:</label>
                                        <p class="form-control-static">
                                            @if($order->invoice_model == '55')
                                                NFe - Nota Fiscal Eletrônica
                                            @elseif($order->invoice_model == '65')
                                                NFCe - Nota Fiscal de Consumidor Eletrônica
                                            @else
                                                {{ $order->invoice_model ?? 'N/A' }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Ambiente:</label>
                                        <p class="form-control-static">
                                            @if($order->invoice_environment == '1')
                                                Produção
                                            @elseif($order->invoice_environment == '2')
                                                Homologação
                                            @else
                                                {{ $order->invoice_environment ?? 'N/A' }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Protocolo:</label>
                                        <p class="form-control-static">{{ $order->invoice_protocol ?? 'N/A' }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Data do Protocolo:</label>
                                        <p class="form-control-static">{{ $order->invoice_protocol_date ? $order->invoice_protocol_date->format('d/m/Y H:i') : 'N/A' }}</p>
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
                                        <label class="form-label font-weight-bold">Total Produtos:</label>
                                        <p class="form-control-static">R$ {{ number_format($order->invoice_total_products ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Total Impostos:</label>
                                        <p class="form-control-static">R$ {{ number_format($order->invoice_total_taxes ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Total Frete:</label>
                                        <p class="form-control-static">R$ {{ number_format($order->invoice_total_shipping ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Total Desconto:</label>
                                        <p class="form-control-static">R$ {{ number_format($order->invoice_total_discount ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">Total Final:</label>
                                        <p class="form-control-static">R$ {{ number_format($order->invoice_total_final ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- XML da Nota Fiscal -->
                            @if($order->hasInvoiceXml())
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h5>XML da Nota Fiscal</h5>
                                        <div class="alert alert-info">
                                            <i class="fas fa-file-code"></i>
                                            <strong>XML Disponível</strong>
                                            <br><small>Baixado em: {{ $order->invoice_xml_downloaded_at->format('d/m/Y H:i') }}</small>
                                            <br><small>Arquivo: {{ $order->invoice_xml_filename }}</small>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('plug4market.orders.download-xml', $order->id) }}" class="btn btn-primary">
                                                <i class="fas fa-download"></i> Baixar XML
                                            </a>
                                            <a href="{{ route('plug4market.orders.view-xml', $order->id) }}" class="btn btn-info" target="_blank">
                                                <i class="fas fa-eye"></i> Visualizar XML
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h5>XML da Nota Fiscal</h5>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>XML não disponível</strong>
                                            <br><small>O XML da nota fiscal ainda não foi baixado</small>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('plug4market.orders.process-xml', $order->id) }}" class="btn btn-success">
                                                <i class="fas fa-download"></i> Baixar XML da API
                                            </a>
                                            <button type="button" class="btn btn-info" onclick="checkInvoiceAvailability({{ $order->id }})">
                                                <i class="fas fa-search"></i> Verificar Disponibilidade
                                            </button>
                                            <!-- <button type="button" class="btn btn-warning" onclick="testModal()">
                                                <i class="fas fa-bug"></i> Teste Modal
                                            </button> -->
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Nota Fiscal não vinculada</strong>
                                <br><small>Este pedido ainda não possui uma nota fiscal vinculada.</small>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary" onclick="importInvoice({{ $order->id }})">
                                    <i class="fas fa-file-invoice"></i> Importar Nota Fiscal
                                </button>
                                <button type="button" class="btn btn-success" onclick="importInvoiceWithXml({{ $order->id }})">
                                    <i class="fas fa-file-code"></i> Importar com XML
                                </button>
                            </div>
                        @endif
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
                        @if($order->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>SKU</th>
                                            <th>Preço Unitário</th>
                                            <th>Quantidade</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>{{ $item->product_name }}</td>
                                                <td>{{ $item->sku }}</td>
                                                <td>{{ $item->formatted_price }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->formatted_total_price }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Nenhum produto encontrado para este pedido.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status de Sincronização -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Sincronização com API</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($order->sincronizado)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Sincronizado</strong>
                                @if($order->external_id)
                                    <br><small>ID na API: {{ $order->external_id }}</small>
                                @endif
                                @if($order->ultima_sincronizacao)
                                    <br><small>Última sincronização: {{ $order->ultima_sincronizacao->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Não Sincronizado</strong>
                                <br><small>Este pedido não foi sincronizado com a API Plug4Market</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Ações</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('plug4market.orders.edit', $order->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Pedido
                            </a>
                            <form action="{{ route('plug4market.orders.destroy', $order->id) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Tem certeza que deseja excluir este pedido?')">
                                    <i class="fas fa-trash"></i> Excluir Pedido
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Dados da API -->
                @if($order->raw_data)
                    <div class="card card-custom">
                        <div class="card-header">
                            <div class="card-title">
                                <h4 class="card-label">Dados da API</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Resposta da API:</label>
                                <div class="bg-light p-3 rounded">
                                    <pre class="mb-0 small">{{ json_encode($order->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar Nota Fiscal -->
<div class="modal fade" id="importInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="importInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importInvoiceModalLabel">
                    <i class="fas fa-file-invoice"></i> Importar Nota Fiscal
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="importInvoiceContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Carregando...</span>
                        </div>
                        <p class="mt-2">Importando nota fiscal...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="refreshPageBtn" style="display: none;" onclick="location.reload()">
                    <i class="fas fa-refresh"></i> Atualizar Página
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar Nota Fiscal com XML -->
<div class="modal fade" id="importInvoiceWithXmlModal" tabindex="-1" role="dialog" aria-labelledby="importInvoiceWithXmlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importInvoiceWithXmlModalLabel">
                    <i class="fas fa-file-code"></i> Importar Nota Fiscal com XML
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="importInvoiceWithXmlContent">
                    <div class="text-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="sr-only">Carregando...</span>
                        </div>
                        <p class="mt-2">Importando nota fiscal e XML...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="refreshPageWithXmlBtn" style="display: none;" onclick="location.reload()">
                    <i class="fas fa-refresh"></i> Atualizar Página
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verificar Disponibilidade -->
<div class="modal fade" id="checkInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="checkInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkInvoiceModalLabel">
                    <i class="fas fa-search"></i> Verificar Disponibilidade da Nota Fiscal
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="checkInvoiceContent">
                    <div class="text-center">
                        <div class="spinner-border text-info" role="status">
                            <span class="sr-only">Carregando...</span>
                        </div>
                        <p class="mt-2">Verificando disponibilidade...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Verificar se jQuery está disponível
// if (typeof $ === 'undefined') {
//     console.error('jQuery não está disponível. Os modais de importação não funcionarão.');
//     alert('Erro: jQuery não está carregado. Os modais não funcionarão.');
// } else {
//     console.log('jQuery está disponível. Modais funcionando.');
//     // Verificar se Bootstrap está disponível
//     if (typeof $.fn.modal === 'undefined') {
//         console.error('Bootstrap modal não está disponível!');
//         alert('Erro: Bootstrap não está carregado. Os modais não funcionarão.');
//     } else {
//         console.log('Bootstrap modal está disponível.');
//     }
// }

function importInvoice(orderId) {
    console.log('Abrindo modal de importação para pedido:', orderId);
    $('#importInvoiceModal').modal('show');
    $.ajax({
        url: '{{ route("plug4market.orders.import-invoice", ":id") }}'.replace(':id', orderId),
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Resposta da importação:', response);
            if (response.success) {
                $('#importInvoiceContent').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Sucesso!</strong> ${response.message}
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h6>Dados da Nota Fiscal</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número:</strong> ${response.data.number || 'N/A'}</p>
                                    <p><strong>Chave:</strong> ${response.data.key || 'N/A'}</p>
                                    <p><strong>Data:</strong> ${response.data.date || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> ${response.data.status || 'N/A'}</p>
                                    <p><strong>URL:</strong> ${response.data.url ? '<a href="' + response.data.url + '" target="_blank">Abrir</a>' : 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $('#refreshPageBtn').show();
            } else {
                $('#importInvoiceContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erro!</strong> ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na importação:', xhr, status, error);
            let errorMessage = 'Erro ao importar nota fiscal';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#importInvoiceContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Erro!</strong> ${errorMessage}
                </div>
            `);
        }
    });
    return false;
}

function importInvoiceWithXml(orderId) {
    console.log('Abrindo modal de importação com XML para pedido:', orderId);
    $('#importInvoiceWithXmlModal').modal('show');
    $.ajax({
        url: '{{ route("plug4market.orders.import-invoice-with-xml", ":id") }}'.replace(':id', orderId),
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Resposta da importação com XML:', response);
            if (response.success) {
                $('#importInvoiceWithXmlContent').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Sucesso!</strong> ${response.message}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Dados da Nota Fiscal</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Número:</strong> ${response.invoice_data.number || 'N/A'}</p>
                                    <p><strong>Chave:</strong> ${response.invoice_data.key || 'N/A'}</p>
                                    <p><strong>Status:</strong> ${response.invoice_data.status || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Dados do XML</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Status:</strong> ${response.xml_data.success ? 'Baixado com sucesso' : 'Erro'}</p>
                                    <p><strong>Arquivo:</strong> ${response.xml_data.filename || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $('#refreshPageWithXmlBtn').show();
            } else {
                $('#importInvoiceWithXmlContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erro!</strong> ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na importação com XML:', xhr, status, error);
            let errorMessage = 'Erro ao importar nota fiscal e XML';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#importInvoiceWithXmlContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Erro!</strong> ${errorMessage}
                </div>
            `);
        }
    });
    return false;
}

function checkInvoiceAvailability(orderId) {
    console.log('Abrindo modal de verificação para pedido:', orderId);
    // Abrir o modal
    $('#checkInvoiceModal').modal('show');
    // Fazer a requisição AJAX
    $.ajax({
        url: '{{ route("plug4market.orders.check-invoice", ":id") }}'.replace(':id', orderId),
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#checkInvoiceContent').html(`
                <div class="text-center">
                    <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Carregando...</span>
                    </div>
                    <p class="mt-2">Verificando disponibilidade...</p>
                </div>
            `);
        },
        success: function(response) {
            console.log('Resposta da verificação:', response);
            if (response.success) {
                let content = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Verificação Concluída</strong>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Status da Nota Fiscal</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nota Fiscal:</strong> 
                                        <span class="badge badge-${response.has_invoice ? 'success' : 'warning'}">
                                            ${response.has_invoice ? 'Disponível' : 'Não disponível'}
                                        </span>
                                    </p>
                                    <p><strong>XML:</strong> 
                                        <span class="badge badge-${response.xml_available ? 'success' : 'warning'}">
                                            ${response.xml_available ? 'Disponível' : 'Não disponível'}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                `;
                if (response.invoice_data) {
                    content += `
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Dados da Nota</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Número:</strong> ${response.invoice_data.number || 'N/A'}</p>
                                    <p><strong>Chave:</strong> ${response.invoice_data.key || 'N/A'}</p>
                                    <p><strong>Status:</strong> ${response.invoice_data.status || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                content += `</div>`;
                if (response.has_invoice && !response.xml_available) {
                    content += `
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" onclick="importInvoiceWithXml(${orderId}); $('#checkInvoiceModal').modal('hide');">
                                <i class="fas fa-download"></i> Importar Nota Fiscal e XML
                            </button>
                        </div>
                    `;
                }
                $('#checkInvoiceContent').html(content);
            } else {
                $('#checkInvoiceContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erro!</strong> ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na verificação:', xhr, status, error);
            let errorMessage = 'Erro ao verificar disponibilidade';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#checkInvoiceContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Erro!</strong> ${errorMessage}
                    <br><small>Status: ${status} | Erro: ${error}</small>
                </div>
            `);
        }
    });
}

// Função de teste para verificar se o modal funciona
function testModal() {
    console.log('Testando modal...');
    $('#checkInvoiceModal').modal('show');
    $('#checkInvoiceContent').html(`
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <strong>Teste bem-sucedido!</strong> O modal está funcionando corretamente.
        </div>
    `);
}
</script>

@endsection 