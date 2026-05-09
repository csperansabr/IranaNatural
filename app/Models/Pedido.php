<?php
namespace App\Models;

use App\Core\Model;

class Pedido extends Model
{
    protected string $table = 'pedidos';

    public function criar(array $dados, array $itens): int
    {
        $this->beginTransaction();
        try {
            $numero = $this->gerarNumero();

            $subtotal = array_sum(array_column($itens, 'subtotal'));
            $frete    = (float)($dados['frete']    ?? 0);
            $desconto = (float)($dados['desconto'] ?? 0);
            // Total = sum of items (already at final prices) + frete.
            // $desconto is stored for display/audit only — do not subtract again.
            $total    = round($subtotal + $frete, 2);

            $insertPedido = [
                'numero'              => $numero,
                'cliente_id'          => $dados['cliente_id'],
                'status'              => 'aguardando_pagamento',
                'forma_pagamento'     => $dados['forma_pagamento'],
                'parcelas'            => (int)($dados['parcelas']          ?? 1),
                'subtotal'            => $subtotal,
                'frete'               => $frete,
                'desconto'            => $desconto,
                'desconto_pix_pct'    => (float)($dados['desconto_pix_pct'] ?? 0),
                'total'               => $total,
                'observacoes'         => $dados['observacoes'] ?? '',
                'entrega_cep'         => $dados['entrega_cep']         ?? '',
                'entrega_logradouro'  => $dados['entrega_logradouro']  ?? '',
                'entrega_numero'      => $dados['entrega_numero']       ?? '',
                'entrega_complemento' => $dados['entrega_complemento'] ?? '',
                'entrega_bairro'      => $dados['entrega_bairro']       ?? '',
                'entrega_cidade'      => $dados['entrega_cidade']       ?? '',
                'entrega_estado'      => $dados['entrega_estado']       ?? '',
            ];

            // Campos de frete preenchidos quando disponíveis (migração v6.0)
            foreach (['tipo_frete','transportadora','prazo_entrega','codigo_transportadora','resp_entrega_cliente'] as $col) {
                if (array_key_exists($col, $dados)) $insertPedido[$col] = $dados[$col];
            }

            $pedidoId = $this->insert($insertPedido);

            foreach ($itens as $item) {
                $this->exec(
                    "INSERT INTO pedido_itens (pedido_id, produto_id, nome_produto, quantidade, preco_unitario, subtotal)
                     VALUES (?,?,?,?,?,?)",
                    [
                        $pedidoId,
                        $item['produto_id'],
                        $item['nome_produto'],
                        $item['quantidade'],
                        $item['preco_unitario'],
                        $item['subtotal'],
                    ]
                );
            }

            // Registrar status inicial no histórico
            $this->exec(
                "INSERT INTO pedidos_historico (pedido_id, status, observacao) VALUES (?,?,?)",
                [$pedidoId, 'aguardando_pagamento', 'Pedido criado — aguardando confirmação de pagamento']
            );

            $this->commit();
            return $pedidoId;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function gerarNumero(): string
    {
        do {
            $numero = 'IRA-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $existe = $this->queryOne("SELECT id FROM pedidos WHERE numero = ?", [$numero]);
        } while ($existe);
        return $numero;
    }

    public function findByNumero(string $numero): ?array
    {
        return $this->queryOne(
            "SELECT p.*, c.nome AS cliente_nome, c.email AS cliente_email, c.telefone AS cliente_telefone, c.cpf AS cliente_cpf
             FROM pedidos p
             JOIN clientes c ON c.id = p.cliente_id
             WHERE p.numero = ?",
            [$numero]
        );
    }

    public function findComCliente(int $id): ?array
    {
        return $this->queryOne(
            "SELECT p.*, c.nome AS cliente_nome, c.email AS cliente_email, c.telefone AS cliente_telefone, c.cpf AS cliente_cpf
             FROM pedidos p
             JOIN clientes c ON c.id = p.cliente_id
             WHERE p.id = ?",
            [$id]
        );
    }

    public function getItens(int $pedidoId): array
    {
        return $this->query(
            "SELECT pi.*, p.slug, c.slug AS categoria_slug,
                    COALESCE(ip.caminho, '') AS imagem
             FROM pedido_itens pi
             JOIN produtos p ON p.id = pi.produto_id
             JOIN categorias c ON c.id = p.categoria_id
             LEFT JOIN imagens_produtos ip ON ip.produto_id = p.id AND ip.principal = 1
             WHERE pi.pedido_id = ?
             ORDER BY pi.id ASC",
            [$pedidoId]
        );
    }

    public function atualizarStatus(int $id, string $status, string $obs = ''): void
    {
        $this->update($id, ['status' => $status]);
        $this->exec(
            "INSERT INTO pedidos_historico (pedido_id, status, observacao) VALUES (?,?,?)",
            [$id, $status, $obs]
        );
    }

    public function allComDetalhes(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['numero'])) {
            $where[]  = "p.numero LIKE ?";
            $params[] = '%' . $filtros['numero'] . '%';
        }
        if (!empty($filtros['cliente'])) {
            $where[]  = "c.nome LIKE ?";
            $params[] = '%' . $filtros['cliente'] . '%';
        }
        if (!empty($filtros['status'])) {
            $where[]  = "p.status = ?";
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['data_ini'])) {
            $where[]  = "DATE(p.criado_em) >= ?";
            $params[] = $filtros['data_ini'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[]  = "DATE(p.criado_em) <= ?";
            $params[] = $filtros['data_fim'];
        }

        $sql = "SELECT p.*, c.nome AS cliente_nome, c.email AS cliente_email,
                       (SELECT COUNT(*) FROM pedido_itens WHERE pedido_id = p.id) AS qtd_itens
                FROM pedidos p
                JOIN clientes c ON c.id = p.cliente_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.criado_em DESC
                LIMIT 500";

        return $this->query($sql, $params);
    }

    public function statsPorStatus(string $mes): array
    {
        return $this->query(
            "SELECT status, forma_pagamento, COUNT(*) AS qtd, COALESCE(SUM(total), 0) AS receita
             FROM pedidos
             WHERE DATE_FORMAT(criado_em, '%Y-%m') = ?
             GROUP BY status, forma_pagamento",
            [$mes]
        );
    }

    public function doCliente(int $clienteId): array
    {
        return $this->query(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM pedido_itens WHERE pedido_id = p.id) AS qtd_itens,
                    pg.receipt_url,
                    pg.pago_em,
                    pg.transaction_nsu,
                    pg.metodo AS metodo_pagamento
             FROM pedidos p
             LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
             WHERE p.cliente_id = ?
             ORDER BY p.criado_em DESC",
            [$clienteId]
        );
    }

    public static function statusLabel(string $status): string
    {
        return match($status) {
            'aguardando_pagamento' => 'Aguardando pagamento',
            'pagamento_expirado'   => 'Pagamento expirado',
            'pagamento_recusado'   => 'Pagamento recusado',
            'pendente'             => 'Aguardando confirmação',
            'pago'                 => 'Pagamento confirmado',
            'separando'            => 'Em separação',
            'enviado'              => 'Enviado',
            'entregue'             => 'Entregue',
            'cancelado'            => 'Cancelado',
            default                => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public static function statusClass(string $status): string
    {
        return match($status) {
            'aguardando_pagamento' => 'adm-status--pendente',
            'pagamento_expirado'   => 'adm-status--cancelado',
            'pagamento_recusado'   => 'adm-status--cancelado',
            'pendente'             => 'adm-status--pendente',
            'pago'                 => 'adm-status--pago',
            'separando'            => 'adm-status--separando',
            'enviado'              => 'adm-status--enviado',
            'entregue'             => 'adm-status--entregue',
            'cancelado'            => 'adm-status--cancelado',
            default                => '',
        };
    }

    public static function pagamentoLabel(string $forma): string
    {
        return match($forma) {
            'pix'            => 'PIX',
            'cartao_credito' => 'Cartão de Crédito',
            'transferencia'  => 'Transferência Bancária',
            'dinheiro'       => 'Dinheiro',
            default          => ucfirst(str_replace('_', ' ', $forma)),
        };
    }

}
