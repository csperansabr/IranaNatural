<?php
$pageTitle      = 'Histórico de Importações';
$pageBreadcrumb = '<a href="/admin/importacao">Importação</a> / Histórico';

$entidadeLabels = ['produtos' => 'Produtos', 'insumos' => 'Insumos', 'estoque' => 'Estoque'];
$entidadeCores  = ['produtos' => '#2C5F2E',  'insumos' => '#5D7A3A',  'estoque' => '#6B4E37'];
$modoLabels     = [
    'criar'           => 'Criar novos',
    'atualizar'       => 'Atualizar existentes',
    'criar_atualizar' => 'Criar e atualizar',
];
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>" style="margin-bottom:1.5rem">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <a href="/admin/importacao" class="adm-btn adm-btn-secondary adm-btn-sm">← Voltar</a>
    <!-- Filter by entity -->
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
        <span style="font-size:.85rem;color:#718096">Filtrar:</span>
        <button class="adm-btn adm-btn-sm filter-btn active" data-filter="all">Todos</button>
        <button class="adm-btn adm-btn-sm filter-btn" data-filter="produtos" style="background:#2C5F2E;color:#fff">Produtos</button>
        <button class="adm-btn adm-btn-sm filter-btn" data-filter="insumos"  style="background:#5D7A3A;color:#fff">Insumos</button>
        <button class="adm-btn adm-btn-sm filter-btn" data-filter="estoque"  style="background:#6B4E37;color:#fff">Estoque</button>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        📋 Histórico de Importações <small style="font-weight:400;color:#718096">(últimas 100)</small>
    </div>
    <div class="adm-card-body" style="padding:0">
        <?php if (empty($historico)): ?>
            <p style="padding:2rem;text-align:center;color:#718096">Nenhuma importação registrada ainda.</p>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="adm-table" id="hist-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Data</th>
                    <th>Entidade</th>
                    <th>Modo</th>
                    <th>Arquivo</th>
                    <th style="text-align:center">Total</th>
                    <th style="text-align:center">Inseridos</th>
                    <th style="text-align:center">Atualizados</th>
                    <th style="text-align:center">Erros</th>
                    <th style="text-align:center">Ignorados</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historico as $h): ?>
                <tr data-entidade="<?= htmlspecialchars($h['entidade'], ENT_QUOTES, 'UTF-8') ?>">
                    <td style="color:#aaa;font-size:.8rem"><?= (int)$h['id'] ?></td>
                    <td style="white-space:nowrap;font-size:.85rem">
                        <?= date('d/m/Y', strtotime($h['criado_em'])) ?><br>
                        <small style="color:#718096"><?= date('H:i:s', strtotime($h['criado_em'])) ?></small>
                    </td>
                    <td>
                        <span class="adm-badge" style="background:<?= $entidadeCores[$h['entidade']] ?? '#718096' ?>;color:#fff;white-space:nowrap">
                            <?= $entidadeLabels[$h['entidade']] ?? htmlspecialchars($h['entidade'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:#444;white-space:nowrap">
                        <?= $modoLabels[$h['modo']] ?? htmlspecialchars($h['modo'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td style="font-size:.82rem;max-width:200px">
                        <span title="<?= htmlspecialchars($h['arquivo_nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                              style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            <?= htmlspecialchars($h['arquivo_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td style="text-align:center;font-size:.9rem"><?= (int)$h['total_linhas'] ?></td>
                    <td style="text-align:center">
                        <?php $v = (int)$h['inseridos']; ?>
                        <?php if ($v > 0): ?>
                            <span class="adm-badge" style="background:#2C5F2E;color:#fff"><?= $v ?></span>
                        <?php else: ?>
                            <span style="color:#CBD5E0">0</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php $v = (int)$h['atualizados']; ?>
                        <?php if ($v > 0): ?>
                            <span class="adm-badge" style="background:#2B6CB0;color:#fff"><?= $v ?></span>
                        <?php else: ?>
                            <span style="color:#CBD5E0">0</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php $v = (int)$h['erros']; ?>
                        <?php if ($v > 0): ?>
                            <span class="adm-badge" style="background:#E53E3E;color:#fff"><?= $v ?></span>
                        <?php else: ?>
                            <span style="color:#CBD5E0">0</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php $v = (int)$h['ignorados']; ?>
                        <?php if ($v > 0): ?>
                            <span class="adm-badge" style="background:#718096;color:#fff"><?= $v ?></span>
                        <?php else: ?>
                            <span style="color:#CBD5E0">0</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-btn { transition: outline .15s, box-shadow .15s; }
.filter-btn.active { outline: 2px solid #2D3748; outline-offset: 1px; }
</style>
<script>
(function () {
    const btns  = document.querySelectorAll('.filter-btn');
    const rows  = document.querySelectorAll('#hist-table tbody tr');

    // Set initial active outline on "Todos" button
    const initialActive = document.querySelector('.filter-btn.active');
    if (initialActive) initialActive.style.outline = '2px solid #2D3748';

    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            btns.forEach(b => {
                b.classList.remove('active');
                b.style.outline = '';
            });
            btn.classList.add('active');
            btn.style.outline = '2px solid #2D3748';

            const filter = btn.dataset.filter;
            rows.forEach(row => {
                if (filter === 'all' || row.dataset.entidade === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
})();
</script>
