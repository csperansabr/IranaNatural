<?php
$pageTitle = 'Configurações';
$pageBreadcrumb = 'Ferramentas / Configurações';
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<form method="POST" action="/admin/configuracoes">

    <!-- Pagamento Online -->
    <div class="adm-card" style="max-width:640px;margin-bottom:1.5rem">
        <div class="adm-card-header">
            <span class="adm-card-title">Pagamento Online (InfinitePay)</span>
        </div>
        <div class="adm-card-body">
            <p style="font-size:0.9rem;color:#555">
                A seleção de forma de pagamento (PIX, cartão de crédito, débito) é realizada diretamente no ambiente da InfinitePay. Não há configurações de desconto PIX ou parcelas neste sistema.
            </p>
        </div>
    </div>

    <!-- Credenciais InfinitePay -->
    <div class="adm-card" style="max-width:640px;margin-bottom:1.5rem">
        <div class="adm-card-header">
            <span class="adm-card-title">Credenciais InfinitePay</span>
        </div>
        <div class="adm-card-body">
            <p style="font-size:0.85rem;color:#666;margin-bottom:1rem">
                As credenciais são configuradas no arquivo <code>config/payment.php</code> por segurança e não podem ser alteradas aqui.
            </p>
            <div class="adm-form-group">
                <label>Handle</label>
                <input type="text" value="<?= defined('INFINITEPAY_HANDLE') ? htmlspecialchars(INFINITEPAY_HANDLE, ENT_QUOTES) : '(não configurado)' ?>"
                       readonly style="background:#f5f5f5;max-width:300px">
            </div>
            <div class="adm-form-group">
                <label>API URL</label>
                <input type="text" value="<?= defined('INFINITEPAY_API_URL') ? htmlspecialchars(INFINITEPAY_API_URL, ENT_QUOTES) : '' ?>"
                       readonly style="background:#f5f5f5;max-width:380px">
            </div>
            <div class="adm-form-group">
                <label>Webhook Secret</label>
                <input type="text" value="<?= defined('INFINITEPAY_WEBHOOK_SECRET') && INFINITEPAY_WEBHOOK_SECRET ? str_repeat('•', min(strlen(INFINITEPAY_WEBHOOK_SECRET), 24)) : '(não configurado)' ?>"
                       readonly style="background:#f5f5f5;max-width:220px">
            </div>
        </div>
    </div>

    <div style="display:flex;gap:0.75rem">
        <button type="submit" class="adm-btn adm-btn-primary">Salvar configurações</button>
        <a href="/admin/dashboard" class="adm-btn adm-btn-secondary">Cancelar</a>
    </div>

</form>
