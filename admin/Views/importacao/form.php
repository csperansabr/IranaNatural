<?php
$entidadeLabels = ['produtos' => 'Produtos', 'insumos' => 'Insumos', 'estoque' => 'Estoque'];
$entidadeLabel  = $entidadeLabels[$entidade] ?? ucfirst($entidade);
$pageTitle      = 'Importar ' . $entidadeLabel;
$pageBreadcrumb = '<a href="/admin/importacao">Importação</a> / ' . htmlspecialchars($entidadeLabel, ENT_QUOTES, 'UTF-8');
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>" style="margin-bottom:1.5rem">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div style="margin-bottom:1rem">
    <a href="/admin/importacao" class="adm-btn adm-btn-secondary adm-btn-sm">← Voltar</a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <span>📥 Importar <?= htmlspecialchars($entidadeLabel, ENT_QUOTES, 'UTF-8') ?></span>
        <a href="/admin/importacao/<?= $entidade ?>/modelo" class="adm-btn adm-btn-secondary adm-btn-sm">📄 Baixar Modelo CSV</a>
    </div>
    <div class="adm-card-body">

        <?php if ($entidade === 'produtos'): ?>
        <div style="background:#EBF8FF;border-left:4px solid #3182CE;padding:.75rem 1rem;border-radius:6px;margin-bottom:1.5rem;font-size:.88rem;color:#2B4B6F">
            <strong>📷 Imagens não são importadas.</strong> Após importar, acesse a edição de cada produto para adicionar imagens manualmente.
        </div>
        <?php endif; ?>

        <!-- Mode selector -->
        <div class="adm-form-group" style="margin-bottom:1.5rem">
            <label style="font-weight:600;display:block;margin-bottom:.6rem">Modo de importação</label>
            <div id="modo-radios" style="display:flex;gap:1rem;flex-wrap:wrap">
                <label class="modo-label" data-modo="criar">
                    <input type="radio" name="modo" value="criar"> Apenas criar novos
                </label>
                <label class="modo-label" data-modo="atualizar">
                    <input type="radio" name="modo" value="atualizar"> Apenas atualizar existentes
                </label>
                <label class="modo-label active" data-modo="criar_atualizar">
                    <input type="radio" name="modo" value="criar_atualizar" checked> Criar e atualizar
                </label>
            </div>
        </div>

        <!-- Upload zone -->
        <div id="upload-zone" class="upload-zone">
            <div class="upload-icon">📂</div>
            <div class="upload-title">Arraste o arquivo ou clique para selecionar</div>
            <div class="upload-sub">Formatos aceitos: CSV, XLSX • Tamanho máximo: 5 MB</div>
            <input type="file" id="file-input" accept=".csv,.xlsx" style="display:none">
        </div>
        <div id="file-info" style="display:none;padding:.8rem 1rem;background:#f8f9fa;border:1px solid #e2e8f0;border-radius:6px;margin-top:.8rem;align-items:center;gap:.8rem">
            <span id="file-info-icon" style="font-size:1.4rem">📄</span>
            <div>
                <div id="file-info-name" style="font-weight:600;font-size:.9rem"></div>
                <div id="file-info-size" style="font-size:.8rem;color:#718096"></div>
            </div>
            <button type="button" id="btn-clear-file" class="adm-btn adm-btn-secondary adm-btn-sm" style="margin-left:auto">✕ Remover</button>
        </div>

        <!-- Loading state -->
        <div id="loading" style="display:none;text-align:center;padding:2rem">
            <div class="spinner"></div>
            <p style="color:#718096;margin-top:.8rem">Analisando arquivo...</p>
        </div>

        <!-- Preview section -->
        <div id="preview-section" style="display:none;margin-top:1.5rem">
            <hr style="border:none;border-top:1px solid #e2e8f0;margin-bottom:1.5rem">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;color:#2C5F2E">Pré-visualização</h3>

            <!-- Summary bar -->
            <div id="summary-bar" style="display:flex;gap:.8rem;flex-wrap:wrap;margin-bottom:1rem;padding:.8rem 1rem;background:#f8f9fa;border-radius:6px;border:1px solid #e2e8f0">
                <span id="sum-total"></span>
                <span id="sum-ok" style="color:#2C5F2E"></span>
                <span id="sum-warning" style="color:#D69E2E"></span>
                <span id="sum-error" style="color:#E53E3E"></span>
                <span id="sum-skip" style="color:#718096"></span>
            </div>

            <!-- Preview table -->
            <div id="preview-wrap" style="overflow-x:auto;max-height:500px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px">
                <table id="preview-table" class="adm-table" style="font-size:.82rem;min-width:100%">
                    <thead id="preview-thead"></thead>
                    <tbody id="preview-tbody"></tbody>
                </table>
            </div>
            <div id="preview-note" style="font-size:.8rem;color:#718096;margin-top:.5rem"></div>

            <!-- Confirm button -->
            <div style="margin-top:1.5rem;display:flex;gap:.8rem;align-items:center">
                <button type="button" id="btn-importar" class="adm-btn adm-btn-primary" disabled>
                    ✅ Confirmar Importação
                </button>
                <span id="btn-importar-note" style="font-size:.85rem;color:#718096"></span>
            </div>
        </div>

        <!-- Results section -->
        <div id="result-section" style="display:none;margin-top:1.5rem">
            <hr style="border:none;border-top:1px solid #e2e8f0;margin-bottom:1.5rem">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;color:#2C5F2E">Resultado da Importação</h3>

            <div id="result-cards" style="display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1.5rem"></div>

            <div id="result-links" style="margin-bottom:1.2rem"></div>

            <div id="result-errors-wrap" style="display:none">
                <h4 style="font-size:.9rem;font-weight:700;color:#E53E3E;margin-bottom:.6rem">Detalhes de erros</h4>
                <div id="result-errors-table" style="overflow-x:auto"></div>
            </div>
        </div>

    </div>
</div>

<style>
/* Upload zone */
.upload-zone {
    border: 2px dashed #CBD5E0;
    border-radius: 10px;
    padding: 2.5rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #fafafa;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: #2C5F2E;
    background: #F0FFF4;
}
.upload-zone.has-file {
    border-color: #2C5F2E;
    background: #F0FFF4;
}
.upload-icon { font-size: 2.5rem; margin-bottom: .5rem; }
.upload-title { font-size: 1rem; font-weight: 600; color: #2D3748; }
.upload-sub   { font-size: .82rem; color: #718096; margin-top: .3rem; }

/* Mode radio labels */
.modo-label {
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem .9rem;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    font-size: .88rem;
    transition: border-color .2s, background .2s;
}
.modo-label.active, .modo-label:hover {
    border-color: #2C5F2E;
    background: #F0FFF4;
}
.modo-label input { margin: 0; accent-color: #2C5F2E; }

/* Preview table row colors */
#preview-tbody tr.row-ok     { background: #F0FFF4; }
#preview-tbody tr.row-warning { background: #FFFBEB; }
#preview-tbody tr.row-error  { background: #FFF5F5; }
#preview-tbody tr.row-skip   { background: #F7FAFC; opacity: .7; }

.row-issue { font-size: .75rem; color: #E53E3E; display: block; margin-top: 2px; }
.row-warn-msg { font-size: .75rem; color: #D69E2E; display: block; margin-top: 2px; }

/* Result cards */
.res-card {
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #e2e8f0;
}
.res-card .res-num  { font-size: 2rem; font-weight: 700; }
.res-card .res-lbl  { font-size: .8rem; color: #718096; margin-top: .2rem; }
@media (max-width: 600px) {
    #result-cards { grid-template-columns: repeat(2, 1fr) !important; }
    .modo-label { font-size: .8rem; padding: .4rem .7rem; }
}

/* Spinner */
.spinner {
    width: 36px; height: 36px;
    border: 4px solid #e2e8f0;
    border-top-color: #2C5F2E;
    border-radius: 50%;
    animation: spin .8s linear infinite;
    margin: 0 auto;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Sticky thead */
#preview-table thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
</style>

<script>
(function () {
    'use strict';

    const entidade = <?= json_encode($entidade) ?>;
    const zone     = document.getElementById('upload-zone');
    const fileInput= document.getElementById('file-input');
    const fileInfo = document.getElementById('file-info');
    const loading  = document.getElementById('loading');
    const preview  = document.getElementById('preview-section');
    const result   = document.getElementById('result-section');
    const btnImp   = document.getElementById('btn-importar');
    const btnNote  = document.getElementById('btn-importar-note');

    let selectedFile   = null;
    let previewData    = null;
    let importDone     = false;

    // ── File selection ─────────────────────────────────────────────
    zone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', e => {
        const f = e.target.files[0];
        if (f) handleFile(f);
    });
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        const f = e.dataTransfer.files[0];
        if (f) handleFile(f);
    });

    document.getElementById('btn-clear-file').addEventListener('click', () => {
        clearState();
    });

    function clearState() {
        selectedFile = null;
        previewData  = null;
        importDone   = false;
        fileInput.value = '';
        zone.classList.remove('has-file');
        fileInfo.style.display = 'none';
        preview.style.display  = 'none';
        result.style.display   = 'none';
        btnImp.disabled = true;
    }

    function handleFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['csv', 'xlsx'].includes(ext)) {
            alert('Formato inválido. Use CSV ou XLSX.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Arquivo muito grande. Máximo: 5 MB.');
            return;
        }
        selectedFile = file;
        zone.classList.add('has-file');
        document.getElementById('file-info-name').textContent = file.name;
        document.getElementById('file-info-size').textContent = formatBytes(file.size);
        fileInfo.style.display = 'flex';
        preview.style.display  = 'none';
        result.style.display   = 'none';

        doPreview();
    }

    // ── Mode selection ──────────────────────────────────────────────
    document.querySelectorAll('.modo-label').forEach(lbl => {
        lbl.addEventListener('click', () => {
            document.querySelectorAll('.modo-label').forEach(l => l.classList.remove('active'));
            lbl.classList.add('active');
            lbl.querySelector('input').checked = true;
            // Re-analyze if a file is selected and import hasn't completed yet
            if (selectedFile && !importDone) doPreview();
        });
    });

    function getModo() {
        const checked = document.querySelector('input[name="modo"]:checked');
        return checked ? checked.value : 'criar_atualizar';
    }

    // ── Preview / Upload ────────────────────────────────────────────
    function doPreview() {
        if (!selectedFile) return;
        const fd = new FormData();
        fd.append('arquivo', selectedFile);
        fd.append('modo', getModo());

        loading.style.display = 'flex';
        loading.style.flexDirection = 'column';
        loading.style.alignItems = 'center';
        preview.style.display  = 'none';
        result.style.display   = 'none';
        btnImp.disabled = true;

        fetch('/admin/importacao/' + entidade + '/preview', {
            method: 'POST',
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            if (data.error) {
                alert('Erro: ' + data.error);
                return;
            }
            previewData = data;
            renderPreview(data);
            preview.style.display = 'block';
        })
        .catch(err => {
            loading.style.display = 'none';
            alert('Erro de comunicação: ' + err.message);
        });
    }

    function renderPreview(data) {
        // Summary
        const s = data.summary;
        document.getElementById('sum-total').innerHTML   = '<strong>' + s.total + '</strong> linhas';
        document.getElementById('sum-ok').innerHTML      = s.ok > 0      ? '✅ ' + s.ok + ' ok'       : '';
        document.getElementById('sum-warning').innerHTML = s.warning > 0 ? '⚠️ ' + s.warning + ' avisos' : '';
        document.getElementById('sum-error').innerHTML   = s.error > 0   ? '❌ ' + s.error + ' erros'  : '';
        document.getElementById('sum-skip').innerHTML    = s.skip > 0    ? '⏭️ ' + s.skip + ' ignoradas' : '';

        // Header
        const thead = document.getElementById('preview-thead');
        const hRow = document.createElement('tr');
        hRow.innerHTML = '<th style="white-space:nowrap">Linha</th><th>Status</th>';
        (data.cols || []).forEach(col => {
            const th = document.createElement('th');
            th.textContent = col;
            hRow.appendChild(th);
        });
        thead.innerHTML = '';
        thead.appendChild(hRow);

        // Rows (max 100 in UI)
        const tbody = document.getElementById('preview-tbody');
        tbody.innerHTML = '';
        const displayRows = data.rows.slice(0, 100);
        const total = data.rows.length;

        displayRows.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = 'row-' + row.status;

            const statusIcons = { ok: '✅', warning: '⚠️', error: '❌', skip: '⏭️' };
            tr.innerHTML = '<td style="white-space:nowrap;font-size:.8rem">' + row.linha + '</td>' +
                '<td style="white-space:nowrap">' + (statusIcons[row.status] || row.status) + '</td>';

            const rawVals = row.raw || [];
            const colNames = data.cols || [];
            colNames.forEach((col, ci) => {
                const td = document.createElement('td');
                const cellVal = rawVals[ci] !== undefined ? rawVals[ci] : (row.data[col] || '');
                let html = '<span>' + escHtml(String(cellVal)) + '</span>';

                // Show field-level errors/warnings
                (row.errors || []).forEach(err => {
                    if (err.campo === col) {
                        html += '<span class="row-issue">❌ ' + escHtml(err.msg) + '</span>';
                    }
                });
                (row.warnings || []).forEach(w => {
                    if (w.campo === col) {
                        html += '<span class="row-warn-msg">⚠️ ' + escHtml(w.msg) + '</span>';
                    }
                });

                td.innerHTML = html;
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });

        if (total > 100) {
            document.getElementById('preview-note').textContent =
                'Mostrando 100 de ' + total + ' linhas. Todas serão processadas na importação.';
        } else {
            document.getElementById('preview-note').textContent = '';
        }

        // Enable import button if no critical errors (errors = 0, or has ok/warning rows)
        const hasImportable = (s.ok + s.warning) > 0;
        btnImp.disabled = !hasImportable;
        if (s.error > 0 && hasImportable) {
            btnNote.textContent = s.error + ' linha(s) com erro serão ignoradas.';
        } else if (!hasImportable) {
            btnNote.textContent = 'Nenhuma linha pode ser importada.';
        } else {
            btnNote.textContent = '';
        }
    }

    // ── Import ──────────────────────────────────────────────────────
    btnImp.addEventListener('click', () => {
        if (!confirm('Confirmar importação de dados? Esta ação não pode ser desfeita.')) return;

        btnImp.disabled = true;
        btnImp.textContent = '⏳ Importando...';
        loading.style.display = 'flex';
        loading.style.flexDirection = 'column';
        loading.style.alignItems = 'center';

        fetch('/admin/importacao/' + entidade + '/processar', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            if (data.error) {
                alert('Erro: ' + data.error);
                btnImp.disabled = false;
                btnImp.textContent = '✅ Confirmar Importação';
                return;
            }
            importDone = true;
            renderResult(data);
            preview.style.display = 'none';
            result.style.display  = 'block';
            btnImp.textContent    = '✅ Confirmar Importação';
        })
        .catch(err => {
            loading.style.display = 'none';
            alert('Erro de comunicação: ' + err.message);
            btnImp.disabled = false;
            btnImp.textContent = '✅ Confirmar Importação';
        });
    });

    function renderResult(data) {
        const cards = [
            { num: data.inseridos,   lbl: 'Inseridos',   bg: '#F0FFF4', color: '#2C5F2E' },
            { num: data.atualizados, lbl: 'Atualizados', bg: '#EBF8FF', color: '#2B6CB0' },
            { num: data.erros,       lbl: 'Erros',       bg: '#FFF5F5', color: '#E53E3E' },
            { num: data.ignorados,   lbl: 'Ignorados',   bg: '#F7FAFC', color: '#718096' },
        ];
        const cardHtml = cards.map(c =>
            '<div class="res-card" style="background:' + c.bg + ';border-color:' + c.color + '30">' +
            '<div class="res-num" style="color:' + c.color + '">' + (c.num || 0) + '</div>' +
            '<div class="res-lbl">' + c.lbl + '</div></div>'
        ).join('');
        document.getElementById('result-cards').innerHTML = cardHtml;

        // Links
        const linkMap = {
            produtos: '<a href="/admin/produtos" class="adm-btn adm-btn-primary adm-btn-sm">🌿 Gerenciar Produtos</a>',
            insumos:  '<a href="/admin/insumos"  class="adm-btn adm-btn-primary adm-btn-sm">🌾 Gerenciar Insumos</a>',
            estoque:  '<a href="/admin/estoque"  class="adm-btn adm-btn-primary adm-btn-sm">📦 Ver Estoque</a>',
        };
        document.getElementById('result-links').innerHTML =
            (linkMap[entidade] || '') +
            ' <a href="/admin/importacao/historico" class="adm-btn adm-btn-secondary adm-btn-sm">📋 Histórico</a>';

        // Error details
        const errs = (data.detalhes || []).filter(d => d.status === 'error');
        const errWrap = document.getElementById('result-errors-wrap');
        if (errs.length > 0) {
            errWrap.style.display = 'block';
            let tbl = '<table class="adm-table" style="font-size:.82rem"><thead><tr><th>Linha</th><th>Mensagem</th></tr></thead><tbody>';
            errs.forEach(e => {
                tbl += '<tr><td>' + e.linha + '</td><td>' + escHtml(e.msg) + '</td></tr>';
            });
            tbl += '</tbody></table>';
            document.getElementById('result-errors-table').innerHTML = tbl;
        } else {
            errWrap.style.display = 'none';
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────
    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();
</script>
