<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span aria-hidden="true">›</span>
            <a href="<?= APP_URL ?>/minha-conta">Minha Conta</a>
            <span aria-hidden="true">›</span>
            <span>Alterar Senha</span>
        </nav>
        <h1>Alterar Senha</h1>
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
                <h2>Alterar sua senha</h2>
                <p>Informe sua senha atual para confirmar a alteração.</p>
            </div>

            <form method="POST"
                  action="<?= APP_URL ?>/minha-conta/alterar-senha"
                  class="auth-form"
                  novalidate
                  id="form-alterar-senha">

                <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">

                <div class="form-group">
                    <label for="senha_atual" class="form-label">
                        Senha atual <span class="required" aria-hidden="true">*</span>
                    </label>
                    <div class="input-password-wrap">
                        <input type="password"
                               id="senha_atual"
                               name="senha_atual"
                               class="form-input"
                               placeholder="••••••••"
                               autocomplete="current-password"
                               required
                               aria-required="true">
                        <button type="button"
                                class="toggle-password"
                                data-target="senha_atual"
                                aria-label="Mostrar senha atual">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nova_senha" class="form-label">
                        Nova senha <span class="required" aria-hidden="true">*</span>
                    </label>
                    <div class="input-password-wrap">
                        <input type="password"
                               id="nova_senha"
                               name="nova_senha"
                               class="form-input"
                               placeholder="Mínimo 8 caracteres"
                               minlength="8"
                               autocomplete="new-password"
                               required
                               aria-required="true"
                               aria-describedby="hint-nova-senha">
                        <button type="button"
                                class="toggle-password"
                                data-target="nova_senha"
                                aria-label="Mostrar nova senha">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <small id="hint-nova-senha" class="form-hint">Use ao menos 8 caracteres.</small>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha" class="form-label">
                        Confirmar nova senha <span class="required" aria-hidden="true">*</span>
                    </label>
                    <div class="input-password-wrap">
                        <input type="password"
                               id="confirmar_senha"
                               name="confirmar_senha"
                               class="form-input"
                               placeholder="Repita a nova senha"
                               autocomplete="new-password"
                               required
                               aria-required="true">
                        <button type="button"
                                class="toggle-password"
                                data-target="confirmar_senha"
                                aria-label="Mostrar confirmação de senha">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="btn btn-primary btn-block"
                        id="btn-salvar-senha">
                    Salvar nova senha
                </button>

                <a href="<?= APP_URL ?>/minha-conta"
                   class="btn btn-light btn-block"
                   style="margin-top:0.5rem">
                    Cancelar
                </a>

            </form>

            <div class="auth-card__footer">
                <p>
                    Não lembra sua senha atual?
                    <a href="<?= APP_URL ?>/minha-conta/recuperar-senha" class="link-verde">
                        Recuperar por e-mail →
                    </a>
                </p>
            </div>
        </div>

    </div>
</section>

<script>
(function () {
    'use strict';

    var form = document.getElementById('form-alterar-senha');
    var btn  = document.getElementById('btn-salvar-senha');
    if (!form || !btn) return;

    // Bloqueia envio duplo e exibe estado de carregamento
    form.addEventListener('submit', function (e) {
        if (btn.disabled) { e.preventDefault(); return; }

        var nova     = document.getElementById('nova_senha');
        var confirma = document.getElementById('confirmar_senha');

        // Validação client-side antes de enviar
        if (nova && confirma && nova.value !== confirma.value) {
            e.preventDefault();
            confirma.setCustomValidity('As senhas não conferem.');
            confirma.reportValidity();
            return;
        }
        if (confirma) confirma.setCustomValidity('');

        btn.disabled    = true;
        btn.textContent = 'Salvando…';
        btn.setAttribute('aria-busy', 'true');
    });

    // Limpa validação customizada ao digitar
    var confirmaInput = document.getElementById('confirmar_senha');
    if (confirmaInput) {
        confirmaInput.addEventListener('input', function () {
            this.setCustomValidity('');
        });
    }
})();
</script>
