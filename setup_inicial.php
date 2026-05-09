<?php
/**
 * Iraná Natural — Instalador Automatizado do Ambiente Local
 *
 * Cria toda a estrutura do banco de dados MySQL a partir do zero,
 * configurando tabelas, índices, foreign keys e dados iniciais.
 *
 * COMO USAR:
 *   1. Configure seu servidor local (XAMPP / WAMP / Laragon)
 *   2. Acesse: http://localhost/irananatural/setup_inicial.php
 *   3. Preencha os dados de conexão e clique em Instalar
 *   4. APAGUE este arquivo após confirmar o acesso ao painel!
 *
 * COMPATIBILIDADE:
 *   - PHP 8.0+
 *   - MySQL 8.0+ / MariaDB 10.3+
 *   - Funciona em XAMPP, WAMP, Laragon, hospedagem compartilhada
 *
 * SEGURANÇA:
 *   - Este arquivo não deve existir em produção
 *   - Não loga senhas ou credenciais
 *   - Remove automaticamente a capacidade de re-instalar após conclusão
 */

declare(strict_types=1);

// ─────────────────────────────────────────────────────────────────────────────
// Constantes e configuração do script
// ─────────────────────────────────────────────────────────────────────────────

define('SETUP_VERSION', '2.0');
define('SQL_FILE', __DIR__ . '/database/setup_inicial.sql');
define('ADMIN_EMAIL_PADRAO', 'admin@irananatural.com.br');
define('ADMIN_SENHA_PADRAO', 'Iran@2024');
define('BCRYPT_COST', 12);
define('UPLOAD_DIRS', ['produtos', 'banners', 'depoimentos', 'categorias', 'temp']);

// ─────────────────────────────────────────────────────────────────────────────
// Funções utilitárias
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Parseia o arquivo SQL e retorna array de statements executáveis.
 * Remove comentários, linhas em branco e instruções não aplicáveis
 * quando o banco já foi selecionado via PDO.
 */
function parsearSQL(string $caminhoArquivo): array
{
    if (!file_exists($caminhoArquivo)) {
        throw new RuntimeException("Arquivo SQL não encontrado: {$caminhoArquivo}");
    }

    $conteudo = file_get_contents($caminhoArquivo);
    if ($conteudo === false) {
        throw new RuntimeException("Não foi possível ler o arquivo SQL.");
    }

    // Remove bloco de comentários /* ... */ (incluindo multiline)
    $conteudo = preg_replace('/\/\*[\s\S]*?\*\//', '', $conteudo);

    $statements  = [];
    $buffer      = '';
    $emString    = false;
    $charAnterior = '';

    foreach (str_split($conteudo) as $char) {
        if ($char === "'" && $charAnterior !== '\\') {
            $emString = !$emString;
        }

        if (!$emString && $char === ';') {
            $stmt = trim($buffer);
            if (!empty($stmt)) {
                $statements[] = $stmt;
            }
            $buffer = '';
        } else {
            $buffer .= $char;
        }

        $charAnterior = $char;
    }

    // Filtra statements vazios e linhas apenas de comentário
    return array_filter($statements, function (string $s): bool {
        $limpo = trim(preg_replace('/^--.*$/m', '', $s));
        return !empty($limpo);
    });
}

/**
 * Classifica o tipo de statement SQL para log amigável.
 */
function classificarStatement(string $stmt): string
{
    $inicio = strtoupper(substr(ltrim($stmt), 0, 20));

    if (str_starts_with($inicio, 'CREATE DATABASE'))  return 'criar_banco';
    if (str_starts_with($inicio, 'USE '))             return 'selecionar_banco';
    if (str_starts_with($inicio, 'CREATE TABLE'))     return 'criar_tabela';
    if (str_starts_with($inicio, 'ALTER TABLE'))      return 'alterar_tabela';
    if (str_starts_with($inicio, 'INSERT INTO'))      return 'inserir_dados';
    if (str_starts_with($inicio, 'SET '))             return 'configurar';
    if (str_starts_with($inicio, 'DROP '))            return 'remover';

    return 'outro';
}

/**
 * Extrai o nome da tabela de um CREATE TABLE statement.
 */
function extrairNomeTabela(string $stmt): string
{
    if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w+)[`"]?/i', $stmt, $m)) {
        return $m[1];
    }
    return '';
}

/**
 * Testa a conexão PDO com os parâmetros fornecidos.
 * Retorna [true, $pdo] em sucesso ou [false, $mensagem_erro] em falha.
 */
function testarConexao(string $host, int $porta, string $usuario, string $senha): array
{
    try {
        $dsn = "mysql:host={$host};port={$porta};charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario, $senha, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 10,
        ]);
        return [true, $pdo];
    } catch (PDOException $e) {
        return [false, $e->getMessage()];
    }
}

/**
 * Verifica se o usuário MySQL tem a permissão especificada.
 */
function verificarPermissao(PDO $pdo, string $permissao): bool
{
    $grants = $pdo->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($grants as $grant) {
        if (str_contains(strtoupper($grant), 'ALL PRIVILEGES') ||
            str_contains(strtoupper($grant), strtoupper($permissao))) {
            return true;
        }
    }
    return false;
}

/**
 * Cria o banco de dados se não existir e conecta a ele.
 */
function criarEConectar(string $host, int $porta, string $banco, string $usuario, string $senha): PDO
{
    $dsn = "mysql:host={$host};port={$porta};charset=utf8mb4";
    $pdo = new PDO($dsn, $usuario, $senha, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Cria o banco com charset correto
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$banco}`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$banco}`");

    return $pdo;
}

/**
 * Verifica os diretórios de upload e tenta criá-los.
 * Retorna array com [nome => status_bool].
 */
function verificarDiretorios(): array
{
    $base       = __DIR__ . '/uploads/';
    $resultados = [];

    foreach (UPLOAD_DIRS as $dir) {
        $caminho = $base . $dir;
        if (!is_dir($caminho)) {
            $criou = @mkdir($caminho, 0755, true);
            $resultados[$dir] = $criou;
        } else {
            $resultados[$dir] = true;
        }
    }

    return $resultados;
}

/**
 * Formata bytes em string legível.
 */
function formatarBytes(int $bytes): string
{
    if ($bytes < 1024) return "{$bytes} B";
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

// ─────────────────────────────────────────────────────────────────────────────
// Lógica principal — processamento das fases
// ─────────────────────────────────────────────────────────────────────────────

$fase = $_POST['fase'] ?? 'configurar';
$resultado = null;

if ($fase === 'instalar' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitização dos inputs
    $dbHost    = trim(htmlspecialchars($_POST['db_host'] ?? 'localhost', ENT_QUOTES, 'UTF-8'));
    $dbPorta   = max(1, min(65535, (int) ($_POST['db_porta'] ?? 3306)));
    $dbNome    = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['db_nome'] ?? 'irananatural');
    $dbUsuario = trim($_POST['db_usuario'] ?? 'root');
    $dbSenha   = $_POST['db_senha'] ?? '';
    $admEmail  = filter_var(trim($_POST['adm_email'] ?? ADMIN_EMAIL_PADRAO), FILTER_SANITIZE_EMAIL);
    $admSenha  = $_POST['adm_senha'] ?? ADMIN_SENHA_PADRAO;
    $admNome   = trim(htmlspecialchars($_POST['adm_nome'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'));

    $erros    = [];
    $logs     = [];
    $inicio   = microtime(true);

    // Validações básicas
    if (empty($dbNome))   $erros[] = 'Nome do banco de dados é obrigatório.';
    if (empty($admEmail)) $erros[] = 'E-mail do administrador inválido.';
    if (strlen($admSenha) < 6) $erros[] = 'Senha do administrador deve ter ao menos 6 caracteres.';

    if (!file_exists(SQL_FILE)) {
        $erros[] = 'Arquivo database/setup_inicial.sql não encontrado. Verifique a estrutura do projeto.';
    }

    if (!empty($erros)) {
        $resultado = ['status' => 'erro_validacao', 'erros' => $erros];
    } else {
        try {
            // ── Fase 1: Conexão ──────────────────────────────────────────────
            $logs[] = ['tipo' => 'info', 'msg' => "Conectando em {$dbHost}:{$dbPorta}..."];

            [$conectou, $pdoOuErro] = testarConexao($dbHost, $dbPorta, $dbUsuario, $dbSenha);
            if (!$conectou) {
                throw new RuntimeException("Falha na conexão: {$pdoOuErro}");
            }

            $logs[] = ['tipo' => 'ok', 'msg' => 'Conexão com MySQL estabelecida.'];

            // ── Fase 2: Criar/selecionar banco ───────────────────────────────
            $pdo = criarEConectar($dbHost, $dbPorta, $dbNome, $dbUsuario, $dbSenha);
            $logs[] = ['tipo' => 'ok', 'msg' => "Banco `{$dbNome}` pronto."];

            // ── Fase 3: Parsear e executar o SQL ─────────────────────────────
            $statements = parsearSQL(SQL_FILE);
            $totalStmts = count($statements);
            $logs[] = ['tipo' => 'info', 'msg' => "{$totalStmts} statements encontrados em setup_inicial.sql."];

            $tabelasCriadas   = [];
            $configsInseridas = 0;
            $errosSQL         = [];
            $executados       = 0;

            foreach ($statements as $stmt) {
                $tipo = classificarStatement($stmt);

                // Pula CREATE DATABASE e USE — já tratados acima
                if ($tipo === 'criar_banco' || $tipo === 'selecionar_banco') {
                    continue;
                }

                // Pula inserção do admin placeholder — tratado abaixo com hash real
                if ($tipo === 'inserir_dados' &&
                    str_contains(strtoupper($stmt), 'PENDENTE_EXECUTAR_SETUP_INICIAL_PHP')) {
                    continue;
                }

                try {
                    $pdo->exec($stmt);
                    $executados++;

                    if ($tipo === 'criar_tabela') {
                        $nome = extrairNomeTabela($stmt);
                        if ($nome) {
                            $tabelasCriadas[] = $nome;
                            $logs[] = ['tipo' => 'ok', 'msg' => "Tabela `{$nome}` criada/verificada."];
                        }
                    } elseif ($tipo === 'inserir_dados') {
                        $configsInseridas++;
                    }
                } catch (PDOException $e) {
                    $codigo = (string) $e->getCode();
                    // 42S01 = tabela já existe (esperado em reinstalação)
                    // 1060  = coluna duplicada (ALTER TABLE já aplicado)
                    // 1061  = chave duplicada
                    $errosEsperados = ['42S01', '1060', '1061', '1062'];
                    if (!in_array($codigo, $errosEsperados, true)) {
                        $preview = substr(trim($stmt), 0, 80);
                        $errosSQL[] = "[{$codigo}] {$e->getMessage()} — SQL: {$preview}...";
                        $logs[] = [
                            'tipo' => 'aviso',
                            'msg'  => "Aviso [{$codigo}]: {$e->getMessage()}"
                        ];
                    }
                }
            }

            $logs[] = ['tipo' => 'info', 'msg' => "{$executados} statements SQL executados."];

            // ── Fase 4: Criar/atualizar usuário administrador com hash bcrypt ──
            $hashSenha  = password_hash($admSenha, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $stmtAdmin  = $pdo->prepare(
                "INSERT INTO `usuarios` (`nome`, `email`, `senha`) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `senha` = VALUES(`senha`)"
            );
            $stmtAdmin->execute([$admNome, $admEmail, $hashSenha]);
            $logs[] = ['tipo' => 'ok', 'msg' => "Usuário administrador configurado: {$admEmail}"];

            // ── Fase 5: Verificar diretórios de upload ────────────────────────
            $diretorios = verificarDiretorios();
            foreach ($diretorios as $dir => $ok) {
                if ($ok) {
                    $logs[] = ['tipo' => 'ok', 'msg' => "Diretório uploads/{$dir}/ OK."];
                } else {
                    $logs[] = ['tipo' => 'aviso', 'msg' => "Não foi possível criar uploads/{$dir}/ — crie manualmente."];
                }
            }

            // ── Fase 6: Verificar versão MySQL ────────────────────────────────
            $versaoMySQL = $pdo->query("SELECT VERSION()")->fetchColumn();
            $logs[] = ['tipo' => 'info', 'msg' => "MySQL/MariaDB versão: {$versaoMySQL}"];

            // ── Fase 7: Contar tabelas no banco ───────────────────────────────
            $qtdTabelas = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'"
            )->fetchColumn();

            $tempo = round((microtime(true) - $inicio) * 1000, 1);

            $resultado = [
                'status'          => empty($errosSQL) ? 'sucesso' : 'sucesso_com_avisos',
                'logs'            => $logs,
                'tabelas_criadas' => $tabelasCriadas,
                'qtd_tabelas'     => $qtdTabelas,
                'configs'         => $configsInseridas,
                'executados'      => $executados,
                'erros_sql'       => $errosSQL,
                'tempo_ms'        => $tempo,
                'versao_mysql'    => $versaoMySQL,
                'adm_email'       => $admEmail,
                'adm_senha'       => $admSenha,
                'db_nome'         => $dbNome,
            ];

        } catch (Throwable $e) {
            $resultado = [
                'status' => 'erro_fatal',
                'logs'   => $logs,
                'erro'   => $e->getMessage(),
                'arquivo'=> $e->getFile() . ':' . $e->getLine(),
            ];
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Recuperar valores do formulário para repopular em caso de erro
// ─────────────────────────────────────────────────────────────────────────────

$formHost    = htmlspecialchars($_POST['db_host']    ?? 'localhost',                ENT_QUOTES, 'UTF-8');
$formPorta   = htmlspecialchars($_POST['db_porta']   ?? '3306',                     ENT_QUOTES, 'UTF-8');
$formNome    = htmlspecialchars($_POST['db_nome']    ?? 'irananatural',             ENT_QUOTES, 'UTF-8');
$formUsuario = htmlspecialchars($_POST['db_usuario'] ?? 'root',                     ENT_QUOTES, 'UTF-8');
$formAdmNome = htmlspecialchars($_POST['adm_nome']   ?? 'Administrador',           ENT_QUOTES, 'UTF-8');
$formAdmEmail= htmlspecialchars($_POST['adm_email']  ?? ADMIN_EMAIL_PADRAO,        ENT_QUOTES, 'UTF-8');
$formAdmSenha= htmlspecialchars($_POST['adm_senha']  ?? ADMIN_SENHA_PADRAO,        ENT_QUOTES, 'UTF-8');

// ─────────────────────────────────────────────────────────────────────────────
// Helpers de renderização HTML
// ─────────────────────────────────────────────────────────────────────────────

function iconeLog(string $tipo): string
{
    return match ($tipo) {
        'ok'    => '<span class="ic ok">✓</span>',
        'aviso' => '<span class="ic warn">⚠</span>',
        'erro'  => '<span class="ic err">✗</span>',
        default => '<span class="ic info">ℹ</span>',
    };
}

function corStatus(string $status): string
{
    return match ($status) {
        'sucesso'            => '#2C5F2E',
        'sucesso_com_avisos' => '#B7791F',
        'erro_fatal',
        'erro_validacao'     => '#C53030',
        default              => '#4A5568',
    };
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Inicial — Iraná Natural</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --verde:     #2C5F2E;
            --verde-cl:  #E8F5E9;
            --verde-md:  #4CAF50;
            --amarelo:   #B7791F;
            --amarelo-cl:#FFFBEB;
            --vermelho:  #C53030;
            --verm-cl:   #FFF5F5;
            --cinza:     #718096;
            --cinza-cl:  #F7FAFC;
            --borda:     #E2E8F0;
            --texto:     #2D3748;
            --shadow:    0 4px 24px rgba(0,0,0,.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 15px;
            color: var(--texto);
            background: #F0F4F8;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--verde);
            letter-spacing: -.5px;
        }
        .header .logo span { color: #888; font-weight: 300; }
        .header p {
            color: var(--cinza);
            margin-top: .4rem;
            font-size: .9rem;
        }

        /* ── Card base ── */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .card h2 {
            font-size: 1.1rem;
            color: var(--verde);
            margin-bottom: 1.2rem;
            padding-bottom: .6rem;
            border-bottom: 2px solid var(--verde-cl);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        /* ── Formulário ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 560px) { .form-grid { grid-template-columns: 1fr; } }

        .form-group { display: flex; flex-direction: column; gap: .35rem; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--cinza);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
            padding: .65rem .9rem;
            border: 1.5px solid var(--borda);
            border-radius: 7px;
            font-size: .95rem;
            color: var(--texto);
            transition: border-color .2s;
            background: #fff;
        }
        input:focus {
            outline: none;
            border-color: var(--verde);
            box-shadow: 0 0 0 3px rgba(44,95,46,.1);
        }

        .hint {
            font-size: .76rem;
            color: var(--cinza);
            margin-top: .15rem;
        }

        /* ── Botão ── */
        .btn-instalar {
            width: 100%;
            padding: 1rem;
            background: var(--verde);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
            margin-top: .5rem;
        }
        .btn-instalar:hover  { background: #1a3d1c; }
        .btn-instalar:active { transform: scale(.99); }
        .btn-instalar:disabled { background: #9CA3AF; cursor: not-allowed; }

        /* ── Barra de progresso ── */
        .progress-wrap {
            background: var(--cinza-cl);
            border-radius: 999px;
            height: 8px;
            margin-bottom: 1.2rem;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width .4s ease;
        }
        .progress-bar.ok    { background: var(--verde-md); }
        .progress-bar.warn  { background: #F6AD55; }
        .progress-bar.error { background: var(--vermelho); }

        /* ── Logs ── */
        .log-list {
            list-style: none;
            font-size: .875rem;
            line-height: 1.8;
            max-height: 380px;
            overflow-y: auto;
            background: #FAFAFA;
            border: 1px solid var(--borda);
            border-radius: 8px;
            padding: .75rem 1rem;
        }
        .ic { display: inline-block; width: 18px; font-weight: 700; }
        .ic.ok   { color: var(--verde); }
        .ic.warn { color: var(--amarelo); }
        .ic.err  { color: var(--vermelho); }
        .ic.info { color: #4299E1; }

        /* ── Status banner ── */
        .status-banner {
            border-radius: 10px;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .status-banner .status-icon { font-size: 2rem; flex-shrink: 0; }
        .status-banner .status-text h3 { font-size: 1.15rem; margin-bottom: .2rem; }
        .status-banner .status-text p  { font-size: .875rem; opacity: .85; }
        .status-banner.sucesso       { background: var(--verde-cl);  color: var(--verde); }
        .status-banner.aviso         { background: var(--amarelo-cl); color: var(--amarelo); }
        .status-banner.erro          { background: var(--verm-cl);   color: var(--vermelho); }

        /* ── Credenciais ── */
        .credenciais {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 8px;
            padding: 1rem 1.2rem;
            font-size: .875rem;
        }
        .credenciais h4 { color: #1E40AF; margin-bottom: .6rem; }
        .cred-row { display: flex; justify-content: space-between; padding: .25rem 0; border-bottom: 1px solid #DBEAFE; }
        .cred-row:last-child { border-bottom: none; }
        .cred-label { font-weight: 600; color: #3B82F6; }
        .cred-val   { font-family: monospace; color: #1E3A8A; }

        /* ── Tabelas criadas ── */
        .tabelas-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-top: .5rem;
        }
        .tabela-badge {
            background: var(--verde-cl);
            color: var(--verde);
            border-radius: 5px;
            padding: .2rem .6rem;
            font-size: .78rem;
            font-family: monospace;
            font-weight: 600;
        }

        /* ── Métricas ── */
        .metricas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: .5rem;
        }
        .metrica {
            text-align: center;
            background: var(--cinza-cl);
            border-radius: 8px;
            padding: .8rem;
        }
        .metrica .num { font-size: 1.8rem; font-weight: 700; color: var(--verde); }
        .metrica .lab { font-size: .72rem; color: var(--cinza); text-transform: uppercase; letter-spacing: .04em; }

        /* ── Alerta final ── */
        .alerta-seguranca {
            background: #FFF8F1;
            border: 2px solid #F6AD55;
            border-radius: 8px;
            padding: 1rem 1.2rem;
            font-size: .85rem;
            color: #744210;
        }
        .alerta-seguranca strong { display: block; margin-bottom: .3rem; color: #92400E; }

        /* ── Erros ── */
        .lista-erros {
            list-style: none;
            font-size: .82rem;
            font-family: monospace;
            color: var(--vermelho);
            background: var(--verm-cl);
            border-radius: 7px;
            padding: .75rem 1rem;
            max-height: 200px;
            overflow-y: auto;
        }
        .lista-erros li { padding: .2rem 0; border-bottom: 1px solid #FED7D7; }
        .lista-erros li:last-child { border-bottom: none; }

        /* ── Spinner ── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            vertical-align: middle;
            margin-right: .4rem;
        }

        /* ── Link painel ── */
        .link-painel {
            display: inline-block;
            margin-top: 1rem;
            padding: .75rem 1.5rem;
            background: var(--verde);
            color: #fff;
            text-decoration: none;
            border-radius: 7px;
            font-weight: 600;
            font-size: .95rem;
        }
        .link-painel:hover { background: #1a3d1c; }

        footer {
            text-align: center;
            color: #A0AEC0;
            font-size: .78rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- ── Header ── -->
    <div class="header">
        <div class="logo">🌿 Iraná Natural <span>/ Setup</span></div>
        <p>Instalador automatizado do ambiente local — v<?= SETUP_VERSION ?></p>
    </div>

    <?php if ($resultado === null): ?>
    <!-- ═══════════════════════════════════════════════════════════════════════
         FASE 1: Formulário de configuração
    ════════════════════════════════════════════════════════════════════════ -->

        <div class="card">
            <h2>⚙️ Configuração do Banco de Dados</h2>
            <form method="POST" action="" id="form-setup"
                  onsubmit="return iniciarInstalacao(event)">
                <input type="hidden" name="fase" value="instalar">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="db_host">Host MySQL</label>
                        <input type="text" id="db_host" name="db_host"
                               value="<?= $formHost ?>" required>
                        <span class="hint">Geralmente: localhost</span>
                    </div>
                    <div class="form-group">
                        <label for="db_porta">Porta</label>
                        <input type="number" id="db_porta" name="db_porta"
                               value="<?= $formPorta ?>" min="1" max="65535" required>
                        <span class="hint">Padrão MySQL: 3306</span>
                    </div>
                    <div class="form-group">
                        <label for="db_nome">Nome do Banco</label>
                        <input type="text" id="db_nome" name="db_nome"
                               value="<?= $formNome ?>" required
                               pattern="[a-zA-Z0-9_]+" title="Apenas letras, números e _">
                        <span class="hint">Criado automaticamente se não existir</span>
                    </div>
                    <div class="form-group">
                        <label for="db_usuario">Usuário MySQL</label>
                        <input type="text" id="db_usuario" name="db_usuario"
                               value="<?= $formUsuario ?>" required>
                        <span class="hint">XAMPP: root | WAMP: root</span>
                    </div>
                    <div class="form-group full">
                        <label for="db_senha">Senha MySQL</label>
                        <input type="password" id="db_senha" name="db_senha" value=""
                               placeholder="Deixe em branco se não tiver senha (XAMPP padrão)">
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>👤 Usuário Administrador</h2>
            <form id="form-admin-info">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="adm_nome">Nome do Administrador</label>
                        <input type="text" id="adm_nome" name="adm_nome"
                               form="form-setup" value="<?= $formAdmNome ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="adm_email">E-mail de Login</label>
                        <input type="email" id="adm_email" name="adm_email"
                               form="form-setup" value="<?= $formAdmEmail ?>" required>
                    </div>
                    <div class="form-group full">
                        <label for="adm_senha">Senha do Painel</label>
                        <input type="password" id="adm_senha" name="adm_senha"
                               form="form-setup" value="<?= $formAdmSenha ?>"
                               minlength="6" required>
                        <span class="hint">
                            Mínimo 6 caracteres. Padrão: <strong><?= ADMIN_SENHA_PADRAO ?></strong>
                        </span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Arquivo SQL a ser executado -->
        <?php if (file_exists(SQL_FILE)): ?>
        <div class="card">
            <h2>📄 Arquivo SQL</h2>
            <p style="font-size:.875rem; color:var(--cinza);">
                O seguinte arquivo será executado:
            </p>
            <p style="font-family:monospace; margin:.5rem 0; color:var(--verde); font-size:.9rem;">
                <?= htmlspecialchars(str_replace(__DIR__, '', SQL_FILE), ENT_QUOTES, 'UTF-8') ?>
                <span style="color:var(--cinza);">
                    (<?= formatarBytes(filesize(SQL_FILE)) ?>)
                </span>
            </p>
            <p style="font-size:.8rem; color:var(--cinza); margin-top:.5rem;">
                18 tabelas serão criadas ou verificadas. Dados existentes não serão sobrescritos.
            </p>
        </div>
        <?php else: ?>
        <div class="card" style="border: 2px solid var(--vermelho);">
            <h2 style="color:var(--vermelho);">⚠️ Arquivo SQL não encontrado</h2>
            <p style="color:var(--vermelho); font-size:.875rem;">
                <strong>database/setup_inicial.sql</strong> não foi encontrado.<br>
                Verifique se o arquivo existe na pasta correta.
            </p>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="form-setup-submit"
              onsubmit="return false;" style="margin-bottom:1.5rem;">
            <button type="button" class="btn-instalar"
                    id="btn-instalar"
                    onclick="document.getElementById('form-setup').submit(); iniciarUI();">
                🚀 Instalar e Configurar Banco de Dados
            </button>
        </form>

        <p style="text-align:center; font-size:.8rem; color:var(--cinza);">
            ⚠️ Dados existentes no banco não serão apagados. Tabelas já existentes serão ignoradas.
        </p>

    <?php elseif (isset($resultado['status'])): ?>
    <!-- ═══════════════════════════════════════════════════════════════════════
         FASE 2: Resultado da instalação
    ════════════════════════════════════════════════════════════════════════ -->

        <?php
        $s = $resultado['status'];

        // ── Banner de status ──────────────────────────────────────────────
        $bannerClasse = match ($s) {
            'sucesso'            => 'sucesso',
            'sucesso_com_avisos' => 'aviso',
            default              => 'erro',
        };
        $bannerIcone = match ($s) {
            'sucesso'            => '✅',
            'sucesso_com_avisos' => '⚠️',
            default              => '❌',
        };
        $bannerTitulo = match ($s) {
            'sucesso'            => 'Instalação concluída com sucesso!',
            'sucesso_com_avisos' => 'Instalação concluída com avisos',
            'erro_validacao'     => 'Erro de validação',
            default              => 'Erro durante a instalação',
        };
        $bannerDesc = match ($s) {
            'sucesso'            => 'Ambiente local pronto. Você já pode acessar o painel.',
            'sucesso_com_avisos' => 'A instalação foi concluída, mas alguns avisos foram registrados. Verifique o log abaixo.',
            'erro_validacao'     => 'Corrija os erros abaixo e tente novamente.',
            default              => 'Verifique as configurações e o log de erros abaixo.',
        };
        ?>

        <div class="status-banner <?= $bannerClasse ?>">
            <div class="status-icon"><?= $bannerIcone ?></div>
            <div class="status-text">
                <h3><?= $bannerTitulo ?></h3>
                <p><?= $bannerDesc ?></p>
            </div>
        </div>

        <?php if ($s === 'erro_validacao'): ?>
            <div class="card">
                <h2 style="color:var(--vermelho);">Erros encontrados</h2>
                <ul class="lista-erros">
                    <?php foreach ($resultado['erros'] as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <div style="margin-top:1rem;">
                    <a href="?" class="link-painel" style="background:#718096;">← Voltar e corrigir</a>
                </div>
            </div>

        <?php elseif ($s === 'erro_fatal'): ?>
            <div class="card">
                <h2 style="color:var(--vermelho);">Erro fatal</h2>
                <p style="font-family:monospace; font-size:.875rem; color:var(--vermelho); background:var(--verm-cl); padding:.75rem; border-radius:6px; margin-bottom:1rem;">
                    <?= htmlspecialchars($resultado['erro'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>
                <?php if (!empty($resultado['arquivo'])): ?>
                <p style="font-size:.78rem; color:var(--cinza);">
                    <?= htmlspecialchars($resultado['arquivo'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <?php endif; ?>

                <?php if (!empty($resultado['logs'])): ?>
                <h3 style="margin-top:1.2rem; font-size:.95rem;">Log até o ponto de falha</h3>
                <ul class="log-list" style="margin-top:.5rem;">
                    <?php foreach ($resultado['logs'] as $log): ?>
                        <li><?= iconeLog($log['tipo']) ?> <?= htmlspecialchars($log['msg'], ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div style="margin-top:1rem;">
                    <a href="?" class="link-painel" style="background:#718096;">← Tentar novamente</a>
                </div>
            </div>

        <?php else: ?>
            <!-- ── Sucesso ou sucesso com avisos ─────────────────────────── -->

            <!-- Métricas -->
            <?php if (!empty($resultado['metricas']) || isset($resultado['tempo_ms'])): ?>
            <div class="card">
                <h2>📊 Resumo da Instalação</h2>
                <div class="metricas">
                    <div class="metrica">
                        <div class="num"><?= $resultado['qtd_tabelas'] ?? '-' ?></div>
                        <div class="lab">Tabelas no banco</div>
                    </div>
                    <div class="metrica">
                        <div class="num"><?= count($resultado['tabelas_criadas'] ?? []) ?></div>
                        <div class="lab">Criadas agora</div>
                    </div>
                    <div class="metrica">
                        <div class="num"><?= $resultado['executados'] ?? '-' ?></div>
                        <div class="lab">Statements SQL</div>
                    </div>
                    <div class="metrica">
                        <div class="num"><?= $resultado['tempo_ms'] ?? '-' ?><span style="font-size:1rem;">ms</span></div>
                        <div class="lab">Tempo total</div>
                    </div>
                </div>
                <?php if (!empty($resultado['versao_mysql'])): ?>
                <p style="font-size:.78rem; color:var(--cinza); margin-top:.75rem;">
                    Servidor: <?= htmlspecialchars($resultado['versao_mysql'], ENT_QUOTES, 'UTF-8') ?>
                    &nbsp;|&nbsp; Banco: <strong><?= htmlspecialchars($resultado['db_nome'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Barra de progresso visual -->
            <?php
            $temErrosSql = !empty($resultado['erros_sql']);
            $pct = $temErrosSql ? 80 : 100;
            $corBarra = $temErrosSql ? 'warn' : 'ok';
            ?>
            <div class="card" style="padding-bottom:1.5rem;">
                <h2>📈 Progresso</h2>
                <div class="progress-wrap">
                    <div class="progress-bar <?= $corBarra ?>" style="width:<?= $pct ?>%;"></div>
                </div>
                <p style="font-size:.82rem; color:var(--cinza);">
                    <?= $pct ?>% — <?= $temErrosSql ? 'Instalação com avisos' : 'Instalação completa' ?>
                </p>
            </div>

            <!-- Tabelas criadas -->
            <?php if (!empty($resultado['tabelas_criadas'])): ?>
            <div class="card">
                <h2>🗄️ Tabelas Criadas/Verificadas</h2>
                <div class="tabelas-grid">
                    <?php foreach ($resultado['tabelas_criadas'] as $t): ?>
                        <span class="tabela-badge"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Credenciais de acesso -->
            <div class="card">
                <h2>🔑 Credenciais de Acesso ao Painel</h2>
                <div class="credenciais">
                    <h4>Administrador configurado:</h4>
                    <div class="cred-row">
                        <span class="cred-label">URL do painel:</span>
                        <span class="cred-val">/admin/login</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">E-mail:</span>
                        <span class="cred-val"><?= htmlspecialchars($resultado['adm_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">Senha:</span>
                        <span class="cred-val"><?= htmlspecialchars($resultado['adm_senha'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                <a href="/admin/login" class="link-painel">→ Acessar o painel administrativo</a>
            </div>

            <!-- Log de execução -->
            <?php if (!empty($resultado['logs'])): ?>
            <div class="card">
                <h2>📋 Log de Execução</h2>
                <ul class="log-list">
                    <?php foreach ($resultado['logs'] as $log): ?>
                        <li>
                            <?= iconeLog($log['tipo']) ?>
                            <?= htmlspecialchars($log['msg'], ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Erros SQL (avisos) -->
            <?php if (!empty($resultado['erros_sql'])): ?>
            <div class="card">
                <h2 style="color:var(--amarelo);">⚠️ Avisos SQL (<?= count($resultado['erros_sql']) ?>)</h2>
                <p style="font-size:.82rem; color:var(--cinza); margin-bottom:.75rem;">
                    Erros esperados em reinstalação (tabelas/índices já existentes) são ignorados automaticamente.
                    Os erros abaixo merecem atenção:
                </p>
                <ul class="lista-erros" style="color:var(--amarelo); background:var(--amarelo-cl);">
                    <?php foreach ($resultado['erros_sql'] as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Alerta de segurança -->
            <div class="alerta-seguranca">
                <strong>⚠️ IMPORTANTE — Segurança:</strong>
                Após confirmar o acesso ao painel, <u>apague este arquivo imediatamente</u>:
                <br><code style="font-family:monospace; background:#FEF3C7; padding:.1rem .4rem; border-radius:3px;">
                    setup_inicial.php
                </code>
                <br><br>
                Manter este arquivo em produção é uma <strong>vulnerabilidade de segurança grave</strong>.
            </div>

        <?php endif; ?>

    <?php endif; ?>

    <footer>
        Iraná Natural &mdash; Setup v<?= SETUP_VERSION ?> &mdash; PHP <?= PHP_VERSION ?>
    </footer>
</div>

<script>
function iniciarInstalacao(e) {
    const btn = document.getElementById('btn-instalar');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Instalando, aguarde...';
    }
    return true;
}
function iniciarUI() {
    const btn = document.getElementById('btn-instalar');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Instalando, aguarde...';
    }
}
// Submete ambos os formulários juntos
document.addEventListener('DOMContentLoaded', function () {
    const btnInstalar = document.getElementById('btn-instalar');
    if (btnInstalar) {
        btnInstalar.addEventListener('click', function () {
            this.disabled = true;
            this.innerHTML = '<span class="spinner"></span> Instalando, aguarde...';
            setTimeout(function () {
                document.getElementById('form-setup').submit();
            }, 80);
        });
    }
});
</script>
</body>
</html>
