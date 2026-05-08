<?php use App\Core\Helper; ?>

<!-- ===== HERO / BANNER ===== -->
<section class="hero" id="hero">
    <?php if (!empty($banners)):
          $b = $banners[0]; ?>
    <div class="banner-hero"<?= $b['imagem'] ? ' style="background-image:url(' . Helper::upload($b['imagem']) . ')"' : '' ?>>
        <div class="banner-overlay">
            <?php if ($b['titulo']): ?>
            <h1 class="banner-title"><?= htmlspecialchars($b['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>
            <?php endif; ?>
            <?php if ($b['subtitulo']): ?>
            <p class="banner-subtitle"><?= htmlspecialchars($b['subtitulo'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/produtos" class="btn btn-light">Conhecer produtos</a>
        </div>
    </div>
    <?php else: ?>
    <div class="hero-default">
        <div class="hero-default-content">
            <div class="hero-leaf-dec">✦</div>
            <h1>Natureza em cada detalhe</h1>
            <p>Produtos artesanais feitos com ervas, resinas e intenção — para o corpo, a mente e o espírito.</p>
            <a href="<?= APP_URL ?>/produtos" class="btn btn-primary">Ver produtos</a>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- ===== SOBRE RESUMO ===== -->
<section class="section-sobre-resumo">
    <div class="container sobre-resumo-grid">
        <div class="sobre-resumo-text">
            <span class="label-small">Nossa essência</span>
            <h2>Iraná Natural</h2>
            <p>Nascemos do amor pelas ervas, pela terra e pela espiritualidade consciente. Cada produto é elaborado com cuidado artesanal, respeitando os ciclos da natureza e honrando a sabedoria ancestral das plantas.</p>
            <a href="<?= APP_URL ?>/sobre" class="link-elegante">Conhecer nossa história →</a>
        </div>
        <div class="sobre-resumo-visual">
            <div class="botanical-card">
                <div class="botanical-icon">🌿</div>
                <h3>100% Natural</h3>
                <p>Ingredientes selecionados da natureza</p>
            </div>
            <div class="botanical-card">
                <div class="botanical-icon">✨</div>
                <h3>Feito à Mão</h3>
                <p>Produção artesanal com intenção</p>
            </div>
            <div class="botanical-card">
                <div class="botanical-icon">🌱</div>
                <h3>Com Propósito</h3>
                <p>Cuidado com o ser e o ambiente</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== CATEGORIAS ===== -->
<section class="section-categorias">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Linha de produtos</span>
            <h2>Nossas Categorias</h2>
        </div>
        <div class="categorias-grid">
            <?php foreach ($categorias as $cat): ?>
            <a href="<?= APP_URL ?>/produtos/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
               class="categoria-card">
                <?php if ($cat['imagem']): ?>
                <div class="categoria-img" style="background-image:url(<?= Helper::upload($cat['imagem']) ?>)"></div>
                <?php else: ?>
                <div class="categoria-img categoria-img-placeholder"></div>
                <?php endif; ?>
                <div class="categoria-info">
                    <h3><?= htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <span><?= $cat['total_produtos'] ?> produto<?= $cat['total_produtos'] != 1 ? 's' : '' ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== PRODUTOS EM DESTAQUE ===== -->
<?php if (!empty($destaques)): ?>
<section class="section-destaques">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Seleção especial</span>
            <h2>Produtos em Destaque</h2>
        </div>
        <div class="produtos-grid">
            <?php foreach ($destaques as $produto): ?>
            <div class="produto-card">
                <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>/<?= $produto['slug'] ?>" class="produto-card-img-wrap">
                    <?php if ($produto['imagem_principal']): ?>
                    <img src="<?= Helper::upload($produto['imagem_principal']) ?>"
                         alt="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='<?= APP_URL ?>/assets/images/placeholder.svg'">
                    <?php else: ?>
                    <div class="produto-img-placeholder">🌿</div>
                    <?php endif; ?>
                </a>
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
                               class="btn btn-outline btn-sm">Ver detalhes</a>
                            <a href="<?= Helper::whatsappProduct($produto['nome']) ?>"
                               target="_blank" rel="noopener"
                               class="btn btn-whatsapp btn-sm" aria-label="Comprar pelo WhatsApp">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-lg">
            <a href="<?= APP_URL ?>/produtos" class="btn btn-primary">Ver todos os produtos</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== DEPOIMENTOS ===== -->
<?php if (!empty($depoimentos)): ?>
<section class="section-depoimentos">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Quem nos escolhe</span>
            <h2>Depoimentos</h2>
        </div>
        <div class="depoimentos-grid">
            <?php foreach ($depoimentos as $dep): ?>
            <div class="depoimento-card">
                <div class="depoimento-estrelas">
                    <?php for ($s = 0; $s < (int)$dep['avaliacao']; $s++): ?>★<?php endfor; ?>
                </div>
                <p class="depoimento-texto">"<?= htmlspecialchars($dep['texto'], ENT_QUOTES, 'UTF-8') ?>"</p>
                <div class="depoimento-autor">
                    <?php if ($dep['foto']): ?>
                    <img src="<?= Helper::upload($dep['foto']) ?>" alt="<?= htmlspecialchars($dep['nome'], ENT_QUOTES, 'UTF-8') ?>" class="depoimento-foto">
                    <?php else: ?>
                    <div class="depoimento-avatar"><?= mb_substr($dep['nome'], 0, 1) ?></div>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($dep['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== CTA FINAL ===== -->
<section class="section-cta">
    <div class="container">
        <div class="cta-box">
            <div class="cta-leaf">🌿</div>
            <h2>Pronta para sua transformação?</h2>
            <p>Entre em contato pelo WhatsApp e descubra o produto perfeito para o seu momento.</p>
            <a href="<?= Helper::whatsapp() ?>" target="_blank" rel="noopener" class="btn btn-primary btn-lg">
                Falar pelo WhatsApp
            </a>
        </div>
    </div>
</section>
