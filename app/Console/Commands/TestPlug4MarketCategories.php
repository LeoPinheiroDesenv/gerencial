<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketCategory;
use App\Models\Plug4MarketSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestPlug4MarketCategories extends Command
{
    protected $signature = 'plug4market:test-categories {--create : Criar categoria de teste} {--list : Listar categorias da API} {--verbose : Mostrar informações detalhadas}';
    protected $description = 'Testa as funcionalidades de categorias do Plug4Market';
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new Plug4MarketService();
    }

    public function handle()
    {
        Log::info('Iniciando comando de teste de categorias Plug4Market');

        $this->info('🧪 Testando funcionalidades de categorias Plug4Market...');
        
        try {
            // Teste 1: Listar categorias
            $this->info('📋 Teste 1: Listando categorias...');
            Log::info('Executando teste 1: Listar categorias Plug4Market');
            
            try {
                $categories = $this->service->listCategories();
                
                Log::info('Teste 1 concluído com sucesso', [
                    'total_categorias' => count($categories['data'] ?? []),
                    'tem_dados' => !empty($categories)
                ]);
                
                $this->info('✅ Categorias listadas com sucesso!');
                $this->info("   Total de categorias: " . count($categories['data'] ?? []));
                
                if (!empty($categories['data'])) {
                    $this->info('   Primeiras categorias:');
                    foreach (array_slice($categories['data'], 0, 3) as $category) {
                        $this->info("   - {$category['name']} (ID: {$category['id']})");
                    }
                }
            } catch (\Exception $e) {
                Log::error('Teste 1 falhou: Erro ao listar categorias', [
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode()
                ]);
                
                $this->error('❌ Erro ao listar categorias: ' . $e->getMessage());
            }

            // Teste 2: Criar categoria de teste
            $this->info('📝 Teste 2: Criando categoria de teste...');
            Log::info('Executando teste 2: Criar categoria de teste Plug4Market');
            
            try {
                $testCategoryData = [
                    'name' => 'Categoria Teste ' . now()->format('Y-m-d H:i:s'),
                    'description' => 'Categoria criada automaticamente pelo comando de teste',
                    'is_active' => true
                ];

                Log::info('Dados da categoria de teste', [
                    'dados' => $testCategoryData
                ]);

                $createdCategory = $this->service->createCategory($testCategoryData);
                
                Log::info('Teste 2 concluído com sucesso', [
                    'categoria_criada' => $createdCategory,
                    'tem_id' => isset($createdCategory['id'])
                ]);
                
                $this->info('✅ Categoria de teste criada com sucesso!');
                $this->info("   Nome: {$createdCategory['name']}");
                $this->info("   ID: {$createdCategory['id']}");
                
                $createdCategoryId = $createdCategory['id'];
                
                // Teste 3: Buscar categoria criada
                $this->info('🔍 Teste 3: Buscando categoria criada...');
                Log::info('Executando teste 3: Buscar categoria criada', [
                    'category_id' => $createdCategoryId
                ]);
                
                try {
                    $retrievedCategory = $this->service->getCategory($createdCategoryId);
                    
                    Log::info('Teste 3 concluído com sucesso', [
                        'category_id' => $createdCategoryId,
                        'categoria_retornada' => $retrievedCategory
                    ]);
                    
                    $this->info('✅ Categoria encontrada com sucesso!');
                    $this->info("   Nome: {$retrievedCategory['name']}");
                    $this->info("   Descrição: {$retrievedCategory['description']}");
                    
                    // Teste 4: Atualizar categoria
                    $this->info('✏️ Teste 4: Atualizando categoria...');
                    Log::info('Executando teste 4: Atualizar categoria', [
                        'category_id' => $createdCategoryId
                    ]);
                    
                    try {
                        $updateData = [
                            'name' => 'Categoria Teste Atualizada ' . now()->format('Y-m-d H:i:s'),
                            'description' => 'Categoria atualizada automaticamente pelo comando de teste',
                            'is_active' => true
                        ];

                        Log::info('Dados para atualização da categoria', [
                            'category_id' => $createdCategoryId,
                            'dados_atualizacao' => $updateData
                        ]);

                        $updatedCategory = $this->service->updateCategory($createdCategoryId, $updateData);
                        
                        Log::info('Teste 4 concluído com sucesso', [
                            'category_id' => $createdCategoryId,
                            'categoria_atualizada' => $updatedCategory
                        ]);
                        
                        $this->info('✅ Categoria atualizada com sucesso!');
                        $this->info("   Novo nome: {$updatedCategory['name']}");
                        
                        // Teste 5: Deletar categoria
                        $this->info('🗑️ Teste 5: Deletando categoria de teste...');
                        Log::info('Executando teste 5: Deletar categoria de teste', [
                            'category_id' => $createdCategoryId
                        ]);
                        
                        try {
                            $deleteResult = $this->service->deleteCategory($createdCategoryId);
                            
                            Log::info('Teste 5 concluído com sucesso', [
                                'category_id' => $createdCategoryId,
                                'resultado_exclusao' => $deleteResult
                            ]);
                            
                            $this->info('✅ Categoria deletada com sucesso!');
                            
                        } catch (\Exception $e) {
                            Log::error('Teste 5 falhou: Erro ao deletar categoria', [
                                'category_id' => $createdCategoryId,
                                'error' => $e->getMessage(),
                                'error_code' => $e->getCode()
                            ]);
                            
                            $this->error('❌ Erro ao deletar categoria: ' . $e->getMessage());
                        }
                        
                    } catch (\Exception $e) {
                        Log::error('Teste 4 falhou: Erro ao atualizar categoria', [
                            'category_id' => $createdCategoryId,
                            'error' => $e->getMessage(),
                            'error_code' => $e->getCode()
                        ]);
                        
                        $this->error('❌ Erro ao atualizar categoria: ' . $e->getMessage());
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Teste 3 falhou: Erro ao buscar categoria', [
                        'category_id' => $createdCategoryId,
                        'error' => $e->getMessage(),
                        'error_code' => $e->getCode()
                    ]);
                    
                    $this->error('❌ Erro ao buscar categoria: ' . $e->getMessage());
                }
                
            } catch (\Exception $e) {
                Log::error('Teste 2 falhou: Erro ao criar categoria de teste', [
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'dados_tentativa' => $testCategoryData
                ]);
                
                $this->error('❌ Erro ao criar categoria de teste: ' . $e->getMessage());
            }

            Log::info('Comando de teste de categorias Plug4Market concluído com sucesso');
            $this->info('🎉 Todos os testes de categorias Plug4Market foram concluídos!');
            
        } catch (\Exception $e) {
            Log::error('Erro geral no comando de teste de categorias Plug4Market', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('💥 Erro geral: ' . $e->getMessage());
        }
    }
} 