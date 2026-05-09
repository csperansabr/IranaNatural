<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/carrinho">Carrinho</a>
            <span>›</span>
            <span>Forma de Pagamento</span>
        </nav>
        <h1>Forma de Pagamento</h1>
    </div>
</section>

<section class="section-checkout">
    <div class="container">

        <div class="checkout-steps">
            <div class="checkout-step done"><span class="checkout-step__num">✓</span><span class="checkout-step__label">Resumo</span></div>
            <div class="checkout-step done"><span class="checkout-step__num">✓</span><span class="checkout-step__label">Endereço</span></div>
            <div class="checkout-step active"><span class="checkout-step__num">3</span><span class="checkout-step__label">Pagamento</span></div>
            <div class="checkout-step"><span class="checkout-step__num">4</span><span class="checkout-step__label">Confirmação</span></div>
        </div>

        <?php if ($erro): ?>
        <div class="alert alert--erro"><?= Helper::e($erro) ?></div>
        <?php endif; ?>

        <div class="checkout-layout">
            <main class="checkout-main">
                <h2 class="checkout-section-title">Como deseja pagar?</h2>

                <form method="POST" action="<?= APP_URL ?>/checkout/pagamento" id="form-pagamento" class="checkout-form">
                    <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">

                    <div class="pagamento-opcoes">

                        <?php if ($pixAtivo): ?>
                        <label class="pagamento-opcao<?= ($selecao['forma'] ?? '') === 'pix' ? ' pagamento-opcao--selected' : '' ?>" id="opcao-pix">
                            <input type="radio" name="forma_pagamento" value="pix"
                                   <?= ($selecao['forma'] ?? '') === 'pix' ? 'checked' : '' ?>
                                   required class="pagamento-opcao__radio">
                            <div class="pagamento-opcao__card">
                                <div class="pagamento-opcao__icon pagamento-opcao__icon--pix">
                                    <svg viewBox="0 0 512 512" fill="currentColor" width="28" height="28"><path d="M112.57 391.19c20.93 0 40.59-8.15 55.41-22.96l83.25-83.26c5.09-5.09 13.88-5.09 18.97 0l83.57 83.57c14.82 14.81 34.48 22.96 55.41 22.96h16.46l-105.3 105.3c-30.7 30.7-80.54 30.7-111.24 0L102.8 391.19h9.77zm297.61-271.55c-20.93 0-40.59 8.15-55.41 22.96l-83.57 83.57c-5.24 5.24-13.73 5.24-18.97 0l-83.25-83.26c-14.82-14.81-34.48-22.96-55.41-22.96H103.1L208.7 14.66c30.7-30.7 80.54-30.7 111.24 0l105.3 105.3-15.06-.32zm52.6 74.95-52.55-52.55h-47.67c-14.41 0-28.56 5.79-38.85 15.94l-83.57 83.57c-9.69 9.69-22.42 14.53-35.14 14.53s-25.45-4.84-35.14-14.53l-83.25-83.26c-10.29-10.29-24.43-15.94-38.85-15.94H48.95L.62 194.4c-30.7 30.7-30.7 80.54 0 111.24l48.33 48.33h51.62c14.41 0 28.56-5.79 38.85-15.94l83.25-83.26c18.97-18.97 52.31-18.97 71.28 0l83.57 83.57c10.29 10.29 24.44 15.94 38.85 15.94h44.06l52.55-52.55c30.7-30.7 30.7-80.54 0-111.24v.1z"/></svg>
                                </div>
                                <div class="pagamento-opcao__info">
                                    <strong>PIX</strong>
                                    <?php if ($pixPct > 0): ?>
                                    <span class="pagamento-pix-destaque">
                                        <span class="pagamento-pix-badge"><?= number_format($pixPct, 0) ?>% OFF</span>
                                        Pague <strong id="pix-total"><?= Helper::money($pixTotal) ?></strong> à vista
                                    </span>
                                    <?php else: ?>
                                    <span>Pagamento instantâneo — aprovação imediata.</span>
                                    <?php endif; ?>
                                </div>
                                <div class="pagamento-opcao__check">✓</div>
                            </div>
                        </label>
                        <?php endif; ?>

                        <?php if ($cartaoAtivo): ?>
                        <label class="pagamento-opcao<?= ($selecao['forma'] ?? '') === 'cartao_credito' ? ' pagamento-opcao--selected' : '' ?>" id="opcao-cartao">
                            <input type="radio" name="forma_pagamento" value="cartao_credito"
                                   <?= ($selecao['forma'] ?? '') === 'cartao_credito' ? 'checked' : '' ?>
                                   class="pagamento-opcao__radio">
                            <div class="pagamento-opcao__card">
                                <div class="pagamento-opcao__icon pagamento-opcao__icon--cartao">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="28" height="28"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                </div>
                                <div class="pagamento-opcao__info">
                                    <strong>Cartão de Crédito</strong>
                                    <span>Parcelado em até <?= (int)$maxParcelas ?>× sem juros.</span>
                                </div>
                                <div class="pagamento-opcao__check">✓</div>
                            </div>

                            <?php if ($maxParcelas > 1): ?>
                            <div class="pagamento-parcelas" id="bloco-parcelas">
                                <label class="pagamento-parcelas__label">Número de parcelas:</label>
                                <div class="pagamento-parcelas__grid">
                                    <?php for ($p = 1; $p <= $maxParcelas; $p++): ?>
                                    <label class="parcela-opcao">
                                        <input type="radio" name="parcelas" value="<?= $p ?>"
                                               <?= ($selecao['parcelas'] ?? 1) == $p ? 'checked' : ($p === 1 ? 'checked' : '') ?>
                                               class="parcela-opcao__radio">
                                        <span class="parcela-opcao__card">
                                            <strong><?= $p ?>×</strong>
                                            <span class="parcela-opcao__valor" data-parcela="<?= $p ?>">
                                                <?= Helper::money(round($total / $p, 2)) ?>
                                            </span>
                                            <?php if ($p === 1): ?>
                                            <small>à vista</small>
                                            <?php else: ?>
                                            <small>sem juros</small>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="parcelas" value="1">
                            <?php endif; ?>
                        </label>
                        <?php endif; ?>

                    </div>

                    <div class="checkout-nav">
                        <a href="<?= APP_URL ?>/checkout/endereco" class="btn btn-light">← Voltar</a>
                        <button type="submit" class="btn btn-primary">Continuar →</button>
                    </div>
                </form>
            </main>

            <aside class="checkout-sidebar">
                <div class="checkout-resumo-card">
                    <h3>Entrega em</h3>
                    <p class="checkout-endereco-resumo">
                        <?= Helper::e($endereco['logradouro'] ?? '') ?>, <?= Helper::e($endereco['numero'] ?? '') ?>
                        <?php if (!empty($endereco['complemento'])): ?> — <?= Helper::e($endereco['complemento']) ?><?php endif; ?><br>
                        <?= Helper::e($endereco['bairro'] ?? '') ?><br>
                        <?= Helper::e($endereco['cidade'] ?? '') ?>/<?= Helper::e($endereco['estado'] ?? '') ?><br>
                        CEP <?= Helper::e($endereco['cep'] ?? '') ?>
                    </p>
                    <a href="<?= APP_URL ?>/checkout/endereco" class="link-subtle">Alterar endereço</a>
                    <hr class="checkout-hr">
                    <div class="checkout-resumo-linha">
                        <span>Subtotal</span>
                        <span id="resumo-subtotal"><?= Helper::money($total) ?></span>
                    </div>
                    <?php if ($pixAtivo && $pixPct > 0): ?>
                    <div class="checkout-resumo-linha checkout-resumo-desconto" id="resumo-desconto-row" style="display:none">
                        <span>Desconto PIX (<?= number_format($pixPct, 0) ?>%)</span>
                        <span id="resumo-desconto-valor" class="resumo-desconto-valor">-<?= Helper::money($total - $pixTotal) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="checkout-resumo-linha checkout-resumo-linha--total">
                        <span>Total</span>
                        <span id="resumo-total"><?= Helper::money($total) ?></span>
                    </div>
                </div>
            </aside>
        </div>

    </div>
</section>

<script>
(function () {
    var totalBase  = <?= json_encode((float)$total) ?>;
    var pixTotal   = <?= json_encode((float)$pixTotal) ?>;
    var pixPct     = <?= json_encode((float)$pixPct) ?>;
    var maxParcelas = <?= json_encode((int)$maxParcelas) ?>;

    function formatMoney(v) {
        return 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function updateResumo(forma) {
        var totalEl    = document.getElementById('resumo-total');
        var descontoRow = document.getElementById('resumo-desconto-row');
        var pixTotalEl = document.getElementById('pix-total');

        if (forma === 'pix' && pixPct > 0) {
            if (totalEl) totalEl.textContent = formatMoney(pixTotal);
            if (descontoRow) descontoRow.style.display = '';
            if (pixTotalEl) pixTotalEl.textContent = formatMoney(pixTotal);
        } else {
            if (totalEl) totalEl.textContent = formatMoney(totalBase);
            if (descontoRow) descontoRow.style.display = 'none';
        }
    }

    function showParcelas(show) {
        var bloco = document.getElementById('bloco-parcelas');
        if (bloco) bloco.style.display = show ? '' : 'none';
    }

    var radios = document.querySelectorAll('input[name="forma_pagamento"]');
    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            // Update selected style
            document.querySelectorAll('.pagamento-opcao').forEach(function (el) {
                el.classList.remove('pagamento-opcao--selected');
            });
            var parent = radio.closest('.pagamento-opcao');
            if (parent) parent.classList.add('pagamento-opcao--selected');

            updateResumo(radio.value);
            showParcelas(radio.value === 'cartao_credito');
        });
    });

    // Init state
    var checked = document.querySelector('input[name="forma_pagamento"]:checked');
    if (checked) {
        updateResumo(checked.value);
        showParcelas(checked.value === 'cartao_credito');
    } else {
        showParcelas(false);
    }
})();
</script>
