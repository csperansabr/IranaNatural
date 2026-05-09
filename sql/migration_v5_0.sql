-- =============================================================================
-- Iraná Natural — Migração v5.0
-- Recibo InfinitePay na tabela pagamentos
-- Data: 2026-05-08
-- =============================================================================
-- Adiciona receipt_url na tabela pagamentos para persistir o link do recibo
-- retornado pelo webhook InfinitePay na aprovação do pagamento.
-- O campo já existe em webhook_logs (migration_v3_1); esta coluna espelha o
-- valor no registro de pagamento para acesso direto sem JOIN em webhook_logs.
-- =============================================================================

ALTER TABLE pagamentos
    ADD COLUMN IF NOT EXISTS receipt_url VARCHAR(500) NULL DEFAULT NULL
        COMMENT 'Link do recibo retornado pela InfinitePay no webhook de aprovação'
        AFTER checkout_url;

SELECT 'migration_v5_0 aplicada — receipt_url adicionada a pagamentos' AS status;
