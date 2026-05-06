<?php
/**
 * Iraná Natural — Script de Instalação
 * Execute uma vez: https://irananatural.com.br/setup/install.php
 * APAGUE este arquivo imediatamente após a instalação!
 */

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/database.php';
require_once ROOT . '/app/Core/Database.php';

use App\Core\Database;

header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Instalação — Iraná Natural</title>
<style>body{font-family:sans-serif;max-width:700px;margin:3rem auto;padding:1rem;background:#f5f5f5}
.ok{color:#2C5F2E}.err{color:#E53E3E}.box{background:white;padding:2rem;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
h1{color:#2C5F2E}pre{background:#f0f0f0;padding:1rem;border-radius:4px;overflow-x:auto;font-size:13px}
.alerta{background:#FFF3CD;border:1px solid #FFEAA7;padding:1rem;border-radius:4px;margin-top:1rem}
</style></head><body><div class="box"><h1>🌿 Iraná Natural — Instalação</h1>';

$erros  = [];
$passos = [];

try {
    $db = Database::getInstance();

    // 1. Criar tabelas
    echo '<h3>1. Verificando banco de dados...</h3>';
    $schemaFile = ROOT . '/sql/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Arquivo sql/schema.sql não encontrado.");
    }

    $sql    = file_get_contents($schemaFile);
    $stmts  = array_filter(array_map('trim', explode(';', $sql)));
    $criadas = 0;
    foreach ($stmts as $stmt) {
        if (empty($stmt) || str_starts_with(ltrim($stmt), '--') || str_starts_with(ltrim($stmt), 'SET')) continue;
        try {
            $db->exec($stmt);
            $criadas++;
        } catch (\PDOException $e) {
            // Tabela já existe — ok
        }
    }
    echo "<p class='ok'>✓ Tabelas verificadas/criadas ($criadas instruções executadas).</p>";

    // 2. Criar usuário admin
    echo '<h3>2. Criando usuário administrador...</h3>';
    $senhaHash = password_hash('Iran@2024', PASSWORD_BCRYPT);
    $stmt      = $db->prepare(
        "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE nome = VALUES(nome), senha = VALUES(senha)"
    );
    $stmt->execute(['Administrador', 'admin@irananatural.com.br', $senhaHash]);
    echo "<p class='ok'>✓ Usuário criado:</p>
          <pre>E-mail: admin@irananatural.com.br\nSenha:  Iran@2024</pre>";

    // 3. Seed (opcional — só executa se não houver categorias)
    echo '<h3>3. Dados de demonstração...</h3>';
    $temCats = $db->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    if ($temCats == 0) {
        $seedFile = ROOT . '/sql/seed.sql';
        if (file_exists($seedFile)) {
            $seedSql  = file_get_contents($seedFile);
            $seedStmts= array_filter(array_map('trim', explode(';', $seedSql)));
            $seedOk   = 0;
            foreach ($seedStmts as $s) {
                if (empty($s) || str_starts_with(ltrim($s), '--')) continue;
                try { $db->exec($s); $seedOk++; } catch (\PDOException $e) { /* ignora duplicatas */ }
            }
            echo "<p class='ok'>✓ Dados de demonstração inseridos ($seedOk instruções).</p>";
        }
    } else {
        echo "<p style='color:#718096'>ℹ️ Banco já possui dados — seed ignorado.</p>";
    }

    // 4. Verificar uploads
    echo '<h3>4. Verificando diretórios de upload...</h3>';
    $uploadDirs = ['produtos','banners','depoimentos','categorias'];
    foreach ($uploadDirs as $dir) {
        $path = ROOT . '/uploads/' . $dir;
        if (!is_dir($path)) mkdir($path, 0755, true);
        echo "<p class='ok'>✓ uploads/{$dir}/</p>";
    }

    echo '<hr>';
    echo '<h2 class="ok">✅ Instalação concluída com sucesso!</h2>';
    echo '<p>Acesse o painel em: <a href="/admin/login"><strong>/admin/login</strong></a></p>';
    echo '<div class="alerta"><strong>⚠️ IMPORTANTE:</strong> Apague o arquivo <code>setup/install.php</code> imediatamente após confirmar o acesso!</div>';

} catch (\Exception $e) {
    echo "<p class='err'><strong>Erro:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>Verifique as configurações em <code>config/database.php</code> e tente novamente.</p>";
}

echo '</div></body></html>';
