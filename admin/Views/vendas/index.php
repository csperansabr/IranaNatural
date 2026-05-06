<?php use App\Core\Helper; $pageTitle = 'Vendas'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Histórico de Vendas</span>
        <a href="/admin/vendas/nova" class="adm-btn adm-btn-primary adm-btn-sm">+ Registrar Venda</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>Data</th><th>Pagamento</th><th>Itens</th><th>Subtotal</th><th>Desconto</th><th>Total</th><th>Lucro</th></tr>
            </thead>
            <tbody>
            <?php if (empty($vendas)): ?>
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:#718096">Nenhuma venda registrada.</td></tr>
            <?php else: ?>
            <?php foreach ($vendas as $v): ?>
            <tr>
                <td><?= Helper::date($v['data_venda']) ?></td>
                <td>
                    <span class="adm-badge adm-badge-gray"><?= strtoupper($v['forma_pagamento']) ?></span>
                </td>
                <td><?= $v['qtd_itens'] ?> item(ns)</td>
                <td><?= Helper::money((float)$v['subtotal']) ?></td>
                <td><?= $v['desconto'] > 0 ? '<span style="color:#E53E3E">-' . Helper::money((float)$v['desconto']) . '</span>' : '—' ?></td>
                <td><strong><?= Helper::money((float)$v['valor_final']) ?></strong></td>
                <td style="color:#2C5F2E;font-weight:700"><?= Helper::money((float)$v['lucro_total']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
