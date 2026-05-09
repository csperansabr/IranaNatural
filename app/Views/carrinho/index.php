<?php use App\Core\Helper; ?>

<section class="page-hero page-hero--sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Carrinho</span>
        </nav>
        <h1>Carrinho de Compras</h1>
    </div>
</section>

<section class="section-carrinho">
    <div class="container">

        <?php if ($flash): ?>
        <div class="alert alert--success"><?= Helper::e($flash) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
        <div class="alert alert--erro"><?= Helper::e($erro) ?></div>
        <?php endif; ?>

        <?php if ($cliente): ?>
        <!-- Seção Meus Dados -->
        <div class="meus-dados-section">
            <div class="meus-dados-header">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Meus Dados
                </h3>
                <a href="<?= APP_URL ?>/minha-conta/editar" class="btn btn-light btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Editar Dados
                </a>
            </div>

            <?php if (!empty($dadosFaltando)): ?>
            <div class="alert alert--warning" role="alert">
                <strong>Dados incompletos:</strong> Para finalizar a compra, preencha os campos:
                <strong><?= implode(', ', array_map([App\Core\Helper::class, 'e'], $dadosFaltando)) ?></strong>.
                <a href="<?= APP_URL ?>/minha-conta/editar" class="link-verde" style="font-weight:600;margin-left:.5rem">Completar cadastro →</a>
            </div>
            <?php endif; ?>

            <div class="meus-dados-grid">
                <div class="meus-dados-item">
                    <span class="meus-dados-label">Nome</span>
                    <span class="meus-dados-valor<?= empty($cliente['nome']) ? ' meus-dados-vazio' : '' ?>">
                        <?= $cliente['nome'] ? Helper::e($cliente['nome']) : '—' ?>
                    </span>
                </div>
                <div class="meus-dados-item">
                    <span class="meus-dados-label">CPF</span>
                    <span class="meus-dados-valor<?= empty($cliente['cpf']) ? ' meus-dados-vazio' : '' ?>">
                        <?= $cliente['cpf'] ? Helper::e($cliente['cpf_fmt'] ?? $cliente['cpf']) : '—' ?>
                    </span>
                </div>
                <div class="meus-dados-item">
                    <span class="meus-dados-label">E-mail</span>
                    <span class="meus-dados-valor<?= empty($cliente['email']) ? ' meus-dados-vazio' : '' ?>">
                        <?= $cliente['email'] ? Helper::e($cliente['email']) : '—' ?>
                    </span>
                </div>
                <div class="meus-dados-item">
                    <span class="meus-dados-label">Telefone</span>
                    <span class="meus-dados-valor<?= empty($cliente['telefone']) ? ' meus-dados-vazio' : '' ?>">
                        <?= $cliente['telefone'] ? Helper::e($cliente['telefone']) : '—' ?>
                    </span>
                </div>
                <div class="meus-dados-item">
                    <span class="meus-dados-label">Nascimento</span>
                    <span class="meus-dados-valor<?= empty($cliente['data_nascimento']) ? ' meus-dados-vazio' : '' ?>">
                        <?= $cliente['data_nascimento'] ? date('d/m/Y', strtotime($cliente['data_nascimento'])) : '—' ?>
                    </span>
                </div>
                <?php if ($endereco): ?>
                <div class="meus-dados-item meus-dados-item--full">
                    <span class="meus-dados-label">Endereço</span>
                    <span class="meus-dados-valor">
                        <?= Helper::e($endereco['logradouro'] ?? '—') ?>,
                        <?= Helper::e($endereco['numero'] ?? '') ?>
                        <?php if ($endereco['complemento']): ?> — <?= Helper::e($endereco['complemento']) ?><?php endif; ?><br>
                        <?= Helper::e($endereco['bairro'] ?? '') ?>,
                        <?= Helper::e($endereco['cidade'] ?? '') ?>/<?= Helper::e($endereco['estado'] ?? '') ?> —
                        CEP <?= Helper::e($endereco['cep_fmt'] ?? $endereco['cep'] ?? '') ?>
                    </span>
                </div>
                <?php else: ?>
                <div class="meus-dados-item meus-dados-item--full">
                    <span class="meus-dados-label">Endereço</span>
                    <span class="meus-dados-valor meus-dados-vazio">Não cadastrado</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($itens)): ?>

        <div class="carrinho-vazio">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="64" height="64" class="carrinho-vazio__icon">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <h2>Seu carrinho está vazio</h2>
            <p>Explore nossa linha de produtos naturais e encontre algo especial para você.</p>
            <a href="<?= APP_URL ?>/produtos" class="btn btn-primary btn-lg">Ver produtos</a>
        </div>

        <?php else: ?>

        <div class="carrinho-layout">

            <!-- Itens -->
            <div class="carrinho-itens" id="carrinho-itens">
                <div class="carrinho-itens__header">
                    <span><?= count($itens) ?> <?= count($itens) === 1 ? 'produto' : 'produtos' ?></span>
                </div>

                <?php foreach ($itens as $item): ?>
                <div class="carrinho-item" data-item-id="<?= $item['id'] ?>">
                    <a href="<?= APP_URL ?>/produtos/<?= Helper::e($item['categoria_slug']) ?>/<?= Helper::e($item['slug']) ?>" class="carrinho-item__img-link">
                        <?php if ($item['imagem']): ?>
                        <img src="<?= Helper::upload($item['imagem']) ?>"
                             alt="<?= Helper::e($item['nome']) ?>"
                             class="carrinho-item__img"
                             loading="lazy">
                        <?php else: ?>
                        <img src="<?= APP_URL ?>/assets/images/placeholder.svg"
                             alt="<?= Helper::e($item['nome']) ?>"
                             class="carrinho-item__img">
                        <?php endif; ?>
                    </a>

                    <div class="carrinho-item__info">
                        <a href="<?= APP_URL ?>/produtos/<?= Helper::e($item['categoria_slug']) ?>/<?= Helper::e($item['slug']) ?>" class="carrinho-item__nome">
                            <?= Helper::e($item['nome']) ?>
                        </a>
                        <span class="carrinho-item__preco-unit"><?= Helper::money((float)$item['preco_unitario']) ?> cada</span>
                    </div>

                    <div class="carrinho-item__qty">
                        <button class="qty-btn qty-btn--minus" aria-label="Diminuir quantidade"
                                data-item-id="<?= $item['id'] ?>"
                                data-action="diminuir"
                                data-preco="<?= $item['preco_unitario'] ?>">−</button>
                        <input type="number" class="qty-input"
                               value="<?= (int)$item['quantidade'] ?>"
                               min="1" max="<?= (int)$item['estoque_atual'] ?>"
                               data-item-id="<?= $item['id'] ?>"
                               data-preco="<?= $item['preco_unitario'] ?>"
                               aria-label="Quantidade">
                        <button class="qty-btn qty-btn--plus" aria-label="Aumentar quantidade"
                                data-item-id="<?= $item['id'] ?>"
                                data-action="aumentar"
                                data-preco="<?= $item['preco_unitario'] ?>"
                                data-estoque="<?= (int)$item['estoque_atual'] ?>">+</button>
                    </div>

                    <div class="carrinho-item__subtotal" id="subtotal-<?= $item['id'] ?>">
                        <?= Helper::money((float)$item['quantidade'] * (float)$item['preco_unitario']) ?>
                    </div>

                    <button class="carrinho-item__remover" aria-label="Remover produto"
                            data-item-id="<?= $item['id'] ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumo -->
            <aside class="carrinho-resumo">
                <div class="carrinho-resumo__card">
                    <h3>Resumo do pedido</h3>

                    <div class="carrinho-resumo__linha">
                        <span>Subtotal</span>
                        <span id="resumo-subtotal"><?= Helper::money($total) ?></span>
                    </div>
                    <div class="carrinho-resumo__linha">
                        <span>Frete</span>
                        <span class="frete-info">A combinar</span>
                    </div>
                    <div class="carrinho-resumo__linha carrinho-resumo__linha--total">
                        <span>Total estimado</span>
                        <span id="resumo-total"><?= Helper::money($total) ?></span>
                    </div>

                    <?php if (!empty($dadosFaltando)): ?>
                    <a href="<?= APP_URL ?>/minha-conta/editar" class="btn btn-primary btn-block btn-lg checkout-btn checkout-btn--bloqueado"
                       title="Completar dados antes de finalizar">
                        Completar Cadastro
                    </a>
                    <p class="checkout-aviso-dados">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Preencha os dados cadastrais para continuar.
                    </p>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>/checkout" class="btn btn-primary btn-block btn-lg checkout-btn">
                        Finalizar compra
                    </a>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>/produtos" class="btn btn-light btn-block">
                        Continuar comprando
                    </a>

                    <div class="carrinho-resumo__seguranca">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Compra 100% segura</span>
                    </div>
                </div>

            </aside>

        </div>

        <?php endif; ?>

    </div>
</section>

<input type="hidden" id="csrf-token" value="<?= Helper::e(\App\Core\Session::csrfToken()) ?>">
