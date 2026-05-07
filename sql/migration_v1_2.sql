-- =====================================================
-- Iraná Natural — Migration v1.2
-- Adiciona SKU em produtos + tabelas de histórico de importação
-- =====================================================

-- Adiciona coluna sku em produtos (pode já existir em instalações novas)
ALTER TABLE produtos ADD COLUMN sku VARCHAR(100) NULL UNIQUE AFTER slug;

-- Histórico de importações
CREATE TABLE IF NOT EXISTS import_history (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entidade     ENUM('produtos','insumos','estoque') NOT NULL,
    modo         ENUM('criar','atualizar','criar_atualizar') NOT NULL DEFAULT 'criar_atualizar',
    arquivo_nome VARCHAR(255),
    total_linhas INT NOT NULL DEFAULT 0,
    inseridos    INT NOT NULL DEFAULT 0,
    atualizados  INT NOT NULL DEFAULT 0,
    erros        INT NOT NULL DEFAULT 0,
    ignorados    INT NOT NULL DEFAULT 0,
    usuario_id   INT UNSIGNED NULL,
    criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Erros detalhados por importação
CREATE TABLE IF NOT EXISTS import_errors (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    import_id INT UNSIGNED NOT NULL,
    linha     INT NOT NULL,
    campo     VARCHAR(100),
    valor     TEXT,
    mensagem  TEXT NOT NULL,
    FOREIGN KEY (import_id) REFERENCES import_history(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
