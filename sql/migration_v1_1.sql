-- =====================================================
-- Iraná Natural — Migration v1.1
-- Adiciona campos de SEO e tags à tabela produtos
-- Execute uma vez em instalações existentes
-- =====================================================

ALTER TABLE produtos
    ADD COLUMN seo_titulo    VARCHAR(70)  NULL AFTER cuidados,
    ADD COLUMN seo_descricao VARCHAR(160) NULL AFTER seo_titulo,
    ADD COLUMN tags          TEXT         NULL AFTER seo_descricao;
