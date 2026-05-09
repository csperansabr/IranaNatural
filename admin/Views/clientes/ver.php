<?php
use App\Models\Pedido;
$pageTitle = 'Cliente — ' . htmlspecialchars($cliente['nome'], ENT_QUOTES, 'UTF-8');
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="/admin/clientes" class="adm-btn adm-btn-secondary adm-btn-sm">← Voltar</a>
    <a href="/admin/clientes/<?= (int)$cliente['id'] ?>/editar" class="adm-btn adm-btn-primary adm-btn-sm">Editar Cliente</a>
</div>

<div class="adm-form-grid adm-form-grid-2" style="gap:1.5rem">

    <!-- Dados pessoais -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Dados Pessoais</span></div>
        <div class="adm-card-body" style="font-size:.9rem">
            <?php
            $row = function(string $label, ?string $valor) {
                echo '<div style="display:flex;gap:1rem;padding:.5rem 0;border-bottom:1px solid #F7FAFC">';
                echo '<span style="color:#718096;min-width:130px;flex-shrink:0">' . htmlspecialchars($label, ENT_QUOTES) . '</span>';
                echo '<span><strong>' . ($valor ? htmlspecialchars($valor, ENT_QUOTES) : '<em style="color:#A0AEC0">—</em>') . '</strong></span>';
                echo '</div>';
            };
            $row('Nome', $cliente['nome']);
            $row('CPF', $cliente['cpf']);
            $row('E-mail', $cliente['email']);
            $row('Telefone', $cliente['telefone'] ? \App\Models\Cliente::formatarTelefone($cliente['telefone']) : null);
            $row('Nascimento', $cliente['data_nascimento'] ? date('d/m/Y', strtotime($cliente['data_nascimento'])) : null);
            $row('Origem', ($cliente['origem'] ?? 'online') === 'admin' ? 'Admin' : 'Online (site)');
            $row('Status', $cliente['ativo'] ? 'Ativo' : 'Inativo');
            $row('Cadastrado em', $cliente['criado_em'] ? date('d/m/Y H:i', strtotime($cliente['criado_em'])) : null);
            ?>
        </div>
    </div>

    <!-- Endereço -->
    <div class="adm-card">
        <div class="adm-card-header"><span class="adm-card-title">Endereço</span></div>
        <div class="adm-card-body" style="font-size:.9rem">
            <?php if ($endereco): ?>
            <?php
            $cep = preg_replace('/\D/', '', $endereco['cep'] ?? '');
            $cepFmt = strlen($cep) === 8 ? substr($cep, 0, 5) . '-' . substr($cep, 5) : ($endereco['cep'] ?? '');
            $row('CEP', $cepFmt);
            $row('Logradouro', $endereco['logradouro']);
            $row('Número', $endereco['numero']);
            $row('Complemento', $endereco['complemento'] ?: null);
            $row('Bairro', $endereco['bairro']);
            $row('Cidade', $endereco['cidade']);
            $row('Estado', $endereco['estado']);
            ?>
            <?php else: ?>
            <p style="color:#A0AEC0;font-style:italic">Endereço não cadastrado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pedidos -->
<div class="adm-card" style="margin-top:1.5rem">
    <div class="adm-card-header"><span class="adm-card-title">Pedidos Online (<?= count($pedidos) ?>)</span></div>
    <div class="adm-card-body" style="padding:0">
        <?php if (empty($pedidos)): ?>
        <p style="padding:1.5rem;color:#A0AEC0;text-align:center">Nenhum pedido online registrado.</p>
        <?php else: ?>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr><th>Número</th><th>Data</th><th>Itens</th><th>Total</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['numero'], ENT_QUOTES) ?></td>
                    <td><?= date('d/m/Y', strtotime($p['criado_em'])) ?></td>
                    <td><?= (int)$p['qtd_itens'] ?></td>
                    <td>R$ <?= number_format((float)$p['total'], 2, ',', '.') ?></td>
                    <td><span class="adm-badge"><?= Pedido::statusLabel($p['status']) ?></span></td>
                    <td><a href="/admin/pedidos/<?= (int)$p['id'] ?>" class="adm-btn adm-btn-secondary adm-btn-sm">Ver</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
