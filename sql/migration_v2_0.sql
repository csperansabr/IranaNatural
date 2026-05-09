-- =====================================================
-- Iraná Natural — Migration v2.0 — Módulo E-commerce
-- Carrinho de Compras, Clientes e Pedidos
-- MySQL 5.7+ / MariaDB 10.3+
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Clientes (cadastro público)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(150)  NOT NULL,
    cpf           VARCHAR(14)   NOT NULL UNIQUE,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    telefone      VARCHAR(20),
    senha         VARCHAR(255)  NOT NULL,
    ativo         TINYINT(1)    NOT NULL DEFAULT 1,
    criado_em     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_cpf   (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Endereços dos clientes (principal do cadastro)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS enderecos_clientes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id  INT UNSIGNED  NOT NULL UNIQUE,
    cep         VARCHAR(9)    NOT NULL,
    logradouro  VARCHAR(200)  NOT NULL,
    numero      VARCHAR(20)   NOT NULL,
    complemento VARCHAR(100),
    bairro      VARCHAR(100)  NOT NULL,
    cidade      VARCHAR(100)  NOT NULL,
    estado      CHAR(2)       NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tokens de recuperação de senha
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS tokens_senha (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id  INT UNSIGNED  NOT NULL,
    token       VARCHAR(64)   NOT NULL UNIQUE,
    expira_em   DATETIME      NOT NULL,
    usado       TINYINT(1)    NOT NULL DEFAULT 0,
    criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Carrinhos de compras (por sessão, opcionalmente vinculado a cliente)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS carrinhos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sessao_id     VARCHAR(64)   NOT NULL,
    cliente_id    INT UNSIGNED  NULL,
    criado_em     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sessao (sessao_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Itens do carrinho
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS carrinho_itens (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carrinho_id    INT UNSIGNED  NOT NULL,
    produto_id     INT UNSIGNED  NOT NULL,
    quantidade     INT           NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uk_carrinho_produto (carrinho_id, produto_id),
    FOREIGN KEY (carrinho_id) REFERENCES carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id)  REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Pedidos (cabeçalho)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero              VARCHAR(20)   NOT NULL UNIQUE,
    cliente_id          INT UNSIGNED  NOT NULL,
    status              ENUM('pendente','pago','separando','enviado','entregue','cancelado')
                        NOT NULL DEFAULT 'pendente',
    forma_pagamento     ENUM('pix','cartao_credito','transferencia','dinheiro') NOT NULL,
    subtotal            DECIMAL(12,2) NOT NULL DEFAULT 0,
    frete               DECIMAL(10,2) NOT NULL DEFAULT 0,
    desconto            DECIMAL(10,2) NOT NULL DEFAULT 0,
    total               DECIMAL(12,2) NOT NULL DEFAULT 0,
    observacoes         TEXT,
    -- Endereço de entrega (snapshot no momento do pedido)
    entrega_cep         VARCHAR(9),
    entrega_logradouro  VARCHAR(200),
    entrega_numero      VARCHAR(20),
    entrega_complemento VARCHAR(100),
    entrega_bairro      VARCHAR(100),
    entrega_cidade      VARCHAR(100),
    entrega_estado      CHAR(2),
    criado_em           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    INDEX idx_numero    (numero),
    INDEX idx_cliente   (cliente_id),
    INDEX idx_status    (status),
    INDEX idx_criado    (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Itens do pedido
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pedido_itens (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id      INT UNSIGNED  NOT NULL,
    produto_id     INT UNSIGNED  NOT NULL,
    nome_produto   VARCHAR(150)  NOT NULL,
    quantidade     INT           NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal       DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pedido_id)  REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Histórico de status dos pedidos (audit trail)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos_historico (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id   INT UNSIGNED  NOT NULL,
    status      VARCHAR(50)   NOT NULL,
    observacao  TEXT,
    criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
