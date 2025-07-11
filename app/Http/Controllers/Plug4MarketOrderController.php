<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Plug4MarketService;

class Plug4MarketOrderController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new Plug4MarketService();
    }

    public function index()
    {
        // Listar pedidos Plug4Market
        $title = 'Pedidos Plug4Market';
        
        // Buscar pedidos locais com relacionamentos
        $localOrders = \App\Models\Plug4MarketOrder::with(['cliente', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('plug4market.orders.index', compact('title', 'localOrders'));
    }

    public function create()
    {
        // Formulário de novo pedido
        $title = 'Novo Pedido Plug4Market';
        
        // Buscar produtos ativos e sincronizados
        $products = \App\Models\Plug4MarketProduct::ativos()->sincronizados()->get(['id', 'external_id', 'codigo', 'descricao', 'valor_unitario']);
        
        // Buscar clientes existentes no sistema
        $clientes = \App\Models\Cliente::where('empresa_id', session('user_logged')['empresa'])
            ->where('inativo', false)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'nome_fantasia', 'cpf_cnpj', 'email', 'telefone', 'rua', 'numero', 'complemento', 'bairro', 'cidade_id', 'cep']);
        
        return view('plug4market.orders.create', compact('title', 'products', 'clientes'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $startTime = microtime(true);
        
        // Processar produtos selecionados
        $produtosSelecionados = [];
        if (!empty($data['produtos']) && is_array($data['produtos'])) {
            foreach ($data['produtos'] as $productId => $productData) {
                if (isset($productData['id']) && !empty($productData['id']) && isset($productData['quantidade'])) {
                    // Buscar informações do produto no banco
                    $product = \App\Models\Plug4MarketProduct::find($productData['id']);
                    
                    $produtosSelecionados[$productId] = [
                        'id' => $productData['id'],
                        'sku' => $product ? $product->codigo : $productData['id'],
                        'quantidade' => (int) $productData['quantidade'],
                        'preco' => $product ? $product->valor_unitario : 0
                    ];
                }
            }
        }
        
        // Substituir produtos no array de dados
        $data['produtos'] = $produtosSelecionados;
        
        // Calcular valor total dos produtos
        $totalAmount = 0;
        foreach ($produtosSelecionados as $productData) {
            $totalAmount += $productData['preco'] * $productData['quantidade'];
        }
        
        try {
            $result = $this->service->createOrder($data);
            
            // Se a API retornar sucesso e um ID, salvar localmente
            if (isset($result['id']) || isset($result['external_id'])) {
                $order = $this->createLocalOrder($data, $result, $produtosSelecionados, $totalAmount, $request);
                
                $executionTime = round((microtime(true) - $startTime) * 1000);
                \App\Models\Plug4MarketLog::create([
                    'action' => 'create_order',
                    'status' => 'success',
                    'message' => 'Pedido cadastrado e sincronizado com sucesso',
                    'details' => [
                        'pedido' => $order->toArray(),
                        'api_response' => $result
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                if (!empty($order->id)) {
                    return redirect()->route('plug4market.orders.show', ['order' => $order->id])->with('success', 'Pedido criado com sucesso!');
                } else {
                    return redirect()->route('plug4market.orders.index')->with('error', 'Pedido criado, mas ID não encontrado para exibição.');
                }
            } else {
                // API falhou, mas vamos salvar localmente mesmo assim
                $order = $this->createLocalOrder($data, null, $produtosSelecionados, $totalAmount, $request);
                
                $executionTime = round((microtime(true) - $startTime) * 1000);
                \App\Models\Plug4MarketLog::create([
                    'action' => 'create_order',
                    'status' => 'warning',
                    'message' => 'Pedido salvo localmente, mas falha na API',
                    'details' => [
                        'pedido' => $order->toArray(),
                        'api_response' => $result,
                        'error_message' => $result['error_messages'][0]['message'] ?? 'Erro desconhecido'
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                return redirect()->route('plug4market.orders.show', ['order' => $order->id])
                    ->with('warning', 'Pedido criado localmente, mas houve erro na API: ' . ($result['error_messages'][0]['message'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            // Mesmo com erro na API, vamos salvar o pedido localmente
            $order = $this->createLocalOrder($data, null, $produtosSelecionados, $totalAmount, $request);
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            \App\Models\Plug4MarketLog::create([
                'action' => 'create_order',
                'status' => 'error',
                'message' => 'Erro na API, mas pedido salvo localmente: ' . $e->getMessage(),
                'details' => [
                    'pedido' => $order->toArray(),
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return redirect()->route('plug4market.orders.show', ['order' => $order->id])
                ->with('warning', 'Pedido criado localmente, mas houve erro na API: ' . $e->getMessage());
        }
    }

    /**
     * Criar pedido localmente no banco de dados
     */
    private function createLocalOrder($data, $apiResult, $produtosSelecionados, $totalAmount, $request)
    {
        $order = new \App\Models\Plug4MarketOrder();
        
        // Se temos resposta da API, usar os dados dela
        if ($apiResult) {
            $order->external_id = $apiResult['id'] ?? $apiResult['external_id'];
            $order->order_number = $apiResult['numero'] ?? ($apiResult['order_number'] ?? null);
            $order->total_amount = $apiResult['total_amount'] ?? $totalAmount;
            $order->sincronizado = true;
            $order->raw_data = $apiResult;
        } else {
            // Gerar ID temporário para pedidos não sincronizados
            $order->external_id = 'TEMP_' . time() . '_' . rand(1000, 9999);
            $order->order_number = 'PED_' . date('YmdHis') . '_' . rand(100, 999);
            $order->total_amount = $totalAmount;
            $order->sincronizado = false;
            $order->raw_data = ['error' => 'Pedido criado localmente devido a erro na API'];
        }
        
        // Dados básicos do pedido
        $order->marketplace = $data['marketplace'] ?? 7;
        $order->status = $data['status'] ?? 2;
        $order->shipping_cost = $data['shipping_cost'] ?? 1;
        $order->shipping_name = $data['shipping_name'] ?? 'SEDEX';
        $order->payment_name = $data['payment_name'] ?? 'Cartão Crédito';
        $order->interest = $data['interest'] ?? 0;
        $order->total_commission = $data['total_commission'] ?? 1000;
        $order->type_billing = $data['type_billing'] ?? 'PJ';
        
        // Dados de entrega
        $order->shipping_recipient_name = $data['shipping_recipient_name'] ?? 'João da Silva (PEDIDO TESTE)';
        $order->shipping_phone = $data['shipping_phone'] ?? '41999999999';
        $order->shipping_street = $data['shipping_street'] ?? 'Rua Doutor Corrêa Coelho';
        $order->shipping_street_number = $data['shipping_street_number'] ?? '741';
        $order->shipping_city = $data['shipping_city'] ?? 'Curitiba';
        $order->shipping_street_complement = $data['shipping_street_complement'] ?? 'Sala 4A';
        $order->shipping_country = $data['shipping_country'] ?? 'BR';
        $order->shipping_district = $data['shipping_district'] ?? 'Jardim Botânico';
        $order->shipping_state = $data['shipping_state'] ?? 'PR';
        $order->shipping_zip_code = $data['shipping_zip_code'] ?? '80210350';
        $order->shipping_ibge = $data['shipping_ibge'] ?? '4106902';
        
        // Dados de cobrança
        $order->billing_name = $data['billing_name'] ?? 'João da Silva (PEDIDO TESTE)';
        $order->billing_email = $data['billing_email'] ?? '537422410963@email.com';
        $order->billing_document_id = $data['billing_document_id'] ?? '24075890503';
        $order->billing_phone = $data['billing_phone'] ?? '41999999999';
        $order->billing_street = $data['billing_street'] ?? 'Rua Loefgren';
        $order->billing_street_number = $data['billing_street_number'] ?? '741';
        $order->billing_street_complement = $data['billing_street_complement'] ?? 'Sala 4A';
        $order->billing_district = $data['billing_district'] ?? 'Jardim Botânico';
        $order->billing_city = $data['billing_city'] ?? 'Curitiba';
        $order->billing_state = $data['billing_state'] ?? 'PR';
        $order->billing_country = $data['billing_country'] ?? 'BR';
        $order->billing_zip_code = $data['billing_zip_code'] ?? '80210350';
        $order->billing_ibge = $data['billing_ibge'] ?? '4106902';
        $order->billing_tax_payer = $data['billing_tax_payer'] ?? false;
        
        // Dados de nota fiscal
        $order->invoice_number = $data['invoice_number'] ?? null;
        $order->invoice_key = $data['invoice_key'] ?? null;
        $order->invoice_date = $data['invoice_date'] ? \Carbon\Carbon::parse($data['invoice_date']) : null;
        $order->invoice_url = $data['invoice_url'] ?? null;
        $order->invoice_status = $data['invoice_status'] ?? null;
        $order->invoice_series = $data['invoice_series'] ?? null;
        $order->invoice_model = $data['invoice_model'] ?? null;
        $order->invoice_environment = $data['invoice_environment'] ?? null;
        $order->invoice_protocol = $data['invoice_protocol'] ?? null;
        $order->invoice_protocol_date = $data['invoice_protocol_date'] ? \Carbon\Carbon::parse($data['invoice_protocol_date']) : null;
        $order->invoice_total_products = $data['invoice_total_products'] ?? null;
        $order->invoice_total_taxes = $data['invoice_total_taxes'] ?? null;
        $order->invoice_total_shipping = $data['invoice_total_shipping'] ?? null;
        $order->invoice_total_discount = $data['invoice_total_discount'] ?? null;
        $order->invoice_total_final = $data['invoice_total_final'] ?? null;
        
        // Processar upload do arquivo da nota fiscal
        if ($request->hasFile('invoice_file') && $request->file('invoice_file')->isValid()) {
            $file = $request->file('invoice_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('invoices', $fileName, 'public');
            
            $order->invoice_file_name = $file->getClientOriginalName();
            $order->invoice_file_path = $filePath;
            $order->invoice_file_size = $file->getSize();
            $order->invoice_file_type = $file->getMimeType();
            $order->invoice_file_uploaded_at = now();
        }
        
        // Vincular cliente se selecionado
        if (!empty($data['cliente_id'])) {
            $order->cliente_id = $data['cliente_id'];
        }
        
        $order->ultima_sincronizacao = now();
        
        $order->save();
        
        // Salvar itens do pedido
        if (!empty($produtosSelecionados)) {
            foreach ($produtosSelecionados as $productData) {
                $product = \App\Models\Plug4MarketProduct::find($productData['id']);
                $order->items()->create([
                    'sku' => $productData['sku'],
                    'quantity' => $productData['quantidade'],
                    'price' => $productData['preco'],
                    'total_price' => $productData['preco'] * $productData['quantidade'],
                    'product_id' => $productData['id']
                ]);
            }
        }
        
        return $order;
    }

    public function show($id)
    {
        // Detalhes do pedido
        $title = 'Detalhes do Pedido Plug4Market';
        $order = \App\Models\Plug4MarketOrder::with(['cliente', 'items.product'])->findOrFail($id);
        
        return view('plug4market.orders.show', compact('id', 'title', 'order'));
    }

    public function edit($id)
    {
        // Formulário de edição
        $title = 'Editar Pedido Plug4Market';
        $order = \App\Models\Plug4MarketOrder::with(['cliente', 'items.product'])->findOrFail($id);
        
        // Buscar produtos ativos e sincronizados
        $products = \App\Models\Plug4MarketProduct::ativos()->sincronizados()->get(['id', 'external_id', 'codigo', 'descricao', 'valor_unitario']);
        
        // Buscar clientes existentes no sistema
        $clientes = \App\Models\Cliente::where('empresa_id', session('user_logged')['empresa'])
            ->where('inativo', false)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'nome_fantasia', 'cpf_cnpj', 'email', 'telefone', 'rua', 'numero', 'complemento', 'bairro', 'cidade_id', 'cep']);
        
        return view('plug4market.orders.edit', compact('id', 'title', 'order', 'products', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($id);
        $data = $request->all();
        
        // Atualizar dados básicos
        $order->marketplace = $data['marketplace'] ?? $order->marketplace;
        $order->status = $data['status'] ?? $order->status;
        $order->shipping_cost = $data['shipping_cost'] ?? $order->shipping_cost;
        $order->shipping_name = $data['shipping_name'] ?? $order->shipping_name;
        $order->payment_name = $data['payment_name'] ?? $order->payment_name;
        $order->interest = $data['interest'] ?? $order->interest;
        $order->total_commission = $data['total_commission'] ?? $order->total_commission;
        $order->type_billing = $data['type_billing'] ?? $order->type_billing;
        
        // Atualizar dados de entrega
        $order->shipping_recipient_name = $data['shipping_recipient_name'] ?? $order->shipping_recipient_name;
        $order->shipping_phone = $data['shipping_phone'] ?? $order->shipping_phone;
        $order->shipping_street = $data['shipping_street'] ?? $order->shipping_street;
        $order->shipping_street_number = $data['shipping_street_number'] ?? $order->shipping_street_number;
        $order->shipping_city = $data['shipping_city'] ?? $order->shipping_city;
        $order->shipping_street_complement = $data['shipping_street_complement'] ?? $order->shipping_street_complement;
        $order->shipping_country = $data['shipping_country'] ?? $order->shipping_country;
        $order->shipping_district = $data['shipping_district'] ?? $order->shipping_district;
        $order->shipping_state = $data['shipping_state'] ?? $order->shipping_state;
        $order->shipping_zip_code = $data['shipping_zip_code'] ?? $order->shipping_zip_code;
        $order->shipping_ibge = $data['shipping_ibge'] ?? $order->shipping_ibge;
        
        // Atualizar dados de cobrança
        $order->billing_name = $data['billing_name'] ?? $order->billing_name;
        $order->billing_email = $data['billing_email'] ?? $order->billing_email;
        $order->billing_document_id = $data['billing_document_id'] ?? $order->billing_document_id;
        $order->billing_phone = $data['billing_phone'] ?? $order->billing_phone;
        $order->billing_street = $data['billing_street'] ?? $order->billing_street;
        $order->billing_street_number = $data['billing_street_number'] ?? $order->billing_street_number;
        $order->billing_street_complement = $data['billing_street_complement'] ?? $order->billing_street_complement;
        $order->billing_district = $data['billing_district'] ?? $order->billing_district;
        $order->billing_city = $data['billing_city'] ?? $order->billing_city;
        $order->billing_state = $data['billing_state'] ?? $order->billing_state;
        $order->billing_country = $data['billing_country'] ?? $order->billing_country;
        $order->billing_zip_code = $data['billing_zip_code'] ?? $order->billing_zip_code;
        $order->billing_ibge = $data['billing_ibge'] ?? $order->billing_ibge;
        $order->billing_tax_payer = $data['billing_tax_payer'] ?? $order->billing_tax_payer;
        
        // Atualizar dados de nota fiscal
        $order->invoice_number = $data['invoice_number'] ?? $order->invoice_number;
        $order->invoice_key = $data['invoice_key'] ?? $order->invoice_key;
        $order->invoice_date = $data['invoice_date'] ? \Carbon\Carbon::parse($data['invoice_date']) : $order->invoice_date;
        $order->invoice_url = $data['invoice_url'] ?? $order->invoice_url;
        $order->invoice_status = $data['invoice_status'] ?? $order->invoice_status;
        $order->invoice_series = $data['invoice_series'] ?? $order->invoice_series;
        $order->invoice_model = $data['invoice_model'] ?? $order->invoice_model;
        $order->invoice_environment = $data['invoice_environment'] ?? $order->invoice_environment;
        $order->invoice_protocol = $data['invoice_protocol'] ?? $order->invoice_protocol;
        $order->invoice_protocol_date = $data['invoice_protocol_date'] ? \Carbon\Carbon::parse($data['invoice_protocol_date']) : $order->invoice_protocol_date;
        $order->invoice_total_products = $data['invoice_total_products'] ?? $order->invoice_total_products;
        $order->invoice_total_taxes = $data['invoice_total_taxes'] ?? $order->invoice_total_taxes;
        $order->invoice_total_shipping = $data['invoice_total_shipping'] ?? $order->invoice_total_shipping;
        $order->invoice_total_discount = $data['invoice_total_discount'] ?? $order->invoice_total_discount;
        $order->invoice_total_final = $data['invoice_total_final'] ?? $order->invoice_total_final;
        
        // Processar upload do arquivo da nota fiscal
        if ($request->hasFile('invoice_file') && $request->file('invoice_file')->isValid()) {
            $file = $request->file('invoice_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('invoices', $fileName, 'public');
            
            $order->invoice_file_name = $file->getClientOriginalName();
            $order->invoice_file_path = $filePath;
            $order->invoice_file_size = $file->getSize();
            $order->invoice_file_type = $file->getMimeType();
            $order->invoice_file_uploaded_at = now();
        }
        
        // Vincular cliente se selecionado
        $order->cliente_id = $data['cliente_id'] ?? $order->cliente_id;
        
        $order->save();
        
        return redirect()->route('plug4market.orders.show', $order->id)->with('success', 'Pedido atualizado com sucesso!');
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteOrder($id);
            return redirect()->route('plug4market.orders.index')->with('success', 'Pedido removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('plug4market.orders.index')->with('error', 'Erro ao remover pedido: ' . $e->getMessage());
        }
    }

    /**
     * Testar conectividade com a API
     */
    public function testApi()
    {
        try {
            $result = $this->service->testConnection();
            return response()->json([
                'success' => true,
                'message' => 'API conectada com sucesso',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao conectar com a API: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar criação de pedido simples
     */
    public function testCreateOrder()
    {
        try {
            $testData = [
                'marketplace' => 7,
                'status' => 2,
                'shipping_cost' => 1,
                'shipping_name' => 'SEDEX',
                'payment_name' => 'Cartão Crédito',
                'interest' => 0,
                'total_commission' => 1000,
                'type_billing' => 'PJ',
                'produtos' => [
                    '1' => [
                        'id' => '1',
                        'sku' => '102030',
                        'quantidade' => 1
                    ]
                ]
            ];

            $result = $this->service->createOrder($testData);
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido de teste criado com sucesso',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pedido de teste: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function importInvoice($orderId)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($orderId);
        $apiOrderId = $order->external_id;

        // Buscar configurações dinâmicas
        $settings = \App\Models\Plug4MarketSetting::getSettings();
        $token = $settings->access_token;
        $baseUrl = $settings->base_url;
        if (!$token || !$baseUrl) {
            return response()->json(['success' => false, 'message' => 'Configuração Plug4Market ausente ou incompleta.'], 500);
        }
        $apiUrl = rtrim($baseUrl, '/') . '/orders/' . $apiOrderId . '/invoice';
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            $order->invoice_number = $data['number'] ?? null;
            $order->invoice_key = $data['key'] ?? null;
            $order->invoice_date = $data['date'] ?? null;
            $order->invoice_url = $data['url'] ?? null;
            $order->invoice_status = $data['status'] ?? null;
            $order->invoice_payload = json_encode($data);
            $order->save();
            return response()->json(['success' => true, 'message' => 'Nota importada com sucesso', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao importar nota', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sincronizar pedido local com a API
     */
    public function syncOrder($id)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($id);
        
        // Se já está sincronizado, não precisa sincronizar novamente
        if ($order->sincronizado && !empty($order->external_id) && !str_starts_with($order->external_id, 'TEMP_')) {
            return response()->json([
                'success' => true,
                'message' => 'Pedido já está sincronizado',
                'order_id' => $order->id
            ]);
        }
        
        try {
            // Preparar dados do pedido para a API
            $orderData = [
                'marketplace' => $order->marketplace,
                'status' => $order->status,
                'shipping_cost' => $order->shipping_cost,
                'shipping_name' => $order->shipping_name,
                'payment_name' => $order->payment_name,
                'interest' => $order->interest,
                'total_commission' => $order->total_commission,
                'type_billing' => $order->type_billing,
                'produtos' => []
            ];
            
            // Adicionar produtos do pedido
            foreach ($order->items as $item) {
                $orderData['produtos'][$item->product_id] = [
                    'id' => $item->product_id,
                    'sku' => $item->sku,
                    'quantidade' => $item->quantity
                ];
            }
            
            // Dados de entrega
            $orderData['shipping_recipient_name'] = $order->shipping_recipient_name;
            $orderData['shipping_phone'] = $order->shipping_phone;
            $orderData['shipping_street'] = $order->shipping_street;
            $orderData['shipping_street_number'] = $order->shipping_street_number;
            $orderData['shipping_city'] = $order->shipping_city;
            $orderData['shipping_street_complement'] = $order->shipping_street_complement;
            $orderData['shipping_country'] = $order->shipping_country;
            $orderData['shipping_district'] = $order->shipping_district;
            $orderData['shipping_state'] = $order->shipping_state;
            $orderData['shipping_zip_code'] = $order->shipping_zip_code;
            $orderData['shipping_ibge'] = $order->shipping_ibge;
            
            // Dados de cobrança
            $orderData['billing_name'] = $order->billing_name;
            $orderData['billing_email'] = $order->billing_email;
            $orderData['billing_document_id'] = $order->billing_document_id;
            $orderData['billing_state_registration_id'] = $order->billing_state_registration_id;
            $orderData['billing_street'] = $order->billing_street;
            $orderData['billing_street_number'] = $order->billing_street_number;
            $orderData['billing_street_complement'] = $order->billing_street_complement;
            $orderData['billing_district'] = $order->billing_district;
            $orderData['billing_city'] = $order->billing_city;
            $orderData['billing_state'] = $order->billing_state;
            $orderData['billing_country'] = $order->billing_country;
            $orderData['billing_zip_code'] = $order->billing_zip_code;
            $orderData['billing_phone'] = $order->billing_phone;
            $orderData['billing_gender'] = $order->billing_gender;
            $orderData['billing_date_of_birth'] = $order->billing_date_of_birth;
            $orderData['billing_tax_payer'] = $order->billing_tax_payer;
            $orderData['billing_ibge'] = $order->billing_ibge;
            
            // Tentar criar na API
            $result = $this->service->createOrder($orderData);
            
            if (isset($result['id']) || isset($result['external_id'])) {
                // Atualizar pedido local com dados da API
                $order->external_id = $result['id'] ?? $result['external_id'];
                $order->order_number = $result['numero'] ?? ($result['order_number'] ?? $order->order_number);
                $order->total_amount = $result['total_amount'] ?? $order->total_amount;
                $order->sincronizado = true;
                $order->raw_data = $result;
                $order->ultima_sincronizacao = now();
                $order->save();
                
                \App\Models\Plug4MarketLog::create([
                    'action' => 'sync_order',
                    'status' => 'success',
                    'message' => 'Pedido sincronizado com sucesso',
                    'details' => [
                        'order_id' => $order->id,
                        'external_id' => $order->external_id,
                        'api_response' => $result
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'execution_time_ms' => 0
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido sincronizado com sucesso',
                    'order_id' => $order->id,
                    'external_id' => $order->external_id
                ]);
            } else {
                \App\Models\Plug4MarketLog::create([
                    'action' => 'sync_order',
                    'status' => 'error',
                    'message' => 'Erro ao sincronizar pedido',
                    'details' => [
                        'order_id' => $order->id,
                        'api_response' => $result
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'execution_time_ms' => 0
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao sincronizar pedido: ' . ($result['error_messages'][0]['message'] ?? 'Erro desconhecido'),
                    'order_id' => $order->id
                ], 500);
            }
            
        } catch (\Exception $e) {
            \App\Models\Plug4MarketLog::create([
                'action' => 'sync_order',
                'status' => 'error',
                'message' => 'Erro ao sincronizar pedido: ' . $e->getMessage(),
                'details' => [
                    'order_id' => $order->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => 0
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar pedido: ' . $e->getMessage(),
                'order_id' => $order->id
            ], 500);
        }
    }

    /**
     * Baixar XML da nota fiscal
     */
    public function downloadInvoiceXml($id)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($id);
        
        if (!$order->hasInvoiceXml()) {
            return response()->json([
                'success' => false,
                'message' => 'XML da nota fiscal não disponível'
            ], 404);
        }
        
        try {
            $filename = $order->invoice_xml_filename ?: ($order->invoice_key . '.xml');
            
            return response($order->invoice_xml)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($order->invoice_xml));
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar XML: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Visualizar XML da nota fiscal
     */
    public function viewInvoiceXml($id)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($id);
        
        if (!$order->hasInvoiceXml()) {
            return response()->json([
                'success' => false,
                'message' => 'XML da nota fiscal não disponível'
            ], 404);
        }
        
        try {
            return response($order->invoice_xml)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'inline; filename="' . $order->invoice_xml_filename . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao visualizar XML: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar XML da nota fiscal
     */
    public function processInvoiceXml($id)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($id);
        
        try {
            $result = $this->service->processInvoiceXml($order->id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                    'error' => $result
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar XML: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar disponibilidade da nota fiscal
     */
    public function checkInvoiceAvailability($id)
    {


        
        
        $order = \App\Models\Plug4MarketOrder::findOrFail($id);
        
        


        try {
            $result = $this->service->checkInvoiceAvailability($order->external_id);
            

            dd($result);

            if ($result['success']) {
                // Atualizar dados da nota fiscal se disponível
                if ($result['has_invoice'] && isset($result['invoice_data'])) {
                    $invoiceData = $result['invoice_data'];
                    
                    $order->invoice_number = $invoiceData['number'] ?? $order->invoice_number;
                    $order->invoice_key = $invoiceData['key'] ?? $order->invoice_key;
                    $order->invoice_date = $invoiceData['date'] ?? $order->invoice_date;
                    $order->invoice_url = $invoiceData['url'] ?? $order->invoice_url;
                    $order->invoice_status = $invoiceData['status'] ?? $order->invoice_status;
                    $order->invoice_payload = $invoiceData;
                    $order->save();
                }
                
                return response()->json([
                    'success' => true,
                    'has_invoice' => $result['has_invoice'],
                    'xml_available' => $result['xml_available'],
                    'invoice_data' => $result['invoice_data'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                    'error' => $result
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar disponibilidade: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar nota fiscal e XML
     */
    public function importInvoiceWithXml($orderId)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($orderId);
        
        try {
            // Primeiro importar dados da nota fiscal
            $invoiceResult = $this->importInvoice($orderId);
            $invoiceData = json_decode($invoiceResult->getContent(), true);
            
            if (!$invoiceData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao importar nota fiscal: ' . $invoiceData['message']
                ], 500);
            }
            
            // Depois processar XML
            $xmlResult = $this->service->processInvoiceXml($order->id);
            
            if ($xmlResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nota fiscal e XML importados com sucesso',
                    'invoice_data' => $invoiceData['data'],
                    'xml_data' => $xmlResult
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Nota fiscal importada, mas erro no XML: ' . $xmlResult['error'],
                    'invoice_data' => $invoiceData['data'],
                    'xml_error' => $xmlResult['error']
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar nota fiscal e XML: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download do arquivo da nota fiscal
     */
    public function downloadInvoiceFile($id)
    {
        $order = \App\Models\Plug4MarketOrder::findOrFail($id);
        
        if (!$order->hasInvoiceFile()) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo da nota fiscal não disponível'
            ], 404);
        }
        
        try {
            $filePath = storage_path('app/public/' . $order->invoice_file_path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado no servidor'
                ], 404);
            }
            
            return response()->download($filePath, $order->invoice_file_name);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }
} 