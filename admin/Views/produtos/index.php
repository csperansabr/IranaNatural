<?php use App\Core\Helper; $pageTitle = 'Produtos'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">Produtos Cadastrados</span>
        <a href="/admin/produtos/novo" class="adm-btn adm-btn-primary adm-btn-sm">+ Novo Produto</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Custo</th><th>Margem Real</th><th>Estoque</th><th>Publicação</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php if (empty($produtos)): ?>
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:#718096">Nenhum produto cadastrado.</td></tr>
            <?php else: ?>
            <?php foreach ($produtos as $p): ?>
            <?php
            $margemOk = (float)$p['margem_real'] >= (float)$p['margem_desejada'];
            $estBaixo = (int)$p['estoque_atual'] <= (int)$p['estoque_minimo'];
            $inativo  = !(int)$p['ativo'];
            ?>
            <tr style="<?= $inativo ? 'opacity:0.6' : '' ?>">
                <td>
                    <strong><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if (!empty($p['tags'])): ?>
                    <br><small style="color:#718096"><?= htmlspecialchars($p['tags'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= Helper::money((float)$p['preco_venda']) ?></td>
                <td><?= Helper::money((float)$p['custo_calculado']) ?></td>
                <td>
                    <span style="color:<?= $margemOk ? '#38A169' : '#E53E3E' ?>;font-weight:700">
                        <?= number_format($p['margem_real'], 1, ',', '.') ?>%
                        <?= !$margemOk ? ' ⚠️' : '' ?>
                    </span>
                    <br><small style="color:#718096">Meta: <?= number_format($p['margem_desejada'], 1, ',', '.') ?>%</small>
                </td>
                <td class="<?= $estBaixo ? 'estoque-baixo' : 'estoque-ok' ?>">
                    <?= $p['estoque_atual'] ?> un
                    <?php if ($estBaixo): ?>⚠️<?php endif; ?>
                </td>
                <td>
                    <?php if ($p['ativo']): ?>
                    <span class="adm-badge adm-badge-success">Publicado</span>
                    <?php else: ?>
                    <span class="adm-badge adm-badge-gray">Rascunho</span>
                    <?php endif; ?>
                </td>
                <td style="white-space:nowrap">
                    <a href="/admin/produtos/<?= $p['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm">Editar</a>
                    <a href="/admin/produtos/<?= $p['id'] ?>/ficha" class="adm-btn adm-btn-warning adm-btn-sm" title="Ficha Técnica">🧪</a>
                    <?php if ($p['ativo']): ?>
                    <form method="POST" action="/admin/produtos/<?= $p['id'] ?>/excluir" style="display:inline" onsubmit="return confirm('Despublicar este produto?')">
                        <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" title="Despublicar">Off</button>
                    </form>
                    <?php else: ?>
                    <a href="/admin/produtos/<?= $p['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm" title="Reativar via edição" style="background:#E2E8F0">On?</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
