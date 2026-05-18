-- Migration v6.7 — Handle InfinitePay em pagamentos
-- Adiciona coluna handle para rastreamento por conta/terminal InfinitePay

ALTER TABLE pagamentos
    ADD COLUMN handle VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Handle InfinitePay usado na criação do link de pagamento'
    AFTER order_nsu;

-- Índice opcional (caso queira consultar por handle futuramente)
CREATE INDEX idx_pagamentos_handle ON pagamentos (handle);
