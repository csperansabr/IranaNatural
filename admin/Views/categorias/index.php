<?php use App\Core\Helper; $pageTitle = 'Categorias'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Categorias de Produtos</span>
        <a href="/admin/categorias/nova" class="adm-btn adm-btn-primary adm-btn-sm">+ Nova Categoria</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>Nome</th><th>Slug</th><th>Ordem</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php if (empty($categorias)): ?>
            <tr><td colspan="5" style="text-align:center;padding:2rem;color:#718096">Nenhuma categoria cadastrada.</td></tr>
            <?php else: ?>
            <?php foreach ($categorias as $cat): ?>
            <tr>
                <td><strong><?= htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><code><?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= $cat['ordem'] ?></td>
                <td>
                    <?php if ($cat['ativo']): ?>
                    <span class="adm-badge adm-badge-success">Ativa</span>
                    <?php else: ?>
                    <span class="adm-badge adm-badge-gray">Inativa</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/admin/categorias/<?= $cat['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm">Editar</a>
                    <form method="POST" action="/admin/categorias/<?= $cat['id'] ?>/excluir" style="display:inline"
                          onsubmit="return confirm('Excluir esta categoria?')">
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
