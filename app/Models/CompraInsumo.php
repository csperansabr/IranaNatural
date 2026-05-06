<?php
namespace App\Models;

use App\Core\Model;

class CompraInsumo extends Model
{
    protected string $table = 'compras_insumos';

    public function allComDetalhes(int $limit = 100): array
    {
        return $this->query(
            "SELECT c.*, i.nome as insumo_nome, i.unidade_medida
             FROM compras_insumos c
             JOIN insumos i ON i.id = c.insumo_id
             ORDER BY c.data_compra DESC, c.criado_em DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function registrarCompra(array $data): int
    {
        $insumoModel = new Insumo();
        $insumo      = $insumoModel->findById((int)$data['insumo_id']);
        if (!$insumo) return 0;

        $qtd          = (float)$data['quantidade'];
        $valorTotal   = (float)$data['valor_total'];
        $valorUnit    = $qtd > 0 ? $valorTotal / $qtd : 0;
        $custoAnt     = (float)$insumo['custo_medio'];
        $novoMedio    = $insumoModel->atualizarCustoMedio((int)$data['insumo_id'], $qtd, $valorUnit);

        $id = $this->insert([
            'data_compra'      => $data['data_compra'],
            'fornecedor'       => $data['fornecedor'] ?? '',
            'insumo_id'        => (int)$data['insumo_id'],
            'quantidade'       => $qtd,
            'valor_total'      => $valorTotal,
            'valor_unitario'   => round($valorUnit, 6),
            'custo_medio_ant'  => $custoAnt,
            'custo_medio_novo' => $novoMedio,
            'observacoes'      => $data['observacoes'] ?? '',
        ]);

        // Atualiza custo médio e estoque do insumo
        $this->exec(
            "UPDATE insumos SET custo_medio = ?, estoque_atual = estoque_atual + ? WHERE id = ?",
            [$novoMedio, $qtd, (int)$data['insumo_id']]
        );

        // Registra movimentação
        $estoqueAntes = (float)$insumo['estoque_atual'];
        $this->exec(
            "INSERT INTO mov_insumos (insumo_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, ref_id)
             VALUES (?, 'entrada', ?, ?, ?, 'compra', ?)",
            [(int)$data['insumo_id'], $qtd, $estoqueAntes, $estoqueAntes + $qtd, $id]
        );

        // Recalcula custo de todos os produtos que usam este insumo
        (new Produto())->recalcularPorInsumo((int)$data['insumo_id']);

        return $id;
    }
}
