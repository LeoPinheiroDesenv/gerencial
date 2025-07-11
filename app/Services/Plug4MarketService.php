<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Plug4MarketProduct;
use App\Models\Plug4MarketOrder;
use App\Models\Plug4MarketToken;
use App\Models\Plug4MarketSetting;

class Plug4MarketService
{
    protected $baseUrl;
    protected $userToken;
    protected $accessToken;
    protected $refreshToken;
    protected $isSandbox;
    protected $settings;
    protected $tokenExpiresAt;
    protected $email;
    protected $password;

    public function __construct()
    {
        $this->settings = Plug4MarketSetting::getSettings();
        $this->loadConfiguration();
    }

    protected function loadConfiguration()
    {
        if ($this->settings->isConfigured()) {
            $this->isSandbox = $this->settings->sandbox;
            $this->baseUrl = $this->settings->base_url;
            $this->accessToken = $this->settings->access_token;
            $this->refreshToken = $this->settings->refresh_token;
        } else {
            // Fallback para configurações do .env
            $this->isSandbox = config('services.plug4market.sandbox', true);
            $this->baseUrl = $this->isSandbox 
                ? 'https://api.sandbox.plug4market.com.br'
                : 'https://api.plug4market.com.br';
            
            $this->accessToken = config('services.plug4market.token');
            $this->refreshToken = config('services.plug4market.refresh_token');
        }
    }

    /**
     * Login do usuário para obter token de usuário
     */
    public function loginUser($login, $password)
    {
        try {
            Log::info('Fazendo login do usuário Plug4Market', ['login' => $login]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/auth/login', [
                'login' => $login,
                'password' => $password
            ]);

            Log::info('Resposta do login Plug4Market', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['accessToken'])) {
                    $this->userToken = $data['accessToken'];
                    
                    Log::info('Login do usuário Plug4Market realizado com sucesso', [
                        'user_id' => $data['user']['id'] ?? 'N/A',
                        'user_name' => $data['user']['name'] ?? 'N/A'
                    ]);
                    
                    return $data;
                } else {
                    Log::error('Resposta de login não contém accessToken', $data);
                    return false;
                }
            } else {
                Log::error('Falha no login do usuário Plug4Market', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Erro no login do usuário Plug4Market: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Gerar tokens da loja usando o token do usuário
     */
    public function generateStoreTokens($storeCnpj, $softwareHouseCnpj)
    {
        try {
            if (!$this->userToken) {
                Log::error('Token do usuário não disponível para gerar tokens da loja');
                return false;
            }

            Log::info('Gerando tokens da loja Plug4Market', [
                'store_cnpj' => $storeCnpj,
                'software_house_cnpj' => $softwareHouseCnpj
            ]);

            $url = $this->baseUrl . "/stores/{$storeCnpj}/software-houses/{$softwareHouseCnpj}/token?notEncoded=true";
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->userToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($url);

            Log::info('Resposta da geração de tokens da loja Plug4Market', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['accessToken']) && isset($data['refreshToken'])) {
                    // Salvar tokens no banco
                    Plug4MarketToken::updateOrCreate(
                        ['id' => 1],
                        [
                            'access_token' => $data['accessToken'],
                            'refresh_token' => $data['refreshToken'],
                            'expires_at' => now()->addHours(23), // 23 horas conforme documentação
                            'updated_at' => now()
                        ]
                    );

                    // Atualizar configurações
                    $this->settings->update([
                        'access_token' => $data['accessToken'],
                        'refresh_token' => $data['refreshToken']
                    ]);

                    $this->accessToken = $data['accessToken'];
                    $this->refreshToken = $data['refreshToken'];

                    Log::info('Tokens da loja Plug4Market gerados com sucesso');
                    return $data;
                } else {
                    Log::error('Resposta de geração de tokens não contém tokens válidos', $data);
                    return false;
                }
            } else {
                Log::error('Falha na geração de tokens da loja Plug4Market', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Erro na geração de tokens da loja Plug4Market: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return false;
        }
    }

    protected function headers()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Adicionar token de autorização se disponível
        if ($this->accessToken) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        return $headers;
    }

    /**
     * Autenticar e obter token de acesso
     */
    public function authenticate()
    {
        Log::info('Iniciando autenticação Plug4Market', [
            'email' => $this->email,
            'base_url' => $this->baseUrl
        ]);

        try {
            $response = Http::post($this->baseUrl . '/auth/login', [
                'email' => $this->email,
                'password' => $this->password
            ]);

            Log::info('Resposta da autenticação Plug4Market', [
                'status_code' => $response->status(),
                'sucesso' => $response->successful(),
                'tem_token' => $response->successful() && isset($response->json()['access_token'])
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['access_token'])) {
                    $this->accessToken = $data['access_token'];
                    $this->refreshToken = $data['refresh_token'] ?? null;
                    $this->tokenExpiresAt = isset($data['expires_in']) 
                        ? now()->addSeconds($data['expires_in']) 
                        : now()->addHours(1);
                    
                    Log::info('Autenticação Plug4Market bem-sucedida', [
                        'token_expira_em' => $this->tokenExpiresAt->toISOString(),
                        'tem_refresh_token' => !empty($this->refreshToken)
                    ]);
                    
                    return true;
                } else {
                    Log::error('Resposta de autenticação Plug4Market não contém access_token', [
                        'resposta' => $data
                    ]);
                    return false;
                }
            } else {
                Log::error('Falha na autenticação Plug4Market', [
                    'status_code' => $response->status(),
                    'resposta' => $response->json(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Erro durante autenticação Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e)
            ]);
            return false;
        }
    }

    /**
     * Renovar token de acesso
     */
    public function refreshAccessToken()
    {
        if (!$this->refreshToken) {
            Log::warning('Tentativa de renovar token Plug4Market sem refresh_token');
            return false;
        }

        Log::info('Iniciando renovação de token Plug4Market', [
            'tem_refresh_token' => !empty($this->refreshToken),
            'token_atual_expira_em' => $this->tokenExpiresAt?->toISOString()
        ]);

        try {
            $response = Http::post($this->baseUrl . '/auth/refresh', [
                'refresh_token' => $this->refreshToken
            ]);

            Log::info('Resposta da renovação de token Plug4Market', [
                'status_code' => $response->status(),
                'sucesso' => $response->successful(),
                'tem_novo_token' => $response->successful() && isset($response->json()['access_token'])
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['access_token'])) {
                    $this->accessToken = $data['access_token'];
                    $this->refreshToken = $data['refresh_token'] ?? $this->refreshToken;
                    $this->tokenExpiresAt = isset($data['expires_in']) 
                        ? now()->addSeconds($data['expires_in']) 
                        : now()->addHours(1);
                    
                    // Atualizar configurações no banco de dados
                    try {
                        $this->settings->update([
                            'access_token' => $this->accessToken,
                            'refresh_token' => $this->refreshToken
                        ]);
                        
                        Log::info('Configurações Plug4Market atualizadas no banco após renovação do token');
                    } catch (\Exception $dbException) {
                        Log::warning('Erro ao atualizar configurações no banco após renovação do token', [
                            'error' => $dbException->getMessage()
                        ]);
                    }
                    
                    Log::info('Token Plug4Market renovado com sucesso', [
                        'novo_token_expira_em' => $this->tokenExpiresAt->toISOString(),
                        'tem_novo_refresh_token' => !empty($data['refresh_token'])
                    ]);
                    
                    return true;
                } else {
                    Log::error('Resposta de renovação Plug4Market não contém access_token', [
                        'resposta' => $data
                    ]);
                    return false;
                }
            } else {
                Log::error('Falha na renovação de token Plug4Market', [
                    'status_code' => $response->status(),
                    'resposta' => $response->json(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Erro durante renovação de token Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e)
            ]);
            return false;
        }
    }

    /**
     * Testar a conexão com a API Plug4Market
     * 
     * @return bool
     */
    public function testConnection()
    {
        try {
            if (!$this->accessToken) {
                Log::warning('Token Plug4Market não configurado');
                return false;
            }
            
            // Testar com endpoint de categorias que é mais confiável
            $response = $this->makeRequest('get', '/products');

            $success = $response->successful();

            Log::info('Teste de conexão Plug4Market', [
                'success' => $success,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200),
                'headers' => $response->headers()
            ]);
            
            // Se falhou, tentar sem autenticação para ver se é problema de token
            if (!$success && $response->status() === 401) {
                Log::info('Token pode estar inválido, tentando renovar...');
                
                if ($this->refreshAccessToken()) {
                    // Tentar novamente com novo token
                    $response = $this->makeRequest('get', '/products');
                    $success = $response->successful();
                    
                    Log::info('Teste após renovação do token', [
                        'success' => $success,
                        'status' => $response->status()
                    ]);
                }
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Erro no teste de conexão Plug4Market: ' . $e->getMessage(), [
                'exception' => $e,
                'token_length' => $this->accessToken ? strlen($this->accessToken) : 0,
                'base_url' => $this->baseUrl
            ]);
            return false;
        }
    }

    /**
     * Verificar se o token está válido
     */
    public function isTokenValid()
    {
        $isValid = $this->accessToken && $this->tokenExpiresAt && $this->tokenExpiresAt->isFuture();
        
        Log::info('Verificação de validade do token Plug4Market', [
            'tem_token' => !empty($this->accessToken),
            'tem_expiração' => !empty($this->tokenExpiresAt),
            'expira_em' => $this->tokenExpiresAt?->toISOString(),
            'é_válido' => $isValid
        ]);
        
        return $isValid;
    }

    /**
     * Limpar e validar dados para JSON
     */
    protected function cleanDataForJson($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = $this->cleanDataForJson($value);
            } elseif (is_string($value)) {
                // Remover caracteres problemáticos e garantir UTF-8
                $cleanedValue = trim($value);
                // Remover caracteres de controle exceto tab, newline, carriage return
                $cleanedValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleanedValue);
                // Garantir UTF-8 válido
                $cleanedValue = mb_convert_encoding($cleanedValue, 'UTF-8', 'UTF-8');
                // Limitar tamanho se necessário
                if (strlen($cleanedValue) > 1000) {
                    $cleanedValue = substr($cleanedValue, 0, 1000);
                }
                $cleaned[$key] = $cleanedValue;
            } elseif (is_numeric($value)) {
                $cleaned[$key] = is_float($value) ? (float) $value : (int) $value;
            } elseif (is_bool($value)) {
                $cleaned[$key] = $value;
            } elseif (is_null($value)) {
                $cleaned[$key] = null;
            } else {
                // Converter outros tipos para string
                $cleaned[$key] = (string) $value;
            }
        }

        return $cleaned;
    }

    /**
     * Fazer requisição HTTP
     */
    protected function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        // Limpar dados antes de enviar
        $cleanData = $this->cleanDataForJson($data);
        
        Log::info('Iniciando requisição HTTP Plug4Market', [
            'metodo' => strtoupper($method),
            'url' => $url,
            'endpoint' => $endpoint,
            'tem_dados' => !empty($cleanData),
            'tipo_dados' => is_array($cleanData) ? 'array' : gettype($cleanData),
            'dados_json' => is_array($cleanData) ? json_encode($cleanData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null
        ]);

        try {
            // Verificar token e renovar se necessário
            if ($this->refreshToken && (!$this->tokenExpiresAt || $this->tokenExpiresAt->isPast())) {
                Log::info('Token expirado, tentando renovar antes da requisição');
                $this->refreshAccessToken();
            }

            $request = Http::withHeaders($this->headers())
                ->timeout(30)
                ->retry(3, 1000);

            switch (strtolower($method)) {
                case 'get':
                    $response = $request->get($url, $cleanData ?? []);
                    break;
                case 'post':
                    // Para POST, enviar como JSON
                    if (is_array($cleanData)) {
                        $response = $request->withBody(json_encode($cleanData, JSON_UNESCAPED_UNICODE), 'application/json')
                                           ->post($url);
                    } else {
                        $response = $request->post($url, $cleanData ?? []);
                    }
                    break;
                case 'put':
                    // Para PUT, enviar como JSON
                    if (is_array($cleanData)) {
                        $response = $request->withBody(json_encode($cleanData, JSON_UNESCAPED_UNICODE), 'application/json')
                                           ->put($url);
                    } else {
                        $response = $request->put($url, $cleanData ?? []);
                    }
                    break;
                case 'patch':
                    // Para PATCH, enviar como JSON
                    if (is_array($cleanData)) {
                        $response = $request->withBody(json_encode($cleanData, JSON_UNESCAPED_UNICODE), 'application/json')
                                           ->patch($url);
                    } else {
                        $response = $request->patch($url, $cleanData ?? []);
                    }
                    break;
                case 'delete':
                    $response = $request->delete($url, $cleanData ?? []);
                    break;
                default:
                    throw new \InvalidArgumentException("Método HTTP inválido: {$method}");
            }

            Log::info('Resposta da requisição HTTP Plug4Market', [
                'status_code' => $response->status(),
                'sucesso' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 500),
                'headers' => $response->headers()
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Erro na requisição HTTP Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'url' => $url,
                'metodo' => $method,
                'dados_enviados' => is_array($cleanData) ? json_encode($cleanData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $cleanData
            ]);
            
            throw $e;
        }
    }

    // ========== PRODUTOS ==========

    /**
     * Listar produtos da API
     */
    public function listProducts($params = [])
    {
        Log::info('Fazendo requisição para listar produtos Plug4Market', [
            'endpoint' => '/products',
            'metodo' => 'GET'
        ]);

        $response = $this->makeRequest('get', '/products', $params);
        return $response->json();
    }

    /**
     * Buscar produto específico
     */
    public function getProduct($productId)
    {
        Log::info('Fazendo requisição para buscar produto Plug4Market', [
            'endpoint' => "/products/{$productId}",
            'metodo' => 'GET',
            'product_id' => $productId
        ]);

        $response = $this->makeRequest('get', "/products/{$productId}");
        return $response->json();
    }

    /**
     * Criar produto
     */
    public function createProduct($data)
    {
        Log::info('Fazendo requisição para criar produto Plug4Market', [
            'endpoint' => '/products',
            'metodo' => 'POST',
            'dados_produto' => array_intersect_key($data, array_flip(['codigo', 'descricao', 'categoria_id', 'marca', 'valor_unitario']))
        ]);

        try {
            $productData = $this->formatProductData($data);
            
            Log::info('Dados formatados para API Plug4Market', [
                'dados_formatados' => $productData,
                'tamanho_dados' => count($productData)
            ]);
            
            $response = $this->makeRequest('post', '/products', $productData);
            $responseData = $response->json();
            
            Log::info('Resposta da API Plug4Market ao criar produto', [
                'status_code' => $response->status(),
                'response_data' => $responseData,
                'response_body' => $response->body(),
                'tem_id' => isset($responseData['id']),
                'tem_erro' => isset($responseData['error']) || isset($responseData['message'])
            ]);
            
            // Se a resposta não for 2xx, padronizar retorno de erro
            if (!$response->successful()) {
                return [
                    'error_status' => $response->status(),
                    'error_messages' => isset($responseData['errors']) ? $responseData['errors'] : [
                        [
                            'message' => $responseData['message'] ?? 'Erro desconhecido',
                            'statusCode' => $response->status()
                        ]
                    ],
                    'raw_response' => $responseData
                ];
            }
            
            return $responseData;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar produto Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar produto
     */
    public function updateProduct($productId, $data)
    {
        Log::info('Fazendo requisição para atualizar produto Plug4Market', [
            'endpoint' => "/products/{$productId}",
            'metodo' => 'PUT',
            'product_id' => $productId,
            'dados_produto' => array_intersect_key($data, array_flip(['codigo', 'descricao', 'categoria_id', 'marca', 'valor_unitario']))
        ]);

        $productData = $this->formatProductData($data, true);
        $response = $this->makeRequest('put', "/products/{$productId}", $productData);
        return $response->json();
    }

    /**
     * Deletar produto
     */
    public function deleteProduct($productId)
    {
        Log::info('Fazendo requisição para deletar produto Plug4Market', [
            'endpoint' => "/products/{$productId}",
            'metodo' => 'DELETE',
            'product_id' => $productId
        ]);

        $response = $this->makeRequest('delete', "/products/{$productId}");
        return $response->json();
    }

    protected function formatProductData($data, $isUpdate = false)
    {
        // Campos obrigatórios conforme documentação da API
        $productData = [
            'productId' => $data['codigo'],
            'productName' => $data['descricao'],
            'sku' => $data['codigo'],
            'name' => $data['descricao'],
            'description' => $data['descricao'],
            'width' => (float) ($data['largura'] ?? 10),
            'height' => (float) ($data['altura'] ?? 10),
            'length' => (float) ($data['comprimento'] ?? 10),
            'weight' => (float) ($data['peso'] ?? 1),
            'stock' => (int) ($data['estoque'] ?? 0),
            'price' => (float) $data['valor_unitario'],
            'salesChannels' => [
                [
                    'id' => 1, // Amazon (ID 1 conforme enum da API)
                    'price' => (float) $data['valor_unitario']
                ]
            ]
        ];

        // Campos opcionais
        if (!empty($data['categoria_id'])) {
            $productData['categoryId'] = $data['categoria_id'];
        }

        if (!empty($data['marca'])) {
            $productData['brand'] = $data['marca'];
        }

        if (!empty($data['origem'])) {
            $productData['origin'] = $data['origem'];
        } else {
            $productData['origin'] = 'nacional';
        }

        if (!empty($data['ean'])) {
            $productData['ean'] = $data['ean'];
        }

        if (!empty($data['modelo'])) {
            $productData['model'] = $data['modelo'];
        }

        if (!empty($data['garantia'])) {
            $productData['warranty'] = (float) $data['garantia'];
        }

        if (!empty($data['ncm'])) {
            $productData['ncm'] = $data['ncm'];
        }

        if (!empty($data['preco_custo'])) {
            $productData['costPrice'] = (float) $data['preco_custo'];
        }

        // Campos adicionais para atualização
        if ($isUpdate) {
            if (!empty($data['imagens'])) {
                $productData['images'] = $data['imagens'];
            }
            
            if (!empty($data['metafields'])) {
                $productData['metafields'] = $data['metafields'];
            }
            
            if (!empty($data['active'])) {
                $productData['active'] = (bool) $data['active'];
            }
        }

        Log::info('Dados do produto formatados para API Plug4Market', [
            'dados_originais' => array_intersect_key($data, array_flip(['codigo', 'descricao', 'categoria_id', 'marca', 'valor_unitario', 'preco_custo', 'ncm', 'ean', 'modelo', 'garantia', 'largura', 'altura', 'comprimento', 'peso', 'estoque', 'origem'])),
            'dados_formatados' => $productData,
            'is_update' => $isUpdate,
            'campos_obrigatorios_presentes' => [
                'productId' => isset($productData['productId']),
                'productName' => isset($productData['productName']),
                'sku' => isset($productData['sku']),
                'name' => isset($productData['name']),
                'salesChannels' => isset($productData['salesChannels']),
                'description' => isset($productData['description']),
                'width' => isset($productData['width']),
                'height' => isset($productData['height']),
                'length' => isset($productData['length']),
                'weight' => isset($productData['weight']),
                'stock' => isset($productData['stock']),
                'price' => isset($productData['price'])
            ]
        ]);

        return $productData;
    }

    // ========== PEDIDOS ==========

    public function listOrders($params = [])
    {
        $response = $this->makeRequest('get', '/orders', $params);
        return $response->json();
    }

    public function getOrder($orderId)
    {
        $response = $this->makeRequest('get', "/orders/{$orderId}");
        return $response->json();
    }

    public function updateOrderStatus($orderId, $status)
    {
        $response = $this->makeRequest('put', "/orders/{$orderId}/status", [
            'status' => $status
        ]);
        return $response->json();
    }

    /**
     * Criar pedido na API Plug4Market
     */
    public function createOrder($data)
    {
        Log::info('Fazendo requisição para criar pedido Plug4Market', [
            'endpoint' => '/orders/new/sandbox',
            'metodo' => 'POST',
            'dados_pedido' => array_intersect_key($data, array_flip(['marketplace', 'status', 'shipping_cost', 'shipping_name', 'payment_name', 'interest', 'total_commission', 'type_billing']))
        ]);

        try {
            // Testar conectividade primeiro
            Log::info('Testando conectividade com a API Plug4Market antes de criar pedido');
            $testResponse = $this->makeRequest('get', '/products');
            Log::info('Teste de conectividade', [
                'status' => $testResponse->status(),
                'success' => $testResponse->successful(),
                'body' => substr($testResponse->body(), 0, 200)
            ]);
            
            // Se o teste falhou, tentar renovar o token
            if (!$testResponse->successful() && $testResponse->status() === 401) {
                Log::info('Token pode estar inválido, tentando renovar...');
                if ($this->refreshAccessToken()) {
                    Log::info('Token renovado, testando novamente...');
                    $testResponse = $this->makeRequest('get', '/products');
                    Log::info('Teste após renovação', [
                        'status' => $testResponse->status(),
                        'success' => $testResponse->successful()
                    ]);
                }
            }
            
            $orderData = $this->formatOrderData($data);
            
            Log::info('Dados formatados para API Plug4Market - Pedido', [
                'dados_formatados' => $orderData,
                'tamanho_dados' => count($orderData),
                'json_teste' => json_encode($orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'json_erro' => json_last_error_msg()
            ]);
            
            // Testar se o JSON é válido antes de enviar
            $jsonTest = json_encode($orderData, JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erro na geração do JSON para pedido', [
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'dados_problematicos' => $orderData
                ]);
                throw new \Exception('Erro na formatação dos dados: ' . json_last_error_msg());
            }
            
            // Log do JSON que será enviado
            Log::info('JSON que será enviado para a API', [
                'json_data' => $jsonTest,
                'json_size' => strlen($jsonTest)
            ]);
            
            $response = $this->makeRequest('post', '/orders/new/sandbox', $orderData);
            $responseData = $response->json();
            
            Log::info('Resposta da API Plug4Market ao criar pedido', [
                'status_code' => $response->status(),
                'response_data' => $responseData,
                'response_body' => $response->body(),
                'response_headers' => $response->headers(),
                'tem_id' => isset($responseData['id']),
                'tem_erro' => isset($responseData['error']) || isset($responseData['message'])
            ]);
            
            // Se a resposta não for 2xx, padronizar retorno de erro
            if (!$response->successful()) {
                return [
                    'error_status' => $response->status(),
                    'error_messages' => isset($responseData['errors']) ? $responseData['errors'] : [
                        [
                            'message' => $responseData['message'] ?? 'Erro desconhecido',
                            'statusCode' => $response->status()
                        ]
                    ],
                    'raw_response' => $responseData
                ];
            }
            
            return $responseData;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar pedido Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Se for erro 401, tentar renovar o token e tentar novamente
            if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Unauthorized') !== false) {
                Log::info('Erro 401 detectado, tentando renovar token e tentar novamente...');
                
                try {
                    if ($this->refreshAccessToken()) {
                        Log::info('Token renovado, tentando criar pedido novamente...');
                        
                        $orderData = $this->formatOrderData($data);
                        $response = $this->makeRequest('post', '/orders/new/sandbox', $orderData);
                        $responseData = $response->json();
                        
                        if ($response->successful()) {
                            Log::info('Pedido criado com sucesso após renovação do token');
                            return $responseData;
                        } else {
                            return [
                                'error_status' => $response->status(),
                                'error_messages' => isset($responseData['errors']) ? $responseData['errors'] : [
                                    [
                                        'message' => $responseData['message'] ?? 'Erro desconhecido após renovação do token',
                                        'statusCode' => $response->status()
                                    ]
                                ],
                                'raw_response' => $responseData
                            ];
                        }
                    }
                } catch (\Exception $retryException) {
                    Log::error('Erro ao tentar renovar token e tentar novamente', [
                        'original_error' => $e->getMessage(),
                        'retry_error' => $retryException->getMessage()
                    ]);
                }
            }
            
            throw $e;
        }
    }

    /**
     * Atualizar pedido na API Plug4Market
     */
    public function updateOrder($orderId, $data)
    {
        Log::info('Fazendo requisição para atualizar pedido Plug4Market', [
            'endpoint' => "/orders/{$orderId}",
            'metodo' => 'PUT',
            'order_id' => $orderId,
            'dados_pedido' => array_intersect_key($data, array_flip(['cliente_nome', 'customer_email', 'valor_total', 'status']))
        ]);

        $orderData = $this->formatOrderData($data, true);
        $response = $this->makeRequest('put', "/orders/{$orderId}", $orderData);
        return $response->json();
    }

    /**
     * Deletar pedido na API Plug4Market
     */
    public function deleteOrder($orderId)
    {
        $response = $this->makeRequest('delete', "/orders/{$orderId}");
        return $response->json();
    }

    /**
     * Formatar dados do pedido para a API
     */
    protected function formatOrderData($data, $isUpdate = false)
    {
        // Preparar itens do pedido
        $orderItems = [];
        if (!empty($data['produtos']) && is_array($data['produtos'])) {
            foreach ($data['produtos'] as $productId => $productData) {
                if (isset($productData['id']) && !empty($productData['id']) && isset($productData['quantidade'])) {
                    $orderItems[] = [
                        'sku' => $productData['sku'] ?? $productData['id'],
                        'quantity' => (int) $productData['quantidade']
                    ];
                }
            }
        }
        
        // Se não há produtos, criar um item padrão
        if (empty($orderItems)) {
            $orderItems = [
                [
                    'sku' => '102030',
                    'quantity' => 1
                ]
            ];
        }
        
        // Estrutura do pedido conforme modelo JSON fornecido
        $orderData = [
            'marketplace' => (int) ($data['marketplace'] ?? 7),
            'status' => (int) ($data['status'] ?? 2),
            'shippingCost' => (float) ($data['shipping_cost'] ?? 1),
            'shippingName' => $data['shipping_name'] ?? 'SEDEX',
            'paymentName' => $data['payment_name'] ?? 'Cartão Crédito',
            'interest' => (float) ($data['interest'] ?? 0),
            'totalCommission' => (float) ($data['total_commission'] ?? 1000),
            'typeBilling' => $data['type_billing'] ?? 'PJ',
            'orderItems' => $orderItems,
            'shipping' => [
                'recipientName' => $data['shipping_recipient_name'] ?? 'João da Silva (PEDIDO TESTE)',
                'phone' => $data['shipping_phone'] ?? '41999999999',
                'street' => $data['shipping_street'] ?? 'Rua Doutor Corrêa Coelho',
                'streetNumber' => $data['shipping_street_number'] ?? '741',
                'city' => $data['shipping_city'] ?? 'Curitiba',
                'streetComplement' => $data['shipping_street_complement'] ?? 'Sala 4A',
                'country' => $data['shipping_country'] ?? 'BR',
                'district' => $data['shipping_district'] ?? 'Jardim Botânico',
                'state' => $data['shipping_state'] ?? 'PR',
                'zipCode' => $data['shipping_zip_code'] ?? '80210350',
                'ibge' => $data['shipping_ibge'] ?? '4106902'
            ],
            'billing' => [
                'name' => $data['billing_name'] ?? 'João da Silva (PEDIDO TESTE)',
                'email' => $data['billing_email'] ?? '537422410963@email.com',
                'documentId' => $data['billing_document_id'] ?? '24075890503',
                'stateRegistrationId' => $data['billing_state_registration_id'] ?? null,
                'street' => $data['billing_street'] ?? 'Rua Loefgren',
                'streetNumber' => $data['billing_street_number'] ?? '656',
                'streetComplement' => $data['billing_street_complement'] ?? 'AP 14',
                'district' => $data['billing_district'] ?? 'Vila Clementino',
                'city' => $data['billing_city'] ?? 'São Paulo',
                'state' => $data['billing_state'] ?? 'SP',
                'country' => $data['billing_country'] ?? 'BR',
                'zipCode' => $data['billing_zip_code'] ?? '04040000',
                'phone' => $data['billing_phone'] ?? '41999999999',
                'gender' => $data['billing_gender'] ?? null,
                'dateOfBirth' => $data['billing_date_of_birth'] ?? null,
                'taxPayer' => (bool) ($data['billing_tax_payer'] ?? false),
                'ibge' => $data['billing_ibge'] ?? '3550308'
            ]
        ];

        Log::info('Dados do pedido formatados para API Plug4Market', [
            'dados_originais' => array_intersect_key($data, array_flip(['marketplace', 'status', 'shipping_cost', 'shipping_name', 'payment_name', 'interest', 'total_commission', 'type_billing'])),
            'dados_formatados' => $orderData,
            'total_itens' => count($orderItems),
            'json_teste' => json_encode($orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'json_erro' => json_last_error_msg()
        ]);

        return $orderData;
    }

    /**
     * Mapear status de texto para número conforme API Plug4Market
     */
    protected function mapStatusToNumber($status)
    {
        $statusMap = [
            'pending' => 1,      // Pendente
            'confirmed' => 2,    // Confirmado
            'shipped' => 3,      // Enviado
            'delivered' => 4,    // Entregue
            'cancelled' => 5     // Cancelado
        ];

        return $statusMap[$status] ?? 1; // Padrão: pendente
    }

    // ========== CATEGORIAS ==========

    /**
     * Listar categorias da API
     */
    public function listCategories($params = [])
    {
        Log::info('Fazendo requisição para listar categorias Plug4Market', [
            'endpoint' => '/categories',
            'metodo' => 'GET'
        ]);

        $response = $this->makeRequest('get', '/categories', $params);
        return $response->json();
    }

    /**
     * Buscar categoria específica
     */
    public function getCategory($categoryId)
    {
        Log::info('Fazendo requisição para buscar categoria Plug4Market', [
            'endpoint' => "/categories/{$categoryId}",
            'metodo' => 'GET',
            'category_id' => $categoryId
        ]);

        $response = $this->makeRequest('get', "/categories/{$categoryId}");
        return $response->json();
    }

    /**
     * Criar categoria
     */
    public function createCategory($data)
    {
        Log::info('Fazendo requisição para criar categoria Plug4Market', [
            'endpoint' => '/categories',
            'metodo' => 'POST',
            'dados_categoria' => array_intersect_key($data, array_flip(['name', 'description', 'parent_id', 'is_active']))
        ]);

        $categoryData = $this->formatCategoryData($data);
        $response = $this->makeRequest('post', '/categories', $categoryData);
        return $response->json();
    }

    /**
     * Atualizar categoria
     */
    public function updateCategory($categoryId, $data)
    {
        Log::info('Fazendo requisição para atualizar categoria Plug4Market', [
            'endpoint' => "/categories/{$categoryId}",
            'metodo' => 'PUT',
            'category_id' => $categoryId,
            'dados_categoria' => array_intersect_key($data, array_flip(['name', 'description', 'parent_id', 'is_active']))
        ]);

        $categoryData = $this->formatCategoryData($data, true);
        $response = $this->makeRequest('put', "/categories/{$categoryId}", $categoryData);
        return $response->json();
    }

    /**
     * Deletar categoria
     */
    public function deleteCategory($categoryId)
    {
        Log::info('Fazendo requisição para deletar categoria Plug4Market', [
            'endpoint' => "/categories/{$categoryId}",
            'metodo' => 'DELETE',
            'category_id' => $categoryId
        ]);

        $response = $this->makeRequest('delete', "/categories/{$categoryId}");
        return $response->json();
    }

    /**
     * Formatar dados da categoria para a API
     */
    protected function formatCategoryData($data, $isUpdate = false)
    {
        $categoryData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'isActive' => $data['is_active'] ?? true
        ];

        // Se tem categoria pai
        if (!empty($data['parent_id'])) {
            $categoryData['parentId'] = $data['parent_id'];
        }

        // Campos adicionais para atualização
        if ($isUpdate) {
            $categoryData['level'] = $data['level'] ?? 0;
            $categoryData['path'] = $data['path'] ?? '';
        }

        Log::info('Dados da categoria formatados para API Plug4Market', [
            'dados_originais' => array_intersect_key($data, array_flip(['name', 'description', 'is_active', 'parent_id', 'level', 'path'])),
            'dados_formatados' => $categoryData,
            'is_update' => $isUpdate,
            'campos_obrigatorios_presentes' => [
                'name' => isset($categoryData['name']) && !empty($categoryData['name']),
                'description' => isset($categoryData['description']),
                'isActive' => isset($categoryData['isActive'])
            ]
        ]);

        return $categoryData;
    }

    // ========== CANAIS DE VENDA ==========

    public function listSalesChannels()
    {
        $response = $this->makeRequest('get', '/sales-channels');
        return $response->json();
    }

    /**
     * Testa conectividade básica sem autenticação
     */
    public function testBasicConnectivity()
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/products');
            
            Log::info('Teste de conectividade básica Plug4Market', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200)
            ]);
            
            return $response->status() !== 0; // Qualquer resposta é melhor que timeout
        } catch (\Exception $e) {
            Log::error('Erro na conectividade básica Plug4Market: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Testa se o token está funcionando corretamente
     */
    public function testTokenAuthentication()
    {
        try {
            if (!$this->accessToken) {
                Log::error('Token de acesso não configurado para teste de autenticação');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(15)->get($this->baseUrl . '/products');

            Log::info('Teste de autenticação do token Plug4Market', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
                'token_length' => strlen($this->accessToken),
                'token_preview' => substr($this->accessToken, 0, 50) . '...',
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                Log::info('Autenticação do token Plug4Market bem-sucedida');
                return true;
            } else {
                Log::error('Falha na autenticação do token Plug4Market', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error_details' => [
                        'is_401' => $response->status() === 401,
                        'is_403' => $response->status() === 403,
                        'is_500' => $response->status() >= 500,
                        'response_headers' => $response->headers()
                    ]
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Erro no teste de autenticação do token: ' . $e->getMessage(), [
                'exception' => $e,
                'token_length' => $this->accessToken ? strlen($this->accessToken) : 0,
                'base_url' => $this->baseUrl
            ]);
            return false;
        }
    }

    // ========== ETIQUETAS DE PEDIDOS ==========

    /**
     * Criar etiqueta de pedido
     */
    public function createLabelOrder($data)
    {
        Log::info('Fazendo requisição para criar etiqueta de pedido Plug4Market', [
            'endpoint' => '/orders/labels',
            'metodo' => 'POST',
            'dados_etiqueta' => array_intersect_key($data, array_flip(['orderId', 'shippingCompany', 'shippingService', 'trackingCode']))
        ]);

        try {
            $labelData = $this->formatLabelData($data);
            
            Log::info('Dados formatados para API Plug4Market - Etiqueta', [
                'dados_formatados' => $labelData,
                'tamanho_dados' => count($labelData)
            ]);
            
            $response = $this->makeRequest('post', '/orders/labels', $labelData);
            $responseData = $response->json();
            
            Log::info('Resposta da API Plug4Market ao criar etiqueta', [
                'status_code' => $response->status(),
                'response_data' => $responseData,
                'tem_id' => isset($responseData['id']),
                'tem_erro' => isset($responseData['error']) || isset($responseData['message'])
            ]);
            
            return $responseData;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar etiqueta Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Listar etiquetas de pedidos
     */
    public function listLabelOrders($params = [])
    {
        Log::info('Fazendo requisição para listar etiquetas de pedidos Plug4Market', [
            'endpoint' => '/orders/labels',
            'metodo' => 'GET',
            'params' => $params
        ]);

        $response = $this->makeRequest('get', '/orders/labels', $params);
        return $response->json();
    }

    /**
     * Buscar etiqueta específica
     */
    public function getLabelOrder($labelId)
    {
        Log::info('Fazendo requisição para buscar etiqueta Plug4Market', [
            'endpoint' => "/orders/labels/{$labelId}",
            'metodo' => 'GET',
            'label_id' => $labelId
        ]);

        $response = $this->makeRequest('get', "/orders/labels/{$labelId}");
        return $response->json();
    }

    /**
     * Atualizar etiqueta de pedido
     */
    public function updateLabelOrder($labelId, $data)
    {
        Log::info('Fazendo requisição para atualizar etiqueta Plug4Market', [
            'endpoint' => "/orders/labels/{$labelId}",
            'metodo' => 'PUT',
            'label_id' => $labelId,
            'dados_etiqueta' => array_intersect_key($data, array_flip(['shippingCompany', 'shippingService', 'trackingCode']))
        ]);

        $labelData = $this->formatLabelData($data, true);
        $response = $this->makeRequest('put', "/orders/labels/{$labelId}", $labelData);
        return $response->json();
    }

    /**
     * Deletar etiqueta de pedido
     */
    public function deleteLabelOrder($labelId)
    {
        Log::info('Fazendo requisição para deletar etiqueta Plug4Market', [
            'endpoint' => "/orders/labels/{$labelId}",
            'metodo' => 'DELETE',
            'label_id' => $labelId
        ]);

        $response = $this->makeRequest('delete', "/orders/labels/{$labelId}");
        return $response->json();
    }

    /**
     * Formatar dados da etiqueta para a API
     */
    protected function formatLabelData($data, $isUpdate = false)
    {
        $labelData = [
            'orderId' => $data['orderId'],
            'shippingCompany' => $data['shippingCompany'],
            'shippingService' => $data['shippingService'],
            'trackingCode' => $data['trackingCode'] ?? null
        ];

        // Campos opcionais
        if (!empty($data['shippingDate'])) {
            $labelData['shippingDate'] = $data['shippingDate'];
        }

        if (!empty($data['estimatedDelivery'])) {
            $labelData['estimatedDelivery'] = $data['estimatedDelivery'];
        }

        if (!empty($data['shippingCost'])) {
            $labelData['shippingCost'] = (float) $data['shippingCost'];
        }

        if (!empty($data['notes'])) {
            $labelData['notes'] = $data['notes'];
        }

        Log::info('Dados da etiqueta formatados para API Plug4Market', [
            'dados_originais' => array_intersect_key($data, array_flip(['orderId', 'shippingCompany', 'shippingService', 'trackingCode', 'shippingDate', 'estimatedDelivery', 'shippingCost', 'notes'])),
            'dados_formatados' => $labelData,
            'is_update' => $isUpdate,
            'campos_obrigatorios_presentes' => [
                'orderId' => isset($labelData['orderId']),
                'shippingCompany' => isset($labelData['shippingCompany']),
                'shippingService' => isset($labelData['shippingService'])
            ]
        ]);

        return $labelData;
    }

    // ========== UTILITÁRIOS ==========

    public function getTokenInfo()
    {
        return [
            'user_token' => $this->userToken ? 'Configurado' : 'Não configurado',
            'access_token' => $this->accessToken ? 'Configurado' : 'Não configurado',
            'refresh_token' => $this->refreshToken ? 'Configurado' : 'Não configurado',
            'base_url' => $this->baseUrl,
            'sandbox' => $this->isSandbox,
            'seller_id' => $this->settings->seller_id ?? 'Não configurado',
            'software_house_cnpj' => $this->settings->software_house_cnpj ?? 'Não configurado',
            'store_cnpj' => $this->settings->store_cnpj ?? 'Não configurado',
            'user_id' => $this->settings->user_id ?? 'Não configurado'
        ];
    }

    /**
     * Método para validar se o token JWT é válido
     */
    public function validateToken()
    {
        if (!$this->accessToken) {
            return false;
        }

        try {
            // Decodificar o JWT para verificar se é válido
            $tokenParts = explode('.', $this->accessToken);
            
            if (count($tokenParts) !== 3) {
                return false;
            }

            $payload = json_decode(base64_decode($tokenParts[1]), true);
            
            if (!$payload) {
                return false;
            }

            // Verificar se o token não expirou
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao validar token JWT: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Baixar XML da nota fiscal de um pedido
     */
    public function downloadInvoiceXml($orderId)
    {
        Log::info('Fazendo requisição para baixar XML da nota fiscal Plug4Market', [
            'endpoint' => "/orders/{$orderId}/invoice/xml",
            'metodo' => 'GET',
            'order_id' => $orderId
        ]);

        try {
            $response = $this->makeRequest('get', "/orders/{$orderId}/invoice/xml");
            
            if ($response->successful()) {
                $xmlContent = $response->body();
                
                Log::info('XML da nota fiscal baixado com sucesso', [
                    'order_id' => $orderId,
                    'xml_size' => strlen($xmlContent),
                    'content_type' => $response->header('Content-Type')
                ]);
                
                return [
                    'success' => true,
                    'xml_content' => $xmlContent,
                    'content_type' => $response->header('Content-Type'),
                    'filename' => "nfe_{$orderId}.xml"
                ];
            } else {
                Log::error('Erro ao baixar XML da nota fiscal', [
                    'order_id' => $orderId,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Erro ao baixar XML: ' . $response->status(),
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao baixar XML da nota fiscal', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Exceção ao baixar XML: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar se o pedido tem nota fiscal disponível
     */
    public function checkInvoiceAvailability($orderId)
    {
        Log::info('Verificando disponibilidade da nota fiscal Plug4Market', [
            'endpoint' => "/orders/{$orderId}/invoice",
            'metodo' => 'GET',
            'order_id' => $orderId
        ]);

        try {
            $response = $this->makeRequest('get', "/orders/{$orderId}/invoice");
            
            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Verificação de disponibilidade da nota fiscal', [
                    'order_id' => $orderId,
                    'has_invoice' => isset($data['number']),
                    'invoice_status' => $data['status'] ?? 'unknown',
                    'has_xml' => isset($data['xml_available']) ? $data['xml_available'] : false
                ]);
                
                return [
                    'success' => true,
                    'has_invoice' => isset($data['number']),
                    'invoice_data' => $data,
                    'xml_available' => isset($data['xml_available']) ? $data['xml_available'] : false
                ];
            } else {
                Log::error('Erro ao verificar disponibilidade da nota fiscal', [
                    'order_id' => $orderId,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Erro ao verificar disponibilidade: ' . $response->status(),
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao verificar disponibilidade da nota fiscal', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Exceção ao verificar disponibilidade: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processar e salvar XML da nota fiscal para um pedido
     */
    public function processInvoiceXml($orderId)
    {
        Log::info('Iniciando processamento do XML da nota fiscal', [
            'order_id' => $orderId
        ]);

        try {
            // Verificar se o pedido existe
            $order = \App\Models\Plug4MarketOrder::find($orderId);
            if (!$order) {
                Log::error('Pedido não encontrado para processamento do XML', [
                    'order_id' => $orderId
                ]);
                return [
                    'success' => false,
                    'error' => 'Pedido não encontrado'
                ];
            }

            // Verificar se já tem XML baixado
            if ($order->hasInvoiceXml()) {
                Log::info('XML já foi baixado anteriormente', [
                    'order_id' => $orderId,
                    'downloaded_at' => $order->invoice_xml_downloaded_at
                ]);
                return [
                    'success' => true,
                    'message' => 'XML já foi baixado anteriormente',
                    'downloaded_at' => $order->invoice_xml_downloaded_at
                ];
            }

            // Verificar se tem nota fiscal
            if (!$order->hasInvoice()) {
                Log::warning('Pedido não tem nota fiscal para baixar XML', [
                    'order_id' => $orderId
                ]);
                return [
                    'success' => false,
                    'error' => 'Pedido não tem nota fiscal'
                ];
            }

            // Baixar XML da API
            $downloadResult = $this->downloadInvoiceXml($order->external_id);
            
            if (!$downloadResult['success']) {
                $order->markInvoiceXmlError($downloadResult['error']);
                
                Log::error('Falha ao baixar XML da API', [
                    'order_id' => $orderId,
                    'error' => $downloadResult['error']
                ]);
                
                return $downloadResult;
            }

            // Salvar XML no banco
            $xmlSaved = $order->saveInvoiceXml(
                $downloadResult['xml_content'],
                $downloadResult['filename']
            );

            if ($xmlSaved) {
                Log::info('XML da nota fiscal processado com sucesso', [
                    'order_id' => $orderId,
                    'filename' => $order->invoice_xml_filename,
                    'xml_size' => strlen($downloadResult['xml_content'])
                ]);
                
                return [
                    'success' => true,
                    'message' => 'XML processado com sucesso',
                    'filename' => $order->invoice_xml_filename,
                    'xml_size' => strlen($downloadResult['xml_content'])
                ];
            } else {
                Log::error('Falha ao salvar XML no banco', [
                    'order_id' => $orderId
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Falha ao salvar XML no banco'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exceção ao processar XML da nota fiscal', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Exceção ao processar XML: ' . $e->getMessage()
            ];
        }
    }
} 