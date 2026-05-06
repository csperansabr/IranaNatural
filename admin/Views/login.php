<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Admin <?= APP_NAME ?></title>
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
    </style>
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <img src="<?= APP_URL ?>/assets/images/logo.png" alt="<?= APP_NAME ?>" onerror="this.style.display='none'">
        <h2><?= APP_NAME ?></h2>
        <p>Painel Administrativo</p>
    </div>

    <?php if (!empty($erro)): ?>
    <div class="adm-alert adm-alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="/admin/login" method="POST">
        <div class="adm-form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required autofocus placeholder="admin@irananatural.com.br">
        </div>
        <div class="adm-form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required placeholder="••••••••">
        </div>
        <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg login-submit">Entrar</button>
    </form>

    <div class="login-footer">
        <a href="<?= APP_URL ?>" style="color: #2C5F2E;">← Voltar ao site</a>
    </div>
</div>
</body>
</html>
