<?php
$pageTitle      = 'Alterar Senha';
$pageBreadcrumb = 'Perfil / Alterar Senha';
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="adm-card" style="max-width:520px">
    <div class="adm-card-header">
        <span class="adm-card-title">Alterar Senha</span>
    </div>
    <div class="adm-card-body">

        <p style="font-size:0.88rem;color:#555;margin-bottom:1.5rem">
            Você está alterando a senha de
            <strong><?= htmlspecialchars($user['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
            (<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>).
        </p>

        <form method="POST" action="/admin/alterar-senha" novalidate id="form-alterar-senha">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

            <div class="adm-form-group">
                <label for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual"
                       class="adm-input" required autofocus
                       placeholder="••••••••" autocomplete="current-password">
            </div>

            <hr style="border:none;border-top:1px solid #E2E8F0;margin:1.25rem 0">

            <div class="adm-form-group">
                <label for="senha_nova">Nova senha</label>
                <input type="password" id="senha_nova" name="senha_nova"
                       class="adm-input" required minlength="8"
                       placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                <div class="adm-hint">Mínimo de 8 caracteres. Não pode ser igual à senha atual.</div>
            </div>

            <div class="adm-form-group">
                <label for="senha_confirmacao">Confirmar nova senha</label>
                <input type="password" id="senha_confirmacao" name="senha_confirmacao"
                       class="adm-input" required minlength="8"
                       placeholder="Repita a nova senha" autocomplete="new-password">
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:0.5rem">
                <button type="submit" class="adm-btn adm-btn-primary">Salvar nova senha</button>
                <a href="/admin/dashboard" class="adm-btn adm-btn-secondary">Cancelar</a>
            </div>
        </form>

    </div>
</div>

<script>
(function () {
    var form = document.getElementById('form-alterar-senha');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        var nova = document.getElementById('senha_nova').value;
        var conf = document.getElementById('senha_confirmacao').value;
        if (nova.length > 0 && nova.length < 8) {
            e.preventDefault();
            alert('A nova senha deve ter no mínimo 8 caracteres.');
            return;
        }
        if (nova !== conf) {
            e.preventDefault();
            alert('As senhas não conferem.');
        }
    });
}());
</script>
