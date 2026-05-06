<?php use App\Core\Helper; $pageTitle = 'Compras de Insumos'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Histórico de Compras</span>
        <a href="/admin/compras/nova" class="adm-btn adm-btn-primary adm-btn-sm">+ Nova Compra</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>Data</th><th>Insumo</th><th>Fornecedor</th><th>Qtd</th><th>Total</th><th>Unit.</th><th>CMA Anterior</th><th>CMA Novo</th></tr>
            </thead>
            <tbody>
            <?php if (empty($compras)): ?>
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:#718096">Nenhuma compra registrada.</td></tr>
            <?php else: ?>
            <?php foreach ($compras as $c): ?>
            <tr>
                <td><?= Helper::date($c['data_compra']) ?></td>
                <td><strong><?= htmlspecialchars($c['insumo_nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= htmlspecialchars($c['fornecedor'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= number_format($c['quantidade'], 4, ',', '.') ?> <?= $c['unidade_medida'] ?></td>
                <td><?= Helper::money((float)$c['valor_total']) ?></td>
                <td><?= Helper::money((float)$c['valor_unitario']) ?>/<?= $c['unidade_medida'] ?></td>
                <td style="color:#718096"><?= Helper::money((float)$c['custo_medio_ant']) ?></td>
                <td style="color:#2C5F2E;font-weight:700"><?= Helper::money((float)$c['custo_medio_novo']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
