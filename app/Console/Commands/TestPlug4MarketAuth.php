<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestPlug4MarketAuth extends Command
{
    protected $signature = 'plug4market:test-auth {--verbose : Mostrar detalhes completos}';
    protected $description = 'Teste específico de autenticação Plug4Market';

    public function handle()
    {
        $this->info('🔐 Teste de Autenticação Plug4Market');
        $this->newLine();

        $settings = Plug4MarketSetting::getSettings();
        $verbose = $this->option('verbose');

        // 1. Verificar se temos tokens
        $this->info('1. Verificando tokens...');
        if (!$settings->access_token) {
            $this->error('❌ Access Token não configurado');
            $this->info('💡 Use: php artisan plug4market:generate-tokens');
            return 1;
        }
        
        if (!$settings->refresh_token) {
            $this->error('❌ Refresh Token não configurado');
            $this->info('💡 Use: php artisan plug4market:generate-tokens');
            return 1;
        }

        $this->info('✅ Tokens configurados');
        if ($verbose) {
            $this->line("   Access Token: " . substr($settings->access_token, 0, 50) . "...");
            $this->line("   Refresh Token: " . substr($settings->refresh_token, 0, 50) . "...");
        }

        // 2. Validar formato JWT
        $this->info('2. Validando formato JWT...');
        $tokenParts = explode('.', $settings->access_token);
        if (count($tokenParts) !== 3) {
            $this->error('❌ Token não é um JWT válido');
            return 1;
        }

        try {
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            if (!$payload) {
                $this->error('❌ Não foi possível decodificar o payload do JWT');
                return 1;
            }

            $this->info('✅ JWT válido');
            if ($verbose) {
                $this->line("   Emitido em: " . (isset($payload['iat']) ? date('Y-m-d H:i:s', $payload['iat']) : 'N/A'));
                $this->line("   Expira em: " . (isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : 'N/A'));
                
                if (isset($payload['exp']) && $payload['exp'] < time()) {
                    $this->warn('⚠️  Token expirado!');
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro ao decodificar JWT: ' . $e->getMessage());
            return 1;
        }

        // 3. Testar autenticação direta
        $this->info('3. Testando autenticação direta...');
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $settings->access_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(15)->get($settings->base_url . '/categories');

            $this->info("   Status: {$response->status()}");
            
            if ($response->successful()) {
                $this->info('✅ Autenticação bem-sucedida!');
                if ($verbose) {
                    $this->line("   Resposta: " . substr($response->body(), 0, 200));
                }
            } else {
                $this->error('❌ Falha na autenticação');
                $this->error("   Resposta: " . substr($response->body(), 0, 300));
                
                if ($response->status() === 401) {
                    $this->warn('⚠️  Token inválido ou expirado (401)');
                    $this->info('💡 Tentando renovar token...');
                    
                    // 4. Tentar renovar token
                    $this->info('4. Renovando token...');
                    $refreshResponse = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->timeout(15)->post($settings->base_url . '/auth/refresh', [
                        'refreshToken' => $settings->refresh_token
                    ]);

                    $this->info("   Status do refresh: {$refreshResponse->status()}");
                    
                    if ($refreshResponse->successful()) {
                        $this->info('✅ Token renovado com sucesso!');
                        $refreshData = $refreshResponse->json();
                        
                        // Atualizar tokens no banco
                        $settings->update([
                            'access_token' => $refreshData['accessToken'],
                            'refresh_token' => $refreshData['refreshToken']
                        ]);
                        
                        $this->info('💾 Tokens atualizados no banco de dados');
                        
                        // Testar novamente com novo token
                        $this->info('5. Testando com novo token...');
                        $newResponse = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $refreshData['accessToken'],
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ])->timeout(15)->get($settings->base_url . '/categories');

                        $this->info("   Status: {$newResponse->status()}");
                        
                        if ($newResponse->successful()) {
                            $this->info('✅ Autenticação funcionando após renovação!');
                        } else {
                            $this->error('❌ Ainda falhando após renovação');
                            $this->error("   Resposta: " . substr($newResponse->body(), 0, 300));
                        }
                    } else {
                        $this->error('❌ Falha na renovação do token');
                        $this->error("   Resposta: " . substr($refreshResponse->body(), 0, 300));
                        $this->info('💡 Use: php artisan plug4market:generate-tokens');
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro na requisição: ' . $e->getMessage());
            return 1;
        }

        // 5. Testar service completo
        $this->info('6. Testando service completo...');
        try {
            $service = new Plug4MarketService();
            $connectionTest = $service->testConnection();
            
            if ($connectionTest) {
                $this->info('✅ Service funcionando corretamente');
            } else {
                $this->error('❌ Service com problemas');
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro no service: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎯 Teste concluído!');
        
        return 0;
    }
} 