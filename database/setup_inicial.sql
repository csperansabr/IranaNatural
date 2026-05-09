-- =============================================================================
-- Iraná Natural — Setup Inicial Consolidado do Banco de Dados
-- Versão: 2.0 (Schema original + Migration v1.1 + v1.2 + Importação de Insumos)
-- Charset:    utf8mb4
-- Collation:  utf8mb4_unicode_ci
-- Engine:     InnoDB
-- MySQL 8.0+ / MariaDB 10.3+
-- =============================================================================
--
-- USO LOCAL (XAMPP / WAMP):
--   Opção A (recomendado): Executar via http://localhost/projeto/setup_inicial.php
--   Opção B (manual):      Importar via phpMyAdmin ou mysql CLI:
--                          mysql -u root -p < database/setup_inicial.sql
--
-- USO MANUAL — ajuste o nome do banco na linha CREATE DATABASE abaixo.
-- Em produção (hospedagem) o banco já existe — NÃO execute este arquivo lá.
--
-- ATENÇÃO: O hash da senha do administrador NÃO está neste arquivo.
--          A senha é definida pelo setup_inicial.php (usa password_hash PHP).
--          Se importar manualmente, altere a senha após o import.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- BANCO DE DADOS
-- Altere 'irananatural' para o nome desejado se necessário.
-- Em hospedagem compartilhada, o banco já existe — comente as duas linhas abaixo.
-- -----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `irananatural`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `irananatural`;

-- =============================================================================
-- BLOCO 1 — TABELAS INDEPENDENTES (sem dependências de chave estrangeira)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Usuários administrativos do painel
-- Senha armazenada como hash bcrypt (via password_hash PHP)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`      VARCHAR(100)    NOT NULL,
    `email`     VARCHAR(150)    NOT NULL,
    `senha`     VARCHAR(255)    NOT NULL COMMENT 'Hash bcrypt gerado pelo PHP',
    `ativo`     TINYINT(1)      NOT NULL DEFAULT 1,
    `criado_em` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_usuarios_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuários com acesso ao painel administrativo';

-- -----------------------------------------------------------------------------
-- Configurações globais do site (chave → valor)
-- Usada para parâmetros editáveis sem deploy de código
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracoes` (
    `chave`     VARCHAR(100)    NOT NULL,
    `valor`     TEXT,
    `descricao` VARCHAR(255),
    PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Parâmetros configuráveis do site (nome, slogan, contato, etc.)';

-- -----------------------------------------------------------------------------
-- Categorias de produtos do site
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`      VARCHAR(100)    NOT NULL,
    `slug`      VARCHAR(110)    NOT NULL COMMENT 'URL amigável, único',
    `descricao` TEXT,
    `imagem`    VARCHAR(255)    COMMENT 'Caminho relativo em uploads/categorias/',
    `ativo`     TINYINT(1)      NOT NULL DEFAULT 1,
    `ordem`     SMALLINT        NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição no site',
    `criado_em` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categorias_slug` (`slug`),
    KEY `idx_categorias_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorias de produtos (Banhos, Chás, Incensos, etc.)';

-- -----------------------------------------------------------------------------
-- Categorias de insumos / matérias-primas
-- Adicionada na migration de suporte à importação de insumos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias_insumos` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`      VARCHAR(100)    NOT NULL,
    `criado_em` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cat_insumos_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Agrupamento de insumos por tipo (Resinas, Ervas, Embalagens, etc.)';

-- -----------------------------------------------------------------------------
-- Banners do carrossel da página inicial
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `banners` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `titulo`    VARCHAR(200),
    `subtitulo` VARCHAR(200),
    `imagem`    VARCHAR(255)    NOT NULL COMMENT 'Caminho relativo em uploads/banners/',
    `link`      VARCHAR(255)    COMMENT 'URL de destino ao clicar',
    `ordem`     SMALLINT        NOT NULL DEFAULT 0,
    `ativo`     TINYINT(1)      NOT NULL DEFAULT 1,
    `criado_em` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_banners_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Banners do carrossel na página inicial';

-- -----------------------------------------------------------------------------
-- Depoimentos de clientes exibidos no site
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `depoimentos` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`      VARCHAR(100)    NOT NULL,
    `texto`     TEXT            NOT NULL,
    `avaliacao` TINYINT         NOT NULL DEFAULT 5 COMMENT '1 a 5 estrelas',
    `foto`      VARCHAR(255)    COMMENT 'Caminho relativo em uploads/depoimentos/',
    `ativo`     TINYINT(1)      NOT NULL DEFAULT 1,
    `ordem`     SMALLINT        NOT NULL DEFAULT 0,
    `criado_em` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_depoimentos_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Depoimentos de clientes exibidos na página inicial';

-- -----------------------------------------------------------------------------
-- Vendas — cabeçalho do pedido
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vendas` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `data_venda`      DATE            NOT NULL,
    `forma_pagamento` ENUM('pix','dinheiro','debito','credito','transferencia','outro')
                                      NOT NULL DEFAULT 'pix',
    `subtotal`        DECIMAL(12,2)   NOT NULL DEFAULT 0,
    `desconto`        DECIMAL(12,2)   NOT NULL DEFAULT 0,
    `valor_final`     DECIMAL(12,2)   NOT NULL DEFAULT 0,
    `lucro_total`     DECIMAL(12,2)   NOT NULL DEFAULT 0,
    `observacoes`     TEXT,
    `criado_em`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_vendas_data` (`data_venda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de vendas (cabeçalho)';

-- -----------------------------------------------------------------------------
-- Histórico de importações (via módulo de importação CSV/XLSX)
-- Adicionada na migration v1.2
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `import_history` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `entidade`     ENUM('produtos','insumos','estoque')
                                   NOT NULL COMMENT 'Tipo de dado importado',
    `modo`         ENUM('criar','atualizar','criar_atualizar')
                                   NOT NULL DEFAULT 'criar_atualizar',
    `arquivo_nome` VARCHAR(255)    COMMENT 'Nome original do arquivo enviado',
    `total_linhas` INT             NOT NULL DEFAULT 0,
    `inseridos`    INT             NOT NULL DEFAULT 0,
    `atualizados`  INT             NOT NULL DEFAULT 0,
    `erros`        INT             NOT NULL DEFAULT 0,
    `ignorados`    INT             NOT NULL DEFAULT 0,
    `usuario_id`   INT UNSIGNED    NULL COMMENT 'FK para usuarios.id (sem constraint — permite registros históricos)',
    `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_import_history_entidade` (`entidade`),
    KEY `idx_import_history_criado` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Log de importações em lote (CSV / XLSX)';

-- =============================================================================
-- BLOCO 2 — TABELAS COM DEPENDÊNCIAS DE PRIMEIRO NÍVEL
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Insumos (matérias-primas utilizadas nos produtos)
-- Depende de: categorias_insumos (tipo_id, nullable)
-- Colunas tipo_id, observacoes e data_conferencia adicionadas na migration
-- de suporte ao importador de insumos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `insumos` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `tipo_id`          INT UNSIGNED    NULL COMMENT 'FK para categorias_insumos.id',
    `nome`             VARCHAR(150)    NOT NULL,
    `descricao`        TEXT,
    `unidade_medida`   VARCHAR(20)     NOT NULL COMMENT 'kg, g, mg, l, ml, un, pct, cx',
    `estoque_atual`    DECIMAL(12,4)   NOT NULL DEFAULT 0,
    `estoque_minimo`   DECIMAL(12,4)   NOT NULL DEFAULT 0,
    `custo_medio`      DECIMAL(12,6)   NOT NULL DEFAULT 0 COMMENT 'Custo médio ponderado por unidade',
    `fornecedor`       VARCHAR(200),
    `observacoes`      TEXT,
    `data_conferencia` DATE            NULL COMMENT 'Última data de conferência física do estoque',
    `ativo`            TINYINT(1)      NOT NULL DEFAULT 1,
    `criado_em`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_insumos_tipo_id` (`tipo_id`),
    KEY `idx_insumos_ativo` (`ativo`),
    CONSTRAINT `fk_insumos_tipo_id`
        FOREIGN KEY (`tipo_id`) REFERENCES `categorias_insumos` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Matérias-primas e insumos utilizados na produção';

-- -----------------------------------------------------------------------------
-- Produtos acabados
-- Depende de: categorias
-- Inclui campos de SEO (migration v1.1) e SKU (migration v1.2)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `produtos` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `categoria_id`        INT UNSIGNED    NOT NULL,
    `nome`                VARCHAR(150)    NOT NULL,
    `slug`                VARCHAR(160)    NOT NULL COMMENT 'URL amigável única',
    `sku`                 VARCHAR(100)    NULL COMMENT 'Código interno do produto (opcional)',
    `descricao_curta`     TEXT            COMMENT 'Exibida em cards e listagens',
    `descricao_completa`  TEXT            COMMENT 'Exibida na página do produto',
    `composicao`          TEXT            COMMENT 'Lista de ingredientes/componentes',
    `modo_uso`            TEXT,
    `cuidados`            TEXT,
    `seo_titulo`          VARCHAR(70)     COMMENT 'Title tag para SEO (migration v1.1)',
    `seo_descricao`       VARCHAR(160)    COMMENT 'Meta description para SEO (migration v1.1)',
    `tags`                TEXT            COMMENT 'Palavras-chave separadas por vírgula (migration v1.1)',
    `preco_venda`         DECIMAL(10,2)   NOT NULL DEFAULT 0,
    `margem_desejada`     DECIMAL(5,2)    NOT NULL DEFAULT 0 COMMENT 'Margem de lucro desejada em %',
    `custo_calculado`     DECIMAL(12,4)   NOT NULL DEFAULT 0 COMMENT 'Custo calculado via ficha técnica',
    `lucro_calculado`     DECIMAL(12,2)   NOT NULL DEFAULT 0 COMMENT 'preco_venda - custo_calculado',
    `margem_real`         DECIMAL(5,2)    NOT NULL DEFAULT 0 COMMENT 'Margem real em %',
    `estoque_atual`       INT             NOT NULL DEFAULT 0,
    `estoque_minimo`      INT             NOT NULL DEFAULT 0,
    `ativo`               TINYINT(1)      NOT NULL DEFAULT 1,
    `criado_em`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_produtos_slug` (`slug`),
    UNIQUE KEY `uk_produtos_sku`  (`sku`),
    KEY `idx_produtos_categoria` (`categoria_id`),
    KEY `idx_produtos_ativo` (`ativo`),
    KEY `idx_produtos_ativo_categoria` (`ativo`, `categoria_id`),
    CONSTRAINT `fk_produtos_categoria_id`
        FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Produtos acabados disponíveis para venda no site';

-- =============================================================================
-- BLOCO 3 — TABELAS COM DEPENDÊNCIAS DE SEGUNDO NÍVEL
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Compras de insumos (entrada de matéria-prima)
-- Depende de: insumos
-- Armazena histórico de CMA (Custo Médio Ponderado) para auditoria
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `compras_insumos` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `data_compra`      DATE            NOT NULL,
    `fornecedor`       VARCHAR(200),
    `insumo_id`        INT UNSIGNED    NOT NULL,
    `quantidade`       DECIMAL(12,4)   NOT NULL,
    `valor_total`      DECIMAL(12,2)   NOT NULL,
    `valor_unitario`   DECIMAL(12,6)   NOT NULL COMMENT 'valor_total / quantidade (calculado)',
    `custo_medio_ant`  DECIMAL(12,6)   NOT NULL DEFAULT 0 COMMENT 'CMA do insumo antes da compra (auditoria)',
    `custo_medio_novo` DECIMAL(12,6)   NOT NULL DEFAULT 0 COMMENT 'CMA do insumo após a compra (auditoria)',
    `observacoes`      TEXT,
    `criado_em`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_compras_insumos_data` (`data_compra`),
    KEY `idx_compras_insumos_insumo` (`insumo_id`),
    CONSTRAINT `fk_compras_insumos_insumo_id`
        FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de compras de insumos com rastreio de custo médio';

-- -----------------------------------------------------------------------------
-- Imagens dos produtos
-- Depende de: produtos (CASCADE: apaga imagens ao apagar produto)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `imagens_produtos` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `produto_id` INT UNSIGNED    NOT NULL,
    `caminho`    VARCHAR(255)    NOT NULL COMMENT 'Caminho relativo em uploads/produtos/',
    `principal`  TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1 = imagem principal do produto',
    `ordem`      SMALLINT        NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_imagens_produto_id` (`produto_id`),
    KEY `idx_imagens_principal` (`produto_id`, `principal`),
    CONSTRAINT `fk_imagens_produtos_produto_id`
        FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Galeria de imagens dos produtos';

-- -----------------------------------------------------------------------------
-- Fichas técnicas — composição dos produtos (quais insumos e quantidades)
-- Depende de: produtos (CASCADE), insumos
-- Base para cálculo de custo e verificação de estoque antes da produção
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fichas_tecnicas` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `produto_id` INT UNSIGNED    NOT NULL,
    `insumo_id`  INT UNSIGNED    NOT NULL,
    `quantidade` DECIMAL(12,4)   NOT NULL COMMENT 'Quantidade de insumo por unidade do produto',
    `unidade`    VARCHAR(20)     NOT NULL COMMENT 'Unidade usada na ficha (pode diferir do insumo — haverá conversão)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ficha_produto_insumo` (`produto_id`, `insumo_id`),
    KEY `idx_fichas_insumo_id` (`insumo_id`),
    CONSTRAINT `fk_fichas_tecnicas_produto_id`
        FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_fichas_tecnicas_insumo_id`
        FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Composição de insumos por produto (base para custo e produção)';

-- -----------------------------------------------------------------------------
-- Produções — registro de lotes produzidos
-- Depende de: produtos
-- Ao registrar produção: debita insumos da ficha técnica, credita estoque produto
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `producoes` (
    `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `produto_id`           INT UNSIGNED    NOT NULL,
    `quantidade_produzida` INT             NOT NULL,
    `quantidade_perda`     DECIMAL(10,4)   NOT NULL DEFAULT 0 COMMENT 'Perda de insumo durante o processo',
    `motivo_perda`         TEXT,
    `data_producao`        DATE            NOT NULL,
    `responsavel`          VARCHAR(100),
    `custo_real`           DECIMAL(12,2)   NOT NULL DEFAULT 0 COMMENT 'Custo real do lote na data de produção',
    `observacoes`          TEXT,
    `criado_em`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_producoes_produto_id` (`produto_id`),
    KEY `idx_producoes_data` (`data_producao`),
    CONSTRAINT `fk_producoes_produto_id`
        FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de lotes de produção com rastreio de custo real';

-- -----------------------------------------------------------------------------
-- Movimentações de insumos (log de entradas, saídas, ajustes e perdas)
-- Depende de: insumos
-- ref_tipo / ref_id: identifica a origem da movimentação (compra, produção, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mov_insumos` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `insumo_id`   INT UNSIGNED    NOT NULL,
    `tipo`        ENUM('entrada','saida','ajuste','perda') NOT NULL,
    `quantidade`  DECIMAL(12,4)   NOT NULL,
    `saldo_antes` DECIMAL(12,4)   NOT NULL DEFAULT 0,
    `saldo_apos`  DECIMAL(12,4)   NOT NULL DEFAULT 0,
    `ref_tipo`    VARCHAR(50)     COMMENT 'Origem: compra | producao | ajuste | importacao',
    `ref_id`      INT UNSIGNED    NULL COMMENT 'ID do registro de origem',
    `observacoes` TEXT,
    `criado_em`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_mov_insumos_insumo_id` (`insumo_id`),
    KEY `idx_mov_insumos_tipo` (`tipo`),
    KEY `idx_mov_insumos_criado` (`criado_em`),
    CONSTRAINT `fk_mov_insumos_insumo_id`
        FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de todas as movimentações de estoque de insumos';

-- -----------------------------------------------------------------------------
-- Movimentações de produtos acabados (log de entradas, saídas, ajustes)
-- Depende de: produtos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mov_produtos` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `produto_id`  INT UNSIGNED    NOT NULL,
    `tipo`        ENUM('entrada','saida','ajuste','perda') NOT NULL,
    `quantidade`  INT             NOT NULL,
    `saldo_antes` INT             NOT NULL DEFAULT 0,
    `saldo_apos`  INT             NOT NULL DEFAULT 0,
    `ref_tipo`    VARCHAR(50)     COMMENT 'Origem: producao | venda | ajuste',
    `ref_id`      INT UNSIGNED    NULL COMMENT 'ID do registro de origem',
    `observacoes` TEXT,
    `criado_em`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_mov_produtos_produto_id` (`produto_id`),
    KEY `idx_mov_produtos_tipo` (`tipo`),
    KEY `idx_mov_produtos_criado` (`criado_em`),
    CONSTRAINT `fk_mov_produtos_produto_id`
        FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de todas as movimentações de estoque de produtos acabados';

-- =============================================================================
-- BLOCO 4 — TABELAS COM DEPENDÊNCIAS DE TERCEIRO NÍVEL
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Itens de venda (detalhamento de cada produto vendido)
-- Depende de: vendas (CASCADE), produtos
-- Armazena preço e custo no momento da venda (imutável após registro)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vendas_itens` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `venda_id`       INT UNSIGNED    NOT NULL,
    `produto_id`     INT UNSIGNED    NOT NULL,
    `quantidade`     INT             NOT NULL,
    `preco_unitario` DECIMAL(10,2)   NOT NULL COMMENT 'Preço no momento da venda',
    `custo_unitario` DECIMAL(12,4)   NOT NULL DEFAULT 0 COMMENT 'Custo calculado no momento da venda',
    `subtotal`       DECIMAL(12,2)   NOT NULL,
    `lucro`          DECIMAL(12,2)   NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_vendas_itens_venda_id` (`venda_id`),
    KEY `idx_vendas_itens_produto_id` (`produto_id`),
    CONSTRAINT `fk_vendas_itens_venda_id`
        FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_vendas_itens_produto_id`
        FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Itens individuais de cada venda';

-- -----------------------------------------------------------------------------
-- Erros detalhados por importação
-- Depende de: import_history (CASCADE)
-- Adicionada na migration v1.2
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `import_errors` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `import_id` INT UNSIGNED    NOT NULL,
    `linha`     INT             NOT NULL COMMENT 'Número da linha do arquivo com erro',
    `campo`     VARCHAR(100)    COMMENT 'Nome da coluna com erro',
    `valor`     TEXT            COMMENT 'Valor que causou o erro',
    `mensagem`  TEXT            NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_import_errors_import_id` (`import_id`),
    CONSTRAINT `fk_import_errors_import_id`
        FOREIGN KEY (`import_id`) REFERENCES `import_history` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Erros linha a linha registrados durante importações';

-- =============================================================================
-- BLOCO 5 — DADOS INICIAIS OBRIGATÓRIOS PARA FUNCIONAMENTO DO SISTEMA
-- Somente registros técnicos mínimos — sem dados operacionais ou comerciais
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Configurações padrão do site
-- Usadas pelo sistema quando não há valor definido no banco
-- -----------------------------------------------------------------------------
INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`) VALUES
    ('site_nome',     'Iraná Natural',                           'Nome exibido no site e e-mails'),
    ('site_slogan',   'Natureza em cada detalhe',               'Slogan do site'),
    ('whatsapp',      '5551992296036',                          'Número WhatsApp com DDI (sem + ou espaços)'),
    ('email_contato', 'contato@irananatural.com.br',            'E-mail de contato público'),
    ('instagram',     'https://instagram.com/irananatural',     'Link para o perfil Instagram'),
    ('horario',       'Seg–Sex: 9h–18h | Sáb: 9h–13h',         'Horário de atendimento exibido no site'),
    ('endereco',      'Rio Grande do Sul, Brasil',              'Endereço de contato exibido no site'),
    ('sobre_resumo',  'A Iraná Natural nasceu do amor pelas ervas, pela terra e pela espiritualidade consciente. Cada produto é elaborado com cuidado artesanal, respeitando os ciclos da natureza e honrando a sabedoria ancestral das plantas.',
                                                                'Texto resumo exibido na página Sobre')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`), `descricao` = VALUES(`descricao`);

-- -----------------------------------------------------------------------------
-- Usuário administrador inicial
-- IMPORTANTE: A senha real (hash bcrypt) é definida pelo setup_inicial.php.
-- Se executar este SQL manualmente, o hash abaixo ('PENDENTE') é inválido —
-- execute o setup_inicial.php para gerar o hash correto, ou atualize com:
--   UPDATE usuarios SET senha = '<hash_bcrypt>' WHERE email = 'admin@irananatural.com.br';
-- Credenciais padrão (definidas pelo setup_inicial.php): Iran@2024
-- -----------------------------------------------------------------------------
INSERT INTO `usuarios` (`nome`, `email`, `senha`) VALUES
    ('Administrador', 'admin@irananatural.com.br', 'PENDENTE_EXECUTAR_SETUP_INICIAL_PHP')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- RESUMO DAS TABELAS CRIADAS
-- Total: 18 tabelas
-- =============================================================================
--
--  Grupos e tabelas (em ordem de dependência):
--
--  [Autenticação]
--    usuarios             — usuários do painel administrativo
--
--  [Configuração]
--    configuracoes        — parâmetros do site (chave → valor)
--
--  [Catálogo de Produtos]
--    categorias           — agrupamento de produtos
--    produtos             — produtos acabados (inclui SEO e SKU)
--    imagens_produtos     — galeria de imagens por produto
--
--  [Insumos e Compras]
--    categorias_insumos   — agrupamento de insumos por tipo
--    insumos              — matérias-primas (inclui tipo, observações)
--    compras_insumos      — entradas de insumo com CMA histórico
--
--  [Fichas Técnicas e Produção]
--    fichas_tecnicas      — composição de cada produto
--    producoes            — lotes de produção registrados
--
--  [Estoque e Movimentações]
--    mov_insumos          — log de movimentações de insumos
--    mov_produtos         — log de movimentações de produtos
--
--  [Vendas]
--    vendas               — cabeçalho das vendas
--    vendas_itens         — itens de cada venda
--
--  [Conteúdo do Site]
--    banners              — carrossel da página inicial
--    depoimentos          — depoimentos de clientes
--
--  [Importação em Lote]
--    import_history       — log de importações CSV/XLSX
--    import_errors        — erros por linha de cada importação
--
-- =============================================================================
