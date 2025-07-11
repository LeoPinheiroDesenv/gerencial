<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plug4MarketSetting;
use App\Models\Plug4MarketLog;
use App\Services\Plug4MarketService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class Plug4MarketSettingController extends Controller
{
    public function index()
    {
        $title = 'Configurações Plug4Market';
        $settings = Plug4MarketSetting::getSettings();
        
        // Get recent logs for dashboard with error handling
        try {
            $recentLogs = Plug4MarketLog::getRecent(5);
            $todayLogs = Plug4MarketLog::getToday();
            $errorLogs = Plug4MarketLog::getByStatus('error', 5);
        } catch (\Exception $e) {
            // If there's an error (e.g., table doesn't exist), use empty collections
            $recentLogs = collect();
            $todayLogs = collect();
            $errorLogs = collect();
        }
        
        return view('plug4market.settings.index', compact('settings', 'title', 'recentLogs', 'todayLogs', 'errorLogs'));
    }

    public function update(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $data = $request->validate([
                'user_login' => 'nullable|string|email',
                'user_password' => 'nullable|string|min:6',
                'access_token' => 'nullable|string',
                'refresh_token' => 'nullable|string',
                'base_url' => 'required|url',
                'sandbox' => 'boolean',
                'seller_id' => 'required|string|max:50',
                'software_house_cnpj' => 'required|string|max:20',
                'store_cnpj' => 'required|string|max:20',
                'user_id' => 'required|string|max:100',
            ]);

            $settings = Plug4MarketSetting::getSettings();
            $settings->update($data);

            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log the update
            Plug4MarketLog::create([
                'action' => 'settings_update',
                'status' => 'success',
                'message' => 'Configurações atualizadas com sucesso',
                'details' => [
                    'updated_fields' => array_keys($data),
                    'base_url' => $data['base_url'],
                    'sandbox' => $data['sandbox'] ?? false
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.settings.index')
                ->with('success', 'Configurações atualizadas com sucesso!');
                
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            Plug4MarketLog::create([
                'action' => 'settings_update',
                'status' => 'error',
                'message' => 'Erro ao atualizar configurações: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return redirect()->route('plug4market.settings.index')
                ->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }

    public function testConnection()
    {
        $startTime = microtime(true);
        $settings = Plug4MarketSetting::getSettings();
        $testResults = [];
        $overallSuccess = true;
        $errorMessages = [];
        $title = 'Teste de Conexão Plug4Market';

        try {
            // Teste 1: Verificar se as configurações estão preenchidas
            $testResults[] = [
                'test' => 'Configurações Básicas',
                'status' => 'checking',
                'message' => 'Verificando se as configurações estão preenchidas...'
            ];

            if (empty($settings->access_token) || empty($settings->refresh_token)) {
                $testResults[0]['status'] = 'failed';
                $testResults[0]['message'] = 'Tokens de acesso não configurados';
                $overallSuccess = false;
                $errorMessages[] = 'Tokens de acesso não configurados';
            } else {
                $testResults[0]['status'] = 'success';
                $testResults[0]['message'] = 'Tokens configurados corretamente';
            }

            // Teste 2: Verificar URL da API
            $testResults[] = [
                'test' => 'URL da API',
                'status' => 'checking',
                'message' => 'Verificando URL da API...'
            ];

            if (empty($settings->base_url)) {
                $testResults[1]['status'] = 'failed';
                $testResults[1]['message'] = 'URL da API não configurada';
                $overallSuccess = false;
                $errorMessages[] = 'URL da API não configurada';
            } else {
                $testResults[1]['status'] = 'success';
                $testResults[1]['message'] = 'URL da API: ' . $settings->base_url;
            }

            // Teste 3: Validar formato do token JWT
            $testResults[] = [
                'test' => 'Validação do Token JWT',
                'status' => 'checking',
                'message' => 'Validando formato do token JWT...'
            ];

            try {
                $service = new Plug4MarketService();
                $tokenValid = $service->validateToken();
                
                if ($tokenValid) {
                    $testResults[2]['status'] = 'success';
                    $testResults[2]['message'] = 'Token JWT válido e não expirado';
                } else {
                    $testResults[2]['status'] = 'failed';
                    $testResults[2]['message'] = 'Token JWT inválido ou expirado';
                    $overallSuccess = false;
                    $errorMessages[] = 'Token JWT inválido ou expirado';
                }
            } catch (\Exception $e) {
                $testResults[2]['status'] = 'failed';
                $testResults[2]['message'] = 'Erro na validação do token: ' . $e->getMessage();
                $overallSuccess = false;
                $errorMessages[] = 'Erro na validação do token: ' . $e->getMessage();
            }

            // Teste 4: Testar conectividade com a API
            $testResults[] = [
                'test' => 'Conectividade com API',
                'status' => 'checking',
                'message' => 'Testando conectividade com a API...'
            ];

            try {
                $service = new Plug4MarketService();
                $connectivityTest = $service->testBasicConnectivity();
                
                if ($connectivityTest) {
                    $testResults[3]['status'] = 'success';
                    $testResults[3]['message'] = 'API respondendo corretamente';
                } else {
                    $testResults[3]['status'] = 'failed';
                    $testResults[3]['message'] = 'API não está respondendo';
                    $overallSuccess = false;
                    $errorMessages[] = 'API não está respondendo';
                }
            } catch (\Exception $e) {
                $testResults[3]['status'] = 'failed';
                $testResults[3]['message'] = 'Erro de conectividade: ' . $e->getMessage();
                $overallSuccess = false;
                $errorMessages[] = 'Erro de conectividade: ' . $e->getMessage();
            }

            // Teste 5: Validar autenticação com a API
            $testResults[] = [
                'test' => 'Autenticação com API',
                'status' => 'checking',
                'message' => 'Testando autenticação com a API...'
            ];

            try {
                $service = new Plug4MarketService();
                
                // Testar autenticação do token especificamente
                $tokenAuthTest = $service->testTokenAuthentication();
                
                if ($tokenAuthTest) {
                    $testResults[4]['status'] = 'success';
                    $testResults[4]['message'] = 'Autenticação funcionando - API acessível';
                } else {
                    // Testar a conexão geral que inclui refresh automático
                    $connectionTest = $service->testConnection();
                    
                    if ($connectionTest) {
                        $testResults[4]['status'] = 'success';
                        $testResults[4]['message'] = 'Autenticação funcionando após renovação do token';
                    } else {
                        $testResults[4]['status'] = 'failed';
                        $testResults[4]['message'] = 'Falha na autenticação - token pode estar inválido';
                        $overallSuccess = false;
                        $errorMessages[] = 'Falha na autenticação com a API';
                    }
                }
            } catch (\Exception $e) {
                $testResults[4]['status'] = 'failed';
                $testResults[4]['message'] = 'Erro na autenticação: ' . $e->getMessage();
                $overallSuccess = false;
                $errorMessages[] = 'Erro na autenticação: ' . $e->getMessage();
            }

            // Teste 6: Testar busca de produtos
            $testResults[] = [
                'test' => 'Busca de Produtos',
                'status' => 'checking',
                'message' => 'Testando busca de produtos...'
            ];

            try {
                $service = new Plug4MarketService();
                $products = $service->listProducts(['limit' => 1]);
                
                if (isset($products['data']) && is_array($products['data'])) {
                    $testResults[5]['status'] = 'success';
                    $testResults[5]['message'] = 'Busca de produtos funcionando - ' . count($products['data']) . ' produto(s) encontrado(s)';
                } else {
                    $testResults[5]['status'] = 'warning';
                    $testResults[5]['message'] = 'Busca de produtos retornou resposta inesperada';
                }
            } catch (\Exception $e) {
                $testResults[5]['status'] = 'failed';
                $testResults[5]['message'] = 'Erro na busca de produtos: ' . $e->getMessage();
                $overallSuccess = false;
                $errorMessages[] = 'Erro na busca de produtos: ' . $e->getMessage();
            }

            // Teste 7: Verificar configurações específicas
            $testResults[] = [
                'test' => 'Configurações Específicas',
                'status' => 'checking',
                'message' => 'Verificando configurações específicas...'
            ];

            $configIssues = [];
            if (empty($settings->seller_id)) $configIssues[] = 'Seller ID';
            if (empty($settings->software_house_cnpj)) $configIssues[] = 'CNPJ Software House';
            if (empty($settings->store_cnpj)) $configIssues[] = 'CNPJ Store';
            if (empty($settings->user_id)) $configIssues[] = 'User ID';

            if (empty($configIssues)) {
                $testResults[6]['status'] = 'success';
                $testResults[6]['message'] = 'Todas as configurações específicas estão preenchidas';
            } else {
                $testResults[6]['status'] = 'warning';
                $testResults[6]['message'] = 'Configurações pendentes: ' . implode(', ', $configIssues);
            }

            // Atualizar status do teste nas configurações
            $settings->last_test_at = now();
            $settings->last_test_success = $overallSuccess;
            $settings->last_test_message = $overallSuccess ? 'Teste realizado com sucesso' : 'Falhas encontradas: ' . implode(', ', $errorMessages);
            $settings->save();

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Log do teste
            Plug4MarketLog::create([
                'action' => 'test_connection',
                'status' => $overallSuccess ? 'success' : 'error',
                'message' => $overallSuccess ? 'Teste de conexão realizado com sucesso' : 'Falhas encontradas no teste de conexão',
                'details' => [
                    'test_results' => $testResults,
                    'overall_success' => $overallSuccess,
                    'error_messages' => $errorMessages,
                    'settings_used' => [
                        'base_url' => $settings->base_url,
                        'sandbox' => $settings->sandbox,
                        'has_tokens' => !empty($settings->access_token) && !empty($settings->refresh_token)
                    ]
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return view('plug4market.settings.test-results', compact('testResults', 'overallSuccess', 'settings', 'title'));

        } catch (\Exception $e) {
            $testResults[] = [
                'test' => 'Erro Geral',
                'status' => 'failed',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ];

            $settings->last_test_at = now();
            $settings->last_test_success = false;
            $settings->last_test_message = 'Erro inesperado: ' . $e->getMessage();
            $settings->save();

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Log the error
            Plug4MarketLog::create([
                'action' => 'test_connection',
                'status' => 'error',
                'message' => 'Erro inesperado durante teste de conexão: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'test_results' => $testResults
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return view('plug4market.settings.test-results', compact('testResults', 'overallSuccess', 'settings', 'title'));
        }
    }

    public function getTokenInfo()
    {
        $startTime = microtime(true);
        $settings = Plug4MarketSetting::getSettings();
        
        if (!$settings->isConfigured()) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            Plug4MarketLog::create([
                'action' => 'token_info',
                'status' => 'error',
                'message' => 'Tokens não configurados',
                'details' => [
                    'error' => 'Tokens não configurados',
                    'has_access_token' => !empty($settings->access_token),
                    'has_refresh_token' => !empty($settings->refresh_token)
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return response()->json([
                'error' => 'Tokens não configurados'
            ], 400);
        }

        try {
            $service = new Plug4MarketService();
            $tokenInfo = $service->getTokenInfo();
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            Plug4MarketLog::create([
                'action' => 'token_info',
                'status' => 'success',
                'message' => 'Informações do token obtidas com sucesso',
                'details' => [
                    'token_info' => $tokenInfo,
                    'base_url' => $settings->base_url
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);
            
            return response()->json($tokenInfo);
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            Plug4MarketLog::create([
                'action' => 'token_info',
                'status' => 'error',
                'message' => 'Erro ao obter informações do token: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return response()->json([
                'error' => 'Erro ao obter informações do token: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logs(Request $request)
    {
        $title = 'Logs Plug4Market';
        
        try {
            $query = Plug4MarketLog::query();
            
            // Filter by action
            if ($request->has('action') && $request->action) {
                $query->where('action', $request->action);
            }
            
            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            // Filter by type (products, categories, orders, labels, sync, etc.)
            if ($request->has('type') && $request->type) {
                switch ($request->type) {
                    case 'products':
                        $query->whereIn('action', ['create_product', 'update_product', 'delete_product', 'sync_product']);
                        break;
                    case 'categories':
                        $query->whereIn('action', ['create_category', 'update_category', 'delete_category', 'sync_category']);
                        break;
                    case 'orders':
                        $query->whereIn('action', ['create_order', 'update_order', 'delete_order', 'sync_order']);
                        break;
                    case 'labels':
                        $query->whereIn('action', ['create_label', 'update_label', 'delete_label', 'sync_label']);
                        break;
                    case 'sync':
                        $query->whereIn('action', ['sync_product', 'sync_category', 'sync_order', 'sync_label', 'sync_all_products', 'sync_all_categories']);
                        break;
                    case 'errors':
                        $query->where('status', 'error');
                        break;
                    case 'success':
                        $query->where('status', 'success');
                        break;
                }
            }
            
            // Filter by date range
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Search in message
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('message', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhereJsonContains('details', $search);
                });
            }
            
            $logs = $query->orderBy('created_at', 'desc')->paginate(50);
            
            // Get comprehensive statistics
            $stats = [
                'total' => Plug4MarketLog::count(),
                'today' => Plug4MarketLog::getToday()->count(),
                'errors' => Plug4MarketLog::getByStatus('error')->count(),
                'success' => Plug4MarketLog::getByStatus('success')->count(),
                'products' => Plug4MarketLog::whereIn('action', ['create_product', 'update_product', 'delete_product', 'sync_product'])->count(),
                'categories' => Plug4MarketLog::whereIn('action', ['create_category', 'update_category', 'delete_category', 'sync_category'])->count(),
                'orders' => Plug4MarketLog::whereIn('action', ['create_order', 'update_order', 'delete_order', 'sync_order'])->count(),
                'labels' => Plug4MarketLog::whereIn('action', ['create_label', 'update_label', 'delete_label', 'sync_label'])->count(),
                'sync_errors' => Plug4MarketLog::whereIn('action', ['sync_product', 'sync_category', 'sync_order', 'sync_label', 'sync_all_products', 'sync_all_categories'])
                    ->where('status', 'error')->count(),
                'recent_errors' => Plug4MarketLog::where('status', 'error')
                    ->where('created_at', '>=', now()->subDays(7))->count(),
            ];
            
            // Get unique actions and statuses for filters
            $actions = Plug4MarketLog::distinct()->pluck('action')->sort();
            $statuses = Plug4MarketLog::distinct()->pluck('status')->sort();
            
            // Get types for filter
            $types = [
                'all' => 'Todos os tipos',
                'products' => 'Produtos',
                'categories' => 'Categorias', 
                'orders' => 'Pedidos',
                'labels' => 'Etiquetas',
                'sync' => 'Sincronização',
                'errors' => 'Erros',
                'success' => 'Sucessos'
            ];
            
        } catch (\Exception $e) {
            // If there's an error (e.g., table doesn't exist), use empty data
            $logs = collect()->paginate(50);
            $stats = [
                'total' => 0,
                'today' => 0,
                'errors' => 0,
                'success' => 0,
                'products' => 0,
                'categories' => 0,
                'orders' => 0,
                'labels' => 0,
                'sync_errors' => 0,
                'recent_errors' => 0,
            ];
            $actions = collect();
            $statuses = collect();
            $types = [];
        }
        
        return view('plug4market.settings.logs', compact('logs', 'title', 'stats', 'actions', 'statuses', 'types'));
    }

    public function logDetails($id)
    {
        try {
            $log = Plug4MarketLog::findOrFail($id);
            $title = 'Detalhes do Log - Plug4Market';
            
            return view('plug4market.settings.log-details', compact('log', 'title'));
        } catch (\Exception $e) {
            // If there's an error (e.g., log doesn't exist or table doesn't exist)
            return redirect()->route('plug4market.settings.logs')
                ->with('error', 'Log não encontrado ou tabela de logs não existe.');
        }
    }

    public function testDatabase()
    {
        try {
            // Check if table exists
            $tableExists = Schema::hasTable('plug4market_logs');
            
            if (!$tableExists) {
                return response()->json([
                    'error' => 'Tabela plug4market_logs não existe. Execute as migrações.',
                    'table_exists' => false
                ]);
            }
            
            // Check if there are any logs
            $logCount = Plug4MarketLog::count();
            $recentLogs = Plug4MarketLog::getRecent(5);
            
            return response()->json([
                'success' => true,
                'table_exists' => true,
                'log_count' => $logCount,
                'recent_logs' => $recentLogs->map(function($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'status' => $log->status,
                        'message' => $log->message,
                        'created_at' => $log->created_at
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao verificar banco de dados: ' . $e->getMessage(),
                'table_exists' => false
            ]);
        }
    }

    public function generateTokens(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $data = $request->validate([
                'user_login' => 'required|string|email',
                'user_password' => 'required|string|min:6',
                'store_cnpj' => 'required|string|max:20',
                'software_house_cnpj' => 'required|string|max:20',
            ]);

            $service = new Plug4MarketService();
            
            // 1. Fazer login do usuário
            $loginResult = $service->loginUser($data['user_login'], $data['user_password']);
            
            if (!$loginResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha no login do usuário. Verifique as credenciais.'
                ], 400);
            }

            // 2. Gerar tokens da loja
            $tokenResult = $service->generateStoreTokens($data['store_cnpj'], $data['software_house_cnpj']);
            
            if (!$tokenResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha na geração dos tokens da loja. Verifique os CNPJs.'
                ], 400);
            }

            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log do sucesso
            Plug4MarketLog::create([
                'action' => 'generate_tokens',
                'status' => 'success',
                'message' => 'Tokens gerados com sucesso',
                'details' => [
                    'user_login' => $data['user_login'],
                    'store_cnpj' => $data['store_cnpj'],
                    'software_house_cnpj' => $data['software_house_cnpj'],
                    'user_info' => $loginResult['user'] ?? null
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tokens gerados com sucesso!',
                'user_info' => $loginResult['user'] ?? null
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            Plug4MarketLog::create([
                'action' => 'generate_tokens',
                'status' => 'error',
                'message' => 'Erro ao gerar tokens: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'execution_time_ms' => $executionTime
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar tokens: ' . $e->getMessage()
            ], 500);
        }
    }
} 