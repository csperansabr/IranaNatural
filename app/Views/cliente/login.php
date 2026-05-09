<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span aria-hidden="true">›</span>
            <span>Entrar na Conta</span>
        </nav>
        <h1>Minha Conta</h1>
    </div>
</section>

<section class="section-auth">
    <div class="container container--narrow">

        <?php if ($flash): ?>
        <div class="alert alert--success" role="alert"><?= Helper::e($flash) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
        <div class="alert alert--erro" role="alert"><?= Helper::e($erro) ?></div>
        <?php endif; ?>

        <div class="auth-card">
            <div class="auth-card__header">
                <h2>Bem-vinda de volta!</h2>
                <p>Acesse sua conta para finalizar a compra e acompanhar seus pedidos.</p>
            </div>

            <form method="POST" action="<?= APP_URL ?>/minha-conta/login" class="auth-form" novalidate>
                <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">
                <?php if ($redirect): ?>
                <input type="hidden" name="redirect" value="<?= Helper::e($redirect) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="seu@email.com" autocomplete="email" required>
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-password-wrap">
                        <input type="password" id="senha" name="senha" class="form-input"
                               placeholder="Sua senha" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar/ocultar senha" data-target="senha">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="form-hint">
                        <a href="<?= APP_URL ?>/minha-conta/recuperar-senha" class="link-subtle">Esqueci minha senha</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>

            <div class="auth-card__footer">
                <p>Ainda não tem conta? <a href="<?= APP_URL ?>/minha-conta/cadastro" class="link-verde">Criar conta grátis</a></p>
            </div>
        </div>

    </div>
</section>
