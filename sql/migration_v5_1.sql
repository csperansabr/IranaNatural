-- =============================================================================
-- Iraná Natural — Migração v5.1
-- Integração Venda Manual ↔ Pedido Online pós-pagamento
-- Data: 2026-05-08
-- =============================================================================
-- 1. vendas.forma_pagamento: muda de ENUM rígido para VARCHAR(30) para aceitar
--    os valores retornados pela InfinitePay (credit_card, pix, etc.).
--    Os valores existentes (pix, dinheiro, debito, credito, transferencia, outro)
--    continuam válidos — nenhum dado é perdido.
--
-- 2. vendas.pedido_id: FK opcional que liga a venda a um pedido online.
--    NULL para vendas manuais; preenchido quando a venda é gerada pelo webhook.
-- =============================================================================

-- 1. Converter forma_pagamento para VARCHAR
ALTER TABLE vendas
    MODIFY COLUMN forma_pagamento VARCHAR(30) NOT NULL DEFAULT 'outro';

-- 2. Adicionar pedido_id (rastreio da origem do pedido online)
ALTER TABLE vendas
    ADD COLUMN IF NOT EXISTS pedido_id INT UNSIGNED NULL DEFAULT NULL
        COMMENT 'ID do pedido online que originou esta venda (NULL para vendas manuais)'
        AFTER id;

-- 3. Índice para consulta por pedido_id
ALTER TABLE vendas
    ADD INDEX IF NOT EXISTS idx_vendas_pedido (pedido_id);

SELECT 'migration_v5_1 aplicada — vendas.forma_pagamento=VARCHAR(30), vendas.pedido_id adicionado' AS status;
