# Iraná Natural — Documentação do Projeto

> Versão: 1.2.0 | Data: 2026-05 | Hospedagem: HostGator (PHP 8+, MySQL)

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
│       ├── depoimentos/{index,form}.php
│       └── importacao/{index,form,historico}.php
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
| `usuarios` | Usuários do painel admin (v1.0) |
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

### Novos campos em `produtos` (v1.1)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `seo_titulo` | VARCHAR(70) | Título para mecanismos de busca (fallback: `nome`) |
| `seo_descricao` | VARCHAR(160) | Meta description (fallback: `descricao_curta`) |
| `tags` | TEXT | Tags separadas por vírgula (ex: `relaxante,floral,ansiedade`) |

**Migration para instalações existentes:** executar `sql/migration_v1_1.sql`.  
**Novas instalações:** `sql/schema.sql` já inclui as colunas.

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

### 5.6 Publicação de Produtos (v1.1)
- Produto com `ativo = 0` é **invisível no site público** mas permanece **visível e editável no admin**
- `Produto::allAtivos()` filtra `WHERE ativo = 1` (site público)
- `Produto::allForAdmin()` retorna todos os produtos (admin) ordenados: ativos primeiro
- A listagem admin mostra badges "Publicado" / "Rascunho"; linhas inativas têm opacidade reduzida
- O formulário de edição exibe banner de aviso quando o produto é rascunho

### 5.7 SEO e Schema.org (v1.1)
- Cada página de produto gera `<meta name="description">` a partir de `seo_descricao` (fallback `descricao_curta`)
- `<title>` usa `seo_titulo` (fallback `nome`)
- `og:type = product` + `og:image` com a imagem principal
- JSON-LD `Product` com `offers` (preço em BRL, disponibilidade por estoque) injetado no `<head>`
- `Helper::md()` renderiza Markdown da `descricao_completa` em HTML seguro (escape HTML antes dos padrões)

### 5.8 Tags (v1.1)
- Armazenadas como string separada por vírgula: `relaxante,floral,ansiedade`
- Input visual de chips no admin (Enter ou vírgula para adicionar, Backspace remove o último)
- Exibidas como badges verdes na página pública do produto
- Sem tabela separada — simplicidade prevalece sobre normalização

### 5.9 Ordenação de Imagens (v1.1)
- Coluna `ordem` em `imagens_produtos` determina a sequência (ASC)
- Admin exibe galeria com botões ↑ ↓ por linha; cada clique envia POST e recarrega a página
- `Produto::moverImagem()` normaliza os valores de `ordem` (0, 1, 2…) antes de trocar para evitar colisões
- Primeiro `upload` de imagem em produto sem imagens torna-se automaticamente `principal = 1`
- Excluir a imagem principal elege a primeira remanescente como nova principal

### 5.10 Estoque
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
| `POST /admin/produtos/{id}/imagem-principal/{imgId}` | Definir imagem principal |
| `POST /admin/produtos/{id}/imagem-excluir/{imgId}` | Excluir imagem |
| `POST /admin/produtos/{id}/imagem-mover/{imgId}` | Mover imagem (body: `direction=up\|down`) |

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

### Migrations em instalações existentes

Toda alteração de banco (ALTER TABLE, CREATE TABLE, novos campos) é disponibilizada como um script PHP em `setup/migrate_vX_Y.php`. Para aplicar:

1. **Fazer login** no painel admin (`/admin/login`)
2. **Acessar** `https://irananatural.com.br/setup/migrate_vX_Y.php`
   - O script valida a sessão admin antes de executar
   - Cada passo é idempotente: colunas já existentes são ignoradas sem erro
3. **APAGAR** o arquivo imediatamente após a confirmação

| Arquivo | Versão | O que faz |
|---------|--------|-----------|
| `setup/install.php` | v1.0 | Cria todas as tabelas + usuário admin + seed |
| `setup/migrate_v1_1.php` | v1.1 | Adiciona `seo_titulo`, `seo_descricao`, `tags` em `produtos` |
| `setup/migrate_v1_2.php` | v1.2 | Adiciona `sku` em `produtos`; cria `import_history` e `import_errors` |

> O arquivo `.sql` correspondente em `sql/` serve apenas como referência/documentação.

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

## 12. Sistema de Importação (v1.2)

### Entidades Suportadas
| Entidade | Tabela | Identificação |
|----------|--------|---------------|
| `produtos` | `produtos` | SKU (preferencial) → slug derivado do nome |
| `insumos`  | `insumos`  | Nome (case-insensitive) |
| `estoque`  | `produtos` / `insumos` | SKU ou nome para produtos; nome para insumos |

### Formatos Aceitos
- **CSV**: separador auto-detectado (`;`, `,`, `\t`, `|`); encoding auto-detectado (UTF-8, ISO-8859-1, Windows-1252)
- **XLSX**: leitura pura PHP via ZipArchive + SimpleXML (sem extensão extra); strings compartilhadas, células esparsas e booleans suportados
- Tamanho máximo: 5 MB | Máximo de 2000 linhas de dados por importação

### Fluxo de Importação
1. Usuário acessa `/admin/importacao/{entidade}`
2. Seleciona o modo de importação e faz upload do arquivo
3. Sistema faz preview via AJAX (`POST /admin/importacao/{entidade}/preview`):
   - Parseia o arquivo (CSV ou XLSX)
   - Mapeia cabeçalhos para nomes canônicos via aliases
   - Valida cada linha e retorna status por linha
4. Usuário revisa a tabela de preview com indicadores coloridos:
   - ✅ ok (verde) — linha válida, será importada
   - ⚠️ warning (amarelo) — tem avisos mas será importada
   - ❌ error (vermelho) — erro de validação, será ignorada
   - ⏭️ skip (cinza) — ignorada pelo modo de importação selecionado
5. Usuário confirma → `POST /admin/importacao/{entidade}/processar`
6. Resultado exibido com contadores e detalhes de erros
7. Importação registrada em `import_history`

### Estrutura de Colunas por Entidade

**Produtos** (obrigatórias: `nome`, `categoria`, `preco_venda`):
```
nome, sku, categoria, descricao_curta, descricao_completa, modo_uso, cuidados,
preco_venda, margem_desejada, estoque_atual, estoque_minimo,
tags, seo_titulo, seo_descricao, ativo
```

**Insumos** (obrigatórias: `nome`, `unidade_medida`):
```
nome, descricao, unidade_medida, fornecedor, custo_medio, estoque_atual, estoque_minimo
```

**Estoque** (obrigatórias: `identificador`, `tipo`, `quantidade`):
```
identificador, tipo (produto|insumo), quantidade, observacao
```

### Validações Aplicadas
- Campos obrigatórios não podem estar vazios
- `preco_venda` e `custo_medio`: número positivo (aceita `R$ 1.234,56`, `1234.56`, `1234,56`)
- `quantidade` (estoque): número não-negativo, pode ser 0
- `unidade_medida`: deve ser `kg`, `g`, `mg`, `l`, `ml`, `un`, `pct` ou `cx`
- `tipo` (estoque): deve ser `produto` ou `insumo`
- `categoria` (produto): deve existir na tabela `categorias` com `ativo = 1`
- Duplicatas de `nome` ou `sku` dentro do arquivo geram aviso
- Referência não encontrada no DB gera skip ou erro

### Modos de Importação
| Modo | Comportamento |
|------|--------------|
| `criar` | Insere apenas registros não existentes; existentes são ignorados (skip) |
| `atualizar` | Atualiza apenas registros existentes; não-existentes são ignorados (skip) |
| `criar_atualizar` | Insere se não existe, atualiza se existe |

### Lógica de Identificação (SKU Matching)
- **Produtos**: busca primeiro por `sku` (exato); se não encontrar, busca por `slug` derivado do `nome`
- Slug único garantido: se já existe, adiciona sufixo `-2`, `-3`, etc.
- **Insumos**: busca por `nome` (case-insensitive, LOWER())
- **Estoque/Produto**: busca por `sku`, depois por `nome` (case-insensitive)
- **Estoque/Insumo**: busca por `nome` (case-insensitive)

### Campo `ativo` (Produtos)
Valores aceitos como `true`: `sim`, `s`, `yes`, `y`, `1`, `true`, `ativo`, `publicado`  
Tudo mais → `0` (inativo)  
**Célula vazia**: trata como `1` (ativo por padrão — coluna ausente ou vazia não desativa o produto)

### Imagens
Imagens **não são importadas**. Após a importação, cada produto deve ter suas imagens adicionadas manualmente via `/admin/produtos/{id}/editar`. A interface de import exibe um aviso claro sobre isso.

### Tabelas de Controle
| Tabela | Finalidade |
|--------|-----------|
| `import_history` | Registro de cada importação (entidade, modo, arquivo, contadores) |
| `import_errors`  | Erros detalhados por linha (import_id, linha, campo, valor, mensagem) |

### Migration
Para instalações existentes executar: `setup/migrate_v1_2.php`
- Adiciona `sku VARCHAR(100) NULL UNIQUE` em `produtos`
- Cria `import_history` e `import_errors`

### Correções Aplicadas (v1.2 — validação completa)

| # | Arquivo | Problema | Correção |
|---|---------|----------|----------|
| 1 | `app/Core/CsvParser.php` | Linhas de comentário (`#`) tratadas como dados, quebrando import do template CSV | Adiciona skip de linhas onde `$line[0]` começa com `#` |
| 2 | `admin/Views/importacao/form.php` | `file-info` sempre visível — `display:flex` no final do inline style sobrepunha `display:none` | Remove o `display:flex` duplicado; JS já aplica `flex` ao mostrar |
| 3 | `admin/Controllers/ImportacaoController.php` | Re-preview após troca de modo acumulava arquivos temporários órfãos em `uploads/temp/` | Limpa o arquivo temp anterior (se existir) antes de salvar o novo |
| 4 | `app/Core/Importador.php` | `ativo` com célula vazia → `toBool('')` = 0 (inativo); produto importado sem coluna ficaria oculto | Trata string vazia como padrão ativo (1); só aplica `toBool` se valor não for vazio |
| 5 | `admin/Views/importacao/form.php` | Troca de modo não re-analisava o arquivo — preview continuava mostrando resultado do modo anterior | Listener de radio chama `doPreview()` automaticamente se `selectedFile` existe e `importDone` é false |
| 6 | `admin/Views/importacao/form.php` | Sem aviso de que imagens não são importadas; usuário poderia esperar que fossem | Adiciona banner informativo fixo para entidade `produtos` |
| 7 | `admin/Views/importacao/form.php` | Grid de resultado `repeat(4,1fr)` quebrava em telas <600px | Adiciona `@media` para `repeat(2,1fr)` em mobile |
| 8 | `admin/Views/importacao/historico.php` | Botão de filtro ativo sem distinção visual — usuário não sabia qual filtro estava selecionado | Aplica `outline` via JS para destacar o botão ativo; inicializa "Todos" com outline |

### Arquivos Criados/Modificados (v1.2)
- `app/Core/CsvParser.php` — parser CSV com detecção de encoding, delimitador e skip de comentários
- `app/Core/XlsxParser.php` — parser XLSX puro PHP via ZipArchive + SimpleXML
- `app/Core/Importador.php` — lógica central de mapeamento, validação e processamento
- `app/Models/ImportHistory.php` — modelo de histórico de importação
- `admin/Controllers/ImportacaoController.php` — controller admin com cleanup de temp files
- `admin/Views/importacao/index.php` — página inicial com cards e histórico recente
- `admin/Views/importacao/form.php` — UI de upload + preview + importação (JS puro, corrigida)
- `admin/Views/importacao/historico.php` — tabela completa de histórico com filtro visual
- `sql/migration_v1_2.sql` — SQL puro para referência
- `setup/migrate_v1_2.php` — script PHP de migration com auth e idempotência
- `uploads/temp/.htaccess` — nega acesso direto aos arquivos temporários

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
| EasyMDE CDN | Editor Markdown leve sem API key, sem build step |
| `Helper::md()` manual | Renderiza Markdown em HTML sem Composer (admin-authored, escape-first) |
| Tags como CSV | Simples, sem tabela extra; suficiente para a escala atual |
| Imagem-mover POST | Reordenação server-side com redirecionamento; sem drag-and-drop JS complexo |

---

*Documento mantido pelo desenvolvedor. Atualizar a cada decisão relevante.*
