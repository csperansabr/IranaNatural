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
(function () {
    document.querySelectorAll('.card-gallery[data-images]').forEach(gallery => {
        let images;
        try { images = JSON.parse(gallery.getAttribute('data-images')); } catch (e) { return; }
        if (!images || images.length < 2) return;

        const img = gallery.querySelector('img');
        if (!img) return;

        let current = 0;

        // Botão anterior
        const btnPrev = document.createElement('button');
        btnPrev.type = 'button';
        btnPrev.className = 'card-gallery-btn prev';
        btnPrev.innerHTML = '&#8249;';
        btnPrev.setAttribute('aria-label', 'Imagem anterior');

        // Botão próximo
        const btnNext = document.createElement('button');
        btnNext.type = 'button';
        btnNext.className = 'card-gallery-btn next';
        btnNext.innerHTML = '&#8250;';
        btnNext.setAttribute('aria-label', 'Próxima imagem');

        // Dots
        const dotsWrap = document.createElement('div');
        dotsWrap.className = 'card-gallery-dots';
        const dots = images.map((_, i) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'card-gallery-dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('aria-label', 'Imagem ' + (i + 1));
            dot.addEventListener('click', e => { e.preventDefault(); goTo(i); });
            dotsWrap.appendChild(dot);
            return dot;
        });

        function goTo(idx) {
            current = (idx + images.length) % images.length;
            img.style.opacity = '0';
            setTimeout(() => { img.src = images[current]; img.style.opacity = '1'; }, 150);
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
        }

        btnPrev.addEventListener('click', e => { e.preventDefault(); goTo(current - 1); });
        btnNext.addEventListener('click', e => { e.preventDefault(); goTo(current + 1); });

        gallery.appendChild(btnPrev);
        gallery.appendChild(btnNext);
        gallery.appendChild(dotsWrap);

        // Swipe em touch
        let touchStartX = 0;
        gallery.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        gallery.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(dx) > 40) goTo(dx < 0 ? current + 1 : current - 1);
        }, { passive: true });
    });
})();
