<?php
$pageTitle      = 'Logs de Webhook';
$pageBreadcrumb = 'E-commerce / Logs de Webhook';

// ── Helpers de apresentação ────────────────────────────────────────
function whBadgeClass(?string $status): string {
    return match(strtolower($status ?? '')) {
        'approved'           => 'adm-badge-success',
        'failed', 'declined' => 'adm-badge-danger',
        'canceled', 'refunded' => 'adm-badge-danger',
        'expired'            => 'adm-badge-warning',
        default              => 'adm-badge-gray',
    };
}
function whStatusLabel(?string $status): string {
    if ($status === null || $status === '') return '—';
    return match(strtolower($status)) {
        'approved'  => 'Aprovado',
        'failed'    => 'Falhou',
        'declined'  => 'Recusado',
        'canceled'  => 'Cancelado',
        'refunded'  => 'Estornado',
        'expired'   => 'Expirado',
        default     => htmlspecialchars($status, ENT_QUOTES, 'UTF-8'),
    };
}
function whMethodLabel(?string $m): string {
    if ($m === null || $m === '') return '—';
    return match(strtolower($m)) {
        'credit_card', 'cartao_credito' => 'Cartão Crédito',
        'debit_card',  'cartao_debito'  => 'Cartão Débito',
        'pix'                           => 'PIX',
        default                         => htmlspecialchars($m, ENT_QUOTES, 'UTF-8'),
    };
}

// ── Stats a partir da lista filtrada ──────────────────────────────
$total      = count($logs);
$processados = 0; $comErro = 0; $valorTotal = 0.0;
foreach ($logs as $l) {
    if ((int)$l['processado'] === 1) $processados++;
    if ($l['erro'] !== null && $l['erro'] !== '') $comErro++;
    if ($l['paid_amount'] !== null) $valorTotal += (float)$l['paid_amount'];
}
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="adm-stats" style="margin-bottom:1.5rem">
    <div class="adm-stat">
        <div class="adm-stat-label">Total (página)</div>
        <div class="adm-stat-value"><?= $total ?></div>
        <div class="adm-stat-sub">registros</div>
    </div>
    <div class="adm-stat verde">
        <div class="adm-stat-label">Processados</div>
        <div class="adm-stat-value"><?= $processados ?></div>
        <div class="adm-stat-sub"><?= $total > 0 ? round($processados / $total * 100) : 0 ?>% do total</div>
    </div>
    <div class="adm-stat <?= $comErro > 0 ? 'laranja' : '' ?>">
        <div class="adm-stat-label">Com Erro</div>
        <div class="adm-stat-value"><?= $comErro ?></div>
        <div class="adm-stat-sub"><?= $total > 0 ? round($comErro / $total * 100) : 0 ?>% do total</div>
    </div>
    <div class="adm-stat">
        <div class="adm-stat-label">Valor Pago Total</div>
        <div class="adm-stat-value" style="font-size:1.2rem"><?= \App\Core\Helper::money($valorTotal) ?></div>
        <div class="adm-stat-sub">aprovados</div>
    </div>
</div>

<!-- Filtros -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <form method="GET" action="/admin/webhook_logs" class="adm-filter-form">
        <div class="adm-filter-row">

            <div class="adm-filter-group">
                <label class="adm-label">Order NSU / Pedido</label>
                <input type="text" name="order_nsu" class="adm-input"
                       value="<?= htmlspecialchars($filtros['order_nsu'], ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="IRA-...">
            </div>

            <div class="adm-filter-group">
                <label class="adm-label">Status</label>
                <select name="status" class="adm-input">
                    <option value="">Todos</option>
                    <?php foreach (['approved','failed','declined','canceled','refunded','expired'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filtros['status'] ?? '') === $s ? 'selected' : '' ?>>
                        <?= whStatusLabel($s) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="adm-filter-group">
                <label class="adm-label">Processado</label>
                <select name="processado" class="adm-input">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filtros['processado'] ?? '') === '1' ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= ($filtros['processado'] ?? '') === '0' ? 'selected' : '' ?>>Não / Erro</option>
                </select>
            </div>

            <div class="adm-filter-group">
                <label class="adm-label">Data inicial</label>
                <input type="date" name="data_ini" class="adm-input"
                       value="<?= htmlspecialchars($filtros['data_ini'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="adm-filter-group">
                <label class="adm-label">Data final</label>
                <input type="date" name="data_fim" class="adm-input"
                       value="<?= htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="adm-filter-group adm-filter-group--actions">
                <button type="submit" class="adm-btn adm-btn-primary">🔍 Filtrar</button>
                <a href="/admin/webhook_logs" class="adm-btn adm-btn-secondary">Limpar</a>
            </div>

        </div>
    </form>
</div>

<!-- Tabela -->
<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">
            Chamadas recebidas
            <span class="adm-badge adm-badge-gray" style="margin-left:.5rem"><?= $total ?></span>
        </span>
    </div>

    <?php if (empty($logs)): ?>
    <div class="adm-empty">Nenhum registro encontrado com os filtros aplicados.</div>
    <?php else: ?>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Data / Hora</th>
                    <th>Order NSU</th>
                    <th>Status</th>
                    <th>Método</th>
                    <th style="text-align:right">Valor Pago</th>
                    <th style="text-align:center">Processado</th>
                    <th>Erro</th>
                    <th style="width:70px">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td style="color:#aaa;font-size:0.8rem"><?= (int)$l['id'] ?></td>
                    <td style="white-space:nowrap;font-size:0.85rem">
                        <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($l['criado_em'])), ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <?php if ($l['order_nsu']): ?>
                        <a href="/admin/pedidos?numero=<?= urlencode($l['order_nsu']) ?>"
                           style="font-weight:600;color:var(--adm-verde)">
                            <?= htmlspecialchars($l['order_nsu'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php else: ?>
                        <span style="color:#aaa">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($l['status']): ?>
                        <span class="adm-badge <?= whBadgeClass($l['status']) ?>">
                            <?= whStatusLabel($l['status']) ?>
                        </span>
                        <?php else: ?>
                        <span style="color:#aaa">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= whMethodLabel($l['capture_method']) ?></td>
                    <td style="text-align:right;font-weight:600">
                        <?= $l['paid_amount'] !== null ? \App\Core\Helper::money((float)$l['paid_amount']) : '—' ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ((int)$l['processado'] === 1): ?>
                        <span class="adm-badge adm-badge-success">✓ Sim</span>
                        <?php else: ?>
                        <span class="adm-badge adm-badge-gray">Não</span>
                        <?php endif; ?>
                    </td>
                    <td style="max-width:200px">
                        <?php if ($l['erro']): ?>
                        <span style="color:#9B2C2C;font-size:0.8rem" title="<?= htmlspecialchars($l['erro'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(mb_strimwidth($l['erro'], 0, 60, '…'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <?php else: ?>
                        <span style="color:#aaa">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/admin/webhook_logs/<?= (int)$l['id'] ?>" class="adm-btn adm-btn-secondary adm-btn-sm">
                            Ver
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
