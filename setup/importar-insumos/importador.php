#!/usr/bin/env php
<?php
/**
 * Iraná Natural — Importador CLI de Insumos
 *
 * Importa Iraná_Insumos.csv para insumos, categorias_insumos e compras_insumos.
 * Script de uso único. Aplica migração de banco automaticamente se necessário.
 *
 * Uso:
 *   php importador.php <arquivo.csv> [--dry-run] [--skip-cleanup]
 *
 * Opções:
 *   --dry-run       Valida e exibe o que seria feito, sem gravar no banco
 *   --skip-cleanup  Pula DELETE fichas_tecnicas + TRUNCATE insumos/categorias_insumos
 *   --help          Exibe esta ajuda
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acesso negado.' . PHP_EOL);
}

if (PHP_VERSION_ID < 80000) {
    fwrite(STDERR, 'Erro: PHP 8.0+ é necessário (atual: ' . PHP_VERSION . ').' . PHP_EOL);
    exit(1);
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/mapper.php';

// ─── CLI Arguments ────────────────────────────────────────────────────────────

$args        = array_slice($argv, 1);
$isDryRun    = in_array('--dry-run',      $args, true);
$skipCleanup = in_array('--skip-cleanup', $args, true);
$showHelp    = in_array('--help', $args, true) || in_array('-h', $args, true);
$csvFile     = null;

foreach ($args as $arg) {
    if ($arg !== '' && $arg[0] !== '-') {
        $csvFile = $arg;
        break;
    }
}

if ($showHelp || !$csvFile) {
    showHelp();
    exit($csvFile ? 0 : 1);
}

if (!file_exists($csvFile) || !is_readable($csvFile)) {
    fwrite(STDERR, "Erro: arquivo não encontrado: {$csvFile}" . PHP_EOL);
    exit(1);
}

// ─── Init ─────────────────────────────────────────────────────────────────────

$log = new Logger(LOG_FILE);

$log->line(c('╔══════════════════════════════════════════════════╗', 'green'));
$log->line(c('║  Iraná Natural — Importador de Insumos           ║', 'green'));
$log->line(c('╚══════════════════════════════════════════════════╝', 'green'));
$log->line('');

if ($isDryRun) {
    $log->line(c('  ★ MODO DRY-RUN — nenhuma alteração será feita no banco', 'yellow'));
    $log->line('');
}

// ─── Banco de dados ───────────────────────────────────────────────────────────

$log->title('Banco de dados');
$pdo = dbConnect($log);

// ─── Migration ────────────────────────────────────────────────────────────────

$log->title('Verificação de estrutura');
runMigration($pdo, $log);

// ─── Parse CSV ────────────────────────────────────────────────────────────────

$log->title('Leitura do CSV');
$log->info('Arquivo: ' . realpath($csvFile));

$rows = parseCsv($csvFile, CSV_DELIMITER);

if (count($rows) < 2) {
    $log->error('Arquivo vazio ou apenas com cabeçalho.');
    $log->close();
    exit(1);
}

$log->success(sprintf('%d linha(s) encontrada(s) + cabeçalho.', count($rows) - 1));

// ─── Mapeamento de colunas ────────────────────────────────────────────────────

$log->title('Mapeamento de colunas');

$mapResult = CsvMapper::map($rows[0]);

if (!empty($mapResult['missing'])) {
    $log->error('Colunas obrigatórias não localizadas: ' . implode(', ', $mapResult['missing']));
    $log->line('  Cabeçalho recebido: ' . implode(' | ', $rows[0]));
    $log->close();
    exit(1);
}

$colMap = $mapResult['map'];
$log->success('Mapeadas: ' . implode(', ', array_keys($colMap)));
if (!empty($mapResult['extra'])) {
    $log->warn('Ignoradas: ' . implode(', ', $mapResult['extra']));
}

// ─── Validação ────────────────────────────────────────────────────────────────

$log->title('Validação dos registros');

[$records, $skipped] = validateRows(array_slice($rows, 1), $colMap, $log);

$log->line('');
$log->line(sprintf('  %-22s %s', 'Total de linhas:',  c((string)(count($rows) - 1), 'bold')));
$log->line(sprintf('  %-22s %s', 'Válidos:',          c((string)count($records), 'green')));
$log->line(sprintf('  %-22s %s', 'Ignorados (erro):', c((string)$skipped, $skipped > 0 ? 'red' : 'white')));

if (count($records) === 0) {
    $log->error('Nenhum registro válido para importar.');
    $log->close();
    exit(1);
}

// Exibe categorias detectadas
$cats = array_values(array_unique(array_filter(array_column($records, 'categoria'))));
sort($cats);
if (!empty($cats)) {
    $log->line('');
    $log->line(sprintf('  Categorias detectadas (%d):', count($cats)));
    foreach ($cats as $cat) {
        $log->line(c('    · ' . $cat, 'cyan'));
    }
}

// ─── Dry-run exit ─────────────────────────────────────────────────────────────

if ($isDryRun) {
    $log->line('');
    $log->line(c('  ✓ Dry-run concluído. Nenhum dado foi alterado.', 'green'));
    $log->line('  Log: ' . LOG_FILE);
    $log->close();
    exit(0);
}

// ─── Confirmação de limpeza ───────────────────────────────────────────────────

if (!$skipCleanup) {
    $log->title('Limpeza de dados existentes');
    $log->line(c('  ATENÇÃO: Esta operação irá:', 'red'));
    $log->line(c('    · DELETE FROM fichas_tecnicas  (remove referências FK)', 'red'));
    $log->line(c('    · TRUNCATE TABLE insumos', 'red'));
    $log->line(c('    · TRUNCATE TABLE categorias_insumos', 'red'));
    $log->line('');

    $resp = prompt(c('  Digite "sim" para confirmar (qualquer outra coisa cancela): ', 'yellow'));
    $log->line('');

    if (strtolower($resp) !== 'sim') {
        $log->line('  Operação cancelada.');
        $log->close();
        exit(0);
    }

    runCleanup($pdo, $log);
}

// ─── Importação ───────────────────────────────────────────────────────────────

$log->title('Importação');
$stats = runImport($pdo, $log, $records);

// ─── Resumo final ─────────────────────────────────────────────────────────────

$log->title('Resultado');
$log->line(sprintf('  %-24s %s', 'Insumos inseridos:',   c((string)$stats['inseridos'],  'green')));
$log->line(sprintf('  %-24s %s', 'Categorias criadas:',  c((string)$stats['categorias'], 'cyan')));
$log->line(sprintf('  %-24s %s', 'Compras registradas:', c((string)$stats['compras'],    'cyan')));
$log->line(sprintf('  %-24s %s', 'Erros:',               c((string)$stats['erros'], $stats['erros'] > 0 ? 'red' : 'green')));
$log->line('');
$log->line('  Log completo: ' . LOG_FILE);
$log->line('');

if ($stats['erros'] > 0) {
    $log->warn('Importação concluída com ' . $stats['erros'] . ' erro(s). Verifique o log.');
} else {
    $log->line(c('  ✓ Importação concluída com sucesso!', 'green'));
}

$log->close();
exit($stats['erros'] > 0 ? 1 : 0);


// ═════════════════════════════════════════════════════════════════════════════
// FUNÇÕES
// ═════════════════════════════════════════════════════════════════════════════

function showHelp(): void
{
    echo <<<'HELP'

Iraná Natural — Importador CLI de Insumos

Uso:
  php importador.php <arquivo.csv> [opções]

Opções:
  --dry-run       Valida e exibe o que seria importado, sem alterar o banco
  --skip-cleanup  Pula a etapa de limpeza (DELETE + TRUNCATE)
  --help          Exibe esta ajuda

Exemplos:
  php importador.php "Iraná_Insumos.csv" --dry-run
  php importador.php "Iraná_Insumos.csv"

HELP;
}

// ─── Conexão ──────────────────────────────────────────────────────────────────

function dbConnect(Logger $log): PDO
{
    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ]);
        $log->success(DB_NAME . '@' . DB_HOST);
        return $pdo;
    } catch (PDOException $e) {
        $log->error('Falha na conexão: ' . $e->getMessage());
        $log->close();
        exit(1);
    }
}

// ─── Migration ────────────────────────────────────────────────────────────────

function runMigration(PDO $pdo, Logger $log): void
{
    $steps = [
        [
            'label' => 'Criar tabela categorias_insumos',
            'sql'   => "CREATE TABLE IF NOT EXISTS `categorias_insumos` (
                            `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
                            `nome`      VARCHAR(100) NOT NULL,
                            `criado_em` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `uk_cat_insumos_nome` (`nome`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ],
        [
            'label' => 'Adicionar tipo_id em insumos',
            'sql'   => "ALTER TABLE `insumos` ADD COLUMN `tipo_id` INT UNSIGNED NULL AFTER `id`",
        ],
        [
            'label' => 'Adicionar FK tipo_id → categorias_insumos',
            'sql'   => "ALTER TABLE `insumos` ADD CONSTRAINT `fk_insumos_tipo_id`
                            FOREIGN KEY (`tipo_id`) REFERENCES `categorias_insumos`(`id`) ON DELETE SET NULL",
        ],
        [
            'label' => 'Adicionar observacoes em insumos',
            'sql'   => "ALTER TABLE `insumos` ADD COLUMN `observacoes` TEXT NULL AFTER `fornecedor`",
        ],
        [
            'label' => 'Adicionar data_conferencia em insumos',
            'sql'   => "ALTER TABLE `insumos` ADD COLUMN `data_conferencia` DATE NULL AFTER `observacoes`",
        ],
    ];

    foreach ($steps as $step) {
        try {
            $pdo->exec($step['sql']);
            $log->success($step['label']);
        } catch (PDOException $e) {
            $msg   = $e->getMessage();
            $code  = $e->getCode();
            $isDup = in_array($code, ['42S21', '42S01'], true)
                || str_contains($msg, 'Duplicate')
                || str_contains($msg, 'already exists')
                || str_contains($msg, 'already defined');
            if ($isDup) {
                $log->info($step['label'] . ' (já existe, ignorado)');
            } else {
                $log->error($step['label'] . ': ' . $msg);
                $log->close();
                exit(1);
            }
        }
    }
}

// ─── Validação de linhas ──────────────────────────────────────────────────────

/**
 * @return array{0: array<int,array<string,mixed>>, 1: int}  [records, skippedCount]
 */
function validateRows(array $dataRows, array $colMap, Logger $log): array
{
    $records   = [];
    $skipped   = 0;
    $seenNomes = [];

    foreach ($dataRows as $i => $raw) {
        $lineNum = $i + 2; // linha 1 = cabeçalho, dados começam na 2

        // Pular linha em branco
        if (empty(array_filter(array_map('trim', $raw)))) {
            continue;
        }

        // Extrair campos canônicos
        $r = [];
        foreach ($colMap as $canonical => $idx) {
            $r[$canonical] = isset($raw[$idx]) ? trim($raw[$idx]) : '';
        }

        $errors = [];
        $warns  = [];

        // nome obrigatório
        $nome = trim($r['nome'] ?? '');
        if ($nome === '') {
            $errors[] = 'nome vazio';
        }

        // unidade_medida obrigatória e válida
        $unit = normUnit($r['unidade_medida'] ?? '');
        if ($unit === '') {
            $errors[] = 'unidade_medida vazia';
        } elseif (!in_array($unit, VALID_UNITS, true)) {
            $errors[] = "unidade inválida: \"{$unit}\" — aceitas: " . implode(', ', VALID_UNITS);
        }

        // estoque: "-" → 0, negativo → 0
        $estoqueRaw = $r['estoque_atual'] ?? '';
        $estoque    = sanitizeStock($estoqueRaw);
        if ($estoqueRaw !== '' && $estoqueRaw !== '-') {
            $ef = parseFloat($estoqueRaw);
            if ($ef !== null && $ef < 0) {
                $warns[] = "estoque negativo ({$estoqueRaw}) convertido para 0";
            }
        }

        // preço unitário de compra
        $preco    = null;
        $precoRaw = $r['compra_preco_unitario'] ?? '';
        if ($precoRaw !== '') {
            $preco = parseFloat($precoRaw);
            if ($preco === null) {
                $warns[] = "preço inválido \"{$precoRaw}\" → campo ignorado";
            }
        }

        // quantidade de compra
        $compraQtd = null;
        $qtdRaw    = $r['compra_quantidade'] ?? '';
        if ($qtdRaw !== '') {
            $compraQtd = parseFloat($qtdRaw);
            if ($compraQtd === null) {
                $warns[] = "quantidade inválida \"{$qtdRaw}\" → campo ignorado";
            }
        }

        // data de conferência (DD/MM/YYYY)
        $data    = null;
        $dataRaw = $r['data_conferencia'] ?? '';
        if ($dataRaw !== '') {
            $data = parseDate($dataRaw);
            if ($data === null) {
                $warns[] = "data inválida \"{$dataRaw}\" → campo ignorado";
            }
        }

        // Duplicata por nome dentro do arquivo
        $nomeKey = mb_strtolower($nome, 'UTF-8');
        if ($nome !== '' && isset($seenNomes[$nomeKey])) {
            $errors[] = "nome duplicado — primeira ocorrência na linha {$seenNomes[$nomeKey]}";
        } elseif ($nome !== '') {
            $seenNomes[$nomeKey] = $lineNum;
        }

        foreach ($errors as $e) {
            $log->error("Linha {$lineNum}: {$e}");
        }
        foreach ($warns as $w) {
            $log->warn("Linha {$lineNum}: {$w}");
        }

        if (!empty($errors)) {
            $skipped++;
            continue;
        }

        $records[] = [
            'linha'                 => $lineNum,
            'nome'                  => $nome,
            'categoria'             => trim($r['categoria'] ?? ''),
            'unidade_medida'        => $unit,
            'estoque_atual'         => $estoque,
            'compra_preco_unitario' => $preco,
            'compra_quantidade'     => $compraQtd,
            'data_conferencia'      => $data,
            'observacoes'           => trim($r['observacoes'] ?? ''),
        ];
    }

    return [$records, $skipped];
}

// ─── Limpeza ──────────────────────────────────────────────────────────────────

function runCleanup(PDO $pdo, Logger $log): void
{
    // 1. Remover fichas_tecnicas (FK referencia insumos)
    $log->info('Removendo registros de fichas_tecnicas...');
    try {
        $exists = $pdo->query("SHOW TABLES LIKE 'fichas_tecnicas'")->fetch();
        if ($exists) {
            $n = $pdo->exec("DELETE FROM fichas_tecnicas");
            $log->success("fichas_tecnicas: {$n} registro(s) removido(s).");
        } else {
            $log->info('fichas_tecnicas não encontrada, pulando.');
        }
    } catch (PDOException $e) {
        $log->warn('fichas_tecnicas: ' . $e->getMessage());
    }

    // 2. Truncar insumos (tem FK tipo_id → categorias_insumos)
    $log->info('Limpando insumos...');
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('TRUNCATE TABLE insumos');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $log->success('insumos limpa.');
    } catch (PDOException $e) {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $log->error('Erro ao limpar insumos: ' . $e->getMessage());
    }

    // 3. Truncar categorias_insumos
    $log->info('Limpando categorias_insumos...');
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('TRUNCATE TABLE categorias_insumos');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $log->success('categorias_insumos limpa.');
    } catch (PDOException $e) {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $log->error('Erro ao limpar categorias_insumos: ' . $e->getMessage());
    }
}

// ─── Importação ───────────────────────────────────────────────────────────────

/**
 * @return array{inseridos: int, categorias: int, compras: int, erros: int}
 */
function runImport(PDO $pdo, Logger $log, array $records): array
{
    $stats    = ['inseridos' => 0, 'categorias' => 0, 'compras' => 0, 'erros' => 0];
    $catCache = []; // nome_lower => id, evita INSERTs repetidos de categoria
    $total    = count($records);

    try {
        $pdo->beginTransaction();

        foreach ($records as $i => $rec) {
            fwrite(STDOUT, progressBar($i + 1, $total));

            try {
                // 1. UPSERT categoria (se informada)
                $tipoId = null;
                if ($rec['categoria'] !== '') {
                    $tipoId = upsertCategoria($pdo, $rec['categoria'], $catCache, $stats);
                }

                // 2. INSERT insumo
                $insumoId = insertInsumo($pdo, $rec, $tipoId);
                $stats['inseridos']++;

                // 3. INSERT compras_insumos (apenas se houver preço)
                if ($rec['compra_preco_unitario'] !== null && $rec['compra_preco_unitario'] > 0) {
                    insertCompra($pdo, $insumoId, $rec);
                    $stats['compras']++;

                    // Atualiza custo_medio no insumo com o preço da compra
                    $pdo->prepare("UPDATE insumos SET custo_medio = ? WHERE id = ?")
                        ->execute([$rec['compra_preco_unitario'], $insumoId]);
                }

            } catch (PDOException $e) {
                $stats['erros']++;
                fwrite(STDOUT, "\n");
                $log->error("Linha {$rec['linha']}: " . $e->getMessage());
            }
        }

        $pdo->commit();
        fwrite(STDOUT, "\n");
        $log->success('Transação confirmada.');

    } catch (PDOException $e) {
        $pdo->rollBack();
        fwrite(STDOUT, "\n");
        $log->error('Transação revertida: ' . $e->getMessage());
        $stats['erros']++;
    }

    return $stats;
}

function upsertCategoria(PDO $pdo, string $nome, array &$cache, array &$stats): int
{
    $key = mb_strtolower($nome, 'UTF-8');
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $stmt = $pdo->prepare("SELECT id FROM categorias_insumos WHERE LOWER(nome) = LOWER(?) LIMIT 1");
    $stmt->execute([$nome]);
    $row = $stmt->fetch();

    if ($row) {
        $id = (int)$row['id'];
    } else {
        $ins = $pdo->prepare("INSERT INTO categorias_insumos (nome) VALUES (?)");
        $ins->execute([$nome]);
        $id = (int)$pdo->lastInsertId();
        $stats['categorias']++;
    }

    $cache[$key] = $id;
    return $id;
}

function insertInsumo(PDO $pdo, array $rec, ?int $tipoId): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO insumos
            (tipo_id, nome, unidade_medida, fornecedor, custo_medio,
             estoque_atual, estoque_minimo, observacoes, data_conferencia, ativo)
         VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?, 1)"
    );
    $stmt->execute([
        $tipoId,
        $rec['nome'],
        $rec['unidade_medida'],
        FORNECEDOR_PADRAO,
        $rec['estoque_atual'],
        ESTOQUE_MINIMO_PADRAO,
        $rec['observacoes'] !== '' ? $rec['observacoes'] : null,
        $rec['data_conferencia'],
    ]);
    return (int)$pdo->lastInsertId();
}

function insertCompra(PDO $pdo, int $insumoId, array $rec): void
{
    $preco  = (float)$rec['compra_preco_unitario'];
    $qtd    = $rec['compra_quantidade'] ?? 1.0;
    $qtd    = $qtd !== null ? (float)$qtd : 1.0;
    $total  = $qtd * $preco;
    $data   = $rec['data_conferencia'] ?? date('Y-m-d');

    $stmt = $pdo->prepare(
        "INSERT INTO compras_insumos
            (data_compra, fornecedor, insumo_id, quantidade, valor_unitario,
             valor_total, custo_medio_ant, custo_medio_novo, observacoes)
         VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)"
    );
    $stmt->execute([
        $data,
        FORNECEDOR_PADRAO,
        $insumoId,
        $qtd,
        $preco,
        $total,
        $preco,
        $rec['observacoes'] !== '' ? $rec['observacoes'] : null,
    ]);
}
