<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/minha-conta/login">Entrar</a>
            <span>›</span>
            <span>Recuperar Senha</span>
        </nav>
        <h1>Recuperar Senha</h1>
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
                <h2>Redefinir senha</h2>
                <p>Informe seu e-mail e enviaremos um link para criar uma nova senha.</p>
            </div>

            <form method="POST" action="<?= APP_URL ?>/minha-conta/recuperar-senha" class="auth-form">
                <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">

                <div class="form-group">
                    <label for="email" class="form-label">E-mail cadastrado</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="seu@email.com" autocomplete="email" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Enviar link de recuperação</button>
            </form>

            <div class="auth-card__footer">
                <p><a href="<?= APP_URL ?>/minha-conta/login" class="link-verde">← Voltar ao login</a></p>
            </div>
        </div>

    </div>
</section>
