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

-- ─────────────────────────────────────────────────────────────────────────────
-- 15. CONFIGURAÇÕES — seeds para o módulo de frete (migration_v6_0)
-- ─────────────────────────────────────────────────────────────────────────────
INSERT IGNORE INTO configuracoes (chave, valor, descricao) VALUES
    ('frete_ativo',          '1',        'Cálculo de frete via Melhor Envio habilitado (1=sim, 0=não)'),
    ('frete_sandbox',        '1',        'Ambiente Melhor Envio: 1=sandbox (testes), 0=produção'),
    ('frete_cep_origem',     '92110060', 'CEP de origem da loja (8 dígitos, sem hífen)'),
    ('frete_timeout',        '15',       'Timeout da API Melhor Envio em segundos'),
    ('frete_services',       '1,2,9,10', 'IDs dos serviços ME habilitados: 1=PAC,2=SEDEX,9=Jadlog.Package,10=Jadlog.Com'),
    ('frete_token_sandbox',  '',         'Token JWT Melhor Envio — ambiente sandbox (sandbox.melhorenvio.com.br)'),
    ('frete_token_producao', '',         'Token JWT Melhor Envio — ambiente produção (app.melhorenvio.com.br)'),
    ('frete_ssl_verify',    '1',        'Verificar SSL nas chamadas à API Melhor Envio (0=desativar em ambiente local sem CA bundle)');

-- ─────────────────────────────────────────────────────────────────────────────
-- 16. TOKENS DE RECUPERAÇÃO DE SENHA — painel admin (migration_v7_0)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tokens_senha_admin` (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED NOT NULL,
    token       VARCHAR(64)  NOT NULL,
    expira_em   DATETIME     NOT NULL,
    usado       TINYINT(1)   NOT NULL DEFAULT 0,
    criado_em   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY  uk_token     (token),
    INDEX       idx_usuario  (usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tokens temporários para recuperação de senha do painel administrativo';

-- ─────────────────────────────────────────────────────────────────────────────
-- 17. PEDIDOS — flags de controle de e-mail (migration_v6_1)
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `pedidos`
    ADD COLUMN IF NOT EXISTS email_pedido_enviado TINYINT(1) NOT NULL DEFAULT 0
        COMMENT 'E-mail de confirmação de criação do pedido enviado ao cliente' AFTER resp_entrega_cliente,
    ADD COLUMN IF NOT EXISTS email_pago_enviado   TINYINT(1) NOT NULL DEFAULT 0
        COMMENT 'E-mail de confirmação de pagamento enviado ao cliente' AFTER email_pedido_enviado;

-- ─────────────────────────────────────────────────────────────────────────────
-- 18. EMAIL_LOGS — auditoria de envios de e-mail (migration_v6_1)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `email_logs` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `tipo`         VARCHAR(50)      NOT NULL COMMENT 'pedido_criado_cliente | pedido_criado_loja | pago_cliente',
    `pedido_id`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `destinatario` VARCHAR(255)     NOT NULL,
    `assunto`      VARCHAR(255)     NOT NULL,
    `status`       ENUM('enviado','falhou') NOT NULL DEFAULT 'enviado',
    `erro`         TEXT             NULL,
    `criado_em`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_el_pedido` (`pedido_id`),
    INDEX `idx_el_tipo`   (`tipo`),
    INDEX `idx_el_status` (`status`),
    INDEX `idx_el_data`   (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 19. PEDIDOS_HISTORICO — campos de auditoria estruturada (migration_v6_2)
-- ─────────────────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS _add_historico_v62_cols;
DELIMITER //
CREATE PROCEDURE _add_historico_v62_cols()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'pedidos_historico'
          AND COLUMN_NAME  = 'status_anterior'
    ) THEN
        ALTER TABLE `pedidos_historico`
            ADD COLUMN `status_anterior` VARCHAR(50)  NULL    AFTER `status`,
            ADD COLUMN `admin_id`        INT UNSIGNED NULL    AFTER `observacao`,
            ADD COLUMN `admin_nome`      VARCHAR(100) NULL    AFTER `admin_id`,
            ADD COLUMN `origem`          ENUM('sistema','admin','webhook') NOT NULL DEFAULT 'sistema' AFTER `admin_nome`;
    END IF;
END //
DELIMITER ;
CALL _add_historico_v62_cols();
DROP PROCEDURE IF EXISTS _add_historico_v62_cols;

-- ─────────────────────────────────────────────────────────────────────────────
-- 20. PEDIDOS — flag idempotência e-mail Em Separação (migration_v6_3)
-- ─────────────────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS _add_email_separando_col;
DELIMITER //
CREATE PROCEDURE _add_email_separando_col()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'pedidos'
          AND COLUMN_NAME  = 'email_separando_enviado'
    ) THEN
        ALTER TABLE `pedidos`
            ADD COLUMN `email_separando_enviado` TINYINT(1) NOT NULL DEFAULT 0
                COMMENT 'E-mail automático de Em Separação enviado ao cliente';
    END IF;
END //
DELIMITER ;
CALL _add_email_separando_col();
DROP PROCEDURE IF EXISTS _add_email_separando_col;

SELECT 'migration_hostgator_ecommerce (v2.0 → v10.0) concluida com sucesso!' AS status;
