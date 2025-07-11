<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WooCommerceConfig;
use App\Utils\WooCommerceUtil;
use Carbon\Carbon;

class WooCommerceSync extends Command
{
    protected $signature = 'woocommerce:sync';
    protected $description = 'Sincroniza produtos e pedidos com o WooCommerce';

    public function handle()
    {
        $configs = WooCommerceConfig::where('is_active', true)
            ->where('auto_sync', true)
            ->get();

        foreach ($configs as $config) {
            $lastSync = $config->last_sync;
            $interval = $config->sync_interval;

            if (!$lastSync || Carbon::parse($lastSync)->addMinutes($interval)->isPast()) {
                $this->info("Iniciando sincronização para empresa {$config->empresa_id}");

                try {
                    $wooCommerceUtil = new WooCommerceUtil($config->empresa_id);

                    if ($config->sync_products) {
                        $this->info("Sincronizando produtos...");
                        // Implementar lógica de sincronização de produtos
                    }

                    if ($config->sync_orders) {
                        $this->info("Sincronizando pedidos...");
                        // Implementar lógica de sincronização de pedidos
                    }

                    if ($config->sync_stock) {
                        $this->info("Sincronizando estoque...");
                        // Implementar lógica de sincronização de estoque
                    }

                    $config->last_sync = now();
                    $config->save();

                    $this->info("Sincronização concluída com sucesso!");
                } catch (\Exception $e) {
                    $this->error("Erro durante a sincronização: " . $e->getMessage());
                }
            } else {
                $this->info("Empresa {$config->empresa_id} não precisa de sincronização neste momento");
            }
        }
    }
} 