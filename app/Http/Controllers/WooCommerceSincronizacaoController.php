<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoWooCommerce;
use App\Utils\WooCommerceUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\Cache;

class WooCommerceSincronizacaoController extends Controller
{
    protected $empresa_id = null;
    protected $woocommerceService;

    public function __construct(WooCommerceService $woocommerceService)
    {
        $this->middleware(function ($request, $next) {
            $value = session('user_logged');
            if(!$value){
                return redirect("/login");
            }
            $this->empresa_id = $value['empresa'];
            return $next($request);
        });
        $this->woocommerceService = $woocommerceService;
    }
    
    public function index()
    {
        return view('woocommerce.sincronizacao.index')->with('title', 'Sincronização WooCommerce');
    }
    
    public function executar(Request $request)
    {
        try {
            $this->woocommerceService->initialize($this->empresa_id);
            
            // Obtém o tipo e direção da sincronização do request
            $tipo = $request->input('tipo', 'tudo');
            $direcao = $request->input('direcao', 'export');
            
            // Inicializa o cache de progresso
            $this->initializeProgress();
            
            // Inicia a sincronização em background
            if ($direcao === 'import') {
                // Importar do WooCommerce
                if ($tipo === 'produtos' || $tipo === 'tudo') {
                    $this->woocommerceService->importarProdutos($this->empresa_id);
                }
                if ($tipo === 'pedidos' || $tipo === 'tudo') {
                    $this->woocommerceService->importarPedidos($this->empresa_id);
                }
            } else {
                // Exportar para o WooCommerce
                if ($tipo === 'produtos' || $tipo === 'tudo') {
                    $this->woocommerceService->exportarProdutos($this->empresa_id);
                }
                if ($tipo === 'pedidos' || $tipo === 'tudo') {
                    $this->woocommerceService->exportarPedidos($this->empresa_id);
                }
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sincronização iniciada'
                ]);
            }
            
            return redirect()->route('woocommerce-sincronizacao.index')
                ->with('message', 'Sincronização iniciada!');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            
            return redirect()->route('woocommerce-sincronizacao.index')
                ->with('error', 'Erro na sincronização: ' . $e->getMessage());
        }
    }
    
    public function progresso()
    {
        $progress = Cache::get('woocommerce_sync_progress_' . $this->empresa_id, [
            'status' => 'pending',
            'progress' => 0,
            'items_processed' => 0,
            'total_items' => 0,
            'status_text' => 'Aguardando início...',
            'logs' => []
        ]);
        
        return response()->json($progress);
    }
    
    private function initializeProgress()
    {
        Cache::put('woocommerce_sync_progress_' . $this->empresa_id, [
            'status' => 'processing',
            'progress' => 0,
            'items_processed' => 0,
            'total_items' => 0,
            'status_text' => 'Iniciando sincronização...',
            'logs' => []
        ], 3600); // Expira em 1 hora
    }
    
    private function updateProgress($progress, $itemsProcessed, $totalItems, $statusText, $logs = [])
    {
        $currentProgress = Cache::get('woocommerce_sync_progress_' . $this->empresa_id, []);
        $currentLogs = $currentProgress['logs'] ?? [];
        
        Cache::put('woocommerce_sync_progress_' . $this->empresa_id, [
            'status' => 'processing',
            'progress' => $progress,
            'items_processed' => $itemsProcessed,
            'total_items' => $totalItems,
            'status_text' => $statusText,
            'logs' => array_merge($currentLogs, $logs)
        ], 3600);
    }
    
    private function completeProgress($success = true, $message = '')
    {
        Cache::put('woocommerce_sync_progress_' . $this->empresa_id, [
            'status' => $success ? 'completed' : 'error',
            'progress' => 100,
            'status_text' => $success ? 'Sincronização concluída' : 'Erro na sincronização',
            'message' => $message,
            'logs' => array_merge(
                Cache::get('woocommerce_sync_progress_' . $this->empresa_id, [])['logs'] ?? [],
                [[
                    'message' => $success ? 'Sincronização concluída com sucesso!' : 'Erro: ' . $message,
                    'type' => $success ? 'success' : 'error'
                ]]
            )
        ], 3600);
    }
    
    private function startSync($tipo, $direcao)
    {
        // Inicia a sincronização em background
        dispatch(function() use ($tipo, $direcao) {
            try {
                if ($direcao === 'import') {
                    // Importar do WooCommerce
                    if ($tipo === 'produtos' || $tipo === 'tudo') {
                        $this->syncImportProducts();
                    }
                    if ($tipo === 'pedidos' || $tipo === 'tudo') {
                        $this->syncImportOrders();
                    }
                } else {
                    // Exportar para o WooCommerce
                    if ($tipo === 'produtos' || $tipo === 'tudo') {
                        $this->syncExportProducts();
                    }
                    if ($tipo === 'pedidos' || $tipo === 'tudo') {
                        $this->syncExportOrders();
                    }
                }
                
                $this->completeProgress(true);
            } catch (\Exception $e) {
                $this->completeProgress(false, $e->getMessage());
            }
        })->afterResponse();
    }
    
    private function syncImportProducts()
    {
        $produtos = $this->woocommerceService->getProducts();
        $total = count($produtos);
        $processados = 0;
        
        foreach ($produtos as $produto) {
            $processados++;
            $progress = ($processados / $total) * 100;
            
            $this->updateProgress(
                $progress,
                $processados,
                $total,
                "Importando produtos ({$processados}/{$total})",
                [[
                    'message' => "Processando produto: {$produto->name}",
                    'type' => 'info'
                ]]
            );
            
            $this->woocommerceService->importarProdutos($this->empresa_id);
        }
    }
    
    private function syncImportOrders()
    {
        $pedidos = $this->woocommerceService->getOrders();
        $total = count($pedidos);
        $processados = 0;
        
        foreach ($pedidos as $pedido) {
            $processados++;
            $progress = ($processados / $total) * 100;
            
            $this->updateProgress(
                $progress,
                $processados,
                $total,
                "Importando pedidos ({$processados}/{$total})",
                [[
                    'message' => "Processando pedido #{$pedido->id}",
                    'type' => 'info'
                ]]
            );
            
            $this->woocommerceService->importarPedidos($this->empresa_id);
        }
    }
    
    private function syncExportProducts()
    {
        $produtos = Produto::where('empresa_id', $this->empresa_id)->get();
        $total = count($produtos);
        $processados = 0;
        
        foreach ($produtos as $produto) {
            $processados++;
            $progress = ($processados / $total) * 100;
            
            $this->updateProgress(
                $progress,
                $processados,
                $total,
                "Exportando produtos ({$processados}/{$total})",
                [[
                    'message' => "Processando produto: {$produto->nome}",
                    'type' => 'info'
                ]]
            );
            
            $this->woocommerceService->exportarProdutos($this->empresa_id);
        }
    }
    
    private function syncExportOrders()
    {
        $pedidos = Pedido::where('empresa_id', $this->empresa_id)
            ->whereNull('woocommerce_id')
            ->get();
        $total = count($pedidos);
        $processados = 0;
        
        foreach ($pedidos as $pedido) {
            $processados++;
            $progress = ($processados / $total) * 100;
            
            $this->updateProgress(
                $progress,
                $processados,
                $total,
                "Exportando pedidos ({$processados}/{$total})",
                [[
                    'message' => "Processando pedido #{$pedido->id}",
                    'type' => 'info'
                ]]
            );
            
            $this->woocommerceService->exportarPedidos($this->empresa_id);
        }
    }
}
