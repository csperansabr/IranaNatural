<?php use App\Core\Helper;
$pageTitle = $insumo ? 'Editar Insumo' : 'Novo Insumo';
$unidades  = ['g','kg','mg','ml','l','un','pct','cx'];
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<a href="/admin/insumos" class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-bottom:1.5rem;display:inline-flex">← Voltar</a>

<div class="adm-grid-2" style="align-items:start">
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title"><?= $pageTitle ?></span></div>
        <div class="adm-card-body">
            <form method="POST" action="/admin/insumos/<?= $insumo ? $insumo['id'] . '/editar' : 'novo' ?>">
                <div class="adm-form-group">
                    <label>Nome *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($insumo['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Arruda Seca">
                </div>
                <div class="adm-form-grid adm-form-grid-2">
                    <div class="adm-form-group">
                        <label>Unidade de Medida *</label>
                        <select name="unidade_medida" required>
                            <?php foreach ($unidades as $u): ?>
                            <option value="<?= $u ?>" <?= ($insumo['unidade_medida'] ?? '') === $u ? 'selected' : '' ?>><?= $u ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="adm-form-group">
                        <label>Estoque Mínimo</label>
                        <input type="number" step="0.01" name="estoque_minimo" value="<?= $insumo['estoque_minimo'] ?? 0 ?>">
                    </div>
                </div>
                <div class="adm-form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" rows="2"><?= htmlspecialchars($insumo['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="adm-form-group">
                    <label>Fornecedor habitual</label>
                    <input type="text" name="fornecedor" value="<?= htmlspecialchars($insumo['fornecedor'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nome do fornecedor">
                </div>
                <div class="adm-form-group">
                    <label class="adm-switch">
                        <input type="checkbox" name="ativo" <?= ($insumo['ativo'] ?? 1) ? 'checked' : '' ?>>
                        Insumo ativo
                    </label>
                </div>
                <?php if ($insumo): ?>
                <div class="adm-alert adm-alert-info" style="margin-bottom:1rem">
                    <strong>Estoque atual:</strong> <?= number_format($insumo['estoque_atual'], 4, ',', '.') ?> <?= $insumo['unidade_medida'] ?> |
                    <strong>Custo médio:</strong> <?= Helper::money((float)$insumo['custo_medio']) ?>/<?= $insumo['unidade_medida'] ?>
                    <br><small>Para ajustar estoque use o módulo <a href="/admin/estoque">Estoque</a>. Para atualizar custo, registre uma <a href="/admin/compras/nova">Compra</a>.</small>
                </div>
                <?php endif; ?>
                <button type="submit" class="adm-btn adm-btn-primary">💾 Salvar</button>
            </form>
        </div>
    </div>

    <!-- Histórico de movimentações -->
    <?php if (!empty($movimentacoes)): ?>
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Histórico de Movimentações</span></div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead><tr><th>Data</th><th>Tipo</th><th>Qtd</th><th>Saldo Após</th><th>Origem</th></tr></thead>
                <tbody>
                <?php foreach ($movimentacoes as $mov): ?>
                <tr>
                    <td><?= Helper::datetime($mov['criado_em']) ?></td>
                    <td>
                        <span class="adm-badge adm-badge-<?= $mov['tipo'] === 'entrada' ? 'success' : ($mov['tipo'] === 'saida' ? 'danger' : 'warning') ?>">
                            <?= $mov['tipo'] ?>
                        </span>
                    </td>
                    <td><?= number_format($mov['quantidade'], 4, ',', '.') ?></td>
                    <td><?= number_format($mov['saldo_apos'], 4, ',', '.') ?></td>
                    <td><?= htmlspecialchars($mov['ref_tipo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
