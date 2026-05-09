<?php
use App\Core\Helper;
$pageTitle   = $produto ? 'Editar Produto' : 'Novo Produto';
$pageStyles  = '<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">';
$pageScripts = '<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>';
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;align-items:center">
    <a href="/admin/produtos" class="adm-btn adm-btn-secondary adm-btn-sm">← Produtos</a>
    <?php if ($produto): ?>
    <a href="/admin/produtos/<?= $produto['id'] ?>/ficha" class="adm-btn adm-btn-warning adm-btn-sm">🧪 Ficha Técnica</a>
    <?php if ($produto['ativo']): ?>
    <a href="<?= APP_URL ?>/produtos/<?= $produto['categoria_slug'] ?>/<?= $produto['slug'] ?>" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm">🌐 Ver no Site</a>
    <?php endif; ?>
    <?php if (!$produto['ativo']): ?>
    <span style="background:#FFF3CD;border:1px solid #FFEAA7;padding:0.3rem 0.75rem;border-radius:4px;font-size:0.85rem">⚠️ Produto em rascunho — invisível no site</span>
    <?php endif; ?>
    <?php endif; ?>
</div>

<form method="POST" action="/admin/produtos/<?= $produto ? $produto['id'] . '/editar' : 'novo' ?>" enctype="multipart/form-data" id="produto-form">

<!-- ═══════════════════════════════════════════════════════
     SEÇÃO 1 — IDENTIDADE
═══════════════════════════════════════════════════════ -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <div class="adm-card-header">
        <span class="adm-card-title">1. Identidade do Produto</span>
    </div>
    <div class="adm-card-body">
        <div class="adm-form-grid adm-form-grid-2">
            <div class="adm-form-group">
                <label>Nome do Produto *</label>
                <input type="text" name="nome" id="nome" required
                       value="<?= htmlspecialchars($produto['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       oninput="onNomeInput()">
            </div>
            <div class="adm-form-group">
                <label>Categoria *</label>
                <select name="categoria_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($produto['categoria_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="adm-form-group">
            <label>
                Descrição Curta
                <span style="float:right;font-size:0.8rem;color:#718096" id="desc-curta-counter">0/150</span>
            </label>
            <textarea name="descricao_curta" id="descricao_curta" rows="2" maxlength="150"
                      placeholder="Frase curta exibida na listagem e nas redes sociais (até 150 caracteres)"
                      oninput="updateCounter('descricao_curta','desc-curta-counter',150)"
            ><?= htmlspecialchars($produto['descricao_curta'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Tags / chip input -->
        <div class="adm-form-group">
            <label>Tags</label>
            <input type="hidden" name="tags" id="tags-value" value="<?= htmlspecialchars($produto['tags'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div id="tag-chips-container" style="
                display:flex;flex-wrap:wrap;gap:0.35rem;align-items:center;
                border:1px solid #E2E8F0;border-radius:4px;padding:0.45rem 0.6rem;
                background:#fff;cursor:text;min-height:40px"
                 onclick="document.getElementById('tag-input').focus()">
                <!-- chips inserted by JS -->
                <input type="text" id="tag-input"
                       style="border:none;outline:none;flex:1;min-width:120px;font-size:0.95rem;padding:0"
                       placeholder="Escreva e pressione Enter ou vírgula">
            </div>
            <div class="adm-hint">Pressione Enter ou vírgula para adicionar. Backspace remove a última.</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SEÇÃO 2 — CONTEÚDO
═══════════════════════════════════════════════════════ -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <div class="adm-card-header">
        <span class="adm-card-title">2. Conteúdo do Produto</span>
    </div>
    <div class="adm-card-body">
        <div class="adm-form-group">
            <label>Composição (ingredientes / ervas)</label>
            <textarea name="composicao" rows="3"
                      placeholder="Ex: Lavanda, Alecrim, Rosa, Sálvia..."
            ><?= htmlspecialchars($produto['composicao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="adm-form-group">
            <label>Descrição Completa</label>
            <textarea name="descricao_completa" id="descricao_completa"
            ><?= htmlspecialchars($produto['descricao_completa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="adm-hint">Suporta formatação Markdown: **negrito**, *itálico*, - listas.</div>
        </div>

        <div class="adm-form-grid adm-form-grid-2">
            <div class="adm-form-group">
                <label>Modo de Uso</label>
                <textarea name="modo_uso" rows="4"
                          placeholder="Instruções de como utilizar o produto..."
                ><?= htmlspecialchars($produto['modo_uso'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="adm-form-group">
                <label>Cuidados e Avisos</label>
                <textarea name="cuidados" rows="4"
                          placeholder="Contraindicações, cuidados de armazenamento..."
                ><?= htmlspecialchars($produto['cuidados'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SEÇÃO 3 — SEO
═══════════════════════════════════════════════════════ -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <div class="adm-card-header">
        <span class="adm-card-title">3. SEO (Busca Orgânica)</span>
    </div>
    <div class="adm-card-body">
        <div class="adm-form-grid adm-form-grid-2">
            <div>
                <div class="adm-form-group">
                    <label>
                        Título SEO
                        <span style="float:right;font-size:0.8rem;color:#718096" id="seo-titulo-counter">0/70</span>
                    </label>
                    <input type="text" name="seo_titulo" id="seo_titulo" maxlength="70"
                           placeholder="Deixe vazio para usar o nome do produto"
                           value="<?= htmlspecialchars($produto['seo_titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           oninput="updateCounter('seo_titulo','seo-titulo-counter',70);updateSeoPreview()">
                </div>
                <div class="adm-form-group">
                    <label>
                        Meta Descrição
                        <span style="float:right;font-size:0.8rem;color:#718096" id="seo-desc-counter">0/160</span>
                    </label>
                    <textarea name="seo_descricao" id="seo_descricao" rows="3" maxlength="160"
                              placeholder="Deixe vazio para usar a descrição curta"
                              oninput="updateCounter('seo_descricao','seo-desc-counter',160);updateSeoPreview()"
                    ><?= htmlspecialchars($produto['seo_descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <!-- Google Preview -->
            <div>
                <label style="display:block;margin-bottom:0.5rem">Pré-visualização no Google</label>
                <div style="border:1px solid #E2E8F0;border-radius:6px;padding:1rem;background:#fff;font-family:Arial,sans-serif">
                    <div style="font-size:0.72rem;color:#006621;margin-bottom:2px">
                        <?= APP_URL ?>/produtos/…/<span style="color:#006621"><?= htmlspecialchars($produto['slug'] ?? 'nome-do-produto', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div id="seo-preview-title" style="font-size:1.1rem;color:#1a0dab;font-weight:400;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= htmlspecialchars($produto['seo_titulo'] ?: ($produto['nome'] ?? 'Título do produto'), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div id="seo-preview-desc" style="font-size:0.85rem;color:#545454;margin-top:4px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                        <?= htmlspecialchars($produto['seo_descricao'] ?: ($produto['descricao_curta'] ?? 'Descrição do produto...'), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
                <div class="adm-hint" style="margin-top:0.5rem">A pré-visualização é indicativa. Resultados reais variam.</div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SEÇÃO 4 — PRECIFICAÇÃO E ESTOQUE
═══════════════════════════════════════════════════════ -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <div class="adm-card-header">
        <span class="adm-card-title">4. Precificação e Estoque</span>
    </div>
    <div class="adm-card-body">
        <div class="adm-form-grid adm-form-grid-2">
            <div class="adm-form-group">
                <label>Preço de Venda (R$) *</label>
                <input type="number" name="preco_venda" step="0.01" min="0" required
                       value="<?= $produto['preco_venda'] ?? '0.00' ?>">
            </div>
            <div class="adm-form-group">
                <label>Margem Desejada (%)</label>
                <input type="number" name="margem_desejada" step="0.01" min="0"
                       value="<?= $produto['margem_desejada'] ?? '0' ?>">
            </div>
        </div>

        <?php if ($produto): ?>
        <?php
        $margemOk = (float)$produto['margem_real'] >= (float)$produto['margem_desejada'];
        ?>
        <div class="adm-alert adm-alert-<?= $margemOk ? 'success' : 'error' ?>" style="margin-top:0">
            <strong>Custo calculado:</strong> <?= Helper::money((float)$produto['custo_calculado']) ?> &nbsp;|&nbsp;
            <strong>Lucro:</strong> <?= Helper::money((float)$produto['lucro_calculado']) ?> &nbsp;|&nbsp;
            <strong>Margem real:</strong> <?= number_format($produto['margem_real'], 2, ',', '.') ?>%
            <?php if (!$margemOk): ?>
            <br><span>⚠️ Margem real abaixo da desejada (<?= number_format($produto['margem_desejada'], 2, ',', '.') ?>%)</span>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="adm-hint">O custo será calculado automaticamente após definir a ficha técnica do produto.</div>
        <?php endif; ?>

        <div class="adm-form-grid adm-form-grid-2" style="margin-top:1rem">
            <div class="adm-form-group">
                <label>Estoque Mínimo (un)</label>
                <input type="number" name="estoque_minimo" min="0"
                       value="<?= $produto['estoque_minimo'] ?? 0 ?>">
            </div>
            <div class="adm-form-group">
                <label>Estoque Atual</label>
                <input type="text" value="<?= $produto['estoque_atual'] ?? 0 ?> un" readonly
                       style="background:#F8F9FA;color:#718096">
                <div class="adm-hint">Ajustado via <a href="/admin/producao/nova">Produção</a> ou <a href="/admin/estoque">Estoque</a>.</div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SEÇÃO 5 — LOGÍSTICA (Dimensões para cálculo de frete)
═══════════════════════════════════════════════════════ -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <div class="adm-card-header">
        <span class="adm-card-title">5. Logística</span>
    </div>
    <div class="adm-card-body">
        <div class="adm-hint" style="margin-bottom:1rem">Dimensões usadas para calcular o frete via Melhor Envio. Informe as medidas da embalagem pronta para envio.</div>
        <div class="adm-form-grid adm-form-grid-2">
            <div class="adm-form-group">
                <label>Peso (kg)</label>
                <input type="number" name="peso" step="0.001" min="0.001"
                       value="<?= number_format((float)($produto['peso'] ?? 0.1), 3, '.', '') ?>"
                       placeholder="0.300">
                <div class="adm-hint">Ex: 0.300 = 300g</div>
            </div>
            <div class="adm-form-group">
                <label>Altura (cm)</label>
                <input type="number" name="altura" min="1"
                       value="<?= (int)($produto['altura'] ?? 10) ?>"
                       placeholder="10">
            </div>
            <div class="adm-form-group">
                <label>Largura (cm)</label>
                <input type="number" name="largura" min="1"
                       value="<?= (int)($produto['largura'] ?? 10) ?>"
                       placeholder="10">
            </div>
            <div class="adm-form-group">
                <label>Comprimento (cm)</label>
                <input type="number" name="comprimento" min="1"
                       value="<?= (int)($produto['comprimento'] ?? 15) ?>"
                       placeholder="15">
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SEÇÃO 6 — MÍDIA E PUBLICAÇÃO
═══════════════════════════════════════════════════════ -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <div class="adm-card-header">
        <span class="adm-card-title">6. Mídia e Publicação</span>
    </div>
    <div class="adm-card-body">

        <!-- Upload de novas imagens -->
        <div class="adm-form-group">
            <label>Adicionar imagens</label>
            <input type="file" name="imagens[]" multiple accept="image/jpeg,image/png,image/webp">
            <div class="adm-hint">JPG/PNG/WEBP, máx 5 MB cada. A primeira enviada torna-se principal se não houver nenhuma.</div>
        </div>

        <!-- Galeria existente -->
        <?php if (!empty($imagens)): ?>
        <div style="margin-top:1rem">
            <label style="display:block;margin-bottom:0.5rem">Galeria (<?= count($imagens) ?> imagem<?= count($imagens) !== 1 ? 'ns' : '' ?>)</label>
            <div style="display:flex;flex-direction:column;gap:0.5rem" id="img-gallery">
            <?php foreach ($imagens as $i => $img): ?>
            <div style="display:flex;align-items:center;gap:0.75rem;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:0.5rem 0.75rem">
                <!-- Thumbnail -->
                <img src="<?= Helper::upload($img['caminho']) ?>" alt=""
                     style="height:56px;width:56px;object-fit:cover;border-radius:4px;border:<?= $img['principal'] ? '2px solid #2C5F2E' : '1px solid #E2E8F0' ?>">

                <!-- Info -->
                <div style="flex:1;min-width:0">
                    <?php if ($img['principal']): ?>
                    <span style="background:#2C5F2E;color:#fff;font-size:0.72rem;padding:1px 6px;border-radius:3px;font-weight:700">PRINCIPAL</span>
                    <?php else: ?>
                    <span style="background:#E2E8F0;color:#4A5568;font-size:0.72rem;padding:1px 6px;border-radius:3px">#<?= $i + 1 ?></span>
                    <?php endif; ?>
                    <div style="font-size:0.78rem;color:#718096;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= htmlspecialchars(basename($img['caminho']), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display:flex;gap:0.35rem;flex-shrink:0;flex-wrap:wrap">
                    <?php if (!$img['principal']): ?>
                    <form method="POST" action="/admin/produtos/<?= $produto['id'] ?>/imagem-principal/<?= $img['id'] ?>" style="display:inline">
                        <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm" title="Definir como principal">★ Principal</button>
                    </form>
                    <?php endif; ?>

                    <?php if ($i > 0): ?>
                    <form method="POST" action="/admin/produtos/<?= $produto['id'] ?>/imagem-mover/<?= $img['id'] ?>" style="display:inline">
                        <input type="hidden" name="direction" value="up">
                        <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm" title="Mover para cima">↑</button>
                    </form>
                    <?php endif; ?>

                    <?php if ($i < count($imagens) - 1): ?>
                    <form method="POST" action="/admin/produtos/<?= $produto['id'] ?>/imagem-mover/<?= $img['id'] ?>" style="display:inline">
                        <input type="hidden" name="direction" value="down">
                        <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm" title="Mover para baixo">↓</button>
                    </form>
                    <?php endif; ?>

                    <form method="POST" action="/admin/produtos/<?= $produto['id'] ?>/imagem-excluir/<?= $img['id'] ?>" style="display:inline"
                          onsubmit="return confirm('Excluir esta imagem permanentemente?')">
                        <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" title="Excluir imagem">✕</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Publicação -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #E2E8F0">
            <div class="adm-form-group" style="margin-bottom:0">
                <label class="adm-switch">
                    <input type="checkbox" name="ativo" id="ativo" <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>>
                    <span id="status-label"><?= ($produto['ativo'] ?? 1) ? 'Publicado — visível no site' : 'Rascunho — invisível no site' ?></span>
                </label>
            </div>
            <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg">💾 Salvar Produto</button>
        </div>
    </div>
</div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Character counters ──────────────────────────────────────
    function updateCounter(fieldId, counterId, max) {
        var el = document.getElementById(fieldId);
        var counter = document.getElementById(counterId);
        if (!el || !counter) return;
        function refresh() {
            var len = el.value.length;
            counter.textContent = len + '/' + max;
            counter.style.color = len >= max ? '#E53E3E' : len >= max * 0.9 ? '#D69E2E' : '#718096';
        }
        el.addEventListener('input', refresh);
        refresh();
    }
    window.updateCounter = updateCounter;
    updateCounter('descricao_curta', 'desc-curta-counter', 150);
    updateCounter('seo_titulo',      'seo-titulo-counter', 70);
    updateCounter('seo_descricao',   'seo-desc-counter',   160);

    // ── SEO preview ─────────────────────────────────────────────
    function updateSeoPreview() {
        var nome   = document.getElementById('nome').value;
        var dcurta = document.getElementById('descricao_curta').value;
        var title  = (document.getElementById('seo_titulo').value  || nome   || 'Título do produto').substring(0, 70);
        var desc   = (document.getElementById('seo_descricao').value || dcurta || 'Descrição do produto...').substring(0, 160);
        document.getElementById('seo-preview-title').textContent = title;
        document.getElementById('seo-preview-desc').textContent  = desc;
    }
    window.updateSeoPreview = updateSeoPreview;
    window.onNomeInput = function() { updateSeoPreview(); };

    // ── Tag chip input ──────────────────────────────────────────
    var hidden    = document.getElementById('tags-value');
    var tagInput  = document.getElementById('tag-input');
    var container = document.getElementById('tag-chips-container');
    var tags      = hidden && hidden.value
                    ? hidden.value.split(',').map(function(t){ return t.trim(); }).filter(Boolean)
                    : [];

    function renderTags() {
        container.querySelectorAll('.pf-tag-chip').forEach(function(el){ el.remove(); });
        tags.forEach(function(tag, i) {
            var chip = document.createElement('span');
            chip.className = 'pf-tag-chip';
            chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;background:#EBF8EE;color:#276749;border:1px solid #C6F6D5;border-radius:999px;padding:2px 10px;font-size:0.82rem;white-space:nowrap';
            chip.textContent = tag + ' ';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = '×';
            btn.style.cssText = 'background:none;border:none;cursor:pointer;color:#276749;font-size:1rem;padding:0;line-height:1';
            btn.onclick = (function(idx){ return function(){ tags.splice(idx,1); renderTags(); }; })(i);
            chip.appendChild(btn);
            container.insertBefore(chip, tagInput);
        });
        if (hidden) hidden.value = tags.join(',');
    }

    if (tagInput) {
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                var val = tagInput.value.trim().replace(/,/g, '');
                if (val && tags.indexOf(val) === -1) { tags.push(val); renderTags(); }
                tagInput.value = '';
            } else if (e.key === 'Backspace' && tagInput.value === '' && tags.length) {
                tags.pop();
                renderTags();
            }
        });
    }
    renderTags();

    // ── EasyMDE for descricao_completa ──────────────────────────
    var mdArea = document.getElementById('descricao_completa');
    if (mdArea && typeof EasyMDE !== 'undefined') {
        new EasyMDE({
            element: mdArea,
            spellChecker: false,
            status: ['chars', 'words'],
            toolbar: [
                'bold', 'italic', '|',
                'unordered-list', '|',
                'preview', 'guide'
            ],
            minHeight: '200px',
            placeholder: 'Escreva a descrição completa do produto...\nSuporta **negrito**, *itálico*, - listas.'
        });
    }

    // ── Publication toggle label ─────────────────────────────────
    var ativoChk  = document.getElementById('ativo');
    var statusLbl = document.getElementById('status-label');
    if (ativoChk && statusLbl) {
        ativoChk.addEventListener('change', function() {
            statusLbl.textContent = ativoChk.checked
                ? 'Publicado — visível no site'
                : 'Rascunho — invisível no site';
        });
    }
});
</script>
