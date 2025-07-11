<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketSetting;
use Illuminate\Support\Facades\Log;

class TestPlug4MarketProducts extends Command
{
    protected $signature = 'plug4market:test-products {--create : Criar produto de teste} {--list : Listar produtos da API} {--verbose : Mostrar informações detalhadas}';
    protected $description = 'Testa as funcionalidades de produtos do Plug4Market';
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new Plug4MarketService();
    }

    public function handle()
    {
        Log::info('Iniciando comando de teste de produtos Plug4Market');

        $this->info('🧪 Testando funcionalidades de produtos Plug4Market...');
        
        // Verificar configurações
        $this->info('📋 Verificando configurações...');
        
        $settings = Plug4MarketSetting::getSettings();
        if (!$settings->isConfigured()) {
            $this->error('❌ Tokens não configurados!');
            $this->line('Configure os tokens em: /plug4market/settings');
            return 1;
        }

        $this->info('✅ Tokens configurados');
        
        if ($this->option('verbose')) {
            $this->line("   URL: {$settings->base_url}");
            $this->line("   Sandbox: " . ($settings->sandbox ? 'Sim' : 'Não'));
            $this->line("   Seller ID: " . ($settings->seller_id ?? 'Não configurado'));
        }

        // Testar conexão
        $this->newLine();
        $this->info('🔐 Testando conexão...');
        
        if (!$this->service->testConnection()) {
            $this->error('❌ Falha na conexão!');
            $this->line('Verifique os tokens e tente novamente.');
            return 1;
        }

        $this->info('✅ Conexão bem-sucedida!');

        try {
            // Teste 1: Listar produtos
            if ($this->option('list')) {
                $this->info('📋 Teste 1: Listando produtos...');
                Log::info('Executando teste 1: Listar produtos Plug4Market');
                
                try {
                    $products = $this->service->listProducts();
                    
                    Log::info('Teste 1 concluído com sucesso', [
                        'total_produtos' => count($products['data'] ?? []),
                        'tem_dados' => !empty($products)
                    ]);
                    
                    $this->info('✅ Produtos listados com sucesso!');
                    $this->info("   Total de produtos: " . count($products['data'] ?? []));
                    
                    if (!empty($products['data']) && $this->option('verbose')) {
                        $this->info('   Primeiros produtos:');
                        foreach (array_slice($products['data'], 0, 3) as $product) {
                            $this->info("   - {$product['name']} (ID: {$product['id']})");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Teste 1 falhou: Erro ao listar produtos', [
                        'error' => $e->getMessage(),
                        'error_code' => $e->getCode()
                    ]);
                    
                    $this->error('❌ Erro ao listar produtos: ' . $e->getMessage());
                }
            }

            // Teste 2: Criar produto de teste
            if ($this->option('create')) {
                $this->info('📝 Teste 2: Criando produto de teste...');
                Log::info('Executando teste 2: Criar produto de teste Plug4Market');
                
                try {
                    $testProductData = [
                        'codigo' => 'TESTE_' . now()->format('YmdHis'),
                        'descricao' => 'Produto Teste ' . now()->format('Y-m-d H:i:s'),
                        'nome' => 'Produto Teste ' . now()->format('Y-m-d H:i:s'),
                        'valor_unitario' => 99.99,
                        'marca' => 'Marca Teste',
                        'categoria_id' => 1,
                        'largura' => 10.5,
                        'altura' => 15.2,
                        'comprimento' => 25.0,
                        'peso' => 500.0,
                        'estoque' => 10,
                        'origem' => 'nacional',
                        'ean' => '1234567890123',
                        'modelo' => 'Modelo Teste',
                        'garantia' => 12.0,
                        'ncm' => '19023000',
                        'preco_custo' => 75.50
                    ];

                    Log::info('Dados do produto de teste conforme especificação da API', [
                        'dados' => $testProductData,
                        'campos_obrigatorios' => [
                            'productId' => $testProductData['codigo'],
                            'productName' => $testProductData['descricao'],
                            'sku' => $testProductData['codigo'],
                            'name' => $testProductData['descricao'],
                            'description' => $testProductData['descricao'],
                            'width' => $testProductData['largura'],
                            'height' => $testProductData['altura'],
                            'length' => $testProductData['comprimento'],
                            'weight' => $testProductData['peso'],
                            'stock' => $testProductData['estoque'],
                            'price' => $testProductData['valor_unitario']
                        ]
                    ]);

                    $createdProduct = $this->service->createProduct($testProductData);
                    
                    Log::info('Teste 2 concluído', [
                        'produto_criado' => $createdProduct,
                        'tem_id' => isset($createdProduct['id'])
                    ]);
                    
                    if (isset($createdProduct['id'])) {
                        $this->info('✅ Produto de teste criado com sucesso!');
                        $this->info("   Nome: {$createdProduct['name']}");
                        $this->info("   ID: {$createdProduct['id']}");
                        $this->info("   SKU: {$createdProduct['sku']}");
                        
                        if ($this->option('verbose')) {
                            $this->info('   Detalhes completos:');
                            $this->line('   ' . json_encode($createdProduct, JSON_PRETTY_PRINT));
                        }
                    } else {
                        $this->error('❌ Produto criado mas sem ID retornado');
                        $this->line('Resposta da API: ' . json_encode($createdProduct));
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Teste 2 falhou: Erro ao criar produto de teste', [
                        'error' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'dados_tentativa' => $testProductData,
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    $this->error('❌ Erro ao criar produto de teste: ' . $e->getMessage());
                    
                    if ($this->option('verbose')) {
                        $this->line('Stack trace:');
                        $this->line($e->getTraceAsString());
                    }
                }
            }

            Log::info('Comando de teste de produtos Plug4Market concluído com sucesso');
            $this->info('🎉 Testes de produtos Plug4Market concluídos!');
            
        } catch (\Exception $e) {
            Log::error('Erro geral no comando de teste de produtos Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('💥 Erro geral: ' . $e->getMessage());
        }
    }
} 