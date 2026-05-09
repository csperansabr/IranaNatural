<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/carrinho">Carrinho</a>
            <span>›</span>
            <span>Checkout</span>
        </nav>
        <h1>Finalizar Pedido</h1>
    </div>
</section>

<section class="section-checkout">
    <div class="container">

        <!-- Steps -->
        <div class="checkout-steps">
            <div class="checkout-step active">
                <span class="checkout-step__num">1</span>
                <span class="checkout-step__label">Resumo</span>
            </div>
            <div class="checkout-step">
                <span class="checkout-step__num">2</span>
                <span class="checkout-step__label">Endereço</span>
            </div>
            <div class="checkout-step">
                <span class="checkout-step__num">3</span>
                <span class="checkout-step__label">Confirmação</span>
            </div>
        </div>

        <div class="checkout-layout">
            <main class="checkout-main">
                <h2 class="checkout-section-title">Produtos no carrinho</h2>

                <div class="checkout-produtos">
                    <?php foreach ($itens as $item): ?>
                    <div class="checkout-produto-item">
                        <?php if ($item['imagem']): ?>
                        <img src="<?= Helper::upload($item['imagem']) ?>"
                             alt="<?= Helper::e($item['nome']) ?>"
                             class="checkout-produto-item__img" loading="lazy">
                        <?php else: ?>
                        <img src="<?= APP_URL ?>/assets/images/placeholder.svg"
                             alt="<?= Helper::e($item['nome']) ?>"
                             class="checkout-produto-item__img">
                        <?php endif; ?>
                        <div class="checkout-produto-item__info">
                            <span class="checkout-produto-item__nome"><?= Helper::e($item['nome']) ?></span>
                            <span class="checkout-produto-item__qty">Qtd: <?= (int)$item['quantidade'] ?></span>
                        </div>
                        <span class="checkout-produto-item__subtotal">
                            <?= Helper::money((float)$item['quantidade'] * (float)$item['preco_unitario']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="checkout-nav">
                    <a href="<?= APP_URL ?>/carrinho" class="btn btn-light">← Editar carrinho</a>
                    <a href="<?= APP_URL ?>/checkout/endereco" class="btn btn-primary">Continuar →</a>
                </div>
            </main>

            <aside class="checkout-sidebar">
                <div class="checkout-resumo-card">
                    <h3>Resumo</h3>
                    <div class="checkout-resumo-linha">
                        <span>Subtotal (<?= count($itens) ?> itens)</span>
                        <span><?= Helper::money($total) ?></span>
                    </div>
                    <div class="checkout-resumo-linha">
                        <span>Frete</span>
                        <span>A combinar</span>
                    </div>
                    <div class="checkout-resumo-linha checkout-resumo-linha--total">
                        <span>Total</span>
                        <span><?= Helper::money($total) ?></span>
                    </div>
                </div>
            </aside>
        </div>

    </div>
</section>
