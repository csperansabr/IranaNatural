<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' — Admin ' : 'Admin — ' ?><?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <meta name="robots" content="noindex, nofollow">
    <?php if (isset($pageStyles)) echo $pageStyles; ?>
</head>
<body>
<div class="adm-wrapper">

    <!-- ===== SIDEBAR ===== -->
    <aside class="adm-sidebar" id="adm-sidebar">
        <div class="adm-sidebar-logo">
            <img src="<?= APP_URL ?>/assets/images/logo.png" alt="<?= APP_NAME ?>" onerror="this.style.display='none'">
            <span><?= APP_NAME ?><br><small class="adm-sidebar-sub">Painel Admin</small></span>
        </div>

        <nav class="adm-nav">
            <a href="/admin/dashboard" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') || ($_SERVER['REQUEST_URI'] ?? '') === '/admin/' ? 'active' : '' ?>">
                <span class="adm-nav-icon">📊</span> Dashboard
            </a>

            <div class="adm-nav-section">Site</div>
            <a href="/admin/categorias" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/categorias') ? 'active' : '' ?>">
                <span class="adm-nav-icon">🏷️</span> Categorias
            </a>
            <a href="/admin/produtos" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/produtos') ? 'active' : '' ?>">
                <span class="adm-nav-icon">🌿</span> Produtos
            </a>
            <a href="/admin/banners" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/banners') ? 'active' : '' ?>">
                <span class="adm-nav-icon">🖼️</span> Banners
            </a>
            <a href="/admin/depoimentos" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/depoimentos') ? 'active' : '' ?>">
                <span class="adm-nav-icon">💬</span> Depoimentos
            </a>

            <div class="adm-nav-section">E-commerce</div>
            <a href="/admin/pedidos" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/pedidos') ? 'active' : '' ?>">
                <span class="adm-nav-icon">🛍️</span> Pedidos Online
            </a>
            <a href="/admin/clientes" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/clientes') ? 'active' : '' ?>">
                <span class="adm-nav-icon">👥</span> Clientes
            </a>
            <a href="/admin/webhook_logs" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/webhook_logs') ? 'active' : '' ?>">
                <span class="adm-nav-icon">🪝</span> Webhook Logs
            </a>

            <div class="adm-nav-section">Operação</div>
            <a href="/admin/insumos" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/insumos') ? 'active' : '' ?>">
                <span class="adm-nav-icon">🌾</span> Insumos
            </a>
            <a href="/admin/compras" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/compras') ? 'active' : '' ?>">
                <span class="adm-nav-icon">🛒</span> Compras
            </a>
            <a href="/admin/producao" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/producao') ? 'active' : '' ?>">
                <span class="adm-nav-icon">⚗️</span> Produção
            </a>
            <a href="/admin/vendas" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/vendas') ? 'active' : '' ?>">
                <span class="adm-nav-icon">💰</span> Vendas
            </a>
            <a href="/admin/estoque" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/estoque') ? 'active' : '' ?>">
                <span class="adm-nav-icon">📦</span> Estoque
            </a>

            <div class="adm-nav-section">Ferramentas</div>
            <a href="/admin/importacao" class="adm-nav-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/importacao') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/importacao/historico')) ? 'active' : '' ?>">
                <span class="adm-nav-icon">📥</span> Importar Dados
            </a>
            <a href="/admin/importacao/historico" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/importacao/historico') ? 'active' : '' ?>">
                <span class="adm-nav-icon">📋</span> Histórico
            </a>
            <a href="/admin/configuracoes" class="adm-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/configuracoes') ? 'active' : '' ?>">
                <span class="adm-nav-icon">⚙️</span> Configurações
            </a>
        </nav>

        <div class="adm-sidebar-footer">
            <div class="adm-user-info">
                <strong><?= htmlspecialchars($user['nome'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></strong>
                <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <a href="/admin/logout" class="adm-logout">⬅ Sair</a>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <div class="adm-main">
        <div class="adm-topbar">
            <div>
                <div class="adm-topbar-title"><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : 'Painel' ?></div>
                <?php if (isset($pageBreadcrumb)): ?>
                <div class="adm-topbar-breadcrumb"><?= $pageBreadcrumb ?></div>
                <?php endif; ?>
            </div>
            <div class="adm-topbar-actions">
                <a href="<?= APP_URL ?>" target="_blank" rel="noopener" class="adm-btn adm-btn-secondary adm-btn-sm">🌐 Ver site</a>
            </div>
        </div>

        <div class="adm-content">
            <?= $content ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/masks.js"></script>
<script>
// Sidebar mobile toggle
document.getElementById('adm-sidebar');
</script>
<?php if (isset($pageScripts)) echo $pageScripts; ?>
</body>
</html>
