<?php
use App\Core\Helper;

$pageStyles = '<style>
.wh-pre {
    background:#1a1a2e;color:#e0e0e0;font-family:monospace;font-size:0.78rem;
    padding:1.25rem;border-radius:8px;overflow-x:auto;white-space:pre;
    max-height:480px;overflow-y:auto;line-height:1.55;
}
.wh-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.wh-meta-item { display:flex; flex-direction:column; gap:0.25rem; }
.wh-meta-label { font-size:0.72rem; text-transform:uppercase; letter-spacing:.06em; color:#888; }
.wh-meta-value { font-size:0.9rem; font-weight:500; color:#222; word-break:break-all; }
@media(max-width:640px){ .wh-meta-grid{ grid-template-columns:1fr; } }
</style>';

// helpers
$statusBadge = function(?string $s): string {
    $cls = match(strtolower($s ?? '')) {
        'approved'                       => 'adm-badge-success',
        'failed','declined'              => 'adm-badge-danger',
        'canceled','refunded'            => 'adm-badge-danger',
        'expired'                        => 'adm-badge-warning',
        default                          => 'adm-badge-gray',
    };
    $lbl = match(strtolower($s ?? '')) {
        'approved'  => 'Aprovado',
        'failed'    => 'Falhou',
        'declined'  => 'Recusado',
        'canceled'  => 'Cancelado',
        'refunded'  => 'Estornado',
        'expired'   => 'Expirado',
        default     => htmlspecialchars($s ?? '—', ENT_QUOTES, 'UTF-8'),
    };
    return "<span class=\"adm-badge {$cls}\">{$lbl}</span>";
};

$methodLabel = function(?string $m): string {
    if ($m === null || $m === '') return '—';
    return match(strtolower($m)) {
        'credit_card', 'cartao_credito' => 'Cartão de Crédito',
        'debit_card',  'cartao_debito'  => 'Cartão de Débito',
        'pix'                           => 'PIX',
        default                         => htmlspecialchars($m, ENT_QUOTES, 'UTF-8'),
    };
};

// Decode JSON payload for pretty-print
$payloadDecoded = null;
if (!empty($log['payload'])) {
    $decoded = json_decode($log['payload'], true);
    $payloadDecoded = $decoded !== null
        ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : $log['payload'];
}
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div style="margin-bottom:1.25rem">
    <a href="/admin/webhook_logs" class="adm-btn adm-btn-secondary adm-btn-sm">← Voltar à lista</a>
    <?php if ($log['order_nsu']): ?>
    <a href="/admin/pedidos?numero=<?= urlencode($log['order_nsu']) ?>"
       class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-left:.5rem">
        🛍️ Ver pedido <?= htmlspecialchars($log['order_nsu'], ENT_QUOTES, 'UTF-8') ?>
    </a>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">

    <!-- Identificação -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Identificação</span></div>
        <div class="adm-card-body">
            <div class="wh-meta-grid">
                <div class="wh-meta-item">
                    <span class="wh-meta-label">ID do Log</span>
                    <span class="wh-meta-value">#<?= (int)$log['id'] ?></span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Fonte</span>
                    <span class="wh-meta-value"><?= htmlspecialchars($log['source'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Recebido em</span>
                    <span class="wh-meta-value">
                        <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['criado_em'])), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">IP de Origem</span>
                    <span class="wh-meta-value"><?= htmlspecialchars($log['ip'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Order NSU</span>
                    <span class="wh-meta-value"><?= htmlspecialchars($log['order_nsu'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Pedido ID</span>
                    <span class="wh-meta-value">
                        <?php if ($log['pedido_id']): ?>
                        <a href="/admin/pedidos/<?= (int)$log['pedido_id'] ?>" style="color:var(--adm-verde)">
                            #<?= (int)$log['pedido_id'] ?>
                        </a>
                        <?php else: ?>—<?php endif; ?>
                    </span>
                </div>
                <div class="wh-meta-item" style="grid-column:1/-1">
                    <span class="wh-meta-label">Transaction NSU</span>
                    <span class="wh-meta-value"><?= htmlspecialchars($log['transaction_nsu'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagamento -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Dados do Pagamento</span></div>
        <div class="adm-card-body">
            <div class="wh-meta-grid">
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Status</span>
                    <span class="wh-meta-value"><?= $statusBadge($log['status']) ?></span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Processado</span>
                    <span class="wh-meta-value">
                        <?php if ((int)$log['processado'] === 1): ?>
                        <span class="adm-badge adm-badge-success">✓ Sim</span>
                        <?php else: ?>
                        <span class="adm-badge adm-badge-gray">Não</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Método de Captura</span>
                    <span class="wh-meta-value"><?= $methodLabel($log['capture_method']) ?></span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Parcelas</span>
                    <span class="wh-meta-value">
                        <?= $log['installments'] !== null ? (int)$log['installments'] . 'x' : '—' ?>
                    </span>
                </div>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Valor Pago</span>
                    <span class="wh-meta-value" style="font-size:1.1rem;color:#276749;font-weight:700">
                        <?= $log['paid_amount'] !== null ? Helper::money((float)$log['paid_amount']) : '—' ?>
                    </span>
                </div>
                <?php if (!empty($log['receipt_url'])): ?>
                <div class="wh-meta-item">
                    <span class="wh-meta-label">Comprovante</span>
                    <span class="wh-meta-value">
                        <a href="<?= htmlspecialchars($log['receipt_url'], ENT_QUOTES, 'UTF-8') ?>"
                           target="_blank" rel="noopener" style="color:var(--adm-verde)">
                            Abrir comprovante ↗
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($log['erro']): ?>
            <div style="margin-top:1rem;padding:.75rem 1rem;background:#FFF5F5;border:1px solid #FED7D7;border-radius:6px">
                <div class="wh-meta-label" style="color:#9B2C2C;margin-bottom:.35rem">Mensagem de Erro</div>
                <div style="font-size:0.85rem;color:#9B2C2C;word-break:break-word">
                    <?= htmlspecialchars($log['erro'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Payload JSON -->
<?php if ($payloadDecoded): ?>
<div class="adm-card" style="margin-bottom:1.25rem">
    <div class="adm-card-header">
        <span class="adm-card-title">Payload (JSON decodificado)</span>
    </div>
    <div class="adm-card-body" style="padding:0">
        <pre class="wh-pre"><?= htmlspecialchars($payloadDecoded, ENT_QUOTES, 'UTF-8') ?></pre>
    </div>
</div>
<?php endif; ?>

<!-- Raw Body -->
<?php if (!empty($log['raw_body'])): ?>
<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Corpo Bruto (raw_body)</span>
        <span style="font-size:0.8rem;color:#888;margin-left:.75rem">
            <?= number_format(strlen($log['raw_body'])) ?> bytes
        </span>
    </div>
    <div class="adm-card-body" style="padding:0">
        <pre class="wh-pre"><?= htmlspecialchars($log['raw_body'], ENT_QUOTES, 'UTF-8') ?></pre>
    </div>
</div>
<?php endif; ?>
