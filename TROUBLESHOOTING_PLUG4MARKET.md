# Troubleshooting Plug4Market - Guia de Solução de Problemas

## Problema: "Erro desconhecido ao criar produto na API"

### 1. Verificar Logs Detalhados

Primeiro, vamos verificar os logs para entender exatamente o que está acontecendo:

```bash
# Ver logs relacionados à criação de produtos
php artisan plug4market:logs --search="criar produto"

# Ver logs de erro recentes
php artisan plug4market:logs --search="ERROR" --lines=100

# Ver logs de requisições HTTP
php artisan plug4market:logs --search="HTTP" --lines=50
```

### 2. Testar Conexão e Autenticação

```bash
# Testar conexão básica
php artisan plug4market:test-products --list

# Testar criação de produto
php artisan plug4market:test-products --create --verbose
```

### 3. Verificar Configurações

Acesse `/plug4market/settings` e verifique:

- ✅ **Base URL**: Deve estar correto (sandbox ou produção)
- ✅ **Access Token**: Deve estar válido e não expirado
- ✅ **Refresh Token**: Deve estar configurado
- ✅ **Seller ID**: Deve estar configurado corretamente

### 4. Possíveis Causas e Soluções

#### A. Token Expirado ou Inválido
**Sintomas**: Erro 401 (Unauthorized)
**Solução**: 
1. Renovar o token em `/plug4market/settings`
2. Verificar se o token está sendo renovado automaticamente

#### B. Dados Inválidos
**Sintomas**: Erro 400 (Bad Request)
**Solução**:
1. Verificar se todos os campos obrigatórios estão preenchidos
2. Verificar se os tipos de dados estão corretos
3. Verificar se a categoria_id existe na API

#### C. Problema de Conectividade
**Sintomas**: Timeout ou erro de conexão
**Solução**:
1. Verificar conectividade com a internet
2. Verificar se a URL da API está acessível
3. Verificar firewall/proxy

#### D. Rate Limiting
**Sintomas**: Erro 429 (Too Many Requests)
**Solução**:
1. Aguardar alguns minutos antes de tentar novamente
2. Implementar retry com backoff exponencial

### 5. Comandos de Diagnóstico

#### Verificar Status da API
```bash
# Testar conectividade básica
php artisan plug4market:test-products --list

# Testar autenticação
php artisan plug4market:test-products --create --verbose
```

#### Verificar Logs em Tempo Real
```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log | grep -i plug4market

# Ver apenas erros
tail -f storage/logs/laravel.log | grep -i "error.*plug4market"
```

### 6. Estrutura de Dados Esperada

A API espera os seguintes campos **OBRIGATÓRIOS** conforme documentação oficial:

```json
{
  "productId": "string",
  "productName": "string", 
  "sku": "string",
  "name": "string",
  "description": "string",
  "width": "number",
  "height": "number", 
  "length": "number",
  "weight": "number",
  "stock": "number",
  "price": "number",
  "salesChannels": [
    {
      "id": "number",
      "price": "number"
    }
  ]
}
```

**Campos Opcionais Importantes:**
```json
{
  "categoryId": "string",
  "brand": "string",
  "origin": "nacional|importado",
  "ean": "string",
  "model": "string",
  "warranty": "number",
  "ncm": "string",
  "costPrice": "number",
  "images": ["string"],
  "active": "boolean"
}
```

**Observações Importantes:**
- ✅ `width`, `height`, `length`, `weight` devem ser números (float)
- ✅ `stock` deve ser inteiro
- ✅ `price` deve ser número (float)
- ✅ `salesChannels` é obrigatório e deve conter pelo menos um canal
- ✅ IDs de canais válidos: 1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 31, 66, 70

### 7. Validações Implementadas

O sistema valida automaticamente:

- ✅ Código único do produto
- ✅ Descrição obrigatória
- ✅ Valor unitário positivo
- ✅ Categoria existente
- ✅ Dimensões e peso válidos

### 8. Fluxo de Criação de Produto

1. **Validação Local**: Dados são validados no controller
2. **Criação Local**: Produto é salvo no banco local
3. **Formatação**: Dados são formatados para a API
4. **Requisição HTTP**: POST para `/products`
5. **Resposta**: Verificação se retornou ID
6. **Atualização**: External ID é salvo se sucesso

### 9. Logs Importantes a Verificar

#### Logs de Sucesso
```
[INFO] Iniciando criação de produto Plug4Market
[INFO] Produto Plug4Market criado localmente
[INFO] Iniciando sincronização do produto com API Plug4Market
[INFO] Produto Plug4Market sincronizado com sucesso
```

#### Logs de Erro
```
[ERROR] Erro ao criar produto na API Plug4Market
[ERROR] Requisição HTTP Plug4Market falhou
[WARNING] API não retornou ID para produto criado
```

### 10. Soluções Rápidas

#### Solução 1: Renovar Token
```bash
# Acessar configurações
# /plug4market/settings
# Clicar em "Renovar Token"
```

#### Solução 2: Verificar Categorias
```bash
# Testar categorias primeiro
php artisan plug4market:test-categories
```

#### Solução 3: Limpar Cache
```bash
php artisan cache:clear
php artisan config:clear
```

#### Solução 4: Verificar Configurações do .env
```env
PLUG4MARKET_BASE_URL=https://api.sandbox.plug4market.com.br
PLUG4MARKET_ACCESS_TOKEN=seu_token_aqui
PLUG4MARKET_REFRESH_TOKEN=seu_refresh_token_aqui
PLUG4MARKET_SELLER_ID=7
```

### 11. Contato com Suporte

Se o problema persistir:

1. **Coletar Logs**: `php artisan plug4market:logs --lines=200 > logs.txt`
2. **Testar Conexão**: `php artisan plug4market:test-products --create --verbose`
3. **Verificar Configurações**: Screenshot das configurações
4. **Descrever Passos**: Como reproduzir o problema

### 12. Monitoramento Contínuo

Para evitar problemas futuros:

- ✅ Verificar logs regularmente
- ✅ Monitorar expiração de tokens
- ✅ Testar conexão periodicamente
- ✅ Manter configurações atualizadas

---

**Última atualização**: Janeiro 2024
**Versão**: 1.0 