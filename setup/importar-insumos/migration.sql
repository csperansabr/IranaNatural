-- =============================================================================
-- Iraná Natural — Migration: Suporte a Importação de Insumos
-- =============================================================================
-- Este SQL é executado automaticamente pelo importador.php.
-- Use este arquivo apenas para referência ou para rodar manualmente via
-- phpMyAdmin / MySQL Workbench ANTES de executar o script.
--
-- Compatível com MariaDB 10.0+ e MySQL 8.0+.
-- Em MySQL 5.7, remova as cláusulas IF NOT EXISTS dos ALTER TABLE.
-- =============================================================================

SET NAMES utf8mb4;

-- ─── 1. Tabela de categorias de insumos ──────────────────────────────────────

CREATE TABLE IF NOT EXISTS `categorias_insumos` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`      VARCHAR(100) NOT NULL,
    `criado_em` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cat_insumos_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. Novas colunas em insumos ─────────────────────────────────────────────
-- MariaDB 10.0+ suporta ADD COLUMN IF NOT EXISTS
-- MySQL 8.0+ também suporta

ALTER TABLE `insumos`
    ADD COLUMN IF NOT EXISTS `tipo_id`          INT UNSIGNED NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `observacoes`      TEXT         NULL AFTER `fornecedor`,
    ADD COLUMN IF NOT EXISTS `data_conferencia` DATE         NULL AFTER `observacoes`;

-- ─── 3. Chave estrangeira tipo_id → categorias_insumos ───────────────────────
-- Só adicionar se ainda não existir. Em MariaDB 10.0+ pode usar IF NOT EXISTS.

ALTER TABLE `insumos`
    ADD CONSTRAINT IF NOT EXISTS `fk_insumos_tipo_id`
        FOREIGN KEY (`tipo_id`) REFERENCES `categorias_insumos`(`id`) ON DELETE SET NULL;

-- =============================================================================
-- Resultado esperado em insumos:
--   id | tipo_id | nome | descricao | unidade_medida | estoque_atual |
--   estoque_minimo | custo_medio | fornecedor | observacoes |
--   data_conferencia | ativo | criado_em | atualizado_em
-- =============================================================================
