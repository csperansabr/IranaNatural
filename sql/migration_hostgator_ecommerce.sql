-- =============================================================================
-- Iraná Natural — Script de Atualização HostGator (v2.0 → v5.0)
-- Aplica todas as tabelas e colunas do módulo e-commerce que estavam faltando.
-- SEGURO para executar mesmo com banco parcialmente atualizado (usa IF NOT EXISTS).
-- Data: 2026-05-08
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. CLIENTES
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `clientes` (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(150)  NOT NULL,
    cpf           VARCHAR(14)   NULL,
    email         VARCHAR(150)  NULL,
    telefone      VARCHAR(20)   NULL,
    data_nascimento DATE         NULL,
    origem        ENUM('online','admin') NOT NULL DEFAULT 'online',
    senha         VARCHAR(255)  NULL,
    ativo         TINYINT(1)    NOT NULL DEFAULT 1,
    criado_em     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_cpf   (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar colunas que migrações posteriores exigem (caso a tabela já existisse antes)
ALTER TABLE `clientes`
    MODIFY COLUMN cpf   VARCHAR(14)  NULL,
    MODIFY COLUMN email VARCHAR(150) NULL,
    MODIFY COLUMN senha VARCHAR(255) NULL;

-- data_nascimento
ALTER TABLE `clientes` ADD COLUMN IF NOT EXISTS
    data_nascimento DATE NULL AFTER telefone;

-- origem
ALTER TABLE `clientes` ADD COLUMN IF NOT EXISTS
    origem ENUM('online','admin') NOT NULL DEFAULT 'online' AFTER data_nascimento;

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. ENDEREÇOS DOS CLIENTES
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `enderecos_clientes` (
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

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. TOKENS DE RECUPERAÇÃO DE SENHA
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tokens_senha` (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id  INT UNSIGNED  NOT NULL,
    token       VARCHAR(64)   NOT NULL UNIQUE,
    expira_em   DATETIME      NOT NULL,
    usado       TINYINT(1)    NOT NULL DEFAULT 0,
    criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. CARRINHOS
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `carrinhos` (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sessao_id     VARCHAR(64)   NOT NULL,
    cliente_id    INT UNSIGNED  NULL,
    criado_em     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sessao (sessao_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. ITENS DO CARRINHO
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `carrinho_itens` (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carrinho_id    INT UNSIGNED  NOT NULL,
    produto_id     INT UNSIGNED  NOT NULL,
    quantidade     INT           NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uk_carrinho_produto (carrinho_id, produto_id),
    FOREIGN KEY (carrinho_id) REFERENCES carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id)  REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 6. PEDIDOS
--    Criado já com estrutura final (v2.0 + v3.0 + v3.2)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pedidos` (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero              VARCHAR(20)   NOT NULL UNIQUE,
    invoice_slug        VARCHAR(100)  NULL DEFAULT NULL,
    transaction_id      VARCHAR(100)  NULL DEFAULT NULL,
    cliente_id          INT UNSIGNED  NOT NULL,
    status              ENUM(
                            'aguardando_pagamento',
                            'pagamento_expirado',
                            'pagamento_recusado',
                            'pago',
                            'separando',
                            'enviado',
                            'entregue',
                            'cancelado',
                            'pendente'
                        ) NOT NULL DEFAULT 'aguardando_pagamento',
    forma_pagamento     VARCHAR(30)   NOT NULL DEFAULT 'pendente',
    parcelas            TINYINT UNSIGNED NOT NULL DEFAULT 1,
    subtotal            DECIMAL(12,2) NOT NULL DEFAULT 0,
    frete               DECIMAL(10,2) NOT NULL DEFAULT 0,
    desconto            DECIMAL(10,2) NOT NULL DEFAULT 0,
    desconto_pix_pct    DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    total               DECIMAL(12,2) NOT NULL DEFAULT 0,
    observacoes         TEXT,
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

-- ─────────────────────────────────────────────────────────────────────────────
-- 7. ITENS DO PEDIDO
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pedido_itens` (
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

-- ─────────────────────────────────────────────────────────────────────────────
-- 8. HISTÓRICO DE STATUS DOS PEDIDOS
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pedidos_historico` (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id   INT UNSIGNED  NOT NULL,
    status      VARCHAR(50)   NOT NULL,
    observacao  TEXT,
    criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 9. PAGAMENTOS (InfinitePay)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pagamentos` (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pedido_id        INT UNSIGNED NOT NULL,
    order_nsu        VARCHAR(50)  NOT NULL,
    invoice_slug     VARCHAR(100) NULL DEFAULT NULL,
    transaction_nsu  VARCHAR(100) NULL DEFAULT NULL,
    checkout_url     TEXT         NULL DEFAULT NULL,
    receipt_url      VARCHAR(500) NULL DEFAULT NULL,
    metodo           VARCHAR(30)  NOT NULL DEFAULT 'pendente',
    parcelas         TINYINT UNSIGNED NOT NULL DEFAULT 1,
    valor_original   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_desconto   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_cobrado    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_pago       DECIMAL(10,2) NULL DEFAULT NULL,
    status           VARCHAR(30)  NOT NULL DEFAULT 'pending',
    payload_criacao  JSON         NULL DEFAULT NULL,
    payload_webhook  JSON         NULL DEFAULT NULL,
    criado_em        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    pago_em          TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_pag_pedido      (pedido_id),
    KEY idx_pag_order_nsu   (order_nsu),
    KEY idx_pag_transaction (transaction_nsu),
    KEY idx_pag_status      (status),
    CONSTRAINT fk_pagamentos_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 10. WEBHOOK LOGS (InfinitePay — auditoria completa)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `webhook_logs` (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    source          VARCHAR(50)  NOT NULL DEFAULT 'infinitepay',
    order_nsu       VARCHAR(50)  NULL DEFAULT NULL,
    pedido_id       INT UNSIGNED NULL DEFAULT NULL,
    transaction_nsu VARCHAR(100) NULL DEFAULT NULL,
    status          VARCHAR(30)  NULL DEFAULT NULL,
    payload         JSON         NULL DEFAULT NULL,
    raw_body        MEDIUMTEXT   NULL DEFAULT NULL,
    paid_amount     DECIMAL(10,2) NULL DEFAULT NULL,
    receipt_url     TEXT         NULL DEFAULT NULL,
    capture_method  VARCHAR(30)  NULL DEFAULT NULL,
    installments    TINYINT UNSIGNED NULL DEFAULT NULL,
    ip              VARCHAR(45)  NULL DEFAULT NULL,
    processado      TINYINT(1)   NOT NULL DEFAULT 0,
    erro            TEXT         NULL DEFAULT NULL,
    criado_em       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_wh_order_nsu   (order_nsu),
    KEY idx_wh_transaction (transaction_nsu),
    KEY idx_wh_criado      (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 11. CONFIGURAÇÕES DO SISTEMA
--     (tabela já existe na HostGator, mas sem o campo 'id')
--     Apenas insere chaves que podem estar faltando.
-- ─────────────────────────────────────────────────────────────────────────────
-- A tabela 'configuracoes' na HostGator usa 'chave' como PK (sem coluna id).
-- Inserção segura:
INSERT IGNORE INTO configuracoes (chave, valor, descricao) VALUES
    ('pix_ativo',           '0',    'PIX habilitado (1=sim, 0=não) — gerenciado pela InfinitePay'),
    ('cartao_ativo',        '1',    'Cartão habilitado (1=sim, 0=não) — gerenciado pela InfinitePay'),
    ('pix_desconto_pct',    '0.00', 'Percentual de desconto PIX (obsoleto na v3.2)'),
    ('cartao_max_parcelas', '3',    'Máx. parcelas no cartão (obsoleto na v3.2)');

-- ─────────────────────────────────────────────────────────────────────────────
-- 12. VENDAS — ajustes v3.3 + v5.1
--     • cliente_id / cliente_nome     (migration_v3_3)
--     • forma_pagamento → VARCHAR(30) (migration_v5_1 — aceita valores InfinitePay)
--     • pedido_id                     (migration_v5_1 — liga venda a pedido online)
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `vendas`
    MODIFY COLUMN forma_pagamento VARCHAR(30) NOT NULL DEFAULT 'outro',
    ADD COLUMN IF NOT EXISTS cliente_id   INT UNSIGNED NULL AFTER id,
    ADD COLUMN IF NOT EXISTS cliente_nome VARCHAR(150) NULL AFTER cliente_id,
    ADD COLUMN IF NOT EXISTS pedido_id    INT UNSIGNED NULL DEFAULT NULL AFTER id;

-- Índice para pedido_id
ALTER TABLE `vendas`
    ADD INDEX IF NOT EXISTS idx_vendas_pedido (pedido_id);

-- FK cliente — só adiciona se não existir
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'vendas'
      AND CONSTRAINT_NAME = 'fk_vendas_cliente'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE vendas ADD CONSTRAINT fk_vendas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL',
    'SELECT ''fk_vendas_cliente ja existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────────────────────────────────────
-- 13. PRODUTOS — dimensões para cálculo de frete (migration_v6_0)
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS peso        DECIMAL(8,3) NOT NULL DEFAULT 0.100 AFTER estoque_atual,
    ADD COLUMN IF NOT EXISTS altura      SMALLINT UNSIGNED NOT NULL DEFAULT 10  AFTER peso,
    ADD COLUMN IF NOT EXISTS largura     SMALLINT UNSIGNED NOT NULL DEFAULT 10  AFTER altura,
    ADD COLUMN IF NOT EXISTS comprimento SMALLINT UNSIGNED NOT NULL DEFAULT 15  AFTER largura;

-- ─────────────────────────────────────────────────────────────────────────────
-- 14. PEDIDOS — detalhes da modalidade de entrega (migration_v6_0)
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `pedidos`
    ADD COLUMN IF NOT EXISTS tipo_frete            VARCHAR(30)  NULL DEFAULT NULL  AFTER frete,
    ADD COLUMN IF NOT EXISTS transportadora        VARCHAR(100) NULL DEFAULT NULL  AFTER tipo_frete,
    ADD COLUMN IF NOT EXISTS prazo_entrega         VARCHAR(50)  NULL DEFAULT NULL  AFTER transportadora,
    ADD COLUMN IF NOT EXISTS codigo_transportadora INT UNSIGNED NULL DEFAULT NULL  AFTER prazo_entrega,
    ADD COLUMN IF NOT EXISTS resp_entrega_cliente  TINYINT(1)   NOT NULL DEFAULT 0 AFTER codigo_transportadora;

SELECT 'migration_hostgator_ecommerce (v2.0 → v6.0) concluida com sucesso!' AS status;
