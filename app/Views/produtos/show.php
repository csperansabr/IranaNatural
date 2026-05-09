<?php use App\Core\Helper; ?>

<!-- Breadcrumb -->
<section class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/produtos">Produtos</a>
            <span>›</span>
            <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>"><?= htmlspecialchars($produto['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></a>
            <span>›</span>
            <span><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
    </div>
</section>

<!-- Produto Detail -->
<section class="section-produto-detalhe">
    <div class="container produto-detalhe-grid">

        <!-- Galeria -->
        <div class="produto-galeria" id="galeria-wrapper">
            <?php if (!empty($imagens)):
                  $multiImg = count($imagens) > 1; ?>
            <div class="galeria-main" id="galeria-main">
                <img src="<?= Helper::upload($imagens[0]['caminho']) ?>"
                     alt="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                     id="galeria-img-principal"
                     onerror="this.onerror=null;this.src='<?= APP_URL ?>/assets/images/placeholder.svg'">
                <?php if ($multiImg): ?>
                <button class="galeria-prev" id="galeria-prev" aria-label="Imagem anterior">&#8249;</button>
                <button class="galeria-next" id="galeria-next" aria-label="Próxima imagem">&#8250;</button>
                <?php endif; ?>
            </div>
            <?php if ($multiImg): ?>
            <div class="galeria-dots" id="galeria-dots" role="tablist" aria-label="Imagens do produto">
                <?php foreach ($imagens as $i => $img): ?>
                <button class="galeria-dot <?= $i === 0 ? 'active' : '' ?>"
                        data-index="<?= $i ?>"
                        role="tab"
                        aria-label="Imagem <?= $i + 1 ?>"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="galeria-thumbs">
                <?php foreach ($imagens as $i => $img): ?>
                <button class="galeria-thumb <?= $i === 0 ? 'active' : '' ?>"
                        data-src="<?= Helper::upload($img['caminho']) ?>"
                        aria-label="Ver imagem <?= $i + 1 ?>">
                    <img src="<?= Helper::upload($img['caminho']) ?>"
                         alt="Imagem <?= $i + 1 ?> de <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='<?= APP_URL ?>/assets/images/placeholder.svg'">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="galeria-placeholder">🌿</div>
            <?php endif; ?>
        </div>

        <!-- Informações -->
        <div class="produto-info">
            <span class="produto-categoria-tag">
                <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>"><?= htmlspecialchars($produto['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></a>
            </span>
            <h1 class="produto-titulo"><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></h1>

            <?php if ($produto['descricao_curta']): ?>
            <p class="produto-resumo"><?= htmlspecialchars($produto['descricao_curta'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php
            // Tags
            $tagsList = array_filter(array_map('trim', explode(',', $produto['tags'] ?? '')));
            if (!empty($tagsList)):
            ?>
            <div class="produto-tags" style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-bottom:1rem">
                <?php foreach ($tagsList as $tag): ?>
                <span style="display:inline-block;background:#EBF8EE;color:#276749;border:1px solid #C6F6D5;border-radius:999px;padding:2px 12px;font-size:0.8rem">
                    <?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="produto-preco-grande"><?= Helper::money((float)$produto['preco_venda']) ?></div>

            <div class="produto-cta-buttons">
                <?php if ($produto['estoque_atual'] > 0): ?>
                <form class="form-add-carrinho" action="<?= APP_URL ?>/carrinho/adicionar" method="POST" style="display:flex;align-items:center;gap:0.75rem;">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">
                    <input type="hidden" name="produto_id" value="<?= (int)$produto['id'] ?>">
                    <div class="carrinho-item__qty">
                        <button type="button" class="qty-btn qty-btn--minus" onclick="adjustQty(this,-1)" aria-label="Diminuir">−</button>
                        <input type="number" name="quantidade" class="qty-input" value="1" min="1" max="<?= (int)$produto['estoque_atual'] ?>" id="qty-produto">
                        <button type="button" class="qty-btn qty-btn--plus" onclick="adjustQty(this,1)" data-estoque="<?= (int)$produto['estoque_atual'] ?>" aria-label="Aumentar">+</button>
                    </div>
                    <button type="submit" class="btn-carrinho">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        Adicionar ao carrinho
                    </button>
                </form>
                <script>
                function adjustQty(btn, delta) {
                    const input = document.getElementById('qty-produto');
                    if (!input) return;
                    const max = parseInt(btn.dataset.estoque || btn.closest('.carrinho-item__qty').querySelector('[data-estoque]')?.dataset.estoque || 9999, 10);
                    input.value = Math.max(1, Math.min(parseInt(input.value, 10) + delta, max));
                }
                </script>
                <?php else: ?>
                <p class="produto-sem-estoque">Produto temporariamente indisponível</p>
                <?php endif; ?>

                <a href="<?= Helper::whatsappProduct($produto['nome']) ?>"
                   target="_blank" rel="noopener"
                   class="btn btn-light btn-lg">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20" style="margin-right:8px;vertical-align:middle"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Dúvidas? WhatsApp
                </a>
            </div>

            <!-- Abas de informação -->
            <?php
            $hasTabs = $produto['descricao_completa'] || $produto['composicao'] || $produto['modo_uso'] || $produto['cuidados'];
            ?>
            <?php if ($hasTabs): ?>
            <div class="produto-tabs">
                <div class="tabs-header">
                    <?php if ($produto['descricao_completa']): ?>
                    <button class="tab-btn active" data-tab="descricao">Descrição</button>
                    <?php endif; ?>
                    <?php if ($produto['composicao']): ?>
                    <button class="tab-btn <?= !$produto['descricao_completa'] ? 'active' : '' ?>" data-tab="composicao">Composição</button>
                    <?php endif; ?>
                    <?php if ($produto['modo_uso']): ?>
                    <button class="tab-btn" data-tab="modo-uso">Modo de Uso</button>
                    <?php endif; ?>
                    <?php if ($produto['cuidados']): ?>
                    <button class="tab-btn" data-tab="cuidados">Cuidados</button>
                    <?php endif; ?>
                </div>

                <?php if ($produto['descricao_completa']): ?>
                <div class="tab-content active" id="tab-descricao">
                    <div class="produto-rich-text"><?= Helper::md($produto['descricao_completa']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($produto['composicao']): ?>
                <div class="tab-content <?= !$produto['descricao_completa'] ? 'active' : '' ?>" id="tab-composicao">
                    <p><?= nl2br(htmlspecialchars($produto['composicao'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($produto['modo_uso']): ?>
                <div class="tab-content" id="tab-modo-uso">
                    <p><?= nl2br(htmlspecialchars($produto['modo_uso'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($produto['cuidados']): ?>
                <div class="tab-content" id="tab-cuidados">
                    <p><?= nl2br(htmlspecialchars($produto['cuidados'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Produtos relacionados -->
<?php if (!empty($relacionados)): ?>
<section class="section-relacionados">
    <div class="container">
        <h2 class="section-title-sm">Você também pode gostar</h2>
        <div class="produtos-grid produtos-grid-sm">
            <?php foreach ($relacionados as $rel): ?>
            <div class="produto-card">
                <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>/<?= $rel['slug'] ?>" class="produto-card-img-wrap">
                    <?php if (!empty($rel['imagem_principal'])): ?>
                    <img src="<?= Helper::upload($rel['imagem_principal']) ?>"
                         alt="<?= htmlspecialchars($rel['nome'], ENT_QUOTES, 'UTF-8') ?>"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='<?= APP_URL ?>/assets/images/placeholder.svg'">
                    <?php else: ?>
                    <div class="produto-img-placeholder">🌿</div>
                    <?php endif; ?>
                </a>
                <div class="produto-card-body">
                    <h3 class="produto-nome">
                        <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>/<?= $rel['slug'] ?>">
                            <?= htmlspecialchars($rel['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h3>
                    <div class="produto-card-footer">
                        <span class="produto-preco"><?= Helper::money((float)$rel['preco_venda']) ?></span>
                        <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>/<?= $rel['slug'] ?>" class="btn btn-outline btn-sm">Ver</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
