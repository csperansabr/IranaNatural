<?php use App\Core\Helper;
$pageTitle = $categoria ? 'Editar Categoria' : 'Nova Categoria'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="display:flex;gap:1rem;margin-bottom:1.5rem">
    <a href="/admin/categorias" class="adm-btn adm-btn-secondary adm-btn-sm">← Voltar</a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title"><?= $pageTitle ?></span>
    </div>
    <div class="adm-card-body">
        <form method="POST" action="/admin/categorias/<?= $categoria ? $categoria['id'] . '/editar' : 'nova' ?>" enctype="multipart/form-data">
            <div class="adm-form-grid adm-form-grid-2">
                <div class="adm-form-group">
                    <label>Nome *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($categoria['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Incensos">
                </div>
                <div class="adm-form-group">
                    <label>Ordem de exibição</label>
                    <input type="number" name="ordem" value="<?= $categoria['ordem'] ?? 0 ?>" min="0">
                </div>
            </div>
            <div class="adm-form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3" placeholder="Breve descrição da categoria..."><?= htmlspecialchars($categoria['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="adm-form-group">
                <label>Imagem da categoria</label>
                <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp">
                <div class="adm-hint">JPG, PNG ou WEBP. Máx 5MB. Proporção recomendada: 4:3.</div>
                <?php if (!empty($categoria['imagem'])): ?>
                <div style="margin-top:0.75rem">
                    <img src="<?= Helper::upload($categoria['imagem']) ?>" alt="" style="height:80px;border-radius:4px">
                    <div class="adm-hint">Imagem atual — envie uma nova para substituir</div>
                </div>
                <?php endif; ?>
            </div>
            <div class="adm-form-group">
                <label class="adm-switch">
                    <input type="checkbox" name="ativo" <?= ($categoria['ativo'] ?? 1) ? 'checked' : '' ?>>
                    Categoria ativa (visível no site)
                </label>
            </div>
            <button type="submit" class="adm-btn adm-btn-primary">💾 Salvar Categoria</button>
        </form>
    </div>
</div>
