<?php use App\Core\Helper; $pageTitle = 'Banners'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Banners da Página Inicial</span>
        <a href="/admin/banners/novo" class="adm-btn adm-btn-primary adm-btn-sm">+ Novo Banner</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead><tr><th>Imagem</th><th>Título</th><th>Subtítulo</th><th>Ordem</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
            <?php if (empty($banners)): ?>
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:#718096">Nenhum banner cadastrado. A página inicial exibirá o hero padrão.</td></tr>
            <?php else: ?>
            <?php foreach ($banners as $b): ?>
            <tr>
                <td><img src="<?= Helper::upload($b['imagem']) ?>" alt="" style="height:50px;border-radius:4px;object-fit:cover;width:90px"></td>
                <td><?= htmlspecialchars($b['titulo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($b['subtitulo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $b['ordem'] ?></td>
                <td><span class="adm-badge adm-badge-<?= $b['ativo'] ? 'success' : 'gray' ?>"><?= $b['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                <td>
                    <a href="/admin/banners/<?= $b['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm">Editar</a>
                    <form method="POST" action="/admin/banners/<?= $b['id'] ?>/excluir" style="display:inline" onsubmit="return confirm('Excluir este banner?')">
                        <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">Excluir</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
