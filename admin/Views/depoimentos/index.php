<?php use App\Core\Helper; $pageTitle = 'Depoimentos'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Depoimentos de Clientes</span>
        <a href="/admin/depoimentos/novo" class="adm-btn adm-btn-primary adm-btn-sm">+ Novo Depoimento</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead><tr><th>Nome</th><th>Texto</th><th>Avaliação</th><th>Ordem</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
            <?php if (empty($depoimentos)): ?>
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:#718096">Nenhum depoimento cadastrado.</td></tr>
            <?php else: ?>
            <?php foreach ($depoimentos as $d): ?>
            <tr>
                <td><strong><?= htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= htmlspecialchars(Helper::excerpt($d['texto'], 60), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= str_repeat('★', (int)$d['avaliacao']) ?></td>
                <td><?= $d['ordem'] ?></td>
                <td><span class="adm-badge adm-badge-<?= $d['ativo'] ? 'success' : 'gray' ?>"><?= $d['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                <td>
                    <a href="/admin/depoimentos/<?= $d['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm">Editar</a>
                    <form method="POST" action="/admin/depoimentos/<?= $d['id'] ?>/excluir" style="display:inline" onsubmit="return confirm('Excluir?')">
                        <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">✕</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
