<?php
/**
 * Iraná Natural — Migration v1.1
 * Adiciona campos SEO e tags à tabela produtos.
 *
 * ACESSO: /setup/migrate_v1_1.php  (requer sessão admin ativa)
 * APAGUE este arquivo após confirmar a execução bem-sucedida.
 */

define('ROOT', dirname(__DIR__));

require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';
require_once ROOT . '/app/Core/Database.php';
require_once ROOT . '/app/Core/Session.php';

use App\Core\Database;
use App\Core\Session;

Session::start();

// ── Autenticação ──────────────────────────────────────────────────────────────
if (!Session::has(ADMIN_SESSION)) {
    header('Location: /admin/login');
    exit;
}

$adminUser = Session::get(ADMIN_SESSION);

// ── HTML header ───────────────────────────────────────────────────────────────
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Migration v1.1 — <?= APP_NAME ?></title>
<style>
  *{box-sizing:border-box}
  body{font-family:system-ui,sans-serif;background:#F5EFE3;margin:0;padding:2rem 1rem;color:#2D3748}
  .box{background:#fff;max-width:720px;margin:0 auto;padding:2rem;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.08)}
  h1{color:#2C5F2E;margin:0 0 .25rem}
  .sub{color:#718096;font-size:.9rem;margin-bottom:1.5rem}
  .user-badge{display:inline-block;background:#EBF8EE;color:#276749;border:1px solid #C6F6D5;border-radius:4px;padding:.2rem .75rem;font-size:.82rem;margin-bottom:1.5rem}
  h3{margin:1.5rem 0 .5rem;font-size:1rem;color:#4A5568}
  .step{border:1px solid #E2E8F0;border-radius:6px;padding:.75rem 1rem;margin-bottom:.5rem;display:flex;align-items:center;gap:.75rem}
  .step-icon{font-size:1.2rem;flex-shrink:0}
  .step-label{flex:1;font-size:.9rem}
  .step-info{font-size:.78rem;color:#718096}
  .ok   {border-color:#C6F6D5;background:#F0FFF4}
  .skip {border-color:#BEE3F8;background:#EBF8FF}
  .err  {border-color:#FEB2B2;background:#FFF5F5}
  .done-box{background:#F0FFF4;border:1px solid #9AE6B4;border-radius:8px;padding:1.25rem 1.5rem;margin-top:1.5rem}
  .done-box h2{color:#276749;margin:0 0 .5rem}
  .warn-box{background:#FFFBEB;border:1px solid #F6E05E;border-radius:8px;padding:1rem 1.25rem;margin-top:1rem;font-size:.88rem}
  .warn-box strong{color:#B7791F}
  .err-box{background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:1.25rem 1.5rem;margin-top:1.5rem}
  .err-box h2{color:#C53030;margin:0 0 .5rem}
  a.btn{display:inline-block;background:#2C5F2E;color:#fff;text-decoration:none;padding:.55rem 1.25rem;border-radius:5px;font-size:.9rem;margin-top:1rem}
  a.btn:hover{background:#276749}
  code{background:#EDF2F7;padding:1px 5px;border-radius:3px;font-size:.85rem}
</style>
</head>
<body>
<div class="box">

<h1>🌿 Migration v1.1</h1>
<p class="sub">Produtos — campos SEO e tags</p>
<span class="user-badge">👤 <?= htmlspecialchars($adminUser['nome'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>

<?php

// ── Passos da migration ───────────────────────────────────────────────────────
// Cada passo: [sql, label, coluna_alvo] — coluna_alvo para checar duplicata (erro 1060)
$steps = [
    [
        "ALTER TABLE produtos ADD COLUMN seo_titulo VARCHAR(70) NULL AFTER cuidados",
        "produtos.seo_titulo <code>VARCHAR(70)</code>",
    ],
    [
        "ALTER TABLE produtos ADD COLUMN seo_descricao VARCHAR(160) NULL AFTER seo_titulo",
        "produtos.seo_descricao <code>VARCHAR(160)</code>",
    ],
    [
        "ALTER TABLE produtos ADD COLUMN tags TEXT NULL AFTER seo_descricao",
        "produtos.tags <code>TEXT</code>",
    ],
];

$erros  = 0;
$feitos = 0;
$pulados = 0;

try {
    $db = Database::getInstance();
    echo '<h3>Aplicando alterações...</h3>';

    foreach ($steps as [$sql, $label]) {
        try {
            $db->exec($sql);
            echo '<div class="step ok">
                    <span class="step-icon">✅</span>
                    <span class="step-label">Adicionado: ' . $label . '</span>
                  </div>';
            $feitos++;
        } catch (\PDOException $e) {
            // Código 1060 = Duplicate column name (coluna já existe)
            if ((int)$e->getCode() === '42S21' || str_contains($e->getMessage(), 'Duplicate column name')) {
                echo '<div class="step skip">
                        <span class="step-icon">⏭️</span>
                        <span class="step-label">Já existe: ' . $label . '</span>
                        <span class="step-info">nenhuma ação necessária</span>
                      </div>';
                $pulados++;
            } else {
                echo '<div class="step err">
                        <span class="step-icon">❌</span>
                        <span class="step-label">Erro: ' . $label . '</span>
                        <span class="step-info">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</span>
                      </div>';
                $erros++;
            }
        }
    }

} catch (\Exception $e) {
    echo '<div class="err-box"><h2>Erro de conexão</h2><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p></div>';
    echo '</div></body></html>';
    exit;
}

// ── Resultado final ───────────────────────────────────────────────────────────
if ($erros === 0):
?>
<div class="done-box">
    <h2>✅ Migration concluída</h2>
    <p>
        <?= $feitos ?> coluna<?= $feitos !== 1 ? 's' : '' ?> adicionada<?= $feitos !== 1 ? 's' : '' ?><?php if ($pulados): ?>,
        <?= $pulados ?> já existia<?= $pulados !== 1 ? 'm' : '' ?> (ignorada<?= $pulados !== 1 ? 's' : '' ?>)<?php endif; ?>.
    </p>
    <a class="btn" href="/admin/produtos">← Ir para Produtos</a>
</div>
<?php else: ?>
<div class="err-box">
    <h2>⚠️ Concluído com erros</h2>
    <p><?= $erros ?> passo<?= $erros !== 1 ? 's' : '' ?> falhou. Verifique as mensagens acima e confira as permissões do usuário do banco.</p>
</div>
<?php endif; ?>

<div class="warn-box">
    <strong>⚠️ Segurança:</strong> apague o arquivo
    <code>setup/migrate_v1_1.php</code> imediatamente após confirmar o sucesso.
</div>

</div><!-- .box -->
</body>
</html>
