<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Contato</span>
        </nav>
        <h1>Entre em Contato</h1>
        <p class="page-hero-desc">Toda mensagem é lida por uma pessoa real. Estamos aqui para ajudar antes, durante e depois da sua compra.</p>
    </div>
</section>

<!-- MENSAGEM INSTITUCIONAL -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="contato-intro">
            <p>A Iraná Natural é um negócio artesanal e familiar. Quando você entra em contato, fala diretamente com quem faz os produtos — não com um atendimento automatizado. Isso significa respostas mais humanas e atenção real ao seu caso.</p>
            <p>O canal mais rápido é o <strong>WhatsApp</strong>. Para pedidos, dúvidas sobre produtos ou qualquer situação urgente, é por lá que a conversa flui melhor. O formulário e o e-mail são ótimos para mensagens detalhadas ou sem pressa.</p>
        </div>
    </div>
</section>

<!-- CANAIS + FORMULÁRIO -->
<section class="section-contato">
    <div class="container contato-grid">

        <!-- Canais de Atendimento -->
        <div class="contato-info">
            <h2>Canais de Atendimento</h2>

            <div class="contato-item">
                <div class="contato-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div>
                    <h4>WhatsApp <span class="canal-badge">Mais rápido</span></h4>
                    <a href="<?= Helper::whatsapp() ?>" target="_blank" rel="noopener">(51) 99229-6036</a>
                    <p>Canal ideal para pedidos, dúvidas sobre produtos, trocas e qualquer atendimento que precise de resposta ágil.</p>
                </div>
            </div>

            <div class="contato-item">
                <div class="contato-icon" aria-hidden="true">✉️</div>
                <div>
                    <h4>E-mail</h4>
                    <a href="mailto:<?= EMAIL_CONTATO ?>"><?= EMAIL_CONTATO ?></a>
                    <p>Para dúvidas detalhadas, solicitações formais e assuntos de privacidade. Respondemos em até <strong>2 dias úteis</strong>.</p>
                </div>
            </div>

            <div class="contato-item">
                <div class="contato-icon" aria-hidden="true">📸</div>
                <div>
                    <h4>Instagram</h4>
                    <a href="<?= INSTAGRAM_URL ?>" target="_blank" rel="noopener">@irananatural</a>
                    <p>Acompanhe novos produtos, bastidores da produção artesanal e lançamentos. DMs respondidas sempre que possível.</p>
                </div>
            </div>

            <div class="contato-item">
                <div class="contato-icon" aria-hidden="true">🕐</div>
                <div>
                    <h4>Horário de Atendimento</h4>
                    <p><strong>Segunda a Sexta</strong> — 9h às 18h</p>
                    <p><strong>Sábado</strong> — 9h às 13h</p>
                    <p class="contato-aviso">Mensagens recebidas fora do horário são respondidas no início do próximo período de atendimento.</p>
                </div>
            </div>

            <div class="contato-item">
                <div class="contato-icon" aria-hidden="true">📍</div>
                <div>
                    <h4>Retirada Pessoal</h4>
                    <p>Atendemos em Porto Alegre e região. O endereço de retirada é combinado individualmente pelo WhatsApp após a confirmação do pedido.</p>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <div class="contato-form-box">
            <h2>Enviar Mensagem</h2>
            <p class="form-intro">Prefere escrever com calma? Use o formulário abaixo. Respondemos em até 2 dias úteis. Para retorno rápido, o WhatsApp é a melhor opção.</p>

            <?php if ($flash): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
            <div class="alert alert-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/contato/enviar" method="POST" class="form-contato" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group">
                    <label for="nome">Seu nome <span class="form-obrigatorio" aria-hidden="true">*</span></label>
                    <input type="text" id="nome" name="nome" required placeholder="Como podemos te chamar?" autocomplete="name" maxlength="120">
                </div>
                <div class="form-group">
                    <label for="email">E-mail <span class="form-obrigatorio" aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com" autocomplete="email" maxlength="254">
                </div>
                <div class="form-group">
                    <label for="assunto">Assunto</label>
                    <input type="text" id="assunto" name="assunto" placeholder="Pedido, dúvida, parceria..." maxlength="120">
                </div>
                <div class="form-group">
                    <label for="mensagem">Mensagem <span class="form-obrigatorio" aria-hidden="true">*</span></label>
                    <textarea id="mensagem" name="mensagem" rows="5" required placeholder="Escreva sua mensagem aqui..." maxlength="2000"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%">Enviar Mensagem</button>
                <p class="form-privacidade">Ao enviar, você concorda com nossa <a href="<?= APP_URL ?>/politica-privacidade">Política de Privacidade</a>. Seus dados são usados exclusivamente para responder sua mensagem.</p>
            </form>
        </div>
    </div>
</section>
