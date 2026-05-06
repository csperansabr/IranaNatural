<?php
namespace App\Models;

use App\Core\Model;

class Producao extends Model
{
    protected string $table = 'producoes';

    public function allComDetalhes(int $limit = 100): array
    {
        return $this->query(
            "SELECT pr.*, p.nome as produto_nome
             FROM producoes pr
             JOIN produtos p ON p.id = pr.produto_id
             ORDER BY pr.data_producao DESC, pr.criado_em DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function registrar(array $data): int
    {
        $fichaModel   = new FichaTecnica();
        $produtoModel = new Produto();
        $insumoModel  = new Insumo();

        $produtoId   = (int)$data['produto_id'];
        $qtdProduzida = (int)$data['quantidade_produzida'];
        $qtdPerda    = (float)($data['quantidade_perda'] ?? 0);
        $qtdEstoque  = max(0, $qtdProduzida - (int)$qtdPerda);

        $this->beginTransaction();
        try {
            // Insere produção
            $id = $this->insert([
                'produto_id'          => $produtoId,
                'quantidade_produzida'=> $qtdProduzida,
                'quantidade_perda'    => $qtdPerda,
                'motivo_perda'        => $data['motivo_perda'] ?? '',
                'data_producao'       => $data['data_producao'],
                'responsavel'         => $data['responsavel'] ?? '',
                'observacoes'         => $data['observacoes'] ?? '',
                'custo_real'          => 0,
            ]);

            // Debita insumos (pelo total produzido, incluindo perdas)
            $custo = $fichaModel->debitarInsumos($produtoId, $qtdProduzida, $id);

            // Atualiza custo real da produção
            $this->exec("UPDATE producoes SET custo_real = ? WHERE id = ?", [$custo, $id]);

            // Aumenta estoque do produto (produzido - perdas)
            $produto  = $produtoModel->findById($produtoId);
            $estAntes = (int)$produto['estoque_atual'];
            $estApos  = $estAntes + $qtdEstoque;

            $this->exec(
                "UPDATE produtos SET estoque_atual = ? WHERE id = ?",
                [$estApos, $produtoId]
            );

            // Movimentação do produto
            $this->exec(
                "INSERT INTO mov_produtos (produto_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, ref_id)
                 VALUES (?, 'entrada', ?, ?, ?, 'producao', ?)",
                [$produtoId, $qtdEstoque, $estAntes, $estApos, $id]
            );

            $this->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function recentes(int $limit = 5): array
    {
        return $this->query(
            "SELECT pr.*, p.nome as produto_nome
             FROM producoes pr
             JOIN produtos p ON p.id = pr.produto_id
             ORDER BY pr.data_producao DESC, pr.criado_em DESC
             LIMIT ?",
            [$limit]
        );
    }
}
