<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Trocas e Devoluções</span>
        </nav>
        <h1>Trocas e Devoluções</h1>
        <p class="page-hero-desc">Conheça seus direitos, os motivos que cobrem troca ou devolução e como acionar o processo de forma simples pelo WhatsApp.</p>
    </div>
</section>

<!-- MOTIVOS ACEITOS -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Motivos aceitos</span>
            <h2>Quando você pode solicitar troca ou devolução</h2>
            <p style="color:var(--texto-medio);max-width:560px;margin:0.75rem auto 0">Aceitamos solicitações nos quatro cenários abaixo. Cada caso tem regras específicas de prazo, frete e resolução detalhadas mais adiante.</p>
        </div>

        <div class="sobre-valores">
            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">💭</div>
                <h3>Arrependimento de compra</h3>
                <p>Mudou de ideia após receber o pedido? Você pode devolver o produto em até <strong>7 dias corridos</strong> do recebimento, sem precisar justificar — desde que esteja lacrado e sem sinais de uso.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">🔍</div>
                <h3>Defeito de fabricação</h3>
                <p>O produto chegou com problema visível de preparo — textura irregular, separação incomum, odor atípico não descrito ou qualquer falha de produção. Coberto em até <strong>30 dias</strong> do recebimento.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">📦</div>
                <h3>Avaria no transporte</h3>
                <p>O produto chegou com embalagem violada, quebrado ou com conteúdo comprometido em decorrência do envio. Fotografe ao receber — isso agiliza a análise. Coberto em até <strong>30 dias</strong> do recebimento.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">📋</div>
                <h3>Produto divergente</h3>
                <p>O produto recebido é diferente do pedido — em composição, fragrância, tamanho ou qualquer característica descrita no momento da compra. Coberto em até <strong>30 dias</strong> do recebimento.</p>
            </div>
        </div>
    </div>
</section>

<!-- PROCEDIMENTO -->
<section class="section-passos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Passo a passo</span>
            <h2>Como solicitar troca ou devolução</h2>
            <p style="color:var(--texto-medio);max-width:520px;margin:0.75rem auto 0">Tudo é feito pelo WhatsApp. Nossa equipe orienta cada etapa e informa o prazo de resolução após a análise do caso.</p>
        </div>

        <div class="passos-grid">
            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">1</div>
                <h3>Acione pelo WhatsApp</h3>
                <p>Entre em contato dentro do prazo correspondente ao seu caso (7 dias para arrependimento, 30 dias para defeito, avaria ou divergência). Descreva o ocorrido e envie fotos ou vídeos do produto e da embalagem.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">2</div>
                <h3>Devolva o produto</h3>
                <p>Após a análise inicial pelo WhatsApp, você receberá instruções de envio. Para defeito, avaria ou produto divergente, a Iraná Natural envia o código de postagem. Para arrependimento, o frete de retorno é por conta do cliente.</p>
            </div>

            <div class="passo-item">
                <div class="passo-numero" aria-hidden="true">3</div>
                <h3>Análise e resolução</h3>
                <p>Com o produto em mãos, analisamos em até <strong>2 dias úteis</strong>. Se aprovado, você escolhe entre troca por produto equivalente ou reembolso — que é processado em até <strong>3 dias úteis</strong> após a decisão.</p>
            </div>
        </div>
    </div>
</section>

<!-- DETALHES POR MOTIVO -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Arrependimento de compra</h2>
            <p>O direito de arrependimento é garantido pelo <strong>CDC art. 49</strong> para compras realizadas fora de estabelecimentos comerciais — o que inclui todas as compras feitas pelo WhatsApp. Você tem <strong>7 dias corridos</strong> a partir da data de recebimento para solicitar a devolução, sem precisar apresentar justificativa.</p>
            <p>Para que a devolução por arrependimento seja aceita, o produto deve:</p>
            <ul>
                <li>Estar lacrado e na embalagem original, sem sinais de abertura ou uso;</li>
                <li>Ser devolvido fisicamente à Iraná Natural em perfeitas condições.</li>
            </ul>
            <p>O <strong>frete de retorno é de responsabilidade do cliente</strong>. O reembolso cobre apenas o valor do produto — o frete pago na compra original não é reembolsado. O valor devolvido é processado em até 3 dias úteis após o recebimento e aprovação do produto.</p>

            <div class="alerta-info">
                <strong>Atenção:</strong> o prazo de 7 dias começa a contar na data em que o produto é entregue, não na data do pedido. Solicitações fora do prazo não podem ser aceitas como arrependimento, mas podem ser analisadas sob outros critérios se houver defeito ou divergência.
            </div>

            <h2>Defeito de fabricação</h2>
            <p>Produtos com vício de fabricação — falha no processo de preparo artesanal que compromete a qualidade, segurança ou função do produto — são cobertos pelo <strong>CDC art. 26, inciso I</strong>, com prazo de <strong>30 dias corridos</strong> a partir do recebimento para produtos não duráveis (cosméticos e similares). Consulte também nossa <a href="<?= APP_URL ?>/garantia">política de garantia</a> para entender o processo completo de análise e aprovação.</p>
            <p>Exemplos de defeito de fabricação coberto: textura completamente diferente do padrão do produto, odor acentuadamente atípico não descrito, separação de fases de forma anormal, embalagem com vedação deficiente de origem.</p>

            <h2>Avaria no transporte</h2>
            <p>Danos causados durante o envio — embalagem violada, produto quebrado, conteúdo vazado ou comprometido — são cobertos em até <strong>30 dias corridos</strong> do recebimento, independentemente da transportadora utilizada.</p>
            <p>Para facilitar a análise e o acionamento junto à transportadora, fotografe a embalagem externa e o produto antes de descartá-los. Quanto mais documentação, mais ágil o processo.</p>

            <div class="alerta-info">
                <strong>Dica importante:</strong> se o pacote chegar com sinais visíveis de violação ou amassados expressivos, <strong>recuse o recebimento</strong>, fotografe o pacote antes de devolver ao entregador e acione a Iraná Natural imediatamente pelo WhatsApp. Isso facilita muito o processo de ressarcimento junto à transportadora.
            </div>

            <h2>Produto divergente</h2>
            <p>Se o produto recebido for diferente do que foi combinado no pedido — em composição, fragrância, tamanho, quantidade ou qualquer característica que conste na descrição —, a situação é coberta em até <strong>30 dias corridos</strong> do recebimento. Envie pelo WhatsApp a descrição do pedido e fotos do produto recebido para agilizar a análise.</p>

        </div>
    </div>
</section>

<!-- FRETE REVERSO -->
<section class="section-institucional">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Frete reverso</h2>
            <p>O responsável pelo custo do envio de retorno varia conforme o motivo da solicitação:</p>
            <ul>
                <li><strong>Defeito de fabricação</strong> — frete de retorno por conta da <strong>Iraná Natural</strong>. Você recebe as instruções de postagem pelo WhatsApp e não paga nada pelo envio;</li>
                <li><strong>Avaria no transporte</strong> — frete de retorno por conta da <strong>Iraná Natural</strong>;</li>
                <li><strong>Produto divergente</strong> — frete de retorno por conta da <strong>Iraná Natural</strong>;</li>
                <li><strong>Arrependimento de compra</strong> — frete de retorno por conta do <strong>cliente</strong>. O produto deve ser enviado por Correios ou transportadora à escolha do cliente, com rastreamento.</li>
            </ul>
            <p>Em todos os casos, o produto deve ser embalado com cuidado para evitar danos durante o transporte de retorno. Produtos que cheguem danificados por embalagem inadequada no envio de volta podem ter a solicitação impactada pela análise.</p>

        </div>
    </div>
</section>

<!-- REEMBOLSO E ESTORNO -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Reembolso e estorno</h2>
            <p>Após a aprovação da solicitação, o reembolso é processado de acordo com a forma de pagamento original:</p>
            <ul>
                <li><strong>PIX</strong> — reembolso via PIX em até <strong>3 dias úteis</strong> após a aprovação. A chave PIX de destino será solicitada pelo WhatsApp;</li>
                <li><strong>Transferência bancária (TED/DOC)</strong> — reembolso via TED em até <strong>3 dias úteis</strong> após a aprovação. Os dados bancários serão solicitados pelo WhatsApp;</li>
                <li><strong>Cartão de crédito</strong> — estorno processado pela InfinitePay em até <strong>3 dias úteis</strong> após a aprovação. O prazo para o valor aparecer na fatura depende do banco emissor e do ciclo de fechamento do cartão — podendo aparecer na fatura atual ou na seguinte;</li>
                <li><strong>Dinheiro (retirada presencial)</strong> — reembolso em dinheiro ou via PIX, conforme preferência combinada pelo WhatsApp.</li>
            </ul>

            <div class="alerta-info">
                <strong>Para reembolso por arrependimento:</strong> o valor devolvido corresponde ao preço pago pelo produto. O frete da compra original não é reembolsado. Para defeito, avaria ou produto divergente, o reembolso cobre o valor integral pago, incluindo o frete proporcional ao item devolvido quando aplicável.
            </div>

            <h2>Troca por produto equivalente</h2>
            <p>Quando a troca é aprovada e o produto está disponível em estoque, enviamos um produto equivalente sem custo adicional de frete. O prazo de envio do substituto é informado pelo WhatsApp após a aprovação — seguindo a mesma lógica de preparação e postagem de um pedido novo. O produto de substituição tem as mesmas condições de garantia de um produto original.</p>

        </div>
    </div>
</section>

<!-- RECUSAS E O QUE NÃO É COBERTO -->
<section class="section-institucional">
    <div class="container">
        <div class="institucional-conteudo">

            <h2>Situações não cobertas pela política</h2>
            <p>As solicitações de troca ou devolução são recusadas nas seguintes situações:</p>

            <h2>Fora do prazo</h2>
            <ul>
                <li>Arrependimento solicitado após 7 dias corridos do recebimento;</li>
                <li>Defeito, avaria ou divergência reportados após 30 dias corridos do recebimento.</li>
            </ul>

            <h2>Produto com sinais de uso (arrependimento)</h2>
            <p>Devoluções por arrependimento só são aceitas com o produto <strong>lacrado e sem qualquer sinal de abertura ou uso</strong>. Produtos abertos, parcialmente utilizados ou sem embalagem original não são elegíveis para devolução por arrependimento — independentemente do prazo.</p>

            <h2>Mau uso e armazenamento inadequado</h2>
            <ul>
                <li>Danos causados por armazenamento em local com calor excessivo, umidade alta ou exposição ao sol;</li>
                <li>Produto utilizado de forma diferente da indicada (aplicação incorreta, mistura com outras substâncias não recomendadas);</li>
                <li>Danos físicos à embalagem causados pelo cliente após o recebimento (quedas, impactos).</li>
            </ul>

            <h2>Produto após o prazo de validade</h2>
            <p>Solicitações de troca ou devolução de produtos cujo prazo de validade já expirou não são cobertas pela política, independentemente do motivo apresentado.</p>

            <h2>Reações alérgicas a ingredientes listados</h2>
            <p>Produtos naturais contêm ingredientes ativos que podem causar reações em pessoas com sensibilidades específicas. A composição completa é disponibilizada na descrição de cada produto. Reações a ingredientes que constam na lista de composição não são cobertas pela política de trocas — a responsabilidade de verificar a composição antes da compra é do cliente.</p>
            <p>Exceção: se o ingrediente causador da reação <strong>não estiver listado</strong> na composição — caracterizando erro de descrição ou divergência de produto —, a solicitação é analisada com prioridade como produto divergente.</p>

            <div class="alerta-info">
                <strong>Dúvidas antes de comprar?</strong> Pergunte pelo WhatsApp sobre ingredientes, indicações ou contraindicações de qualquer produto. Preferimos orientar antes da compra do que lidar com uma situação desconfortável depois.
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-cta">
    <div class="container">
        <div class="cta-box">
            <div class="cta-leaf" aria-hidden="true">🌿</div>
            <h2>Precisa solicitar uma troca ou devolução?</h2>
            <p>Entre em contato pelo WhatsApp com fotos do produto e uma descrição do ocorrido. Nossa equipe retorna em até 2 dias úteis.</p>
            <a href="<?= Helper::whatsapp() ?>" class="btn btn-light btn-lg" target="_blank" rel="noopener">
                Falar pelo WhatsApp
            </a>
        </div>
    </div>
</section>
