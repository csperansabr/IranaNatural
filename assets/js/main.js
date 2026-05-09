/* Iraná Natural — JavaScript Principal */
'use strict';

// ---- Navbar scroll shadow ----
const header = document.getElementById('site-header');
if (header) {
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });
}

// ---- Mobile nav toggle ----
const navToggle = document.getElementById('nav-toggle');
const navMenu   = document.getElementById('nav-menu');
if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
        const open = navMenu.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', String(open));
    });
    // Fechar ao clicar fora
    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('open');
            navToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

// ---- Galeria de produto (página de detalhe) ----
(function () {
    const mainImg  = document.getElementById('galeria-img-principal');
    if (!mainImg) return;

    const thumbBtns = Array.from(document.querySelectorAll('.galeria-thumb'));
    if (thumbBtns.length < 2) return;

    const srcs    = thumbBtns.map(b => b.getAttribute('data-src'));
    const dots    = Array.from(document.querySelectorAll('.galeria-dot'));
    const prevBtn = document.getElementById('galeria-prev');
    const nextBtn = document.getElementById('galeria-next');
    let current   = 0;

    function goTo(idx) {
        current = (idx + srcs.length) % srcs.length;
        mainImg.style.opacity = '0';
        setTimeout(() => { mainImg.src = srcs[current]; mainImg.style.opacity = '1'; }, 180);
        thumbBtns.forEach((b, i) => {
            b.classList.toggle('active', i === current);
        });
        dots.forEach((d, i) => {
            d.classList.toggle('active', i === current);
            d.setAttribute('aria-selected', i === current ? 'true' : 'false');
        });
    }

    if (prevBtn) prevBtn.addEventListener('click', () => goTo(current - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goTo(current + 1));

    dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));
    thumbBtns.forEach((btn, i) => btn.addEventListener('click', () => goTo(i)));

    // Swipe support (mobile)
    let startX = 0;
    const mainArea = document.getElementById('galeria-main');
    if (mainArea) {
        mainArea.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
        mainArea.addEventListener('touchend',   e => {
            const dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 40) goTo(dx < 0 ? current + 1 : current - 1);
        }, { passive: true });
    }
})();

// ---- Tabs de produto ----
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const tab = this.getAttribute('data-tab');
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const content = document.getElementById('tab-' + tab);
        if (content) content.classList.add('active');
    });
});

// ---- Galeria de imagens nos cards da listagem ----
// Botões de navegação e dots desabilitados intencionalmente nos cards.
// Os controles de galeria estão disponíveis somente na página de detalhe do produto.

// ---- Dropdown minha conta ----
(function () {
    const toggle   = document.getElementById('conta-toggle');
    const dropdown = document.getElementById('conta-dropdown');
    if (!toggle || !dropdown) return;

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => dropdown.classList.remove('open'));
})();

// ---- Badge do carrinho ----
(function () {
    const badge = document.getElementById('carrinho-badge');
    if (!badge) return;

    async function atualizarBadge() {
        try {
            const res  = await fetch('/carrinho/mini');
            const data = await res.json();
            const qtd  = data.count || 0;
            badge.textContent    = qtd > 99 ? '99+' : qtd;
            badge.style.display  = qtd > 0 ? 'flex' : 'none';
        } catch (_) {}
    }
    atualizarBadge();
})();

// ---- Toast helper ----
function mostrarToast(msg, tipo = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast toast--' + tipo;
    toast.textContent = msg;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('toast--saindo');
        setTimeout(() => toast.remove(), 320);
    }, 3200);
}

// ---- Adicionar ao carrinho (página de produto) ----
(function () {
    document.querySelectorAll('.form-add-carrinho').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn    = form.querySelector('.btn-carrinho');
            const fd     = new FormData(form);
            const orig   = btn ? btn.textContent : '';

            if (btn) { btn.disabled = true; btn.textContent = 'Adicionando…'; }

            try {
                const res  = await fetch('/carrinho/adicionar', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.ok) {
                    mostrarToast(data.msg || 'Produto adicionado!', 'success');
                    // Atualiza badge
                    const badge = document.getElementById('carrinho-badge');
                    if (badge) {
                        badge.textContent   = data.count > 99 ? '99+' : data.count;
                        badge.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                } else {
                    mostrarToast(data.msg || 'Erro ao adicionar.', 'erro');
                }
            } catch (_) {
                mostrarToast('Erro de comunicação. Tente novamente.', 'erro');
            } finally {
                if (btn) { btn.disabled = false; btn.textContent = orig; }
            }
        });
    });
})();

// ---- Carrinho: atualizar quantidade e remover ----
(function () {
    const csrf = document.getElementById('csrf-token')?.value;
    if (!csrf) return; // Só executa na página do carrinho

    async function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ ...body, _csrf: csrf }),
        }).then(r => r.json());
    }

    function atualizarResumo(data) {
        const subtotalEl = document.getElementById('resumo-subtotal');
        const totalEl    = document.getElementById('resumo-total');
        if (subtotalEl) subtotalEl.textContent = data.total;
        if (totalEl)    totalEl.textContent    = data.total;

        const badge = document.getElementById('carrinho-badge');
        if (badge) {
            badge.textContent   = data.count > 99 ? '99+' : data.count;
            badge.style.display = data.count > 0 ? 'flex' : 'none';
        }
    }

    // Botões +/–
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const itemId = this.dataset.itemId;
            const input  = document.querySelector(`.qty-input[data-item-id="${itemId}"]`);
            const estoque = parseInt(this.dataset.estoque || 9999, 10);
            if (!input) return;

            let qtd = parseInt(input.value, 10);
            if (this.dataset.action === 'aumentar') qtd = Math.min(qtd + 1, estoque);
            if (this.dataset.action === 'diminuir') qtd = Math.max(qtd - 1, 0);

            input.value = qtd;

            const data = await postJson('/carrinho/atualizar', { item_id: itemId, quantidade: qtd });
            if (!data.ok) { mostrarToast(data.msg || 'Erro ao atualizar.', 'erro'); return; }

            // Atualizar subtotal da linha
            const subtotalEl = document.getElementById('subtotal-' + itemId);
            if (subtotalEl) subtotalEl.textContent = data.subtotal;

            if (data.removido) {
                document.querySelector(`.carrinho-item[data-item-id="${itemId}"]`)?.remove();
            }
            atualizarResumo(data);
        });
    });

    // Input direto
    document.querySelectorAll('.qty-input').forEach(input => {
        let timeout;
        input.addEventListener('change', async function () {
            clearTimeout(timeout);
            const itemId = this.dataset.itemId;
            const qtd    = Math.max(0, parseInt(this.value, 10) || 0);
            this.value = qtd;

            const data = await postJson('/carrinho/atualizar', { item_id: itemId, quantidade: qtd });
            if (!data.ok) { mostrarToast(data.msg || 'Erro ao atualizar.', 'erro'); return; }

            const subtotalEl = document.getElementById('subtotal-' + itemId);
            if (subtotalEl) subtotalEl.textContent = data.subtotal;
            if (data.removido) document.querySelector(`.carrinho-item[data-item-id="${itemId}"]`)?.remove();
            atualizarResumo(data);
        });
    });

    // Remover
    document.querySelectorAll('.carrinho-item__remover').forEach(btn => {
        btn.addEventListener('click', async function () {
            const itemId = this.dataset.itemId;
            const item   = document.querySelector(`.carrinho-item[data-item-id="${itemId}"]`);

            const data = await postJson('/carrinho/remover', { item_id: itemId });
            if (!data.ok) { mostrarToast(data.msg || 'Erro ao remover.', 'erro'); return; }

            item?.remove();
            atualizarResumo(data);

            // Recarregar se ficou vazio
            if (data.count === 0) window.location.reload();
        });
    });
})();

// Máscaras delegadas para assets/js/masks.js

// ---- Toggle senha (mostrar/ocultar) ----
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function () {
        const targetId = this.dataset.target;
        const input    = document.getElementById(targetId);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
    });
});
