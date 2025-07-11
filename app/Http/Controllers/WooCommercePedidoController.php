<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\WooCommerceConfig;
use App\Models\Produto;
use App\Utils\WooCommerceUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WooCommercePedido;
use App\Models\WooCommerceItemPedido;
use App\Services\WooCommerceService;

class WooCommercePedidoController extends Controller
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
        $pedidos = WooCommercePedido::where('empresa_id', $this->empresa_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('woocommerce.pedidos.index', compact('pedidos'))
            ->with('title', 'Pedidos WooCommerce');
    }

    public function show($id)
    {
        $pedido = WooCommercePedido::where('empresa_id', $this->empresa_id)
            ->with('itens')
            ->findOrFail($id);
        
        return view('woocommerce.pedidos.show', compact('pedido'))
            ->with('title', 'Detalhes do Pedido #' . $pedido->woocommerce_id);
    }

    public function sincronizar()
    {
        try {
            $this->woocommerceService->initialize($this->empresa_id);
            
            if (!$this->woocommerceService->isConnected()) {
                return redirect()->route('woocommerce-pedidos.index')
                    ->with('error', 'Configuração do WooCommerce não encontrada. Por favor, configure primeiro.');
            }

            $result = $this->woocommerceService->sincronizarTudo();

            if ($result['success']) {
                return redirect()->route('woocommerce-pedidos.index')
                    ->with('message', 'Pedidos sincronizados com sucesso!');
            } else {
                return redirect()->route('woocommerce-pedidos.index')
                    ->with('error', 'Erro ao sincronizar pedidos: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('woocommerce-pedidos.index')
                ->with('error', 'Erro ao sincronizar pedidos: ' . $e->getMessage());
        }
    }
}
