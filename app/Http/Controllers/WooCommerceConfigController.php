<?php

namespace App\Http\Controllers;

use App\Models\WooCommerceConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WooCommerceConfigController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $value = session('user_logged');
            if(!$value){
                return redirect("/login");
            }
            $this->empresa_id = $value['empresa'];
            return $next($request);
        });
    }

    public function index()
    {
        $config = WooCommerceConfig::where('empresa_id', $this->empresa_id)->first();
        return view('woocommerce.config.index', compact('config'))->with('title', 'Configurações WooCommerce');
    }

    public function store(Request $request)
    {
        try {
            $data = [
                'empresa_id' => $this->empresa_id,
                'store_url' => $request->store_url,
                'consumer_key' => $request->consumer_key,
                'consumer_secret' => $request->consumer_secret,
                'is_active' => $request->has('ativar_integracao'),
                'sync_products' => $request->has('sincronizar_produtos'),
                'sync_orders' => $request->has('sincronizar_pedidos'),
                'sync_stock' => $request->has('sincronizar_estoque'),
                'price_markup' => $request->markup_preco ?? 0,
                'sync_interval' => $request->intervalo_sincronizacao ?? 60,
                'auto_sync' => $request->has('sincronizacao_automatica'),
                'default_status' => 'publish'
            ];

            $config = WooCommerceConfig::where('empresa_id', $this->empresa_id)->first();

            if ($config) {
                $config->update($data);
                Log::info('Configuração do WooCommerce atualizada para empresa_id: ' . $this->empresa_id);
            } else {
                WooCommerceConfig::create($data);
                Log::info('Nova configuração do WooCommerce criada para empresa_id: ' . $this->empresa_id);
            }

            return redirect()->route('woocommerce-config')
                ->with('success', 'Configurações salvas com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar configurações do WooCommerce: ' . $e->getMessage());
            return redirect()->route('woocommerce-config')
                ->with('error', 'Erro ao salvar configurações: ' . $e->getMessage());
        }
    }
}
