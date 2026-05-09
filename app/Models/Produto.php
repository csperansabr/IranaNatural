<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Helper;

class Produto extends Model
{
    protected string $table = 'produtos';

    // Public site: only published products
    public function allAtivos(int $categoriaId = 0): array
    {
        $sql    = "SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug,
                          (SELECT caminho FROM imagens_produtos WHERE produto_id = p.id AND principal = 1 LIMIT 1) as imagem_principal,
                          (SELECT GROUP_CONCAT(caminho ORDER BY principal DESC, ordem ASC SEPARATOR '|')
                           FROM imagens_produtos WHERE produto_id = p.id) as todas_imagens
                   FROM produtos p
                   JOIN categorias c ON c.id = p.categoria_id
                   WHERE p.ativo = 1";
        $params = [];
        if ($categoriaId) {
            $sql   .= " AND p.categoria_id = ?";
            $params[] = $categoriaId;
        }
        $sql .= " ORDER BY p.nome ASC";
        return $this->query($sql, $params);
    }

    // Admin panel: all products regardless of active status
    public function allForAdmin(): array
    {
        return $this->query(
            "SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug,
                    (SELECT caminho FROM imagens_produtos WHERE produto_id = p.id AND principal = 1 LIMIT 1) as imagem_principal
             FROM produtos p
             JOIN categorias c ON c.id = p.categoria_id
             ORDER BY p.ativo DESC, p.nome ASC"
        );
    }

    // Admin panel: single product with category data (usado no formulário de edição)
    public function findByIdAdmin(int $id): ?array
    {
        return $this->queryOne(
            "SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug
             FROM produtos p
             JOIN categorias c ON c.id = p.categoria_id
             WHERE p.id = ?",
            [$id]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne(
            "SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug
             FROM produtos p
             JOIN categorias c ON c.id = p.categoria_id
             WHERE p.slug = ? AND p.ativo = 1",
            [$slug]
        );
    }

    public function getImagens(int $produtoId): array
    {
        return $this->query(
            "SELECT * FROM imagens_produtos WHERE produto_id = ? ORDER BY principal DESC, ordem ASC",
            [$produtoId]
        );
    }

    public function relacionados(int $produtoId, int $categoriaId, int $limit = 4): array
    {
        return $this->query(
            "SELECT p.*, (SELECT caminho FROM imagens_produtos WHERE produto_id = p.id AND principal = 1 LIMIT 1) as imagem_principal
             FROM produtos p
             WHERE p.categoria_id = ? AND p.id != ? AND p.ativo = 1
             ORDER BY RAND() LIMIT ?",
            [$categoriaId, $produtoId, $limit]
        );
    }

    public function destaques(int $limit = 6): array
    {
        return $this->query(
            "SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug,
                    (SELECT caminho FROM imagens_produtos WHERE produto_id = p.id AND principal = 1 LIMIT 1) as imagem_principal
             FROM produtos p
             JOIN categorias c ON c.id = p.categoria_id
             WHERE p.ativo = 1
             ORDER BY p.estoque_atual DESC, p.nome ASC
             LIMIT ?",
            [$limit]
        );
    }

    public function recalcularCusto(int $produtoId): void
    {
        $ficha = $this->query(
            "SELECT ft.quantidade, ft.unidade, i.custo_medio, i.unidade_medida
             FROM fichas_tecnicas ft
             JOIN insumos i ON i.id = ft.insumo_id
             WHERE ft.produto_id = ?",
            [$produtoId]
        );

        $custo = 0.0;
        foreach ($ficha as $item) {
            $custoUnitario = Helper::costPerUnit(
                (float)$item['custo_medio'],
                $item['unidade_medida'],
                $item['unidade']
            );
            if ($custoUnitario !== null) {
                $custo += $custoUnitario * (float)$item['quantidade'];
            }
        }

        $produto = $this->findById($produtoId);
        if (!$produto) return;

        $preco  = (float)$produto['preco_venda'];
        $lucro  = $preco - $custo;
        $margem = $preco > 0 ? round(($lucro / $preco) * 100, 2) : 0;

        $this->exec(
            "UPDATE produtos SET custo_calculado = ?, lucro_calculado = ?, margem_real = ? WHERE id = ?",
            [round($custo, 4), round($lucro, 2), $margem, $produtoId]
        );
    }

    public function recalcularPorInsumo(int $insumoId): void
    {
        $rows = $this->query(
            "SELECT DISTINCT produto_id FROM fichas_tecnicas WHERE insumo_id = ?",
            [$insumoId]
        );
        foreach ($rows as $row) {
            $this->recalcularCusto((int)$row['produto_id']);
        }
    }

    public function addImagem(int $produtoId, string $caminho, bool $principal = false): void
    {
        if ($principal) {
            $this->exec("UPDATE imagens_produtos SET principal = 0 WHERE produto_id = ?", [$produtoId]);
        }
        $maxOrdem = $this->queryOne(
            "SELECT COALESCE(MAX(ordem), -1) as m FROM imagens_produtos WHERE produto_id = ?",
            [$produtoId]
        );
        $ordem = ((int)($maxOrdem['m'] ?? -1)) + 1;
        $this->exec(
            "INSERT INTO imagens_produtos (produto_id, caminho, principal, ordem) VALUES (?, ?, ?, ?)",
            [$produtoId, $caminho, $principal ? 1 : 0, $ordem]
        );
    }

    public function deleteImagem(int $imagemId): ?string
    {
        $img = $this->queryOne("SELECT * FROM imagens_produtos WHERE id = ?", [$imagemId]);
        if ($img) {
            $this->exec("DELETE FROM imagens_produtos WHERE id = ?", [$imagemId]);
        }
        return $img['caminho'] ?? null;
    }

    public function setPrincipalImagem(int $produtoId, int $imagemId): void
    {
        $this->exec("UPDATE imagens_produtos SET principal = 0 WHERE produto_id = ?", [$produtoId]);
        $this->exec("UPDATE imagens_produtos SET principal = 1 WHERE id = ? AND produto_id = ?", [$imagemId, $produtoId]);
    }

    public function moverImagem(int $produtoId, int $imagemId, string $direction): void
    {
        $imgs = $this->getImagens($produtoId);
        // Normalise ordem values to 0,1,2,... ignoring gaps
        foreach ($imgs as $i => $img) {
            $this->exec("UPDATE imagens_produtos SET ordem = ? WHERE id = ?", [$i, $img['id']]);
            $imgs[$i]['ordem'] = $i;
        }
        // Find current index
        $currentIdx = null;
        foreach ($imgs as $i => $img) {
            if ((int)$img['id'] === $imagemId) { $currentIdx = $i; break; }
        }
        if ($currentIdx === null) return;
        $swapIdx = $direction === 'up' ? $currentIdx - 1 : $currentIdx + 1;
        if ($swapIdx < 0 || $swapIdx >= count($imgs)) return;
        // Swap ordem
        $this->exec("UPDATE imagens_produtos SET ordem = ? WHERE id = ?", [$swapIdx, $imgs[$currentIdx]['id']]);
        $this->exec("UPDATE imagens_produtos SET ordem = ? WHERE id = ?", [$currentIdx, $imgs[$swapIdx]['id']]);
    }

    public function withAlertaEstoque(): array
    {
        return $this->query(
            "SELECT * FROM produtos WHERE estoque_atual <= estoque_minimo AND ativo = 1 ORDER BY nome ASC"
        );
    }

    public function maisVendidos(int $limit = 5): array
    {
        return $this->query(
            "SELECT p.nome, SUM(vi.quantidade) as total_vendido, SUM(vi.lucro) as total_lucro
             FROM vendas_itens vi
             JOIN produtos p ON p.id = vi.produto_id
             GROUP BY vi.produto_id
             ORDER BY total_vendido DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function debitarEstoque(int $produtoId, int $quantidade): void
    {
        $this->exec(
            "UPDATE produtos SET estoque_atual = GREATEST(0, estoque_atual - ?) WHERE id = ?",
            [$quantidade, $produtoId]
        );
    }
}
