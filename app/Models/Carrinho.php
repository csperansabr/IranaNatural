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

        // Fallback: sessão mudou mas cliente está logado (ex: sessão regenerada sem merge)
        if (!$carrinho && $clienteId) {
            $carrinho = $this->queryOne(
                "SELECT * FROM carrinhos WHERE cliente_id = ? ORDER BY atualizado_em DESC LIMIT 1",
                [$clienteId]
            );
            if ($carrinho) {
                $this->exec("UPDATE carrinhos SET sessao_id = ? WHERE id = ?", [$sessaoId, $carrinho['id']]);
                $carrinho['sessao_id'] = $sessaoId;
            }
        }

        if ($carrinho) {
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

    /**
     * Migra ou faz merge do carrinho anônimo (sessão anterior) para o cliente recém-autenticado.
     * Chamado imediatamente após session_regenerate_id() no login e no cadastro.
     *
     * Casos cobertos:
     *   1. Sem carrinho anônimo  → apenas atualiza sessao_id do carrinho do cliente (ou cria novo)
     *   2. Carrinho anônimo, sem carrinho de cliente → migra: atualiza sessao_id + vincula cliente
     *   3. Ambos existem → merge atômico: soma quantidades (cap ao estoque), descarta anônimo
     */
    public function mergeOuMigrar(string $sessaoAnterior, string $sessaoNova, int $clienteId): array
    {
        $anonimo     = $this->queryOne("SELECT * FROM carrinhos WHERE sessao_id = ?", [$sessaoAnterior]);
        $cartCliente = $this->queryOne(
            "SELECT * FROM carrinhos WHERE cliente_id = ? ORDER BY atualizado_em DESC LIMIT 1",
            [$clienteId]
        );

        // Caso 1: sem carrinho anônimo
        if (!$anonimo) {
            if ($cartCliente) {
                $this->exec("UPDATE carrinhos SET sessao_id = ? WHERE id = ?", [$sessaoNova, $cartCliente['id']]);
                $cartCliente['sessao_id'] = $sessaoNova;
                return $cartCliente;
            }
            $id = $this->insert(['sessao_id' => $sessaoNova, 'cliente_id' => $clienteId]);
            return $this->findById($id);
        }

        // Caso especial: carrinho anônimo já pertence a este cliente
        if ($cartCliente && $cartCliente['id'] === $anonimo['id']) {
            $this->exec("UPDATE carrinhos SET sessao_id = ? WHERE id = ?", [$sessaoNova, $anonimo['id']]);
            $anonimo['sessao_id'] = $sessaoNova;
            return $anonimo;
        }

        // Caso 2: sem carrinho de cliente — migrar o carrinho anônimo
        if (!$cartCliente) {
            $this->exec(
                "UPDATE carrinhos SET sessao_id = ?, cliente_id = ? WHERE id = ?",
                [$sessaoNova, $clienteId, $anonimo['id']]
            );
            $anonimo['sessao_id']  = $sessaoNova;
            $anonimo['cliente_id'] = $clienteId;
            error_log("[Carrinho] Migrado carrinho #{$anonimo['id']} sessao={$sessaoAnterior}→{$sessaoNova} cliente={$clienteId}");
            return $anonimo;
        }

        // Caso 3: merge — atualizar sessao_id antes da transação (garante acesso mesmo em falha)
        $this->exec("UPDATE carrinhos SET sessao_id = ? WHERE id = ?", [$sessaoNova, $cartCliente['id']]);

        $this->beginTransaction();
        try {
            $itens = $this->query(
                "SELECT ci.produto_id, ci.quantidade, p.preco_venda, p.estoque_atual, p.ativo
                 FROM carrinho_itens ci
                 JOIN produtos p ON p.id = ci.produto_id
                 WHERE ci.carrinho_id = ?",
                [$anonimo['id']]
            );

            $mesclados  = 0;
            $descartados = 0;
            foreach ($itens as $item) {
                if (!$item['ativo'] || (int)$item['estoque_atual'] <= 0) {
                    $descartados++;
                    continue;
                }

                $estoqueDisp   = (int)$item['estoque_atual'];
                $itemExistente = $this->queryOne(
                    "SELECT id, quantidade FROM carrinho_itens WHERE carrinho_id = ? AND produto_id = ?",
                    [$cartCliente['id'], $item['produto_id']]
                );

                if ($itemExistente) {
                    $novaQtd = min($itemExistente['quantidade'] + $item['quantidade'], $estoqueDisp);
                    $this->exec(
                        "UPDATE carrinho_itens SET quantidade = ? WHERE id = ?",
                        [$novaQtd, $itemExistente['id']]
                    );
                } else {
                    $qtd = min((int)$item['quantidade'], $estoqueDisp);
                    $this->exec(
                        "INSERT INTO carrinho_itens (carrinho_id, produto_id, quantidade, preco_unitario) VALUES (?,?,?,?)",
                        [$cartCliente['id'], $item['produto_id'], $qtd, (float)$item['preco_venda']]
                    );
                }
                $mesclados++;
            }

            $this->exec("DELETE FROM carrinho_itens WHERE carrinho_id = ?", [$anonimo['id']]);
            $this->exec("DELETE FROM carrinhos WHERE id = ?", [$anonimo['id']]);
            $this->exec("UPDATE carrinhos SET atualizado_em = NOW() WHERE id = ?", [$cartCliente['id']]);

            $this->commit();
            error_log("[Carrinho] Merge concluído: anonimo=#{$anonimo['id']} cliente=#{$cartCliente['id']} mesclados={$mesclados} descartados={$descartados}");
        } catch (\Throwable $e) {
            $this->rollback();
            error_log("[Carrinho] Merge falhou (carrinho cliente acessível, anônimo preservado): " . $e->getMessage());
        }

        $cartCliente['sessao_id'] = $sessaoNova;
        return $cartCliente;
    }

    /** @deprecated Use mergeOuMigrar() — mantido para compatibilidade */
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
                    p.peso, p.altura, p.largura, p.comprimento,
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
