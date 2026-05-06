<?php $pageTitle = $dep ? 'Editar Depoimento' : 'Novo Depoimento'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<a href="/admin/depoimentos" class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-bottom:1.5rem;display:inline-flex">← Voltar</a>

<div class="adm-card" style="max-width:680px">
    <div class="adm-card-header"><span class="adm-card-title"><?= $pageTitle ?></span></div>
    <div class="adm-card-body">
        <form method="POST" action="/admin/depoimentos/<?= $dep ? $dep['id'] . '/editar' : 'novo' ?>" enctype="multipart/form-data">
            <div class="adm-form-grid adm-form-grid-2">
                <div class="adm-form-group">
                    <label>Nome do Cliente *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($dep['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="adm-form-group">
                    <label>Avaliação (1–5 estrelas)</label>
                    <select name="avaliacao">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                        <option value="<?= $s ?>" <?= ($dep['avaliacao'] ?? 5) == $s ? 'selected' : '' ?>><?= str_repeat('★',$s) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="adm-form-group">
                <label>Depoimento *</label>
                <textarea name="texto" rows="4" required><?= htmlspecialchars($dep['texto'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="adm-form-grid adm-form-grid-2">
                <div class="adm-form-group">
                    <label>Foto (opcional)</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                    <?php if (!empty($dep['foto'])): ?>
                    <img src="<?= \App\Core\Helper::upload($dep['foto']) ?>" alt="" style="height:40px;border-radius:50%;margin-top:0.5rem">
                    <?php endif; ?>
                </div>
                <div class="adm-form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $dep['ordem'] ?? 0 ?>" min="0">
                </div>
            </div>
            <div class="adm-form-group">
                <label class="adm-switch">
                    <input type="checkbox" name="ativo" <?= ($dep['ativo'] ?? 1) ? 'checked' : '' ?>>
                    Depoimento ativo (visível no site)
                </label>
            </div>
            <button type="submit" class="adm-btn adm-btn-primary">💾 Salvar</button>
        </form>
    </div>
</div>
