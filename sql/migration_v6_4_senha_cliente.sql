-- ============================================================
-- Migration v6.4 — Auditoria de alteração de senha (cliente)
-- Adiciona coluna para registrar data/hora da última troca de senha
-- Execute: uma única vez no banco de produção (HostGator) e local
-- ============================================================

ALTER TABLE clientes
    ADD COLUMN senha_alterada_em TIMESTAMP NULL DEFAULT NULL
        COMMENT 'Data/hora da última alteração de senha pelo próprio cliente'
        AFTER senha;
