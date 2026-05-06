<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Helper;

class FichaTecnica extends Model
{
    protected string $table = 'fichas_tecnicas';

    public function dosProduto(int $produtoId): array
    {
        return $this->query(
            "SELECT ft.*, i.nome as insumo_nome, i.unidade_medida as insumo_unidade,
                    i.custo_medio, i.estoque_atual as insumo_estoque
             FROM fichas_tecnicas ft
             JOIN insumos i ON i.id = ft.insumo_id
             WHERE ft.produto_id = ?
             ORDER BY i.nome ASC",
            [$produtoId]
        );
    }

    // Verifica disponibilidade de insumos para produzir $qtd unidades do produto
    // Retorna array com status de cada insumo
    public function verificarDisponibilidade(int $produtoId, int $qtd): array
    {
        $itens      = $this->dosProduto($produtoId);
        $resultado  = [];
        $podeTotal  = true;

        foreach ($itens as $item) {
            $qtdNecessaria = (float)$item['quantidade'] * $qtd;
            // Converte para a unidade do insumo para comparar com estoque
            $qtdConvertida = Helper::convertUnit($qtdNecessaria, $item['unidade'], $item['insumo_unidade']);

            if ($qtdConvertida === null) {
                // Unidades incompatíveis — alerta mas não bloqueia
                $resultado[] = [
                    'insumo_nome'   => $item['insumo_nome'],
                    'necessario'    => $qtdNecessaria,
                    'unidade'       => $item['unidade'],
                    'disponivel'    => $item['insumo_estoque'],
                    'disponivel_un' => $item['insumo_unidade'],
                    'ok'            => false,
                    'aviso'         => 'Unidades incompatíveis — verifique manualmente.',
                ];
                $podeTotal = false;
                continue;
            }

            $ok = (float)$item['insumo_estoque'] >= $qtdConvertida;
            if (!$ok) $podeTotal = false;

            $resultado[] = [
                'insumo_nome'   => $item['insumo_nome'],
                'necessario'    => $qtdConvertida,
                'unidade'       => $item['insumo_unidade'],
                'disponivel'    => (float)$item['insumo_estoque'],
                'disponivel_un' => $item['insumo_unidade'],
                'ok'            => $ok,
                'aviso'         => $ok ? '' : 'Estoque insuficiente.',
            ];
        }

        return ['itens' => $resultado, 'pode_total' => $podeTotal];
    }

    // Debita insumos proporcionalmente para $qtd unidades
    public function debitarInsumos(int $produtoId, int $qtd, int $producaoId): float
    {
        $itens       = $this->dosProduto($produtoId);
        $insumoModel = new Insumo();
        $custoTotal  = 0.0;

        foreach ($itens as $item) {
            $qtdNecessaria = (float)$item['quantidade'] * $qtd;
            $qtdConvertida = Helper::convertUnit($qtdNecessaria, $item['unidade'], $item['insumo_unidade'])
                             ?? $qtdNecessaria;

            $insumoModel->debitar(
                (int)$item['insumo_id'],
                $qtdConvertida,
                'producao',
                $producaoId
            );

            // Custo desta linha
            $custoUnit = Helper::costPerUnit(
                (float)$item['custo_medio'],
                $item['insumo_unidade'],
                $item['unidade']
            ) ?? (float)$item['custo_medio'];

            $custoTotal += $custoUnit * (float)$item['quantidade'] * $qtd;
        }

        return round($custoTotal, 2);
    }
}
