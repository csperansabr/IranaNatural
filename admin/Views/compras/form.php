<?php use App\Core\Helper; $pageTitle = 'Registrar Compra'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<a href="/admin/compras" class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-bottom:1.5rem;display:inline-flex">← Voltar</a>

<div class="adm-card" style="max-width:680px">
    <div class="adm-card-header"><span class="adm-card-title">Nova Entrada de Insumo</span></div>
    <div class="adm-card-body">
        <div class="adm-alert adm-alert-info">
            Ao registrar a compra, o <strong>custo médio ponderado</strong> do insumo será recalculado automaticamente, e o custo de todos os produtos que utilizam esse insumo será atualizado.
        </div>
        <form method="POST" action="/admin/compras/nova">
            <div class="adm-form-group">
                <label>Insumo *</label>
                <select name="insumo_id" required id="sel-insumo" onchange="mostrarInfo(this)">
                    <option value="">Selecione o insumo...</option>
                    <?php foreach ($insumos as $ins): ?>
                    <option value="<?= $ins['id'] ?>"
                            data-unidade="<?= htmlspecialchars($ins['unidade_medida'], ENT_QUOTES, 'UTF-8') ?>"
                            data-cma="<?= $ins['custo_medio'] ?>"
                            data-estoque="<?= $ins['estoque_atual'] ?>">
                        <?= htmlspecialchars($ins['nome'], ENT_QUOTES, 'UTF-8') ?>
                        (Estoque: <?= number_format($ins['estoque_atual'], 2, ',', '.') ?> <?= $ins['unidade_medida'] ?> | CMA: <?= Helper::money((float)$ins['custo_medio']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="insumo-info" style="display:none;margin-bottom:1rem" class="adm-alert adm-alert-info">
                <strong>Insumo selecionado:</strong> <span id="info-nome"></span><br>
                Estoque atual: <span id="info-estoque"></span> | CMA atual: <span id="info-cma"></span>
            </div>

            <div class="adm-form-grid adm-form-grid-2">
                <div class="adm-form-group">
                    <label>Data da Compra *</label>
                    <input type="date" name="data_compra" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="adm-form-group">
                    <label>Fornecedor</label>
                    <input type="text" name="fornecedor" placeholder="Nome do fornecedor">
                </div>
                <div class="adm-form-group">
                    <label>Quantidade * <span id="lbl-unidade" style="font-weight:400;text-transform:none;color:#718096"></span></label>
                    <input type="number" name="quantidade" step="0.0001" min="0.0001" required id="inp-qtd" oninput="calcUnit()">
                </div>
                <div class="adm-form-group">
                    <label>Valor Total (R$) *</label>
                    <input type="number" name="valor_total" step="0.01" min="0.01" required id="inp-total" oninput="calcUnit()">
                </div>
            </div>

            <div class="adm-alert adm-alert-info" id="calc-unitario" style="display:none;margin-bottom:1rem">
                Valor unitário calculado: <strong id="valor-unit">R$ 0,00</strong> / <span id="unit-label"></span>
            </div>

            <div class="adm-form-group">
                <label>Observações</label>
                <textarea name="observacoes" rows="2" placeholder="Notas sobre esta compra..."></textarea>
            </div>
            <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg">✔ Registrar Compra</button>
        </form>
    </div>
</div>

<script>
function mostrarInfo(sel) {
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('insumo-info');
    if (!opt.value) { info.style.display = 'none'; return; }
    document.getElementById('info-nome').textContent    = opt.text.split('(')[0].trim();
    document.getElementById('info-estoque').textContent = opt.dataset.estoque + ' ' + opt.dataset.unidade;
    document.getElementById('info-cma').textContent     = 'R$ ' + parseFloat(opt.dataset.cma).toFixed(6).replace('.',',');
    document.getElementById('lbl-unidade').textContent  = '(' + opt.dataset.unidade + ')';
    document.getElementById('unit-label').textContent   = opt.dataset.unidade;
    info.style.display = 'block';
    calcUnit();
}
function calcUnit() {
    const qtd   = parseFloat(document.getElementById('inp-qtd').value) || 0;
    const total = parseFloat(document.getElementById('inp-total').value) || 0;
    const el    = document.getElementById('calc-unitario');
    if (qtd > 0 && total > 0) {
        const unit = total / qtd;
        document.getElementById('valor-unit').textContent = 'R$ ' + unit.toFixed(6).replace('.',',');
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}
</script>
