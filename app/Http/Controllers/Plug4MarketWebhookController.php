<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class Plug4MarketWebhookController extends Controller
{
    /**
     * Recebe WebHook de novo pedido do Plug4Market
     * Documentação: https://atendimento.tecnospeed.com.br/hc/pt-br/articles/18302073160471-WebHook
     */
    public function orderCreated(Request $request)
    {
        // Validação dos headers obrigatórios
        $requiredHeaders = [
            'x-tecnospeed-event',
            'x-tecnospeed-signature',
            'x-tecnospeed-timestamp',
        ];
        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                Log::warning('Webhook Plug4Market: Header obrigatório ausente', ['header' => $header]);
                return response()->json(['success' => false, 'message' => 'Header obrigatório ausente: ' . $header], 400);
            }
        }

        // Log do recebimento do WebHook
        Log::info('Webhook Plug4Market recebido', [
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);

        // Salvar pedido
        $order = $this->saveOrderFromWebhook($request->all());

        return response()->json(['success' => true, 'message' => 'Pedido recebido e salvo com sucesso', 'order_id' => $order->id]);
    }

    /**
     * Salva o pedido e itens recebidos do Plug4Market via WebHook
     */
    protected function saveOrderFromWebhook(array $payload)
    {
        // 1. Cliente (opcional)
        $clienteId = null;
        if (isset($payload['customer']['document'])) {
            $cliente = \App\Models\Cliente::verificaCadastrado($payload['customer']['document']);
            if (!$cliente) {
                $cliente = \App\Models\Cliente::create([
                    'razao_social' => $payload['customer']['name'] ?? $payload['customer']['document'],
                    'nome_fantasia' => $payload['customer']['name'] ?? $payload['customer']['document'],
                    'cpf_cnpj' => $payload['customer']['document'],
                    'email' => $payload['customer']['email'] ?? null,
                    'telefone' => $payload['customer']['phone'] ?? null,
                    'empresa_id' => 1 // Ajuste conforme sua regra
                ]);
            }
            $clienteId = $cliente->id;
        }

        // 2. Pedido
        $order = new \App\Models\Plug4MarketOrder();
        $order->external_id = $payload['id'] ?? null;
        $order->order_number = $payload['order_number'] ?? null;
        $order->marketplace = $payload['marketplace'] ?? 7;
        $order->status = $payload['status'] ?? 2;
        $order->total_amount = $payload['total_amount'] ?? 0;
        $order->cliente_id = $clienteId;
        $order->raw_data = $payload;
        $order->sincronizado = true;
        $order->ultima_sincronizacao = now();
        // Dados de entrega
        if (isset($payload['shipping'])) {
            $order->shipping_recipient_name = $payload['shipping']['recipientName'] ?? null;
            $order->shipping_phone = $payload['shipping']['phone'] ?? null;
            $order->shipping_street = $payload['shipping']['street'] ?? null;
            $order->shipping_street_number = $payload['shipping']['streetNumber'] ?? null;
            $order->shipping_city = $payload['shipping']['city'] ?? null;
            $order->shipping_street_complement = $payload['shipping']['streetComplement'] ?? null;
            $order->shipping_country = $payload['shipping']['country'] ?? null;
            $order->shipping_district = $payload['shipping']['district'] ?? null;
            $order->shipping_state = $payload['shipping']['state'] ?? null;
            $order->shipping_zip_code = $payload['shipping']['zipCode'] ?? null;
            $order->shipping_ibge = $payload['shipping']['ibge'] ?? null;
        }
        // Dados de cobrança
        if (isset($payload['billing'])) {
            $order->billing_name = $payload['billing']['name'] ?? null;
            $order->billing_email = $payload['billing']['email'] ?? null;
            $order->billing_document_id = $payload['billing']['documentId'] ?? null;
            $order->billing_state_registration_id = $payload['billing']['stateRegistrationId'] ?? null;
            $order->billing_street = $payload['billing']['street'] ?? null;
            $order->billing_street_number = $payload['billing']['streetNumber'] ?? null;
            $order->billing_street_complement = $payload['billing']['streetComplement'] ?? null;
            $order->billing_district = $payload['billing']['district'] ?? null;
            $order->billing_city = $payload['billing']['city'] ?? null;
            $order->billing_state = $payload['billing']['state'] ?? null;
            $order->billing_country = $payload['billing']['country'] ?? null;
            $order->billing_zip_code = $payload['billing']['zipCode'] ?? null;
            $order->billing_phone = $payload['billing']['phone'] ?? null;
            $order->billing_gender = $payload['billing']['gender'] ?? null;
            $order->billing_date_of_birth = $payload['billing']['dateOfBirth'] ?? null;
            $order->billing_tax_payer = $payload['billing']['taxPayer'] ?? false;
            $order->billing_ibge = $payload['billing']['ibge'] ?? null;
        }
        $order->save();

        // 3. Itens do pedido
        if (!empty($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $item) {
                // Tentar associar ao produto local
                $product = null;
                if (!empty($item['sku'])) {
                    $product = \App\Models\Plug4MarketProduct::where('codigo', $item['sku'])->first();
                }
                $order->items()->create([
                    'sku' => $item['sku'] ?? null,
                    'product_id' => $product ? $product->id : null,
                    'product_name' => $item['name'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['price'] ?? 0,
                    'total_price' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1)
                ]);
            }
        }
        return $order;
    }
} 