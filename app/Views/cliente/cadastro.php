<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span aria-hidden="true">›</span>
            <span>Criar Conta</span>
        </nav>
        <h1>Criar Conta</h1>
    </div>
</section>

<section class="section-auth">
    <div class="container container--narrow">

        <?php if ($erro): ?>
        <div class="alert alert--erro" role="alert">
            <?= Helper::e($erro) ?>
            <?php if (!empty($erroLink)): ?>
            <a href="<?= Helper::e($erroLink) ?>" class="link-verde" style="font-weight:600;white-space:nowrap">
                <?= Helper::e($erroLinkTexto ?? 'clique aqui') ?> →
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="auth-card auth-card--wide">
            <div class="auth-card__header">
                <h2>Seus dados</h2>
                <p>Preencha os dados abaixo para criar sua conta e finalizar compras.</p>
            </div>

            <form method="POST" action="<?= APP_URL ?>/minha-conta/cadastro" class="auth-form" novalidate id="form-cadastro">
                <input type="hidden" name="_csrf" value="<?= Helper::e($csrf) ?>">

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Dados Pessoais</legend>

                    <div class="form-row">
                        <div class="form-group form-group--lg">
                            <label for="nome" class="form-label">Nome completo <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-input"
                                   value="<?= Helper::e($old['nome'] ?? '') ?>"
                                   placeholder="Seu nome completo" autocomplete="name" required>
                        </div>
                        <div class="form-group">
                            <label for="cpf" class="form-label">CPF <span class="required">*</span></label>
                            <input type="text" id="cpf" name="cpf" class="form-input"
                                   value="<?= Helper::e($old['cpf'] ?? '') ?>"
                                   placeholder="000.000.000-00" maxlength="14"
                                   data-mask="cpf" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--lg">
                            <label for="email" class="form-label">E-mail <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?= Helper::e($old['email'] ?? '') ?>"
                                   placeholder="seu@email.com" autocomplete="email" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone" class="form-label">WhatsApp / Telefone</label>
                            <input type="tel" id="telefone" name="telefone" class="form-input"
                                   value="<?= Helper::e($old['telefone'] ?? '') ?>"
                                   placeholder="(00) 00000-0000" autocomplete="tel"
                                   data-mask="telefone">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" class="form-input"
                                   value="<?= Helper::e($old['data_nascimento'] ?? '') ?>"
                                   max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="senha" class="form-label">Senha <span class="required">*</span></label>
                            <div class="input-password-wrap">
                                <input type="password" id="senha" name="senha" class="form-input"
                                       placeholder="Mínimo 8 caracteres" autocomplete="new-password"
                                       minlength="8" required>
                                <button type="button" class="toggle-password" aria-label="Mostrar/ocultar senha" data-target="senha">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_senha" class="form-label">Confirmar senha <span class="required">*</span></label>
                            <div class="input-password-wrap">
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-input"
                                       placeholder="Repita a senha" autocomplete="new-password" required>
                                <button type="button" class="toggle-password" aria-label="Mostrar/ocultar senha" data-target="confirmar_senha">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Endereço Principal</legend>

                    <div class="form-row">
                        <div class="form-group form-group--sm">
                            <label for="cep" class="form-label">CEP <span class="required">*</span></label>
                            <input type="text" id="cep" name="cep" class="form-input"
                                   value="<?= Helper::e($old['cep'] ?? '') ?>"
                                   placeholder="00000-000" maxlength="9"
                                   data-mask="cep" required>
                        </div>
                        <div class="form-group form-group--lg">
                            <label for="logradouro" class="form-label">Rua / Avenida <span class="required">*</span></label>
                            <input type="text" id="logradouro" name="logradouro" class="form-input"
                                   value="<?= Helper::e($old['logradouro'] ?? '') ?>"
                                   placeholder="Nome da rua" autocomplete="street-address" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--sm">
                            <label for="numero" class="form-label">Número <span class="required">*</span></label>
                            <input type="text" id="numero" name="numero" class="form-input"
                                   value="<?= Helper::e($old['numero'] ?? '') ?>"
                                   placeholder="Ex: 123" required>
                        </div>
                        <div class="form-group">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" id="complemento" name="complemento" class="form-input"
                                   value="<?= Helper::e($old['complemento'] ?? '') ?>"
                                   placeholder="Apto, Bloco, etc.">
                        </div>
                        <div class="form-group">
                            <label for="bairro" class="form-label">Bairro <span class="required">*</span></label>
                            <input type="text" id="bairro" name="bairro" class="form-input"
                                   value="<?= Helper::e($old['bairro'] ?? '') ?>"
                                   placeholder="Seu bairro" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-group--lg">
                            <label for="cidade" class="form-label">Cidade <span class="required">*</span></label>
                            <input type="text" id="cidade" name="cidade" class="form-input"
                                   value="<?= Helper::e($old['cidade'] ?? '') ?>"
                                   placeholder="Sua cidade" autocomplete="address-level2" required>
                        </div>
                        <div class="form-group form-group--sm">
                            <label for="estado" class="form-label">Estado <span class="required">*</span></label>
                            <select id="estado" name="estado" class="form-input" required>
                                <option value="">UF</option>
                                <?php
                                $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                $selEstado = $old['estado'] ?? '';
                                foreach ($estados as $uf):
                                ?>
                                <option value="<?= $uf ?>"<?= $selEstado === $uf ? ' selected' : '' ?>><?= $uf ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primary btn-block">Criar minha conta</button>
            </form>

            <div class="auth-card__footer">
                <p>Já tem conta? <a href="<?= APP_URL ?>/minha-conta/login" class="link-verde">Entrar</a></p>
            </div>
        </div>

    </div>
</section>
