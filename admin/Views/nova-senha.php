<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha — Admin <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <style>
        body { background: #1a1a1a; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box {
            background: white; border-radius: 12px; padding: 3rem;
            width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .login-logo { text-align: center; margin-bottom: 2rem; }
        .login-logo img { height: 56px; }
        .login-logo h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem; font-weight: 400;
            color: #2C5F2E; margin-top: 0.5rem;
        }
        .login-logo p { font-size: 0.82rem; color: #718096; margin: 0; }
        .login-box .adm-form-group { margin-bottom: 1.1rem; }
        .login-submit { width: 100%; padding: 0.8rem; margin-top: 0.5rem; }
        .login-footer { text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: #718096; }
        .login-footer a { color: #2C5F2E; text-decoration: none; }
        .login-footer a:hover { text-decoration: underline; }
        .senha-requisitos {
            font-size: 0.78rem; color: #718096;
            background: #f8f9fa; border-radius: 6px;
            padding: 0.6rem 0.85rem; margin-top: 0.35rem;
            border-left: 3px solid #CBD5E0;
        }
    </style>
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <img src="<?= APP_URL ?>/assets/images/logo.png" alt="<?= APP_NAME ?>" onerror="this.style.display='none'">
        <h2><?= APP_NAME ?></h2>
        <p>Criar Nova Senha</p>
    </div>

    <?php if ($erro): ?>
    <div class="adm-alert adm-alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="/admin/nova-senha/<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" method="POST" novalidate id="form-nova-senha">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

        <div class="adm-form-group">
            <label for="senha">Nova senha</label>
            <input type="password" id="senha" name="senha" required autofocus
                   minlength="8" placeholder="Mínimo 8 caracteres"
                   autocomplete="new-password">
            <div class="senha-requisitos">Mínimo de 8 caracteres.</div>
        </div>

        <div class="adm-form-group">
            <label for="senha_confirmacao">Confirmar nova senha</label>
            <input type="password" id="senha_confirmacao" name="senha_confirmacao" required
                   minlength="8" placeholder="Repita a nova senha"
                   autocomplete="new-password">
        </div>

        <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg login-submit">
            Salvar nova senha
        </button>
    </form>

    <div class="login-footer">
        <a href="/admin/login">← Voltar ao login</a>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('form-nova-senha');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        var s = document.getElementById('senha').value;
        var c = document.getElementById('senha_confirmacao').value;
        if (s.length < 8) {
            e.preventDefault();
            alert('A senha deve ter no mínimo 8 caracteres.');
            return;
        }
        if (s !== c) {
            e.preventDefault();
            alert('As senhas não conferem.');
        }
    });
}());
</script>
</body>
</html>
