<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/carrinho">Carrinho</a>
            <span>›</span>
            <span>Confirmação</span>
        </nav>
        <h1>Confirmar Pedido</h1>
    </div>
</section>

<section class="section-checkout">
    <div class="container">

        <div class="checkout-steps">
            <div class="checkout-step done"><span class="checkout-step__num">✓</span><span class="checkout-step__label">Resumo</span></div>
            <div class="checkout-step done"><span class="checkout-step__num">✓</span><span class="checkout-step__label">Endereço</span></div>
            <div class="checkout-step active"><span class="checkout-step__num">3</span><span class="checkout-step__label">Confirmação</span></div>
        </div>

        <?php if ($erro ?? null): ?>
        <div class="alert alert--erro" style="margin-bottom:1.5rem">
            <?= Helper::e($erro) ?>
        </div>
        <?php endif; ?>

        <div class="checkout-layout">
            <main class="checkout-main">
                <h2 class="checkout-section-title">Revise seu pedido</h2>

                <!-- Produtos -->
                <div class="confirmar-bloco">
                    <h3 class="confirmar-bloco__titulo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        Produtos (<?= count($itens) ?>)
                    </h3>
                    <?php foreach ($itens as $item): ?>
                    <div class="checkout-produto-item">
                        <?php if ($item['imagem']): ?>
                        <img src="<?= Helper::upload($item['imagem']) ?>" alt="<?= Helper::e($item['nome']) ?>" class="checkout-produto-item__img" loading="lazy">
                        <?php else: ?>
                        <img src="<?= APP_URL ?>/assets/images/placeholder.svg" alt="<?= Helper::e($item['nome']) ?>" class="checkout-produto-item__img">
                        <?php endif; ?>
                        <div class="checkout-produto-item__info">
                            <span class="checkout-produto-item__nome"><?= Helper::e($item['nome']) ?></span>
                            <span class="checkout-produto-item__qty">Qtd: <?= (int)$item['quantidade'] ?> × <?= Helper::money((float)$item['preco_unitario']) ?></span>
                        </div>
                        <span class="checkout-produto-item__subtotal">
                            <?= Helper::money(round((float)$item['preco_unitario'] * (int)$item['quantidade'], 2)) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Endereço -->
                <div class="confirmar-bloco">
                    <h3 class="confirmar-bloco__titulo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Endereço de entrega
                        <a href="<?= APP_URL ?>/checkout/endereco" class="confirmar-editar">Alterar</a>
                    </h3>
                    <p class="confirmar-bloco__texto">
                        <?= Helper::e($endereco['logradouro']) ?>, <?= Helper::e($endereco['numero']) ?>
                        <?php if (!empty($endereco['complemento'])): ?> — <?= Helper::e($endereco['complemento']) ?><?php endif; ?><br>
                        <?= Helper::e($endereco['bairro']) ?> — <?= Helper::e($endereco['cidade']) ?>/<?= Helper::e($endereco['estado']) ?><br>
                        CEP <?= Helper::e($endereco['cep']) ?>
                    </p>
                </div>

                <!-- Pagamento -->
                <div class="confirmar-bloco">
                    <h3 class="confirmar-bloco__titulo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Pagamento
                    </h3>
                    <p class="confirmar-bloco__texto">
                        Você será redirecionado para o ambiente seguro da <strong>InfinitePay</strong> para escolher a forma de pagamento (PIX, cartão de crédito ou débito).
                    </p>
                </div>

                <!-- Frete -->
                <div class="confirmar-bloco" id="bloco-frete">
                    <h3 class="confirmar-bloco__titulo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Entrega
                    </h3>
                    <div id="frete-loading" class="frete-loading">
                        <span class="frete-spinner"></span> Calculando opções de frete…
                    </div>
                    <div id="frete-opcoes" class="frete-opcoes" style="display:none"></div>
                    <div id="frete-erro" class="frete-erro" style="display:none">
                        Não foi possível calcular o frete automaticamente. Por favor, selecione uma opção abaixo.
                    </div>
                </div>

                <!-- Observações -->
                <form method="POST" action="<?= APP_URL ?>/checkout/finalizar" id="form-finalizar">
                    <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">
                    <input type="hidden" name="frete_tipo"            id="inp-frete-tipo">
                    <input type="hidden" name="frete_transportadora"  id="inp-frete-transportadora">
                    <input type="hidden" name="frete_valor"           id="inp-frete-valor" value="0">
                    <input type="hidden" name="frete_prazo"           id="inp-frete-prazo">
                    <input type="hidden" name="frete_codigo"          id="inp-frete-codigo" value="0">
                    <input type="hidden" name="frete_resp_cliente"    id="inp-frete-resp" value="0">

                    <div class="confirmar-bloco">
                        <h3 class="confirmar-bloco__titulo">Observações (opcional)</h3>
                        <textarea name="observacoes" class="form-input form-textarea"
                                  placeholder="Alguma instrução especial para o seu pedido?"
                                  rows="3"></textarea>
                    </div>

                    <div class="checkout-nav">
                        <a href="<?= APP_URL ?>/checkout/endereco" class="btn btn-light">← Voltar</a>
                        <button type="submit" class="btn btn-primary btn-lg" id="btn-finalizar">
                            Ir para pagamento →
                        </button>
                    </div>
                </form>
            </main>

            <aside class="checkout-sidebar">
                <div class="checkout-resumo-card">
                    <h3>Total do pedido</h3>
                    <div class="checkout-resumo-linha">
                        <span>Subtotal</span>
                        <span><?= Helper::money($total) ?></span>
                    </div>
                    <div class="checkout-resumo-linha" id="resumo-frete-linha">
                        <span>Frete</span>
                        <span id="resumo-frete-valor">—</span>
                    </div>
                    <div class="checkout-resumo-linha checkout-resumo-linha--total">
                        <span>Total</span>
                        <span id="resumo-total"><?= Helper::money($total) ?></span>
                    </div>
                    <div class="checkout-resumo__seguranca">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Pedido seguro — você será redirecionado para o ambiente InfinitePay</span>
                    </div>
                </div>
            </aside>
        </div>

    </div>
</section>

<script>
(function () {
    var subtotal    = <?= (float)$total ?>;
    var cep         = '<?= preg_replace('/\D/', '', $endereco['cep'] ?? '') ?>';
    var freteValor  = 0;
    var freteSelecionado = false;

    function fmt(v) {
        return 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function atualizarResumo(valor) {
        freteValor = valor;
        var elFrete = document.getElementById('resumo-frete-valor');
        var elTotal = document.getElementById('resumo-total');
        if (elFrete) elFrete.textContent = valor === 0 ? 'Grátis' : fmt(valor);
        if (elTotal) elTotal.textContent = fmt(subtotal + valor);
    }

    function selecionarOpcao(radio) {
        var opcoes = document.querySelectorAll('.frete-opcao');
        opcoes.forEach(function(el) { el.classList.remove('frete-opcao--selecionada'); });
        var card = radio.closest('.frete-opcao');
        if (card) card.classList.add('frete-opcao--selecionada');

        document.getElementById('inp-frete-tipo').value           = radio.dataset.tipo;
        document.getElementById('inp-frete-transportadora').value = radio.dataset.transportadora;
        document.getElementById('inp-frete-valor').value          = radio.dataset.valor;
        document.getElementById('inp-frete-prazo').value          = radio.dataset.prazo;
        document.getElementById('inp-frete-codigo').value         = radio.dataset.codigo;
        document.getElementById('inp-frete-resp').value           = radio.dataset.resp;
        atualizarResumo(parseFloat(radio.dataset.valor));
        freteSelecionado = true;
    }

    function renderOpcoes(opcoes) {
        var el = document.getElementById('frete-opcoes');
        if (!opcoes || !opcoes.length) {
            document.getElementById('frete-loading').style.display = 'none';
            document.getElementById('frete-erro').style.display    = 'block';
            el.style.display = 'block';
            // Still render local options from server-side constant if JS fails,
            // but here we just show the error — local options come from the API response too
            return;
        }
        var html = '';
        opcoes.forEach(function (o, i) {
            var label = o.nome;
            var preco = parseFloat(o.valor);
            var precoLabel = preco <= 0 ? '<strong>Grátis</strong>' : '<strong>' + fmt(preco) + '</strong>';
            var prazoLabel = o.prazo ? '<small>' + o.prazo + '</small>' : '';
            var aviso = o.resp_cliente ? '<span class="frete-resp-aviso">Você contrata diretamente</span>' : '';
            html += '<label class="frete-opcao">';
            html += '<input type="radio" name="frete_radio" value="' + i + '"'
                + ' data-tipo="' + o.id + '"'
                + ' data-transportadora="' + escHtml(o.transportadora) + '"'
                + ' data-valor="' + preco + '"'
                + ' data-prazo="' + escHtml(o.prazo) + '"'
                + ' data-codigo="' + (o.codigo || 0) + '"'
                + ' data-resp="' + (o.resp_cliente ? 1 : 0) + '">';
            html += '<span class="frete-opcao__info">'
                + '<span class="frete-opcao__nome">' + escHtml(label) + '</span>'
                + prazoLabel + aviso + '</span>';
            html += '<span class="frete-opcao__preco">' + precoLabel + '</span>';
            html += '</label>';
        });
        el.innerHTML = html;
        el.style.display = 'block';
        document.getElementById('frete-loading').style.display = 'none';

        el.querySelectorAll('input[type=radio]').forEach(function (r) {
            r.addEventListener('change', function () { selecionarOpcao(this); });
        });

        // Auto-select first option
        var first = el.querySelector('input[type=radio]');
        if (first) { first.checked = true; selecionarOpcao(first); }
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Local options always available as fallback
    var opcoesLocais = <?= json_encode(array_map(function($l) {
        return [
            'id'           => $l['id'],
            'nome'         => $l['nome'],
            'transportadora'=> $l['transportadora'],
            'valor'        => (float)$l['valor'],
            'prazo'        => $l['prazo'],
            'codigo'       => 0,
            'tipo'         => 'local',
            'resp_cliente' => (bool)$l['resp_cliente'],
        ];
    }, FRETE_LOCAIS), JSON_UNESCAPED_UNICODE) ?>;

    function mostrarSoLocais() {
        document.getElementById('frete-loading').style.display = 'none';
        document.getElementById('frete-erro').style.display    = 'block';
        renderOpcoes(opcoesLocais);
    }

    // Load frete options on page load
    if (cep.length === 8) {
        fetch('<?= APP_URL ?>/api/frete/calcular', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cep: cep })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok && data.opcoes && data.opcoes.length) renderOpcoes(data.opcoes);
            else mostrarSoLocais();
        })
        .catch(function () { mostrarSoLocais(); });
    } else {
        mostrarSoLocais();
    }

    // Form submit validation
    document.getElementById('form-finalizar')?.addEventListener('submit', function (e) {
        var tipo = document.getElementById('inp-frete-tipo').value;
        if (!tipo) {
            e.preventDefault();
            alert('Por favor, selecione uma opção de entrega antes de continuar.');
            document.getElementById('bloco-frete').scrollIntoView({ behavior: 'smooth' });
            return;
        }
        var btn = document.getElementById('btn-finalizar');
        if (btn) { btn.disabled = true; btn.textContent = 'Redirecionando…'; }
    });
}());
</script>
