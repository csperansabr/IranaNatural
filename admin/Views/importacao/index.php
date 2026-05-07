<?php
$pageTitle      = 'Importação de Dados';
$pageBreadcrumb = 'Ferramentas / Importação';

$entidades = [
    'produtos' => [
        'icon'  => '🌿',
        'label' => 'Produtos',
        'desc'  => 'Importe produtos em massa com nome, categoria, preço, descrição, SEO, estoque e SKU.',
        'color' => '#2C5F2E',
    ],
    'insumos' => [
        'icon'  => '🌾',
        'label' => 'Insumos',
        'desc'  => 'Importe matérias-primas com unidade de medida, custo médio, estoque e fornecedor.',
        'color' => '#5D7A3A',
    ],
    'estoque' => [
        'icon'  => '📦',
        'label' => 'Estoque',
        'desc'  => 'Ajuste o estoque atual de produtos ou insumos por SKU ou nome.',
        'color' => '#6B4E37',
    ],
];

$entidadeLabels = ['produtos' => 'Produtos', 'insumos' => 'Insumos', 'estoque' => 'Estoque'];
$modoLabels     = [
    'criar'           => 'Criar novos',
    'atualizar'       => 'Atualizar existentes',
    'criar_atualizar' => 'Criar e atualizar',
];
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>" style="margin-bottom:1.5rem">
    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<!-- Cards de entidades -->
<div class="adm-form-grid adm-form-grid-3" style="--cols:3">
<?php foreach ($entidades as $slug => $info): ?>
<div class="adm-card">
    <div class="adm-card-header" style="border-left: 4px solid <?= $info['color'] ?>">
        <span style="font-size:1.6rem"><?= $info['icon'] ?></span>
        <div>
            <div style="font-weight:700;font-size:1.1rem"><?= $info['label'] ?></div>
        </div>
    </div>
    <div class="adm-card-body">
        <p style="color:#555;font-size:.9rem;margin-bottom:1.2rem"><?= $info['desc'] ?></p>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="/admin/importacao/<?= $slug ?>" class="adm-btn adm-btn-primary adm-btn-sm">
                📥 Importar
            </a>
            <a href="/admin/importacao/<?= $slug ?>/modelo" class="adm-btn adm-btn-secondary adm-btn-sm">
                📄 Baixar Modelo
            </a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Histórico recente -->
<div class="adm-card" style="margin-top:2rem">
    <div class="adm-card-header" style="display:flex;justify-content:space-between;align-items:center">
        <span>📋 Importações Recentes</span>
        <a href="/admin/importacao/historico" class="adm-btn adm-btn-secondary adm-btn-sm">Ver tudo</a>
    </div>
    <div class="adm-card-body" style="padding:0">
        <?php if (empty($historico)): ?>
            <p style="padding:1.5rem;color:#718096;text-align:center">Nenhuma importação realizada ainda.</p>
        <?php else: ?>
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Entidade</th>
                    <th>Modo</th>
                    <th>Arquivo</th>
                    <th style="text-align:center">Total</th>
                    <th style="text-align:center">Inseridos</th>
                    <th style="text-align:center">Atualizados</th>
                    <th style="text-align:center">Erros</th>
                    <th style="text-align:center">Ignorados</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historico as $h): ?>
                <tr>
                    <td style="white-space:nowrap;font-size:.85rem">
                        <?= date('d/m/Y H:i', strtotime($h['criado_em'])) ?>
                    </td>
                    <td>
                        <span class="adm-badge" style="background:<?= match($h['entidade']) { 'produtos' => '#2C5F2E', 'insumos' => '#5D7A3A', default => '#6B4E37' } ?>;color:#fff">
                            <?= $entidadeLabels[$h['entidade']] ?? $h['entidade'] ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:#555"><?= $modoLabels[$h['modo']] ?? $h['modo'] ?></td>
                    <td style="font-size:.82rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($h['arquivo_nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($h['arquivo_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td style="text-align:center"><?= (int)$h['total_linhas'] ?></td>
                    <td style="text-align:center">
                        <?php if ((int)$h['inseridos'] > 0): ?>
                            <span class="adm-badge" style="background:#2C5F2E;color:#fff"><?= (int)$h['inseridos'] ?></span>
                        <?php else: ?>
                            <span style="color:#aaa">0</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ((int)$h['atualizados'] > 0): ?>
                            <span class="adm-badge" style="background:#5D7A3A;color:#fff"><?= (int)$h['atualizados'] ?></span>
                        <?php else: ?>
                            <span style="color:#aaa">0</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ((int)$h['erros'] > 0): ?>
                            <span class="adm-badge" style="background:#E53E3E;color:#fff"><?= (int)$h['erros'] ?></span>
                        <?php else: ?>
                            <span style="color:#aaa">0</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ((int)$h['ignorados'] > 0): ?>
                            <span class="adm-badge" style="background:#718096;color:#fff"><?= (int)$h['ignorados'] ?></span>
                        <?php else: ?>
                            <span style="color:#aaa">0</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<style>
.adm-form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem; }
@media (max-width: 900px) { .adm-form-grid-3 { grid-template-columns: 1fr; } }
</style>
