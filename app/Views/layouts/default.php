<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($meta['title'] ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? APP_SLOGAN, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($meta['url'] ?? APP_URL, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="<?= htmlspecialchars($meta['og_type'] ?? 'website', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($meta['title'] ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta['description'] ?? APP_SLOGAN, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($meta['url'] ?? APP_URL, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($meta['image'] ?? APP_URL . '/assets/images/og-default.jpg', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name"   content="<?= APP_NAME ?>">
    <meta property="og:locale"      content="pt_BR">

    <?php if (!empty($meta['schema'])): ?>
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json"><?= $meta['schema'] ?></script>
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/images/favicon.png">
</head>
<body>

<?php
$_navPath = '/' . trim($_GET['url'] ?? '', '/');
$_active  = function(string $seg) use ($_navPath): string {
    if ($seg === '/') return $_navPath === '/' ? ' active' : '';
    return str_starts_with($_navPath, $seg) ? ' active' : '';
};
?>
<!-- ===== NAVEGAÇÃO ===== -->
<header class="site-header" id="site-header">
    <nav class="navbar container">
        <a href="<?= APP_URL ?>" class="logo" aria-label="<?= APP_NAME ?> — Início">
            <img src="<?= APP_URL ?>/assets/images/logo.png" alt="<?= APP_NAME ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <span class="logo-text" style="display:none"><?= APP_NAME ?></span>
        </a>

        <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-menu" id="nav-menu">
            <li><a href="<?= APP_URL ?>/" class="nav-link<?= $_active('/') ?>">Início</a></li>
            <li><a href="<?= APP_URL ?>/produtos" class="nav-link<?= $_active('/produtos') ?>">Produtos</a></li>
            <li><a href="<?= APP_URL ?>/sobre" class="nav-link<?= $_active('/sobre') ?>">Sobre</a></li>
            <li><a href="<?= APP_URL ?>/como-comprar" class="nav-link<?= $_active('/como-comprar') ?>">Como Comprar</a></li>
            <li><a href="<?= APP_URL ?>/contato" class="nav-link<?= $_active('/contato') ?>">Contato</a></li>
        </ul>

        <div class="nav-actions">
            <!-- Ícone carrinho -->
            <a href="<?= APP_URL ?>/carrinho" class="nav-carrinho<?= str_starts_with($_navPath, '/carrinho') || str_starts_with($_navPath, '/checkout') ? ' active' : '' ?>" aria-label="Carrinho de compras" id="nav-carrinho-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="22" height="22">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span class="nav-carrinho__badge" id="carrinho-badge" style="display:none">0</span>
            </a>

            <!-- Conta do cliente -->
            <?php if (\App\Core\Session::has('cliente_id')): ?>
            <div class="nav-conta nav-conta--logado" id="nav-conta">
                <button class="nav-conta__btn" aria-label="Minha conta" id="conta-toggle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="22" height="22"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="nav-conta__nome"><?= htmlspecialchars(explode(' ', \App\Core\Session::get('cliente_nome', ''))[0], ENT_QUOTES, 'UTF-8') ?></span>
                </button>
                <div class="nav-conta__dropdown" id="conta-dropdown">
                    <a href="<?= APP_URL ?>/minha-conta">Minha Conta</a>
                    <a href="<?= APP_URL ?>/minha-conta/logout">Sair</a>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= APP_URL ?>/minha-conta/login" class="nav-conta__link" aria-label="Entrar na conta">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="22" height="22"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- ===== CONTEÚDO ===== -->
<main>
    <?= $content ?>
</main>

<!-- ===== RODAPÉ ===== -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <img src="<?= APP_URL ?>/assets/images/logo.png" alt="<?= APP_NAME ?>" class="footer-logo" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <span class="footer-logo-text" style="display:none"><?= APP_NAME ?></span>
            <p class="footer-slogan"><?= APP_SLOGAN ?></p>
            <p class="footer-desc">Produtos naturais artesanais feitos com amor, intenção e respeito pela natureza.</p>
        </div>

        <div class="footer-links">
            <h4>Navegação</h4>
            <ul>
                <li><a href="<?= APP_URL ?>/">Início</a></li>
                <li><a href="<?= APP_URL ?>/produtos">Produtos</a></li>
                <li><a href="<?= APP_URL ?>/sobre">Sobre</a></li>
                <li><a href="<?= APP_URL ?>/como-comprar">Como Comprar</a></li>
                <li><a href="<?= APP_URL ?>/contato">Contato</a></li>
            </ul>
        </div>

        <div class="footer-info">
            <h4>Informações</h4>
            <ul>
                <li><a href="<?= APP_URL ?>/pagamento">Pagamento</a></li>
                <li><a href="<?= APP_URL ?>/envio">Envio e Entrega</a></li>
                <li><a href="<?= APP_URL ?>/garantia">Garantia</a></li>
                <li><a href="<?= APP_URL ?>/trocas">Trocas e Devoluções</a></li>
                <li><a href="<?= APP_URL ?>/politica-privacidade">Privacidade e Segurança</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h4>Contato</h4>
            <ul>
                <li><a href="https://wa.me/<?= WHATSAPP ?>" target="_blank" rel="noopener">WhatsApp</a></li>
                <li><a href="<?= INSTAGRAM_URL ?>" target="_blank" rel="noopener">Instagram</a></li>
                <li><a href="mailto:<?= EMAIL_CONTATO ?>"><?= EMAIL_CONTATO ?></a></li>
                <li class="footer-hours">Seg–Sex: 9h–18h<br>Sáb: 9h–13h</li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos os direitos reservados. &middot; <a href="<?= APP_URL ?>/politica-privacidade">Política de Privacidade</a></p>
        </div>
    </div>
</footer>

<!-- ===== BOTÃO WHATSAPP FLUTUANTE ===== -->
<a href="<?= \App\Core\Helper::whatsapp() ?>"
   class="whatsapp-float"
   target="_blank" rel="noopener"
   aria-label="Chamar no WhatsApp">
    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
</a>

<script src="<?= APP_URL ?>/assets/js/masks.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
