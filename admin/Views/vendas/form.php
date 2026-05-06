<?php use App\Core\Helper; $pageTitle = 'Registrar Venda'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<a href="/admin/vendas" class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-bottom:1.5rem;display:inline-flex">← Voltar</a>

<div class="adm-card">
    <div class="adm-card-header"><span class="adm-card-title">Nova Venda</span></div>
    <div class="adm-card-body">
        <div class="adm-alert adm-alert-info">
            Adicione os produtos vendidos. O estoque será debitado automaticamente ao confirmar. Vendas com estoque insuficiente serão bloqueadas.
        </div>

        <form method="POST" action="/admin/vendas/nova" id="form-venda">
            <div class="adm-form-grid adm-form-grid-3" style="margin-bottom:1.25rem">
                <div class="adm-form-group">
                    <label>Data da Venda *</label>
                    <input type="date" name="data_venda" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="adm-form-group">
                    <label>Forma de Pagamento *</label>
                    <select name="forma_pagamento" required>
                        <option value="pix">PIX</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="debito">Débito</option>
                        <option value="credito">Crédito</option>
                        <option value="transferencia">Transferência</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="adm-form-group">
                    <label>Desconto (R$)</label>
                    <input type="number" name="desconto" step="0.01" min="0" value="0" id="inp-desconto" oninput="calcTotal()">
                </div>
            </div>

            <!-- Itens da venda -->
            <div class="adm-card" style="margin-bottom:1.5rem">
                <div class="adm-card-header">
                    <span class="adm-card-title">Itens da Venda</span>
                    <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" onclick="addLinha()">+ Adicionar Produto</button>
                </div>
                <div id="linhas-container" style="padding:1rem">
                    <!-- Linha inicial -->
                </div>
            </div>

            <!-- Totais -->
            <div class="adm-card" style="margin-bottom:1.5rem;max-width:350px;margin-left:auto">
                <div class="adm-card-body" style="font-size:0.9rem">
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem">
                        <span>Subtotal:</span><strong id="txt-subtotal">R$ 0,00</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;color:#E53E3E">
                        <span>Desconto:</span><strong id="txt-desconto">R$ 0,00</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:1.1rem;border-top:2px solid #E2E8F0;padding-top:0.5rem;margin-top:0.25rem">
                        <span><strong>Total:</strong></span><strong id="txt-total">R$ 0,00</strong>
                    </div>
                </div>
            </div>

            <div class="adm-form-group">
                <label>Observações</label>
                <textarea name="observacoes" rows="2" placeholder="Notas sobre a venda..."></textarea>
            </div>

            <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg">💰 Confirmar Venda</button>
        </form>
    </div>
</div>

<script>
const produtos = <?= json_encode(array_map(fn($p) => [
    'id'    => $p['id'],
    'nome'  => $p['nome'],
    'preco' => $p['preco_venda'],
    'est'   => $p['estoque_atual'],
], $produtos), JSON_UNESCAPED_UNICODE) ?>;

let linhaCount = 0;

function addLinha() {
    linhaCount++;
    const n   = linhaCount;
    const div = document.createElement('div');
    div.id    = 'linha-' + n;
    div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1.5fr auto;gap:0.75rem;align-items:end;margin-bottom:0.75rem';

    const opts = produtos.map(p =>
        `<option value="${p.id}" data-preco="${p.preco}" data-est="${p.est}">${p.nome} (Est: ${p.est} un)</option>`
    ).join('');

    div.innerHTML = `
        <div class="adm-form-group" style="margin:0">
            <label style="font-size:0.72rem">Produto *</label>
            <select name="produto_id[]" required onchange="preencherPreco(this,${n})">
                <option value="">Selecione...</option>${opts}
            </select>
        </div>
        <div class="adm-form-group" style="margin:0">
            <label style="font-size:0.72rem">Qtd *</label>
            <input type="number" name="quantidade[]" min="1" required value="1" id="qtd-${n}" oninput="calcTotal()">
        </div>
        <div class="adm-form-group" style="margin:0">
            <label style="font-size:0.72rem">Preço Unit. (R$) *</label>
            <input type="number" name="preco_unitario[]" step="0.01" min="0.01" required id="preco-${n}" oninput="calcTotal()">
        </div>
        <div>
            <button type="button" onclick="remLinha(${n})" class="adm-btn adm-btn-danger adm-btn-sm" style="margin-top:1.5rem">✕</button>
        </div>
    `;
    document.getElementById('linhas-container').appendChild(div);
    calcTotal();
}

function preencherPreco(sel, n) {
    const opt   = sel.options[sel.selectedIndex];
    const preco = opt?.dataset?.preco;
    if (preco) document.getElementById('preco-' + n).value = parseFloat(preco).toFixed(2);
    calcTotal();
}

function remLinha(n) {
    const el = document.getElementById('linha-' + n);
    if (el) el.remove();
    calcTotal();
}

function calcTotal() {
    let sub = 0;
    for (let i = 1; i <= linhaCount; i++) {
        const qtd   = parseFloat(document.getElementById('qtd-' + i)?.value || 0);
        const preco = parseFloat(document.getElementById('preco-' + i)?.value || 0);
        sub += qtd * preco;
    }
    const desc  = parseFloat(document.getElementById('inp-desconto').value || 0);
    const total = Math.max(0, sub - desc);
    const fmt   = v => 'R$ ' + v.toFixed(2).replace('.',',');
    document.getElementById('txt-subtotal').textContent = fmt(sub);
    document.getElementById('txt-desconto').textContent = fmt(desc);
    document.getElementById('txt-total').textContent    = fmt(total);
}

// Linha inicial
addLinha();
</script>
