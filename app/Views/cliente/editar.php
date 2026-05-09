<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span aria-hidden="true">›</span>
            <a href="<?= APP_URL ?>/minha-conta">Minha Conta</a>
            <span aria-hidden="true">›</span>
            <span>Editar Dados</span>
        </nav>
        <h1>Editar Dados</h1>
    </div>
</section>

<section class="section-auth">
    <div class="container container--narrow">

        <?php if ($erro): ?>
        <div class="alert alert--erro" role="alert"><?= Helper::e($erro) ?></div>
        <?php endif; ?>

        <div class="auth-card auth-card--wide">
            <div class="auth-card__header">
                <h2>Seus dados</h2>
                <p>Mantenha seu cadastro atualizado para facilitar suas compras.</p>
            </div>

            <?php
            // Prioridade: old (flash de erro) > dados do DB
            $v = function(string $campo, string $fonte = 'cliente') use ($old, $cliente, $endereco): string {
                if (!empty($old[$campo])) return Helper::e($old[$campo]);
                if ($fonte === 'endereco') return Helper::e($endereco[$campo] ?? '');
                return Helper::e($cliente[$campo] ?? '');
            };
            $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS',
                        'MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
            $selEstado = !empty($old['estado']) ? $old['estado'] : ($endereco['estado'] ?? '');
            ?>

            <form method="POST" action="<?= APP_URL ?>/minha-conta/editar" class="auth-form" novalidate id="form-editar">
                <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Dados Pessoais</legend>

                    <div class="form-row">
                        <div class="form-group form-group--lg">
                            <label for="nome" class="form-label">Nome completo <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-input"
                                   value="<?= $v('nome') ?>"
                                   placeholder="Seu nome completo" autocomplete="name" required>
                        </div>
                        <div class="form-group">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" id="cpf" name="cpf" class="form-input"
                                   value="<?= $v('cpf') ?>"
                                   placeholder="000.000.000-00" maxlength="14"
                                   data-mask="cpf">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--lg">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?= $v('email') ?>"
                                   placeholder="seu@email.com" autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="telefone" class="form-label">WhatsApp / Telefone</label>
                            <input type="tel" id="telefone" name="telefone" class="form-input"
                                   value="<?= $v('telefone') ?>"
                                   placeholder="(00) 00000-0000" autocomplete="tel"
                                   data-mask="telefone">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" class="form-input"
                                   value="<?= $v('data_nascimento') ?>"
                                   max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Endereço Principal</legend>

                    <div class="form-row">
                        <div class="form-group form-group--sm">
                            <label for="cep" class="form-label">CEP <span class="required">*</span></label>
                            <input type="text" id="cep" name="cep" class="form-input"
                                   value="<?= $v('cep', 'endereco') ?>"
                                   placeholder="00000-000" maxlength="9"
                                   data-mask="cep" required>
                        </div>
                        <div class="form-group form-group--lg">
                            <label for="logradouro" class="form-label">Rua / Avenida <span class="required">*</span></label>
                            <input type="text" id="logradouro" name="logradouro" class="form-input"
                                   value="<?= $v('logradouro', 'endereco') ?>"
                                   placeholder="Nome da rua" autocomplete="street-address" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--sm">
                            <label for="numero" class="form-label">Número <span class="required">*</span></label>
                            <input type="text" id="numero" name="numero" class="form-input"
                                   value="<?= $v('numero', 'endereco') ?>"
                                   placeholder="Ex: 123" required>
                        </div>
                        <div class="form-group">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" id="complemento" name="complemento" class="form-input"
                                   value="<?= $v('complemento', 'endereco') ?>"
                                   placeholder="Apto, Bloco, etc.">
                        </div>
                        <div class="form-group">
                            <label for="bairro" class="form-label">Bairro <span class="required">*</span></label>
                            <input type="text" id="bairro" name="bairro" class="form-input"
                                   value="<?= $v('bairro', 'endereco') ?>"
                                   placeholder="Seu bairro" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--lg">
                            <label for="cidade" class="form-label">Cidade <span class="required">*</span></label>
                            <input type="text" id="cidade" name="cidade" class="form-input"
                                   value="<?= $v('cidade', 'endereco') ?>"
                                   placeholder="Sua cidade" autocomplete="address-level2" required>
                        </div>
                        <div class="form-group form-group--sm">
                            <label for="estado" class="form-label">Estado <span class="required">*</span></label>
                            <select id="estado" name="estado" class="form-input" required>
                                <option value="">UF</option>
                                <?php foreach ($estados as $uf): ?>
                                <option value="<?= $uf ?>"<?= $selEstado === $uf ? ' selected' : '' ?>><?= $uf ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div class="form-row" style="gap:1rem">
                    <button type="submit" class="btn btn-primary btn-block">Salvar alterações</button>
                    <a href="<?= APP_URL ?>/minha-conta" class="btn btn-light btn-block">Cancelar</a>
                </div>
            </form>

            <div class="auth-card__footer">
                <p>Deseja alterar sua senha? <a href="<?= APP_URL ?>/minha-conta/recuperar-senha" class="link-verde">Clique aqui →</a></p>
            </div>
        </div>

    </div>
</section>
