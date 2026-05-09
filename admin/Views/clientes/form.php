<?php
$pageTitle = $modo === 'criar' ? 'Novo Cliente' : 'Editar Cliente — ' . htmlspecialchars($cliente['nome'] ?? '', ENT_QUOTES, 'UTF-8');

// Prioridade: flash old > dados do DB
$v = function(string $campo, string $fonte = 'cliente') use ($old, $cliente, $endereco): string {
    if (!empty($old[$campo])) return htmlspecialchars($old[$campo], ENT_QUOTES, 'UTF-8');
    if ($fonte === 'endereco') return htmlspecialchars($endereco[$campo] ?? '', ENT_QUOTES, 'UTF-8');
    return htmlspecialchars($cliente[$campo] ?? '', ENT_QUOTES, 'UTF-8');
};

$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS',
            'MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
$selEstado = !empty($old['estado']) ? $old['estado'] : ($endereco['estado'] ?? '');
?>

<?php if (!empty($flash['msg'])): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="margin-bottom:1.5rem">
    <?php if ($modo === 'editar'): ?>
    <a href="/admin/clientes/<?= (int)$cliente['id'] ?>" class="adm-btn adm-btn-secondary adm-btn-sm">← Voltar</a>
    <?php else: ?>
    <a href="/admin/clientes" class="adm-btn adm-btn-secondary adm-btn-sm">← Voltar</a>
    <?php endif; ?>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <span class="adm-card-title"><?= $modo === 'criar' ? 'Novo Cliente' : 'Editar Cliente' ?></span>
    </div>
    <div class="adm-card-body">

        <?php $action = $modo === 'criar' ? '/admin/clientes/novo' : '/admin/clientes/' . (int)$cliente['id'] . '/editar'; ?>
        <form method="POST" action="<?= $action ?>" novalidate>

            <!-- Dados Pessoais -->
            <fieldset style="border:1px solid #E2E8F0;border-radius:8px;padding:1.25rem;margin-bottom:1.5rem">
                <legend style="padding:0 .5rem;font-weight:700;color:#2D3748;font-size:.9rem">Dados Pessoais</legend>

                <div class="adm-form-grid adm-form-grid-2">
                    <div class="adm-form-group" style="grid-column:1/-1">
                        <label>Nome completo <span style="color:#E53E3E">*</span></label>
                        <input type="text" name="nome" value="<?= $v('nome') ?>"
                               placeholder="Nome completo" required>
                    </div>
                </div>

                <div class="adm-form-grid adm-form-grid-3">
                    <div class="adm-form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf" value="<?= $v('cpf') ?>"
                               placeholder="000.000.000-00" maxlength="14" data-mask="cpf">
                    </div>
                    <div class="adm-form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" value="<?= $v('email') ?>"
                               placeholder="cliente@email.com">
                    </div>
                    <div class="adm-form-group">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" value="<?= $v('telefone') ?>"
                               placeholder="(00) 00000-0000" data-mask="telefone">
                    </div>
                </div>

                <div class="adm-form-grid adm-form-grid-3">
                    <div class="adm-form-group">
                        <label>Data de Nascimento</label>
                        <input type="date" name="data_nascimento" value="<?= $v('data_nascimento') ?>"
                               max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="adm-form-group">
                        <label><?= $modo === 'criar' ? 'Senha (opcional)' : 'Nova Senha (deixe em branco para manter)' ?></label>
                        <input type="password" name="senha" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    </div>
                    <div class="adm-form-group" style="display:flex;align-items:flex-end;padding-bottom:.5rem">
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:400">
                            <input type="checkbox" name="ativo" value="1"
                                   <?= (isset($old['ativo']) ? (int)$old['ativo'] : ($cliente['ativo'] ?? 1)) ? 'checked' : '' ?>>
                            Cliente ativo
                        </label>
                    </div>
                </div>
            </fieldset>

            <!-- Endereço -->
            <fieldset style="border:1px solid #E2E8F0;border-radius:8px;padding:1.25rem;margin-bottom:1.5rem">
                <legend style="padding:0 .5rem;font-weight:700;color:#2D3748;font-size:.9rem">Endereço</legend>

                <div class="adm-form-grid adm-form-grid-3">
                    <div class="adm-form-group">
                        <label>CEP</label>
                        <input type="text" name="cep" value="<?= $v('cep', 'endereco') ?>"
                               placeholder="00000-000" maxlength="9" data-mask="cep">
                    </div>
                    <div class="adm-form-group" style="grid-column:span 2">
                        <label>Logradouro (Rua / Avenida)</label>
                        <input type="text" name="logradouro" value="<?= $v('logradouro', 'endereco') ?>"
                               placeholder="Nome da rua">
                    </div>
                </div>

                <div class="adm-form-grid adm-form-grid-3">
                    <div class="adm-form-group">
                        <label>Número</label>
                        <input type="text" name="numero" value="<?= $v('numero', 'endereco') ?>"
                               placeholder="Ex: 123">
                    </div>
                    <div class="adm-form-group">
                        <label>Complemento</label>
                        <input type="text" name="complemento" value="<?= $v('complemento', 'endereco') ?>"
                               placeholder="Apto, Bloco, etc.">
                    </div>
                    <div class="adm-form-group">
                        <label>Bairro</label>
                        <input type="text" name="bairro" value="<?= $v('bairro', 'endereco') ?>"
                               placeholder="Nome do bairro">
                    </div>
                </div>

                <div class="adm-form-grid adm-form-grid-3">
                    <div class="adm-form-group" style="grid-column:span 2">
                        <label>Cidade</label>
                        <input type="text" name="cidade" value="<?= $v('cidade', 'endereco') ?>"
                               placeholder="Cidade">
                    </div>
                    <div class="adm-form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="">UF</option>
                            <?php foreach ($estados as $uf): ?>
                            <option value="<?= $uf ?>"<?= $selEstado === $uf ? ' selected' : '' ?>><?= $uf ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </fieldset>

            <div class="adm-form-group">
                <button type="submit" class="adm-btn adm-btn-primary adm-btn-lg">
                    <?= $modo === 'criar' ? 'Cadastrar Cliente' : 'Salvar Alterações' ?>
                </button>
            </div>

        </form>
    </div>
</div>
