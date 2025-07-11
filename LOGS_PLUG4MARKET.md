# Logs do Plug4Market - Documentação

## Visão Geral

Foram implementados logs detalhados para todas as operações relacionadas ao Plug4Market, incluindo produtos e categorias. Os logs são registrados no arquivo `storage/logs/laravel.log` e podem ser visualizados através do comando `plug4market:logs`.

## Logs Implementados

### 1. Controllers

#### Plug4MarketProductController
- **Acesso às páginas**: Log quando usuário acessa listagem, criação, edição e detalhes
- **Criação de produtos**: Log detalhado do processo de criação local e sincronização com API
- **Atualização de produtos**: Log das mudanças e sincronização
- **Exclusão de produtos**: Log do processo de exclusão local e na API
- **Sincronização individual**: Log de cada produto sincronizado
- **Sincronização em massa**: Log do processo completo com estatísticas

#### Plug4MarketCategoryController
- **Acesso às páginas**: Log quando usuário acessa listagem, criação, edição e detalhes
- **Criação de categorias**: Log detalhado do processo de criação local e sincronização
- **Atualização de categorias**: Log das mudanças na hierarquia e sincronização
- **Exclusão de categorias**: Log das validações e processo de exclusão
- **Sincronização individual**: Log de cada categoria sincronizada
- **Sincronização em massa**: Log do processo completo com estatísticas

### 2. Service (Plug4MarketService)

#### Autenticação
- **Login**: Log do processo de autenticação e resposta da API
- **Renovação de token**: Log da renovação automática de tokens
- **Validação de token**: Log da verificação de validade

#### Requisições HTTP
- **Início da requisição**: Log dos parâmetros e configuração
- **Resposta recebida**: Log do status code e tamanho da resposta
- **Erros HTTP**: Log detalhado de erros 4xx, 5xx e de conexão
- **Erros genéricos**: Log de exceções não tratadas

#### Produtos
- **Listagem**: Log da requisição e quantidade de produtos retornados
- **Busca individual**: Log da busca por ID específico
- **Criação**: Log dos dados enviados e resposta da API
- **Atualização**: Log das mudanças e resposta
- **Exclusão**: Log do processo de exclusão

#### Categorias
- **Listagem**: Log da requisição e quantidade de categorias retornadas
- **Busca individual**: Log da busca por ID específico
- **Criação**: Log dos dados enviados e resposta da API
- **Atualização**: Log das mudanças e resposta
- **Exclusão**: Log do processo de exclusão

### 3. Comandos Artisan

#### TestPlug4MarketCategories
- **Início do teste**: Log quando o comando é executado
- **Cada teste individual**: Log do resultado de cada teste (listar, criar, buscar, atualizar, deletar)
- **Erros de teste**: Log detalhado de falhas em cada etapa
- **Conclusão**: Log do resultado geral do teste

#### ViewPlug4MarketLogs
- **Filtragem**: Filtra apenas logs relacionados ao Plug4Market
- **Busca**: Permite buscar por termos específicos
- **Estatísticas**: Mostra contagem de erros, avisos e informações
- **Colorização**: Diferencia tipos de log por cores

## Estrutura dos Logs

### Níveis de Log
- **INFO**: Operações normais e bem-sucedidas
- **WARNING**: Situações que merecem atenção mas não são erros
- **ERROR**: Erros que impedem o funcionamento normal

### Informações Registradas
- **Timestamps**: Data e hora de cada operação
- **IDs**: IDs dos produtos/categorias envolvidos
- **Dados**: Informações relevantes (nomes, valores, status)
- **Respostas da API**: Status codes e dados retornados
- **Erros**: Mensagens de erro e stack traces
- **Estatísticas**: Contadores e métricas de performance

## Comandos Disponíveis

### Visualizar Logs
```bash
# Ver últimos 50 logs do Plug4Market
php artisan plug4market:logs

# Ver últimas 100 linhas
php artisan plug4market:logs --lines=100

# Ver apenas as últimas linhas (modo tail)
php artisan plug4market:logs --tail

# Buscar por termo específico
php artisan plug4market:logs --search="erro"

# Combinar opções
php artisan plug4market:logs --lines=200 --tail --search="produto"
```

### Testar Funcionalidades
```bash
# Testar categorias
php artisan plug4market:test-categories
```

## Exemplos de Logs

### Criação de Produto
```
[2024-01-15 10:30:15] local.INFO: Iniciando criação de produto Plug4Market {"dados_recebidos":{"codigo":"PROD001","descricao":"Produto Teste","valor_unitario":100}}
[2024-01-15 10:30:16] local.INFO: Produto Plug4Market criado localmente {"produto_id":123,"codigo":"PROD001","descricao":"Produto Teste","valor_unitario":100}
[2024-01-15 10:30:17] local.INFO: Iniciando sincronização do produto com API Plug4Market {"produto_id":123,"codigo":"PROD001"}
[2024-01-15 10:30:18] local.INFO: Produto Plug4Market sincronizado com sucesso {"produto_id":123,"external_id":456,"codigo":"PROD001"}
```

### Erro de API
```
[2024-01-15 10:35:20] local.ERROR: Erro ao criar produto na API Plug4Market {"produto_id":124,"codigo":"PROD002","error":"Connection timeout","status_code":500}
```

### Sincronização em Massa
```
[2024-01-15 11:00:00] local.INFO: Iniciando sincronização em massa de produtos Plug4Market
[2024-01-15 11:00:01] local.INFO: Produtos encontrados para sincronização {"total_produtos":50,"produtos_com_external_id":30,"produtos_sem_external_id":20}
[2024-01-15 11:05:00] local.INFO: Sincronização em massa de produtos Plug4Market concluída {"total_processados":50,"sucessos":45,"erros":5}
```

## Benefícios

1. **Debugging**: Facilita a identificação de problemas
2. **Monitoramento**: Acompanha o status das operações
3. **Auditoria**: Registra todas as ações realizadas
4. **Performance**: Identifica gargalos e problemas de performance
5. **Suporte**: Fornece informações para suporte técnico

## Configuração

Os logs são configurados automaticamente pelo Laravel. Para ajustar o nível de log, edite o arquivo `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

## Manutenção

- Os logs são rotacionados automaticamente pelo Laravel
- Arquivos antigos são compactados e mantidos por 30 dias por padrão
- Para limpar logs antigos: `php artisan log:clear`
- Para visualizar logs em tempo real: `tail -f storage/logs/laravel.log` 