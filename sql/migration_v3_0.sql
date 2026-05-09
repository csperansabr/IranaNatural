-- =============================================================
-- Migration v3.0 — InfinitePay Integration + PIX Discount
-- Execute AFTER migration_v2_0.sql
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Extend pedidos table ──────────────────────────────────────────
ALTER TABLE pedidos
  MODIFY COLUMN status ENUM(
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
  ADD COLUMN parcelas         TINYINT UNSIGNED  NOT NULL DEFAULT 1    COMMENT 'Nº parcelas no cartão'     AFTER forma_pagamento,
  ADD COLUMN desconto_pix_pct DECIMAL(5,2)      NOT NULL DEFAULT 0.00 COMMENT '% desconto PIX aplicado'   AFTER desconto,
  ADD COLUMN invoice_slug     VARCHAR(100)      NULL DEFAULT NULL      COMMENT 'InfinitePay invoice_slug'  AFTER numero,
  ADD COLUMN transaction_id   VARCHAR(100)      NULL DEFAULT NULL      COMMENT 'InfinitePay transaction_nsu' AFTER invoice_slug;

-- ── pagamentos: InfinitePay transaction records ───────────────────
CREATE TABLE IF NOT EXISTS pagamentos (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pedido_id        INT UNSIGNED NOT NULL,
  order_nsu        VARCHAR(50)  NOT NULL COMMENT 'Número do pedido usado como order_nsu',
  invoice_slug     VARCHAR(100) NULL DEFAULT NULL,
  transaction_nsu  VARCHAR(100) NULL DEFAULT NULL,
  checkout_url     TEXT         NULL DEFAULT NULL,
  metodo           ENUM('pix','cartao_credito') NOT NULL,
  parcelas         TINYINT UNSIGNED NOT NULL DEFAULT 1,
  valor_original   DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal sem desconto',
  valor_desconto   DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor do desconto PIX',
  valor_cobrado    DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor enviado à InfinitePay',
  valor_pago       DECIMAL(10,2) NULL DEFAULT NULL     COMMENT 'Confirmado no webhook',
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

-- ── webhook_logs: full audit trail of InfinitePay notifications ───
CREATE TABLE IF NOT EXISTS webhook_logs (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  source          VARCHAR(50)  NOT NULL DEFAULT 'infinitepay',
  order_nsu       VARCHAR(50)  NULL DEFAULT NULL,
  pedido_id       INT UNSIGNED NULL DEFAULT NULL,
  transaction_nsu VARCHAR(100) NULL DEFAULT NULL,
  status          VARCHAR(30)  NULL DEFAULT NULL,
  payload         JSON         NULL DEFAULT NULL,
  ip              VARCHAR(45)  NULL DEFAULT NULL,
  processado      TINYINT(1)   NOT NULL DEFAULT 0,
  erro            TEXT         NULL DEFAULT NULL,
  criado_em       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_wh_order_nsu   (order_nsu),
  KEY idx_wh_transaction (transaction_nsu),
  KEY idx_wh_criado      (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── configuracoes: runtime business settings editable by admin ────
CREATE TABLE IF NOT EXISTS configuracoes (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  chave     VARCHAR(100) NOT NULL,
  valor     TEXT         NULL DEFAULT NULL,
  descricao VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_config_chave (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracoes (chave, valor, descricao) VALUES
  ('pix_ativo',           '1',    'PIX habilitado (1=sim, 0=não)'),
  ('cartao_ativo',        '1',    'Cartão de crédito habilitado (1=sim, 0=não)'),
  ('pix_desconto_pct',    '5.00', 'Percentual de desconto para pagamento via PIX'),
  ('cartao_max_parcelas', '3',    'Número máximo de parcelas no cartão (1-12)')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);

SET FOREIGN_KEY_CHECKS = 1;
