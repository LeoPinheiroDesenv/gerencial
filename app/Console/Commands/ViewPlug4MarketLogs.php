<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViewPlug4MarketLogs extends Command
{
    protected $signature = 'plug4market:logs {--lines=50 : Número de linhas para mostrar} {--tail : Mostrar apenas as últimas linhas} {--search= : Filtrar por termo de busca}';
    protected $description = 'Visualizar logs relacionados ao Plug4Market';

    public function handle()
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = $this->option('lines');
        $tail = $this->option('tail');
        $search = $this->option('search');

        if (!File::exists($logFile)) {
            $this->error('❌ Arquivo de log não encontrado: ' . $logFile);
            return 1;
        }

        $this->info('📋 Visualizando logs do Plug4Market...');
        $this->info("   Arquivo: {$logFile}");
        $this->info("   Linhas: {$lines}");
        $this->info("   Modo tail: " . ($tail ? 'Sim' : 'Não'));
        if ($search) {
            $this->info("   Busca: {$search}");
        }
        $this->newLine();

        try {
            $content = File::get($logFile);
            $logLines = explode("\n", $content);

            // Filtrar apenas logs do Plug4Market
            $plug4marketLogs = [];
            foreach ($logLines as $line) {
                if (str_contains($line, 'Plug4Market') || 
                    str_contains($line, 'plug4market') || 
                    str_contains($line, 'PLUG4MARKET')) {
                    
                    if (!$search || str_contains(strtolower($line), strtolower($search))) {
                        $plug4marketLogs[] = $line;
                    }
                }
            }

            if (empty($plug4marketLogs)) {
                $this->warn('⚠️  Nenhum log do Plug4Market encontrado');
                if ($search) {
                    $this->line("   Tente remover o filtro de busca ou usar um termo diferente");
                }
                return 0;
            }

            $this->info('✅ Encontrados ' . count($plug4marketLogs) . ' logs do Plug4Market');

            // Aplicar limite de linhas
            if ($tail) {
                $plug4marketLogs = array_slice($plug4marketLogs, -$lines);
            } else {
                $plug4marketLogs = array_slice($plug4marketLogs, 0, $lines);
            }

            $this->newLine();
            $this->line('📄 Últimos logs do Plug4Market:');
            $this->newLine();

            foreach ($plug4marketLogs as $line) {
                // Colorir diferentes tipos de log
                if (str_contains($line, 'ERROR')) {
                    $this->error($line);
                } elseif (str_contains($line, 'WARNING')) {
                    $this->warn($line);
                } elseif (str_contains($line, 'INFO')) {
                    $this->info($line);
                } else {
                    $this->line($line);
                }
            }

            $this->newLine();
            $this->info('📊 Estatísticas dos logs mostrados:');
            $this->line('   Total de linhas: ' . count($plug4marketLogs));
            
            $errorCount = count(array_filter($plug4marketLogs, fn($line) => str_contains($line, 'ERROR')));
            $warningCount = count(array_filter($plug4marketLogs, fn($line) => str_contains($line, 'WARNING')));
            $infoCount = count(array_filter($plug4marketLogs, fn($line) => str_contains($line, 'INFO')));
            
            $this->line('   Erros: ' . $errorCount);
            $this->line('   Avisos: ' . $warningCount);
            $this->line('   Informações: ' . $infoCount);

        } catch (\Exception $e) {
            $this->error('❌ Erro ao ler arquivo de log: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 