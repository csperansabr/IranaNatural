<?php use App\Core\Helper;
$pageTitle = 'Ficha Técnica — ' . ($produto['nome'] ?? '');
$unidades  = ['g','kg','mg','ml','l','un','pct','cx'];
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="/admin/produtos" class="adm-btn adm-btn-secondary adm-btn-sm">← Produtos</a>
    <a href="/admin/produtos/<?= $produto['id'] ?>/editar" class="adm-btn adm-btn-secondary adm-btn-sm">✏️ Editar Produto</a>
</div>

<div class="adm-alert adm-alert-info">
    <strong>Produto:</strong> <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?> |
    <strong>Custo calculado:</strong> <?= Helper::money((float)$produto['custo_calculado']) ?> |
    <strong>Preço de venda:</strong> <?= Helper::money((float)$produto['preco_venda']) ?> |
    <strong>Margem real:</strong> <?= number_format($produto['margem_real'], 2, ',', '.') ?>%
    <?php if ((float)$produto['margem_real'] < (float)$produto['margem_desejada']): ?>
    — <span style="color:#E53E3E">⚠️ Abaixo da margem desejada (<?= $produto['margem_desejada'] ?>%)</span>
    <?php endif; ?>
</div>

<div class="adm-grid-2" style="align-items:start">
    <!-- Lista de itens da ficha -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Composição (por unidade produzida)</span></div>
        <?php if (empty($ficha)): ?>
        <div class="adm-card-body" style="text-align:center;color:#718096;padding:2rem">
            Nenhum insumo adicionado. Use o formulário ao lado para montar a ficha técnica.
        </div>
        <?php else: ?>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr><th>Insumo</th><th>Qtd</th><th>Unidade</th><th>Custo Médio</th><th>Custo na Receita</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($ficha as $item): ?>
                <?php
                    $custoUnit = Helper::costPerUnit(
                        (float)$item['custo_medio'],
                        $item['insumo_unidade'],
                        $item['unidade']
                    );
                    $custoItem = $custoUnit !== null ? $custoUnit * (float)$item['quantidade'] : null;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['insumo_nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= number_format($item['quantidade'], 4, ',', '.') ?></td>
                    <td><?= htmlspecialchars($item['unidade'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= Helper::money((float)$item['custo_medio']) ?>/<?= $item['insumo_unidade'] ?></td>
                    <td>
                        <?php if ($custoItem !== null): ?>
                        <?= Helper::money($custoItem) ?>
                        <?php else: ?>
                        <span style="color:#E53E3E">⚠️ Unidades incompatíveis</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="/admin/produtos/<?= $produto['id'] ?>/ficha-excluir/<?= $item['id'] ?>"
                              onsubmit="return confirm('Remover este insumo da ficha?')">
                            <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulário adicionar item -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Adicionar Insumo à Ficha</span></div>
        <div class="adm-card-body">
            <div class="adm-alert adm-alert-info">
                A ficha define os insumos necessários para produzir <strong>1 unidade</strong> do produto. Conversão de unidades é automática (g↔kg, ml↔l).
            </div>
            <form method="POST" action="/admin/produtos/<?= $produto['id'] ?>/ficha">
                <div class="adm-form-group">
                    <label>Insumo *</label>
                    <select name="insumo_id" required id="ficha-insumo" onchange="preencherUnidade(this)">
                        <option value="">Selecione o insumo...</option>
                        <?php foreach ($insumos as $ins): ?>
                        <option value="<?= $ins['id'] ?>"
                                data-unidade="<?= htmlspecialchars($ins['unidade_medida'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($ins['nome'], ENT_QUOTES, 'UTF-8') ?> (<?= $ins['unidade_medida'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-form-grid adm-form-grid-2">
                    <div class="adm-form-group">
                        <label>Quantidade *</label>
                        <input type="number" name="quantidade" step="0.0001" min="0.0001" required placeholder="Ex: 50">
                    </div>
                    <div class="adm-form-group">
                        <label>Unidade *</label>
                        <select name="unidade" required id="ficha-unidade">
                            <?php foreach ($unidades as $u): ?>
                            <option value="<?= $u ?>"><?= $u ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="adm-hint" style="margin-bottom:1rem">
                    Se a unidade da ficha diferir da unidade de compra do insumo, a conversão é automática (ex: comprou em kg, usa g na ficha — ok).
                </div>
                <button type="submit" class="adm-btn adm-btn-primary" style="width:100%">+ Adicionar à Ficha</button>
            </form>
        </div>
    </div>
</div>

<script>
function preencherUnidade(sel) {
    const unidade = sel.options[sel.selectedIndex]?.dataset?.unidade;
    const uSel    = document.getElementById('ficha-unidade');
    if (unidade && uSel) {
        for (let opt of uSel.options) {
            if (opt.value === unidade) { opt.selected = true; break; }
        }
    }
}
</script>
