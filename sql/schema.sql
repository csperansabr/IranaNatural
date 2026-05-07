-- =====================================================
-- Iraná Natural — Esquema do Banco de Dados
-- MySQL 5.7+ / MariaDB 10.3+
-- Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Usuários administrativos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    senha      VARCHAR(255)  NOT NULL,
    ativo      TINYINT(1)    NOT NULL DEFAULT 1,
    criado_em  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Configurações do site
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracoes (
    chave     VARCHAR(100) PRIMARY KEY,
    valor     TEXT,
    descricao VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Categorias de produtos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100)  NOT NULL,
    slug       VARCHAR(110)  NOT NULL UNIQUE,
    descricao  TEXT,
    imagem     VARCHAR(255),
    ativo      TINYINT(1)    NOT NULL DEFAULT 1,
    ordem      SMALLINT      NOT NULL DEFAULT 0,
    criado_em  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Insumos (matérias-primas)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS insumos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150)    NOT NULL,
    descricao       TEXT,
    unidade_medida  VARCHAR(20)     NOT NULL,  -- kg, g, mg, l, ml, un, pct, cx
    estoque_atual   DECIMAL(12,4)   NOT NULL DEFAULT 0,
    estoque_minimo  DECIMAL(12,4)   NOT NULL DEFAULT 0,
    custo_medio     DECIMAL(12,6)   NOT NULL DEFAULT 0,  -- custo por unidade_medida
    fornecedor      VARCHAR(200),
    ativo           TINYINT(1)      NOT NULL DEFAULT 1,
    criado_em       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Compras de insumos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS compras_insumos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_compra     DATE            NOT NULL,
    fornecedor      VARCHAR(200),
    insumo_id       INT UNSIGNED    NOT NULL,
    quantidade      DECIMAL(12,4)   NOT NULL,
    valor_total     DECIMAL(12,2)   NOT NULL,
    valor_unitario  DECIMAL(12,6)   NOT NULL,  -- calculado: valor_total / quantidade
    custo_medio_ant DECIMAL(12,6)   NOT NULL DEFAULT 0,  -- CMA antes da compra (auditoria)
    custo_medio_novo DECIMAL(12,6)  NOT NULL DEFAULT 0,  -- CMA após a compra (auditoria)
    observacoes     TEXT,
    criado_em       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Produtos acabados
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id      INT UNSIGNED    NOT NULL,
    nome              VARCHAR(150)    NOT NULL,
    slug              VARCHAR(160)    NOT NULL UNIQUE,
    sku               VARCHAR(100)    NULL UNIQUE,
    descricao_curta   TEXT,
    descricao_completa TEXT,
    composicao        TEXT,
    modo_uso          TEXT,
    cuidados          TEXT,
    seo_titulo        VARCHAR(70),
    seo_descricao     VARCHAR(160),
    tags              TEXT,
    preco_venda       DECIMAL(10,2)   NOT NULL DEFAULT 0,
    margem_desejada   DECIMAL(5,2)    NOT NULL DEFAULT 0,  -- %
    custo_calculado   DECIMAL(12,4)   NOT NULL DEFAULT 0,
    lucro_calculado   DECIMAL(12,2)   NOT NULL DEFAULT 0,  -- preco_venda - custo_calculado
    margem_real       DECIMAL(5,2)    NOT NULL DEFAULT 0,  -- % real
    estoque_atual     INT             NOT NULL DEFAULT 0,
    estoque_minimo    INT             NOT NULL DEFAULT 0,
    ativo             TINYINT(1)      NOT NULL DEFAULT 1,
    criado_em         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Imagens dos produtos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS imagens_produtos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id  INT UNSIGNED    NOT NULL,
    caminho     VARCHAR(255)    NOT NULL,
    principal   TINYINT(1)      NOT NULL DEFAULT 0,
    ordem       SMALLINT        NOT NULL DEFAULT 0,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Fichas técnicas (composição dos produtos)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS fichas_tecnicas (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id  INT UNSIGNED    NOT NULL,
    insumo_id   INT UNSIGNED    NOT NULL,
    quantidade  DECIMAL(12,4)   NOT NULL,
    unidade     VARCHAR(20)     NOT NULL,  -- pode diferir da unidade do insumo (conversão automática)
    UNIQUE KEY uk_produto_insumo (produto_id, insumo_id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (insumo_id)  REFERENCES insumos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Produções
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS producoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id          INT UNSIGNED    NOT NULL,
    quantidade_produzida INT            NOT NULL,
    quantidade_perda    DECIMAL(10,4)   NOT NULL DEFAULT 0,
    motivo_perda        TEXT,
    data_producao       DATE            NOT NULL,
    responsavel         VARCHAR(100),
    custo_real          DECIMAL(12,2)   NOT NULL DEFAULT 0,
    observacoes         TEXT,
    criado_em           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Movimentações de insumos (histórico)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS mov_insumos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    insumo_id       INT UNSIGNED    NOT NULL,
    tipo            ENUM('entrada','saida','ajuste','perda') NOT NULL,
    quantidade      DECIMAL(12,4)   NOT NULL,
    saldo_antes     DECIMAL(12,4)   NOT NULL DEFAULT 0,
    saldo_apos      DECIMAL(12,4)   NOT NULL DEFAULT 0,
    ref_tipo        VARCHAR(50),    -- 'compra', 'producao', 'ajuste'
    ref_id          INT UNSIGNED,
    observacoes     TEXT,
    criado_em       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Movimentações de produtos acabados (histórico)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS mov_produtos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id  INT UNSIGNED    NOT NULL,
    tipo        ENUM('entrada','saida','ajuste','perda') NOT NULL,
    quantidade  INT             NOT NULL,
    saldo_antes INT             NOT NULL DEFAULT 0,
    saldo_apos  INT             NOT NULL DEFAULT 0,
    ref_tipo    VARCHAR(50),    -- 'producao', 'venda', 'ajuste'
    ref_id      INT UNSIGNED,
    observacoes TEXT,
    criado_em   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Vendas (cabeçalho)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS vendas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_venda      DATE        NOT NULL,
    forma_pagamento ENUM('pix','dinheiro','debito','credito','transferencia','outro') NOT NULL DEFAULT 'pix',
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0,
    desconto        DECIMAL(12,2) NOT NULL DEFAULT 0,
    valor_final     DECIMAL(12,2) NOT NULL DEFAULT 0,
    lucro_total     DECIMAL(12,2) NOT NULL DEFAULT 0,
    observacoes     TEXT,
    criado_em       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Itens de venda
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS vendas_itens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venda_id        INT UNSIGNED    NOT NULL,
    produto_id      INT UNSIGNED    NOT NULL,
    quantidade      INT             NOT NULL,
    preco_unitario  DECIMAL(10,2)   NOT NULL,
    custo_unitario  DECIMAL(12,4)   NOT NULL DEFAULT 0,
    subtotal        DECIMAL(12,2)   NOT NULL,
    lucro           DECIMAL(12,2)   NOT NULL DEFAULT 0,
    FOREIGN KEY (venda_id)   REFERENCES vendas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Banners da página inicial
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS banners (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo     VARCHAR(200),
    subtitulo  VARCHAR(200),
    imagem     VARCHAR(255)    NOT NULL,
    link       VARCHAR(255),
    ordem      SMALLINT        NOT NULL DEFAULT 0,
    ativo      TINYINT(1)      NOT NULL DEFAULT 1,
    criado_em  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Depoimentos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS depoimentos (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100)    NOT NULL,
    texto      TEXT            NOT NULL,
    avaliacao  TINYINT         NOT NULL DEFAULT 5,
    foto       VARCHAR(255),
    ativo      TINYINT(1)      NOT NULL DEFAULT 1,
    ordem      SMALLINT        NOT NULL DEFAULT 0,
    criado_em  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Histórico de importações (v1.2)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS import_history (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entidade     ENUM('produtos','insumos','estoque') NOT NULL,
    modo         ENUM('criar','atualizar','criar_atualizar') NOT NULL DEFAULT 'criar_atualizar',
    arquivo_nome VARCHAR(255),
    total_linhas INT NOT NULL DEFAULT 0,
    inseridos    INT NOT NULL DEFAULT 0,
    atualizados  INT NOT NULL DEFAULT 0,
    erros        INT NOT NULL DEFAULT 0,
    ignorados    INT NOT NULL DEFAULT 0,
    usuario_id   INT UNSIGNED NULL,
    criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Erros detalhados por importação (v1.2)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS import_errors (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    import_id INT UNSIGNED NOT NULL,
    linha     INT NOT NULL,
    campo     VARCHAR(100),
    valor     TEXT,
    mensagem  TEXT NOT NULL,
    FOREIGN KEY (import_id) REFERENCES import_history(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
