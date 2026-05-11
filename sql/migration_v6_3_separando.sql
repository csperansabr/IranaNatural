-- =============================================================================
-- Iraná Natural — Migração v6.3
-- Transição automática pago → separando após confirmação InfinitePay
-- Data: 2026-05-11
-- Compatível com MariaDB 10+ e MySQL 8+
-- =============================================================================
-- Adiciona à tabela pedidos:
--   email_separando_enviado TINYINT(1) DEFAULT 0
--   Flag de idempotência: garante envio único do e-mail "Em Separação"
--   independente de webhooks duplicados da InfinitePay.
-- =============================================================================

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

SELECT 'migration_v6_3 aplicada — email_separando_enviado em pedidos' AS status;
