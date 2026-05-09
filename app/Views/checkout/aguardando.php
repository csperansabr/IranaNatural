<?php use App\Core\Helper; use App\Models\Pedido; ?>

<section class="section-aguardando">
    <div class="container container--narrow">
        <div class="aguardando-card" id="aguardando-card">

            <div class="aguardando-spinner" id="aguardando-spinner">
                <svg viewBox="0 0 50 50" width="56" height="56" class="spinner-svg">
                    <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"
                            stroke-dasharray="80 40" stroke-linecap="round"/>
                </svg>
            </div>

            <h1 class="aguardando-titulo" id="aguardando-titulo">Aguardando pagamento</h1>

            <p class="aguardando-subtitulo" id="aguardando-subtitulo">
                Você foi redirecionado para a página de pagamento da InfinitePay.<br>
                Após confirmar o pagamento, esta página será atualizada automaticamente.
            </p>

            <div class="aguardando-numero">
                <span class="aguardando-numero__label">Pedido</span>
                <span class="aguardando-numero__valor"><?= Helper::e($pedido['numero']) ?></span>
            </div>

            <div class="aguardando-status" id="aguardando-status">
                <span class="aguardando-status__badge aguardando-status__badge--pendente">
                    <?= Pedido::statusLabel($pedido['status']) ?>
                </span>
            </div>

            <div class="aguardando-acoes" id="aguardando-acoes">
                <?php if (!empty($checkoutUrl)): ?>
                <a href="<?= Helper::e($checkoutUrl) ?>" class="btn btn-primary" id="btn-pagar" target="_blank">
                    Abrir página de pagamento
                </a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/minha-conta" class="btn btn-light">Ver meus pedidos</a>
            </div>

            <p class="aguardando-info" id="aguardando-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="15" height="15"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Esta página verifica o status automaticamente a cada 3 segundos.
            </p>

        </div>
    </div>
</section>

<script>
(function () {
    var statusUrl  = <?= json_encode(APP_URL . '/checkout/status/' . $pedido['numero']) ?>;
    var obrigadoUrl = <?= json_encode(APP_URL . '/checkout/obrigado/' . $pedido['numero']) ?>;
    var interval   = null;
    var tentativas = 0;
    var maxTentativas = 120; // 6 minutos

    function poll() {
        tentativas++;
        if (tentativas > maxTentativas) {
            clearInterval(interval);
            setExpired();
            return;
        }

        fetch(statusUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.pago) {
                    clearInterval(interval);
                    setPago();
                    setTimeout(function () { window.location.href = obrigadoUrl; }, 1800);
                } else if (data.falhou) {
                    clearInterval(interval);
                    setFalhou(data.status_label);
                }
            })
            .catch(function () { /* ignore network blips */ });
    }

    function setPago() {
        document.getElementById('aguardando-spinner').innerHTML =
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="56" height="56" class="aguardando-ok-icon"><polyline points="20 6 9 17 4 12"/></svg>';
        document.getElementById('aguardando-titulo').textContent = 'Pagamento confirmado!';
        document.getElementById('aguardando-subtitulo').textContent = 'Seu pedido foi aprovado. Redirecionando…';
        document.getElementById('aguardando-status').innerHTML =
            '<span class="aguardando-status__badge aguardando-status__badge--pago">Pago</span>';
        document.getElementById('aguardando-info').style.display = 'none';
        document.getElementById('aguardando-card').classList.add('aguardando-card--pago');
    }

    function setFalhou(label) {
        document.getElementById('aguardando-spinner').innerHTML =
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="56" height="56" class="aguardando-erro-icon"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
        document.getElementById('aguardando-titulo').textContent = 'Pagamento não concluído';
        document.getElementById('aguardando-subtitulo').textContent = label || 'Ocorreu um problema com o pagamento.';
        document.getElementById('aguardando-status').innerHTML =
            '<span class="aguardando-status__badge aguardando-status__badge--erro">' + (label || 'Falhou') + '</span>';
        document.getElementById('aguardando-info').style.display = 'none';
        document.getElementById('aguardando-card').classList.add('aguardando-card--erro');
    }

    function setExpired() {
        setFalhou('Tempo de espera esgotado. Verifique seus pedidos.');
    }

    interval = setInterval(poll, 3000);
    poll(); // immediate first check
})();
</script>
