<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketSetting;
use App\Models\Plug4MarketOrder;
use App\Models\Plug4MarketProduct;
use Illuminate\Support\Facades\Log;

class TestPlug4MarketOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plug4market:test-order {--create : Criar pedido de teste} {--verbose : Mostrar informações detalhadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a criação de pedidos Plug4Market';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Testando criação de pedidos Plug4Market...');
        $this->newLine();

        $settings = Plug4MarketSetting::getSettings();
        $verbose = $this->option('verbose');
        $create = $this->option('create');

        // Verificar configurações básicas
        $this->info('📋 Verificando configurações...');
        
        if (!$settings->isConfigured()) {
            $this->error('❌ Tokens não configurados!');
            $this->line('Configure os tokens em: /plug4market/settings');
            return 1;
        }

        $this->info('✅ Tokens configurados');
        
        if ($verbose) {
            $this->line("   URL: {$settings->base_url}");
            $this->line("   Sandbox: " . ($settings->sandbox ? 'Sim' : 'Não'));
            $this->line("   Seller ID: {$settings->seller_id}");
        }

        // Testar serviço
        $this->newLine();
        $this->info('🔐 Testando autenticação...');
        
        $service = new Plug4MarketService();
        
        try {
            $testResult = $service->testConnection();
            
            if ($testResult) {
                $this->info('✅ Conexão com API estabelecida');
            } else {
                $this->warn('⚠️  Conexão com API falhou, mas continuando...');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Erro na conexão: ' . $e->getMessage());
        }

        // Buscar produtos disponíveis
        $this->newLine();
        $this->info('📦 Buscando produtos disponíveis...');
        
        $products = Plug4MarketProduct::ativos()->sincronizados()->take(3)->get();
        
        if ($products->isEmpty()) {
            $this->warn('⚠️  Nenhum produto encontrado. Criando produto de teste...');
            
            // Criar produto de teste
            $testProduct = Plug4MarketProduct::create([
                'external_id' => 'TEST_' . time(),
                'codigo' => 'TEST001',
                'descricao' => 'Produto de Teste',
                'valor_unitario' => 10.00,
                'ativo' => true,
                'sincronizado' => true
            ]);
            
            $products = collect([$testProduct]);
        }

        $this->info('✅ ' . $products->count() . ' produtos encontrados');
        
        if ($verbose) {
            foreach ($products as $product) {
                $this->line("   - {$product->codigo}: {$product->descricao} (R$ {$product->valor_unitario})");
            }
        }

        if ($create) {
            $this->newLine();
            $this->info('🛒 Criando pedido de teste...');
            
            // Preparar dados do pedido
            $orderData = [
                'marketplace' => 7,
                'status' => 2,
                'shipping_cost' => 5.00,
                'shipping_name' => 'SEDEX',
                'payment_name' => 'Cartão Crédito',
                'interest' => 0,
                'total_commission' => 2.00,
                'type_billing' => 'PF',
                'produtos' => []
            ];

            // Adicionar produtos ao pedido
            foreach ($products as $product) {
                $orderData['produtos'][$product->id] = [
                    'id' => $product->id,
                    'sku' => $product->codigo,
                    'quantidade' => 1
                ];
            }

            try {
                $result = $service->createOrder($orderData);
                
                if (isset($result['error_status'])) {
                    $this->warn('⚠️  Erro na API, mas pedido pode ter sido salvo localmente');
                    $this->line("   Status: {$result['error_status']}");
                    $this->line("   Mensagem: " . ($result['error_messages'][0]['message'] ?? 'Erro desconhecido'));
                } else {
                    $this->info('✅ Pedido criado com sucesso na API');
                    $this->line("   ID: " . ($result['id'] ?? 'N/A'));
                    $this->line("   Número: " . ($result['numero'] ?? 'N/A'));
                }
                
                // Verificar se foi salvo localmente
                $localOrder = Plug4MarketOrder::where('external_id', $result['id'] ?? 'TEMP_' . time())
                    ->orWhere('order_number', 'like', 'PED_%')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($localOrder) {
                    $this->info('✅ Pedido salvo localmente');
                    $this->line("   ID Local: {$localOrder->id}");
                    $this->line("   Número: {$localOrder->order_number}");
                    $this->line("   Sincronizado: " . ($localOrder->sincronizado ? 'Sim' : 'Não'));
                    $this->line("   Valor Total: R$ {$localOrder->total_amount}");
                } else {
                    $this->warn('⚠️  Pedido não encontrado localmente');
                }
                
            } catch (\Exception $e) {
                $this->error('❌ Erro ao criar pedido: ' . $e->getMessage());
                
                // Verificar se foi salvo localmente mesmo com erro
                $localOrder = Plug4MarketOrder::where('external_id', 'like', 'TEMP_%')
                    ->orWhere('order_number', 'like', 'PED_%')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($localOrder) {
                    $this->info('✅ Pedido salvo localmente mesmo com erro na API');
                    $this->line("   ID Local: {$localOrder->id}");
                    $this->line("   Número: {$localOrder->order_number}");
                }
            }
        }

        // Mostrar estatísticas
        $this->newLine();
        $this->info('📊 Estatísticas:');
        
        $totalOrders = Plug4MarketOrder::count();
        $syncedOrders = Plug4MarketOrder::where('sincronizado', true)->count();
        $localOrders = Plug4MarketOrder::where('sincronizado', false)->count();
        
        $this->line("   Total de pedidos: {$totalOrders}");
        $this->line("   Pedidos sincronizados: {$syncedOrders}");
        $this->line("   Pedidos locais: {$localOrders}");
        
        if ($verbose) {
            $recentOrders = Plug4MarketOrder::orderBy('created_at', 'desc')->take(5)->get();
            if ($recentOrders->isNotEmpty()) {
                $this->newLine();
                $this->info('📋 Pedidos recentes:');
                foreach ($recentOrders as $order) {
                    $status = $order->sincronizado ? '✅' : '⚠️';
                    $this->line("   {$status} {$order->order_number} - R$ {$order->total_amount} ({$order->status_text})");
                }
            }
        }

        $this->newLine();
        $this->info('🎉 Teste concluído!');
        
        return 0;
    }
} 