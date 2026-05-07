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

// ---- Banner Slider ----
(function () {
    const slider = document.getElementById('banner-slider');
    if (!slider) return;

    const slides   = slider.querySelectorAll('.banner-slide');
    const dots     = slider.querySelectorAll('.dot');
    const prev     = slider.querySelector('.slider-prev');
    const next     = slider.querySelector('.slider-next');

    if (slides.length <= 1) return;

    let current  = 0;
    let interval = null;

    function goTo(idx) {
        slides[current].classList.remove('active');
        if (dots[current]) dots[current].classList.remove('active');
        current = (idx + slides.length) % slides.length;
        slides[current].classList.add('active');
        if (dots[current]) dots[current].classList.add('active');
    }

    function start() { interval = setInterval(() => goTo(current + 1), 5000); }
    function stop()  { clearInterval(interval); }

    if (prev) prev.addEventListener('click', () => { stop(); goTo(current - 1); start(); });
    if (next) next.addEventListener('click', () => { stop(); goTo(current + 1); start(); });

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => { stop(); goTo(i); start(); });
    });

    start();
})();

// ---- Galeria de produto (troca de imagem principal) ----
function trocarImagem(btn) {
    const src  = btn.getAttribute('data-src');
    const img  = document.getElementById('galeria-img-principal');
    if (img) {
        img.style.opacity = '0';
        setTimeout(() => { img.src = src; img.style.opacity = '1'; }, 180);
    }
    document.querySelectorAll('.galeria-thumb').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
}

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

// ---- Ativar link de navegação corrente ----
(function () {
    const path  = window.location.pathname;
    const links = document.querySelectorAll('.nav-link');
    links.forEach(link => {
        const href = new URL(link.href, location.origin).pathname;
        if (href === '/' ? path === '/' : path.startsWith(href)) {
            link.classList.add('active');
        }
    });
})();

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
