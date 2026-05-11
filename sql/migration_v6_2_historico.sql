-- =============================================================================
-- Iraná Natural — Migração v6.2
-- Histórico de status dos pedidos — campos estruturados de auditoria
-- Data: 2026-05-11
-- Compatível com MariaDB 10+ e MySQL 8+
-- =============================================================================
-- Adiciona à tabela pedidos_historico (existente desde v2.0):
--   status_anterior  VARCHAR(50)  NULL  — status antes da alteração
--   admin_id         INT UNSIGNED NULL  — id do admin responsável (NULL = sistema/webhook)
--   admin_nome       VARCHAR(100) NULL  — nome do admin no momento da alteração
--   origem           ENUM(...)         — quem originou: sistema | admin | webhook
-- =============================================================================

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

SELECT 'migration_v6_2 aplicada — status_anterior, admin_id, admin_nome, origem em pedidos_historico' AS status;
