<?php use App\Core\Helper; use App\Models\Pedido; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/minha-conta">Minha Conta</a>
            <span>›</span>
            <span>Pedido Recebido</span>
        </nav>
        <h1>Pedido Recebido</h1>
    </div>
</section>

<section class="section-sucesso">
    <div class="container container--narrow">
        <div class="sucesso-card">

            <div class="sucesso-icone">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="56" height="56">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
            </div>

            <h2 class="sucesso-titulo">Obrigado pelo seu pedido!</h2>
            <p class="sucesso-subtitulo">
                Seu pedido foi recebido e está aguardando a confirmação do pagamento pela InfinitePay.<br>
                Você receberá um e-mail assim que o pagamento for aprovado.
            </p>

            <div class="sucesso-numero">
                <span class="sucesso-numero__label">Número do pedido</span>
                <span class="sucesso-numero__valor"><?= Helper::e($pedido['numero']) ?></span>
            </div>

            <div class="sucesso-status">
                <span class="sucesso-status__label">Status:</span>
                <span class="sucesso-status__badge sucesso-status__badge--pendente">
                    <?= Pedido::statusLabel($pedido['status']) ?>
                </span>
            </div>

            <?php if (!empty($itens)): ?>
            <div class="sucesso-itens">
                <h3 class="sucesso-itens__titulo">Itens do pedido</h3>
                <?php foreach ($itens as $item): ?>
                <div class="sucesso-item">
                    <span class="sucesso-item__nome"><?= Helper::e($item['nome_produto']) ?></span>
                    <span class="sucesso-item__qty">
                        <?= (int)$item['quantidade'] ?>× <?= Helper::money((float)$item['preco_unitario']) ?>
                    </span>
                    <span class="sucesso-item__sub">
                        <?= Helper::money((float)$item['quantidade'] * (float)$item['preco_unitario']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <div class="sucesso-total">
                    <span>Total pago</span>
                    <span><?= Helper::money((float)$pedido['total']) ?></span>
                </div>
            </div>
            <?php endif; ?>

            <div class="sucesso-acoes">
                <a href="<?= APP_URL ?>/minha-conta" class="btn btn-primary">
                    Ver meus pedidos
                </a>
                <a href="<?= APP_URL ?>/produtos" class="btn btn-light">
                    Continuar comprando
                </a>
            </div>

            <p class="sucesso-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Dúvidas? Entre em contato pelo nosso
                <a href="<?= APP_URL ?>/contato">formulário de contato</a>.
            </p>

        </div>
    </div>
</section>
