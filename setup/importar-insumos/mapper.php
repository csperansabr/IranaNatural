<?php
/**
 * Iraná Natural — Importador CLI de Insumos
 * Mapeador de colunas CSV → campos canônicos.
 */
declare(strict_types=1);

class CsvMapper
{
    /**
     * Aliases aceitos por campo canônico.
     * A coluna "Status" é intencionalmente omitida (P3=C: ignorada).
     */
    private static array $aliases = [
        'categoria'             => ['tipo', 'type', 'categoria', 'category', 'grupo', 'classificacao'],
        'nome'                  => ['insumo', 'nome', 'name', 'material', 'ingrediente', 'produto'],
        'compra_quantidade'     => ['quantidade', 'qty', 'qtd', 'amount', 'quant'],
        'unidade_medida'        => ['unidade', 'unit', 'un', 'medida', 'unidade medida'],
        'compra_preco_unitario' => ['preco', 'price', 'valor', 'custo', 'custo unitario', 'valor unitario'],
        'estoque_atual'         => ['saldo estoque', 'estoque', 'stock', 'saldo', 'estoque atual', 'quantidade em estoque'],
        'data_conferencia'      => ['data', 'date', 'data conferencia', 'data lancamento'],
        'observacoes'           => ['observacao', 'obs', 'notes', 'nota', 'observacoes', 'anotacao'],
    ];

    /** Campos que devem estar presentes no CSV */
    private static array $required = ['nome', 'unidade_medida'];

    /**
     * Mapeia cabeçalho CSV para campos canônicos.
     *
     * @param  array $headers  Linha de cabeçalho do CSV (strings brutas)
     * @return array{map: array<string,int>, missing: string[], extra: string[]}
     */
    public static function map(array $headers): array
    {
        $normHeaders = array_map([self::class, 'norm'], $headers);
        $map         = [];
        $missing     = [];

        foreach (self::$aliases as $canonical => $aliasList) {
            $normAliases = array_map([self::class, 'norm'], $aliasList);
            foreach ($normHeaders as $idx => $normH) {
                if (in_array($normH, $normAliases, true)) {
                    $map[$canonical] = $idx;
                    break;
                }
            }
            if (!array_key_exists($canonical, $map) && in_array($canonical, self::$required, true)) {
                $missing[] = $canonical;
            }
        }

        // Colunas CSV sem mapeamento
        $mappedIdx = array_values($map);
        $extra     = [];
        foreach ($headers as $idx => $h) {
            if (!in_array($idx, $mappedIdx, true) && trim($h) !== '') {
                $extra[] = $h;
            }
        }

        return ['map' => $map, 'missing' => $missing, 'extra' => $extra];
    }

    /**
     * Normaliza string de cabeçalho: minúsculas, sem acentos, espaços uniformes.
     * Underscores são tratados como espaços para permitir aliases como "saldo_estoque".
     */
    private static function norm(string $h): string
    {
        $h = mb_strtolower(trim($h), 'UTF-8');
        $h = strtr($h, [
            'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
            'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
            'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','ñ'=>'n',
        ]);
        $h = (string)preg_replace('/[_\s]+/', ' ', $h);
        return trim($h);
    }
}
