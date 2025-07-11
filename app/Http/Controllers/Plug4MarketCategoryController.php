<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plug4MarketCategory;
use App\Models\Plug4MarketLog;
use App\Services\Plug4MarketService;
use Illuminate\Support\Facades\Log;

class Plug4MarketCategoryController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new Plug4MarketService();
    }

    /**
     * Listar categorias
     */
    public function index()
    {
        $title = 'Categorias Plug4Market';
        $startTime = microtime(true);
        
        try {
            // Buscar apenas categorias locais - endpoint /categories não existe na API
            $categories = Plug4MarketCategory::with('parent')
                ->orderBy('level', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(20);
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de sucesso
            Plug4MarketLog::create([
                'action' => 'list_categories',
                'status' => 'success',
                'message' => 'Listagem de categorias Plug4Market carregada com sucesso',
                'details' => [
                    'total_categorias' => $categories->total(),
                    'pagina_atual' => $categories->currentPage(),
                    'observacao' => 'Endpoint /categories não disponível na API - listando apenas categorias locais'
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return view('plug4market.categories.index', compact('categories', 'title'));
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro
            Plug4MarketLog::create([
                'action' => 'list_categories',
                'status' => 'error',
                'message' => 'Erro ao carregar categorias Plug4Market: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return view('plug4market.categories.index', compact('categories', 'title'))
                ->with('error', 'Erro ao carregar categorias: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $title = 'Nova Categoria Plug4Market';
        
        // Buscar categorias para usar como pai
        $parentCategories = Plug4MarketCategory::ativas()
            ->orderBy('name', 'asc')
            ->get();
        
        // Log de acesso ao formulário
        Plug4MarketLog::create([
            'action' => 'create_category_form',
            'status' => 'info',
            'message' => 'Acessando formulário de criação de categoria Plug4Market',
            'details' => [
                'total_categorias_pai' => $parentCategories->count()
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'execution_time_ms' => 0
        ]);
        
        return view('plug4market.categories.create', compact('title', 'parentCategories'));
    }

    /**
     * Salvar nova categoria
     */
    public function store(Request $request)
    {
        $startTime = microtime(true);
        
        // Log de início da criação
        Plug4MarketLog::create([
            'action' => 'create_category_start',
            'status' => 'info',
            'message' => 'Iniciando criação de categoria Plug4Market',
            'details' => [
                'dados_recebidos' => $request->only(['name', 'description', 'parent_id', 'is_active'])
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'execution_time_ms' => 0
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:plug4market_categories,id',
            'is_active' => 'boolean'
        ]);

        try {
            // Criar categoria localmente
            $category = Plug4MarketCategory::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'parent_id' => $data['parent_id'],
                'is_active' => $data['is_active'] ?? true,
                'level' => $this->calculateLevel($data['parent_id']),
                'path' => $this->calculatePath($data['name'], $data['parent_id'])
            ]);

            // Log de sucesso na criação local
            Plug4MarketLog::create([
                'action' => 'create_category_local',
                'status' => 'success',
                'message' => 'Categoria Plug4Market criada localmente com sucesso',
                'details' => [
                    'categoria_id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'level' => $category->level,
                    'path' => $category->path,
                    'is_active' => $category->is_active,
                    'observacao' => 'Endpoint /categories não disponível na API - categoria criada apenas localmente'
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000)
            ]);

            // Desabilitar sincronização temporariamente - endpoint /categories não existe na API
            $totalExecutionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de aviso - sincronização desabilitada
            Plug4MarketLog::create([
                'action' => 'create_category_sync_disabled',
                'status' => 'warning',
                'message' => 'Sincronização com API desabilitada - endpoint /categories não disponível',
                'details' => [
                    'categoria_id' => $category->id,
                    'name' => $category->name,
                    'motivo' => 'Endpoint /categories não existe na API Plug4Market',
                    'tempo_total_ms' => $totalExecutionTime
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $totalExecutionTime
            ]);
            
            return redirect()->route('plug4market.categories.index')
                ->with('success', 'Categoria criada com sucesso! (Sincronização com API temporariamente desabilitada)');
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro na criação local
            Plug4MarketLog::create([
                'action' => 'create_category_error',
                'status' => 'error',
                'message' => 'Erro ao criar categoria Plug4Market: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'dados_tentativa' => $data,
                    'validation_errors' => $request->validator->errors() ?? null
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return redirect()->route('plug4market.categories.index')
                ->with('error', 'Erro ao criar categoria: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar categoria
     */
    public function show($id)
    {
        $startTime = microtime(true);
        
        try {
            $title = 'Detalhes da Categoria Plug4Market';
            $category = Plug4MarketCategory::with('parent', 'children')->findOrFail($id);
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de sucesso ao carregar dados locais
            Plug4MarketLog::create([
                'action' => 'show_category_success',
                'status' => 'success',
                'message' => 'Detalhes da categoria Plug4Market carregados com sucesso',
                'details' => [
                    'categoria_id' => $id,
                    'external_id' => $category->external_id,
                    'name' => $category->name,
                    'sincronizado' => $category->sincronizado,
                    'observacao' => 'Endpoint /categories não disponível na API - exibindo apenas dados locais'
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return view('plug4market.categories.show', compact('category', 'title'));
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro
            Plug4MarketLog::create([
                'action' => 'show_category_error',
                'status' => 'error',
                'message' => 'Erro ao carregar detalhes da categoria Plug4Market: ' . $e->getMessage(),
                'details' => [
                    'categoria_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return redirect()->route('plug4market.categories.index')
                ->with('error', 'Categoria não encontrada: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $startTime = microtime(true);
        
        try {
            $title = 'Editar Categoria Plug4Market';
            $category = Plug4MarketCategory::findOrFail($id);
            
            // Buscar categorias para usar como pai (excluindo a própria e suas filhas)
            $parentCategories = Plug4MarketCategory::ativas()
                ->where('id', '!=', $id)
                ->whereNotIn('id', $this->getDescendantIds($id))
                ->orderBy('name', 'asc')
                ->get();
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de sucesso
            Plug4MarketLog::create([
                'action' => 'edit_category_form',
                'status' => 'success',
                'message' => 'Formulário de edição de categoria Plug4Market carregado',
                'details' => [
                    'categoria_id' => $id,
                    'name' => $category->name,
                    'total_categorias_pai_disponiveis' => $parentCategories->count(),
                    'categoria_pai_atual' => $category->parent_id,
                    'sincronizado' => $category->sincronizado
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return view('plug4market.categories.edit', compact('category', 'title', 'parentCategories'));
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro
            Plug4MarketLog::create([
                'action' => 'edit_category_error',
                'status' => 'error',
                'message' => 'Erro ao carregar formulário de edição: ' . $e->getMessage(),
                'details' => [
                    'categoria_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return redirect()->route('plug4market.categories.index')
                ->with('error', 'Categoria não encontrada: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar categoria
     */
    public function update(Request $request, $id)
    {
        $startTime = microtime(true);
        
        // Log de início da atualização
        Plug4MarketLog::create([
            'action' => 'update_category_start',
            'status' => 'info',
            'message' => 'Iniciando atualização de categoria Plug4Market',
            'details' => [
                'categoria_id' => $id,
                'dados_recebidos' => $request->only(['name', 'description', 'parent_id', 'is_active'])
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'execution_time_ms' => 0
        ]);

        try {
            $category = Plug4MarketCategory::findOrFail($id);
            
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'parent_id' => [
                    'nullable',
                    'exists:plug4market_categories,id',
                    function ($attribute, $value, $fail) use ($id) {
                        if ($value == $id) {
                            $fail('Uma categoria não pode ser pai de si mesma.');
                        }
                        if (in_array($value, $this->getDescendantIds($id))) {
                            $fail('Uma categoria não pode ser pai de suas categorias filhas.');
                        }
                    }
                ],
                'is_active' => 'boolean'
            ]);

            // Salvar dados antigos para log
            $oldData = $category->toArray();

            // Atualizar categoria localmente
            $category->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'parent_id' => $data['parent_id'],
                'is_active' => $data['is_active'] ?? true,
                'level' => $this->calculateLevel($data['parent_id']),
                'path' => $this->calculatePath($data['name'], $data['parent_id'])
            ]);

            // Atualizar categorias filhas
            $this->updateDescendants($category);

            // Log de sucesso na atualização local
            Plug4MarketLog::create([
                'action' => 'update_category_local',
                'status' => 'success',
                'message' => 'Categoria Plug4Market atualizada localmente',
                'details' => [
                    'categoria_id' => $category->id,
                    'name' => $category->name,
                    'dados_antigos' => $oldData,
                    'dados_novos' => $category->toArray(),
                    'parent_id_anterior' => $oldData['parent_id'],
                    'parent_id_novo' => $category->parent_id,
                    'observacao' => 'Endpoint /categories não disponível na API - categoria atualizada apenas localmente'
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000)
            ]);

            // Desabilitar sincronização temporariamente - endpoint /categories não existe na API
            $totalExecutionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de aviso - sincronização desabilitada
            Plug4MarketLog::create([
                'action' => 'update_category_sync_disabled',
                'status' => 'warning',
                'message' => 'Sincronização com API desabilitada - endpoint /categories não disponível',
                'details' => [
                    'categoria_id' => $category->id,
                    'name' => $category->name,
                    'motivo' => 'Endpoint /categories não existe na API Plug4Market',
                    'tempo_total_ms' => $totalExecutionTime
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $totalExecutionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('success', 'Categoria atualizada com sucesso! (Sincronização com API temporariamente desabilitada)');
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro na atualização
            Plug4MarketLog::create([
                'action' => 'update_category_error',
                'status' => 'error',
                'message' => 'Erro ao atualizar categoria Plug4Market: ' . $e->getMessage(),
                'details' => [
                    'categoria_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'dados_tentativa' => $request->all(),
                    'validation_errors' => $request->validator->errors() ?? null
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('error', 'Erro ao atualizar categoria: ' . $e->getMessage());
        }
    }

    /**
     * Excluir categoria
     */
    public function destroy($id)
    {
        $startTime = microtime(true);
        
        // Log de início da exclusão
        Plug4MarketLog::create([
            'action' => 'delete_category_start',
            'status' => 'info',
            'message' => 'Iniciando exclusão de categoria Plug4Market',
            'details' => [
                'categoria_id' => $id
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'execution_time_ms' => 0
        ]);

        try {
            $category = Plug4MarketCategory::findOrFail($id);
            
            // Verificar se tem produtos associados
            $produtosCount = $category->products()->count();
            if ($produtosCount > 0) {
                $executionTime = round((microtime(true) - $startTime) * 1000);
                
                // Log de erro - categoria com produtos
                Plug4MarketLog::create([
                    'action' => 'delete_category_blocked',
                    'status' => 'error',
                    'message' => 'Tentativa de excluir categoria com produtos associados',
                    'details' => [
                        'categoria_id' => $id,
                        'name' => $category->name,
                        'total_produtos' => $produtosCount,
                        'motivo' => 'Categoria possui produtos associados'
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                return redirect()->route('plug4market.categories.index')
                    ->with('error', 'Não é possível excluir uma categoria que possui produtos associados.');
            }

            // Verificar se tem categorias filhas
            $childrenCount = $category->children()->count();
            if ($childrenCount > 0) {
                $executionTime = round((microtime(true) - $startTime) * 1000);
                
                // Log de erro - categoria com filhos
                Plug4MarketLog::create([
                    'action' => 'delete_category_blocked',
                    'status' => 'error',
                    'message' => 'Tentativa de excluir categoria com subcategorias',
                    'details' => [
                        'categoria_id' => $id,
                        'name' => $category->name,
                        'total_filhos' => $childrenCount,
                        'motivo' => 'Categoria possui subcategorias'
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                return redirect()->route('plug4market.categories.index')
                    ->with('error', 'Não é possível excluir uma categoria que possui subcategorias.');
            }

            // Salvar dados da categoria para log
            $categoryData = $category->toArray();

            // Desabilitar sincronização temporariamente - endpoint /categories não existe na API
            if ($category->external_id) {
                $totalExecutionTime = round((microtime(true) - $startTime) * 1000);
                
                // Log de aviso - sincronização desabilitada
                Plug4MarketLog::create([
                    'action' => 'delete_category_sync_disabled',
                    'status' => 'warning',
                    'message' => 'Sincronização com API desabilitada - endpoint /categories não disponível',
                    'details' => [
                        'categoria_id' => $category->id,
                        'external_id' => $category->external_id,
                        'name' => $category->name,
                        'motivo' => 'Endpoint /categories não existe na API Plug4Market',
                        'tempo_total_ms' => $totalExecutionTime
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'execution_time_ms' => $totalExecutionTime
                ]);
            }

            // Excluir categoria localmente
            $category->delete();
            
            $totalExecutionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de sucesso na exclusão local
            Plug4MarketLog::create([
                'action' => 'delete_category_success',
                'status' => 'success',
                'message' => 'Categoria Plug4Market excluída com sucesso',
                'details' => [
                    'categoria_id' => $id,
                    'name' => $categoryData['name'],
                    'external_id' => $categoryData['external_id'] ?? null,
                    'sincronizado' => $categoryData['sincronizado'] ?? false,
                    'tempo_total_ms' => $totalExecutionTime
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $totalExecutionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('success', 'Categoria excluída com sucesso!');
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro na exclusão
            Plug4MarketLog::create([
                'action' => 'delete_category_error',
                'status' => 'error',
                'message' => 'Erro ao excluir categoria Plug4Market: ' . $e->getMessage(),
                'details' => [
                    'categoria_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('error', 'Erro ao excluir categoria: ' . $e->getMessage());
        }
    }

    /**
     * Sincronizar categoria específica
     */
    public function sync($id)
    {
        $startTime = microtime(true);
        
        // Log de início da sincronização
        Plug4MarketLog::create([
            'action' => 'sync_category_start',
            'status' => 'info',
            'message' => 'Iniciando sincronização de categoria específica',
            'details' => [
                'categoria_id' => $id
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'execution_time_ms' => 0
        ]);

        try {
            $category = Plug4MarketCategory::findOrFail($id);
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de aviso - sincronização desabilitada
            Plug4MarketLog::create([
                'action' => 'sync_category_disabled',
                'status' => 'warning',
                'message' => 'Sincronização com API desabilitada - endpoint /categories não disponível',
                'details' => [
                    'categoria_id' => $category->id,
                    'name' => $category->name,
                    'external_id' => $category->external_id,
                    'motivo' => 'Endpoint /categories não existe na API Plug4Market',
                    'tempo_total_ms' => $executionTime
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('warning', 'Sincronização temporariamente desabilitada - endpoint /categories não disponível na API.');
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro geral
            Plug4MarketLog::create([
                'action' => 'sync_category_error',
                'status' => 'error',
                'message' => 'Erro geral na sincronização de categoria: ' . $e->getMessage(),
                'details' => [
                    'categoria_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('error', 'Erro na sincronização: ' . $e->getMessage());
        }
    }

    /**
     * Calcular nível da categoria
     */
    private function calculateLevel($parentId)
    {
        if (!$parentId) {
            return 0;
        }

        $parent = Plug4MarketCategory::find($parentId);
        return $parent ? $parent->level + 1 : 0;
    }

    /**
     * Calcular caminho da categoria
     */
    private function calculatePath($name, $parentId)
    {
        if (!$parentId) {
            return $name;
        }

        $parent = Plug4MarketCategory::find($parentId);
        return $parent ? $parent->path . ' > ' . $name : $name;
    }

    /**
     * Obter ID externo da categoria pai
     */
    private function getExternalParentId($parentId)
    {
        if (!$parentId) {
            return null;
        }

        $parent = Plug4MarketCategory::find($parentId);
        return $parent ? $parent->external_id : null;
    }

    /**
     * Obter IDs das categorias descendentes
     */
    private function getDescendantIds($categoryId)
    {
        $descendants = [];
        $children = Plug4MarketCategory::where('parent_id', $categoryId)->get();
        
        foreach ($children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getDescendantIds($child->id));
        }
        
        return $descendants;
    }

    /**
     * Atualizar categorias descendentes
     */
    private function updateDescendants($category)
    {
        $children = $category->children;
        
        foreach ($children as $child) {
            $child->update([
                'level' => $this->calculateLevel($child->parent_id),
                'path' => $this->calculatePath($child->name, $child->parent_id)
            ]);
            
            $this->updateDescendants($child);
        }
    }

    /**
     * Sincronizar todas as categorias
     */
    public function syncAll()
    {
        $startTime = microtime(true);
        
        // Log de início da sincronização em massa
        Plug4MarketLog::create([
            'action' => 'sync_all_categories_start',
            'status' => 'info',
            'message' => 'Iniciando sincronização em massa de categorias Plug4Market',
            'details' => [
                'tipo' => 'sincronizacao_em_massa'
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'execution_time_ms' => 0
        ]);

        try {
            $categories = Plug4MarketCategory::where('sincronizado', false)->get();
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de aviso - sincronização desabilitada
            Plug4MarketLog::create([
                'action' => 'sync_all_categories_disabled',
                'status' => 'warning',
                'message' => 'Sincronização em massa desabilitada - endpoint /categories não disponível',
                'details' => [
                    'total_categorias_nao_sincronizadas' => $categories->count(),
                    'motivo' => 'Endpoint /categories não existe na API Plug4Market',
                    'tempo_total_ms' => $executionTime
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('warning', 'Sincronização em massa temporariamente desabilitada - endpoint /categories não disponível na API.');
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de erro geral na sincronização em massa
            Plug4MarketLog::create([
                'action' => 'sync_all_categories_error',
                'status' => 'error',
                'message' => 'Erro geral na sincronização em massa: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'tempo_total_ms' => $executionTime
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.categories.index')
                ->with('error', 'Erro na sincronização em massa: ' . $e->getMessage());
        }
    }
} 