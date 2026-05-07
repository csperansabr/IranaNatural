<?php
namespace App\Core;

use App\Core\Database;
use App\Core\Helper;

class Importador
{
    // ──────────────────────────────────────────────────────────────────────────
    // Header aliases: maps raw column names → canonical key per entity
    // ──────────────────────────────────────────────────────────────────────────
    private static array $colAliases = [
        'produtos' => [
            'nome'               => ['nome', 'name', 'produto'],
            'sku'                => ['sku', 'cod', 'codigo', 'code'],
            'categoria'          => ['categoria', 'category', 'cat'],
            'descricao_curta'    => ['descricao_curta', 'descricao curta', 'resumo'],
            'descricao_completa' => ['descricao_completa', 'descricao completa', 'descricao'],
            'modo_uso'           => ['modo_uso', 'modo de uso', 'instrucoes'],
            'cuidados'           => ['cuidados', 'cuidados e avisos', 'avisos'],
            'preco_venda'        => ['preco_venda', 'preco', 'price', 'valor'],
            'margem_desejada'    => ['margem_desejada', 'margem', 'margin'],
            'estoque_atual'      => ['estoque_atual', 'estoque', 'stock', 'quantidade'],
            'estoque_minimo'     => ['estoque_minimo', 'estoque minimo'],
            'tags'               => ['tags', 'palavras-chave', 'keywords'],
            'seo_titulo'         => ['seo_titulo', 'seo titulo', 'titulo seo'],
            'seo_descricao'      => ['seo_descricao', 'seo descricao', 'meta descricao'],
            'ativo'              => ['ativo', 'status', 'publicado', 'active'],
        ],
        'insumos' => [
            'nome'          => ['nome', 'name', 'insumo', 'material'],
            'descricao'     => ['descricao', 'description', 'obs'],
            'unidade_medida'=> ['unidade_medida', 'unidade', 'unit', 'un', 'medida'],
            'fornecedor'    => ['fornecedor', 'supplier', 'vendor'],
            'custo_medio'   => ['custo_medio', 'custo', 'cost', 'preco'],
            'estoque_atual' => ['estoque_atual', 'estoque', 'stock', 'quantidade'],
            'estoque_minimo'=> ['estoque_minimo', 'estoque minimo'],
        ],
        'estoque' => [
            'identificador' => ['identificador', 'sku', 'nome', 'produto', 'name', 'codigo'],
            'tipo'          => ['tipo', 'type', 'entidade'],
            'quantidade'    => ['quantidade', 'qty', 'qtd', 'amount', 'estoque'],
            'observacao'    => ['observacao', 'obs', 'notes'],
        ],
    ];

    private static array $requiredCols = [
        'produtos' => ['nome', 'categoria', 'preco_venda'],
        'insumos'  => ['nome', 'unidade_medida'],
        'estoque'  => ['identificador', 'tipo', 'quantidade'],
    ];

    private static array $validUnidades = ['kg', 'g', 'mg', 'l', 'ml', 'un', 'pct', 'cx'];

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC: mapHeaders
    // Map raw header row to canonical column names.
    // Returns: ['map' => [rawIndex => canonicalName], 'missing' => [required cols not found]]
    // ──────────────────────────────────────────────────────────────────────────
    public static function mapHeaders(string $entidade, array $headerRow): array
    {
        $aliases  = self::$colAliases[$entidade] ?? [];
        $required = self::$requiredCols[$entidade] ?? [];
        $map      = [];

        foreach ($headerRow as $idx => $rawHeader) {
            $norm = self::normHeader($rawHeader);
            foreach ($aliases as $canonical => $aliasList) {
                foreach ($aliasList as $alias) {
                    if (self::normHeader($alias) === $norm) {
                        $map[$idx] = $canonical;
                        break 2;
                    }
                }
            }
        }

        $foundCanonicals = array_values($map);
        $missing = [];
        foreach ($required as $req) {
            if (!in_array($req, $foundCanonicals, true)) {
                $missing[] = $req;
            }
        }

        return ['map' => $map, 'missing' => $missing];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC: validar
    // Full preview: validate all rows, return per-row status for UI.
    // Returns: ['headerError'=>null|string, 'cols'=>[...], 'rows'=>[...], 'summary'=>[...]]
    // ──────────────────────────────────────────────────────────────────────────
    public static function validar(string $entidade, array $rows, string $modo): array
    {
        if (empty($rows)) {
            return [
                'headerError' => 'Arquivo vazio.',
                'cols'        => [],
                'rows'        => [],
                'summary'     => ['ok' => 0, 'warning' => 0, 'error' => 0, 'skip' => 0, 'total' => 0],
            ];
        }

        $headerRow = $rows[0];
        $mapResult = self::mapHeaders($entidade, $headerRow);

        if (!empty($mapResult['missing'])) {
            return [
                'headerError' => 'Colunas obrigatórias não encontradas: ' . implode(', ', $mapResult['missing']),
                'cols'        => [],
                'rows'        => [],
                'summary'     => ['ok' => 0, 'warning' => 0, 'error' => 0, 'skip' => 0, 'total' => 0],
            ];
        }

        $colMap = $mapResult['map'];
        // Canonical column names found, in order
        $cols = [];
        foreach ($headerRow as $idx => $raw) {
            $cols[] = $colMap[$idx] ?? $raw;
        }

        $db = self::db();

        // Pre-load categorias for produtos validation
        $categoriasMap = [];
        if ($entidade === 'produtos') {
            $stmt = $db->query("SELECT id, LOWER(nome) as nome_lower FROM categorias WHERE ativo = 1");
            foreach ($stmt->fetchAll() as $cat) {
                $categoriasMap[$cat['nome_lower']] = (int) $cat['id'];
            }
        }

        // Track duplicates within file (nome/sku)
        $seenNomes = [];
        $seenSkus  = [];

        $resultRows = [];
        $summary    = ['ok' => 0, 'warning' => 0, 'error' => 0, 'skip' => 0, 'total' => 0];

        for ($i = 1; $i < count($rows); $i++) {
            $rawRow = $rows[$i];

            // Map raw cells to canonical columns
            $data = [];
            foreach ($colMap as $rawIdx => $canonical) {
                $data[$canonical] = isset($rawRow[$rawIdx]) ? trim($rawRow[$rawIdx]) : '';
            }

            $errors   = [];
            $warnings = [];
            $status   = 'ok';

            switch ($entidade) {
                case 'produtos':
                    [$errors, $warnings, $status] = self::validarProduto($data, $modo, $categoriasMap, $seenNomes, $seenSkus, $db);
                    break;
                case 'insumos':
                    [$errors, $warnings, $status] = self::validarInsumo($data, $modo, $seenNomes, $db);
                    break;
                case 'estoque':
                    [$errors, $warnings, $status] = self::validarEstoque($data, $modo, $db);
                    break;
            }

            // Track seen nomes/skus
            $nomeKey = mb_strtolower(trim($data['nome'] ?? $data['identificador'] ?? ''), 'UTF-8');
            if ($nomeKey !== '') {
                $seenNomes[$nomeKey] = ($seenNomes[$nomeKey] ?? 0) + 1;
            }
            if (isset($data['sku']) && $data['sku'] !== '') {
                $skuKey = mb_strtolower($data['sku'], 'UTF-8');
                $seenSkus[$skuKey] = ($seenSkus[$skuKey] ?? 0) + 1;
            }

            $summary[$status]++;
            $summary['total']++;

            $resultRows[] = [
                'linha'    => $i + 1, // 1-based display (header = row 1, first data = row 2)
                'data'     => $data,
                'raw'      => $rawRow,
                'status'   => $status,
                'errors'   => $errors,
                'warnings' => $warnings,
            ];
        }

        return [
            'headerError' => null,
            'cols'        => $cols,
            'rows'        => $resultRows,
            'summary'     => $summary,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC: processar
    // Process (import) all valid/warning rows.
    // Returns: ['inseridos'=>n, 'atualizados'=>n, 'erros'=>n, 'ignorados'=>n, 'detalhes'=>[...]]
    // ──────────────────────────────────────────────────────────────────────────
    public static function processar(string $entidade, array $rows, string $modo, int $importId): array
    {
        // Re-validate to get the per-row statuses (source of truth)
        $preview = self::validar($entidade, $rows, $modo);

        if ($preview['headerError']) {
            return ['inseridos' => 0, 'atualizados' => 0, 'erros' => 1, 'ignorados' => 0, 'detalhes' => [
                ['linha' => 0, 'status' => 'error', 'msg' => $preview['headerError']],
            ]];
        }

        $result = ['inseridos' => 0, 'atualizados' => 0, 'erros' => 0, 'ignorados' => 0, 'detalhes' => []];

        foreach ($preview['rows'] as $rowInfo) {
            if ($rowInfo['status'] === 'error' || $rowInfo['status'] === 'skip') {
                $result['ignorados']++;
                continue;
            }

            $data   = $rowInfo['data'];
            $linha  = (int) $rowInfo['linha'];
            $action = '';

            try {
                switch ($entidade) {
                    case 'produtos':
                        $action = self::processarProduto($data, $modo, $importId);
                        break;
                    case 'insumos':
                        $action = self::processarInsumo($data, $modo, $importId);
                        break;
                    case 'estoque':
                        $action = self::processarEstoque($data, $importId);
                        break;
                }

                if ($action === 'inserido') {
                    $result['inseridos']++;
                    $result['detalhes'][] = ['linha' => $linha, 'status' => 'ok', 'msg' => 'Inserido com sucesso.'];
                } elseif ($action === 'atualizado') {
                    $result['atualizados']++;
                    $result['detalhes'][] = ['linha' => $linha, 'status' => 'ok', 'msg' => 'Atualizado com sucesso.'];
                } elseif ($action === 'ignorado') {
                    $result['ignorados']++;
                    $result['detalhes'][] = ['linha' => $linha, 'status' => 'skip', 'msg' => 'Ignorado (modo de importação).'];
                } else {
                    $result['ignorados']++;
                }
            } catch (\Exception $e) {
                $result['erros']++;
                $msg = $e->getMessage();
                $result['detalhes'][] = ['linha' => $linha, 'status' => 'error', 'msg' => $msg];
                // Log to import_errors
                try {
                    $histModel = new \App\Models\ImportHistory();
                    $histModel->addErro($importId, $linha, '', '', $msg);
                } catch (\Exception $ex) {
                    // Silently ignore logging errors
                }
            }
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC: gerarTemplate
    // Generate CSV template content for download.
    // ──────────────────────────────────────────────────────────────────────────
    public static function gerarTemplate(string $entidade): string
    {
        switch ($entidade) {
            case 'produtos':
                return self::templateProdutos();
            case 'insumos':
                return self::templateInsumos();
            case 'estoque':
                return self::templateEstoque();
            default:
                return '';
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE: Per-entity validation helpers
    // Each returns: [$errors, $warnings, $status]
    // ──────────────────────────────────────────────────────────────────────────

    private static function validarProduto(
        array $data,
        string $modo,
        array $categoriasMap,
        array &$seenNomes,
        array &$seenSkus,
        \PDO $db
    ): array {
        $errors   = [];
        $warnings = [];
        $status   = 'ok';

        $nome  = $data['nome'] ?? '';
        $sku   = $data['sku']  ?? '';
        $cat   = $data['categoria'] ?? '';
        $preco = $data['preco_venda'] ?? '';

        // Required: nome
        if (trim($nome) === '') {
            $errors[] = ['campo' => 'nome', 'msg' => 'Nome é obrigatório.'];
        }

        // Required: categoria
        if (trim($cat) === '') {
            $errors[] = ['campo' => 'categoria', 'msg' => 'Categoria é obrigatória.'];
        } else {
            $catKey = mb_strtolower(trim($cat), 'UTF-8');
            if (!isset($categoriasMap[$catKey])) {
                $errors[] = ['campo' => 'categoria', 'msg' => "Categoria \"{$cat}\" não encontrada no sistema."];
            }
        }

        // Required: preco_venda — must be positive number
        if (trim($preco) === '') {
            $errors[] = ['campo' => 'preco_venda', 'msg' => 'Preço de venda é obrigatório.'];
        } else {
            $precoVal = self::toFloat($preco);
            if ($precoVal === null || $precoVal <= 0) {
                $errors[] = ['campo' => 'preco_venda', 'msg' => "Preço de venda inválido: \"{$preco}\". Use formato numérico positivo."];
            }
        }

        // Optional: margem_desejada
        if (isset($data['margem_desejada']) && $data['margem_desejada'] !== '') {
            $margem = self::toFloat($data['margem_desejada']);
            if ($margem === null || $margem < 0) {
                $warnings[] = ['campo' => 'margem_desejada', 'msg' => "Margem inválida: \"{$data['margem_desejada']}\". Será ignorada."];
            }
        }

        // Optional: estoque_atual — must be >= 0
        if (isset($data['estoque_atual']) && $data['estoque_atual'] !== '') {
            $estoque = self::toInt($data['estoque_atual']);
            if ($estoque === null || $estoque < 0) {
                $warnings[] = ['campo' => 'estoque_atual', 'msg' => "Estoque inválido: \"{$data['estoque_atual']}\". Será ignorado (usará 0)."];
            }
        }

        // Optional: estoque_minimo — must be >= 0
        if (isset($data['estoque_minimo']) && $data['estoque_minimo'] !== '') {
            $estoqueMin = self::toInt($data['estoque_minimo']);
            if ($estoqueMin === null || $estoqueMin < 0) {
                $warnings[] = ['campo' => 'estoque_minimo', 'msg' => "Estoque mínimo inválido. Será ignorado (usará 0)."];
            }
        }

        // Duplicate nome within file
        $nomeKey = mb_strtolower(trim($nome), 'UTF-8');
        if ($nomeKey !== '' && ($seenNomes[$nomeKey] ?? 0) >= 1) {
            $warnings[] = ['campo' => 'nome', 'msg' => "Nome duplicado no arquivo: \"{$nome}\"."];
        }

        // Duplicate SKU within file
        if ($sku !== '') {
            $skuKey = mb_strtolower($sku, 'UTF-8');
            if (($seenSkus[$skuKey] ?? 0) >= 1) {
                $warnings[] = ['campo' => 'sku', 'msg' => "SKU duplicado no arquivo: \"{$sku}\"."];
            }
        }

        // Check existence in DB for skip logic
        if (!empty($errors)) {
            $status = 'error';
        } else {
            $exists = self::produtoExiste($sku, $nome, $db);
            if ($modo === 'criar' && $exists) {
                $status = 'skip';
            } elseif ($modo === 'atualizar' && !$exists) {
                $status = 'skip';
            } elseif (!empty($warnings)) {
                $status = 'warning';
            }
        }

        return [$errors, $warnings, $status];
    }

    private static function validarInsumo(
        array $data,
        string $modo,
        array &$seenNomes,
        \PDO $db
    ): array {
        $errors   = [];
        $warnings = [];
        $status   = 'ok';

        $nome   = $data['nome'] ?? '';
        $un     = $data['unidade_medida'] ?? '';
        $custo  = $data['custo_medio'] ?? '';

        // Required: nome
        if (trim($nome) === '') {
            $errors[] = ['campo' => 'nome', 'msg' => 'Nome é obrigatório.'];
        }

        // Required: unidade_medida
        if (trim($un) === '') {
            $errors[] = ['campo' => 'unidade_medida', 'msg' => 'Unidade de medida é obrigatória.'];
        } else {
            $unNorm = mb_strtolower(trim($un), 'UTF-8');
            if (!in_array($unNorm, self::$validUnidades, true)) {
                $errors[] = ['campo' => 'unidade_medida', 'msg' => "Unidade \"{$un}\" inválida. Valores aceitos: " . implode(', ', self::$validUnidades)];
            }
        }

        // Optional: custo_medio — must be positive or zero
        if ($custo !== '') {
            $custoVal = self::toFloat($custo);
            if ($custoVal === null || $custoVal < 0) {
                $warnings[] = ['campo' => 'custo_medio', 'msg' => "Custo inválido: \"{$custo}\". Será ignorado (usará 0)."];
            }
        }

        // Optional: estoque_atual — can be 0, not negative
        if (isset($data['estoque_atual']) && $data['estoque_atual'] !== '') {
            $estq = self::toFloat($data['estoque_atual']);
            if ($estq === null || $estq < 0) {
                $warnings[] = ['campo' => 'estoque_atual', 'msg' => "Estoque inválido: \"{$data['estoque_atual']}\". Será ignorado (usará 0)."];
            }
        }

        // Optional: estoque_minimo — can be 0, not negative
        if (isset($data['estoque_minimo']) && $data['estoque_minimo'] !== '') {
            $estqMin = self::toFloat($data['estoque_minimo']);
            if ($estqMin === null || $estqMin < 0) {
                $warnings[] = ['campo' => 'estoque_minimo', 'msg' => "Estoque mínimo inválido. Será ignorado (usará 0)."];
            }
        }

        // Duplicate nome within file
        $nomeKey = mb_strtolower(trim($nome), 'UTF-8');
        if ($nomeKey !== '' && ($seenNomes[$nomeKey] ?? 0) >= 1) {
            $warnings[] = ['campo' => 'nome', 'msg' => "Nome duplicado no arquivo: \"{$nome}\"."];
        }

        if (!empty($errors)) {
            $status = 'error';
        } else {
            $exists = self::insumoExiste($nome, $db);
            if ($modo === 'criar' && $exists) {
                $status = 'skip';
            } elseif ($modo === 'atualizar' && !$exists) {
                $status = 'skip';
            } elseif (!empty($warnings)) {
                $status = 'warning';
            }
        }

        return [$errors, $warnings, $status];
    }

    private static function validarEstoque(array $data, string $modo, \PDO $db): array
    {
        $errors   = [];
        $warnings = [];
        $status   = 'ok';

        $identificador = $data['identificador'] ?? '';
        $tipo          = $data['tipo'] ?? '';
        $quantidade    = $data['quantidade'] ?? '';

        // Required: identificador
        if (trim($identificador) === '') {
            $errors[] = ['campo' => 'identificador', 'msg' => 'Identificador (SKU ou nome) é obrigatório.'];
        }

        // Required: tipo — must be 'produto' or 'insumo'
        $tipoNorm = mb_strtolower(trim($tipo), 'UTF-8');
        if (trim($tipo) === '') {
            $errors[] = ['campo' => 'tipo', 'msg' => 'Tipo é obrigatório (produto ou insumo).'];
        } elseif (!in_array($tipoNorm, ['produto', 'insumo'], true)) {
            $errors[] = ['campo' => 'tipo', 'msg' => "Tipo inválido: \"{$tipo}\". Use \"produto\" ou \"insumo\"."];
        }

        // Required: quantidade — numeric, can be 0, not negative
        if (trim($quantidade) === '') {
            $errors[] = ['campo' => 'quantidade', 'msg' => 'Quantidade é obrigatória.'];
        } else {
            $qtdVal = self::toFloat($quantidade);
            if ($qtdVal === null || $qtdVal < 0) {
                $errors[] = ['campo' => 'quantidade', 'msg' => "Quantidade inválida: \"{$quantidade}\". Deve ser um número não-negativo."];
            }
        }

        // Check that the referenced entity actually exists
        if (empty($errors) && trim($identificador) !== '' && in_array($tipoNorm, ['produto', 'insumo'], true)) {
            $exists = self::entidadeEstoqueExiste($identificador, $tipoNorm, $db);
            if (!$exists) {
                $warnings[] = ['campo' => 'identificador', 'msg' => "Registro \"{$identificador}\" não encontrado na tabela de {$tipo}s. Será ignorado."];
                $status = 'skip';
            }
        }

        if (!empty($errors)) {
            $status = 'error';
        } elseif ($status !== 'skip' && !empty($warnings)) {
            $status = 'warning';
        }

        return [$errors, $warnings, $status];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE: Per-entity processing helpers
    // ──────────────────────────────────────────────────────────────────────────

    private static function processarProduto(array $data, string $modo, int $importId): string
    {
        $db = self::db();

        $nome     = trim($data['nome'] ?? '');
        $sku      = trim($data['sku'] ?? '');
        $catNome  = trim($data['categoria'] ?? '');

        // Resolve categoria_id
        $stmt = $db->prepare("SELECT id FROM categorias WHERE LOWER(nome) = LOWER(?) AND ativo = 1 LIMIT 1");
        $stmt->execute([$catNome]);
        $catRow = $stmt->fetch();
        if (!$catRow) {
            throw new \RuntimeException("Categoria \"{$catNome}\" não encontrada.");
        }
        $categoriaId = (int) $catRow['id'];

        // Find existing record
        $existingId = null;
        if ($sku !== '') {
            $stmt = $db->prepare("SELECT id FROM produtos WHERE sku = ? LIMIT 1");
            $stmt->execute([$sku]);
            $row = $stmt->fetch();
            if ($row) $existingId = (int) $row['id'];
        }
        if ($existingId === null) {
            $slugBase = Helper::slug($nome);
            $stmt = $db->prepare("SELECT id FROM produtos WHERE slug = ? LIMIT 1");
            $stmt->execute([$slugBase]);
            $row = $stmt->fetch();
            if ($row) $existingId = (int) $row['id'];
        }

        // Parse fields
        $precoVenda     = self::toFloat($data['preco_venda'] ?? '') ?? 0.0;
        $margemDesejada = self::toFloat($data['margem_desejada'] ?? '') ?? 0.0;
        $estoqueAtual   = max(0, self::toInt($data['estoque_atual'] ?? '') ?? 0);
        $estoqueMinimo  = max(0, self::toInt($data['estoque_minimo'] ?? '') ?? 0);
        // Treat empty ativo as active (default 1); only apply false when explicitly set
        $ativoRaw = $data['ativo'] ?? '';
        $ativo    = ($ativoRaw !== '') ? self::toBool($ativoRaw) : 1;

        $fields = [
            'categoria_id'       => $categoriaId,
            'nome'               => $nome,
            'descricao_curta'    => $data['descricao_curta']    ?? '',
            'descricao_completa' => $data['descricao_completa'] ?? '',
            'modo_uso'           => $data['modo_uso']           ?? '',
            'cuidados'           => $data['cuidados']           ?? '',
            'seo_titulo'         => $data['seo_titulo']         ?? '',
            'seo_descricao'      => $data['seo_descricao']      ?? '',
            'tags'               => $data['tags']               ?? '',
            'preco_venda'        => $precoVenda,
            'margem_desejada'    => $margemDesejada,
            'estoque_minimo'     => $estoqueMinimo,
            'ativo'              => $ativo,
        ];
        if ($sku !== '') {
            $fields['sku'] = $sku;
        }

        if ($existingId === null) {
            // INSERT
            if ($modo === 'atualizar') return 'ignorado';

            $slug = self::gerarSlugUnico($nome, $db);
            $fields['slug']          = $slug;
            $fields['estoque_atual'] = $estoqueAtual;

            $cols   = implode(', ', array_keys($fields));
            $places = implode(', ', array_fill(0, count($fields), '?'));
            $stmt   = $db->prepare("INSERT INTO produtos ({$cols}) VALUES ({$places})");
            $stmt->execute(array_values($fields));
            return 'inserido';
        } else {
            // UPDATE
            if ($modo === 'criar') return 'ignorado';

            // Don't change slug on update
            unset($fields['slug']);
            $fields['estoque_atual'] = $estoqueAtual;

            $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($fields)));
            $stmt = $db->prepare("UPDATE produtos SET {$sets} WHERE id = ?");
            $stmt->execute([...array_values($fields), $existingId]);
            return 'atualizado';
        }
    }

    private static function processarInsumo(array $data, string $modo, int $importId): string
    {
        $db   = self::db();
        $nome = trim($data['nome'] ?? '');
        $un   = mb_strtolower(trim($data['unidade_medida'] ?? ''), 'UTF-8');

        // Find existing
        $stmt = $db->prepare("SELECT id, estoque_atual FROM insumos WHERE LOWER(nome) = LOWER(?) LIMIT 1");
        $stmt->execute([$nome]);
        $existing = $stmt->fetch();
        $existingId = $existing ? (int) $existing['id'] : null;

        $custoMedio    = max(0.0, self::toFloat($data['custo_medio']    ?? '') ?? 0.0);
        $estoqueAtual  = max(0.0, self::toFloat($data['estoque_atual']  ?? '') ?? 0.0);
        $estoqueMinimo = max(0.0, self::toFloat($data['estoque_minimo'] ?? '') ?? 0.0);

        $fields = [
            'nome'          => $nome,
            'descricao'     => $data['descricao']  ?? '',
            'unidade_medida'=> $un,
            'fornecedor'    => $data['fornecedor'] ?? '',
            'custo_medio'   => $custoMedio,
            'estoque_atual' => $estoqueAtual,
            'estoque_minimo'=> $estoqueMinimo,
        ];

        if ($existingId === null) {
            if ($modo === 'atualizar') return 'ignorado';

            $cols   = implode(', ', array_keys($fields));
            $places = implode(', ', array_fill(0, count($fields), '?'));
            $stmt   = $db->prepare("INSERT INTO insumos ({$cols}) VALUES ({$places})");
            $stmt->execute(array_values($fields));
            return 'inserido';
        } else {
            if ($modo === 'criar') return 'ignorado';

            $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($fields)));
            $stmt = $db->prepare("UPDATE insumos SET {$sets} WHERE id = ?");
            $stmt->execute([...array_values($fields), $existingId]);
            return 'atualizado';
        }
    }

    private static function processarEstoque(array $data, int $importId): string
    {
        $db            = self::db();
        $identificador = trim($data['identificador'] ?? '');
        $tipo          = mb_strtolower(trim($data['tipo'] ?? ''), 'UTF-8');
        $quantidade    = self::toFloat($data['quantidade'] ?? '') ?? 0.0;
        $observacao    = trim($data['observacao'] ?? '');

        if ($tipo === 'produto') {
            // Look up by sku first, then by nome
            $stmt = $db->prepare("SELECT id, estoque_atual FROM produtos WHERE sku = ? LIMIT 1");
            $stmt->execute([$identificador]);
            $row = $stmt->fetch();

            if (!$row) {
                $stmt = $db->prepare("SELECT id, estoque_atual FROM produtos WHERE LOWER(nome) = LOWER(?) LIMIT 1");
                $stmt->execute([$identificador]);
                $row = $stmt->fetch();
            }

            if (!$row) {
                throw new \RuntimeException("Produto \"{$identificador}\" não encontrado.");
            }

            $produtoId   = (int) $row['id'];
            $saldoAntes  = (int) $row['estoque_atual'];
            $novoEstoque = (int) round($quantidade);

            $stmt = $db->prepare("UPDATE produtos SET estoque_atual = ? WHERE id = ?");
            $stmt->execute([$novoEstoque, $produtoId]);

            // Record movement
            $stmt = $db->prepare(
                "INSERT INTO mov_produtos (produto_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, ref_id, observacoes)
                 VALUES (?, 'ajuste', ?, ?, ?, 'importacao', ?, ?)"
            );
            $stmt->execute([$produtoId, $novoEstoque - $saldoAntes, $saldoAntes, $novoEstoque, $importId, $observacao]);
        } else {
            // Insumo — look up by nome
            $stmt = $db->prepare("SELECT id, estoque_atual FROM insumos WHERE LOWER(nome) = LOWER(?) LIMIT 1");
            $stmt->execute([$identificador]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new \RuntimeException("Insumo \"{$identificador}\" não encontrado.");
            }

            $insumoId    = (int) $row['id'];
            $saldoAntes  = (float) $row['estoque_atual'];
            $novoEstoque = $quantidade;

            $stmt = $db->prepare("UPDATE insumos SET estoque_atual = ? WHERE id = ?");
            $stmt->execute([$novoEstoque, $insumoId]);

            // Record movement
            $stmt = $db->prepare(
                "INSERT INTO mov_insumos (insumo_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, ref_id, observacoes)
                 VALUES (?, 'ajuste', ?, ?, ?, 'importacao', ?, ?)"
            );
            $stmt->execute([$insumoId, $novoEstoque - $saldoAntes, $saldoAntes, $novoEstoque, $importId, $observacao]);
        }

        return 'atualizado';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE: Template generators
    // ──────────────────────────────────────────────────────────────────────────

    private static function templateProdutos(): string
    {
        $rows = [
            '# Modelo de importação de Produtos — Iraná Natural',
            '# Colunas obrigatórias: nome, categoria, preco_venda',
            '# categoria: deve existir exatamente como cadastrado no sistema',
            '# ativo: sim/nao/1/0 | preco_venda e margem_desejada: use ponto ou virgula decimal',
            'nome,sku,categoria,descricao_curta,descricao_completa,modo_uso,cuidados,preco_venda,margem_desejada,estoque_atual,estoque_minimo,tags,seo_titulo,seo_descricao,ativo',
            '"Óleo Essencial de Lavanda","SKU-001","Óleos Essenciais","Óleo 100% puro para relaxamento e bem-estar.","Extraído por destilação a vapor das flores de lavanda...","Difuse de 3 a 5 gotas no difusor. Para uso tópico dilua com óleo carreador.","Não ingerir. Manter fora do alcance de crianças.","45,90","40","20","5","lavanda,relaxamento,aromaterapia","Óleo Essencial de Lavanda Puro 10ml","Óleo essencial de lavanda 100% puro para aromaterapia e relaxamento.","sim"',
            '"Shampoo Vegano de Argan","SKU-002","Cabelos","Shampoo nutritivo com óleo de argan marroquino.","Formulado com ingredientes naturais e óleo de argan puro...","Aplique nos cabelos molhados, massageie e enxágue.","Evitar contato com os olhos. Em caso de irritação, descontinue o uso.","38,50","35","15","3","shampoo,argan,vegano,natural","Shampoo Vegano de Argan Natural","Shampoo vegano nutritivo com óleo de argan para cabelos secos e danificados.","sim"',
        ];
        return implode("\n", $rows) . "\n";
    }

    private static function templateInsumos(): string
    {
        $rows = [
            '# Modelo de importação de Insumos (matérias-primas) — Iraná Natural',
            '# Colunas obrigatórias: nome, unidade_medida',
            '# unidade_medida: kg, g, mg, l, ml, un, pct, cx',
            '# custo_medio, estoque_atual, estoque_minimo: use ponto ou virgula decimal',
            'nome,descricao,unidade_medida,fornecedor,custo_medio,estoque_atual,estoque_minimo',
            '"Óleo de Argan","Óleo de argan marroquino prensado a frio, grau cosmético","ml","Importadora Natural LTDA","0,85","500","100"',
            '"Lavanda Seca","Flores de lavanda secas para extração de óleo essencial","kg","Ervas & Cia","32,50","2","0,5"',
        ];
        return implode("\n", $rows) . "\n";
    }

    private static function templateEstoque(): string
    {
        $rows = [
            '# Modelo de importação de Estoque — Iraná Natural',
            '# Colunas obrigatórias: identificador, tipo, quantidade',
            '# identificador: SKU do produto ou nome do produto/insumo',
            '# tipo: produto ou insumo',
            '# quantidade: valor absoluto que será definido como estoque atual',
            'identificador,tipo,quantidade,observacao',
            '"SKU-001","produto","20","Ajuste de inventário mensal"',
            '"Óleo de Argan","insumo","500","Conferência de estoque - recebimento de pedido"',
        ];
        return implode("\n", $rows) . "\n";
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE: DB lookup helpers
    // ──────────────────────────────────────────────────────────────────────────

    private static function produtoExiste(string $sku, string $nome, \PDO $db): bool
    {
        if ($sku !== '') {
            $stmt = $db->prepare("SELECT id FROM produtos WHERE sku = ? LIMIT 1");
            $stmt->execute([$sku]);
            if ($stmt->fetch()) return true;
        }
        if ($nome !== '') {
            $slugBase = Helper::slug($nome);
            $stmt = $db->prepare("SELECT id FROM produtos WHERE slug = ? LIMIT 1");
            $stmt->execute([$slugBase]);
            if ($stmt->fetch()) return true;
        }
        return false;
    }

    private static function insumoExiste(string $nome, \PDO $db): bool
    {
        if ($nome === '') return false;
        $stmt = $db->prepare("SELECT id FROM insumos WHERE LOWER(nome) = LOWER(?) LIMIT 1");
        $stmt->execute([$nome]);
        return (bool) $stmt->fetch();
    }

    private static function entidadeEstoqueExiste(string $identificador, string $tipo, \PDO $db): bool
    {
        if ($tipo === 'produto') {
            $stmt = $db->prepare("SELECT id FROM produtos WHERE sku = ? LIMIT 1");
            $stmt->execute([$identificador]);
            if ($stmt->fetch()) return true;
            $stmt = $db->prepare("SELECT id FROM produtos WHERE LOWER(nome) = LOWER(?) LIMIT 1");
            $stmt->execute([$identificador]);
            return (bool) $stmt->fetch();
        } else {
            $stmt = $db->prepare("SELECT id FROM insumos WHERE LOWER(nome) = LOWER(?) LIMIT 1");
            $stmt->execute([$identificador]);
            return (bool) $stmt->fetch();
        }
    }

    private static function gerarSlugUnico(string $nome, \PDO $db): string
    {
        $base = Helper::slug($nome);
        $slug = $base;
        $n    = 2;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM produtos WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) break;
            $slug = $base . '-' . $n;
            $n++;
        }
        return $slug;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE: Conversion & normalisation helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Parse a price/float string: strip R$, spaces, dots (thousand sep), replace comma with period.
     * "R$ 12.500,99" → 12500.99
     */
    private static function toFloat(string $v): ?float
    {
        if (trim($v) === '') return null;

        // Remove R$, currency signs and leading/trailing spaces
        $v = preg_replace('/[R\$\s]+/', '', $v);
        $v = trim($v);

        if ($v === '' || $v === '-') return null;

        // Detect if format is 1.234,56 (BR) or 1,234.56 (EN)
        // Count dots and commas
        $dotCount   = substr_count($v, '.');
        $commaCount = substr_count($v, ',');

        if ($commaCount === 1 && $dotCount >= 1) {
            // Likely BR format: 1.234,56 → remove dots (thousands), replace comma with dot
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } elseif ($commaCount >= 1 && $dotCount === 0) {
            // Comma as decimal separator: 12,50 → 12.50
            if ($commaCount === 1) {
                $v = str_replace(',', '.', $v);
            } else {
                // Multiple commas → likely thousands commas, remove them
                // This handles "1,234,567" style
                $v = str_replace(',', '', $v);
            }
        } elseif ($dotCount >= 2) {
            // Multiple dots → thousand separators (EN style like 1.234.567)
            $v = str_replace('.', '', $v);
        }
        // else: single dot = standard decimal separator, leave as-is

        if (!is_numeric($v)) return null;
        return (float) $v;
    }

    /**
     * Parse an integer string. Returns null if not a valid integer.
     */
    private static function toInt(string $v): ?int
    {
        $f = self::toFloat($v);
        if ($f === null) return null;
        return (int) round($f);
    }

    /**
     * Parse boolean-like strings → 1 or 0.
     * Accepts: sim, s, yes, y, 1, true, ativo, publicado → 1; everything else → 0
     */
    private static function toBool(string $v): int
    {
        $norm = mb_strtolower(trim($v), 'UTF-8');
        return in_array($norm, ['sim', 's', 'yes', 'y', '1', 'true', 'ativo', 'publicado'], true) ? 1 : 0;
    }

    /**
     * Normalise a header string for alias matching.
     * Lowercase, trim, remove accents, replace spaces/hyphens with underscores, remove non-alphanumeric.
     */
    private static function normHeader(string $h): string
    {
        $h = mb_strtolower(trim($h), 'UTF-8');

        // Remove accents
        $map = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ];
        $h = strtr($h, $map);

        // Replace spaces and hyphens with underscores
        $h = preg_replace('/[\s\-]+/', '_', $h);

        // Remove non-alphanumeric and non-underscore characters
        $h = preg_replace('/[^a-z0-9_]/', '', $h);

        return $h;
    }

    /**
     * Get PDO instance.
     */
    private static function db(): \PDO
    {
        return Database::getInstance();
    }
}
