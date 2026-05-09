-- Migration v3.1 — Auditoria de Webhook: raw_body + campos extraídos
-- Aplicar em: local (irananatural.test) e produção (irananatural.com.br)
-- Data: 2026-05-08

ALTER TABLE webhook_logs
    ADD COLUMN raw_body      MEDIUMTEXT   NULL AFTER payload,
    ADD COLUMN paid_amount   DECIMAL(10,2) NULL AFTER raw_body,
    ADD COLUMN receipt_url   TEXT          NULL AFTER paid_amount,
    ADD COLUMN capture_method VARCHAR(30)  NULL AFTER receipt_url,
    ADD COLUMN installments  TINYINT UNSIGNED NULL AFTER capture_method;
