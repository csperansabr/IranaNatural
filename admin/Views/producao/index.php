<?php use App\Core\Helper; $pageTitle = 'Controle de Produção'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Histórico de Produções</span>
        <a href="/admin/producao/nova" class="adm-btn adm-btn-primary adm-btn-sm">+ Registrar Produção</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>Data</th><th>Produto</th><th>Qtd Produzida</th><th>Perdas</th><th>Estoque +</th><th>Custo Real</th><th>Responsável</th></tr>
            </thead>
            <tbody>
            <?php if (empty($producoes)): ?>
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:#718096">Nenhuma produção registrada.</td></tr>
            <?php else: ?>
            <?php foreach ($producoes as $pr): ?>
            <tr>
                <td><?= Helper::date($pr['data_producao']) ?></td>
                <td><strong><?= htmlspecialchars($pr['produto_nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= $pr['quantidade_produzida'] ?> un</td>
                <td><?= $pr['quantidade_perda'] > 0 ? '<span style="color:#E53E3E">' . $pr['quantidade_perda'] . ' un</span>' : '—' ?></td>
                <td style="color:#2C5F2E;font-weight:700"><?= $pr['quantidade_produzida'] - $pr['quantidade_perda'] ?> un</td>
                <td><?= Helper::money((float)$pr['custo_real']) ?></td>
                <td><?= htmlspecialchars($pr['responsavel'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
