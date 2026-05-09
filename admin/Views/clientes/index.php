<?php $pageTitle = 'Clientes'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
    <form method="GET" action="/admin/clientes" style="display:flex;gap:.5rem;flex:1;max-width:420px">
        <input type="text" name="q" value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Buscar por nome, e-mail ou CPF..." class="adm-input" style="flex:1">
        <button type="submit" class="adm-btn adm-btn-secondary">Buscar</button>
        <?php if ($busca): ?><a href="/admin/clientes" class="adm-btn adm-btn-secondary">✕</a><?php endif; ?>
    </form>
    <a href="/admin/clientes/novo" class="adm-btn adm-btn-primary">+ Novo Cliente</a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">
            <?= count($clientes) ?> cliente<?= count($clientes) !== 1 ? 's' : '' ?>
            <?= $busca ? ' — filtrado por "' . htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
        </span>
    </div>
    <div class="adm-card-body" style="padding:0">
        <?php if (empty($clientes)): ?>
        <p style="padding:2rem;text-align:center;color:#718096">Nenhum cliente encontrado.</p>
        <?php else: ?>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Cidade/UF</th>
                        <th>Origem</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td style="color:#A0AEC0;font-size:.8rem"><?= (int)$c['id'] ?></td>
                    <td><strong><?= htmlspecialchars($c['nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars($c['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($c['cpf'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $c['telefone'] ? htmlspecialchars(\App\Models\Cliente::formatarTelefone($c['telefone']), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td><?= $c['cidade'] ? htmlspecialchars($c['cidade'] . '/' . $c['estado'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td>
                        <span style="font-size:.78rem;padding:.15rem .5rem;border-radius:4px;
                            background:<?= ($c['origem'] ?? 'online') === 'admin' ? '#EBF8FF' : '#F0FFF4' ?>;
                            color:<?= ($c['origem'] ?? 'online') === 'admin' ? '#2B6CB0' : '#276749' ?>">
                            <?= ($c['origem'] ?? 'online') === 'admin' ? 'Admin' : 'Online' ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size:.78rem;padding:.15rem .5rem;border-radius:4px;
                            background:<?= $c['ativo'] ? '#F0FFF4' : '#FFF5F5' ?>;
                            color:<?= $c['ativo'] ? '#276749' : '#9B2C2C' ?>">
                            <?= $c['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap">
                        <a href="/admin/clientes/<?= (int)$c['id'] ?>" class="adm-btn adm-btn-secondary adm-btn-sm">Ver</a>
                        <a href="/admin/clientes/<?= (int)$c['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
