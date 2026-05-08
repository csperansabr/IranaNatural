<?php use App\Core\Helper; ?>

<!-- Breadcrumb -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/produtos">Produtos</a>
            <?php if (!empty($cat)): ?>
            <span>›</span>
            <span><?= htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </nav>
        <h1><?= !empty($cat) ? htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') : 'Todos os Produtos' ?></h1>
        <?php if (!empty($cat) && $cat['descricao']): ?>
        <p class="page-hero-desc"><?= htmlspecialchars($cat['descricao'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section-produtos-lista">
    <div class="container produtos-layout">

        <!-- Sidebar categorias -->
        <aside class="produtos-sidebar">
            <h3>Categorias</h3>
            <ul class="sidebar-cats">
                <li>
                    <a href="<?= APP_URL ?>/produtos"
                       class="<?= empty($cat) ? 'active' : '' ?>">
                        Todos os produtos
                    </a>
                </li>
                <?php foreach ($categorias as $c): ?>
                <li>
                    <a href="<?= APP_URL ?>/produtos/<?= $c['slug'] ?>"
                       class="<?= (!empty($cat) && $cat['slug'] === $c['slug']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($c['nome'], ENT_QUOTES, 'UTF-8') ?>
                        <span class="sidebar-count"><?= $c['total_produtos'] ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Grid de produtos -->
        <div class="produtos-main">
            <?php if (empty($produtos)): ?>
            <div class="produtos-vazio">
                <div class="vazio-icon">🌿</div>
                <p>Nenhum produto encontrado nesta categoria no momento.</p>
                <a href="<?= APP_URL ?>/produtos" class="btn btn-outline">Ver todos os produtos</a>
            </div>
            <?php else: ?>
            <div class="produtos-count">
                <?= count($produtos) ?> produto<?= count($produtos) != 1 ? 's' : '' ?> encontrado<?= count($produtos) != 1 ? 's' : '' ?>
            </div>
            <div class="produtos-grid">
                <?php foreach ($produtos as $produto): ?>
                <?php
                // Monta array de URLs para galeria do card
                $cardImagens = [];
                if (!empty($produto['todas_imagens'])) {
                    foreach (explode('|', $produto['todas_imagens']) as $p) {
                        $cardImagens[] = Helper::upload($p);
                    }
                } elseif (!empty($produto['imagem_principal'])) {
                    $cardImagens[] = Helper::upload($produto['imagem_principal']);
                }
                $cardHref = APP_URL . '/produtos/' . $produto['categoria_slug'] . '/' . $produto['slug'];
                ?>
                <div class="produto-card">
                    <div class="card-gallery"
                         <?= count($cardImagens) > 1 ? 'data-images=\'' . htmlspecialchars(json_encode($cardImagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') . '\'' : '' ?>>
                        <a href="<?= $cardHref ?>" class="produto-card-img-wrap">
                            <?php if (!empty($cardImagens)): ?>
                            <img src="<?= $cardImagens[0] ?>"
                                 alt="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='<?= APP_URL ?>/assets/images/placeholder.svg'">
                            <?php else: ?>
                            <div class="produto-img-placeholder">🌿</div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="produto-card-body">
                        <span class="produto-categoria"><?= htmlspecialchars($produto['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3 class="produto-nome">
                            <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>/<?= $produto['slug'] ?>">
                                <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h3>
                        <?php if ($produto['descricao_curta']): ?>
                        <p class="produto-desc"><?= htmlspecialchars(Helper::excerpt($produto['descricao_curta'], 90), ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <div class="produto-card-footer">
                            <span class="produto-preco"><?= Helper::money((float)$produto['preco_venda']) ?></span>
                            <div class="produto-actions">
                                <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>/<?= $produto['slug'] ?>"
                                   class="btn btn-outline btn-sm">Detalhes</a>
                                <a href="<?= Helper::whatsappProduct($produto['nome']) ?>"
                                   target="_blank" rel="noopener"
                                   class="btn btn-whatsapp btn-sm" title="Comprar pelo WhatsApp">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
