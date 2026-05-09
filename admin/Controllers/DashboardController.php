<?php
namespace Admin\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\Insumo;
use App\Models\Producao;
use App\Models\CompraInsumo;
use App\Models\Pedido;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $venda   = new Venda();
        $produto = new Produto();
        $insumo  = new Insumo();
        $pedido  = new Pedido();

        $mes        = date('Y-m');
        $stats      = $venda->totalMes($mes);
        $grafVendas = $venda->porDia(30);

        $maisVendidos    = $produto->maisVendidos(5);
        $alertasProdutos = $produto->withAlertaEstoque();
        $alertasInsumos  = $insumo->withAlertaEstoque();
        $producaoRecente = (new Producao())->recentes(5);
        $comprasRecentes = (new CompraInsumo())->allComDetalhes(5);

        // E-commerce stats (pedidos online do mês atual)
        $pedidosStats = $this->pedidosStats($pedido, $mes);
        $pedidosRecentes = $pedido->allComDetalhes(['data_ini' => date('Y-m-01')]);

        $flash = $this->getFlash();
        $user  = $this->currentUser();

        $this->render('dashboard/index', compact(
            'stats', 'grafVendas', 'maisVendidos',
            'alertasProdutos', 'alertasInsumos',
            'producaoRecente', 'comprasRecentes',
            'pedidosStats', 'pedidosRecentes',
            'flash', 'user'
        ));
    }

    private function pedidosStats(Pedido $pedido, string $mes): array
    {
        try {
            $rows = $pedido->statsPorStatus($mes);
        } catch (\Throwable) {
            $rows = [];
        }

        $out = ['aguardando' => 0, 'pagos' => 0, 'cancelados' => 0,
                'receita_pix' => 0.0, 'receita_cartao' => 0.0, 'receita_total' => 0.0];

        foreach ($rows as $r) {
            $s = $r['status'];
            $q = (int)$r['qtd'];
            $v = (float)$r['receita'];

            if (in_array($s, ['aguardando_pagamento', 'pendente'], true)) {
                $out['aguardando'] += $q;
            } elseif (in_array($s, ['pago', 'separando', 'enviado', 'entregue'], true)) {
                $out['pagos'] += $q;
                $out['receita_total'] += $v;
                if ($r['forma_pagamento'] === 'pix') $out['receita_pix'] += $v;
                else $out['receita_cartao'] += $v;
            } elseif (in_array($s, ['cancelado', 'pagamento_expirado', 'pagamento_recusado'], true)) {
                $out['cancelados'] += $q;
            }
        }

        return $out;
    }
}
