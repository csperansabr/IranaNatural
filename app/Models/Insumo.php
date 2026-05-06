<?php
namespace App\Models;

use App\Core\Model;

class Insumo extends Model
{
    protected string $table = 'insumos';

    public function allAtivos(): array
    {
        return $this->findAll('ativo = 1', [], 'nome ASC');
    }

    public function withAlertaEstoque(): array
    {
        return $this->query(
            "SELECT * FROM insumos WHERE estoque_atual <= estoque_minimo AND ativo = 1 ORDER BY nome ASC"
        );
    }

    // Recalcula custo médio ponderado após uma compra
    public function atualizarCustoMedio(int $id, float $qtdComprada, float $valorUnitario): float
    {
        $insumo = $this->findById($id);
        if (!$insumo) return 0.0;

        $estoqueAtual = (float)$insumo['estoque_atual'];
        $custoAtual   = (float)$insumo['custo_medio'];

        if (($estoqueAtual + $qtdComprada) == 0) return $valorUnitario;

        $novoMedio = (($estoqueAtual * $custoAtual) + ($qtdComprada * $valorUnitario))
                     / ($estoqueAtual + $qtdComprada);

        return round($novoMedio, 6);
    }

    public function debitar(int $id, float $quantidade, string $refTipo, int $refId, string $obs = ''): bool
    {
        $insumo = $this->findById($id);
        if (!$insumo) return false;

        $antes = (float)$insumo['estoque_atual'];
        $apos  = $antes - $quantidade;

        $this->exec("UPDATE insumos SET estoque_atual = ? WHERE id = ?", [round($apos, 4), $id]);
        $this->exec(
            "INSERT INTO mov_insumos (insumo_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, ref_id, observacoes)
             VALUES (?, 'saida', ?, ?, ?, ?, ?, ?)",
            [$id, round($quantidade, 4), round($antes, 4), round($apos, 4), $refTipo, $refId, $obs]
        );
        return true;
    }

    public function creditar(int $id, float $quantidade, string $refTipo, int $refId, string $obs = ''): bool
    {
        $insumo = $this->findById($id);
        if (!$insumo) return false;

        $antes = (float)$insumo['estoque_atual'];
        $apos  = $antes + $quantidade;

        $this->exec("UPDATE insumos SET estoque_atual = ? WHERE id = ?", [round($apos, 4), $id]);
        $this->exec(
            "INSERT INTO mov_insumos (insumo_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, ref_id, observacoes)
             VALUES (?, 'entrada', ?, ?, ?, ?, ?, ?)",
            [$id, round($quantidade, 4), round($antes, 4), round($apos, 4), $refTipo, $refId, $obs]
        );
        return true;
    }

    public function ajustar(int $id, float $novoEstoque, string $obs = ''): bool
    {
        $insumo = $this->findById($id);
        if (!$insumo) return false;

        $antes = (float)$insumo['estoque_atual'];
        $diff  = $novoEstoque - $antes;
        $tipo  = $diff >= 0 ? 'ajuste' : 'ajuste';

        $this->exec("UPDATE insumos SET estoque_atual = ? WHERE id = ?", [round($novoEstoque, 4), $id]);
        $this->exec(
            "INSERT INTO mov_insumos (insumo_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, observacoes)
             VALUES (?, 'ajuste', ?, ?, ?, 'ajuste_manual', ?)",
            [$id, round(abs($diff), 4), round($antes, 4), round($novoEstoque, 4), $obs]
        );
        return true;
    }

    public function movimentacoes(int $id, int $limit = 50): array
    {
        return $this->query(
            "SELECT * FROM mov_insumos WHERE insumo_id = ? ORDER BY criado_em DESC LIMIT ?",
            [$id, $limit]
        );
    }
}
