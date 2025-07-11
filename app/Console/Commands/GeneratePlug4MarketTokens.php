<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Plug4MarketService;
use App\Models\Plug4MarketSetting;

class GeneratePlug4MarketTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plug4market:generate-tokens 
                            {--login= : Email do usuário}
                            {--password= : Senha do usuário}
                            {--store-cnpj= : CNPJ da loja}
                            {--software-house-cnpj= : CNPJ da software house}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerar tokens do Plug4Market automaticamente';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔑 Gerando tokens do Plug4Market...');
        $this->newLine();

        $settings = Plug4MarketSetting::getSettings();

        // Obter credenciais
        $login = $this->option('login') ?: $settings->user_login;
        $password = $this->option('password') ?: $settings->user_password;
        $storeCnpj = $this->option('store-cnpj') ?: $settings->store_cnpj;
        $softwareHouseCnpj = $this->option('software-house-cnpj') ?: $settings->software_house_cnpj;

        // Validar credenciais
        if (!$login) {
            $login = $this->ask('Digite o email do usuário:');
        }

        if (!$password) {
            $password = $this->secret('Digite a senha do usuário:');
        }

        if (!$storeCnpj) {
            $storeCnpj = $this->ask('Digite o CNPJ da loja:');
        }

        if (!$softwareHouseCnpj) {
            $softwareHouseCnpj = $this->ask('Digite o CNPJ da software house:');
        }

        // Validar se todos os campos estão preenchidos
        if (!$login || !$password || !$storeCnpj || !$softwareHouseCnpj) {
            $this->error('❌ Todos os campos são obrigatórios!');
            return 1;
        }

        $this->info('📋 Configurações:');
        $this->line("   Login: {$login}");
        $this->line("   Store CNPJ: {$storeCnpj}");
        $this->line("   Software House CNPJ: {$softwareHouseCnpj}");
        $this->line("   Base URL: {$settings->base_url}");
        $this->newLine();

        try {
            $service = new Plug4MarketService();
            
            // 1. Fazer login do usuário
            $this->info('👤 Fazendo login do usuário...');
            $loginResult = $service->loginUser($login, $password);
            
            if (!$loginResult) {
                $this->error('❌ Falha no login do usuário. Verifique as credenciais.');
                return 1;
            }

            $this->info('✅ Login realizado com sucesso!');
            $this->line("   User ID: " . ($loginResult['user']['id'] ?? 'N/A'));
            $this->line("   User Name: " . ($loginResult['user']['name'] ?? 'N/A'));

            // 2. Gerar tokens da loja
            $this->newLine();
            $this->info('🏪 Gerando tokens da loja...');
            $tokenResult = $service->generateStoreTokens($storeCnpj, $softwareHouseCnpj);
            
            if (!$tokenResult) {
                $this->error('❌ Falha na geração dos tokens da loja. Verifique os CNPJs.');
                return 1;
            }

            $this->info('✅ Tokens gerados com sucesso!');
            $this->line("   Access Token: " . substr($tokenResult['accessToken'], 0, 50) . "...");
            $this->line("   Refresh Token: " . substr($tokenResult['refreshToken'], 0, 50) . "...");

            // 3. Atualizar configurações
            $this->newLine();
            $this->info('💾 Salvando configurações...');
            
            $settings->update([
                'user_login' => $login,
                'user_password' => $password,
                'store_cnpj' => $storeCnpj,
                'software_house_cnpj' => $softwareHouseCnpj,
                'access_token' => $tokenResult['accessToken'],
                'refresh_token' => $tokenResult['refreshToken']
            ]);

            $this->info('✅ Configurações salvas!');

            // 4. Testar conexão
            $this->newLine();
            $this->info('🧪 Testando conexão...');
            
            $connectionTest = $service->testConnection();
            
            if ($connectionTest) {
                $this->info('✅ Conexão testada com sucesso!');
            } else {
                $this->warn('⚠️  Conexão falhou. Verifique os logs para mais detalhes.');
            }

            $this->newLine();
            $this->info('🎉 Tokens gerados e configurados com sucesso!');
            $this->line('Agora você pode usar a integração Plug4Market.');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao gerar tokens: ' . $e->getMessage());
            $this->line('Verifique os logs para mais detalhes.');
            return 1;
        }
    }
} 