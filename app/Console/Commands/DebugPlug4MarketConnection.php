<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketSetting;
use App\Models\Plug4MarketLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DebugPlug4MarketConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plug4market:debug {--detailed : Mostrar informações detalhadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug detalhado da conexão Plug4Market';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Iniciando debug detalhado do Plug4Market...');
        $this->newLine();

        // 1. Verificar configurações
        $this->info('📋 1. Verificando configurações...');
        $settings = Plug4MarketSetting::getSettings();
        
        $this->table(
            ['Configuração', 'Valor', 'Status'],
            [
                ['Base URL', $settings->base_url ?? 'Não configurado', $settings->base_url ? '✅' : '❌'],
                ['Sandbox', $settings->sandbox ? 'Sim' : 'Não', '✅'],
                ['User Login', $settings->user_login ? 'Configurado' : 'Não configurado', $settings->user_login ? '✅' : '❌'],
                ['User Password', $settings->user_password ? 'Configurado' : 'Não configurado', $settings->user_password ? '✅' : '❌'],
                ['Access Token', $settings->access_token ? 'Configurado (' . strlen($settings->access_token) . ' chars)' : 'Não configurado', $settings->access_token ? '✅' : '❌'],
                ['Refresh Token', $settings->refresh_token ? 'Configurado (' . strlen($settings->refresh_token) . ' chars)' : 'Não configurado', $settings->refresh_token ? '✅' : '❌'],
                ['Seller ID', $settings->seller_id ?? 'Não configurado', $settings->seller_id ? '✅' : '❌'],
                ['Software House CNPJ', $settings->software_house_cnpj ?? 'Não configurado', $settings->software_house_cnpj ? '✅' : '❌'],
                ['Store CNPJ', $settings->store_cnpj ?? 'Não configurado', $settings->store_cnpj ? '✅' : '❌'],
                ['User ID', $settings->user_id ?? 'Não configurado', $settings->user_id ? '✅' : '❌'],
            ]
        );

        // 2. Testar conectividade básica
        $this->info('🌐 2. Testando conectividade básica...');
        try {
            $baseUrl = $settings->base_url ?? 'https://api.sandbox.plug4market.com.br';
            $response = Http::timeout(10)->get($baseUrl . '/categories');
            
            $this->info("   Status: {$response->status()}");
            $this->info("   Conectividade: " . ($response->status() !== 0 ? '✅ OK' : '❌ Falhou'));
            
            if ($this->option('detailed')) {
                $this->info("   Resposta: " . substr($response->body(), 0, 200));
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro: " . $e->getMessage());
        }

        // 3. Verificar tokens
        $this->info('🔑 3. Verificando tokens...');
        if ($settings->access_token) {
            $service = new Plug4MarketService();
            $tokenValid = $service->validateToken();
            $this->info("   Token JWT válido: " . ($tokenValid ? '✅ Sim' : '❌ Não'));
            
            if ($this->option('detailed') && $settings->access_token) {
                $tokenParts = explode('.', $settings->access_token);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode($tokenParts[1]), true);
                    if ($payload) {
                        $this->info("   Token expira em: " . (isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : 'N/A'));
                        $this->info("   Token emitido em: " . (isset($payload['iat']) ? date('Y-m-d H:i:s', $payload['iat']) : 'N/A'));
                    }
                }
            }
        } else {
            $this->warn("   ⚠️  Nenhum token configurado");
        }

        // 4. Testar autenticação
        $this->info('🔐 4. Testando autenticação...');
        if ($settings->access_token) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $settings->access_token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(10)->get($settings->base_url . '/categories');

                $this->info("   Status: {$response->status()}");
                $this->info("   Autenticação: " . ($response->successful() ? '✅ OK' : '❌ Falhou'));
                
                if (!$response->successful()) {
                    $this->error("   Resposta de erro: " . substr($response->body(), 0, 200));
                }
                
                if ($this->option('detailed')) {
                    $this->info("   Headers de resposta: " . json_encode($response->headers()));
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Erro: " . $e->getMessage());
            }
        } else {
            $this->warn("   ⚠️  Nenhum token para testar");
        }

        // 5. Testar refresh de token
        $this->info('🔄 5. Testando refresh de token...');
        if ($settings->refresh_token) {
            try {
                // Testar refresh manualmente
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($settings->base_url . '/auth/refresh', [
                    'refreshToken' => $settings->refresh_token
                ]);
                
                $this->info("   Status: {$response->status()}");
                $this->info("   Refresh: " . ($response->successful() ? '✅ OK' : '❌ Falhou'));
                
                if (!$response->successful()) {
                    $this->error("   Resposta de erro: " . substr($response->body(), 0, 200));
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Erro no refresh: " . $e->getMessage());
            }
        } else {
            $this->warn("   ⚠️  Nenhum refresh token configurado");
        }

        // 6. Testar geração de tokens
        $this->info('🔧 6. Testando geração de tokens...');
        if ($settings->user_login && $settings->user_password && $settings->store_cnpj && $settings->software_house_cnpj) {
            try {
                $service = new Plug4MarketService();
                
                // Testar login
                $this->info("   Testando login do usuário...");
                $loginResult = $service->loginUser($settings->user_login, $settings->user_password);
                
                if ($loginResult) {
                    $this->info("   ✅ Login bem-sucedido");
                    
                    // Testar geração de tokens
                    $this->info("   Testando geração de tokens da loja...");
                    $tokenResult = $service->generateStoreTokens($settings->store_cnpj, $settings->software_house_cnpj);
                    
                    if ($tokenResult) {
                        $this->info("   ✅ Tokens gerados com sucesso");
                    } else {
                        $this->error("   ❌ Falha na geração de tokens");
                    }
                } else {
                    $this->error("   ❌ Falha no login");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Erro: " . $e->getMessage());
            }
        } else {
            $this->warn("   ⚠️  Credenciais incompletas para teste");
        }

        // 7. Verificar logs recentes
        $this->info('📝 7. Verificando logs recentes...');
        try {
            $recentLogs = Plug4MarketLog::orderBy('created_at', 'desc')->limit(5)->get();
            
            if ($recentLogs->count() > 0) {
                $this->table(
                    ['Data/Hora', 'Ação', 'Status', 'Mensagem'],
                    $recentLogs->map(function($log) {
                        return [
                            $log->created_at->format('d/m/Y H:i:s'),
                            $log->action,
                            $log->status,
                            substr($log->message, 0, 50) . '...'
                        ];
                    })->toArray()
                );
            } else {
                $this->warn("   ⚠️  Nenhum log encontrado");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao verificar logs: " . $e->getMessage());
        }

        // 8. Teste completo do service
        $this->info('🧪 8. Teste completo do service...');
        try {
            $service = new Plug4MarketService();
            $connectionTest = $service->testConnection();
            $this->info("   Teste de conexão: " . ($connectionTest ? '✅ OK' : '❌ Falhou'));
            
            if ($connectionTest) {
                $this->info("   ✅ API funcionando corretamente");
            } else {
                $this->error("   ❌ Problemas na API");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro no teste: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎯 Debug concluído!');
        
        // Resumo final
        $this->newLine();
        $this->info('📊 RESUMO:');
        $this->info('   • Configure as credenciais do usuário se não estiverem configuradas');
        $this->info('   • Use o comando "php artisan plug4market:generate-tokens" para gerar tokens');
        $this->info('   • Verifique se a URL da API está correta');
        $this->info('   • Certifique-se de que os CNPJs estão corretos');
    }
} 