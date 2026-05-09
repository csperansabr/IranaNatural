-- Migration v3.2 — Remove PIX discount and payment method selection
-- Run once on all environments (local + production)

-- 1. Pedidos: change forma_pagamento to VARCHAR(30) to allow 'pendente' and
--    any future capture_method value (credit_card, debit_card, pix, etc.)
ALTER TABLE pedidos
    MODIFY COLUMN forma_pagamento VARCHAR(30) NOT NULL DEFAULT 'pendente';

-- 2. Pagamentos: change metodo column to VARCHAR(30) to allow 'pendente' and
--    any InfinitePay capture_method value.
ALTER TABLE pagamentos
    MODIFY COLUMN metodo VARCHAR(30) NOT NULL DEFAULT 'pendente';

-- 3. Remove obsolete config keys that controlled PIX discount and card
--    instalments — payment method selection now happens at InfinitePay.
DELETE FROM configuracoes
WHERE chave IN ('pix_ativo', 'cartao_ativo', 'pix_desconto_pct', 'cartao_max_parcelas');
