<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Como Comprar</span>
        </nav>
        <h1>Como Comprar</h1>
        <p class="page-hero-desc">Compra 100% online — do produto ao pagamento, tudo em poucos minutos.</p>
    </div>
</section>

<!-- PROCESSO -->
<section class="section-passos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Passo a passo</span>
            <h2>O caminho do seu pedido</h2>
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">Todo o processo é feito aqui no site, sem precisar do WhatsApp para comprar.</p>
        </div>

        <ol style="list-style:none;padding:0;margin:0 auto;max-width:720px;display:flex;flex-direction:column;gap:1.5rem">

            <li style="display:flex;gap:1.25rem;align-items:flex-start">
                <div class="passo-numero" style="flex-shrink:0" aria-hidden="true">1</div>
                <div>
                    <h3 style="margin:0 0 0.25rem">Escolha seus produtos</h3>
                    <p style="margin:0;color:var(--texto-medio)">Navegue pelo catálogo, conheça cada produto e seus ingredientes. Adicione ao carrinho os itens que desejar.</p>
                </div>
            </li>

            <li style="display:flex;gap:1.25rem;align-items:flex-start">
                <div class="passo-numero" style="flex-shrink:0" aria-hidden="true">2</div>
                <div>
                    <h3 style="margin:0 0 0.25rem">Revise o carrinho</h3>
                    <p style="margin:0;color:var(--texto-medio)">Confira quantidades e valores. Ajuste o que quiser antes de continuar — sem compromisso.</p>
                </div>
            </li>

            <li style="display:flex;gap:1.25rem;align-items:flex-start">
                <div class="passo-numero" style="flex-shrink:0" aria-hidden="true">3</div>
                <div>
                    <h3 style="margin:0 0 0.25rem">Informe seu endereço</h3>
                    <p style="margin:0;color:var(--texto-medio)">Preencha o endereço de entrega completo. Se você tiver uma conta, os dados ficam salvos para compras futuras.</p>
                </div>
            </li>

            <li style="display:flex;gap:1.25rem;align-items:flex-start">
                <div class="passo-numero" style="flex-shrink:0" aria-hidden="true">4</div>
                <div>
                    <h3 style="margin:0 0 0.25rem">Calcule o frete</h3>
                    <p style="margin:0;color:var(--texto-medio)">Informe o CEP e veja as opções disponíveis — Correios, Jadlog, Motoboy, Uber Flash ou Retirada Pessoal — com prazo e valor calculados em tempo real.</p>
                </div>
            </li>

            <li style="display:flex;gap:1.25rem;align-items:flex-start">
                <div class="passo-numero" style="flex-shrink:0" aria-hidden="true">5</div>
                <div>
                    <h3 style="margin:0 0 0.25rem">Confirme o pedido</h3>
                    <p style="margin:0;color:var(--texto-medio)">Revise o resumo completo — produtos, frete selecionado e total — e confirme para avançar ao pagamento.</p>
                </div>
            </li>

            <li style="display:flex;gap:1.25rem;align-items:flex-start">
                <div class="passo-numero" style="flex-shrink:0" aria-hidden="true">6</div>
                <div>
                    <h3 style="margin:0 0 0.25rem">Pague com segurança</h3>
                    <p style="margin:0;color:var(--texto-medio)">Você é redirecionado ao ambiente seguro da <strong>InfinitePay</strong> (PCI DSS). Escolha PIX — confirmação imediata — ou cartão de crédito.</p>
                </div>
            </li>

            <li style="display:flex;gap:1.25rem;align-items:flex-start">
                <div class="passo-numero" style="flex-shrink:0" aria-hidden="true">7</div>
                <div>
                    <h3 style="margin:0 0 0.25rem">Pedido confirmado automaticamente</h3>
                    <p style="margin:0;color:var(--texto-medio)">A aprovação do pagamento dispara a confirmação automática. Você recebe um e-mail de confirmação e pode acompanhar tudo em <a href="<?= APP_URL ?>/minha-conta">Minha Conta</a>.</p>
                </div>
            </li>

        </ol>

        <div class="text-center mt-lg">
            <a href="<?= APP_URL ?>/produtos" class="btn btn-primary btn-lg">Ver todos os produtos</a>
        </div>
    </div>
</section>

<!-- FORMAS DE PAGAMENTO -->
<section class="section-pagamentos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Pagamento</span>
            <h2>Formas de pagamento aceitas</h2>
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">O pagamento online é processado pela InfinitePay em ambiente seguro. Na retirada pessoal, também aceitamos dinheiro.</p>
        </div>

        <div class="pagamentos-grid">
            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">⚡</div>
                <h3>PIX</h3>
                <p>Pagamento instantâneo processado pela <strong>InfinitePay</strong> no checkout online. Confirmação automática em minutos, disponível 24h por dia.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">💳</div>
                <h3>Cartão de Crédito</h3>
                <p>Processado pela <strong>InfinitePay</strong> em ambiente seguro (PCI DSS) no checkout online. Disponível também via maquininha na retirada pessoal.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">💵</div>
                <h3>Dinheiro</h3>
                <p>Aceito exclusivamente na retirada pessoal. Troco disponível — combine antecipadamente pelo WhatsApp.</p>
            </div>
        </div>
        <p class="section-crosslink">Veja prazos de confirmação, segurança e política de reembolso em <a href="<?= APP_URL ?>/pagamento">Formas de Pagamento →</a></p>
    </div>
</section>

<!-- FORMAS DE ENTREGA -->
<section class="section-entrega">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Envio e entrega</span>
            <h2>Como seu pedido chega até você</h2>
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">Calcule o frete direto no checkout pelo CEP de destino. Atendemos todo o Brasil e oferecemos opções locais em Porto Alegre.</p>
        </div>

        <div class="entrega-grid">
            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">📦</div>
                <div class="entrega-info">
                    <h3>Correios e Jadlog</h3>
                    <p>Atendemos todo o Brasil via Correios (PAC ou SEDEX) e Jadlog quando disponível para o CEP de destino. Prazo e valor exibidos no checkout em tempo real.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🛵</div>
                <div class="entrega-info">
                    <h3>Motoboy / Uber Flash</h3>
                    <p>Entrega ágil para endereços em Porto Alegre e arredores. Disponível como opção no checkout — o custo é por conta do cliente.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🤝</div>
                <div class="entrega-info">
                    <h3>Retirada Pessoal</h3>
                    <p>Selecione "Retirada" no checkout (sem custo de frete) e combine o horário pelo WhatsApp após finalizar seu pedido.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">📍</div>
                <div class="entrega-info">
                    <h3>Rastreamento</h3>
                    <p>Assim que seu pedido for despachado, você recebe o código de rastreamento por e-mail. Acompanhe também em <a href="<?= APP_URL ?>/minha-conta">Minha Conta</a>.</p>
                </div>
            </div>
        </div>
        <p class="section-crosslink">Veja transportadoras, prazos e política de entrega em <a href="<?= APP_URL ?>/envio">Envio e Entrega →</a></p>
    </div>
</section>

<!-- CTA -->
<section class="section-cta">
    <div class="container">
        <div class="cta-box">
            <div class="cta-leaf" aria-hidden="true">🌿</div>
            <h2>Alguma dúvida antes de comprar?</h2>
            <p>O WhatsApp é nosso canal de suporte — estamos aqui para ajudar com dúvidas sobre produtos, prazos ou qualquer situação após a compra.</p>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center">
                <a href="<?= APP_URL ?>/produtos" class="btn btn-primary btn-lg">Comprar agora</a>
                <a href="<?= Helper::whatsapp() ?>" class="btn btn-light btn-lg" target="_blank" rel="noopener">Falar pelo WhatsApp</a>
            </div>
        </div>
    </div>
</section>
