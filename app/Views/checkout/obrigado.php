<?php use App\Core\Helper; use App\Models\Pedido; ?>

<section class="section-obrigado">
    <div class="container container--narrow">

        <div class="obrigado-card">
            <div class="obrigado-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="48" height="48">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>

            <h1 class="obrigado-titulo">Pedido realizado!</h1>
            <p class="obrigado-subtitulo">
                Obrigada pela sua compra! Seu pedido foi recebido com sucesso.
            </p>

            <div class="obrigado-numero">
                <span class="obrigado-numero__label">Número do pedido</span>
                <span class="obrigado-numero__valor"><?= Helper::e($pedido['numero']) ?></span>
            </div>

            <!-- Status do pagamento -->
            <div class="obrigado-status-wrap">
                <span class="obrigado-status <?= Pedido::statusClass($pedido['status']) ?>">
                    <?= Pedido::statusLabel($pedido['status']) ?>
                </span>
            </div>

            <!-- Resumo -->
            <div class="obrigado-resumo">
                <div class="obrigado-bloco">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Endereço de entrega
                    </h3>
                    <p>
                        <?= Helper::e($pedido['entrega_logradouro']) ?>, <?= Helper::e($pedido['entrega_numero']) ?>
                        <?php if ($pedido['entrega_complemento']): ?> — <?= Helper::e($pedido['entrega_complemento']) ?><?php endif; ?><br>
                        <?= Helper::e($pedido['entrega_bairro']) ?> — <?= Helper::e($pedido['entrega_cidade']) ?>/<?= Helper::e($pedido['entrega_estado']) ?><br>
                        CEP <?= Helper::e($pedido['entrega_cep']) ?>
                    </p>
                </div>

                <div class="obrigado-bloco">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Pagamento
                    </h3>
                    <p>
                        <?php
                        $formaLabel = Pedido::pagamentoLabel($pedido['forma_pagamento']);
                        echo Helper::e($formaLabel !== 'Pendente' ? $formaLabel : 'Aguardando confirmação via InfinitePay');
                        ?>
                    </p>
                </div>
            </div>

            <!-- Itens -->
            <div class="obrigado-itens">
                <h3>Produtos do pedido</h3>
                <?php foreach ($itens as $item): ?>
                <div class="obrigado-item">
                    <span class="obrigado-item__nome"><?= Helper::e($item['nome_produto']) ?></span>
                    <span class="obrigado-item__qty">× <?= (int)$item['quantidade'] ?></span>
                    <span class="obrigado-item__subtotal"><?= Helper::money((float)$item['subtotal']) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="obrigado-total">
                    <span>Total</span>
                    <strong><?= Helper::money((float)$pedido['total']) ?></strong>
                </div>
            </div>

            <div class="obrigado-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p>Uma confirmação foi enviada para <strong><?= Helper::e($pedido['cliente_email']) ?></strong>.</p>
            </div>

            <div class="obrigado-acoes">
                <a href="<?= APP_URL ?>/minha-conta" class="btn btn-light">Ver meus pedidos</a>
                <a href="<?= APP_URL ?>/produtos" class="btn btn-primary">Continuar comprando</a>
            </div>

            <div class="obrigado-whatsapp">
                <p>Dúvidas? Fale conosco pelo WhatsApp:</p>
                <a href="<?= \App\Core\Helper::whatsapp('Olá! Tenho dúvidas sobre o pedido #' . $pedido['numero']) ?>"
                   class="btn btn-whatsapp" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Chamar no WhatsApp
                </a>
            </div>
        </div>

    </div>
</section>
