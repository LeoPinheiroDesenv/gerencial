# Configuração Plug4Market

## Fluxo de Autenticação Correto

A API do Plug4Market utiliza um fluxo de autenticação em duas etapas:

### 1. Login do Usuário
Primeiro, você deve fazer login com suas credenciais de usuário para obter um token de usuário:

**Endpoint:**
- Sandbox: `POST https://api.sandbox.plug4market.com.br/auth/login`
- Produção: `POST https://api.plug4market.com.br/auth/login`

**Payload:**
```json
{
   "login": "seu-email@exemplo.com",
   "password": "sua-senha"
}
```

**Resposta:**
```json
{
   "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
   "user": {
      "id": "d10fd030-ef4a-4808-be6d-c621075fb7bf",
      "name": "Seu Nome",
      "role": "root",
      "login": "seu-email@exemplo.com"
   }
}
```

### 2. Geração de Tokens da Loja
Com o token do usuário, você pode gerar os tokens da loja:

**Endpoint:**
- Sandbox: `GET https://api.sandbox.plug4market.com.br/stores/{CNPJ_LOJA}/software-houses/{CNPJ_SH}/token?notEncoded=true`
- Produção: `GET https://api.plug4market.com.br/stores/{CNPJ_LOJA}/software-houses/{CNPJ_SH}/token?notEncoded=true`

**Headers:**
```
Authorization: Bearer {token_do_usuario}
```

**Resposta:**
```json
{
   "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
   "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

## Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Plug4Market API Configuration
PLUG4MARKET_USER_LOGIN=seu-email@exemplo.com
PLUG4MARKET_USER_PASSWORD=sua-senha
PLUG4MARKET_ACCESS_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
PLUG4MARKET_REFRESH_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
PLUG4MARKET_BASE_URL=https://api.sandbox.plug4market.com.br
PLUG4MARKET_SANDBOX=true
PLUG4MARKET_SELLER_ID=7
PLUG4MARKET_SOFTWARE_HOUSE_CNPJ=04026307000112
PLUG4MARKET_STORE_CNPJ=04026307000112
PLUG4MARKET_USER_ID=89579395-cc99-4a2a-8bb9-8e2165d7611d
PLUG4MARKET_API_VERSION=v1
PLUG4MARKET_TIMEOUT=30
PLUG4MARKET_RETRY_ATTEMPTS=3
```

## Como Configurar

### Opção 1: Interface Web (Recomendado)
1. Acesse `/plug4market/settings`
2. Preencha as credenciais de usuário (login e senha)
3. Preencha os CNPJs da loja e software house
4. Clique em "Gerar Tokens Automaticamente"
5. Os tokens serão gerados e salvos automaticamente

### Opção 2: Manual via .env
1. Configure as credenciais no arquivo `.env`
2. Execute o comando para gerar tokens:
   ```bash
   php artisan plug4market:generate-tokens
   ```

## Autenticação Implementada

### ✅ Melhorias Implementadas

- **Fluxo Correto**: Implementação do fluxo oficial de autenticação
- **Login do Usuário**: Método para fazer login e obter token de usuário
- **Geração de Tokens**: Método para gerar tokens da loja automaticamente
- **Refresh Token Automático**: Renovação automática quando o token expira
- **Headers Corretos**: Implementação dos headers conforme documentação oficial
- **Logs Detalhados**: Logs completos para debug e monitoramento
- **Tratamento de Erros**: Melhor tratamento de erros de autenticação
- **Teste de Conexão**: Comando Artisan para testar a integração

### 🔧 Como Funciona

1. **Login do Usuário**: Sistema faz login com credenciais para obter token de usuário
2. **Geração de Tokens**: Usa token do usuário para gerar tokens da loja
3. **Headers de Autenticação**: Usa `Authorization: Bearer {accessToken}` para operações
4. **Refresh Automático**: Quando recebe 401, tenta renovar o token automaticamente
5. **Retry Logic**: Segunda tentativa com o novo token após renovação
6. **Logs Detalhados**: Registra todas as operações para facilitar debug

## Estrutura do Projeto

### Controllers
- `app/Http/Controllers/Plug4MarketProductController.php` - CRUD de produtos
- `app/Http/Controllers/Plug4MarketOrderController.php` - CRUD de pedidos
- `app/Http/Controllers/Plug4MarketSettingController.php` - Configurações e testes

### Models
- `app/Models/Plug4MarketProduct.php` - Model de produtos
- `app/Models/Plug4MarketOrder.php` - Model de pedidos
- `app/Models/Plug4MarketOrderItem.php` - Model de itens de pedido
- `app/Models/Plug4MarketToken.php` - Model de tokens
- `app/Models/Plug4MarketSetting.php` - Model de configurações
- `app/Models/Plug4MarketLog.php` - Model de logs

### Services
- `app/Services/Plug4MarketService.php` - Integração com API Plug4Market (CORRIGIDO)

### Commands
- `app/Console/Commands/TestPlug4MarketConnection.php` - Comando para testar conexão
- `app/Console/Commands/DebugPlug4MarketConnection.php` - Comando para debug detalhado

### Views
- `resources/views/plug4market/products/` - Views de produtos
- `resources/views/plug4market/orders/` - Views de pedidos
- `resources/views/plug4market/settings/` - Views de configurações

### Rotas
- `/plug4market/products` - Listagem de produtos
- `/plug4market/orders` - Listagem de pedidos
- `/plug4market/settings` - Configurações da API
- `/plug4market/settings/generate-tokens` - Gerar tokens automaticamente

## Funcionalidades

### Produtos
- ✅ Listar produtos da API Plug4Market
- ✅ Criar novos produtos
- ✅ Editar produtos existentes
- ✅ Excluir produtos
- ✅ Visualizar detalhes
- ✅ Sincronização automática com API
- ✅ Sincronização manual individual
- ✅ Sincronização em lote

### Pedidos
- ✅ Listar pedidos da API Plug4Market
- ✅ Visualizar detalhes dos pedidos
- ✅ Atualizar status dos pedidos

### Autenticação (CORRIGIDA)
- ✅ Login do usuário com credenciais
- ✅ Geração automática de tokens da loja
- ✅ Validação JWT automática
- ✅ Refresh token automático
- ✅ Headers de autenticação corretos
- ✅ Armazenamento seguro de tokens
- ✅ Renovação automática quando expirado
- ✅ Logs detalhados de autenticação

### Categorias e Canais
- ✅ Listar categorias disponíveis
- ✅ Listar canais de venda
- ✅ Integração com Mercado Livre, Amazon, Shopee

### Testes e Monitoramento
- ✅ Teste de conexão via interface web
- ✅ Comando Artisan para testes
- ✅ Logs detalhados de todas as operações
- ✅ Validação de configurações
- ✅ Monitoramento de status da API

## Tratamento de Erros

O sistema trata automaticamente:
- ✅ Credenciais de usuário inválidas
- ✅ CNPJs inválidos ou não autorizados
- ✅ Token do usuário expirado
- ✅ Token da loja inválido ou expirado
- ✅ Token expirado (renovação automática)
- ✅ Erros de conexão com a API
- ✅ Dados inválidos
- ✅ Exceções gerais
- ✅ Sincronização parcial (local + API)
- ✅ Logs detalhados para debug

## Campos de Produto

### Campos Básicos
- `codigo` - SKU/Código do produto (único)
- `descricao` - Descrição do produto
- `nome` - Nome do produto
- `valor_unitario` - Preço unitário

### Campos Fiscais
- `ncm` - Classificação NCM
- `cfop` - CFOP
- `unidade` - Unidade de medida
- `aliquota_icms` - Alíquota ICMS (%)
- `aliquota_pis` - Alíquota PIS (%)
- `aliquota_cofins` - Alíquota COFINS (%)

### Campos de Dimensões
- `largura` - Largura em cm
- `altura` - Altura em cm
- `comprimento` - Comprimento em cm
- `peso` - Peso em kg

### Campos Adicionais
- `marca` - Marca do produto
- `categoria_id` - ID da categoria
- `estoque` - Quantidade em estoque
- `origem` - Origem (nacional/importado)
- `ean` - Código EAN
- `modelo` - Modelo do produto
- `garantia` - Garantia em meses

## Próximos Passos

1. **Configure as credenciais** no arquivo `.env` ou via interface web
2. **Execute as migrations**:
   ```bash
   php artisan migrate
   ```
3. **Gere os tokens**:
   ```bash
   php artisan plug4market:generate-tokens
   ```
4. **Teste a conexão**:
   ```bash
   php artisan plug4market:test --verbose
   ```
5. **Acesse o sistema**:
   ```
   http://seu-dominio/plug4market/settings
   ```

## Comandos Artisan

### Geração de Tokens
```bash
# Gerar tokens automaticamente
php artisan plug4market:generate-tokens
```

### Teste de Conexão
```bash
# Teste básico
php artisan plug4market:test

# Teste com informações detalhadas
php artisan plug4market:test --verbose
```

### Debug Detalhado
```bash
# Debug completo da integração
php artisan plug4market:debug
```

O comando de debug fornece:
- ✅ Verificação detalhada das configurações
- ✅ Teste de login do usuário
- ✅ Teste de geração de tokens da loja
- ✅ Teste de conectividade básica
- ✅ Teste de autenticação do token
- ✅ Teste do refresh token
- ✅ Teste do serviço completo
- ✅ Análise dos logs recentes

## Endpoints da API

### Autenticação
- `POST /auth/login` - Login do usuário
- `POST /auth/refresh` - Renovar token
- `GET /stores/{CNPJ_LOJA}/software-houses/{CNPJ_SH}/token` - Gerar tokens da loja

### Produtos
- `GET /products` - Listar produtos
- `GET /products/{id}` - Buscar produto
- `POST /products` - Criar produto
- `PUT /products/{id}` - Atualizar produto
- `DELETE /products/{id}` - Deletar produto

### Pedidos
- `GET /orders` - Listar pedidos
- `GET /orders/{id}` - Buscar pedido
- `PUT /orders/{id}/status` - Atualizar status

### Categorias
- `GET /categories` - Listar categorias
- `GET /categories/{id}` - Buscar categoria

### Canais de Venda
- `GET /sales-channels` - Listar canais

## Status dos Pedidos

- `pending` - Pendente
- `confirmed` - Confirmado
- `shipped` - Enviado
- `delivered` - Entregue
- `cancelled` - Cancelado

## Canais de Venda

- `1` - Amazon
- `7` - Mercado Livre
- `26` - Shopee

## Troubleshooting

### Problemas Comuns

1. **Credenciais de usuário inválidas**
   - Verifique se o email e senha estão corretos
   - Confirme se a conta tem acesso ao Plug4Market
   - Use o comando `php artisan plug4market:debug` para validar

2. **CNPJs inválidos ou não autorizados**
   - Verifique se os CNPJs estão corretos
   - Confirme se o usuário tem acesso à loja
   - Verifique se o software house está correto

3. **Token inválido ou expirado**
   - Gere novos tokens usando a interface web
   - Execute `php artisan plug4market:generate-tokens`
   - Use o comando `php artisan plug4market:test` para validar

4. **Erro de conectividade**
   - Verifique sua conexão com a internet
   - Confirme se a URL da API está correta
   - Teste com `php artisan plug4market:test --verbose`
   - Use `php artisan plug4market:debug` para análise completa

5. **Erro 401 (Unauthorized)**
   - Token expirado - será renovado automaticamente
   - Se persistir, gere novos tokens
   - Verifique se o token está no formato correto

6. **Erro 403 (Forbidden)**
   - Verifique se o Seller ID está correto
   - Confirme se o CNPJ está configurado corretamente
   - Verifique se o User ID está correto

### Comandos de Debug

```bash
# Debug básico
php artisan plug4market:test

# Debug detalhado
php artisan plug4market:test --verbose

# Debug completo com análise de logs
php artisan plug4market:debug
```

### Logs

Os logs detalhados estão disponíveis em:
- Interface web: `/plug4market/settings/logs`
- Arquivo de log: `storage/logs/laravel.log`
- Comando de debug: `php artisan plug4market:debug`

### Suporte

Para problemas persistentes:
1. Execute `php artisan plug4market:debug`
2. Verifique os logs em `/plug4market/settings/logs`
3. Execute `php artisan plug4market:test --verbose`
4. Consulte a documentação oficial do Plug4Market
5. Entre em contato com o suporte técnico 