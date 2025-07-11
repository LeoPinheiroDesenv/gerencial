<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketOrder;
use Illuminate\Support\Facades\Log;

class ProcessPlug4MarketInvoiceXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plug4market:process-invoice-xml 
                            {--order-id= : ID específico do pedido}
                            {--limit=50 : Limite de pedidos para processar}
                            {--force : Forçar reprocessamento mesmo se já baixado}
                            {--verbose : Mostrar informações detalhadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa XMLs de notas fiscais Plug4Market';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Processando XMLs de notas fiscais Plug4Market...');
        $this->newLine();

        $orderId = $this->option('order-id');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $verbose = $this->option('verbose');

        $service = new Plug4MarketService();

        // Processar pedido específico
        if ($orderId) {
            $order = Plug4MarketOrder::find($orderId);
            
            if (!$order) {
                $this->error("❌ Pedido #{$orderId} não encontrado!");
                return 1;
            }

            $this->info("📋 Processando pedido #{$order->id}...");
            
            if ($verbose) {
                $this->line("   Número: {$order->order_number}");
                $this->line("   Status: {$order->status_text}");
                $this->line("   Tem nota: " . ($order->hasInvoice() ? 'Sim' : 'Não'));
                $this->line("   Tem XML: " . ($order->hasInvoiceXml() ? 'Sim' : 'Não'));
            }

            $result = $this->processOrder($service, $order, $force, $verbose);
            
            if ($result['success']) {
                $this->info("✅ {$result['message']}");
            } else {
                $this->error("❌ {$result['error']}");
                return 1;
            }

            return 0;
        }

        // Processar múltiplos pedidos
        $this->info("📋 Buscando pedidos com nota fiscal...");
        
        $query = Plug4MarketOrder::whereNotNull('invoice_number')
            ->whereNotNull('invoice_key')
            ->where('invoice_status', 'approved');

        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('invoice_xml')
                  ->orWhere('invoice_xml_status', '!=', 'downloaded');
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->warn('⚠️  Nenhum pedido encontrado para processar');
            return 0;
        }

        $this->info("✅ {$orders->count()} pedidos encontrados para processar");
        $this->newLine();

        $processed = 0;
        $errors = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($orders->count());
        $progressBar->start();

        foreach ($orders as $order) {
            $result = $this->processOrder($service, $order, $force, false);
            
            if ($result['success']) {
                $processed++;
            } elseif ($result['skipped']) {
                $skipped++;
            } else {
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Estatísticas
        $this->info('📊 Estatísticas do processamento:');
        $this->line("   Processados com sucesso: {$processed}");
        $this->line("   Pulados: {$skipped}");
        $this->line("   Erros: {$errors}");
        $this->line("   Total: " . ($processed + $skipped + $errors));

        if ($verbose) {
            $this->newLine();
            $this->info('📋 Resumo dos pedidos processados:');
            
            $recentOrders = Plug4MarketOrder::whereNotNull('invoice_xml')
                ->where('invoice_xml_status', 'downloaded')
                ->orderBy('invoice_xml_downloaded_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($recentOrders as $order) {
                $status = $order->hasInvoiceXml() ? '✅' : '⚠️';
                $this->line("   {$status} #{$order->id} - {$order->order_number} - XML: {$order->invoice_xml_filename}");
            }
        }

        $this->newLine();
        $this->info('🎉 Processamento concluído!');
        
        return $errors > 0 ? 1 : 0;
    }

    /**
     * Processar um pedido específico
     */
    private function processOrder($service, $order, $force, $verbose)
    {
        // Verificar se já tem XML e não é para forçar
        if (!$force && $order->hasInvoiceXml()) {
            if ($verbose) {
                $this->line("   ⏭️  XML já baixado em: {$order->invoice_xml_downloaded_at}");
            }
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'XML já baixado anteriormente'
            ];
        }

        // Verificar se tem nota fiscal
        if (!$order->hasInvoice()) {
            if ($verbose) {
                $this->line("   ⚠️  Pedido não tem nota fiscal");
            }
            return [
                'success' => false,
                'error' => 'Pedido não tem nota fiscal'
            ];
        }

        try {
            $result = $service->processInvoiceXml($order->id);
            
            if ($result['success']) {
                if ($verbose) {
                    $this->line("   ✅ XML processado: {$result['filename']} ({$result['xml_size']} bytes)");
                }
                return [
                    'success' => true,
                    'message' => $result['message']
                ];
            } else {
                if ($verbose) {
                    $this->line("   ❌ Erro: {$result['error']}");
                }
                return [
                    'success' => false,
                    'error' => $result['error']
                ];
            }
        } catch (\Exception $e) {
            if ($verbose) {
                $this->line("   ❌ Exceção: {$e->getMessage()}");
            }
            return [
                'success' => false,
                'error' => 'Exceção: ' . $e->getMessage()
            ];
        }
    }
} 