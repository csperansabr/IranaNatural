<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Envio e Entrega</span>
        </nav>
        <h1>Envio e Entrega</h1>
        <p class="page-hero-desc">Atendemos todo o Brasil pelos Correios e Jadlog. Em Porto Alegre e região, também entregamos via Motoboy e Uber Flash.</p>
    </div>
</section>

<!-- JORNADA DO PEDIDO -->
<section class="section-passos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Processamento</span>
            <h2>Da confirmação até a sua porta</h2>
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">Após o pagamento confirmado, seu pedido entra na fila de preparação. O prazo de postagem é sempre informado pelo WhatsApp antes de você confirmar o pedido.</p>
        </div>

        <div class="passos-grid">
            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">1</div>
                <h3>Confirmação do pagamento</h3>
                <p>Assim que identificamos o pagamento, confirmamos pelo WhatsApp e informamos o prazo estimado de preparação e postagem do seu pedido.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">2</div>
                <h3>Separação e embalagem</h3>
                <p>Cada pedido é embalado artesanalmente com atenção e cuidado. Produtos frágeis recebem proteção reforçada para garantir que cheguem intactos.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">3</div>
                <h3>Postagem e rastreamento</h3>
                <p>Após a postagem, enviamos o código de rastreamento pelo WhatsApp. A partir daí, você acompanha a entrega diretamente pelo site da transportadora.</p>
            </div>
        </div>
    </div>
</section>

<!-- FORMAS DE ENVIO -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Opções de envio</span>
            <h2>Como o pedido chega até você</h2>
        </div>

        <div class="entrega-grid">
            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">📦</div>
                <div class="entrega-info">
                    <h3>Correios — PAC</h3>
                    <p>Modalidade econômica dos Correios. Indicada para quem não tem urgência. O prazo de entrega varia conforme o CEP de destino e é informado antes da confirmação do pedido.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">⚡</div>
                <div class="entrega-info">
                    <h3>Correios — SEDEX</h3>
                    <p>Modalidade expressa dos Correios, com entrega mais ágil. Indicada para quem precisa receber com rapidez. Custo maior que o PAC, calculado conforme peso e destino.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🚚</div>
                <div class="entrega-info">
                    <h3>Jadlog</h3>
                    <p>Disponível como alternativa aos Correios para determinadas regiões e situações. Prazo e valor seguem a tabela da Jadlog e são informados pelo WhatsApp antes da confirmação.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🛵</div>
                <div class="entrega-info">
                    <h3>Motoboy / Uber Flash</h3>
                    <p>Entrega rápida em Porto Alegre e Grande POA. O custo do serviço é por conta do cliente e combinado pelo WhatsApp antes do envio. Disponibilidade conforme horário e região.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🚗</div>
                <div class="entrega-info">
                    <h3>Entrega local</h3>
                    <p>Entregamos pessoalmente em regiões de Porto Alegre e Grande Porto Alegre em horários combinados pelo WhatsApp, conforme disponibilidade da nossa agenda.</p>
                </div>
            </div>

            <div class="entrega-card">
                <div class="entrega-icon-wrap" aria-hidden="true">🤝</div>
                <div class="entrega-info">
                    <h3>Retirada pessoal</h3>
                    <p>Combine o ponto de retirada e o horário pelo WhatsApp. O pagamento pode ser feito na hora — em dinheiro, cartão ou PIX.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FRETE E RASTREAMENTO -->
<section class="section-institucional">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Cálculo do frete</h2>
            <p>O valor do frete é calculado com base no peso e nas dimensões do pedido, no CEP de destino e na modalidade de envio escolhida. Não há frete grátis — o custo de envio é integralmente informado e aprovado por você antes da confirmação.</p>
            <p>Para saber o frete antes de fechar o pedido, basta nos informar seu CEP pelo WhatsApp. Calculamos e enviamos os valores para PAC, SEDEX e Jadlog (quando disponível para o seu CEP) para que você escolha a melhor opção.</p>

            <h2>Rastreamento do pedido</h2>
            <p>Para todos os envios pelos Correios e pela Jadlog, o código de rastreamento é enviado pelo WhatsApp assim que o pedido é postado. Com ele, você pode acompanhar a entrega:</p>
            <ul>
                <li><strong>Correios</strong> — rastreie em <a href="https://rastreamento.correios.com.br" target="_blank" rel="noopener noreferrer">rastreamento.correios.com.br</a> ou no aplicativo dos Correios;</li>
                <li><strong>Jadlog</strong> — rastreie no site da Jadlog usando o código informado no WhatsApp.</li>
            </ul>
            <p>Para entregas por motoboy ou Uber Flash, o acompanhamento é feito diretamente pelo aplicativo utilizado para o envio.</p>

        </div>
    </div>
</section>

<!-- POLÍTICAS DE ENTREGA -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Prazo de postagem</h2>
            <p>Não trabalhamos com um prazo fixo de postagem. Cada pedido é tratado individualmente, e o prazo de preparação e envio é sempre informado e combinado com você pelo WhatsApp antes da confirmação do pedido. Respeitamos nossa fila de produção artesanal — qualidade leva o tempo que precisa.</p>

            <h2>Possíveis atrasos</h2>
            <p>Situações fora do nosso controle podem impactar o prazo de entrega. Fique atento a:</p>
            <ul>
                <li>Greves ou paralisações dos Correios ou da Jadlog;</li>
                <li>Feriados nacionais, estaduais ou municipais que interrompam a operação das transportadoras;</li>
                <li>Condições climáticas extremas que dificultem a circulação nas regiões de origem ou destino;</li>
                <li>Alta demanda em datas comemorativas (Dia das Mães, Natal, Carnaval);</li>
                <li>Endereços de difícil acesso ou CEPs com cobertura restrita;</li>
                <li>Informações de entrega incompletas ou incorretas fornecidas no momento do pedido.</li>
            </ul>
            <p>Em caso de atraso significativo, entre em contato pelo WhatsApp. Faremos o possível para ajudar no rastreamento e na resolução junto à transportadora.</p>

            <h2>Responsabilidade sobre endereço incorreto</h2>
            <p>Conferir cuidadosamente o endereço de entrega antes de confirmar o pedido é fundamental. Caso o pedido seja enviado para um endereço incorreto por informação fornecida pelo cliente, <strong>o custo de reenvio é de responsabilidade do cliente</strong>.</p>

            <div class="alerta-info">
                <strong>Atenção:</strong> verifique seu CEP, número, complemento e nome do destinatário antes de confirmar o pedido pelo WhatsApp. Um endereço incompleto ou incorreto pode resultar em devolução do pacote e custo adicional de reenvio.
            </div>

            <h2>Tentativas de entrega</h2>
            <p>Os Correios realizam até <strong>3 tentativas de entrega</strong> no endereço informado. Caso o destinatário não seja localizado nas três tentativas, o objeto retorna à unidade de origem dos Correios e fica disponível para retirada por um período limitado antes de ser devolvido ao remetente.</p>
            <p>Para a Jadlog, as tentativas de entrega seguem a política interna da transportadora, informada pelo WhatsApp conforme o caso.</p>
            <p>Para evitar problemas, certifique-se de que haverá alguém disponível para receber o pedido ou que o porteiro esteja autorizado a assinar o recebimento.</p>

            <h2>Pacote devolvido ao remetente</h2>
            <p>Se o pedido for devolvido pelos Correios ou pela Jadlog por motivo de ausência do destinatário, endereço incorreto ou recusa de recebimento, o produto retorna à Iraná Natural. Neste caso:</p>
            <ul>
                <li>Entraremos em contato pelo WhatsApp assim que o pacote chegar;</li>
                <li>O reenvio será realizado após o <strong>pagamento de novo frete</strong> pelo cliente;</li>
                <li>O produto permanece reservado por até 30 dias após o contato. Após esse prazo sem resposta, o pedido poderá ser cancelado.</li>
            </ul>

            <div class="alerta-info">
                <strong>Dica ao receber o pedido:</strong> antes de assinar qualquer protocolo de entrega, inspecione a embalagem externa. Se houver sinais evidentes de violação ou dano, recuse o recebimento, fotografe o pacote e nos acione imediatamente pelo WhatsApp. Isso facilita o processo de ressarcimento junto à transportadora. Veja como funciona nossa <a href="<?= APP_URL ?>/trocas">política de trocas e devoluções</a>.
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-cta">
    <div class="container">
        <div class="cta-box">
            <div class="cta-leaf" aria-hidden="true">🌿</div>
            <h2>Dúvidas sobre o envio?</h2>
            <p>Informe seu CEP e o que você quer pedir. Calculamos o frete, informamos o prazo e você decide sem compromisso.</p>
            <a href="<?= Helper::whatsapp() ?>" class="btn btn-light btn-lg" target="_blank" rel="noopener">
                Falar pelo WhatsApp
            </a>
        </div>
    </div>
</section>
