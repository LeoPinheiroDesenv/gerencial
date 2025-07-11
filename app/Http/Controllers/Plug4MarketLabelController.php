<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Plug4MarketService;
use Illuminate\Support\Facades\Log;

class Plug4MarketLabelController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new Plug4MarketService();
    }

    /**
     * Listar etiquetas de pedidos
     */
    public function index()
    {
        $title = 'Etiquetas de Pedidos Plug4Market';
        
        Log::info('Acessando listagem de etiquetas de pedidos Plug4Market');
        
        try {
            $apiResult = $this->service->listLabelOrders();
            // Se a resposta da API vier com ['data'], extrair
            if (is_array($apiResult) && isset($apiResult['data']) && is_array($apiResult['data'])) {
                $labels = $apiResult['data'];
            } else {
                $labels = $apiResult;
            }
            
            Log::info('Listagem de etiquetas Plug4Market carregada com sucesso', [
                'total_etiquetas' => count($labels)
            ]);
            
            return view('plug4market.labels.index', compact('title', 'labels'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar etiquetas da API Plug4Market', [
                'error' => $e->getMessage()
            ]);
            
            return view('plug4market.labels.index', compact('title'))
                ->with('error', 'Erro ao conectar com a API: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de criação de etiqueta
     */
    public function create()
    {
        $title = 'Nova Etiqueta de Pedido Plug4Market';
        
        Log::info('Acessando formulário de criação de etiqueta Plug4Market');
        
        // Buscar pedidos disponíveis
        $orders = [];
        try {
            $apiResult = $this->service->listOrders();
            if (is_array($apiResult) && isset($apiResult['data']) && is_array($apiResult['data'])) {
                $orders = $apiResult['data'];
            } else {
                $orders = $apiResult;
            }
            
            Log::info('Pedidos carregados para formulário de etiqueta', [
                'total_pedidos' => count($orders)
            ]);
        } catch (\Exception $e) {
            Log::warning('Erro ao carregar pedidos para formulário de etiqueta', [
                'error' => $e->getMessage()
            ]);
        }
        
        return view('plug4market.labels.create', compact('title', 'orders'));
    }

    /**
     * Salvar nova etiqueta
     */
    public function store(Request $request)
    {
        Log::info('Iniciando criação de etiqueta Plug4Market', [
            'dados_recebidos' => $request->only(['orderId', 'shippingCompany', 'shippingService', 'trackingCode'])
        ]);

        $data = $request->validate([
            'orderId' => 'required|integer|min:1',
            'shippingCompany' => 'required|string|max:255',
            'shippingService' => 'required|string|max:255',
            'trackingCode' => 'nullable|string|max:255',
            'shippingDate' => 'nullable|date',
            'estimatedDelivery' => 'nullable|date',
            'shippingCost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startTime = microtime(true);

        try {
            $result = $this->service->createLabelOrder($data);
            
            if (isset($result['id'])) {
                $executionTime = round((microtime(true) - $startTime) * 1000);
                
                \App\Models\Plug4MarketLog::create([
                    'action' => 'create_label',
                    'status' => 'success',
                    'message' => 'Etiqueta criada com sucesso',
                    'details' => [
                        'etiqueta' => $result,
                        'dados_enviados' => $data
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                Log::info('Etiqueta Plug4Market criada com sucesso', [
                    'etiqueta_id' => $result['id'],
                    'order_id' => $data['orderId'],
                    'shipping_company' => $data['shippingCompany'],
                    'shipping_service' => $data['shippingService']
                ]);
                
                return redirect()->route('plug4market.labels.index')
                    ->with('success', 'Etiqueta criada com sucesso!');
            } else {
                $executionTime = round((microtime(true) - $startTime) * 1000);
                
                \App\Models\Plug4MarketLog::create([
                    'action' => 'create_label',
                    'status' => 'error',
                    'message' => 'Erro ao criar etiqueta na API',
                    'details' => [
                        'dados_enviados' => $data,
                        'api_response' => $result
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                return redirect()->back()->withInput()
                    ->with('error', 'Erro ao criar etiqueta: ' . ($result['message'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            \App\Models\Plug4MarketLog::create([
                'action' => 'create_label',
                'status' => 'error',
                'message' => 'Erro ao criar etiqueta: ' . $e->getMessage(),
                'details' => [
                    'dados_enviados' => $data,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            Log::error('Erro ao criar etiqueta Plug4Market', [
                'error' => $e->getMessage(),
                'dados_enviados' => $data
            ]);
            
            return redirect()->back()->withInput()
                ->with('error', 'Erro ao criar etiqueta: ' . $e->getMessage());
        }
    }

    /**
     * Exibir detalhes da etiqueta
     */
    public function show($id)
    {
        $title = 'Detalhes da Etiqueta Plug4Market';
        
        Log::info('Acessando detalhes da etiqueta Plug4Market', ['etiqueta_id' => $id]);
        
        try {
            $label = $this->service->getLabelOrder($id);
            
            Log::info('Detalhes da etiqueta carregados com sucesso', [
                'etiqueta_id' => $id,
                'tem_dados' => !empty($label)
            ]);
            
            return view('plug4market.labels.show', compact('title', 'label', 'id'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar detalhes da etiqueta', [
                'etiqueta_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('plug4market.labels.index')
                ->with('error', 'Erro ao carregar etiqueta: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de edição de etiqueta
     */
    public function edit($id)
    {
        $title = 'Editar Etiqueta Plug4Market';
        
        Log::info('Acessando formulário de edição de etiqueta Plug4Market', ['etiqueta_id' => $id]);
        
        try {
            $label = $this->service->getLabelOrder($id);
            
            // Buscar pedidos disponíveis
            $orders = [];
            try {
                $apiResult = $this->service->listOrders();
                if (is_array($apiResult) && isset($apiResult['data']) && is_array($apiResult['data'])) {
                    $orders = $apiResult['data'];
                } else {
                    $orders = $apiResult;
                }
            } catch (\Exception $e) {
                Log::warning('Erro ao carregar pedidos para edição de etiqueta', [
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Dados carregados para edição de etiqueta', [
                'etiqueta_id' => $id,
                'tem_dados_etiqueta' => !empty($label),
                'total_pedidos' => count($orders)
            ]);
            
            return view('plug4market.labels.edit', compact('title', 'label', 'orders', 'id'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados para edição de etiqueta', [
                'etiqueta_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('plug4market.labels.index')
                ->with('error', 'Erro ao carregar etiqueta: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar etiqueta
     */
    public function update(Request $request, $id)
    {
        Log::info('Iniciando atualização de etiqueta Plug4Market', [
            'etiqueta_id' => $id,
            'dados_recebidos' => $request->only(['orderId', 'shippingCompany', 'shippingService', 'trackingCode'])
        ]);

        $data = $request->validate([
            'orderId' => 'required|integer|min:1',
            'shippingCompany' => 'required|string|max:255',
            'shippingService' => 'required|string|max:255',
            'trackingCode' => 'nullable|string|max:255',
            'shippingDate' => 'nullable|date',
            'estimatedDelivery' => 'nullable|date',
            'shippingCost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startTime = microtime(true);

        try {
            $result = $this->service->updateLabelOrder($id, $data);
            
            if (isset($result['id'])) {
                $executionTime = round((microtime(true) - $startTime) * 1000);
                
                \App\Models\Plug4MarketLog::create([
                    'action' => 'update_label',
                    'status' => 'success',
                    'message' => 'Etiqueta atualizada com sucesso',
                    'details' => [
                        'etiqueta_id' => $id,
                        'etiqueta' => $result,
                        'dados_enviados' => $data
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                Log::info('Etiqueta Plug4Market atualizada com sucesso', [
                    'etiqueta_id' => $id,
                    'order_id' => $data['orderId']
                ]);
                
                return redirect()->route('plug4market.labels.index')
                    ->with('success', 'Etiqueta atualizada com sucesso!');
            } else {
                $executionTime = round((microtime(true) - $startTime) * 1000);
                
                \App\Models\Plug4MarketLog::create([
                    'action' => 'update_label',
                    'status' => 'error',
                    'message' => 'Erro ao atualizar etiqueta na API',
                    'details' => [
                        'etiqueta_id' => $id,
                        'dados_enviados' => $data,
                        'api_response' => $result
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'execution_time_ms' => $executionTime
                ]);
                
                return redirect()->back()->withInput()
                    ->with('error', 'Erro ao atualizar etiqueta: ' . ($result['message'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            \App\Models\Plug4MarketLog::create([
                'action' => 'update_label',
                'status' => 'error',
                'message' => 'Erro ao atualizar etiqueta: ' . $e->getMessage(),
                'details' => [
                    'etiqueta_id' => $id,
                    'dados_enviados' => $data,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            Log::error('Erro ao atualizar etiqueta Plug4Market', [
                'etiqueta_id' => $id,
                'error' => $e->getMessage(),
                'dados_enviados' => $data
            ]);
            
            return redirect()->back()->withInput()
                ->with('error', 'Erro ao atualizar etiqueta: ' . $e->getMessage());
        }
    }

    /**
     * Excluir etiqueta
     */
    public function destroy($id)
    {
        Log::info('Iniciando exclusão de etiqueta Plug4Market', ['etiqueta_id' => $id]);

        try {
            $result = $this->service->deleteLabelOrder($id);
            
            \App\Models\Plug4MarketLog::create([
                'action' => 'delete_label',
                'status' => 'success',
                'message' => 'Etiqueta excluída com sucesso',
                'details' => [
                    'etiqueta_id' => $id,
                    'api_response' => $result
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => 0
            ]);
            
            Log::info('Etiqueta Plug4Market excluída com sucesso', [
                'etiqueta_id' => $id
            ]);
            
            return redirect()->route('plug4market.labels.index')
                ->with('success', 'Etiqueta excluída com sucesso!');
        } catch (\Exception $e) {
            \App\Models\Plug4MarketLog::create([
                'action' => 'delete_label',
                'status' => 'error',
                'message' => 'Erro ao excluir etiqueta: ' . $e->getMessage(),
                'details' => [
                    'etiqueta_id' => $id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => 0
            ]);
            
            Log::error('Erro ao excluir etiqueta Plug4Market', [
                'etiqueta_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('plug4market.labels.index')
                ->with('error', 'Erro ao excluir etiqueta: ' . $e->getMessage());
        }
    }
} 