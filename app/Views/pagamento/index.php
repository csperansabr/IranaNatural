<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Formas de Pagamento</span>
        </nav>
        <h1>Formas de Pagamento</h1>
        <p class="page-hero-desc">Aceitamos PIX e Cartão de Crédito no checkout online com ambiente seguro InfinitePay. Na retirada pessoal, também aceitamos dinheiro.</p>
    </div>
</section>

<!-- MÉTODOS ACEITOS -->
<section class="section-pagamentos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Métodos aceitos</span>
            <h2>Pague do jeito que preferir</h2>
        </div>

        <div class="pagamentos-grid">
            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">⚡</div>
                <h3>PIX</h3>
                <p>Pagamento instantâneo processado diretamente pela <strong>InfinitePay</strong> no checkout online. Confirmação automática em minutos, disponível 24h por dia.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">💳</div>
                <h3>Cartão de Crédito</h3>
                <p>Processado pela <strong>InfinitePay</strong> em ambiente seguro e certificado (PCI DSS). Disponível também via maquininha na retirada pessoal.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">💵</div>
                <h3>Dinheiro</h3>
                <p>Aceito exclusivamente na <strong>retirada pessoal</strong>. Avise pelo WhatsApp se precisar de troco para garantirmos o valor correto.</p>
            </div>
        </div>
    </div>
</section>

<!-- FLUXO DE PAGAMENTO -->
<section class="section-passos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Como funciona</span>
            <h2>Do pedido à confirmação</h2>
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">O pagamento online acontece no checkout da loja, em ambiente seguro da InfinitePay. Para compras presenciais com dinheiro, combine a retirada pelo WhatsApp.</p>
        </div>

        <div class="passos-grid">
            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">1</div>
                <h3>Adicione ao carrinho</h3>
                <p>Navegue pelo catálogo, escolha os produtos e adicione ao carrinho. Quando estiver pronto, acesse o checkout — é rápido e seguro.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">2</div>
                <h3>Pague pelo InfinitePay</h3>
                <p>Você será redirecionado para o ambiente seguro da InfinitePay, onde escolhe entre PIX ou Cartão de Crédito. Seus dados de pagamento nunca passam pelo nosso sistema.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">3</div>
                <h3>Pedido confirmado</h3>
                <p>A confirmação é automática — assim que o pagamento é aprovado, seu pedido entra imediatamente na fila de preparação, sem etapas manuais.</p>
            </div>
        </div>
    </div>
</section>

<!-- DETALHES POR MÉTODO -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>PIX</h2>
            <p>O pagamento por PIX é realizado diretamente no checkout da loja, pelo ambiente seguro da InfinitePay. Após finalizar o pedido, você recebe o QR Code ou a chave PIX para pagamento. A confirmação é automática, geralmente em poucos minutos, e o pedido entra em preparação sem qualquer ação adicional da nossa parte.</p>

            <h2>Cartão de Crédito</h2>
            <p>O pagamento por cartão de crédito também é processado integralmente pela InfinitePay no momento do checkout. Você insere os dados do cartão diretamente no ambiente seguro da InfinitePay — nenhuma informação de pagamento trafega pelo nosso sistema.</p>
            <p>Para pedidos com <strong>retirada pessoal</strong>, o pagamento via maquininha também está disponível no ato da retirada.</p>

            <div class="alerta-info">
                <strong>Seus dados de cartão ficam protegidos.</strong> A Iraná Natural não tem acesso ao número do cartão, CVV ou senha. O processamento é feito integralmente pela InfinitePay, plataforma certificada pelo padrão PCI DSS das bandeiras de cartão.
            </div>

            <h2>Dinheiro</h2>
            <p>Aceito exclusivamente na retirada pessoal, no local e horário combinados pelo WhatsApp. Se precisar de troco, informe o valor que vai trazer antes do encontro para que possamos nos preparar com antecedência.</p>

        </div>
    </div>
</section>

<!-- SEGURANÇA DAS TRANSAÇÕES -->
<section class="section-institucional">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Segurança das transações</h2>
            <p>A Iraná Natural não armazena dados de cartão de crédito, senhas ou qualquer informação de pagamento. O fluxo online foi estruturado para proteger você em todas as etapas:</p>
            <ul>
                <li><strong>PIX e Cartão de Crédito online</strong> — processados pela InfinitePay, certificada com o padrão PCI DSS. Os dados são inseridos diretamente no ambiente seguro da InfinitePay e nunca são compartilhados com a Iraná Natural;</li>
                <li><strong>Dinheiro na retirada</strong> — transação presencial e imediata, sem envolvimento de sistemas de pagamento;</li>
                <li><strong>WhatsApp</strong> — utilizado apenas para atendimento e suporte, não como canal de pagamento.</li>
            </ul>
            <p><strong>Atenção:</strong> a Iraná Natural jamais solicita dados de cartão por mensagem ou WhatsApp. O único contexto em que dados de cartão são inseridos é dentro do ambiente seguro da InfinitePay, durante o checkout. Se receber qualquer solicitação fora desse contexto, desconsidere e nos avise imediatamente.</p>

        </div>
    </div>
</section>

<!-- CONFIRMAÇÃO E RECUSAS -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Prazos de confirmação por forma de pagamento</h2>
            <p>O pedido entra em preparação somente após a confirmação automática do pagamento. Veja o prazo esperado para cada método:</p>
            <ul>
                <li><strong>PIX</strong> — confirmação automática em poucos minutos após o pagamento;</li>
                <li><strong>Cartão de Crédito</strong> — confirmação após aprovação pelo sistema da InfinitePay, geralmente em poucos minutos;</li>
                <li><strong>Dinheiro na retirada</strong> — confirmação imediata no ato da retirada.</li>
            </ul>

            <h2>Recusa do pagamento no cartão</h2>
            <p>Recusas no cartão de crédito são processadas pelo sistema da InfinitePay com base nas políticas do banco emissor do cartão. As situações mais comuns incluem:</p>
            <ul>
                <li>Limite de crédito insuficiente para o valor;</li>
                <li>Cartão bloqueado para transações online ou vencido;</li>
                <li>Transação bloqueada por suspeita de fraude pelo banco emissor;</li>
                <li>Dados inseridos incorretamente (número, validade ou CVV).</li>
            </ul>
            <p>Em caso de recusa, tente liberar a transação diretamente com seu banco — pelo aplicativo ou internet banking — antes de tentar novamente. Se o problema persistir, entre em contato pelo WhatsApp para que possamos ajudar. A Iraná Natural não tem acesso ao motivo específico da recusa — essa informação é fornecida apenas pelo seu banco emissor.</p>
            <p>Se precisar cancelar ou devolver o pedido, consulte nossa <a href="<?= APP_URL ?>/trocas">política de trocas e devoluções</a>.</p>

            <div class="alerta-info">
                <strong>Dica:</strong> bancos frequentemente bloqueiam compras online por segurança preventiva, especialmente em lojas não reconhecidas. Liberar a transação pelo aplicativo do banco costuma resolver rapidamente.
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-cta">
    <div class="container">
        <div class="cta-box">
            <div class="cta-leaf" aria-hidden="true">🌿</div>
            <h2>Pronto para comprar?</h2>
            <p>Escolha seus produtos, adicione ao carrinho e finalize pelo checkout online de forma rápida e segura.</p>
            <a href="<?= APP_URL ?>/produtos" class="btn btn-light btn-lg">
                Ver produtos
            </a>
        </div>
    </div>
</section>
