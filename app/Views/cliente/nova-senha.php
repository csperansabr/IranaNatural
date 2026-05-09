<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <h1>Nova Senha</h1>
    </div>
</section>

<section class="section-auth">
    <div class="container container--narrow">

        <?php if ($erro): ?>
        <div class="alert alert--erro"><?= Helper::e($erro) ?></div>
        <?php endif; ?>

        <div class="auth-card">
            <div class="auth-card__header">
                <h2>Criar nova senha</h2>
                <p>Escolha uma senha forte com ao menos 8 caracteres.</p>
            </div>

            <form method="POST" action="<?= APP_URL ?>/minha-conta/nova-senha/<?= Helper::e($token) ?>" class="auth-form">
                <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">

                <div class="form-group">
                    <label for="senha" class="form-label">Nova senha</label>
                    <div class="input-password-wrap">
                        <input type="password" id="senha" name="senha" class="form-input"
                               placeholder="Mínimo 8 caracteres" minlength="8" required>
                        <button type="button" class="toggle-password" data-target="senha" aria-label="Mostrar senha">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha" class="form-label">Confirmar nova senha</label>
                    <div class="input-password-wrap">
                        <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-input"
                               placeholder="Repita a nova senha" required>
                        <button type="button" class="toggle-password" data-target="confirmar_senha" aria-label="Mostrar senha">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Salvar nova senha</button>
            </form>
        </div>
    </div>
</section>
