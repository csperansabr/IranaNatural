<?php use App\Core\Helper; use App\Models\Pedido; ?>

<?php if ($flash['msg']): ?>
<div class="adm-alert adm-alert--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="adm-pedido-layout">

    <!-- Coluna principal -->
    <div class="adm-pedido-main">

        <!-- Cabeçalho do pedido -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Pedido <?= htmlspecialchars($pedido['numero'], ENT_QUOTES, 'UTF-8') ?></h2>
                <span class="adm-status adm-status--<?= $pedido['status'] ?>" style="font-size:14px; padding:6px 14px;">
                    <?= Pedido::statusLabel($pedido['status']) ?>
                </span>
            </div>
            <div class="adm-pedido-meta">
                <div>
                    <label>Data do pedido</label>
                    <span><?= Helper::datetime($pedido['criado_em']) ?></span>
                </div>
                <div>
                    <label>Última atualização</label>
                    <span><?= Helper::datetime($pedido['atualizado_em']) ?></span>
                </div>
                <div>
                    <label>Forma de pagamento</label>
                    <span><?= Pedido::pagamentoLabel($pedido['forma_pagamento']) ?></span>
                </div>
            </div>
        </div>

        <!-- Produtos -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header"><h3>Produtos</h3></div>
            <div class="adm-table-wrap"><table class="adm-table">
                <?php
                $pixPctPed = (float)($pedido['desconto_pix_pct'] ?? 0);
                $isPix     = $pedido['forma_pagamento'] === 'pix' && $pixPctPed > 0;
                ?>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="text-align:center;">Qtd</th>
                        <th style="text-align:right;">Preço Unit.<?= $isPix ? ' (c/ PIX)' : '' ?></th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nome_produto'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="text-align:center;"><?= (int)$item['quantidade'] ?></td>
                        <td style="text-align:right;">
                            <?php if ($isPix): ?>
                            <span style="text-decoration:line-through;color:#aaa;font-size:0.85em">
                                <?= Helper::money(round((float)$item['preco_unitario'] / (1 - $pixPctPed / 100), 2)) ?>
                            </span><br>
                            <strong style="color:#2C5F2E"><?= Helper::money((float)$item['preco_unitario']) ?></strong>
                            <?php else: ?>
                            <?= Helper::money((float)$item['preco_unitario']) ?>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;"><strong><?= Helper::money((float)$item['subtotal']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <?php if ($isPix): ?>
                    <tr>
                        <td colspan="4" style="font-size:12px;color:#2C5F2E;padding:6px 0;text-align:right;">
                            ✓ Desconto PIX de <?= number_format($pixPctPed, 0) ?>% aplicado individualmente em cada item
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="3" style="text-align:right; font-weight:bold;">Subtotal</td>
                        <td style="text-align:right;"><?= Helper::money((float)$pedido['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:right;">Frete</td>
                        <td style="text-align:right;"><?= (float)$pedido['frete'] > 0 ? Helper::money((float)$pedido['frete']) : 'A combinar' ?></td>
                    </tr>
                    <?php if ((float)($pedido['desconto'] ?? 0) > 0): ?>
                    <tr>
                        <td colspan="3" style="text-align:right;">Economia com PIX</td>
                        <td style="text-align:right; color:#2C5F2E;">↓ <?= Helper::money((float)$pedido['desconto']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="3" style="text-align:right; font-size:16px; font-weight:bold; padding-top:12px;">TOTAL</td>
                        <td style="text-align:right; font-size:18px; font-weight:bold; color:#2C5F2E; padding-top:12px;"><?= Helper::money((float)$pedido['total']) ?></td>
                    </tr>
                </tfoot>
            </table></div>
        </div>

        <!-- Endereço de entrega -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header"><h3>Endereço de Entrega</h3></div>
            <div style="padding:16px 20px; color:#5A4E40; line-height:1.8;">
                <?= htmlspecialchars($pedido['entrega_logradouro'] ?? '', ENT_QUOTES, 'UTF-8') ?>,
                <?= htmlspecialchars($pedido['entrega_numero'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                <?php if ($pedido['entrega_complemento']): ?>
                — <?= htmlspecialchars($pedido['entrega_complemento'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?><br>
                <?= htmlspecialchars($pedido['entrega_bairro'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                <?= htmlspecialchars($pedido['entrega_cidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($pedido['entrega_estado'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                CEP <?= htmlspecialchars($pedido['entrega_cep'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

        <?php if ($pedido['observacoes']): ?>
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header"><h3>Observações do cliente</h3></div>
            <div style="padding:16px 20px; color:#5A4E40;">
                <?= htmlspecialchars($pedido['observacoes'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Histórico de status -->
        <?php if (!empty($historico)): ?>
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header"><h3>Histórico de status</h3></div>
            <div style="padding:16px 20px;">
                <ol style="list-style:none;padding:0;margin:0;position:relative;">
                    <?php foreach ($historico as $entrada): ?>
                    <?php
                        $ehAdmin   = $entrada['origem'] === 'admin';
                        $ehWebhook = $entrada['origem'] === 'webhook';
                        $corBorda  = $ehAdmin ? '#2C5F2E' : ($ehWebhook ? '#2980b9' : '#8A7A6A');
                    ?>
                    <li style="display:flex;gap:14px;align-items:flex-start;margin-bottom:1.1rem;">
                        <div style="flex-shrink:0;width:12px;height:12px;border-radius:50%;background:<?= $corBorda ?>;margin-top:4px;box-shadow:0 0 0 3px <?= $corBorda ?>22;"></div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px;">
                                <span class="adm-status adm-status--<?= htmlspecialchars($entrada['status'], ENT_QUOTES, 'UTF-8') ?>" style="font-size:11px;padding:2px 8px;">
                                    <?= \App\Models\Pedido::statusLabel($entrada['status']) ?>
                                </span>
                                <?php if (!empty($entrada['status_anterior'])): ?>
                                <span style="font-size:11px;color:#8A7A6A;">
                                    ← <?= \App\Models\Pedido::statusLabel($entrada['status_anterior']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:11px;color:#8A7A6A;margin-bottom:3px;">
                                <?= htmlspecialchars(\App\Core\Helper::datetime($entrada['criado_em']), ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($entrada['admin_nome'])): ?>
                                &middot; por <strong><?= htmlspecialchars($entrada['admin_nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php elseif ($ehWebhook): ?>
                                &middot; InfinitePay (webhook)
                                <?php else: ?>
                                &middot; Sistema
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($entrada['observacao'])): ?>
                            <p style="margin:0;font-size:13px;color:#5A4E40;line-height:1.5;word-break:break-word;">
                                <?= htmlspecialchars($entrada['observacao'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar -->
    <aside class="adm-pedido-sidebar">

        <!-- Dados do cliente -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header"><h3>Cliente</h3></div>
            <div class="adm-pedido-cliente">
                <div><label>Nome</label><span><?= htmlspecialchars($pedido['cliente_nome'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><label>E-mail</label><a href="mailto:<?= htmlspecialchars($pedido['cliente_email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pedido['cliente_email'], ENT_QUOTES, 'UTF-8') ?></a></div>
                <div><label>CPF</label><span><?= htmlspecialchars($pedido['cliente_cpf'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <?php if ($pedido['cliente_telefone']): ?>
                <div><label>Telefone</label><a href="https://wa.me/55<?= preg_replace('/\D/', '', $pedido['cliente_telefone']) ?>" target="_blank"><?= htmlspecialchars($pedido['cliente_telefone'], ENT_QUOTES, 'UTF-8') ?></a></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagamento InfinitePay -->
        <?php if ($pagamento): ?>
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header"><h3>Pagamento InfinitePay</h3></div>
            <div class="adm-pedido-cliente">
                <div>
                    <label>Status</label>
                    <span><?= htmlspecialchars(ucfirst($pagamento['status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php if (!empty($pagamento['metodo'])): ?>
                <div>
                    <label>Método</label>
                    <span><?= \App\Models\Pedido::pagamentoLabel($pagamento['metodo']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($pagamento['valor_cobrado'])): ?>
                <div>
                    <label>Valor cobrado</label>
                    <span><?= \App\Core\Helper::money((float)$pagamento['valor_cobrado']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($pagamento['valor_pago'])): ?>
                <div>
                    <label>Valor pago</label>
                    <span style="color:#2C5F2E; font-weight:600;"><?= \App\Core\Helper::money((float)$pagamento['valor_pago']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($pagamento['pago_em'])): ?>
                <div>
                    <label>Data de aprovação</label>
                    <span><?= \App\Core\Helper::datetime($pagamento['pago_em']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($pagamento['transaction_nsu'])): ?>
                <div>
                    <label>Transaction NSU</label>
                    <span style="font-family:monospace; font-size:12px; word-break:break-all;"><?= htmlspecialchars($pagamento['transaction_nsu'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($pagamento['invoice_slug'])): ?>
                <div>
                    <label>Invoice Slug</label>
                    <span style="font-family:monospace; font-size:12px;"><?= htmlspecialchars($pagamento['invoice_slug'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($pagamento['receipt_url'])): ?>
                <div style="padding-top:4px;">
                    <label>Recibo</label>
                    <a href="<?= htmlspecialchars($pagamento['receipt_url'], ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="adm-btn adm-btn-sm adm-btn-primary" style="margin-top:4px; display:inline-flex; align-items:center; gap:6px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Ver Recibo
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="12" height="12"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($pagamento['checkout_url'])): ?>
                <div style="padding-top:4px;">
                    <label>Link de pagamento</label>
                    <a href="<?= htmlspecialchars($pagamento['checkout_url'], ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="adm-btn adm-btn-sm adm-btn-secondary" style="margin-top:4px; display:inline-flex; align-items:center; gap:6px; font-size:12px;">
                        Link original
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="11" height="11"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </div>
                <?php endif; ?>

                <?php $statusTerminais = ['pago','separando','enviado','entregue','cancelado']; ?>
                <div style="padding-top:12px; border-top:1px solid #e8e0d8; margin-top:8px;">
                    <button type="button" id="btn-consultar-pagamento"
                        data-pedido="<?= $pedido['id'] ?>"
                        data-csrf="<?= \App\Core\Session::csrfToken() ?>"
                        class="adm-btn adm-btn-sm adm-btn-secondary adm-btn-block"
                        style="display:flex; align-items:center; justify-content:center; gap:6px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14"><path d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/></svg>
                        Consultar status na InfinitePay
                    </button>
                    <div id="consultar-feedback" style="margin-top:8px; font-size:12px; line-height:1.4; display:none;"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Entrega / Frete -->
        <?php
        $tipoFrete = $pedido['tipo_frete'] ?? null;
        $temFrete  = $tipoFrete !== null && $tipoFrete !== '';
        ?>
        <?php if ($temFrete): ?>
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header"><h3>Entrega</h3></div>
            <div class="adm-pedido-cliente">
                <div>
                    <label>Modalidade</label>
                    <span><?= htmlspecialchars($pedido['transportadora'] ?? $tipoFrete, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php if (!empty($pedido['prazo_entrega'])): ?>
                <div>
                    <label>Prazo</label>
                    <span><?= htmlspecialchars($pedido['prazo_entrega'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php endif; ?>
                <div>
                    <label>Valor do frete</label>
                    <span><?= (float)$pedido['frete'] > 0 ? \App\Core\Helper::money((float)$pedido['frete']) : 'Grátis' ?></span>
                </div>
                <?php if (!empty($pedido['resp_entrega_cliente'])): ?>
                <div>
                    <label>Responsável</label>
                    <span style="color:#e67e22; font-weight:600;">Cliente (contrata diretamente)</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Atualizar status -->
        <div class="adm-card">
            <div class="adm-card-header"><h3>Atualizar Status</h3></div>
            <div style="padding:16px 20px;">
                <form id="form-status">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">
                    <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">

                    <div class="adm-form-group" style="margin-bottom:12px;">
                        <label class="adm-label">Novo status</label>
                        <select name="status" class="adm-input" id="select-status">
                            <?php foreach (['pendente','pago','separando','enviado','entregue','cancelado'] as $s): ?>
                            <option value="<?= $s ?>" <?= $pedido['status'] === $s ? 'selected' : '' ?>>
                                <?= Pedido::statusLabel($s) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="adm-form-group" style="margin-bottom:12px;">
                        <label class="adm-label">Observação (opcional)</label>
                        <textarea name="obs" class="adm-input" rows="3" placeholder="Ex: Código de rastreio: BR12345678&#10;Esta informação será exibida no histórico e no e-mail ao cliente."></textarea>
                    </div>

                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;padding:10px 12px;background:#f5f9f2;border:1px solid #b5c99a;border-radius:6px;">
                        <input type="checkbox" name="notificar_cliente" value="1" id="chk-notificar" checked style="width:16px;height:16px;accent-color:#2C5F2E;cursor:pointer;">
                        <label for="chk-notificar" style="margin:0;font-size:13px;color:#2A2218;cursor:pointer;line-height:1.3;">
                            Notificar cliente por e-mail<br>
                            <span style="font-size:11px;color:#8A7A6A;font-weight:normal;">(inclui observação se preenchida)</span>
                        </label>
                    </div>

                    <button type="button" class="adm-btn adm-btn-primary adm-btn-block" id="btn-status">
                        Salvar status
                    </button>
                    <div id="status-feedback" style="margin-top:10px; font-size:13px; line-height:1.4;"></div>
                </form>
            </div>
        </div>

    </aside>
</div>

<div style="margin-top:1rem;">
    <a href="/admin/pedidos" class="adm-btn adm-btn-secondary">← Voltar aos pedidos</a>
</div>

<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
<script>
document.getElementById('btn-consultar-pagamento')?.addEventListener('click', async function () {
    const btn      = this;
    const feedback = document.getElementById('consultar-feedback');
    const pedidoId = btn.dataset.pedido;
    const csrf     = btn.dataset.csrf;

    btn.disabled    = true;
    btn.innerHTML   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Consultando…';
    feedback.style.display  = 'none';
    feedback.textContent    = '';

    try {
        const fd = new FormData();
        fd.append('_csrf', csrf);

        const res  = await fetch('/admin/pedidos/' + pedidoId + '/consultar-pagamento', { method: 'POST', body: fd });
        const data = await res.json();

        feedback.style.display = 'block';
        if (data.ok) {
            feedback.style.color = data.reload ? '#2C5F2E' : '#5A4E40';
            feedback.textContent = '✓ ' + data.msg;
            if (data.reload) {
                setTimeout(() => window.location.reload(), 1800);
            } else {
                btn.disabled  = false;
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14"><path d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/></svg> Consultar status na InfinitePay';
            }
        } else {
            feedback.style.color = '#c0392b';
            feedback.textContent = '✗ ' + (data.msg || 'Erro ao consultar.');
            btn.disabled  = false;
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14"><path d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/></svg> Consultar status na InfinitePay';
        }
    } catch (e) {
        feedback.style.display = 'block';
        feedback.style.color   = '#c0392b';
        feedback.textContent   = '✗ Erro de comunicação. Tente novamente.';
        btn.disabled  = false;
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14"><path d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/></svg> Consultar status na InfinitePay';
    }
});

document.getElementById('btn-status')?.addEventListener('click', async function () {
    const form     = document.getElementById('form-status');
    const fd       = new FormData(form);
    const btn      = this;
    const feedback = document.getElementById('status-feedback');

    btn.disabled    = true;
    btn.textContent = 'Salvando…';
    feedback.textContent = '';
    feedback.style.color = '';

    try {
        const res  = await fetch('/admin/pedidos/status', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok) {
            feedback.style.color = '#2C5F2E';
            feedback.textContent = '✓ ' + data.msg;

            // Atualiza badge: texto + classe CSS
            const badge = document.querySelector('.adm-status');
            if (badge && data.label) {
                badge.textContent = data.label;
                // Remove classes de status anteriores e adiciona a nova
                badge.className = badge.className.replace(/adm-status--\S+/g, '').trim();
                if (data.class) badge.classList.add(data.class);
            }

            // Recarrega a página após 1,2s para atualizar o histórico e o formulário
            setTimeout(() => window.location.reload(), 1200);
        } else {
            feedback.style.color = '#c0392b';
            feedback.textContent = '✗ ' + (data.msg || 'Erro ao atualizar.');
            btn.disabled    = false;
            btn.textContent = 'Salvar status';
        }
    } catch (e) {
        feedback.style.color = '#c0392b';
        feedback.textContent = '✗ Erro de comunicação. Tente novamente.';
        btn.disabled    = false;
        btn.textContent = 'Salvar status';
    }
});
</script>
