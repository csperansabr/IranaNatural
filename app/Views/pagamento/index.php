<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Formas de Pagamento</span>
        </nav>
        <h1>Formas de Pagamento</h1>
        <p class="page-hero-desc">Aceitamos PIX, transferência bancária, cartão de crédito ou débito e dinheiro na retirada. Tudo combinado pelo WhatsApp, de forma simples e segura.</p>
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
                <p>Transferência instantânea disponível 24h por dia. A chave PIX é enviada pelo WhatsApp e tem validade de <strong>24 horas</strong>.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">🏦</div>
                <h3>Transferência Bancária</h3>
                <p>TED ou DOC. Os dados bancários são enviados pelo WhatsApp. TEDs compensam no <strong>mesmo dia útil</strong>, dentro do horário bancário.</p>
            </div>

            <div class="pagamento-card">
                <div class="pagamento-icon" aria-hidden="true">💳</div>
                <h3>Cartão de Crédito ou Débito</h3>
                <p>Crédito em até <strong>12 parcelas com juros</strong> ou débito à vista, via link de pagamento (InfinitePay) enviado pelo WhatsApp ou maquininha na retirada pessoal.</p>
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
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">Não temos carrinho ou checkout automático. Todo o processo é feito pelo WhatsApp, de forma personalizada e sem pressa.</p>
        </div>

        <div class="passos-grid">
            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">1</div>
                <h3>Escolha seus produtos</h3>
                <p>Navegue pelo catálogo, escolha o que deseja e entre em contato pelo WhatsApp. Confirmamos disponibilidade, calculamos o frete e informamos o total antes de qualquer compromisso.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">2</div>
                <h3>Informe como vai pagar</h3>
                <p>Escolha sua forma de pagamento preferida. Enviamos a chave PIX, os dados bancários ou o link de pagamento seguro conforme a opção escolhida.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">3</div>
                <h3>Pedido confirmado</h3>
                <p>Após identificarmos o pagamento, confirmamos o pedido pelo WhatsApp e informamos o prazo de preparação e envio. Só então seu pedido entra na fila de produção.</p>
            </div>
        </div>
    </div>
</section>

<!-- DETALHES POR MÉTODO -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>PIX</h2>
            <p>A chave PIX aleatória é informada pelo WhatsApp no momento do fechamento do pedido. O pagamento deve ser realizado em até <strong>24 horas</strong> — após esse prazo, a reserva dos produtos é cancelada e os itens voltam ao estoque disponível.</p>
            <p>Após efetuar o PIX, envie o comprovante pelo WhatsApp. A confirmação do pedido é feita assim que o pagamento é identificado, geralmente em poucos minutos.</p>

            <h2>Transferência Bancária (TED / DOC)</h2>
            <p>Os dados bancários completos são enviados pelo WhatsApp após a confirmação dos itens e do valor do frete. Após realizar a transferência, encaminhe o comprovante pelo WhatsApp para agilizar a confirmação.</p>
            <ul>
                <li><strong>TED</strong> — compensação no mesmo dia útil, desde que enviada dentro do horário bancário;</li>
                <li><strong>DOC</strong> — compensação no próximo dia útil, independentemente do horário de envio.</li>
            </ul>

            <div class="alerta-info">
                <strong>Preferência para pedidos com urgência:</strong> opte por PIX ou TED. O DOC, por compensar apenas no dia útil seguinte, atrasa a entrada do pedido na fila de preparação.
            </div>

            <h2>Cartão de Crédito ou Débito</h2>
            <p>O pagamento por cartão é realizado de duas formas:</p>
            <ul>
                <li><strong>Link de pagamento</strong> — um link seguro gerado pela InfinitePay é enviado pelo WhatsApp. Você acessa o link, insere os dados do cartão e conclui o pagamento diretamente no ambiente seguro da InfinitePay, sem passar pelo nosso WhatsApp;</li>
                <li><strong>Maquininha presencial</strong> — disponível exclusivamente na retirada pessoal. Aceita crédito e débito das principais bandeiras.</li>
            </ul>
            <p>O parcelamento em <strong>até 12 vezes</strong> está sujeito a juros. As taxas aplicadas a cada número de parcelas são informadas antes da confirmação do pedido para que você escolha a opção mais conveniente.</p>

            <div class="alerta-info">
                <strong>Seus dados de cartão ficam protegidos.</strong> A Iraná Natural não tem acesso ao número do cartão, CVV ou senha. O processamento é feito integralmente pela InfinitePay, plataforma certificada pelas bandeiras de cartão. Nenhuma informação de pagamento trafega pelo WhatsApp.
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
            <p>A Iraná Natural não armazena dados de cartão de crédito, senhas bancárias ou informações de acesso a contas em nenhum sistema próprio. O fluxo de pagamento foi estruturado para proteger você em todas as etapas:</p>
            <ul>
                <li><strong>PIX e TED/DOC</strong> — transações diretas entre contas bancárias, protegidas pela infraestrutura do Banco Central e das instituições financeiras emissoras;</li>
                <li><strong>Cartão via link</strong> — processado pela InfinitePay, certificada com o padrão PCI DSS. Os dados do cartão são inseridos diretamente no ambiente seguro da InfinitePay e nunca são compartilhados com a Iraná Natural;</li>
                <li><strong>WhatsApp</strong> — todas as conversas são protegidas por criptografia de ponta a ponta (E2E). As mensagens são ilegíveis para terceiros, inclusive para a Meta.</li>
            </ul>
            <p><strong>Atenção:</strong> a Iraná Natural jamais solicita senha de banco, código CVV isolado ou dados completos de cartão por mensagem de texto. O único contexto em que dados de cartão são inseridos é dentro do link seguro gerado pela InfinitePay. Se receber qualquer solicitação fora desse contexto, desconsidere e nos avise imediatamente pelo WhatsApp.</p>

        </div>
    </div>
</section>

<!-- CONFIRMAÇÃO E RECUSAS -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Prazos de confirmação por forma de pagamento</h2>
            <p>O pedido entra em preparação somente após a confirmação do pagamento. Veja o prazo esperado para cada método:</p>
            <ul>
                <li><strong>PIX</strong> — confirmação em minutos após o envio do comprovante pelo WhatsApp;</li>
                <li><strong>TED</strong> — confirmação no mesmo dia útil após a compensação bancária;</li>
                <li><strong>DOC</strong> — confirmação no próximo dia útil;</li>
                <li><strong>Cartão via link InfinitePay</strong> — confirmação após aprovação pelo sistema da InfinitePay, geralmente em poucos minutos;</li>
                <li><strong>Maquininha / Dinheiro</strong> — confirmação imediata no ato da retirada.</li>
            </ul>

            <h2>Pagamento não realizado no prazo</h2>
            <p>Se o pagamento não for efetuado dentro do prazo combinado — 24 horas para PIX — a reserva dos produtos é cancelada automaticamente e os itens voltam ao estoque. Para retomar o pedido, basta entrar em contato pelo WhatsApp. Verificamos a disponibilidade e reabrimos o pedido se os produtos ainda estiverem disponíveis.</p>

            <h2>Recusa do pagamento no cartão</h2>
            <p>Recusas no cartão de crédito ou débito são processadas pelo sistema da InfinitePay com base nas políticas do banco emissor do cartão. As situações mais comuns incluem:</p>
            <ul>
                <li>Limite de crédito insuficiente para o valor ou número de parcelas;</li>
                <li>Cartão bloqueado para transações online ou vencido;</li>
                <li>Transação bloqueada por suspeita de fraude pelo banco emissor;</li>
                <li>Dados inseridos incorretamente (número, validade ou CVV).</li>
            </ul>
            <p>Em caso de recusa, entre em contato pelo WhatsApp. Podemos reenviar o link, tentar um número de parcelas diferente ou mudar para outra forma de pagamento, como PIX ou TED. A Iraná Natural não tem acesso ao motivo específico da recusa — essa informação é fornecida apenas pelo seu banco emissor. Se precisar cancelar ou devolver o pedido, consulte nossa <a href="<?= APP_URL ?>/trocas">política de trocas e devoluções</a>.</p>

            <div class="alerta-info">
                <strong>Dica:</strong> se o pagamento via link for recusado repetidamente, tente liberar a transação diretamente com seu banco — pelo telefone, aplicativo ou internet banking — antes de tentar novamente. Bancos frequentemente bloqueiam compras online por segurança preventiva, especialmente em valores maiores ou em lojas não reconhecidas.
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
            <p>Entre em contato pelo WhatsApp, escolha seus produtos e finalize seu pedido de forma simples e segura.</p>
            <a href="<?= Helper::whatsapp() ?>" class="btn btn-light btn-lg" target="_blank" rel="noopener">
                Falar pelo WhatsApp
            </a>
        </div>
    </div>
</section>
