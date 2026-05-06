<?php use App\Core\Helper; $pageTitle = 'Insumos'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Insumos / Matérias-primas</span>
        <a href="/admin/insumos/novo" class="adm-btn adm-btn-primary adm-btn-sm">+ Novo Insumo</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>Nome</th><th>Unidade</th><th>Estoque</th><th>Mín.</th><th>Custo Médio</th><th>Fornecedor</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php if (empty($insumos)): ?>
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:#718096">Nenhum insumo cadastrado.</td></tr>
            <?php else: ?>
            <?php foreach ($insumos as $ins): ?>
            <?php $alerta = (float)$ins['estoque_atual'] <= (float)$ins['estoque_minimo']; ?>
            <tr>
                <td><strong><?= htmlspecialchars($ins['nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= htmlspecialchars($ins['unidade_medida'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="<?= $alerta ? 'estoque-baixo' : 'estoque-ok' ?>">
                    <?= number_format($ins['estoque_atual'], 4, ',', '.') ?>
                    <?php if ($alerta): ?> ⚠️<?php endif; ?>
                </td>
                <td><?= number_format($ins['estoque_minimo'], 2, ',', '.') ?></td>
                <td><?= Helper::money((float)$ins['custo_medio']) ?>/<?= $ins['unidade_medida'] ?></td>
                <td><?= htmlspecialchars($ins['fornecedor'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ($ins['ativo']): ?>
                    <span class="adm-badge adm-badge-success">Ativo</span>
                    <?php else: ?>
                    <span class="adm-badge adm-badge-gray">Inativo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/admin/insumos/<?= $ins['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm">Editar</a>
                    <a href="/admin/compras/nova" class="adm-btn adm-btn-warning adm-btn-sm" title="Registrar compra deste insumo">🛒</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
