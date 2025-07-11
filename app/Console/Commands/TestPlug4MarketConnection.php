<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketSetting;
use Illuminate\Support\Facades\Log;

class TestPlug4MarketConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plug4market:test {--verbose : Mostrar informações detalhadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a conexão com a API do Plug4Market';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Testando conexão com a API do Plug4Market...');
        $this->newLine();

        $settings = Plug4MarketSetting::getSettings();
        $verbose = $this->option('verbose');

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
        
        try {
            $service = new Plug4MarketService();
            
            // Validar token JWT
            if ($service->validateToken()) {
                $this->info('✅ Token JWT válido');
            } else {
                $this->warn('⚠️  Token JWT inválido ou expirado');
            }

            // Testar conexão
            if ($service->testConnection()) {
                $this->info('✅ Conexão com API estabelecida');
            } else {
                $this->error('❌ Falha na conexão com API');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro durante teste: ' . $e->getMessage());
            return 1;
        }

        // Testar endpoints específicos
        $this->newLine();
        $this->info('📡 Testando endpoints...');

        try {
            // Testar categorias
            $this->line('   Testando /categories...');
            $categories = $service->listCategories();
            if (isset($categories['data'])) {
                $this->info('   ✅ Categorias: ' . count($categories['data']) . ' encontradas');
            } else {
                $this->warn('   ⚠️  Resposta inesperada de categorias');
            }

            // Testar canais de venda
            $this->line('   Testando /sales-channels...');
            $channels = $service->listSalesChannels();
            if (isset($channels['data'])) {
                $this->info('   ✅ Canais de venda: ' . count($channels['data']) . ' encontrados');
            } else {
                $this->warn('   ⚠️  Resposta inesperada de canais de venda');
            }

            // Testar produtos
            $this->line('   Testando /products...');
            $products = $service->listProducts(['limit' => 5]);
            if (isset($products['data'])) {
                $this->info('   ✅ Produtos: ' . count($products['data']) . ' encontrados');
            } else {
                $this->warn('   ⚠️  Resposta inesperada de produtos');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Erro ao testar endpoints: ' . $e->getMessage());
        }

        // Mostrar informações do token se verbose
        if ($verbose) {
            $this->newLine();
            $this->info('🔑 Informações do Token:');
            
            $tokenInfo = $service->getTokenInfo();
            foreach ($tokenInfo as $key => $value) {
                $this->line("   " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}");
            }
        }

        $this->newLine();
        $this->info('🎉 Teste concluído com sucesso!');
        
        return 0;
    }
} 