<?php

namespace App\Utils;

use Automattic\WooCommerce\Client;
use App\Models\WooCommerceConfig;

class WooCommerceUtil
{
    protected $woocommerce;

    public function __construct($empresa_id)
    {
        $config = WooCommerceConfig::where('empresa_id', $empresa_id)->first();
        
        if ($config) {
            $this->woocommerce = new Client(
                $config->store_url,
                $config->consumer_key,
                $config->consumer_secret,
                [
                    'version' => 'wc/v3',
                    'verify_ssl' => false
                ]
            );
        }
    }

    public function createProduct($data)
    {
        return $this->woocommerce->post('products', $data);
    }

    public function updateProduct($id, $data)
    {
        return $this->woocommerce->put("products/{$id}", $data);
    }

    public function getProduct($id)
    {
        return $this->woocommerce->get("products/{$id}");
    }

    public function getProducts($params = [])
    {
        return $this->woocommerce->get('products', $params);
    }

    public function getCategories($params = [])
    {
        return $this->woocommerce->get('products/categories', $params);
    }

    public function createCategory($data)
    {
        return $this->woocommerce->post('products/categories', $data);
    }

    public function updateCategory($id, $data)
    {
        return $this->woocommerce->put("products/categories/{$id}", $data);
    }

    public function getOrders($params = [])
    {
        return $this->woocommerce->get('orders', $params);
    }

    public function getOrder($id)
    {
        return $this->woocommerce->get("orders/{$id}");
    }

    public function updateOrder($id, $data)
    {
        return $this->woocommerce->put("orders/{$id}", $data);
    }

    public function syncProduct($produto)
    {
        $data = [
            'name' => $produto->nome,
            'type' => 'simple',
            'regular_price' => (string)$produto->valor_venda,
            'description' => $produto->descricao ?? '',
            'short_description' => $produto->descricao_curta ?? substr($produto->nome, 0, 100),
            'stock_quantity' => $produto->estoque ? (int)$produto->estoque->quantidade : 0,
            'manage_stock' => $produto->gerenciar_estoque ? true : false,
            'status' => 'publish'
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

        return $data;
    }

    public function syncStock($produto)
    {
        if (!$produto->gerenciar_estoque) {
            return null;
        }

        $data = [
            'stock_quantity' => (int)$produto->estoque->quantidade,
            'manage_stock' => true
        ];

        return $this->woocommerce->put("products/{$produto->woocommerce->woocommerce_id}", $data);
    }

    public function syncOrder($pedido)
    {
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
            ]
        ];

        return $this->woocommerce->put("orders/{$pedido->woocommerce_id}", $data);
    }

    private function mapOrderStatus($status)
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
}
