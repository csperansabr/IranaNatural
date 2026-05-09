-- Migration v3.3 — Customer birthday, origin tracking, and sales customer linking
-- Run once on all environments (local + production)

-- 1. clientes: allow nullable cpf/email/senha (admin-created customers may lack these),
--    add data_nascimento and origem to track birthday and registration channel.
ALTER TABLE clientes
    MODIFY COLUMN cpf   VARCHAR(14)  NULL,
    MODIFY COLUMN email VARCHAR(150) NULL,
    MODIFY COLUMN senha VARCHAR(255) NULL,
    ADD COLUMN data_nascimento DATE NULL          AFTER telefone,
    ADD COLUMN origem ENUM('online','admin') NOT NULL DEFAULT 'online' AFTER data_nascimento;

-- 2. vendas: link sale to a customer record (nullable) and cache the customer name
--    as a denormalized field so reports don't require JOINs on old data.
ALTER TABLE vendas
    ADD COLUMN cliente_id   INT UNSIGNED NULL     AFTER id,
    ADD COLUMN cliente_nome VARCHAR(150) NULL     AFTER cliente_id,
    ADD CONSTRAINT fk_vendas_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL;
