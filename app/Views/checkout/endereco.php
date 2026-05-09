<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/carrinho">Carrinho</a>
            <span>›</span>
            <span>Endereço de Entrega</span>
        </nav>
        <h1>Endereço de Entrega</h1>
    </div>
</section>

<section class="section-checkout">
    <div class="container">

        <!-- Steps -->
        <div class="checkout-steps">
            <div class="checkout-step done"><span class="checkout-step__num">✓</span><span class="checkout-step__label">Resumo</span></div>
            <div class="checkout-step active"><span class="checkout-step__num">2</span><span class="checkout-step__label">Endereço</span></div>
            <div class="checkout-step"><span class="checkout-step__num">3</span><span class="checkout-step__label">Pagamento</span></div>
            <div class="checkout-step"><span class="checkout-step__num">4</span><span class="checkout-step__label">Confirmação</span></div>
        </div>

        <?php if ($erro): ?>
        <div class="alert alert--erro"><?= Helper::e($erro) ?></div>
        <?php endif; ?>

        <div class="checkout-layout">
            <main class="checkout-main">
                <h2 class="checkout-section-title">Para onde enviar?</h2>

                <form method="POST" action="<?= APP_URL ?>/checkout/endereco" class="checkout-form" id="form-endereco">
                    <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">

                    <div class="form-row">
                        <div class="form-group form-group--sm">
                            <label for="cep" class="form-label">CEP <span class="required">*</span></label>
                            <input type="text" id="cep" name="cep" class="form-input"
                                   value="<?= Helper::e($endereco['cep'] ?? '') ?>"
                                   placeholder="00000-000" maxlength="9" data-mask="cep" required>
                        </div>
                        <div class="form-group form-group--lg">
                            <label for="logradouro" class="form-label">Rua / Avenida <span class="required">*</span></label>
                            <input type="text" id="logradouro" name="logradouro" class="form-input"
                                   value="<?= Helper::e($endereco['logradouro'] ?? '') ?>"
                                   placeholder="Nome da rua" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--sm">
                            <label for="numero" class="form-label">Número <span class="required">*</span></label>
                            <input type="text" id="numero" name="numero" class="form-input"
                                   value="<?= Helper::e($endereco['numero'] ?? '') ?>"
                                   placeholder="123" required>
                        </div>
                        <div class="form-group">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" id="complemento" name="complemento" class="form-input"
                                   value="<?= Helper::e($endereco['complemento'] ?? '') ?>"
                                   placeholder="Apto, Bloco, etc.">
                        </div>
                        <div class="form-group">
                            <label for="bairro" class="form-label">Bairro <span class="required">*</span></label>
                            <input type="text" id="bairro" name="bairro" class="form-input"
                                   value="<?= Helper::e($endereco['bairro'] ?? '') ?>"
                                   placeholder="Seu bairro" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--lg">
                            <label for="cidade" class="form-label">Cidade <span class="required">*</span></label>
                            <input type="text" id="cidade" name="cidade" class="form-input"
                                   value="<?= Helper::e($endereco['cidade'] ?? '') ?>"
                                   placeholder="Sua cidade" required>
                        </div>
                        <div class="form-group form-group--sm">
                            <label for="estado" class="form-label">Estado <span class="required">*</span></label>
                            <select id="estado" name="estado" class="form-input" required>
                                <option value="">UF</option>
                                <?php
                                $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                $selEstado = $endereco['estado'] ?? '';
                                foreach ($estados as $uf):
                                ?>
                                <option value="<?= $uf ?>"<?= $selEstado === $uf ? ' selected' : '' ?>><?= $uf ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group form-checkbox-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="salvar_cadastro" value="1" checked>
                            <span>Salvar como meu endereço principal</span>
                        </label>
                    </div>

                    <div class="checkout-nav">
                        <a href="<?= APP_URL ?>/checkout" class="btn btn-light">← Voltar</a>
                        <button type="submit" class="btn btn-primary">Continuar →</button>
                    </div>
                </form>
            </main>

            <aside class="checkout-sidebar">
                <div class="checkout-resumo-card">
                    <h3>Resumo</h3>
                    <?php foreach ($itens as $item): ?>
                    <div class="checkout-mini-item">
                        <span class="checkout-mini-item__nome"><?= Helper::e($item['nome']) ?> <em>×<?= (int)$item['quantidade'] ?></em></span>
                        <span><?= Helper::money((float)$item['quantidade'] * (float)$item['preco_unitario']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="checkout-resumo-linha checkout-resumo-linha--total">
                        <span>Total</span>
                        <span><?= Helper::money($total) ?></span>
                    </div>
                </div>
            </aside>
        </div>

    </div>
</section>
