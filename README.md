# Sistema ERP - Gestão Empresarial Completa

Sistema ERP desenvolvido em Laravel 10 com funcionalidades completas para gestão empresarial, incluindo módulos fiscais, e-commerce, PDV, delivery, financeiro e muito mais.

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Funcionalidades](#funcionalidades)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Integrações](#integrações)
- [API REST](#api-rest)
- [Comandos Artisan](#comandos-artisan)
- [Documentação Adicional](#documentação-adicional)
- [Suporte](#suporte)

## 🎯 Sobre o Projeto

Este é um sistema ERP completo desenvolvido em Laravel que oferece uma solução integrada para gestão empresarial. O sistema suporta múltiplas empresas, possui módulos fiscais completos, integrações com marketplaces, sistema de PDV, delivery, gestão financeira e muito mais.

### Características Principais

- ✅ **Multi-empresa**: Suporte completo para múltiplas empresas
- ✅ **Sistema Fiscal Completo**: NFe, NFCe, CT-e, MDF-e, NFS-e
- ✅ **E-commerce**: Integração com WooCommerce, Mercado Livre, Plug4Market, NuvemShop
- ✅ **PDV**: Sistema completo de ponto de venda
- ✅ **Delivery**: Gestão completa de entregas e pedidos
- ✅ **Financeiro**: Controle financeiro completo
- ✅ **Estoque**: Gestão de estoque avançada
- ✅ **API REST**: API completa para aplicativos móveis

## 🛠 Tecnologias Utilizadas

### Backend
- **PHP 8.1+**
- **Laravel 10.10+**
- **MySQL/MariaDB**
- **Laravel Sanctum** (Autenticação API)

### Bibliotecas e Pacotes Principais

#### Fiscal
- `nfephp-org/sped-nfe` - Emissão de NFe
- `nfephp-org/sped-cte` - Emissão de CT-e
- `nfephp-org/sped-mdfe` - Emissão de MDF-e
- `nfephp-org/sped-efd` - EFD (Escrituração Fiscal Digital)
- `nfephp-org/sped-da` - DACTE
- `nfephp-org/sped-ibpt` - Consulta IBPT
- `webmaniabr/nfse` - Emissão de NFS-e
- `tecnospeedsa/plugnotas` - Integração PlugNotas

#### E-commerce e Marketplaces
- `automattic/woocommerce` - Integração WooCommerce
- `mercadopago/dx-php` - Integração Mercado Pago
- `tiendanube/php-sdk` - Integração NuvemShop
- `cloud-dfe/sdk-php` - Cloud DFe

#### Relatórios e Documentos
- `dompdf/dompdf` - Geração de PDFs
- `tecnickcom/tcpdf` - Geração de PDFs avançada
- `maatwebsite/excel` - Importação/Exportação Excel
- `picqer/php-barcode-generator` - Geração de códigos de barras
- `simplesoftwareio/simple-qrcode` - Geração de QR Codes

#### Financeiro
- `eduardokum/laravel-boleto` - Geração de boletos bancários

#### Outros
- `guzzlehttp/guzzle` - Cliente HTTP
- `phpmailer/phpmailer` - Envio de e-mails
- `spatie/laravel-backup` - Sistema de backup
- `comtele/comtele_sdk` - Integração SMS

### Frontend
- **Vite** - Build tool
- **Axios** - Cliente HTTP
- **jQuery** - Biblioteca JavaScript
- **Bootstrap** (provavelmente) - Framework CSS

## 🚀 Funcionalidades

### 1. Gestão de Empresas
- Cadastro completo de empresas
- Multi-empresa com isolamento de dados
- Planos e contratos
- Configurações personalizadas por empresa
- Controle de acesso por perfil

### 2. Sistema Fiscal

#### NFe (Nota Fiscal Eletrônica)
- Emissão de NFe
- Cancelamento
- Carta de Correção
- Consulta de status
- Envio automático por e-mail
- Geração de XML
- Manifestação do destinatário

#### NFCe (Nota Fiscal de Consumidor Eletrônica)
- Emissão de NFCe
- Impressão de DANFCe
- QR Code para consulta
- Cancelamento

#### CT-e (Conhecimento de Transporte Eletrônico)
- Emissão de CT-e
- CT-e OS (Ordem de Serviço)
- Manifestação
- Cancelamento
- Geração de DACTE

#### MDF-e (Manifesto de Documentos Fiscais Eletrônicos)
- Emissão de MDF-e
- Encerramento
- Cancelamento

#### NFS-e (Nota Fiscal de Serviços Eletrônica)
- Emissão de NFS-e
- Integração com múltiplas prefeituras
- Cancelamento

#### SPED
- EFD ICMS/IPI
- EFD Contribuições
- Geração de arquivos SPED

### 3. E-commerce e Marketplaces

#### WooCommerce
- Sincronização de produtos
- Sincronização de pedidos
- Controle de estoque
- Gestão de categorias

#### Mercado Livre
- Integração completa
- Sincronização de produtos
- Gestão de pedidos
- Controle de anúncios

#### Plug4Market
- Integração com marketplace
- Sincronização de produtos
- Gestão de pedidos
- Múltiplos canais (Amazon, Mercado Livre, Shopee)
- Documentação completa disponível em `PLUG4MARKET_SETUP.md`

#### NuvemShop
- Integração com plataforma
- Sincronização de produtos e pedidos

### 4. PDV (Ponto de Venda)
- Venda no balcão
- Controle de caixa
- Abertura/fechamento de caixa
- Sangria e suprimento
- Impressão de cupom fiscal
- Vendas por mesa
- Comandas
- Controle de comissões

### 5. Delivery
- Gestão de pedidos delivery
- Controle de motoboys
- Áreas de entrega
- Cálculo de frete
- Status de pedidos
- Integração com aplicativo móvel

### 6. Gestão Financeira
- Contas a receber
- Contas a pagar
- Fluxo de caixa
- DRE (Demonstrativo de Resultado do Exercício)
- Plano de contas
- Contas bancárias
- Transferências
- Boletos bancários
- Remessa e retorno de boletos

### 7. Controle de Estoque
- Cadastro de produtos
- Controle de estoque
- Movimentações
- Alterações de estoque
- Lotes
- Grades de produtos
- Receitas e fórmulas
- Uso e consumo

### 8. Gestão de Clientes e Fornecedores
- Cadastro completo de clientes
- Grupos de clientes
- Histórico de compras
- Cadastro de fornecedores
- Controle de compras
- Orçamentos

### 9. Ordem de Serviço
- Cadastro de OS
- Controle de status
- Agendamentos
- Funcionários por OS
- Itens e serviços
- Faturamento

### 10. Locação
- Controle de locações
- Disponibilidade
- Itens locados
- Contratos

### 11. Funcionários
- Cadastro de funcionários
- Eventos e ocorrências
- Apuração de salário
- Controle de ponto (catraca)

### 12. Relatórios
- Relatórios gerenciais
- Relatórios fiscais
- Exportação para Excel/PDF
- Relatórios personalizados

### 13. API REST
- Autenticação via token
- Endpoints para aplicativo móvel
- Gestão de produtos
- Gestão de pedidos
- Carrinho de compras
- Endereços
- Cupons de desconto

### 14. Outros Módulos
- Blog/E-commerce
- Cashback
- Cupons de desconto
- Pesquisas e pesquisas
- Tickets de suporte
- Vídeos tutoriais
- Configurações de impressão
- Etiquetas

## 📦 Requisitos

### Servidor
- PHP >= 8.1
- Composer
- MySQL >= 5.7 ou MariaDB >= 10.3
- Extensões PHP:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  - GD ou Imagick
  - Zip
  - SOAP
  - cURL

### Desenvolvimento
- Node.js >= 16.x
- NPM ou Yarn
- Git

## 🔧 Instalação

### 1. Clone o repositório

```bash
git clone <repository-url>
cd <project-directory>
```

### 2. Instale as dependências PHP

```bash
composer install
```

### 3. Instale as dependências Node.js

```bash
npm install
```

### 4. Configure o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure o banco de dados

Edite o arquivo `.env` e configure as credenciais do banco de dados:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```

### 6. Execute as migrations

```bash
php artisan migrate
```

### 7. (Opcional) Execute os seeders

```bash
php artisan db:seed
```

### 8. Configure as permissões

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Compile os assets

```bash
npm run dev
# ou para produção
npm run build
```

### 10. Configure o servidor web

#### Apache
Configure o DocumentRoot para apontar para o diretório `public/`.

#### Nginx
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /caminho/para/projeto/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## ⚙️ Configuração

### Variáveis de Ambiente Importantes

#### Aplicação
```env
APP_NAME="Nome da Aplicação"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
```

#### Banco de Dados
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```

#### E-mail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.exemplo.com
MAIL_PORT=587
MAIL_USERNAME=usuario@exemplo.com
MAIL_PASSWORD=senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@exemplo.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Integrações

##### Plug4Market
```env
PLUG4MARKET_USER_LOGIN=seu-email@exemplo.com
PLUG4MARKET_USER_PASSWORD=sua-senha
PLUG4MARKET_BASE_URL=https://api.sandbox.plug4market.com.br
PLUG4MARKET_SANDBOX=true
PLUG4MARKET_STORE_CNPJ=04026307000112
PLUG4MARKET_SOFTWARE_HOUSE_CNPJ=04026307000112
```

##### Mercado Pago
```env
MP_ACCESS_TOKEN=seu_token
MP_PUBLIC_KEY=sua_chave_publica
```

##### WooCommerce
Configure via interface administrativa

### Configuração de Certificados Digitais

Para emissão de documentos fiscais, é necessário configurar o certificado digital A1 ou A3:

1. Certificado A1: Salve o arquivo `.pfx` em local seguro
2. Certificado A3: Configure o driver do token/smartcard

Configure no arquivo de configuração ou via interface administrativa.

## 📁 Estrutura do Projeto

```
/
├── app/
│   ├── Console/          # Comandos Artisan
│   ├── Exceptions/       # Tratamento de exceções
│   ├── Exports/          # Exportações (Excel, etc)
│   ├── Helpers/          # Funções auxiliares
│   ├── Http/
│   │   ├── Controllers/  # Controladores
│   │   │   ├── Api/      # Controllers da API REST
│   │   │   ├── AppFiscal/# Controllers fiscais
│   │   │   ├── Delivery/ # Controllers de delivery
│   │   │   ├── Ifood/    # Controllers iFood
│   │   │   ├── MP/       # Controllers Mercado Pago
│   │   │   └── Pdv/      # Controllers PDV
│   │   ├── Middleware/   # Middlewares
│   │   └── Requests/     # Form Requests
│   ├── Imports/          # Importações (Excel, etc)
│   ├── Models/           # Modelos Eloquent
│   ├── Prints/           # Classes de impressão
│   ├── Providers/        # Service Providers
│   ├── Restaurant/       # Módulo restaurante
│   ├── Rules/            # Regras de validação customizadas
│   ├── Services/         # Serviços de negócio
│   │   └── Nfse/         # Serviços NFS-e
│   └── Utils/            # Utilitários
├── bootstrap/            # Arquivos de inicialização
├── config/               # Arquivos de configuração
├── database/
│   ├── factories/       # Factories para testes
│   ├── migrations/      # Migrations do banco
│   └── seeders/         # Seeders do banco
├── public/              # Arquivos públicos
│   ├── css/            # Estilos CSS
│   ├── js/             # Scripts JavaScript
│   └── images/        # Imagens
├── resources/
│   ├── views/         # Views Blade
│   └── lang/          # Arquivos de idioma
├── routes/
│   ├── api.php        # Rotas da API
│   ├── web.php        # Rotas web
│   └── console.php    # Rotas de console
├── storage/           # Arquivos de armazenamento
│   ├── app/          # Arquivos da aplicação
│   ├── framework/    # Arquivos do framework
│   └── logs/         # Logs
└── tests/            # Testes automatizados
```

## 🔌 Integrações

### Marketplaces e E-commerce
- **WooCommerce**: Integração completa
- **Mercado Livre**: API completa
- **Plug4Market**: Marketplace multi-canal
- **NuvemShop**: Plataforma e-commerce
- **iFood**: Integração com delivery

### Pagamentos
- **Mercado Pago**: Gateway de pagamento
- **PagSeguro**: Gateway de pagamento
- Boletos bancários

### Fiscais
- **Sefaz**: Emissão de documentos fiscais
- **IBPT**: Consulta de alíquotas
- **Sintegra**: Consulta de inscrições estaduais
- **Cloud DFe**: Serviço de DFe

### Comunicação
- **SMS**: Via Comtele
- **WhatsApp**: Integração WhatsApp
- **E-mail**: PHPMailer

### Outros
- **Correios**: Consulta de CEP e frete

## 📱 API REST

A aplicação possui uma API REST completa para integração com aplicativos móveis e sistemas externos.

### Autenticação

```http
POST /api/appUser/login
Content-Type: application/json

{
    "email": "usuario@exemplo.com",
    "password": "senha"
}
```

Resposta:
```json
{
    "token": "token_de_acesso",
    "user": {
        "id": 1,
        "name": "Nome do Usuário",
        "email": "usuario@exemplo.com"
    }
}
```

### Endpoints Principais

#### Produtos
- `GET /api/appProduto/categorias/{usuario_id}` - Listar categorias
- `GET /api/appProduto/destaques/{usuario_id}` - Produtos em destaque
- `GET /api/appProduto/pesquisaProduto` - Pesquisar produtos

#### Carrinho
- `GET /api/appCarrinho/index` - Obter carrinho
- `POST /api/appCarrinho/finalizar` - Finalizar pedido
- `GET /api/appCarrinho/historico` - Histórico de pedidos

#### Clientes
- `GET /api/appUser/enderecos` - Listar endereços
- `POST /api/appUser/novoEndereco` - Adicionar endereço

### Middleware de Autenticação

Use o header `Authorization: Bearer {token}` nas requisições protegidas.

## 🎨 Comandos Artisan

### Comandos Personalizados

#### Plug4Market
```bash
# Gerar tokens automaticamente
php artisan plug4market:generate-tokens

# Testar conexão
php artisan plug4market:test

# Teste detalhado
php artisan plug4market:test --verbose

# Debug completo
php artisan plug4market:debug
```

### Comandos Laravel Padrão

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Otimizar aplicação
php artisan optimize
php artisan config:cache
php artisan route:cache

# Executar migrations
php artisan migrate
php artisan migrate:fresh --seed

# Criar backup
php artisan backup:run
```

## 📚 Documentação Adicional

- [PLUG4MARKET_SETUP.md](./PLUG4MARKET_SETUP.md) - Configuração completa do Plug4Market
- [TROUBLESHOOTING_PLUG4MARKET.md](./TROUBLESHOOTING_PLUG4MARKET.md) - Solução de problemas Plug4Market
- [CORREÇÕES_PLUG4MARKET.md](./CORREÇÕES_PLUG4MARKET.md) - Correções aplicadas
- [LOGS_PLUG4MARKET.md](./LOGS_PLUG4MARKET.md) - Sistema de logs

## 🔒 Segurança

- Autenticação via Laravel Sanctum
- Middleware de verificação de empresa
- Controle de acesso por perfil
- Validação de dados em todas as entradas
- Proteção CSRF
- Sanitização de inputs
- Logs de erros e auditoria

## 🧪 Testes

```bash
# Executar testes
php artisan test

# Com cobertura
php artisan test --coverage
```

## 📝 Licença

Este projeto é proprietário. Todos os direitos reservados.

## 👥 Suporte

Para suporte técnico:
1. Consulte a documentação adicional
2. Verifique os logs em `storage/logs/laravel.log`
3. Execute comandos de debug quando disponíveis
4. Entre em contato com a equipe de desenvolvimento

## 🚀 Deploy

### Produção

1. Configure `APP_ENV=production` e `APP_DEBUG=false`
2. Execute `php artisan config:cache`
3. Execute `php artisan route:cache`
4. Execute `php artisan view:cache`
5. Configure permissões corretas
6. Configure SSL/HTTPS
7. Configure backup automático

### Docker

O projeto possui configuração Docker disponível em `.docker/`.

## 📊 Status do Projeto

✅ **Em desenvolvimento ativo**

Módulos principais implementados e funcionais. Novas funcionalidades sendo adicionadas continuamente.

---

**Desenvolvido com ❤️ usando Laravel**
