<?php
$pageTitle      = 'Configurações';
$pageBreadcrumb = 'Ferramentas / Configurações';

// Serviços Melhor Envio disponíveis para seleção
$servicosME = [
    1  => 'Correios — PAC',
    2  => 'Correios — SEDEX',
    9  => 'Jadlog — .Package',
    10 => 'Jadlog — .Com',
];
$servicosAtivos = array_filter(array_map('intval', explode(',', $config['frete_services'] ?? '1,2,9,10')));
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<form method="POST" action="/admin/configuracoes">

    <!-- ── Pagamento Online ─────────────────────────────────────── -->
    <div class="adm-card" style="max-width:680px;margin-bottom:1.5rem">
        <div class="adm-card-header">
            <span class="adm-card-title">Pagamento Online (InfinitePay)</span>
        </div>
        <div class="adm-card-body">
            <p style="font-size:0.9rem;color:#555">
                A seleção de forma de pagamento (PIX ou cartão de crédito) é realizada diretamente no ambiente da InfinitePay. Não há configurações de parcelas neste sistema.
            </p>
        </div>
    </div>

    <!-- ── Credenciais InfinitePay ──────────────────────────────── -->
    <div class="adm-card" style="max-width:680px;margin-bottom:1.5rem">
        <div class="adm-card-header">
            <span class="adm-card-title">Credenciais InfinitePay</span>
        </div>
        <div class="adm-card-body">
            <p style="font-size:0.85rem;color:#666;margin-bottom:1rem">
                As credenciais são configuradas no arquivo <code>config/payment.php</code> por segurança e não podem ser alteradas aqui.
            </p>
            <div class="adm-form-grid adm-form-grid-2">
                <div class="adm-form-group">
                    <label>Handle</label>
                    <input type="text"
                           value="<?= defined('INFINITEPAY_HANDLE') ? htmlspecialchars(INFINITEPAY_HANDLE, ENT_QUOTES) : '(não configurado)' ?>"
                           readonly style="background:#f5f5f5">
                </div>
                <div class="adm-form-group">
                    <label>Webhook Secret</label>
                    <input type="text"
                           value="<?= defined('INFINITEPAY_WEBHOOK_SECRET') && INFINITEPAY_WEBHOOK_SECRET ? str_repeat('•', min(strlen(INFINITEPAY_WEBHOOK_SECRET), 24)) : '(não configurado)' ?>"
                           readonly style="background:#f5f5f5">
                </div>
            </div>
            <div class="adm-form-group">
                <label>API URL</label>
                <input type="text"
                       value="<?= defined('INFINITEPAY_API_URL') ? htmlspecialchars(INFINITEPAY_API_URL, ENT_QUOTES) : '' ?>"
                       readonly style="background:#f5f5f5">
            </div>
        </div>
    </div>

    <!-- ── Frete — Melhor Envio ─────────────────────────────────── -->
    <div class="adm-card" style="max-width:680px;margin-bottom:1.5rem">
        <div class="adm-card-header">
            <span class="adm-card-title">Frete — Melhor Envio</span>
        </div>
        <div class="adm-card-body">

            <!-- Token Sandbox -->
            <div class="adm-form-group">
                <label>Token — Sandbox <small style="color:#e67e22">(testes)</small></label>
                <input type="text" name="frete_token_sandbox"
                       class="adm-input" style="font-family:monospace;font-size:0.8rem"
                       placeholder="Cole aqui o token JWT do ambiente Sandbox"
                       value="">
                <div class="adm-hint">
                    <?php if (!empty($config['frete_token_sandbox'])): ?>
                        Token configurado (<?= strlen($config['frete_token_sandbox']) ?> chars).
                        Deixe em branco para manter o atual.
                    <?php else: ?>
                        Nenhum token configurado. Obtenha em sandbox.melhorenvio.com.br.
                    <?php endif; ?>
                </div>
            </div>

            <!-- Token Produção -->
            <div class="adm-form-group" style="margin-top:1rem">
                <label>Token — Produção <small style="color:#2C5F2E">(real)</small></label>
                <input type="text" name="frete_token_producao"
                       class="adm-input" style="font-family:monospace;font-size:0.8rem"
                       placeholder="Cole aqui o token JWT do ambiente de Produção"
                       value="">
                <div class="adm-hint">
                    <?php if (!empty($config['frete_token_producao'])): ?>
                        Token configurado (<?= strlen($config['frete_token_producao']) ?> chars).
                        Deixe em branco para manter o atual.
                    <?php else: ?>
                        Nenhum token configurado. Obtenha em app.melhorenvio.com.br.
                    <?php endif; ?>
                </div>
            </div>

            <hr style="border:none;border-top:1px solid #E2E8F0;margin:1.25rem 0">

            <!-- Habilitar frete -->
            <!-- Verificação SSL -->
            <div class="adm-form-group" style="margin-top:1rem">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600">
                    <input type="checkbox" name="frete_ssl_verify" value="1"
                           style="width:16px;height:16px;accent-color:var(--verde-floresta,#2C5F2E)"
                           <?= (!isset($config['frete_ssl_verify']) || $config['frete_ssl_verify'] === '1') ? 'checked' : '' ?>>
                    Verificar certificado SSL nas chamadas à API
                </label>
                <div class="adm-hint" style="color:#c0392b;font-weight:500">
                    <strong>Desmarque apenas em ambiente local (Laragon/WAMP)</strong> — servidores de hospedagem devem manter ativado.
                    Desativar em produção é um risco de segurança.
                </div>
            </div>
            <div class="adm-form-group">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600">
                    <input type="checkbox" name="frete_ativo" value="1"
                           style="width:16px;height:16px;accent-color:var(--verde-floresta,#2C5F2E)"
                           <?= !empty($config['frete_ativo']) && $config['frete_ativo'] === '1' ? 'checked' : '' ?>>
                    Habilitar cálculo de frete via Melhor Envio
                </label>
                <div class="adm-hint">Quando desabilitado, apenas as opções locais (Retirada, Uber, Motoboy) são exibidas no checkout.</div>
            </div>

            <!-- Ambiente -->
            <div class="adm-form-group" style="margin-top:1rem">
                <label>Ambiente da API</label>
                <div style="display:flex;gap:1.5rem;margin-top:0.35rem">
                    <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer">
                        <input type="radio" name="frete_sandbox" value="1"
                               <?= (!isset($config['frete_sandbox']) || $config['frete_sandbox'] === '1') ? 'checked' : '' ?>>
                        <span>Sandbox <small style="color:#e67e22">(testes)</small></span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer">
                        <input type="radio" name="frete_sandbox" value="0"
                               <?= (isset($config['frete_sandbox']) && $config['frete_sandbox'] === '0') ? 'checked' : '' ?>>
                        <span>Produção <small style="color:#2C5F2E">(real)</small></span>
                    </label>
                </div>
                <div class="adm-hint">
                    Sandbox: <code>sandbox.melhorenvio.com.br</code> — não gera cobranças.<br>
                    Produção: <code>melhorenvio.com.br</code> — usar somente após validação.
                </div>
            </div>

            <!-- CEP de origem -->
            <div class="adm-form-group" style="margin-top:1rem">
                <label>CEP de origem <span style="color:#c0392b">*</span></label>
                <input type="text" name="frete_cep_origem"
                       class="adm-input" style="max-width:150px"
                       placeholder="00000-000" maxlength="9"
                       value="<?= htmlspecialchars($this->formatarCep($config['frete_cep_origem'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <div class="adm-hint">CEP do endereço de onde os produtos são despachados.</div>
            </div>

            <!-- Timeout -->
            <div class="adm-form-group" style="margin-top:1rem">
                <label>Timeout da API (segundos)</label>
                <input type="number" name="frete_timeout"
                       class="adm-input" style="max-width:100px"
                       min="5" max="60"
                       value="<?= (int)($config['frete_timeout'] ?? 15) ?>">
                <div class="adm-hint">Tempo máximo de espera pela resposta da API (mín. 5 s, máx. 60 s).</div>
            </div>

            <!-- Serviços habilitados -->
            <div class="adm-form-group" style="margin-top:1rem">
                <label>Serviços habilitados</label>
                <div style="display:flex;flex-direction:column;align-items:flex-start;gap:0.5rem;margin-top:0.4rem">
                    <?php foreach ($servicosME as $idSrv => $nomeSrv): ?>
                    <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-weight:400;text-transform:none;letter-spacing:0;font-size:0.875rem">
                        <input type="checkbox" name="frete_services[]" value="<?= $idSrv ?>"
                               style="width:16px;height:16px;flex-shrink:0;accent-color:var(--verde-floresta,#2C5F2E)"
                               <?= in_array($idSrv, $servicosAtivos, true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($nomeSrv, ENT_QUOTES, 'UTF-8') ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="adm-hint">Selecione quais serviços serão consultados na API do Melhor Envio.</div>
            </div>

        </div>
    </div>

    <div style="display:flex;gap:0.75rem;max-width:680px">
        <button type="submit" class="adm-btn adm-btn-primary">Salvar configurações</button>
        <a href="/admin/dashboard" class="adm-btn adm-btn-secondary">Cancelar</a>
    </div>

</form>

<script>
/* Máscara CEP no campo de origem */
(function () {
    var inp = document.querySelector('input[name="frete_cep_origem"]');
    if (!inp) return;
    inp.addEventListener('input', function () {
        var d = this.value.replace(/\D/g, '').slice(0, 8);
        this.value = d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d;
    });
}());
</script>
