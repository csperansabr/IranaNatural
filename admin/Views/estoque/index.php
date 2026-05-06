<?php use App\Core\Helper; $pageTitle = 'Controle de Estoque'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<!-- Ajuste rápido de estoque -->
<div class="adm-grid-2" style="margin-bottom:1.5rem;align-items:start">
    <!-- Ajuste de insumo -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Ajustar Estoque de Insumo</span></div>
        <div class="adm-card-body">
            <form method="POST" action="/admin/estoque/ajuste">
                <input type="hidden" name="tipo" value="insumo">
                <div class="adm-form-group">
                    <label>Insumo</label>
                    <select name="item_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($insumos as $i): ?>
                        <option value="<?= $i['id'] ?>">
                            <?= htmlspecialchars($i['nome'], ENT_QUOTES, 'UTF-8') ?> (Atual: <?= number_format($i['estoque_atual'],2,',','.') ?> <?= $i['unidade_medida'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-form-group">
                    <label>Novo estoque (na unidade do insumo)</label>
                    <input type="number" name="novo_estoque" step="0.0001" min="0" required>
                </div>
                <div class="adm-form-group">
                    <label>Motivo do ajuste</label>
                    <input type="text" name="observacoes" placeholder="Ex: inventário, perda, correção">
                </div>
                <button type="submit" class="adm-btn adm-btn-primary">Aplicar Ajuste</button>
            </form>
        </div>
    </div>

    <!-- Ajuste de produto -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Ajustar Estoque de Produto</span></div>
        <div class="adm-card-body">
            <form method="POST" action="/admin/estoque/ajuste">
                <input type="hidden" name="tipo" value="produto">
                <div class="adm-form-group">
                    <label>Produto</label>
                    <select name="item_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?> (Atual: <?= $p['estoque_atual'] ?> un)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-form-group">
                    <label>Novo estoque (unidades)</label>
                    <input type="number" name="novo_estoque" min="0" required>
                </div>
                <div class="adm-form-group">
                    <label>Motivo do ajuste</label>
                    <input type="text" name="observacoes" placeholder="Ex: inventário, perda, correção">
                </div>
                <button type="submit" class="adm-btn adm-btn-primary">Aplicar Ajuste</button>
            </form>
        </div>
    </div>
</div>

<!-- Insumos -->
<div class="adm-card" style="margin-bottom:1.5rem">
    <div class="adm-card-header">
        <span class="adm-card-title">🌾 Estoque de Insumos</span>
        <a href="/admin/compras/nova" class="adm-btn adm-btn-warning adm-btn-sm">+ Registrar Compra</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead><tr><th>Insumo</th><th>Unidade</th><th>Estoque Atual</th><th>Mínimo</th><th>Custo Médio</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($insumos as $i): ?>
            <?php $alerta = (float)$i['estoque_atual'] <= (float)$i['estoque_minimo']; ?>
            <tr>
                <td><strong><?= htmlspecialchars($i['nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= $i['unidade_medida'] ?></td>
                <td class="<?= $alerta ? 'estoque-baixo' : 'estoque-ok' ?>"><?= number_format($i['estoque_atual'],4,',','.') ?></td>
                <td><?= number_format($i['estoque_minimo'],2,',','.') ?></td>
                <td><?= Helper::money((float)$i['custo_medio']) ?>/<?= $i['unidade_medida'] ?></td>
                <td>
                    <?php if ($i['estoque_atual'] < 0): ?>
                    <span class="adm-badge adm-badge-danger">Negativo ⚠️</span>
                    <?php elseif ($alerta): ?>
                    <span class="adm-badge adm-badge-warning">Baixo</span>
                    <?php else: ?>
                    <span class="adm-badge adm-badge-success">OK</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Produtos acabados -->
<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title">🌿 Estoque de Produtos Acabados</span>
        <a href="/admin/producao/nova" class="adm-btn adm-btn-primary adm-btn-sm">+ Registrar Produção</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead><tr><th>Produto</th><th>Categoria</th><th>Estoque</th><th>Mínimo</th><th>Custo</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($produtos as $p): ?>
            <?php $alerta = (int)$p['estoque_atual'] <= (int)$p['estoque_minimo']; ?>
            <tr>
                <td><strong><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= htmlspecialchars($p['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="<?= $alerta ? 'estoque-baixo' : 'estoque-ok' ?>"><?= $p['estoque_atual'] ?> un</td>
                <td><?= $p['estoque_minimo'] ?> un</td>
                <td><?= Helper::money((float)$p['custo_calculado']) ?></td>
                <td>
                    <?php if ((int)$p['estoque_atual'] <= 0): ?>
                    <span class="adm-badge adm-badge-danger">Sem estoque</span>
                    <?php elseif ($alerta): ?>
                    <span class="adm-badge adm-badge-warning">Baixo</span>
                    <?php else: ?>
                    <span class="adm-badge adm-badge-success">OK</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
