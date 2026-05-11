-- =============================================================================
-- Iraná Natural — Migração v6.1
-- Sistema de e-mails automáticos (pedido criado + pagamento confirmado)
-- Data: 2026-05-11
-- Compatível com MariaDB 10+ e MySQL 8+
-- =============================================================================
-- 1. pedidos: flags de controle de envio de e-mail (idempotência)
-- 2. email_logs: auditoria de todos os envios de e-mail
-- =============================================================================

-- ── Adicionar colunas em pedidos (portável: procedure + dynamic SQL) ──────────
DROP PROCEDURE IF EXISTS _add_email_cols;
DELIMITER //
CREATE PROCEDURE _add_email_cols()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos'
          AND COLUMN_NAME = 'email_pedido_enviado'
    ) THEN
        ALTER TABLE pedidos ADD COLUMN email_pedido_enviado TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'E-mail de confirmação de criação do pedido enviado ao cliente';
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos'
          AND COLUMN_NAME = 'email_pago_enviado'
    ) THEN
        ALTER TABLE pedidos ADD COLUMN email_pago_enviado TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'E-mail de confirmação de pagamento enviado ao cliente';
    END IF;
END //
DELIMITER ;
CALL _add_email_cols();
DROP PROCEDURE IF EXISTS _add_email_cols;

-- ── Tabela de log de envio de e-mails ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `email_logs` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `tipo`         VARCHAR(50)      NOT NULL COMMENT 'pedido_criado_cliente | pedido_criado_loja | pago_cliente',
    `pedido_id`    INT UNSIGNED     NOT NULL DEFAULT 0 COMMENT '0 para e-mails não vinculados a pedido',
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

SELECT 'migration_v6_1 aplicada — email_pedido_enviado/email_pago_enviado em pedidos, tabela email_logs criada' AS status;
