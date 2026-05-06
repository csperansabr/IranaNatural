# Iraná Natural — Documentação do Projeto

> Versão: 1.0.0 | Data: 2026-05 | Hospedagem: HostGator (PHP 8+, MySQL)

---

## 1. Visão Geral

Sistema web completo para a marca **Iraná Natural**, unindo:
- Site institucional + catálogo de produtos
- Gestão de insumos (matérias-primas) com custo médio ponderado
- Gestão de produtos com ficha técnica e cálculo automático de custo
- Controle de produção (debite de insumos + entrada de produtos)
- Controle de vendas (pedidos com múltiplos itens)
- Controle de estoque (insumos e produtos acabados)
- Painel administrativo completo

**Domínio:** irananatural.com.br  
**WhatsApp:** (51) 99229-6036  
**E-mail contato:** contato@irananatural.com.br  
**Admin inicial:** admin@irananatural.com.br | senha: Iran@2024  

---

## 2. Estrutura de Diretórios

```
/                           ← Raiz do domínio (public_html/)
├── .htaccess               ← Rewrite rules (roteamento público)
├── index.php               ← Front controller público
├── PROJETO.md              ← Esta documentação
│
├── config/
│   ├── database.php        ← Credenciais do banco (NUNCA versionar com dados reais)
│   └── app.php             ← Configurações globais (WhatsApp, e-mail, etc.)
│
├── app/
│   ├── Core/
│   │   ├── Database.php    ← Singleton PDO
│   │   ├── Model.php       ← Base model com CRUD genérico
│   │   ├── Controller.php  ← Base controller (renderiza views)
│   │   ├── Router.php      ← Roteador público (pattern matching)
│   │   ├── Session.php     ← Gerenciamento de sessão + CSRF
│   │   └── Helper.php      ← Utilidades (slug, money, upload, conversão de unidades)
│   │
│   ├── Controllers/        ← Controllers do site público
│   │   ├── HomeController.php
│   │   ├── ProdutosController.php
│   │   ├── SobreController.php
│   │   └── ContatoController.php
│   │
│   ├── Models/             ← Modelos (compartilhados entre público e admin)
│   │   ├── Produto.php
│   │   ├── Categoria.php
│   │   ├── Insumo.php
│   │   ├── CompraInsumo.php
│   │   ├── FichaTecnica.php
│   │   ├── Producao.php
│   │   ├── Venda.php
│   │   ├── Banner.php
│   │   ├── Depoimento.php
│   │   └── Usuario.php
│   │
│   └── Views/              ← Views do site público
│       ├── layouts/default.php
│       ├── home/index.php
│       ├── produtos/{index,show}.php
│       ├── sobre/index.php
│       ├── contato/index.php
│       └── errors/404.php
│
├── admin/
│   ├── .htaccess           ← Rewrite rules do painel
│   ├── index.php           ← Front controller admin
│   ├── Controllers/        ← Controllers administrativos
│   │   ├── AdminController.php     ← Base com render + auth
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── CategoriasController.php
│   │   ├── InsumosController.php
│   │   ├── ComprasController.php
│   │   ├── ProdutosAdminController.php
│   │   ├── ProducaoController.php
│   │   ├── VendasController.php
│   │   ├── EstoqueController.php
│   │   ├── BannersController.php
│   │   └── DepoimentosController.php
│   │
│   └── Views/              ← Views do painel
│       ├── layouts/default.php
│       ├── login.php
│       ├── dashboard/index.php
│       ├── categorias/{index,form}.php
│       ├── insumos/{index,form}.php
│       ├── compras/{index,form}.php
│       ├── produtos/{index,form,ficha}.php
│       ├── producao/{index,form}.php
│       ├── vendas/{index,form}.php
│       ├── estoque/index.php
│       ├── banners/{index,form}.php
│       └── depoimentos/{index,form}.php
│
├── assets/
│   ├── css/style.css       ← Estilos do site público
│   ├── css/admin.css       ← Estilos do painel admin
│   ├── js/main.js          ← Scripts do site público (slider, tabs, nav)
│   └── images/             ← Logo, favicon, imagens estáticas
│
├── uploads/                ← Arquivos enviados pelo admin
│   ├── produtos/
│   ├── banners/
│   ├── depoimentos/
│   └── categorias/
│
├── sql/
│   ├── schema.sql          ← Criação das tabelas
│   └── seed.sql            ← Dados iniciais de demonstração
│
└── setup/
    └── install.php         ← Script de instalação (APAGAR após uso)
```

---

## 3. Arquitetura

**Padrão:** MVC simples sem framework  
**Roteamento:** Pattern matching via query string `?url=`  
**Autoload:** PSR-4-like manual via `spl_autoload_register`  
**Banco:** PDO com prepared statements (proteção SQL Injection)  
**Sessão:** PHP nativa com `session_regenerate_id` no login  
**Uploads:** `move_uploaded_file` com validação de extensão e tamanho

### Namespaces
| Namespace | Diretório |
|-----------|-----------|
| `App\Core\*` | `app/Core/` |
| `App\Controllers\*` | `app/Controllers/` |
| `App\Models\*` | `app/Models/` |
| `Admin\Controllers\*` | `admin/Controllers/` |

---

## 4. Modelagem do Banco

### Tabelas Principais

| Tabela | Finalidade |
|--------|-----------|
| `usuarios` | Usuários do painel admin |
| `configuracoes` | Pares chave-valor de config do site |
| `categorias` | Categorias de produtos |
| `insumos` | Matérias-primas com estoque e custo médio |
| `compras_insumos` | Registro de entradas de insumos |
| `produtos` | Produtos acabados |
| `imagens_produtos` | Galeria de imagens dos produtos |
| `fichas_tecnicas` | Composição dos produtos (receita por unidade) |
| `producoes` | Registros de produção |
| `mov_insumos` | Histórico de movimentações de insumos |
| `mov_produtos` | Histórico de movimentações de produtos acabados |
| `vendas` | Cabeçalho de vendas |
| `vendas_itens` | Itens de cada venda |
| `banners` | Banners da página inicial |
| `depoimentos` | Depoimentos de clientes |

---

## 5. Regras de Negócio

### 5.1 Custo Médio Ponderado (CMP)
Ao registrar uma compra de insumo:
```
Novo CMP = (Estoque Atual × CMP Atual + Quantidade Comprada × Valor Unitário)
           ÷ (Estoque Atual + Quantidade Comprada)
```
- Registrado automaticamente no momento da compra
- Auditado: custo anterior e novo são gravados em `compras_insumos`
- Todos os produtos que usam o insumo têm seu custo recalculado imediatamente

### 5.2 Custo do Produto
- Calculado a partir da ficha técnica: `SUM(quantidade_insumo × custo_médio_insumo)` por unidade
- Conversão automática de unidades (g↔kg, ml↔l)
- Lucro calculado: `preco_venda - custo_calculado`
- Margem real: `(lucro / preco_venda) × 100`
- Margem desejada: serve para sugerir preço e alertar quando abaixo

### 5.3 Conversão de Unidades
| Família | Unidades | Base |
|---------|----------|------|
| Peso | kg, g, mg | g |
| Volume | l, ml | ml |
| Unidade | un, pct, cx | unit (1:1) |

Conversão entre famílias diferentes é **bloqueada** (alerta visual na ficha técnica).

### 5.4 Produção
1. Ficha técnica define insumos para **1 unidade** do produto
2. Ao produzir N unidades, consome `N × quantidade_por_unidade` de cada insumo
3. Se insumo insuficiente: **permite com alerta** (estoque pode ficar negativo)
4. Perdas são registradas manualmente (quantidade + motivo)
5. Incremento no estoque do produto = `quantidade_produzida - quantidade_perda`
6. Insumos debitados por `quantidade_produzida` (incluindo perdas — foram consumidos)
7. Custo real registrado com base no CMP atual no momento da produção

### 5.5 Vendas
1. Pedido com múltiplos produtos (cabeçalho + itens)
2. Preço de venda pode diferir do preço cadastrado (desconto por pedido)
3. Venda **bloqueada** se estoque do produto for insuficiente
4. Ao confirmar: debita estoque, registra lucro por item
5. Custo unitário capturado no momento da venda (snapshot histórico)

### 5.6 Estoque
- Ajustes manuais registram movimentação de auditoria
- Estoque negativo de insumo é permitido (produção forçada) mas marcado com alerta
- Alertas: item com `estoque_atual <= estoque_minimo`

---

## 6. Rotas

### Site Público
| URL | Controller | Método |
|-----|-----------|--------|
| `/` | HomeController | index |
| `/produtos` | ProdutosController | index |
| `/produtos/{cat}` | ProdutosController | categoria |
| `/produtos/{cat}/{slug}` | ProdutosController | show |
| `/sobre` | SobreController | index |
| `/contato` | ContatoController | index |
| `POST /contato/enviar` | ContatoController | enviar |

### Painel Admin
| URL | Ação |
|-----|------|
| `/admin/login` | Login (GET+POST) |
| `/admin/logout` | Logout |
| `/admin/dashboard` | Dashboard |
| `/admin/{modulo}` | Listagem |
| `/admin/{modulo}/novo` | Formulário criação (GET) / Criar (POST) |
| `/admin/{modulo}/{id}/editar` | Formulário edição (GET) / Atualizar (POST) |
| `/admin/{modulo}/{id}/excluir` | Excluir (POST) |
| `/admin/produtos/{id}/ficha` | Ficha técnica |
| `/admin/producao/verificar/{prod}/{qtd}` | AJAX verificação de insumos |
| `/admin/estoque/ajuste` | Ajuste de estoque (GET+POST) |

---

## 7. Instalação na HostGator

### Passo a passo

1. **Fazer upload** de todos os arquivos via FTP para `public_html/`
2. **Criar banco MySQL** no painel da HostGator (cPanel → MySQL Databases)
3. **Editar** `config/database.php` com as credenciais do banco
4. **Editar** `config/app.php` se necessário (URL, WhatsApp, etc.)
5. **Acessar** `https://irananatural.com.br/setup/install.php`
   - Isso criará as tabelas, o usuário admin e os dados de demonstração
6. **Acessar** o painel: `https://irananatural.com.br/admin/login`
7. **APAGAR** o arquivo `setup/install.php` imediatamente!
8. **Adicionar logo** em `assets/images/logo.png` (recomendado: 200×60px, PNG transparente)
9. **Adicionar favicon** em `assets/images/favicon.png` (32×32px)

### Versões mínimas
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- mod_rewrite habilitado (HostGator: sim, por padrão)

### Permissões de diretório
```bash
chmod 755 uploads/
chmod 755 uploads/produtos/ uploads/banners/ uploads/depoimentos/ uploads/categorias/
```

---

## 8. Segurança

- PDO com prepared statements em todas as queries
- `htmlspecialchars()` em todas as saídas
- Senhas com `password_hash(PASSWORD_BCRYPT)`
- `session_regenerate_id(true)` no login
- Sessão com `httponly=true`, `samesite=Lax`
- `Options -Indexes` no `.htaccess` (impede listagem de diretórios)
- Upload: validação de extensão + tamanho máximo 5MB
- Admin inacessível sem sessão válida

---

## 9. Paleta Visual e Tipografia

| Token | Valor | Uso |
|-------|-------|-----|
| `--verde-floresta` | `#2C5F2E` | Cor principal, CTA, header admin |
| `--verde-oliva` | `#5D7A3A` | Destaques, hover |
| `--bege-claro` | `#F5EFE3` | Seções alternadas |
| `--off-white` | `#FDFAF4` | Background principal |
| `--marrom` | `#6B4E37` | Detalhes, bordas |

**Fonts:** Cormorant Garamond (títulos) + Lato (corpo) via Google Fonts

---

## 10. Melhorias Futuras

- [ ] Integração com Nuvemshop para e-commerce completo
- [ ] Controle de lotes e validade de insumos
- [ ] Múltiplos usuários com permissões granulares
- [ ] Relatórios PDF (vendas, produção, estoque)
- [ ] Emissão de nota fiscal (NF-e)
- [ ] Cálculo de frete
- [ ] Disparo de e-mail marketing
- [ ] App mobile para gestão de produção
- [ ] Controle por código de barras (EAN/QR)
- [ ] Integração com gateways de pagamento

---

## 11. Decisões Técnicas

| Decisão | Motivo |
|---------|--------|
| Sem Composer | Máxima compatibilidade com shared hosting |
| Autoloader manual | Evita dependência de Composer em produção |
| Bootstrap/CSS próprio | CSS custom para visual artesanal específico |
| Chart.js CDN | Gráficos sem dependência de build |
| `mail()` PHP | Nativo no HostGator, sem configuração SMTP |
| PDO singleton | Uma conexão por request |
| Sem JS framework | Vanilla JS é suficiente para a escala atual |
| Slug no banco | Não recalculado em tempo real para performance |
| Custo médio no banco | Snapshot atual para auditoria histórica de vendas |

---

*Documento mantido pelo desenvolvedor. Atualizar a cada decisão relevante.*
