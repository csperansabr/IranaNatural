<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Como Comprar</span>
        </nav>
        <h1>Como Comprar</h1>
        <p class="page-hero-desc">Simples, rápido e com todo o cuidado que você merece.</p>
    </div>
</section>

<!-- PROCESSO -->
<section class="section-passos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Passo a passo</span>
            <h2>Três etapas para receber seu pedido</h2>
        </div>

        <div class="passos-grid">
            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">1</div>
                <h3>Escolha seus produtos</h3>
                <p>Navegue pelo nosso catálogo, conheça cada produto e seus ingredientes. Quando encontrar o que procura, anote o nome ou use o botão de cada item.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">2</div>
                <h3>Fale pelo WhatsApp</h3>
                <p>Clique no botão "Pedir pelo WhatsApp" presente em cada produto, ou acione o ícone verde flutuante. Nossa equipe responderá o mais breve possível.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">3</div>
                <h3>Confirme e receba</h3>
                <p>Combinamos a forma de pagamento e a entrega diretamente no chat. Após a confirmação, seu pedido entra em preparação com todo o nosso cuidado artesanal.</p>
            </div>
        </div>

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
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">Os dados e informações para pagamento são sempre enviados pelo WhatsApp, de forma segura e personalizada.</p>
        </div>

        <div class="pagamentos-grid">
            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">⚡</div>
                <h3>PIX</h3>
                <p>Transferência instantânea, disponível a qualquer hora. Confirmação imediata após o envio do comprovante.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">🏦</div>
                <h3>Transferência Bancária</h3>
                <p>Realize seu pagamento via TED ou DOC. Os dados bancários são enviados pelo WhatsApp no momento do pedido.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">💳</div>
                <h3>Cartão de Crédito ou Débito</h3>
                <p>Disponível via link de pagamento gerado no momento da compra, ou presencialmente na retirada com maquininha.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">💵</div>
                <h3>Dinheiro</h3>
                <p>Aceito exclusivamente na retirada pessoal. Troco disponível — combine antecipadamente pelo WhatsApp.</p>
            </div>
        </div>
        <p class="section-crosslink">Confira prazos, parcelamento, InfinitePay e política de reembolso em <a href="<?= APP_URL ?>/pagamento">Formas de Pagamento →</a></p>
    </div>
</section>

<!-- FORMAS DE ENTREGA -->
<section class="section-entrega">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Envio e entrega</span>
            <h2>Como seu pedido chega até você</h2>
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">Atendemos clientes em todo o Brasil. Prazos e valores de frete são combinados pelo WhatsApp conforme seu endereço.</p>
        </div>

        <div class="entrega-grid">
            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">📦</div>
                <div class="entrega-info">
                    <h3>Correios e Jadlog</h3>
                    <p>Atendemos todo o Brasil via Correios (PAC ou SEDEX) e Jadlog quando disponível para o CEP de destino. Prazo e valor informados pelo WhatsApp antes de confirmar.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🛵</div>
                <div class="entrega-info">
                    <h3>Motoboy / Uber Flash</h3>
                    <p>Entrega ágil para endereços em Porto Alegre e arredores. O custo do serviço é por conta do cliente e combinado diretamente no chat.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🚗</div>
                <div class="entrega-info">
                    <h3>Entrega Local</h3>
                    <p>Realizamos entregas pessoalmente em regiões de Porto Alegre e Grande Porto Alegre em horários combinados pelo WhatsApp.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🤝</div>
                <div class="entrega-info">
                    <h3>Retirada Pessoal</h3>
                    <p>Prefere buscar seu pedido? Combine o local e horário pelo WhatsApp. Pagamento em dinheiro, cartão ou PIX na retirada.</p>
                </div>
            </div>
        </div>
        <p class="section-crosslink">Veja transportadoras, prazos, rastreamento e política de entrega em <a href="<?= APP_URL ?>/envio">Envio e Entrega →</a></p>
    </div>
</section>

<!-- CTA -->
<section class="section-cta">
    <div class="container">
        <div class="cta-box">
            <div class="cta-leaf" aria-hidden="true">🌿</div>
            <h2>Pronto para começar?</h2>
            <p>Nossa equipe está pronta para te ajudar a escolher os produtos certos e responder qualquer dúvida antes de você fechar seu pedido.</p>
            <a href="<?= Helper::whatsapp() ?>" class="btn btn-light btn-lg" target="_blank" rel="noopener">
                Falar pelo WhatsApp
            </a>
        </div>
    </div>
</section>
