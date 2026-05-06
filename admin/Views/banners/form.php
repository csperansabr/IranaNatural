<?php use App\Core\Helper; $pageTitle = $banner ? 'Editar Banner' : 'Novo Banner'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<a href="/admin/banners" class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-bottom:1.5rem;display:inline-flex">← Voltar</a>

<div class="adm-card" style="max-width:680px">
    <div class="adm-card-header"><span class="adm-card-title"><?= $pageTitle ?></span></div>
    <div class="adm-card-body">
        <form method="POST" action="/admin/banners/<?= $banner ? $banner['id'] . '/editar' : 'novo' ?>" enctype="multipart/form-data">
            <div class="adm-form-group">
                <label>Imagem do Banner <?= !$banner ? '*' : '' ?></label>
                <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" <?= !$banner ? 'required' : '' ?>>
                <div class="adm-hint">Tamanho recomendado: 1600×600px. JPG/PNG/WEBP. Máx 5MB.</div>
                <?php if ($banner && $banner['imagem']): ?>
                <img src="<?= Helper::upload($banner['imagem']) ?>" alt="" style="margin-top:0.75rem;max-height:100px;border-radius:4px">
                <?php endif; ?>
            </div>
            <div class="adm-form-group">
                <label>Título (opcional)</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($banner['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Natureza em cada detalhe">
            </div>
            <div class="adm-form-group">
                <label>Subtítulo (opcional)</label>
                <input type="text" name="subtitulo" value="<?= htmlspecialchars($banner['subtitulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="adm-form-group">
                <label>Link (opcional)</label>
                <input type="text" name="link" value="<?= htmlspecialchars($banner['link'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: /produtos/incensos">
            </div>
            <div class="adm-form-grid adm-form-grid-2">
                <div class="adm-form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $banner['ordem'] ?? 0 ?>" min="0">
                </div>
                <div class="adm-form-group" style="display:flex;align-items:center">
                    <label class="adm-switch" style="margin-top:1.5rem">
                        <input type="checkbox" name="ativo" <?= ($banner['ativo'] ?? 1) ? 'checked' : '' ?>>
                        Banner ativo
                    </label>
                </div>
            </div>
            <button type="submit" class="adm-btn adm-btn-primary">💾 Salvar Banner</button>
        </form>
    </div>
</div>
