<?php
namespace Admin\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\Insumo;
use App\Models\Producao;
use App\Models\CompraInsumo;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $venda   = new Venda();
        $produto = new Produto();
        $insumo  = new Insumo();

        $mes       = date('Y-m');
        $stats     = $venda->totalMes($mes);
        $grafVendas = $venda->porDia(30);

        $maisVendidos    = $produto->maisVendidos(5);
        $alertasProdutos = $produto->withAlertaEstoque();
        $alertasInsumos  = $insumo->withAlertaEstoque();
        $producaoRecente = (new Producao())->recentes(5);
        $comprasRecentes = (new CompraInsumo())->allComDetalhes(5);

        $flash = $this->getFlash();
        $user  = $this->currentUser();

        $this->render('dashboard/index', compact(
            'stats', 'grafVendas', 'maisVendidos',
            'alertasProdutos', 'alertasInsumos',
            'producaoRecente', 'comprasRecentes',
            'flash', 'user'
        ));
    }
}
