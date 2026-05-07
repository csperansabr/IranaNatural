<?php
/**
 * Iraná Natural — Migration v1.2
 * Adiciona SKU em produtos + tabelas de histórico de importação.
 * Execute: https://irananatural.com.br/setup/migrate_v1_2.php
 * APAGUE este arquivo imediatamente após o uso!
 */

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';
require_once ROOT . '/app/Core/Database.php';
require_once ROOT . '/app/Core/Session.php';

use App\Core\Database;
use App\Core\Session;

Session::start();

// Verificar autenticação admin
if (!isset($_SESSION[ADMIN_SESSION])) {
    header('Location: /admin/login');
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Migration v1.2 — Iraná Natural</title>
<style>
body { font-family: sans-serif; max-width: 720px; margin: 3rem auto; padding: 1rem; background: #f5f5f5; }
.box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
h1 { color: #2C5F2E; margin-top: 0; }
h3 { color: #2C5F2E; border-bottom: 1px solid #e0e0e0; padding-bottom: .4rem; }
.passo { margin: .5rem 0; padding: .6rem 1rem; border-radius: 4px; display: flex; align-items: center; gap: .6rem; font-size: 15px; }
.passo.ok  { background: #F0FFF4; border-left: 4px solid #2C5F2E; }
.passo.skip { background: #FFFBEB; border-left: 4px solid #D69E2E; }
.passo.err { background: #FFF5F5; border-left: 4px solid #E53E3E; }
.passo .icon { font-size: 1.2rem; }
.passo .msg  { flex: 1; }
.passo .detalhe { font-size: 12px; color: #718096; margin-top: 2px; }
.alerta { background: #FFF3CD; border: 1px solid #FFEAA7; padding: 1rem 1.2rem; border-radius: 4px; margin-top: 1.5rem; }
.alerta strong { color: #7D5A00; }
.voltar { display: inline-block; margin-top: 1.5rem; padding: .6rem 1.4rem; background: #2C5F2E; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }
.voltar:hover { background: #1e4520; }
hr { border: none; border-top: 1px solid #e0e0e0; margin: 1.5rem 0; }
</style>
</head>
<body>
<div class="box">
<h1>🌿 Migration v1.2 — Iraná Natural</h1>
<p>Aplicando alterações de banco de dados para a versão <strong>1.2</strong>.</p>

<?php

$db = Database::getInstance();

$passos = [
    [
        'label' => 'Adicionar coluna <code>sku</code> em <code>produtos</code>',
        'sql'   => "ALTER TABLE produtos ADD COLUMN sku VARCHAR(100) NULL UNIQUE AFTER slug",
    ],
    [
        'label' => 'Criar tabela <code>import_history</code>',
        'sql'   => "CREATE TABLE IF NOT EXISTS import_history (
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
    [
        'label' => 'Criar tabela <code>import_errors</code>',
        'sql'   => "CREATE TABLE IF NOT EXISTS import_errors (
                        id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        import_id INT UNSIGNED NOT NULL,
                        linha     INT NOT NULL,
                        campo     VARCHAR(100),
                        valor     TEXT,
                        mensagem  TEXT NOT NULL,
                        FOREIGN KEY (import_id) REFERENCES import_history(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
];

$totalOk   = 0;
$totalSkip = 0;
$totalErr  = 0;

echo '<h3>Executando passos</h3>';

foreach ($passos as $idx => $passo) {
    $num = $idx + 1;
    try {
        $db->exec($passo['sql']);
        echo "<div class='passo ok'><span class='icon'>✅</span><div class='msg'><strong>Passo {$num}:</strong> {$passo['label']} — <em>executado com sucesso</em></div></div>";
        $totalOk++;
    } catch (\PDOException $e) {
        $code = $e->getCode();
        $msg  = $e->getMessage();

        // 42S21 = Column already exists | 1060 = Duplicate column name
        $isDuplicate = ($code === '42S21')
            || str_contains($msg, 'Duplicate column name')
            || str_contains($msg, 'already exists')
            || $code === '42S01'; // Table already exists

        if ($isDuplicate) {
            echo "<div class='passo skip'><span class='icon'>⏭️</span><div class='msg'><strong>Passo {$num}:</strong> {$passo['label']} — <em>já existe, ignorado</em></div></div>";
            $totalSkip++;
        } else {
            echo "<div class='passo err'><span class='icon'>❌</span><div class='msg'><strong>Passo {$num}:</strong> {$passo['label']} — <em>erro</em><div class='detalhe'>" . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
            $totalErr++;
        }
    }
}

echo '<hr>';

if ($totalErr === 0) {
    echo "<p style='color:#2C5F2E;font-size:1.1rem'><strong>✅ Migration concluída!</strong> {$totalOk} passo(s) aplicado(s), {$totalSkip} ignorado(s).</p>";
} else {
    echo "<p style='color:#E53E3E;font-size:1.1rem'><strong>⚠️ Migration concluída com {$totalErr} erro(s).</strong> Verifique os detalhes acima.</p>";
}
?>

<div class="alerta">
    <strong>⚠️ IMPORTANTE:</strong> Apague o arquivo <code>setup/migrate_v1_2.php</code> imediatamente após confirmar o resultado!
</div>

<a href="/admin/importacao" class="voltar">← Ir para Importação de Dados</a>

</div>
</body>
</html>
