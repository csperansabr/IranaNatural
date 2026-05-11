# Iraná Natural — Documentação Técnica do Projeto

> **Última atualização:** 2026-05-11
> **Versão do sistema:** 6.6
> **Status:** Desenvolvimento ativo — pré-deploy de produção

---

## 1. Visão Geral

**Iraná Natural** é um e-commerce artesanal PHP com painel administrativo integrado, voltado para venda de produtos naturais (incensos, chás, banhos de erva, escalda pés). Hospedagem em HostGator (cPanel), ambiente de desenvolvimento em Laragon/Windows.

---

## 2. Objetivo do Sistema

Permitir que clientes realizem compras online com pagamento via InfinitePay (PIX, cartão), gestão completa de pedidos e estoque pelo painel admin, cálculo de frete via API Melhor Envio, e notificações automáticas por e-mail ao longo do ciclo do pedido.

---

## 3. Arquitetura Atual

```
MVC artesanal sem framework
├── index.php          → Front controller + roteador
├── app/
│   ├── Controllers/   → Controladores do site público
│   ├── Models/        → Modelos com PDO direto
│   ├── Views/         → Templates PHP puros
│   ├── Core/          → Router, Session, Database, Mailer, WebhookGuard
│   └── Services/      → FreteService, MelhorEnvioService
├── admin/             → Painel administrativo (namespace Admin)
├── config/            → Configs carregadas por index.php
├── assets/            → CSS, JS, imagens estáticas
├── sql/               → Migrations versionadas (migration_vX_Y.sql)
├── setup/             → Instaladores CLI (bloqueados por .htaccess)
├── tests/             → Scripts de teste CLI (webhook simulator)
└── tools/             → Utilitários de desenvolvimento CLI
```

---

## 4. Tecnologias Utilizadas

| Componente | Tecnologia |
|---|---|
| Linguagem | PHP 8.x |
| Banco de dados | MySQL 8.x (PDO, utf8mb4) |
| Servidor web | Apache + mod_rewrite |
| Ambiente dev | Laragon (Windows 11) |
| Hospedagem prod | HostGator (cPanel, Linux, Percona 8.0.45) |
| Pagamentos | InfinitePay Checkout (API REST) |
| Frete API | Melhor Envio (JWT, sandbox + produção) |
| E-mail | PHP `mail()` (SMTP nativo do cPanel) |
| Frontend | HTML5 + CSS3 + JS vanilla (sem build step) |
| Tipografia | Google Fonts: Cormorant Garamond + Lato |

---

## 5. Estrutura de Pastas

```
IranaNatural/
├── .env                          # Credenciais locais (NUNCA commitado)
├── .env.example                  # Template de variáveis de ambiente
├── .gitignore
├── .htaccess                     # Roteamento + bloqueio de acesso a arquivos
├── index.php                     # Front controller / roteador
├── robots.txt
├── sitemap.xml
├── projeto.md                    # Este arquivo
├── admin/
│   ├── .htaccess
│   ├── index.php                 # Front controller do admin
│   ├── Controllers/
│   │   ├── AdminController.php   # Base dos controllers admin (auth check)
│   │   ├── AuthController.php    # Login, logout, recuperação de senha
│   │   ├── DashboardController.php
│   │   ├── PedidosAdminController.php
│   │   ├── VendasController.php
│   │   ├── ConfiguracoesController.php  # Tokens Melhor Envio no banco
│   │   ├── ClientesAdminController.php
│   │   ├── ProdutosAdminController.php
│   │   ├── EstoqueController.php
│   │   ├── InsumosController.php
│   │   ├── ProducaoController.php
│   │   ├── ComprasController.php
│   │   ├── ImportacaoController.php
│   │   ├── BannersController.php
│   │   ├── CategoriasController.php
│   │   ├── DepoimentosController.php
│   │   └── WebhookLogsController.php
│   └── Views/
├── app/
│   ├── Controllers/              # Site público
│   ├── Core/
│   │   ├── Database.php          # PDO Singleton
│   │   ├── Router.php
│   │   ├── Session.php           # CSRF tokens, flash messages
│   │   ├── Mailer.php            # E-mails HTML transacionais
│   │   ├── InfinitePayProvider.php
│   │   ├── WebhookGuard.php      # Rate limit + validação de secret
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   ├── Helper.php
│   │   ├── CsvParser.php
│   │   └── XlsxParser.php
│   ├── Models/
│   │   ├── Pedido.php            # Criação, status, ciclo completo
│   │   ├── Venda.php             # Registra venda + estoque + movimentações
│   │   ├── Carrinho.php
│   │   ├── Cliente.php
│   │   ├── Usuario.php           # Admin users (bcrypt)
│   │   ├── Produto.php
│   │   ├── EmailLog.php
│   │   └── ...
│   ├── Services/
│   │   ├── FreteService.php      # Orquestra Melhor Envio + opções locais
│   │   └── MelhorEnvioService.php
│   └── Views/
├── assets/
│   ├── css/style.css
│   ├── css/admin.css
│   ├── js/main.js
│   ├── js/masks.js
│   └── images/
├── config/
│   ├── app.php                   # APP_URL dinâmico, constantes globais
│   ├── database.php              # Lê DB_* do .env
│   ├── env.php                   # Loader do .env
│   ├── frete.php                 # Opções locais de entrega
│   └── payment.php               # InfinitePay (lê secret do .env)
├── sql/
│   ├── schema.sql                # Esquema completo
│   ├── seed.sql                  # Dados iniciais de demonstração
│   ├── produtos.sql              # Catálogo de produtos de referência
│   ├── migration_v1_1.sql ... migration_v6_3_separando.sql
│   ├── migration_v6_4_senha_cliente.sql   # ADD COLUMN senha_alterada_em em clientes
│   └── setup_inicial.sql
├── setup/
│   ├── .htaccess                 # Deny from all — bloqueia acesso web
│   ├── install.php
│   ├── migrate_v1_1.php
│   ├── migrate_v1_2.php
│   ├── setup_inicial.php
│   └── importar-insumos/         # Importador CLI de insumos via CSV
├── tests/
│   ├── simular_webhook.php       # Simulador de webhooks InfinitePay (CLI)
│   ├── opcache_reset.php
│   └── test_log_direto.php
├── tools/
│   └── gerar-webhook-secret.php  # Gerador CLI de webhook secret seguro
└── uploads/
    └── temp/.htaccess            # Deny from all para temp
```

---

## 6. Fluxos Principais

### 6.1 Fluxo de Compra

```
Cliente → Carrinho → Checkout (endereço + frete) → Confirmar
→ InfinitePayProvider cria link de checkout
→ Cliente paga em checkout.infinitepay.io
→ InfinitePay POST /webhook/infinitepay/{secret}
→ WebhookGuard valida secret + rate limit
→ WebhookController processa: Venda::registrar() (atômica)
    ├── registra em vendas
    ├── atualiza estoque de produtos
    ├── registra movimentações
    ├── atualiza status do pedido → pago → separando (v6.3)
    └── envia 2 e-mails pós-commit (pagamentoConfirmado + statusAtualizado)
```

### 6.2 Fluxo de Frete

```
CEP informado → FreteService::calcular()
→ MelhorEnvioService::calcular() (API Melhor Envio)
→ Merge com FRETE_LOCAIS (retirada, Uber, Motoboy)
→ Opções exibidas ao cliente
→ Seleção persistida no pedido (transportadora, prazo, valor)
→ Frete incluído como item separado no payload InfinitePay
```

### 6.3 Ciclo de Status do Pedido

```
pendente → pago → separando (automático, webhook v6.3)
         → enviado → entregue
         → cancelado | pagamento_recusado | pagamento_expirado
```

### 6.4 Fluxo de E-mails

```
Pedido criado:  → cliente (pedidoCliente) + loja (pedidoLoja)
Pagamento pago: → cliente (pagamentoConfirmado)
Pago→Separando: → cliente (statusAtualizado: separando)
Status manual:  → cliente (statusAtualizado: {novoStatus}) se checkbox marcado
```

### 6.5 Fluxo de Autenticação do Cliente (site público)

```
Cadastro → session_regenerate_id() → mergeOuMigrar(sessaoAnterior, sessaoNova, clienteId)
Login    → autenticar(email, senha) → session_regenerate_id() → mergeOuMigrar(...)
         → redirect para /minha-conta ou para URL de origem (?redirect=)
Logout   → Session::delete(['cliente_id','cliente_nome','cliente_email']) → /minha-conta/login
```

**Recuperação de senha:**
```
/minha-conta/recuperar-senha → Mailer envia token de 1h
/minha-conta/nova-senha/{token} → valida token → Cliente::alterarSenha()
```

**Alteração de senha (autenticado):**
```
GET/POST /minha-conta/alterar-senha (requer login)
→ CSRF + rate limit (5 tentativas por sessão)
→ autenticar(email, senhaAtual) para confirmar identidade
→ password_hash(novaSenha, BCRYPT) + registro de senha_alterada_em
→ session_regenerate_id() + redirect /minha-conta
```

### 6.6 Persistência e Merge do Carrinho

O carrinho é identificado no banco por `sessao_id` (session PHP) e opcionalmente por `cliente_id`.

**Fluxo após login/cadastro — `Carrinho::mergeOuMigrar()`:**

| Caso | Situação | Ação |
|---|---|---|
| 1 | Sem carrinho anônimo | Atualiza `sessao_id` do carrinho do cliente para a nova sessão |
| 2 | Só carrinho anônimo (conta nova ou sem sessão anterior) | Migra: `sessao_id = nova`, `cliente_id = X` no mesmo registro |
| 3 | Ambos existem | Merge atômico em transação: soma quantidades, cap ao estoque, descarta inativos |

**Regras do merge (Caso 3):**
- `sessao_id` do carrinho do cliente atualizado **antes** da transação (garante acesso em falha)
- Produto com `ativo = 0` ou `estoque_atual = 0`: descartado silenciosamente
- Produto já presente no carrinho do cliente: `quantidade = MIN(qtd_cliente + qtd_anonimo, estoque_atual)`
- Produto novo (não estava no carrinho do cliente): inserido com `preco_venda` atual do banco
- Carrinho anônimo deletado ao final da transação; em falha: rollback, carrinho cliente acessível
- `getOuCriar()` possui fallback por `cliente_id` caso `sessao_id` não coincida (safety net)

---

## 7. Dependências Críticas

| Dependência | Tipo | Impacto se falhar |
|---|---|---|
| MySQL / PDO | Infraestrutura | Site inteiro offline |
| InfinitePay API | Externo | Checkout impossível |
| InfinitePay Webhook | Externo | Pedidos ficam "pendente" para sempre |
| Melhor Envio API | Externo | Apenas opções locais de frete disponíveis |
| PHP mail() | Infraestrutura | Sem notificações de pedido |
| Google Fonts CDN | Externo | Degradação visual (fallback para Arial/serif) |

---

## 8. Variáveis de Ambiente Necessárias

Copie `.env.example` para `.env` e preencha:

```env
# Banco de dados
DB_HOST=localhost
DB_NAME=***REDACTED_DB_PROD***          # HostGator: prefixo_nome
DB_USER=***REDACTED_DB_PROD***          # HostGator: prefixo_usuario
DB_PASS=<senha-forte-aqui>

# InfinitePay
INFINITEPAY_HANDLE=irananatural     # InfiniteTag sem "$"
INFINITEPAY_WEBHOOK_SECRET=<256bits-hex>  # Gere com: php tools/gerar-webhook-secret.php
```

**Tokens Melhor Envio:** configurados no painel admin em `/admin/configuracoes` (armazenados no banco, não em arquivos).

---

## 9. Serviços Externos Integrados

| Serviço | Finalidade | Configuração |
|---|---|---|
| InfinitePay Checkout | Pagamentos (PIX + cartão) | `INFINITEPAY_HANDLE` + `INFINITEPAY_WEBHOOK_SECRET` no `.env` |
| Melhor Envio | Cálculo de frete | Token JWT via `/admin/configuracoes` |
| Google Fonts | Tipografia web | CDN externo (Cormorant Garamond + Lato) |
| HostGator / cPanel | Hospedagem + e-mail | cPanel com PHP mail() habilitado |

---

## 10. Estratégia de Autenticação

### Admin (painel)
- Sessão PHP com `session_regenerate_id()` após login
- Senhas com `password_hash()` / `password_verify()` (bcrypt, PHP padrão)
- CSRF token por sessão em todos os formulários POST (`Session::csrfToken()`)
- Recuperação de senha por token de 1h enviado por e-mail
- Constante `ADMIN_SESSION` como chave da sessão

### Clientes (site público)
- Sessão PHP para carrinho e dados do cliente; cookie `iran_sess` (HttpOnly, SameSite=Lax)
- CPF + e-mail + telefone coletados no checkout
- Opção de cadastro para salvar pedidos (modelo `Cliente`)
- **Expiração automática de sessão (v6.6)**:
  - Inatividade: `SESSION_CLIENTE_INATIVIDADE` = 1800 s (30 min) — renovado a cada requisição
  - Absoluta: `SESSION_CLIENTE_ABSOLUTA` = 28800 s (8 h) — independente de atividade
  - Validação application-level em `Session::start()` a cada requisição (não depende de GC do servidor)
  - `Session::iniciarSessaoCliente()` grava `_cliente_criado_em` e `_cliente_ativo_em` no login/cadastro
  - `Session::_encerrarSessaoCliente()` remove auth, preserva carrinho + CSRF, define flash explicativo
  - Sessões legadas (criadas antes do deploy) recebem contadores zerados na primeira requisição
  - `requerLogin()` preserva mensagem de expiração via `Session::hasFlash()` (não sobrescreve)

### Webhook (InfinitePay)
- Secret de 256 bits (64 hex) na URL: `POST /webhook/infinitepay/{secret}`
- `WebhookGuard::gate()` — validação por `hash_equals()` (timing-safe)
- Rate limiting por IP: máx 20 falhas por 60s (arquivo em sys_get_temp_dir)
- Idempotência: `transaction_nsu` único em `pagamentos` (constraint DB)

---

## 11. Estratégia de Segurança

### Implementado
- [x] `.env` fora do git; credenciais carregadas por `config/env.php`
- [x] Senhas com bcrypt (PHP `password_hash`)
- [x] CSRF em todos os formulários POST (admin + contato + perfil)
- [x] Prepared statements em todas as queries SQL (PDO, emulate_prepares=false)
- [x] `htmlspecialchars()` em todas as saídas de dados do usuário
- [x] `Options -Indexes` no `.htaccess` (bloqueia listagem de diretórios)
- [x] Bloqueio de `.env`, `.log`, `.sql`, `.md` via `.htaccess`
- [x] `setup/` bloqueado por `.htaccess` (Deny from all)
- [x] `uploads/temp/` bloqueado por `.htaccess`
- [x] `robots.txt` bloqueia `/admin/`, `/config/`, `/app/`
- [x] Webhook secret com `hash_equals()` (prevenção de timing attack)
- [x] Rate limiting de webhook por IP
- [x] `session_regenerate_id()` após login e após alteração de senha
- [x] Expiração de sessão por inatividade (30 min) e absoluta (8 h) — application-level
- [x] Logs de login, logout e expiração automática (sem PII)
- [x] Erros de banco não expostos ao usuário (logados em error_log)

### Pendente / Recomendado
- [ ] Habilitar HTTPS redirect no `.htaccess` antes do deploy
- [ ] Headers de segurança HTTP (CSP, HSTS, X-Frame-Options, X-Content-Type)
- [ ] Configurar `session.cookie_secure=1` no php.ini/cPanel (requer HTTPS ativo)
- [ ] Rotacionar webhook secret após qualquer commit em repositório remoto
- [ ] Implementar Content Security Policy para bloquear XSS
- [ ] Substituir Google Fonts por hospedagem local (elimina dependência CDN externa e melhora privacidade)
- [ ] Expiração de sessão do admin (atualmente sem timeout automático)

---

## 12. Estrutura de Banco de Dados

### Tabelas principais

| Tabela | Descrição |
|---|---|
| `usuarios` | Administradores do painel (bcrypt) |
| `clientes` | Compradores cadastrados; campo `senha_alterada_em TIMESTAMP` registra último troca de senha |
| `produtos` | Catálogo de produtos com dimensões e preços |
| `categorias` | Categorias de produtos |
| `pedidos` | Pedidos com status, frete, desconto PIX |
| `pedido_itens` | Itens do pedido (preço já com desconto PIX) |
| `pagamentos` | Transações InfinitePay (idempotência) |
| `vendas` | Vendas registradas (trigger do webhook) |
| `venda_itens` | Itens de venda |
| `carrinhos` | Carrinhos por `sessao_id` + `cliente_id`; migrados/mesclados via `mergeOuMigrar()` no login |
| `carrinho_itens` | Itens do carrinho |
| `insumos` | Matérias-primas (estoque com custo médio ponderado) |
| `compras_insumos` | Entradas de insumos |
| `producao` | Ordens de produção |
| `ficha_tecnica` | Receituário de produtos (insumo + quantidade) |
| `mov_produtos` | Movimentações de estoque de produtos acabados |
| `configuracoes` | Configurações do sistema (KV) — inclui tokens Melhor Envio |
| `email_logs` | Log de envios de e-mail |
| `webhook_logs` | Log de webhooks recebidos |
| `banners` | Banners do carrossel da home |
| `depoimentos` | Depoimentos de clientes |
| `recuperacao_senha` | Tokens de recuperação de senha (1h) |
| `import_history` | Histórico de importações CSV |

### Convenção de campos
- `criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
- `ativo TINYINT(1) DEFAULT 1`
- IDs: `INT UNSIGNED NOT NULL AUTO_INCREMENT`
- Monetário: `DECIMAL(10,2)` (preços) / `DECIMAL(12,6)` (custo médio)
- Charset: `utf8mb4_unicode_ci`

---

## 13. Rotas / Endpoints Principais

### Site público (index.php)
```
GET  /                          HomeController::index
GET  /produtos                  ProdutosController::index
GET  /produtos/{cat}/{slug}     ProdutosController::show
GET  /carrinho                  CarrinhoController::index
POST /carrinho/adicionar        CarrinhoController::adicionar
POST /carrinho/remover          CarrinhoController::remover
POST /carrinho/atualizar        CarrinhoController::atualizar
POST /frete/calcular            FreteController::calcular
GET  /checkout/endereco         CheckoutController::endereco
POST /checkout/endereco         CheckoutController::salvarEndereco
GET  /checkout/confirmar        CheckoutController::confirmar
POST /checkout/finalizar        CheckoutController::finalizar
GET  /checkout/sucesso          CheckoutController::sucesso
GET  /checkout/aguardando       CheckoutController::aguardando
POST /webhook/infinitepay/{secret}  WebhookController::infinitepay
GET  /minha-conta                           ClienteController::painel
GET  /minha-conta/login                     ClienteController::login
POST /minha-conta/login                     ClienteController::login
GET  /minha-conta/logout                    ClienteController::logout
GET  /minha-conta/cadastro                  ClienteController::cadastro
POST /minha-conta/cadastro                  ClienteController::cadastro
GET  /minha-conta/editar                    ClienteController::editarPerfil
POST /minha-conta/editar                    ClienteController::editarPerfil
GET  /minha-conta/alterar-senha             ClienteController::alterarSenha
POST /minha-conta/alterar-senha             ClienteController::alterarSenha
GET  /minha-conta/recuperar-senha           ClienteController::recuperarSenha
POST /minha-conta/recuperar-senha           ClienteController::recuperarSenha
GET  /minha-conta/nova-senha/{token}        ClienteController::novaSenha
POST /minha-conta/nova-senha/{token}        ClienteController::novaSenha
GET  /contato                               ContatoController::index
POST /contato/enviar            ContatoController::enviar
GET  /como-comprar              ComoComprarController::index
GET  /pagamento                 PagamentoController::index
GET  /envio                     EnvioController::index
GET  /garantia                  GarantiaController::index
GET  /trocas                    TrocasController::index
GET  /sobre                     SobreController::index
GET  /politica-privacidade      PoliticaPrivacidadeController::index
```

### Painel admin (admin/index.php)
```
GET/POST /admin/login
GET      /admin/dashboard
GET      /admin/pedidos           Lista de pedidos
GET      /admin/pedidos/{id}      Ver pedido + timeline de status
POST     /admin/pedidos/{id}/status    Atualizar status
GET      /admin/vendas            Lista de vendas
GET      /admin/clientes          CRUD de clientes
GET      /admin/produtos          CRUD de produtos
GET      /admin/estoque           Movimentações
GET      /admin/insumos           CRUD de insumos
GET      /admin/producao          Ordens de produção
GET      /admin/compras           Compras de insumos
GET      /admin/configuracoes     Tokens, configurações gerais
GET      /admin/webhook-logs      Logs de webhooks recebidos
GET      /admin/importacao        Importação de insumos CSV/XLSX
```

---

## 14. Status Atual do Desenvolvimento

**Versão:** 6.6 — Gerenciamento seguro de sessão com expiração automática por inatividade e tempo absoluto

### Funcionalidades implementadas e operacionais
- [x] Catálogo de produtos com categorias e galeria de imagens
- [x] Carrinho de compras (sessão + banco) com persistência após login/cadastro (v6.5)
- [x] Merge inteligente de carrinhos anônimo + conta ao autenticar (v6.5)
- [x] Expiração automática de sessão: 30 min inatividade + 8 h absoluto; mensagem amigável ao expirar (v6.6)
- [x] Checkout completo (endereço, frete, pagamento)
- [x] Integração InfinitePay Checkout (link de pagamento)
- [x] Webhook InfinitePay com idempotência e transação atômica
- [x] Desconto PIX 5% por unidade (aplicado no item, não no subtotal)
- [x] Cálculo de frete via Melhor Envio + opções locais
- [x] E-mails automáticos (pedido criado, pagamento confirmado, status atualizado)
- [x] Fluxo automático pago→separando via webhook
- [x] Painel admin completo (pedidos, vendas, clientes, produtos, estoque, insumos, produção)
- [x] Histórico de status de pedidos com timeline
- [x] Gestão de clientes (CRUD admin + edição pelo próprio cliente)
- [x] Alteração de senha pelo cliente autenticado com CSRF + rate limit + audit timestamp (v6.4)
- [x] Recuperação de senha do cliente por e-mail com token de 1h
- [x] Sistema de usuários admin com recuperação de senha
- [x] Importador CLI de insumos (CSV)
- [x] Módulo de produção (ficha técnica, ordens de produção)
- [x] Páginas institucionais (sobre, contato, como comprar, pagamento, envio, garantia, trocas, política de privacidade)
- [x] robots.txt + sitemap.xml
- [x] CSRF em todos os formulários
- [x] WebhookGuard (rate limiting + timing-safe validation)
- [x] Logs de webhook e e-mail

---

## 15. Pendências Técnicas

- [ ] **Habilitar HTTPS no `.htaccess`** antes do deploy (2 linhas comentadas)
- [ ] **Adicionar headers HTTP de segurança** (CSP, HSTS, X-Frame-Options via `.htaccess` ou cPanel)
- [ ] **Configurar sessões seguras** (`session.cookie_secure=1`, `cookie_httponly=1`, `samesite=Strict`) — fazer via `php.ini` no cPanel ou `session_set_cookie_params()` em `Session.php`
- [ ] **og:image por produto** — imagens Open Graph específicas (atualmente usa `og-default.jpg` genérico)
- [ ] **Lazy loading** nas imagens de produto
- [ ] **Tokens Melhor Envio de produção** — cadastrar no painel `/admin/configuracoes`
- [ ] **Webhook InfinitePay de produção** — registrar URL com secret real no dashboard InfinitePay
- [ ] **Substituir Google Fonts** por hospedagem local (LGPD + performance)
- [ ] **Fontes auto-hospedadas** para eliminar dependência CDN

---

## 16. Débitos Técnicos

- **MVC artesanal** — sem framework; aumenta manutenção a longo prazo
- **PHP mail()** — sem retry, sem fila, sem confirmação de entrega; e-mails podem ser dropados silenciosamente
- **WebhookGuard rate limit em arquivo** — `sys_get_temp_dir()` pode ser limpo pelo SO; usar APCu ou Redis em produção escalável
- **Sem paginação** em algumas listas admin (pode ficar lento com volume alto)
- **Sem cache** — cada request recalcula tudo (sem OPcache configurado para produção ainda)
- **Google Analytics** mencionado na política de privacidade mas não implementado no site
- **Frete calculado por CEP** — prazo e dimensões dependem de produtos cadastrados corretamente

---

## 17. Melhorias Recomendadas

1. **Headers de segurança HTTP** — adicionar ao `.htaccess`:
   ```apache
   Header always set X-Frame-Options "SAMEORIGIN"
   Header always set X-Content-Type-Options "nosniff"
   Header always set Referrer-Policy "strict-origin-when-cross-origin"
   Header always set X-XSS-Protection "1; mode=block"
   ```
2. **Content Security Policy** — definir política CSP estrita
3. **Sessões seguras** — `cookie_secure=1`, `httponly=1`, `samesite=Strict`
4. **Fila de e-mail** — implementar tabela de fila para reenvio em falha
5. **WebP** — converter uploads para WebP no processamento (30-40% menor)
6. **Sitemap dinâmico** — incluir URLs de produtos individuais
7. **Schema.org** — JSON-LD de `Product`, `BreadcrumbList`, `Organization`
8. **Lazy loading nativo** — `loading="lazy"` nas imagens de produto
9. **Substituir Google Fonts** por arquivos locais (LGPD + LCP)

---

## 18. Riscos Identificados

| Risco | Nível | Mitigação |
|---|---|---|
| Deploy sem HTTPS ativo | Alto | Habilitar redirect no `.htaccess` antes do go-live |
| Tokens Melhor Envio em BD sem criptografia | Médio | Tokens ficam em clear text na tabela `configuracoes`; mitigar com acesso restrito ao BD |
| PHP mail() sem SPF/DKIM | Médio | E-mails podem cair em spam; configurar SPF/DKIM no cPanel |
| Google Fonts CDN (LGPD) | Baixo | Usuários EU podem ter IP exposto ao Google; mitigar com self-hosting |
| Sem rate limiting no checkout | Médio | Bot pode criar muitos pedidos; adicionar captcha ou rate limit por IP |
| Webhook secret na URL (path) | Baixo-Médio | Pode aparecer em logs Apache; usar header X-Signature como alternativa futura |
| Scripts de teste acessíveis via web | Médio | `tests/*.php` não bloqueados por `.htaccess`; adicionar proteção |

---

## 19. Checklist de Segurança

### Credenciais e secrets
- [x] `.env` no `.gitignore`, nunca commitado
- [x] `config/database_local.php` removido do rastreamento git
- [x] Credenciais do banco carregadas de variáveis de ambiente
- [x] Webhook secret carregado do `.env` (256 bits)
- [x] Senhas de usuário armazenadas com bcrypt
- [ ] Rotacionar `INFINITEPAY_WEBHOOK_SECRET` se repositório foi ou será publicado
- [ ] Rotacionar senha do banco HostGator se repositório foi publicado

### Controle de acesso
- [x] Painel admin protegido por sessão
- [x] CSRF em todos os formulários POST
- [x] `Options -Indexes` (sem listagem de diretórios)
- [x] `setup/` bloqueado por `.htaccess`
- [ ] `tests/` deve ser bloqueado por `.htaccess` em produção
- [ ] Headers de segurança HTTP (CSP, HSTS, X-Frame-Options)
- [ ] Sessões com `cookie_secure`, `cookie_httponly`, `samesite`

### Dados e privacidade
- [x] Prepared statements em todas as queries
- [x] `htmlspecialchars()` em todas as saídas
- [x] Logs de pagamento fora do git
- [x] Política de privacidade publicada (LGPD)
- [ ] Logs de produção nunca commitados (adicionar `logs/` ao monitoramento)

---

## 20. Checklist Pré-Deploy (Produção)

- [ ] Criar `.env` no servidor com credenciais de produção (HostGator)
- [ ] Rodar migrations pendentes em ordem: `migration_v6_1_email.sql`, `v6_2_historico.sql`, `v6_3_separando.sql`, `v6_4_senha_cliente.sql`
- [ ] Cadastrar tokens Melhor Envio de produção em `/admin/configuracoes`
- [ ] Gerar novo webhook secret: `php tools/gerar-webhook-secret.php`
- [ ] Registrar URL do webhook no dashboard InfinitePay: `https://irananatural.com.br/webhook/infinitepay/{secret}`
- [ ] Habilitar HTTPS redirect no `.htaccess` (descomentar 2 linhas)
- [ ] Adicionar headers de segurança HTTP no `.htaccess` ou cPanel
- [ ] Configurar `session.cookie_secure=1` no cPanel PHP Settings
- [ ] Verificar que `upload_max_filesize` e `post_max_size` estão adequados no cPanel
- [ ] Testar envio de e-mail com `mail()` no HostGator (SPF/DKIM configurados)
- [ ] Verificar permissões de pastas: `uploads/` precisa de escrita
- [ ] Bloquear `tests/` por `.htaccess` em produção
- [ ] Remover ou bloquear acesso a `tools/` em produção
- [ ] Testar fluxo completo de compra em produção com valor mínimo
- [ ] Confirmar que webhook responde 200 OK com body `{"ok":true}`
- [ ] Verificar logs de erro no cPanel após primeira compra

---

## 21. Checklist Pré-Commit

- [ ] Nenhum arquivo `.env` no staging (`git status | grep .env`)
- [ ] `config/database_local.php` **NÃO** no staging
- [ ] Sem credenciais hardcoded em arquivos PHP (grep por senhas/tokens)
- [ ] Sem dumps SQL com dados reais (`sql/SiteIrana_*.sql` no `.gitignore`)
- [ ] Sem arquivos de log no staging (`logs/` no `.gitignore`)
- [ ] Sem uploads de usuário no staging (`uploads/` no `.gitignore`)
- [ ] `.gitignore` commitado e atualizado
- [ ] `projeto.md` refletindo estado atual

---

## 22. Comandos Git de Correção de Segurança

Se o repositório remoto (GitHub) contiver commits com dados sensíveis, executar:

```bash
# Verificar arquivos sensíveis no histórico
git log --all --full-history -- "*.env" "config/database_local.php" "logs/*.log"

# Remover arquivo sensível do HISTÓRICO COMPLETO (requer BFG ou git filter-repo)
# ATENÇÃO: Reescreve o histórico — coordenar com toda a equipe antes
pip install git-filter-repo
git filter-repo --path logs/infinitepay.log --invert-paths
git filter-repo --path config/database_local.php --invert-paths

# Após reescrita, forçar push (CUIDADO: irreversível no remoto)
git push origin --force --all

# ALTERNATIVA SEGURA: Rotacionar as credenciais expostas
# Não requer reescrita do histórico se as credenciais foram trocadas
```

---

## 23. Arquivos que NÃO devem ir ao GitHub

| Arquivo | Motivo |
|---|---|
| `.env` | Credenciais reais do banco e InfinitePay |
| `config/database_local.php` | Senha hardcoded do banco local |
| `logs/*.log` | Logs de transações (PII: nome, e-mail, endereço, CPF) |
| `sql/SiteIrana_*.sql` | Dumps com IP de produção e nome do banco |
| `uploads/` | Imagens de produtos (arquivos grandes, não são código) |

---

*Documento atualizado em 2026-05-11 — reflete versão 6.6 (expiração automática de sessão).*
*Próxima revisão recomendada: antes de cada deploy em produção.*
