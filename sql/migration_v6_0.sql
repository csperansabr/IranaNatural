-- =============================================================================
-- Iraná Natural — Migração v6.0
-- Integração de Frete (Melhor Envio + opções locais)
-- Data: 2026-05-09
-- =============================================================================
-- 1. produtos: dimensões para cálculo de frete
-- 2. pedidos: detalhes da modalidade de entrega selecionada
-- =============================================================================

-- 1. Dimensões do produto para cálculo de frete
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS peso       DECIMAL(8,3) NOT NULL DEFAULT 0.100
        COMMENT 'Peso em kg (ex: 0.300 = 300g)' AFTER estoque_atual,
    ADD COLUMN IF NOT EXISTS altura     SMALLINT UNSIGNED NOT NULL DEFAULT 10
        COMMENT 'Altura em cm' AFTER peso,
    ADD COLUMN IF NOT EXISTS largura    SMALLINT UNSIGNED NOT NULL DEFAULT 10
        COMMENT 'Largura em cm' AFTER altura,
    ADD COLUMN IF NOT EXISTS comprimento SMALLINT UNSIGNED NOT NULL DEFAULT 15
        COMMENT 'Comprimento/profundidade em cm' AFTER largura;

-- 2. Modalidade de entrega escolhida no checkout
ALTER TABLE `pedidos`
    ADD COLUMN IF NOT EXISTS tipo_frete           VARCHAR(30)  NULL DEFAULT NULL
        COMMENT 'ID interno da opção de frete (ex: pac, sedex, retirada, uber, motoboy)' AFTER frete,
    ADD COLUMN IF NOT EXISTS transportadora       VARCHAR(100) NULL DEFAULT NULL
        COMMENT 'Nome da transportadora (ex: Correios, Jadlog, Local)' AFTER tipo_frete,
    ADD COLUMN IF NOT EXISTS prazo_entrega        VARCHAR(50)  NULL DEFAULT NULL
        COMMENT 'Prazo estimado (ex: 5 dias úteis)' AFTER transportadora,
    ADD COLUMN IF NOT EXISTS codigo_transportadora INT UNSIGNED NULL DEFAULT NULL
        COMMENT 'ID do serviço na API Melhor Envio (NULL para opções locais)' AFTER prazo_entrega,
    ADD COLUMN IF NOT EXISTS resp_entrega_cliente TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 = cliente contrata a entrega (Uber/Motoboy)' AFTER codigo_transportadora;

SELECT 'migration_v6_0 aplicada — dimensoes em produtos, detalhes de frete em pedidos' AS status;
