<?php
namespace App\Models;

use App\Core\Model;

class Carrinho extends Model
{
    protected string $table = 'carrinhos';

    public function getOuCriar(string $sessaoId, ?int $clienteId = null): array
    {
        $carrinho = $this->queryOne(
            "SELECT * FROM carrinhos WHERE sessao_id = ?",
            [$sessaoId]
        );
        if ($carrinho) {
            // Associar cliente se ainda não vinculado
            if ($clienteId && !$carrinho['cliente_id']) {
                $this->exec(
                    "UPDATE carrinhos SET cliente_id = ? WHERE id = ?",
                    [$clienteId, $carrinho['id']]
                );
                $carrinho['cliente_id'] = $clienteId;
            }
            return $carrinho;
        }
        $id = $this->insert([
            'sessao_id'  => $sessaoId,
            'cliente_id' => $clienteId,
        ]);
        return $this->findById($id);
    }

    public function vincularCliente(string $sessaoId, int $clienteId): void
    {
        $this->exec(
            "UPDATE carrinhos SET cliente_id = ? WHERE sessao_id = ?",
            [$clienteId, $sessaoId]
        );
    }

    public function addItem(int $carrinhoId, int $produtoId, int $qtd, float $preco): void
    {
        // Verifica se já existe — incrementa quantidade
        $item = $this->queryOne(
            "SELECT * FROM carrinho_itens WHERE carrinho_id = ? AND produto_id = ?",
            [$carrinhoId, $produtoId]
        );
        if ($item) {
            $novaQtd = $item['quantidade'] + $qtd;
            $this->exec(
                "UPDATE carrinho_itens SET quantidade = ?, preco_unitario = ? WHERE id = ?",
                [$novaQtd, $preco, $item['id']]
            );
        } else {
            $this->exec(
                "INSERT INTO carrinho_itens (carrinho_id, produto_id, quantidade, preco_unitario) VALUES (?,?,?,?)",
                [$carrinhoId, $produtoId, $qtd, $preco]
            );
        }
        $this->exec("UPDATE carrinhos SET atualizado_em = NOW() WHERE id = ?", [$carrinhoId]);
    }

    public function updateItem(int $itemId, int $qtd): void
    {
        if ($qtd <= 0) {
            $this->exec("DELETE FROM carrinho_itens WHERE id = ?", [$itemId]);
        } else {
            $this->exec("UPDATE carrinho_itens SET quantidade = ? WHERE id = ?", [$qtd, $itemId]);
        }
    }

    public function removeItem(int $itemId): void
    {
        $this->exec("DELETE FROM carrinho_itens WHERE id = ?", [$itemId]);
    }

    public function getItens(int $carrinhoId): array
    {
        return $this->query(
            "SELECT ci.*, p.nome, p.slug, p.estoque_atual,
                    c.slug AS categoria_slug,
                    COALESCE(ip.caminho, '') AS imagem
             FROM carrinho_itens ci
             JOIN produtos  p  ON p.id  = ci.produto_id
             JOIN categorias c ON c.id  = p.categoria_id
             LEFT JOIN imagens_produtos ip ON ip.produto_id = p.id AND ip.principal = 1
             WHERE ci.carrinho_id = ?
             ORDER BY ci.id ASC",
            [$carrinhoId]
        );
    }

    public function getItem(int $itemId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM carrinho_itens WHERE id = ?",
            [$itemId]
        );
    }

    public function getTotal(int $carrinhoId): float
    {
        $result = $this->queryOne(
            "SELECT COALESCE(SUM(quantidade * preco_unitario), 0) AS total FROM carrinho_itens WHERE carrinho_id = ?",
            [$carrinhoId]
        );
        return (float)($result['total'] ?? 0);
    }

    public function getCount(int $carrinhoId): int
    {
        $result = $this->queryOne(
            "SELECT COALESCE(SUM(quantidade), 0) AS qtd FROM carrinho_itens WHERE carrinho_id = ?",
            [$carrinhoId]
        );
        return (int)($result['qtd'] ?? 0);
    }

    public function limpar(int $carrinhoId): void
    {
        $this->exec("DELETE FROM carrinho_itens WHERE carrinho_id = ?", [$carrinhoId]);
    }
}
