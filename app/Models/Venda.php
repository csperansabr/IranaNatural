<?php
namespace App\Models;

use App\Core\Model;

class Venda extends Model
{
    protected string $table = 'vendas';

    public function allComDetalhes(int $limit = 200): array
    {
        return $this->query(
            "SELECT v.*, COUNT(vi.id) as qtd_itens
             FROM vendas v
             LEFT JOIN vendas_itens vi ON vi.venda_id = v.id
             GROUP BY v.id
             ORDER BY v.data_venda DESC, v.criado_em DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function getItens(int $vendaId): array
    {
        return $this->query(
            "SELECT vi.*, p.nome as produto_nome
             FROM vendas_itens vi
             JOIN produtos p ON p.id = vi.produto_id
             WHERE vi.venda_id = ?",
            [$vendaId]
        );
    }

    // $itens = [['produto_id'=>X,'quantidade'=>Y,'preco_unitario'=>Z],...]
    public function registrar(array $cabecalho, array $itens): int
    {
        $produtoModel = new Produto();

        // Valida estoque antes de qualquer operação
        foreach ($itens as $item) {
            $p = $produtoModel->findById((int)$item['produto_id']);
            if (!$p) throw new \Exception("Produto #{$item['produto_id']} não encontrado.");
            if ((int)$p['estoque_atual'] < (int)$item['quantidade']) {
                throw new \Exception("Estoque insuficiente para: {$p['nome']}. Disponível: {$p['estoque_atual']}.");
            }
        }

        $this->beginTransaction();
        try {
            $subtotal   = 0.0;
            $lucroTotal = 0.0;
            $desconto   = (float)($cabecalho['desconto'] ?? 0);

            // Pré-calcula totais
            $linhas = [];
            foreach ($itens as $item) {
                $p          = $produtoModel->findById((int)$item['produto_id']);
                $qtd        = (int)$item['quantidade'];
                $precoUnit  = (float)$item['preco_unitario'];
                $custoUnit  = (float)$p['custo_calculado'];
                $sub        = round($precoUnit * $qtd, 2);
                $lucro      = round(($precoUnit - $custoUnit) * $qtd, 2);
                $subtotal  += $sub;
                $lucroTotal+= $lucro;
                $linhas[]   = ['produto' => $p, 'qtd' => $qtd, 'preco' => $precoUnit,
                               'custo' => $custoUnit, 'sub' => $sub, 'lucro' => $lucro];
            }

            $valorFinal = max(0, $subtotal - $desconto);

            // Cria cabeçalho da venda
            $vendaId = $this->insert([
                'data_venda'      => $cabecalho['data_venda'],
                'forma_pagamento' => $cabecalho['forma_pagamento'],
                'subtotal'        => round($subtotal, 2),
                'desconto'        => round($desconto, 2),
                'valor_final'     => round($valorFinal, 2),
                'lucro_total'     => round($lucroTotal, 2),
                'observacoes'     => $cabecalho['observacoes'] ?? '',
            ]);

            // Insere itens e debita estoque
            foreach ($linhas as $linha) {
                $p = $linha['produto'];

                $this->exec(
                    "INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario, custo_unitario, subtotal, lucro)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$vendaId, $p['id'], $linha['qtd'], $linha['preco'], $linha['custo'], $linha['sub'], $linha['lucro']]
                );

                $estAntes = (int)$p['estoque_atual'];
                $estApos  = $estAntes - $linha['qtd'];
                $this->exec("UPDATE produtos SET estoque_atual = ? WHERE id = ?", [$estApos, $p['id']]);
                $this->exec(
                    "INSERT INTO mov_produtos (produto_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, ref_id)
                     VALUES (?, 'saida', ?, ?, ?, 'venda', ?)",
                    [$p['id'], $linha['qtd'], $estAntes, $estApos, $vendaId]
                );
            }

            $this->commit();
            return $vendaId;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    // Stats do dashboard
    public function totalMes(string $anoMes): array
    {
        return $this->queryOne(
            "SELECT COALESCE(SUM(valor_final),0) as total, COALESCE(SUM(lucro_total),0) as lucro, COUNT(*) as qtd
             FROM vendas WHERE DATE_FORMAT(data_venda,'%Y-%m') = ?",
            [$anoMes]
        ) ?? ['total' => 0, 'lucro' => 0, 'qtd' => 0];
    }

    public function porDia(int $dias = 30): array
    {
        return $this->query(
            "SELECT DATE(data_venda) as dia, SUM(valor_final) as total
             FROM vendas
             WHERE data_venda >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY dia ORDER BY dia ASC",
            [$dias]
        );
    }

    public function recentes(int $limit = 5): array
    {
        return $this->allComDetalhes($limit);
    }
}
