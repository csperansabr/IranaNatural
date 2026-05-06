<?php use App\Core\Helper; $pageTitle = 'Nova Produção'; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<a href="/admin/producao" class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-bottom:1.5rem;display:inline-flex">← Voltar</a>

<div class="adm-grid-2" style="align-items:start">
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Registrar Produção</span></div>
        <div class="adm-card-body">
            <form method="POST" action="/admin/producao/nova" id="form-producao">
                <div class="adm-form-group">
                    <label>Produto *</label>
                    <select name="produto_id" id="sel-produto" required onchange="verificarInsumos()">
                        <option value="">Selecione o produto...</option>
                        <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?> (Estoque: <?= $p['estoque_atual'] ?> un)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-form-grid adm-form-grid-2">
                    <div class="adm-form-group">
                        <label>Quantidade a Produzir *</label>
                        <input type="number" name="quantidade_produzida" id="inp-qtd-prod" min="1" required onchange="verificarInsumos()">
                    </div>
                    <div class="adm-form-group">
                        <label>Data da Produção *</label>
                        <input type="date" name="data_producao" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <!-- Painel de verificação de insumos (AJAX) -->
                <div id="painel-insumos" style="display:none;margin-bottom:1.25rem"></div>

                <div class="adm-form-grid adm-form-grid-2">
                    <div class="adm-form-group">
                        <label>Quantidade de Perdas (unidades)</label>
                        <input type="number" name="quantidade_perda" min="0" step="0.01" value="0">
                        <div class="adm-hint">Unidades danificadas após a produção.</div>
                    </div>
                    <div class="adm-form-group">
                        <label>Motivo das Perdas</label>
                        <input type="text" name="motivo_perda" placeholder="Ex: embalagem danificada">
                    </div>
                </div>
                <div class="adm-form-group">
                    <label>Responsável</label>
                    <input type="text" name="responsavel" placeholder="Nome do responsável">
                </div>
                <div class="adm-form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" rows="2"></textarea>
                </div>
                <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg" style="width:100%">⚗️ Registrar Produção</button>
            </form>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">ℹ️ Como funciona</span></div>
        <div class="adm-card-body" style="font-size:0.88rem;color:#4A5568;line-height:1.7">
            <p>1. Selecione o produto e a quantidade.</p>
            <p>2. O sistema verifica automaticamente os insumos disponíveis com base na ficha técnica.</p>
            <p>3. Insumos insuficientes são marcados em <span style="color:#E53E3E">vermelho</span>. Você pode prosseguir assim mesmo (estoque pode ficar negativo).</p>
            <p>4. Ao confirmar: os insumos são debitados proporcionalmente e o estoque do produto é aumentado em <code>qtd_produzida − perdas</code>.</p>
            <p><strong>Custo real</strong> é calculado com base no custo médio atual dos insumos.</p>
        </div>
    </div>
</div>

<script>
let debounce;
function verificarInsumos() {
    clearTimeout(debounce);
    debounce = setTimeout(_verificar, 400);
}
function _verificar() {
    const prodId = document.getElementById('sel-produto').value;
    const qtd    = document.getElementById('inp-qtd-prod').value;
    const painel = document.getElementById('painel-insumos');

    if (!prodId || !qtd || qtd <= 0) { painel.style.display = 'none'; return; }

    painel.innerHTML = '<div class="adm-alert adm-alert-info">Verificando disponibilidade...</div>';
    painel.style.display = 'block';

    fetch(`/admin/producao/verificar/${prodId}/${qtd}`)
        .then(r => r.json())
        .then(data => {
            if (!data.itens || !data.itens.length) {
                painel.innerHTML = '<div class="adm-alert adm-alert-warning">Produto sem ficha técnica cadastrada. Cadastre os insumos em <a href="/admin/produtos">Produtos → Ficha Técnica</a>.</div>';
                return;
            }
            const classe = data.pode_total ? 'adm-alert-success' : 'adm-alert-warning';
            let html = `<div class="adm-alert ${classe}">`;
            html += data.pode_total
                ? '<strong>✓ Estoque de insumos suficiente.</strong>'
                : '<strong>⚠️ Estoque insuficiente para alguns insumos. Você pode prosseguir (estoque ficará negativo).</strong>';
            html += '<table style="width:100%;margin-top:0.75rem;font-size:0.82rem;border-collapse:collapse">';
            html += '<tr><th style="text-align:left;padding:4px 8px">Insumo</th><th>Necessário</th><th>Disponível</th><th>Status</th></tr>';
            data.itens.forEach(i => {
                const ok = i.ok;
                html += `<tr style="background:${ok ? 'transparent' : 'rgba(229,62,62,0.08)'}">
                    <td style="padding:4px 8px">${i.insumo_nome}</td>
                    <td style="text-align:center;padding:4px">${parseFloat(i.necessario).toFixed(4)} ${i.unidade}</td>
                    <td style="text-align:center;padding:4px">${parseFloat(i.disponivel).toFixed(4)} ${i.disponivel_un}</td>
                    <td style="text-align:center;padding:4px;color:${ok ? '#38A169' : '#E53E3E'};font-weight:700">${ok ? '✓' : '⚠'}</td>
                </tr>`;
            });
            html += '</table></div>';
            painel.innerHTML = html;
        })
        .catch(() => {
            painel.innerHTML = '<div class="adm-alert adm-alert-error">Erro ao verificar insumos.</div>';
        });
}
</script>
