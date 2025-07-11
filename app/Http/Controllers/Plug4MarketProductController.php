<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plug4MarketProduct;
use App\Services\Plug4MarketService;
use Illuminate\Support\Facades\Log;

class Plug4MarketProductController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new Plug4MarketService();
    }

    public function index()
    {
        $title = 'Produtos Plug4Market';
        
        Log::info('Acessando listagem de produtos Plug4Market');
        
        try {
            // Tentar buscar produtos da API
            $apiProducts = $this->service->listProducts();
            $products = Plug4MarketProduct::with('category')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            Log::info('Listagem de produtos Plug4Market carregada com sucesso', [
                'total_produtos' => $products->total(),
                'produtos_api' => count($apiProducts['data'] ?? []),
                'pagina_atual' => $products->currentPage()
            ]);
            
            return view('plug4market.products.index', compact('products', 'apiProducts', 'title'));
        } catch (\Exception $e) {
            $products = Plug4MarketProduct::with('category')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            Log::error('Erro ao carregar produtos da API Plug4Market', [
                'error' => $e->getMessage(),
                'total_produtos_locais' => $products->total()
            ]);
            
            return view('plug4market.products.index', compact('products', 'title'))
                ->with('error', 'Erro ao conectar com a API: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $title = 'Novo Produto Plug4Market';
        
        Log::info('Acessando formulário de criação de produto Plug4Market');
        
        // Buscar categorias do banco local
        $categories = \App\Models\Plug4MarketCategory::ativas()
            ->orderBy('level', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        Log::info('Categorias carregadas para formulário de produto', [
            'total_categorias' => $categories->count()
        ]);
        
        return view('plug4market.products.create', compact('title', 'categories'));
    }

    public function store(Request $request)
    {
        Log::info('Iniciando criação de produto Plug4Market', [
            'dados_recebidos' => $request->only(['codigo', 'descricao', 'categoria_id', 'marca', 'valor_unitario'])
        ]);

        $data = $request->validate([
            'codigo' => 'required|string|max:255|unique:plug4market_products',
            'descricao' => 'required|string|max:255',
            'nome' => 'nullable|string|max:255',
            'ncm' => 'nullable|string|max:20',
            'cfop' => 'nullable|string|max:10',
            'unidade' => 'nullable|string|max:10',
            'valor_unitario' => 'required|numeric|min:0',
            'aliquota_icms' => 'nullable|numeric|min:0|max:100',
            'aliquota_pis' => 'nullable|numeric|min:0|max:100',
            'aliquota_cofins' => 'nullable|numeric|min:0|max:100',
            'marca' => 'nullable|string|max:255',
            'categoria_id' => 'nullable|integer',
            'largura' => 'nullable|integer|min:0',
            'altura' => 'nullable|integer|min:0',
            'comprimento' => 'nullable|integer|min:0',
            'peso' => 'nullable|integer|min:0',
            'estoque' => 'nullable|integer|min:0',
            'origem' => 'nullable|string|in:nacional,importado',
            'ean' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:255',
            'garantia' => 'nullable|integer|min:0',
        ]);

        try {
            // Criar produto localmente
            $product = Plug4MarketProduct::create($data);
            
            Log::info('Produto Plug4Market criado localmente', [
                'produto_id' => $product->id,
                'codigo' => $product->codigo,
                'descricao' => $product->descricao,
                'categoria_id' => $product->categoria_id,
                'valor_unitario' => $product->valor_unitario
            ]);
            
            // Tentar sincronizar com a API
            try {
                Log::info('Iniciando sincronização do produto com API Plug4Market', [
                    'produto_id' => $product->id,
                    'codigo' => $product->codigo
                ]);

                $apiResult = $this->service->createProduct($data);
                
                Log::info('Resposta da API Plug4Market ao criar produto', [
                    'produto_id' => $product->id,
                    'api_result' => $apiResult,
                    'tem_id' => isset($apiResult['id'])
                ]);
                
                if (isset($apiResult['id'])) {
                    $product->update([
                        'external_id' => $apiResult['id'],
                        'sincronizado' => true,
                        'ultima_sincronizacao' => now()
                    ]);
                    
                    Log::info('Produto Plug4Market sincronizado com sucesso', [
                        'produto_id' => $product->id,
                        'external_id' => $apiResult['id'],
                        'codigo' => $product->codigo
                    ]);
                    
                    return redirect()->route('plug4market.products.index')
                        ->with('success', 'Produto criado e sincronizado com sucesso!');
                } else {
                    // Se a API retornou erro, exibir mensagem detalhada
                    $errorMsg = isset($apiResult['message']) ? $apiResult['message'] : 'Erro desconhecido ao criar produto na API.';
                    if (isset($apiResult['errors'])) {
                        $errorMsg .= ' ' . json_encode($apiResult['errors']);
                    }
                    
                    Log::warning('API Plug4Market não retornou ID para produto criado', [
                        'produto_id' => $product->id,
                        'codigo' => $product->codigo,
                        'api_response' => $apiResult,
                        'error_message' => $errorMsg
                    ]);
                    // Adiciona log no Plug4MarketLog
                    \App\Models\Plug4MarketLog::create([
                        'action' => 'create_product',
                        'status' => 'error',
                        'message' => $errorMsg,
                        'details' => [
                            'produto_id' => $product->id,
                            'codigo' => $product->codigo,
                            'api_response' => $apiResult,
                            'dados_enviados' => $data
                        ],
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'execution_time_ms' => round((microtime(true) - LARAVEL_START) * 1000)
                    ]);
                    
                    return redirect()->route('plug4market.products.index')
                        ->with('error', 'Produto criado localmente. Erro na sincronização com a API: ' . $errorMsg);
                }
            } catch (\Exception $e) {
                // Se falhar na API, salva apenas localmente
                Log::error('Erro ao sincronizar produto com Plug4Market', [
                    'produto_id' => $product->id,
                    'codigo' => $product->codigo,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Adiciona log no Plug4MarketLog
                \App\Models\Plug4MarketLog::create([
                    'action' => 'create_product',
                    'status' => 'error',
                    'message' => 'Exceção ao sincronizar produto com a API: ' . $e->getMessage(),
                    'details' => [
                        'produto_id' => $product->id,
                        'codigo' => $product->codigo,
                        'dados_enviados' => $data,
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'execution_time_ms' => round((microtime(true) - LARAVEL_START) * 1000)
                ]);
                
                return redirect()->route('plug4market.products.index')
                    ->with('warning', 'Produto criado localmente. Erro na sincronização: ' . $e->getMessage());
            }
            
            return redirect()->route('plug4market.products.index')
                ->with('success', 'Produto criado com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto Plug4Market', [
                'dados_tentativa' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('plug4market.products.index')
                ->with('error', 'Erro ao criar produto: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        Log::info('Acessando detalhes do produto Plug4Market', ['produto_id' => $id]);
        
        $title = 'Detalhes do Produto Plug4Market';
        $product = Plug4MarketProduct::with('category')->findOrFail($id);
        
        $apiProduct = null;
        if ($product->external_id) {
            try {
                $apiProduct = $this->service->getProduct($product->external_id);
                
                Log::info('Dados da API carregados para produto', [
                    'produto_id' => $id,
                    'external_id' => $product->external_id,
                    'tem_dados_api' => !empty($apiProduct)
                ]);
            } catch (\Exception $e) {
                Log::warning('Erro ao carregar dados da API para produto', [
                    'produto_id' => $id,
                    'external_id' => $product->external_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return view('plug4market.products.show', compact('product', 'apiProduct', 'title'));
    }

    public function edit($id)
    {
        Log::info('Acessando formulário de edição de produto Plug4Market', ['produto_id' => $id]);
        
        $title = 'Editar Produto Plug4Market';
        $product = Plug4MarketProduct::with('category')->findOrFail($id);
        
        // Buscar categorias do banco local
        $categories = \App\Models\Plug4MarketCategory::ativas()
            ->orderBy('level', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        Log::info('Formulário de edição carregado', [
            'produto_id' => $id,
            'codigo' => $product->codigo,
            'total_categorias' => $categories->count(),
            'categoria_atual' => $product->categoria_id
        ]);
        
        return view('plug4market.products.edit', compact('product', 'title', 'categories'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Iniciando atualização de produto Plug4Market', [
            'produto_id' => $id,
            'dados_recebidos' => $request->only(['codigo', 'descricao', 'categoria_id', 'marca', 'valor_unitario'])
        ]);

        $product = Plug4MarketProduct::findOrFail($id);
        
        $data = $request->validate([
            'codigo' => 'required|string|max:255|unique:plug4market_products,codigo,' . $id,
            'descricao' => 'required|string|max:255',
            'nome' => 'nullable|string|max:255',
            'ncm' => 'nullable|string|max:20',
            'cfop' => 'nullable|string|max:10',
            'unidade' => 'nullable|string|max:10',
            'valor_unitario' => 'required|numeric|min:0',
            'aliquota_icms' => 'nullable|numeric|min:0|max:100',
            'aliquota_pis' => 'nullable|numeric|min:0|max:100',
            'aliquota_cofins' => 'nullable|numeric|min:0|max:100',
            'marca' => 'nullable|string|max:255',
            'categoria_id' => 'nullable|integer',
            'largura' => 'nullable|integer|min:0',
            'altura' => 'nullable|integer|min:0',
            'comprimento' => 'nullable|integer|min:0',
            'peso' => 'nullable|integer|min:0',
            'estoque' => 'nullable|integer|min:0',
            'origem' => 'nullable|string|in:nacional,importado',
            'ean' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:255',
            'garantia' => 'nullable|integer|min:0',
        ]);
        
        try {
            // Atualizar produto localmente
            $product->update($data);
            
            Log::info('Produto Plug4Market atualizado localmente', [
                'produto_id' => $id,
                'codigo' => $product->codigo,
                'categoria_id_anterior' => $product->getOriginal('categoria_id'),
                'categoria_id_novo' => $data['categoria_id'] ?? null,
                'valor_anterior' => $product->getOriginal('valor_unitario'),
                'valor_novo' => $data['valor_unitario']
            ]);
            
            // Tentar sincronizar com a API
            if ($product->external_id) {
                try {
                    Log::info('Iniciando sincronização de atualização com API Plug4Market', [
                        'produto_id' => $id,
                        'external_id' => $product->external_id,
                        'codigo' => $product->codigo
                    ]);

                    $apiResult = $this->service->updateProduct($product->external_id, $data);
                    
                    Log::info('Produto atualizado na API Plug4Market', [
                        'produto_id' => $id,
                        'external_id' => $product->external_id,
                        'api_result' => $apiResult
                    ]);
                    
                    $product->update([
                        'sincronizado' => true,
                        'ultima_sincronizacao' => now()
                    ]);
                    
                    return redirect()->route('plug4market.products.index')
                        ->with('success', 'Produto atualizado e sincronizado com sucesso!');
                } catch (\Exception $e) {
                    Log::error('Erro ao sincronizar atualização do produto com API Plug4Market', [
                        'produto_id' => $id,
                        'external_id' => $product->external_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return redirect()->route('plug4market.products.index')
                        ->with('warning', 'Produto atualizado localmente. Erro na sincronização: ' . $e->getMessage());
                }
            } else {
                Log::info('Produto atualizado localmente (sem external_id)', [
                    'produto_id' => $id,
                    'codigo' => $product->codigo
                ]);
            }
            
            return redirect()->route('plug4market.products.index')
                ->with('success', 'Produto atualizado com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto Plug4Market', [
                'produto_id' => $id,
                'dados_tentativa' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('plug4market.products.index')
                ->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Log::info('Iniciando exclusão de produto Plug4Market', ['produto_id' => $id]);

        $product = Plug4MarketProduct::findOrFail($id);
        
        try {
            // Tentar deletar da API
            if ($product->external_id) {
                try {
                    Log::info('Tentando excluir produto da API Plug4Market', [
                        'produto_id' => $id,
                        'external_id' => $product->external_id,
                        'codigo' => $product->codigo
                    ]);

                    $this->service->deleteProduct($product->external_id);
                    
                    Log::info('Produto excluído da API Plug4Market com sucesso', [
                        'produto_id' => $id,
                        'external_id' => $product->external_id
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Erro ao excluir produto da API Plug4Market, continuando exclusão local', [
                        'produto_id' => $id,
                        'external_id' => $product->external_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $product->delete();
            
            Log::info('Produto Plug4Market excluído localmente', [
                'produto_id' => $id,
                'codigo' => $product->codigo,
                'external_id' => $product->external_id
            ]);
            
            return redirect()->route('plug4market.products.index')
                ->with('success', 'Produto removido com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao excluir produto Plug4Market', [
                'produto_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('plug4market.products.index')
                ->with('error', 'Erro ao remover produto: ' . $e->getMessage());
        }
    }

    public function sync($id)
    {
        Log::info('Iniciando sincronização individual de produto Plug4Market', ['produto_id' => $id]);

        $product = Plug4MarketProduct::findOrFail($id);
        
        try {
            if ($product->external_id) {
                // Atualizar produto existente
                Log::info('Sincronizando produto existente na API', [
                    'produto_id' => $id,
                    'external_id' => $product->external_id,
                    'codigo' => $product->codigo
                ]);

                $apiResult = $this->service->updateProduct($product->external_id, $product->toArray());
                
                Log::info('Produto atualizado na API Plug4Market', [
                    'produto_id' => $id,
                    'external_id' => $product->external_id,
                    'api_result' => $apiResult
                ]);
            } else {
                // Criar novo produto
                Log::info('Criando novo produto na API Plug4Market', [
                    'produto_id' => $id,
                    'codigo' => $product->codigo
                ]);

                $apiResult = $this->service->createProduct($product->toArray());
                
                Log::info('Resposta da API ao criar produto', [
                    'produto_id' => $id,
                    'api_result' => $apiResult,
                    'tem_id' => isset($apiResult['id'])
                ]);
                
                if (isset($apiResult['id'])) {
                    $product->update([
                        'external_id' => $apiResult['id'],
                        'sincronizado' => true,
                        'ultima_sincronizacao' => now()
                    ]);
                    
                    Log::info('Produto sincronizado com sucesso', [
                        'produto_id' => $id,
                        'external_id' => $apiResult['id'],
                        'codigo' => $product->codigo
                    ]);
                } else {
                    Log::warning('API não retornou ID para produto sincronizado', [
                        'produto_id' => $id,
                        'api_result' => $apiResult
                    ]);
                }
            }
            
            return redirect()->route('plug4market.products.index')
                ->with('success', 'Produto sincronizado com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro na sincronização individual de produto Plug4Market', [
                'produto_id' => $id,
                'external_id' => $product->external_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('plug4market.products.index')
                ->with('error', 'Erro na sincronização: ' . $e->getMessage());
        }
    }

    public function syncAll()
    {
        Log::info('Iniciando sincronização em massa de produtos Plug4Market');

        $products = Plug4MarketProduct::where('ativo', true)->get();
        $successCount = 0;
        $errorCount = 0;
        
        Log::info('Produtos encontrados para sincronização', [
            'total_produtos' => $products->count(),
            'produtos_com_external_id' => $products->whereNotNull('external_id')->count(),
            'produtos_sem_external_id' => $products->whereNull('external_id')->count()
        ]);
        
        foreach ($products as $product) {
            try {
                if ($product->external_id) {
                    Log::info('Atualizando produto existente na API', [
                        'produto_id' => $product->id,
                        'external_id' => $product->external_id,
                        'codigo' => $product->codigo
                    ]);

                    $this->service->updateProduct($product->external_id, $product->toArray());
                } else {
                    Log::info('Criando novo produto na API', [
                        'produto_id' => $product->id,
                        'codigo' => $product->codigo
                    ]);

                    $apiResult = $this->service->createProduct($product->toArray());
                    
                    if (isset($apiResult['id'])) {
                        $product->update([
                            'external_id' => $apiResult['id'],
                            'sincronizado' => true,
                            'ultima_sincronizacao' => now()
                        ]);
                        
                        Log::info('Produto criado na API com sucesso', [
                            'produto_id' => $product->id,
                            'external_id' => $apiResult['id'],
                            'codigo' => $product->codigo
                        ]);
                    } else {
                        Log::warning('API não retornou ID para produto', [
                            'produto_id' => $product->id,
                            'api_result' => $apiResult
                        ]);
                    }
                }
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Erro na sincronização de produto individual', [
                    'produto_id' => $product->id,
                    'codigo' => $product->codigo,
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
        }
        
        Log::info('Sincronização em massa de produtos Plug4Market concluída', [
            'total_processados' => $products->count(),
            'sucessos' => $successCount,
            'erros' => $errorCount
        ]);
        
        $message = "Sincronização concluída: {$successCount} sucessos, {$errorCount} erros.";
        $type = $errorCount > 0 ? 'warning' : 'success';
        
        return redirect()->route('plug4market.products.index')
            ->with($type, $message);
    }
} 