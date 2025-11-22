# Plug4Market ERP & Integrações (Laravel 10)

Plataforma completa de gestão empresarial construída sobre Laravel 10, com módulos de faturamento, financeiro, delivery, e-commerce/PDV e integrações profundas com marketplaces — em especial o ecossistema Plug4Market.

---

## 📌 Visão Geral
- ERP fiscal/contábil (NFe, NFC-e, NFSe, SPED, boletos, contas a pagar/receber, estoque, RH).
- Portais omnichannel: Delivery, Cardápio, E-commerce, PDV e aplicativos móveis (`routes/api.php`).
- Integrações nativas com **Plug4Market**, Mercado Livre, Ifood, WooCommerce, Tecnospeed, PlugNotas, PagSeguro, MercadoPago e WooCommerce.
- Automação de rotinas por comandos Artisan, agendamentos (cron) e relatórios detalhados.
- Stack moderna com Vite, Blade, Vue (rotas em `resources/js/router`), Axios e pacotes de terceiros para fiscal (nfephp, NFSe Webmania, Sped, etc.).

---

## 🧱 Stack & Dependências
- **PHP 8.1+ / Laravel 10.10** (`composer.json`).
- **Banco**: MySQL/MariaDB (default `.env.example`), Redis opcional para cache/queues.
- **Node 18+ / NPM** para build com **Vite** (`package.json`).
- Pacotes chave: `nfephp-org/*`, `webmaniabr/nfse`, `mercadopago/dx-php`, `spatie/laravel-backup`, `maatwebsite/excel`, `laravel/sanctum`, `dompdf/dompdf`, `simplesoftwareio/simple-qrcode`, `cloud-dfe/sdk-php`, `woocommerce`, entre outros.
- **Docker/Sail** opcional (`laravel/sail` dev dependency + configs em `.docker/`).

---

## 📂 Estrutura de Pastas Destacada
- `app/Http/Controllers/**`: mais de 500 controllers cobrindo módulos fiscais, delivery, integrações e Plug4Market (`Plug4Market*Controller`).
- `app/Services/Plug4MarketService.php`: núcleo da integração (autenticação em duas etapas, refresh automático, sync de produtos/pedidos/etiquetas, processamento de XML).
- `app/Console/Commands/**`: jobs personalizados (`plug4market:*`, `woocommerce:sync`, `cash-back:cron`, etc.).
- `resources/views/plug4market/**`: interfaces Blade para produtos, pedidos, categorias, etiquetas e configurações.
- `database/migrations/*plug4market*.php`: tabelas de produtos, pedidos, categorias, logs e settings.
- Documentação dedicada: `PLUG4MARKET_SETUP.md`, `LOGS_PLUG4MARKET.md`, `CORREÇÕES_PLUG4MARKET.md`, `TROUBLESHOOTING_PLUG4MARKET.md`.

---

## ✅ Pré-requisitos
- PHP 8.1+ com extensões: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, ZIP, GD (para QRCode/etiquetas) e Soap (integrações fiscais).
- Composer 2.x.
- Node.js 18+ e NPM 9+ (ou PNPM/Yarn se preferir).
- MySQL 8 / MariaDB 10.6+.
- Redis (opcional) para cache/filas.
- Certificados digitais A1 (.pfx) para módulos fiscais (armazenados em `storage/app/**`).
- CLI `wkhtmltopdf` (opcional) para PDFs mais complexos.

---

## 🚀 Setup Rápido (Ambiente Local)
1. **Clonar & instalar dependências**
   ```bash
   git clone <repo> && cd workspace
   composer install
   npm install
   ```
2. **Configurar env & chave**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Configurar banco**
   - Atualize `DB_*` no `.env`.
   - Rode migrations + seeds iniciais:
     ```bash
     php artisan migrate --seed
     ```
   - Opcional: restaure dados legados via `bd.sql`:
     ```bash
     mysql -u user -p database < bd.sql
     ```
4. **Links & assets**
   ```bash
   php artisan storage:link
   npm run dev    # desenvolvimento
   npm run build  # produção
   ```
5. **Servidores**
   ```bash
   php artisan serve
   # ou via Sail
   ./vendor/bin/sail up
   ```
6. **Cron/filas** (produção):
   - Agende `* * * * * php /path/artisan schedule:run`.
   - Execute consumidores `php artisan queue:work` (alterar `QUEUE_CONNECTION` conforme necessidade).

---

## 🗄️ Banco & Seeds
- Seeds padrão (`database/seeders`) criam usuários, categorias e cidades essenciais.
- Módulos Plug4Market criam tabelas dedicadas: `plug4market_products`, `plug4market_orders`, `plug4market_categories`, `plug4market_logs`, `plug4market_settings`, etc.
- Use `php artisan migrate --path=database/migrations/<arquivo>` para execuções seletivas.

---

## 🎨 Front-end & Assets
- Build com **Vite** e módulos ES (`package.json`).
- `resources/js/router/index.js` define rotas Vue assíncronas (ex.: módulo Tecnospeed).
- `public/metronic` e `public/js/**` armazenam assets legacy; evite remover sem checar dependências Blade.

---

## 🔐 Variáveis de Ambiente
### Essenciais
- `APP_*`, `LOG_*`, `DB_*`, `CACHE_DRIVER`, `QUEUE_CONNECTION`, `FILESYSTEM_DISK`, `SESSION_DRIVER`.
- `MAIL_*` para notificações, `AWS_*` se usar S3, `PUSHER_*` para broadcasting.

### Plug4Market (`config/services.php`)
| Variável | Descrição | Exemplo |
| --- | --- | --- |
| `PLUG4MARKET_USER_LOGIN` / `PLUG4MARKET_USER_PASSWORD` | Credenciais do usuário Plug4Market (etapa 1 do login) | `login@empresa.com` |
| `PLUG4MARKET_ACCESS_TOKEN` / `PLUG4MARKET_REFRESH_TOKEN` | Tokens da loja (gerados via API) | `eyJhbGci...` |
| `PLUG4MARKET_BASE_URL` | Endpoint base (`sandbox` ou produção) | `https://api.sandbox.plug4market.com.br` |
| `PLUG4MARKET_SANDBOX` | Boolean para ambiente | `true` |
| `PLUG4MARKET_SELLER_ID` | Seller ID informado pela Plug4Market | `7` |
| `PLUG4MARKET_SOFTWARE_HOUSE_CNPJ` | CNPJ da software house | `04026307000112` |
| `PLUG4MARKET_STORE_CNPJ` | CNPJ da loja operada | `04026307000112` |
| `PLUG4MARKET_USER_ID` | ID do usuário retornado no login | `89579395-...` |
| `PLUG4MARKET_API_VERSION` | Versão da API | `v1` |
| `PLUG4MARKET_TIMEOUT` / `PLUG4MARKET_RETRY_ATTEMPTS` | Configs de HTTP client | `30` / `3` |

> Consulte `PLUG4MARKET_ENV_EXAMPLE.txt` para uma lista completa pronta para copiar.

---

## 🧩 Integração Plug4Market
### Recursos Principais
- **Serviço central**: `Plug4MarketService` cuida de login do usuário, geração/refresh de tokens, validação JWT, consumo de endpoints (`/products`, `/orders`, `/labels`, `/sales-channels`, `/categories`, `/stores/.../token`, etc.), logs estruturados e manipulação de XML de notas.
- **Modelos**: `Plug4MarketProduct`, `Plug4MarketOrder`, `Plug4MarketOrderItem`, `Plug4MarketCategory`, `Plug4MarketSetting`, `Plug4MarketLog`, `Plug4MarketToken`.
- **UI Blade** (`resources/views/plug4market`): CRUD completo de produtos, pedidos (incl. importação de NF), categorias, etiquetas e painel de configurações/testes/logs.
- **Controllers**: `Plug4MarketProductController`, `Plug4MarketOrderController`, `Plug4MarketCategoryController`, `Plug4MarketLabelController`, `Plug4MarketSettingController`.

### Rotas Web Principais
| Rota | Ação |
| --- | --- |
| `GET /plug4market/products` | Listar/sincronizar produtos (sync individual ou em massa). |
| `GET /plug4market/orders` | Painel de pedidos e operações de nota (import, download XML, upload, checagem). |
| `GET /plug4market/categories` | CRUD e sincronização de categorias. |
| `GET /plug4market/labels` | Gestão de etiquetas de envio. |
| `GET /plug4market/settings` | Configurações, geração de tokens, testes de conectividade e visualização de logs. |

### Fluxo de Autenticação (detalhado em `PLUG4MARKET_SETUP.md`)
1. **Login usuário**: `POST /auth/login` com `PLUG4MARKET_USER_LOGIN/PASSWORD`, retornando `accessToken`.
2. **Gerar tokens da loja**: `GET /stores/{CNPJ_LOJA}/software-houses/{CNPJ_SH}/token?notEncoded=true` usando `Bearer {accessToken}` do passo anterior.
3. **Persistir tokens** em `plug4market_settings` (UI, CLI ou `.env`).
4. **Refresh automático**: `Plug4MarketService::refreshAccessToken()` renova ao detectar 401.

### Comandos Artisan
| Comando | Descrição |
| --- | --- |
| `php artisan plug4market:generate-tokens` | Executa o fluxo completo (login → tokens → teste) de forma interativa ou via opções `--login/--password/--store-cnpj/--software-house-cnpj`. |
| `php artisan plug4market:test` / `--verbose` | Valida configurações, tokens, conectividade e leitura de produtos. |
| `php artisan plug4market:debug` | Diagnóstico completo (credenciais, tokens, endpoints, logs recentes). |
| `php artisan plug4market:logs [--lines=100 --tail --search=erro]` | Visualização rápida dos logs estruturados (`LOGS_PLUG4MARKET.md`). |
| `php artisan plug4market:process-invoice-xml [--order-id= --limit=50 --force --verbose]` | Baixa/processa XMLs de NF vinculadas a pedidos, atualizando metadados. |
| `php artisan plug4market:test-products` / `test-orders` / `test-categories` | Suites de teste específicas de cada recurso (criar/listar/atualizar/deletar). |

### Logs & Troubleshooting
- `LOGS_PLUG4MARKET.md`: detalha cobertura de logs (controllers, serviços, comandos) e opções do `plug4market:logs`.
- `TROUBLESHOOTING_PLUG4MARKET.md`: guia passo a passo para erros comuns (401, 400, timeout, rate limit).
- `CORREÇÕES_PLUG4MARKET.md`: histórico das correções implementadas (fluxo oficial de autenticação, endpoints válidos, novas migrations, etc.).

---

## 🌐 Integrações & Módulos Adicionais
- **WooCommerce**: Configurações (`/woocommerce/config`), sync de produtos/pedidos (`WooCommerce*Controller`), cron `woocommerce:sync`.
- **Mercado Livre**: Controllers e rotas para produtos, perguntas, pedidos e chat (`mercado-livre-*`).
- **Ifood**: Controllers em `app/Http/Controllers/Ifood`, views dedicadas e rotas `/ifood/*`.
- **Delivery/E-commerce/PDV**: APIs JSON autenticadas (`authDelivery`, `authEcommerce`, `authPdv`) com cadastros de clientes, pedidos, pagamento e PIX.
- **Pagamentos**: Integração com PagSeguro, MercadoPago (checkout transparente, Pix, boletos) e cartões via controllers específicos.
- **Fiscal**: NF-e, NFC-e, NFSe, SPED, MDF-e, boletos, IBPT, difal, contingência; pacotes `nfephp`, `webmaniabr/nfse`, `eduardokum/laravel-boleto` já configurados.
- **Backups**: `spatie/laravel-backup` para snapshots (`php artisan backup:run`).

---

## 📡 APIs Móveis / Delivery / Cardápio / PDV
- `routes/api.php` agrupa endpoints para apps mobiles (`appUser`, `appProduto`, `appCarrinho`, `appFiscal`, `pdv`, `delivery`, `cardapio`, `ecommerce`).
- Autenticações via middlewares personalizados (`token`, `authApp`, `authDelivery`, `authEcommerce`, `authPdv`).
- Suporte a recursos como carrinho, pedidos, emissão fiscal móvel, inventário, cobranças e notificações push.

---

## 🕒 Agendamentos & Rotinas
`app/Console/Kernel.php` agenda:
- `empresas_logada:cron` (monitoramento de empresas ativas) – a cada minuto.
|- `dfe:cron` – a cada 2h.
- `cash-back:cron` – diário às 08h.
- `woocommerce:sync` – a cada minuto para configs com auto sync.

> Certifique-se de configurar o cron do sistema operacional para disparar `php artisan schedule:run` e, quando necessário, rode workers/daemons para filas customizadas.

---

## ⚙️ Manutenção & Utilidades
- Limpeza rápida: `php artisan cache:clear && php artisan config:clear && php artisan view:clear`.
- Link de storage: `php artisan storage:link` (necessário para exibir certificados, anexos, XML de NF).
- Backups: `php artisan backup:run`.
- Logs em tempo real: `tail -f storage/logs/laravel.log | grep -i plug4market`.
- Scripts AJAX/legacy ficam em `public/js/**`; revisar dependências antes de minificar.

---

## 🧪 Testes & Qualidade
- **PHPUnit/Laravel Test**: `php artisan test` ou `vendor/bin/phpunit`.
- **Cobertura**: testes de exemplo em `tests/Feature` e `tests/Unit` (adapte conforme novos módulos).
- **Code style**: `./vendor/bin/pint` (já instalado como `laravel/pint`).
- **CI/CD**: configure pipelines para rodar `composer install --no-dev --optimize-autoloader`, `php artisan config:cache`, `php artisan route:cache`, `npm run build`.

---

## 🗂️ Documentação Complementar
- `PLUG4MARKET_SETUP.md`: passo a passo completo do fluxo de autenticação e configuração.
- `PLUG4MARKET_ENV_EXAMPLE.txt`: template pronto de variáveis.
- `LOGS_PLUG4MARKET.md`: guia de observabilidade.
- `TROUBLESHOOTING_PLUG4MARKET.md`: resolução de problemas comuns.
- `CORREÇÕES_PLUG4MARKET.md`: changelog do módulo.
- `TROUBLESHOOTING_PLUG4MARKET.md`: diagnóstico operacional.

---

## 📎 Observações Operacionais
- O diretório `public/` contém ~20k arquivos (XMLs, imagens, tema Metronic). Evite deletar em produção e considere mover arquivos estáticos para CDN se necessário.
- Certificados e arquivos sensíveis devem ficar fora de versionamento (`storage/` + `.gitignore`).
- Sempre rode `php artisan plug4market:test --verbose` após alterar credenciais/tokens para validar a integração antes de acionar clientes.
- Para ambientes multi-tenant, validate `Plug4MarketSetting::getSettings()` antes de iniciar sincronizações massivas.

---

## 🤝 Contribuição
- Crie branches por funcionalidade (`feature/plug4market-logs`, `fix/orders-xml`).
- Respeite o padrão PSR-12 e rode `pint`/testes antes de abrir PR.
- Documente comandos/artifacts novos no README e/ou nos arquivos específicos em `/docs` ou raiz.

---

## ✅ Próximos Passos Recomendados
1. Configure o `.env` (principalmente blocos Plug4Market).
2. Rode `php artisan migrate --seed` e importe `bd.sql` se precisar de fixtures legados.
3. Acesse `/plug4market/settings` → gere tokens automaticamente → valide com `php artisan plug4market:test --verbose`.
4. Inicie o worker de cron/queue e acompanhe os logs (`php artisan plug4market:logs --tail`).
5. Documente configurações específicas do cliente em `LOGS_PLUG4MARKET.md` ou notas internas para manter o histórico.

Boas integrações! 🚀
