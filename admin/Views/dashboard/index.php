<?php
use App\Core\Helper;
$pageTitle = 'Dashboard';
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<!-- Stats do Mês -->
<div class="adm-stats">
    <div class="adm-stat verde">
        <div class="adm-stat-label">Vendas no Mês</div>
        <div class="adm-stat-value"><?= Helper::money((float)($stats['total'] ?? 0)) ?></div>
        <div class="adm-stat-sub"><?= $stats['qtd'] ?? 0 ?> venda(s)</div>
    </div>
    <div class="adm-stat azul">
        <div class="adm-stat-label">Lucro Estimado</div>
        <div class="adm-stat-value"><?= Helper::money((float)($stats['lucro'] ?? 0)) ?></div>
        <div class="adm-stat-sub">mês atual</div>
    </div>
    <div class="adm-stat laranja">
        <div class="adm-stat-label">Alertas de Estoque</div>
        <div class="adm-stat-value"><?= count($alertasProdutos) + count($alertasInsumos) ?></div>
        <div class="adm-stat-sub"><?= count($alertasInsumos) ?> insumo(s), <?= count($alertasProdutos) ?> produto(s)</div>
    </div>
    <div class="adm-stat roxo">
        <div class="adm-stat-label">Produções Recentes</div>
        <div class="adm-stat-value"><?= count($producaoRecente) ?></div>
        <div class="adm-stat-sub">últimas registradas</div>
    </div>
</div>

<!-- Gráfico + Top Produtos -->
<div class="adm-grid-2">
    <div class="adm-card">
        <div class="adm-card-header">
            <span class="adm-card-title">📈 Vendas — últimos 30 dias</span>
        </div>
        <div class="adm-card-body">
            <div class="chart-container">
                <canvas id="chart-vendas"></canvas>
            </div>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-header">
            <span class="adm-card-title">🏆 Produtos Mais Vendidos</span>
        </div>
        <div class="adm-card-body" style="padding:0">
            <table class="adm-table">
                <thead><tr><th>Produto</th><th>Qtd</th><th>Lucro</th></tr></thead>
                <tbody>
                <?php if (empty($maisVendidos)): ?>
                <tr><td colspan="3" style="text-align:center;color:#718096;padding:2rem">Nenhuma venda registrada.</td></tr>
                <?php else: ?>
                <?php foreach ($maisVendidos as $mv): ?>
                <tr>
                    <td><?= htmlspecialchars($mv['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $mv['total_vendido'] ?></td>
                    <td><?= Helper::money((float)$mv['total_lucro']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Alertas de Estoque -->
<?php if (!empty($alertasInsumos) || !empty($alertasProdutos)): ?>
<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">⚠️ Alertas de Estoque Baixo</span>
    </div>
    <div class="adm-card-body" style="padding:0">
        <table class="adm-table">
            <thead><tr><th>Tipo</th><th>Item</th><th>Estoque Atual</th><th>Mínimo</th><th>Ação</th></tr></thead>
            <tbody>
            <?php foreach ($alertasInsumos as $i): ?>
            <tr>
                <td><span class="adm-badge adm-badge-warning">Insumo</span></td>
                <td><?= htmlspecialchars($i['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="estoque-baixo"><?= number_format($i['estoque_atual'],2,',','.') ?> <?= $i['unidade_medida'] ?></td>
                <td><?= number_format($i['estoque_minimo'],2,',','.') ?> <?= $i['unidade_medida'] ?></td>
                <td><a href="/admin/compras/nova" class="adm-btn adm-btn-warning adm-btn-sm">Comprar</a></td>
            </tr>
            <?php endforeach; ?>
            <?php foreach ($alertasProdutos as $p): ?>
            <tr>
                <td><span class="adm-badge adm-badge-danger">Produto</span></td>
                <td><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="estoque-baixo"><?= $p['estoque_atual'] ?> un</td>
                <td><?= $p['estoque_minimo'] ?> un</td>
                <td><a href="/admin/producao/nova" class="adm-btn adm-btn-primary adm-btn-sm">Produzir</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Atividade recente -->
<div class="adm-grid-2">
    <div class="adm-card">
        <div class="adm-card-header">
            <span class="adm-card-title">⚗️ Produções Recentes</span>
            <a href="/admin/producao" class="adm-btn adm-btn-secondary adm-btn-sm">Ver todas</a>
        </div>
        <div class="adm-card-body" style="padding:0">
            <table class="adm-table">
                <thead><tr><th>Data</th><th>Produto</th><th>Qtd</th><th>Custo</th></tr></thead>
                <tbody>
                <?php if (empty($producaoRecente)): ?>
                <tr><td colspan="4" style="text-align:center;color:#718096;padding:1.5rem">Nenhuma produção.</td></tr>
                <?php else: ?>
                <?php foreach ($producaoRecente as $pr): ?>
                <tr>
                    <td><?= Helper::date($pr['data_producao']) ?></td>
                    <td><?= htmlspecialchars($pr['produto_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $pr['quantidade_produzida'] ?></td>
                    <td><?= Helper::money((float)$pr['custo_real']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-header">
            <span class="adm-card-title">🛒 Compras Recentes</span>
            <a href="/admin/compras" class="adm-btn adm-btn-secondary adm-btn-sm">Ver todas</a>
        </div>
        <div class="adm-card-body" style="padding:0">
            <table class="adm-table">
                <thead><tr><th>Data</th><th>Insumo</th><th>Qtd</th><th>Total</th></tr></thead>
                <tbody>
                <?php if (empty($comprasRecentes)): ?>
                <tr><td colspan="4" style="text-align:center;color:#718096;padding:1.5rem">Nenhuma compra.</td></tr>
                <?php else: ?>
                <?php foreach ($comprasRecentes as $c): ?>
                <tr>
                    <td><?= Helper::date($c['data_compra']) ?></td>
                    <td><?= htmlspecialchars($c['insumo_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= number_format($c['quantidade'],2,',','.') ?> <?= $c['unidade_medida'] ?></td>
                    <td><?= Helper::money((float)$c['valor_total']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    const dados  = <?= json_encode($grafVendas, JSON_UNESCAPED_UNICODE) ?>;
    const labels = dados.map(d => {
        const [y,m,dy] = d.dia.split('-');
        return `${dy}/${m}`;
    });
    const valores = dados.map(d => parseFloat(d.total));

    const ctx = document.getElementById('chart-vendas');
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Vendas (R$)',
                data: valores,
                fill: true,
                backgroundColor: 'rgba(44,95,46,0.08)',
                borderColor: '#2C5F2E',
                pointBackgroundColor: '#2C5F2E',
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { ticks: { callback: v => 'R$' + v.toFixed(0), font: { size: 11 } } }
            }
        }
    });
})();
</script>
