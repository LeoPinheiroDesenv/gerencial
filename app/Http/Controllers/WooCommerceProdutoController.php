<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoWooCommerce;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;

class WooCommerceProdutoController extends Controller
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
        $produtos = Produto::where('empresa_id', $this->empresa_id)
            ->whereHas('woocommerce')
            ->where('inativo', false)
            ->orderBy('nome')
            ->paginate(20);

        return view('woocommerce.produtos.index', compact('produtos'))->with('title', 'WooCommerce Produtos');
    }

    public function sincronizar($id)
    {
        try {
            $this->woocommerceService->initialize($this->empresa_id);
            
            if (!$this->woocommerceService->isConnected()) {
                return redirect()->route('woocommerce-produtos.index')
                    ->with('error', 'Configuração do WooCommerce não encontrada. Por favor, configure primeiro.');
            }

            $produto = Produto::where('empresa_id', $this->empresa_id)
                ->where('id', $id)
                ->firstOrFail();

            $result = $this->woocommerceService->sincronizarProduto($produto);

            if ($result['success']) {
                return redirect()->route('woocommerce-produtos.index')
                    ->with('message', 'Produto sincronizado com sucesso!');
            } else {
                return redirect()->route('woocommerce-produtos.index')
                    ->with('error', 'Erro ao sincronizar produto: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('woocommerce-produtos.index')
                ->with('error', 'Erro ao sincronizar produto: ' . $e->getMessage());
        }
    }
}
