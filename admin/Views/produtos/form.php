<?php use App\Core\Helper;
$pageTitle = $produto ? 'Editar Produto' : 'Novo Produto'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="/admin/produtos" class="adm-btn adm-btn-secondary adm-btn-sm">← Produtos</a>
    <?php if ($produto): ?>
    <a href="/admin/produtos/<?= $produto['id'] ?>/ficha" class="adm-btn adm-btn-warning adm-btn-sm">🧪 Ficha Técnica</a>
    <a href="<?= APP_URL ?>/produtos/<?= $produto['slug'] ?>" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm">🌐 Ver no Site</a>
    <?php endif; ?>
</div>

<form method="POST" action="/admin/produtos/<?= $produto ? $produto['id'] . '/editar' : 'novo' ?>" enctype="multipart/form-data">
    <div class="adm-grid-2" style="align-items:start">
        <!-- Dados principais -->
        <div>
            <div class="adm-card">
                <div class="adm-card-header"><span class="adm-card-title">Dados Principais</span></div>
                <div class="adm-card-body">
                    <div class="adm-form-group">
                        <label>Nome do Produto *</label>
                        <input type="text" name="nome" required value="<?= htmlspecialchars($produto['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
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
                    <div class="adm-form-group">
                        <label>Descrição Curta (exibida na listagem)</label>
                        <textarea name="descricao_curta" rows="2"><?= htmlspecialchars($produto['descricao_curta'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="adm-form-group">
                        <label>Descrição Completa</label>
                        <textarea name="descricao_completa" rows="4"><?= htmlspecialchars($produto['descricao_completa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="adm-form-group">
                        <label>Composição (ervas, ingredientes...)</label>
                        <textarea name="composicao" rows="3"><?= htmlspecialchars($produto['composicao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="adm-form-group">
                        <label>Modo de Uso</label>
                        <textarea name="modo_uso" rows="3"><?= htmlspecialchars($produto['modo_uso'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="adm-form-group">
                        <label>Cuidados / Avisos</label>
                        <textarea name="cuidados" rows="2"><?= htmlspecialchars($produto['cuidados'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preços + Estoque + Imagens -->
        <div>
            <div class="adm-card" style="margin-bottom:1.5rem">
                <div class="adm-card-header"><span class="adm-card-title">Preço e Margem</span></div>
                <div class="adm-card-body">
                    <div class="adm-form-grid adm-form-grid-2">
                        <div class="adm-form-group">
                            <label>Preço de Venda (R$) *</label>
                            <input type="number" name="preco_venda" step="0.01" min="0" required value="<?= $produto['preco_venda'] ?? '0.00' ?>">
                        </div>
                        <div class="adm-form-group">
                            <label>Margem Desejada (%)</label>
                            <input type="number" name="margem_desejada" step="0.01" min="0" value="<?= $produto['margem_desejada'] ?? '0' ?>">
                        </div>
                    </div>
                    <?php if ($produto): ?>
                    <div class="adm-alert adm-alert-info" style="margin-top:0.5rem">
                        <strong>Custo calculado:</strong> <?= Helper::money((float)$produto['custo_calculado']) ?> |
                        <strong>Lucro:</strong> <?= Helper::money((float)$produto['lucro_calculado']) ?> |
                        <strong>Margem real:</strong> <?= number_format($produto['margem_real'], 2, ',', '.') ?>%
                        <?php if ((float)$produto['margem_real'] < (float)$produto['margem_desejada']): ?>
                        <br><span style="color:#E53E3E">⚠️ Margem abaixo do desejado!</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="adm-card" style="margin-bottom:1.5rem">
                <div class="adm-card-header"><span class="adm-card-title">Estoque</span></div>
                <div class="adm-card-body">
                    <div class="adm-form-grid adm-form-grid-2">
                        <div class="adm-form-group">
                            <label>Estoque Mínimo (un)</label>
                            <input type="number" name="estoque_minimo" min="0" value="<?= $produto['estoque_minimo'] ?? 0 ?>">
                        </div>
                        <div class="adm-form-group">
                            <label>Estoque Atual</label>
                            <input type="text" value="<?= $produto['estoque_atual'] ?? 0 ?> un" readonly style="background:#F8F9FA;color:#718096">
                            <div class="adm-hint">Ajustado via <a href="/admin/producao/nova">Produção</a> ou <a href="/admin/estoque">Estoque</a>.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="adm-card" style="margin-bottom:1.5rem">
                <div class="adm-card-header"><span class="adm-card-title">Imagens</span></div>
                <div class="adm-card-body">
                    <div class="adm-form-group">
                        <label>Adicionar imagens (múltiplas)</label>
                        <input type="file" name="imagens[]" multiple accept="image/jpeg,image/png,image/webp">
                        <div class="adm-hint">A primeira imagem enviada será a principal. JPG/PNG/WEBP, máx 5MB cada.</div>
                    </div>
                    <?php if (!empty($imagens)): ?>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem">
                        <?php foreach ($imagens as $img): ?>
                        <div style="position:relative">
                            <img src="<?= Helper::upload($img['caminho']) ?>" alt="" style="height:72px;border-radius:4px;border:<?= $img['principal'] ? '2px solid #2C5F2E' : '1px solid #E2E8F0' ?>">
                            <?php if ($img['principal']): ?><div style="position:absolute;top:2px;left:2px;background:#2C5F2E;color:white;font-size:9px;padding:1px 4px;border-radius:2px">PRINCIPAL</div><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="adm-card">
                <div class="adm-card-body">
                    <div class="adm-form-group" style="margin-bottom:1rem">
                        <label class="adm-switch">
                            <input type="checkbox" name="ativo" <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>>
                            Produto ativo (visível no site)
                        </label>
                    </div>
                    <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg" style="width:100%">💾 Salvar Produto</button>
                </div>
            </div>
        </div>
    </div>
</form>
