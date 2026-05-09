<?php use App\Core\Helper; use App\Models\Pedido; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Minha Conta</span>
        </nav>
        <h1>Olá, <?= Helper::e(explode(' ', $cliente['nome'])[0]) ?>!</h1>
    </div>
</section>

<section class="section-painel">
    <div class="container">

        <?php if ($flash): ?>
        <div class="alert alert--success"><?= Helper::e($flash) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
        <div class="alert alert--erro"><?= Helper::e($erro) ?></div>
        <?php endif; ?>

        <div class="painel-grid">

            <!-- Sidebar -->
            <aside class="painel-sidebar">
                <div class="painel-cliente-info">
                    <div class="painel-avatar">
                        <?= mb_strtoupper(mb_substr($cliente['nome'], 0, 1)) ?>
                    </div>
                    <div>
                        <strong><?= Helper::e($cliente['nome']) ?></strong>
                        <span><?= Helper::e($cliente['email']) ?></span>
                        <?php if (!empty($cliente['data_nascimento'])): ?>
                        <span style="font-size:0.82rem;color:#8A7A6A;margin-top:2px;display:block">
                            🎂 <?= date('d/m', strtotime($cliente['data_nascimento'])) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <nav class="painel-nav">
                    <a href="<?= APP_URL ?>/minha-conta" class="painel-nav__link active">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Meus Pedidos
                    </a>
                    <a href="<?= APP_URL ?>/minha-conta/editar" class="painel-nav__link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Editar Dados
                    </a>
                    <a href="<?= APP_URL ?>/produtos" class="painel-nav__link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        Continuar comprando
                    </a>
                    <a href="<?= APP_URL ?>/minha-conta/logout" class="painel-nav__link painel-nav__link--sair">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Sair
                    </a>
                </nav>

                <?php if ($endereco): ?>
                <div class="painel-endereco-card">
                    <h4>Endereço cadastrado</h4>
                    <p>
                        <?= Helper::e($endereco['logradouro']) ?>, <?= Helper::e($endereco['numero']) ?>
                        <?php if ($endereco['complemento']): ?> — <?= Helper::e($endereco['complemento']) ?><?php endif; ?><br>
                        <?= Helper::e($endereco['bairro']) ?>, <?= Helper::e($endereco['cidade']) ?>/<?= Helper::e($endereco['estado']) ?><br>
                        CEP <?= Helper::e($endereco['cep']) ?>
                    </p>
                </div>
                <?php endif; ?>
            </aside>

            <!-- Conteúdo principal -->
            <main class="painel-main">
                <h2 class="painel-section-title">Meus Pedidos</h2>

                <?php if (empty($pedidos)): ?>
                <div class="painel-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="48" height="48" class="painel-empty__icon"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <p>Você ainda não fez nenhum pedido.</p>
                    <a href="<?= APP_URL ?>/produtos" class="btn btn-primary">Conhecer produtos</a>
                </div>
                <?php else: ?>
                <div class="pedidos-lista">
                    <?php foreach ($pedidos as $p): ?>
                    <?php
                        $isPago      = in_array($p['status'], ['pago','separando','enviado','entregue'], true);
                        $temRecibo   = $isPago && !empty($p['receipt_url']);
                        $metodoPago  = !empty($p['metodo_pagamento']) ? $p['metodo_pagamento'] : $p['forma_pagamento'];
                    ?>
                    <div class="pedido-card">
                        <div class="pedido-card__header">
                            <div>
                                <span class="pedido-numero"><?= Helper::e($p['numero']) ?></span>
                                <span class="pedido-data"><?= Helper::datetime($p['criado_em']) ?></span>
                            </div>
                            <span class="pedido-status <?= Pedido::statusClass($p['status']) ?>">
                                <?= Pedido::statusLabel($p['status']) ?>
                            </span>
                        </div>
                        <div class="pedido-card__body">
                            <div class="pedido-info">
                                <span><?= $p['qtd_itens'] ?> <?= $p['qtd_itens'] == 1 ? 'produto' : 'produtos' ?></span>
                                <span class="pedido-total"><?= Helper::money((float)$p['total']) ?></span>
                                <span><?= Pedido::pagamentoLabel($metodoPago) ?></span>
                            </div>
                            <?php if ($temRecibo): ?>
                            <div class="pedido-recibo">
                                <a href="<?= Helper::e($p['receipt_url']) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="btn-recibo">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="15" height="15" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    Ver Recibo
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="12" height="12" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </main>

        </div>
    </div>
</section>
