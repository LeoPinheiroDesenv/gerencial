<?php

namespace App\Services;

use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Log;
use App\Models\WooCommerceConfig;
use App\Models\Produto;
use App\Models\ProdutoWooCommerce;
use App\Models\WooCommercePedido;
use App\Models\WooCommerceItemPedido;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use App\Models\PedidoItem;

class WooCommerceService
{
    protected $woocommerce;
    protected $empresa_id;
    protected $config;

    public function __construct()
    {
        $this->woocommerce = new Client(
            config('services.woocommerce.store_url'),
            config('services.woocommerce.consumer_key'),
            config('services.woocommerce.consumer_secret'),
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'verify_ssl' => false // Use true em produção
            ]
        );
    }

    public function initialize($empresa_id)
    {
        try {
            if (!$empresa_id) {
                Log::error('empresa_id não fornecido ao inicializar WooCommerceService');
                return $this;
            }

            $this->empresa_id = $empresa_id;
            $this->config = WooCommerceConfig::where('empresa_id', $empresa_id)->first();

            if (!$this->config) {
                Log::warning('Configuração do WooCommerce não encontrada para empresa_id: ' . $empresa_id);
                return $this;
            }

            if (!$this->config->store_url || !$this->config->consumer_key || !$this->config->consumer_secret) {
                Log::warning('Configuração do WooCommerce incompleta para empresa_id: ' . $empresa_id);
                return $this;
            }

            // Remove barras finais da URL se existirem
            $store_url = rtrim($this->config->store_url, '/');

            $this->woocommerce = new Client(
                $store_url,
                $this->config->consumer_key,
                $this->config->consumer_secret,
                [
                    'version' => 'wc/v3',
                    'verify_ssl' => false,
                    'timeout' => 30
                ]
            );

            // Testa a conexão
            try {
                $this->woocommerce->get('system_status');
                Log::info('Conexão com WooCommerce estabelecida com sucesso para empresa_id: ' . $empresa_id);
            } catch (\Exception $e) {
                Log::error('Erro ao testar conexão com WooCommerce: ' . $e->getMessage());
                $this->woocommerce = null;
            }

            return $this;
        } catch (\Throwable $th) {
            Log::error('Erro ao inicializar WooCommerce: ' . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Verifica se a conexão está ativa
     */
    public function isConnected()
    {
        return $this->woocommerce !== null && $this->config !== null && $this->empresa_id !== null;
    }

    /**
     * Produtos
     */
    public function getProducts($params = [])
    {
        try {
            return $this->woocommerce->get('products', $params);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos do WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getProduct($id)
    {
        try {
            return $this->woocommerce->get("products/{$id}");
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto do WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createProduct($data)
    {
        try {
            return $this->woocommerce->post('products', $data);
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto no WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateProduct($id, $data)
    {
        try {
            return $this->woocommerce->put("products/{$id}", $data);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto no WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteProduct($id)
    {
        try {
            return $this->woocommerce->delete("products/{$id}");
        } catch (\Exception $e) {
            Log::error('Erro ao deletar produto no WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Categorias
     */
    public function getCategories($params = [])
    {
        try {
            return $this->woocommerce->get('products/categories', $params);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar categorias do WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createCategory($data)
    {
        try {
            return $this->woocommerce->post('products/categories', $data);
        } catch (\Exception $e) {
            Log::error('Erro ao criar categoria no WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateCategory($id, $data)
    {
        try {
            return $this->woocommerce->put("products/categories/{$id}", $data);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar categoria no WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Pedidos
     */
    public function getOrders($params = [])
    {
        try {
            return $this->woocommerce->get('orders', $params);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar pedidos do WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getOrder($id)
    {
        try {
            return $this->woocommerce->get("orders/{$id}");
        } catch (\Exception $e) {
            Log::error('Erro ao buscar pedido do WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createOrder($data)
    {
        try {
            return $this->woocommerce->post('orders', $data);
        } catch (\Exception $e) {
            Log::error('Erro ao criar pedido no WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateOrder($id, $data)
    {
        try {
            return $this->woocommerce->put("orders/{$id}", $data);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar pedido no WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sincronização de Produtos
     */
    public function syncProduct($produto)
    {
        if (!$this->isConnected()) {
            throw new \Exception('Conexão com WooCommerce não configurada');
        }

        try {
            DB::beginTransaction();

            $data = [
                'name' => $produto->nome,
                'type' => 'simple',
                'regular_price' => (string)$produto->valor_venda,
                'description' => $produto->descricao ?? '',
                'short_description' => $produto->descricao_curta ?? substr($produto->nome, 0, 100),
                'stock_quantity' => $produto->estoque ? (int)$produto->estoque->quantidade : 0,
                'manage_stock' => $produto->gerenciar_estoque ? true : false,
                'status' => $this->config->default_status ?? 'publish'
            ];

            if ($produto->categoria) {
                $data['categories'] = [
                    ['id' => $produto->categoria->woocommerce_id]
                ];
            }

            if ($produto->imagem) {
                $data['images'] = [
                    ['src' => asset('storage/produtos/' . $produto->imagem)]
                ];
            }

            $produtoWC = ProdutoWooCommerce::where('produto_id', $produto->id)
                ->where('empresa_id', $this->empresa_id)
                ->first();

            if ($produtoWC) {
                $response = $this->updateProduct($produtoWC->woocommerce_id, $data);
            } else {
                $response = $this->createProduct($data);

                ProdutoWooCommerce::create([
                    'produto_id' => $produto->id,
                    'woocommerce_id' => $response->id,
                    'woocommerce_valor' => $response->price,
                    'woocommerce_link' => $response->permalink,
                    'woocommerce_status' => $response->status,
                    'empresa_id' => $this->empresa_id
                ]);
            }

            DB::commit();
            return $response;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao sincronizar produto: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sincronização de Pedidos
     */
    public function syncOrder($pedido)
    {
        if (!$this->isConnected()) {
            throw new \Exception('Conexão com WooCommerce não configurada');
        }

        try {
            DB::beginTransaction();

            $data = [
                'status' => $this->mapOrderStatus($pedido->status),
                'billing' => [
                    'first_name' => $pedido->cliente_nome,
                    'email' => $pedido->cliente_email,
                    'phone' => $pedido->cliente_telefone
                ],
                'shipping' => [
                    'address_1' => $pedido->endereco_entrega,
                    'address_2' => $pedido->bairro_entrega,
                    'city' => $pedido->cidade_entrega,
                    'state' => $pedido->estado_entrega,
                    'postcode' => $pedido->cep_entrega
                ],
                'line_items' => []
            ];

            foreach ($pedido->itens as $item) {
                $produtoWC = ProdutoWooCommerce::where('produto_id', $item->produto_id)
                    ->where('empresa_id', $this->empresa_id)
                    ->first();

                if ($produtoWC) {
                    $data['line_items'][] = [
                        'product_id' => $produtoWC->woocommerce_id,
                        'quantity' => $item->quantidade,
                        'price' => $item->valor_unitario
                    ];
                }
            }

            $pedidoWC = WooCommercePedido::where('pedido_id', $pedido->id)
                ->where('empresa_id', $this->empresa_id)
                ->first();

            if ($pedidoWC) {
                $response = $this->woocommerce->put("orders/{$pedidoWC->woocommerce_id}", $data);
            } else {
                $response = $this->woocommerce->post("orders", $data);

                WooCommercePedido::create([
                    'pedido_id' => $pedido->id,
                    'empresa_id' => $this->empresa_id,
                    'woocommerce_id' => $response->id,
                    'cliente_nome' => $pedido->cliente_nome,
                    'cliente_email' => $pedido->cliente_email,
                    'cliente_telefone' => $pedido->cliente_telefone,
                    'status' => $pedido->status,
                    'total' => $pedido->total,
                    'forma_pagamento' => $pedido->forma_pagamento,
                    'forma_envio' => $pedido->forma_envio,
                    'endereco_entrega' => $pedido->endereco_entrega,
                    'bairro_entrega' => $pedido->bairro_entrega,
                    'cidade_entrega' => $pedido->cidade_entrega,
                    'estado_entrega' => $pedido->estado_entrega,
                    'cep_entrega' => $pedido->cep_entrega,
                    'observacao' => $pedido->observacao,
                    'data_pedido' => $pedido->created_at
                ]);
            }

            DB::commit();
            return $response;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao sincronizar pedido: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mapeia o status do pedido para o formato do WooCommerce
     */
    private function mapOrderStatusToWooCommerce($status)
    {
        $statusMap = [
            'pendente' => 'pending',
            'aprovado' => 'processing',
            'enviado' => 'completed',
            'cancelado' => 'cancelled',
            'reembolsado' => 'refunded'
        ];
        
        return $statusMap[$status] ?? 'pending';
    }

    /**
     * Mapeia o status do pedido do WooCommerce para o formato do sistema
     */
    private function mapOrderStatus($status)
    {
        $statusMap = [
            'pending' => 'pendente',
            'processing' => 'aprovado',
            'completed' => 'enviado',
            'cancelled' => 'cancelado',
            'refunded' => 'reembolsado'
        ];
        
        return $statusMap[$status] ?? 'pendente';
    }

    public function sincronizarProduto($produto)
    {
        try {
            if (!$this->empresa_id) {
                Log::error('empresa_id não definido ao sincronizar produto');
                return [
                    'success' => false,
                    'message' => 'empresa_id não definido'
                ];
            }

            if (!$this->isConnected()) {
                return [
                    'success' => false,
                    'message' => 'Configuração do WooCommerce não encontrada ou inválida'
                ];
            }

            if (!$this->config->sync_products) {
                return [
                    'success' => false,
                    'message' => 'Sincronização de produtos está desativada'
                ];
            }

            Log::info('Iniciando sincronização do produto: ' . $produto->id);

            // Calcula o preço com markup
            $preco = $produto->valor_venda;
            if ($this->config->price_markup > 0) {
                $preco = $preco * (1 + ($this->config->price_markup / 100));
            }

            // Gera um SKU único baseado no ID do produto e empresa
            $sku = $produto->referencia ?: 'WC-' . $this->empresa_id . '-' . $produto->id;

            $data = [
                'name' => $produto->nome,
                'type' => 'simple',
                'regular_price' => (string)number_format($preco, 2, '.', ''),
                'description' => $produto->descricao ?? '',
                'short_description' => $produto->descricao_curta ?? '',
                'status' => 'publish',
                'manage_stock' => true,
                'stock_quantity' => $produto->estoque ? (int)$produto->estoque->quantidade : 0,
                'sku' => $sku,
                'catalog_visibility' => 'visible'
            ];

            // Adiciona imagem se existir
            if ($produto->imagem) {
                $data['images'] = [
                    [
                        'src' => asset('storage/produtos/' . $produto->imagem),
                        'position' => 0
                    ]
                ];
            }

            // Se o produto já existe no WooCommerce, atualiza
            $produtoWooCommerce = ProdutoWooCommerce::where('produto_id', $produto->id)
                ->where('empresa_id', $this->empresa_id)
                ->first();

            if ($produtoWooCommerce) {
                Log::info('Atualizando produto existente no WooCommerce: ' . $produtoWooCommerce->woocommerce_id);
                $result = $this->woocommerce->put("products/{$produtoWooCommerce->woocommerce_id}", $data);
            } else {
                Log::info('Criando novo produto no WooCommerce');
                // Se não existe, cria novo
                $result = $this->woocommerce->post("products", $data);

                // Salva o ID do produto no WooCommerce
                ProdutoWooCommerce::create([
                    'produto_id' => $produto->id,
                    'empresa_id' => $this->empresa_id,
                    'woocommerce_id' => $result->id,
                    'woocommerce_status' => $result->status,
                    'woocommerce_valor' => $result->price,
                    'woocommerce_link' => $result->permalink
                ]);
            }

            Log::info('Produto sincronizado com sucesso: ' . $produto->id);

            return [
                'success' => true,
                'message' => 'Produto sincronizado com sucesso'
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar produto com WooCommerce: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao sincronizar produto: ' . $e->getMessage()
            ];
        }
    }

    private function debugConfig()
    {
        Log::info('Debug WooCommerce Config:', [
            'empresa_id' => $this->empresa_id,
            'config_exists' => !is_null($this->config),
            'config_data' => $this->config ? $this->config->toArray() : null
        ]);
    }

    public function sincronizarTudo($tipo = 'tudo')
    {
        try {
            if (!$this->empresa_id) {
                Log::error('empresa_id não definido ao sincronizar tudo');
                return [
                    'success' => false,
                    'message' => 'empresa_id não definido'
                ];
            }

            $this->debugConfig();

            if (!$this->config) {
                Log::warning('WooCommerce Config não encontrada para empresa_id: ' . $this->empresa_id);
                return [
                    'success' => false,
                    'message' => 'Configuração do WooCommerce não encontrada'
                ];
            }

            DB::beginTransaction();

            // Sincroniza produtos apenas se solicitado e se a configuração permitir
            if (($tipo === 'tudo' || $tipo === 'produtos') && $this->config->sync_products) {
                Log::info('Iniciando sincronização de produtos para empresa_id: ' . $this->empresa_id);
                
                // Primeiro, busca os produtos do WooCommerce
                $wooProducts = $this->woocommerce->get('products', ['per_page' => 100]);
                Log::info('Produtos encontrados no WooCommerce: ' . count($wooProducts));

                // Busca a categoria padrão
                $categoriaPadrao = \App\Models\Categoria::where('empresa_id', $this->empresa_id)
                    ->where('nome', 'WooCommerce')
                    ->first();

                // Se não existir, cria a categoria padrão
                if (!$categoriaPadrao) {
                    $categoriaPadrao = \App\Models\Categoria::create([
                        'nome' => 'WooCommerce',
                        'empresa_id' => $this->empresa_id
                    ]);
                }

                // Atualiza ou cria os produtos no banco local
                foreach ($wooProducts as $wooProduct) {
                    $produtoWooCommerce = ProdutoWooCommerce::where('woocommerce_id', $wooProduct->id)
                        ->where('empresa_id', $this->empresa_id)
                        ->first();

                    if (!$produtoWooCommerce) {
                        // Cria o produto local primeiro
                        $produto = Produto::create([
                            'nome' => $wooProduct->name,
                            'valor_venda' => $wooProduct->price,
                            'descricao' => $wooProduct->description,
                            'descricao_curta' => $wooProduct->short_description,
                            'referencia' => $wooProduct->sku ?? 'WC-' . $wooProduct->id,
                            'empresa_id' => $this->empresa_id,
                            'inativo' => false,
                            'gerenciar_estoque' => true,
                            'estoque_minimo' => 0,
                            'categoria_id' => $categoriaPadrao->id,
                            'unidade_compra' => 'UN',
                            'unidade_venda' => 'UN',
                            'conversao_unitaria' => 1,
                            'valor_livre' => false,
                            'composto' => false,
                            'codBarras' => 'SEM GTIN',
                            'NCM' => '00000000',
                            'CST_CSOSN' => '102',
                            'CST_PIS' => '01',
                            'CST_COFINS' => '01',
                            'CST_IPI' => '53',
                            'CFOP_saida_estadual' => '5102',
                            'CFOP_saida_inter_estadual' => '6102'
                        ]);

                        // Agora cria o registro na tabela produto_woocommerce
                        ProdutoWooCommerce::create([
                            'produto_id' => $produto->id,
                            'empresa_id' => $this->empresa_id,
                            'woocommerce_id' => $wooProduct->id,
                            'woocommerce_status' => $wooProduct->status,
                            'woocommerce_valor' => $wooProduct->price,
                            'woocommerce_link' => $wooProduct->permalink
                        ]);
                    }
                }

                // Agora sincroniza os produtos locais para o WooCommerce
                $produtos = Produto::where('empresa_id', $this->empresa_id)
                    ->where('inativo', false)
                    ->get();

                Log::info('Produtos locais encontrados: ' . count($produtos));

                foreach ($produtos as $produto) {
                    $result = $this->sincronizarProduto($produto);
                    if (!$result['success']) {
                        Log::error('Erro ao sincronizar produto ' . $produto->id . ': ' . $result['message']);
                    }
                }
            }

            // Sincroniza pedidos apenas se solicitado e se a configuração permitir
            if (($tipo === 'tudo' || $tipo === 'pedidos') && $this->config->sync_orders) {
                Log::info('Iniciando sincronização de pedidos para empresa_id: ' . $this->empresa_id);
                
                // Primeiro, busca os pedidos do WooCommerce
                $wooOrders = $this->woocommerce->get('orders', ['per_page' => 100]);
                Log::info('Pedidos encontrados no WooCommerce: ' . count($wooOrders));

                // Atualiza ou cria os pedidos no banco local
                foreach ($wooOrders as $wooOrder) {
                    $pedidoWooCommerce = WooCommercePedido::where('woocommerce_id', $wooOrder->id)
                        ->where('empresa_id', $this->empresa_id)
                        ->first();

                    if (!$pedidoWooCommerce) {
                        // Pega o método de envio do primeiro item de shipping_lines
                        $shippingMethod = '';
                        if (!empty($wooOrder->shipping_lines)) {
                            $shippingMethod = $wooOrder->shipping_lines[0]->method_title ?? '';
                        }

                        WooCommercePedido::create([
                            'pedido_id' => null, // Será atualizado quando o pedido for criado
                            'empresa_id' => $this->empresa_id,
                            'woocommerce_id' => $wooOrder->id,
                            'cliente_nome' => $wooOrder->billing->first_name . ' ' . $wooOrder->billing->last_name,
                            'cliente_email' => $wooOrder->billing->email,
                            'cliente_telefone' => $wooOrder->billing->phone,
                            'status' => $wooOrder->status,
                            'total' => $wooOrder->total,
                            'forma_pagamento' => $wooOrder->payment_method_title ?? '',
                            'forma_envio' => $shippingMethod,
                            'endereco_entrega' => $wooOrder->shipping->address_1 ?? '',
                            'bairro_entrega' => $wooOrder->shipping->address_2 ?? '',
                            'cidade_entrega' => $wooOrder->shipping->city ?? '',
                            'estado_entrega' => $wooOrder->shipping->state ?? '',
                            'cep_entrega' => $wooOrder->shipping->postcode ?? '',
                            'observacao' => $wooOrder->customer_note ?? '',
                            'data_pedido' => $wooOrder->date_created
                        ]);
                    }
                }

                // Agora sincroniza os pedidos locais para o WooCommerce
                $pedidos = Pedido::where('empresa_id', $this->empresa_id)
                    ->where('status', '!=', 'cancelado')
                    ->get();

                Log::info('Pedidos locais encontrados: ' . count($pedidos));

                foreach ($pedidos as $pedido) {
                    try {
                        $this->syncOrder($pedido);
                    } catch (\Exception $e) {
                        Log::error('Erro ao sincronizar pedido ' . $pedido->id . ': ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Sincronização concluída com sucesso'
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao sincronizar com WooCommerce: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao sincronizar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Importar produtos do WooCommerce
     */
    public function importarProdutos($empresa_id)
    {
        try {
            if (!$this->isConnected()) {
                throw new \Exception('Conexão com WooCommerce não configurada');
            }

            // Busca a categoria padrão
            $categoriaPadrao = \App\Models\Categoria::where('empresa_id', $empresa_id)
                ->where('nome', 'WooCommerce')
                ->first();

            // Se não existir, cria a categoria padrão
            if (!$categoriaPadrao) {
                $categoriaPadrao = \App\Models\Categoria::create([
                    'nome' => 'WooCommerce',
                    'empresa_id' => $empresa_id
                ]);
            }

            $produtos = $this->woocommerce->get('products', ['per_page' => 100]);
            
            foreach ($produtos as $produto) {
                // Verifica se o produto já existe
                $produtoExistente = Produto::where('empresa_id', $empresa_id)
                    ->where('referencia', $produto->sku)
                    ->first();

                if (!$produtoExistente) {
                    // Cria novo produto
                    $novoProduto = Produto::create([
                        'nome' => $produto->name,
                        'valor_venda' => $produto->price,
                        'descricao' => $produto->description,
                        'descricao_curta' => $produto->short_description,
                        'referencia' => $produto->sku ?? 'WC-' . $produto->id,
                        'empresa_id' => $empresa_id,
                        'inativo' => false,
                        'gerenciar_estoque' => true,
                        'estoque_minimo' => 0,
                        'categoria_id' => $categoriaPadrao->id,
                        'unidade_compra' => 'UN',
                        'unidade_venda' => 'UN',
                        'conversao_unitaria' => 1,
                        'valor_livre' => false,
                        'composto' => false,
                        'codBarras' => 'SEM GTIN',
                        'NCM' => '00000000',
                        'CST_CSOSN' => '102',
                        'CST_PIS' => '01',
                        'CST_COFINS' => '01',
                        'CST_IPI' => '53',
                        'CFOP_saida_estadual' => '5102',
                        'CFOP_saida_inter_estadual' => '6102'
                    ]);

                    // Registra o produto no WooCommerce
                    ProdutoWooCommerce::create([
                        'produto_id' => $novoProduto->id,
                        'empresa_id' => $empresa_id,
                        'woocommerce_id' => $produto->id,
                        'woocommerce_status' => $produto->status,
                        'woocommerce_valor' => $produto->price,
                        'woocommerce_link' => $produto->permalink
                    ]);

                    // Se o produto tem estoque, atualiza
                    if ($produto->stock_quantity !== null) {
                        $estoque = new \App\Models\Estoque();
                        $estoque->produto_id = $novoProduto->id;
                        $estoque->quantidade = $produto->stock_quantity;
                        $estoque->empresa_id = $empresa_id;
                        $estoque->save();
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao importar produtos do WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Importar pedidos do WooCommerce
     */
    public function importarPedidos($empresa_id)
    {
        try {
            $pedidos = $this->woocommerce->get('orders');
            foreach ($pedidos as $pedido) {
                // Verifica se o pedido já existe
                $pedidoExistente = Pedido::where('empresa_id', $empresa_id)
                    ->where('woocommerce_id', $pedido->id)
                    ->first();

                if (!$pedidoExistente) {
                    // Cria novo pedido
                    $novoPedido = new Pedido();
                    $novoPedido->empresa_id = $empresa_id;
                    $novoPedido->woocommerce_id = $pedido->id;
                    $novoPedido->status = $this->mapOrderStatus($pedido->status);
                    $novoPedido->valor_total = $pedido->total;
                    $novoPedido->save();

                    // Importa os itens do pedido
                    foreach ($pedido->line_items as $item) {
                        $produto = Produto::where('empresa_id', $empresa_id)
                            ->where('sku', $item->sku)
                            ->first();

                        if ($produto) {
                            $pedidoItem = new PedidoItem();
                            $pedidoItem->pedido_id = $novoPedido->id;
                            $pedidoItem->produto_id = $produto->id;
                            $pedidoItem->quantidade = $item->quantity;
                            $pedidoItem->valor_unitario = $item->price;
                            $pedidoItem->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao importar pedidos do WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exportar produtos para o WooCommerce
     */
    public function exportarProdutos($empresa_id)
    {
        try {
            $produtos = Produto::where('empresa_id', $empresa_id)->get();
            foreach ($produtos as $produto) {
                // Verifica se o produto já existe no WooCommerce
                $produtoWoo = ProdutoWooCommerce::where('produto_id', $produto->id)->first();
                
                if (!$produtoWoo) {
                    // Cria novo produto no WooCommerce
                    $data = [
                        'name' => $produto->nome,
                        'type' => 'simple',
                        'regular_price' => (string)$produto->valor_venda,
                        'sku' => $produto->sku,
                        'status' => 'publish'
                    ];
                    
                    $response = $this->woocommerce->post('products', $data);
                    
                    // Registra o produto no WooCommerce
                    $produtoWoo = new ProdutoWooCommerce();
                    $produtoWoo->produto_id = $produto->id;
                    $produtoWoo->woocommerce_id = $response->id;
                    $produtoWoo->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao exportar produtos para o WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exportar pedidos para o WooCommerce
     */
    public function exportarPedidos($empresa_id)
    {
        try {
            $pedidos = Pedido::where('empresa_id', $empresa_id)
                ->whereNull('woocommerce_id')
                ->get();

            foreach ($pedidos as $pedido) {
                // Prepara os dados do pedido
                $data = [
                    'status' => $this->mapOrderStatusToWooCommerce($pedido->status),
                    'line_items' => []
                ];

                // Adiciona os itens do pedido
                foreach ($pedido->itens as $item) {
                    $produtoWoo = ProdutoWooCommerce::where('produto_id', $item->produto_id)->first();
                    if ($produtoWoo) {
                        $data['line_items'][] = [
                            'product_id' => $produtoWoo->woocommerce_id,
                            'quantity' => $item->quantidade
                        ];
                    }
                }

                // Cria o pedido no WooCommerce
                $response = $this->woocommerce->post('orders', $data);

                // Atualiza o pedido com o ID do WooCommerce
                $pedido->woocommerce_id = $response->id;
                $pedido->save();
            }
        } catch (\Exception $e) {
            Log::error('Erro ao exportar pedidos para o WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }
}
 