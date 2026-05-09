<?php use App\Core\Helper; use App\Models\Pedido; ?>

<?php if ($flash['msg']): ?>
<div class="adm-alert adm-alert--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="adm-card" style="margin-bottom:1.5rem;">
    <form method="GET" action="/admin/pedidos" class="adm-filter-form">
        <div class="adm-filter-row">
            <div class="adm-filter-group">
                <label class="adm-label">Nº Pedido</label>
                <input type="text" name="numero" class="adm-input" value="<?= htmlspecialchars($filtros['numero'], ENT_QUOTES, 'UTF-8') ?>" placeholder="IRA-...">
            </div>
            <div class="adm-filter-group">
                <label class="adm-label">Cliente</label>
                <input type="text" name="cliente" class="adm-input" value="<?= htmlspecialchars($filtros['cliente'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Nome do cliente">
            </div>
            <div class="adm-filter-group">
                <label class="adm-label">Status</label>
                <select name="status" class="adm-input">
                    <option value="">Todos</option>
                    <?php foreach (['pendente','pago','separando','enviado','entregue','cancelado'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filtros['status'] === $s ? 'selected' : '' ?>><?= Pedido::statusLabel($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-filter-group">
                <label class="adm-label">Data inicial</label>
                <input type="date" name="data_ini" class="adm-input" value="<?= htmlspecialchars($filtros['data_ini'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="adm-filter-group">
                <label class="adm-label">Data final</label>
                <input type="date" name="data_fim" class="adm-input" value="<?= htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="adm-filter-group adm-filter-group--actions">
                <button type="submit" class="adm-btn adm-btn-primary">🔍 Filtrar</button>
                <a href="/admin/pedidos" class="adm-btn adm-btn-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>

<!-- Tabela de pedidos -->
<div class="adm-card">
    <div class="adm-card-header">
        <h2>Pedidos Online <span class="adm-badge"><?= count($pedidos) ?></span></h2>
    </div>

    <?php if (empty($pedidos)): ?>
    <div class="adm-empty">Nenhum pedido encontrado com os filtros aplicados.</div>
    <?php else: ?>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Total</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['numero'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td>
                        <div><?= htmlspecialchars($p['cliente_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                        <small><?= htmlspecialchars($p['cliente_email'], ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td><?= Helper::datetime($p['criado_em']) ?></td>
                    <td><?= (int)$p['qtd_itens'] ?></td>
                    <td><strong><?= Helper::money((float)$p['total']) ?></strong></td>
                    <td><?= Pedido::pagamentoLabel($p['forma_pagamento']) ?></td>
                    <td>
                        <span class="adm-status adm-status--<?= $p['status'] ?>">
                            <?= Pedido::statusLabel($p['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="/admin/pedidos/<?= $p['id'] ?>" class="adm-btn adm-btn-sm adm-btn-secondary">Ver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
