/**
 * AFFECTA — animations du site public.
 *
 * GSAP + ScrollTrigger sont utilisés pour : l'entrée du hero, les parallaxes,
 * la progression de la timeline et les boutons magnétiques.
 * Si la bibliothèque est absente, la page reste parfaitement utilisable :
 * le noyau (app.js) assure les révélations et les compteurs en CSS/intervalles.
 */
(function () {
    'use strict';

    const doc = document;
    const root = doc.documentElement;
    const body = doc.body;
    const gsap = window.gsap;
    const ScrollTrigger = window.ScrollTrigger;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(pointer: fine)').matches;
    const $ = (selector, scope) => (scope || doc).querySelector(selector);
    const $$ = (selector, scope) => Array.from((scope || doc).querySelectorAll(selector));

    /* ── 1. Écran de chargement ─────────────────────────────────────────── */
    const Loader = {
        init() {
            const loader = $('.loader');
            if (!loader) return;

            const fill = $('.loader__fill', loader);
            const percent = $('.loader__percent', loader);
            const label = $('.loader__status', loader);
            const steps = ['Initialisation', 'Chargement des modules', 'Préparation de l\'interface', 'Prêt'];

            if (reduceMotion) {
                loader.classList.add('is-done');
                this.revealHero();
                return;
            }

            let progress = 0;
            const start = performance.now();
            const duration = 1150;

            const tick = (now) => {
                const elapsed = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - elapsed, 2.2);
                progress = Math.round(eased * 100);

                if (fill) fill.style.width = progress + '%';
                if (percent) percent.textContent = String(progress).padStart(3, '0') + '%';
                if (label) label.textContent = steps[Math.min(Math.floor(progress / 28), steps.length - 1)];

                if (elapsed < 1) {
                    window.requestAnimationFrame(tick);
                } else {
                    window.setTimeout(() => {
                        loader.classList.add('is-done');
                        root.classList.remove('is-locked');
                        this.revealHero();
                    }, 180);
                }
            };

            root.classList.add('is-locked');
            window.requestAnimationFrame(tick);
        },

        /* Entrée du hero : stagger fluide, léger blur-to-focus. */
        revealHero() {
            const hero = $('[data-hero]');
            if (!hero) return;

            if (!gsap || reduceMotion) {
                $$('[data-hero-item]', hero).forEach((el) => { el.style.opacity = 1; el.style.transform = 'none'; });
                return;
            }

            const items = $$('[data-hero-item]', hero);
            const timeline = gsap.timeline({ defaults: { ease: 'power3.out' } });

            timeline
                .from(items, {
                    y: 26,
                    opacity: 0,
                    duration: 0.9,
                    stagger: 0.085,
                    filter: 'blur(10px)',
                    clearProps: 'filter'
                })
                .from('.hero__visual', {
                    y: 42,
                    opacity: 0,
                    scale: 0.97,
                    duration: 1.1
                }, '-=0.85')
                .from('.float-chip', {
                    opacity: 0,
                    scale: 0.9,
                    duration: 0.7,
                    stagger: 0.12
                }, '-=0.6')
                .from('.mock__bars span', {
                    scaleY: 0,
                    transformOrigin: 'bottom',
                    duration: 0.7,
                    stagger: 0.05,
                    ease: 'power2.out'
                }, '-=0.9');
        }
    };

    /* ── 2. Curseur personnalisé (souris uniquement) ────────────────────── */
    const Cursor = {
        init() {
            if (!finePointer || reduceMotion) return;

            const dot = doc.createElement('div');
            const ring = doc.createElement('div');
            dot.className = 'cursor-dot';
            ring.className = 'cursor-ring';
            dot.setAttribute('aria-hidden', 'true');
            ring.setAttribute('aria-hidden', 'true');
            body.append(dot, ring);

            let mouseX = window.innerWidth / 2;
            let mouseY = window.innerHeight / 2;
            let ringX = mouseX;
            let ringY = mouseY;
            let visible = false;

            doc.addEventListener('pointermove', (event) => {
                mouseX = event.clientX;
                mouseY = event.clientY;

                if (!visible) {
                    visible = true;
                    body.classList.add('cursor-active');
                }
            }, { passive: true });

            doc.addEventListener('pointerleave', () => body.classList.remove('cursor-active'));

            const interactive = 'a, button, [role="button"], input, select, textarea, .card--interactive, .service, .tech';
            const textual = 'input[type="text"], input[type="email"], input[type="tel"], textarea';

            doc.addEventListener('pointerover', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) return;

                body.classList.toggle('cursor-hover', Boolean(target.closest(interactive)));
                body.classList.toggle('cursor-text', Boolean(target.closest(textual)));
            });

            const render = () => {
                // Le point suit instantanément, l'anneau avec inertie.
                ringX += (mouseX - ringX) * 0.16;
                ringY += (mouseY - ringY) * 0.16;

                dot.style.transform = 'translate3d(' + (mouseX - 3) + 'px, ' + (mouseY - 3) + 'px, 0)';
                ring.style.transform = 'translate3d(' + (ringX - 17) + 'px, ' + (ringY - 17) + 'px, 0)';

                window.requestAnimationFrame(render);
            };

            window.requestAnimationFrame(render);
        }
    };

    /* ── 3. Navigation : état au scroll, menu mobile, scroll-spy ────────── */
    const Nav = {
        init() {
            const nav = $('.site-nav');
            const progress = $('.scroll-progress');
            const toTop = $('.to-top');
            const burger = $('.nav__burger');
            const mobile = $('.nav__mobile');

            const onScroll = () => {
                const y = window.scrollY;
                if (nav) nav.classList.toggle('is-scrolled', y > 24);
                if (toTop) toTop.classList.toggle('is-visible', y > 700);

                if (progress) {
                    const height = doc.documentElement.scrollHeight - window.innerHeight;
                    progress.style.width = (height > 0 ? (y / height) * 100 : 0) + '%';
                }
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            if (toTop) {
                toTop.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
                });
            }

            // Menu plein écran mobile
            const setMenu = (open) => {
                if (!mobile || !burger) return;
                mobile.classList.toggle('is-open', open);
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
                root.classList.toggle('is-locked', open);
                $$('a', mobile).forEach((link, index) => {
                    link.style.setProperty('--d', (80 + index * 55) + 'ms');
                });
            };

            if (burger) burger.addEventListener('click', () => setMenu(!mobile.classList.contains('is-open')));
            if (mobile) $$('a', mobile).forEach((link) => link.addEventListener('click', () => setMenu(false)));
            doc.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setMenu(false);
            });

            // Défilement fluide vers les ancres (formats « #id » et « /#id »)
            $$('a[href*="#"]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    const href = link.getAttribute('href') || '';
                    const hash = href.slice(href.indexOf('#'));

                    if (!hash || hash === '#') return;
                    if (href.startsWith('/') && !href.startsWith('/#')) return;

                    const target = doc.querySelector(hash);
                    if (!target) return;

                    event.preventDefault();
                    const offset = (nav ? nav.offsetHeight : 0) + 18;
                    const top = target.getBoundingClientRect().top + window.scrollY - offset;

                    window.scrollTo({ top, behavior: reduceMotion ? 'auto' : 'smooth' });
                    history.replaceState(null, '', id);
                });
            });

            // Section active dans la navigation
            const links = $$('.nav__link').filter((link) => (link.getAttribute('href') || '').includes('#'));
            const targets = new Map();

            links.forEach((link) => {
                const href = link.getAttribute('href') || '';
                const target = doc.querySelector(href.slice(href.indexOf('#')));
                if (target && target.id) targets.set(target, link);
            });

            if (targets.size > 0 && 'IntersectionObserver' in window) {
                const spy = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        links.forEach((link) => link.classList.remove('is-active'));
                        const link = targets.get(entry.target);
                        if (link) link.classList.add('is-active');
                    });
                }, { rootMargin: '-45% 0px -50% 0px' });

                targets.forEach((_link, section) => spy.observe(section));
            }
        }
    };

    /* ── 4. Parallaxe, timeline, magnétisme (GSAP) ──────────────────────── */
    const Motion = {
        init() {
            if (!gsap || !ScrollTrigger || reduceMotion) return;

            gsap.registerPlugin(ScrollTrigger);

            // Parallaxe du visuel du hero
            const visual = $('[data-parallax="hero"]');
            if (visual) {
                gsap.to(visual, {
                    yPercent: -8,
                    ease: 'none',
                    scrollTrigger: { trigger: visual, start: 'top bottom', end: 'bottom top', scrub: 0.6 }
                });
            }

            // Parallaxe des panneaux de démonstration
            $$('[data-parallax="soft"]').forEach((element, index) => {
                gsap.to(element, {
                    y: index % 2 === 0 ? -26 : 22,
                    ease: 'none',
                    scrollTrigger: { trigger: element, start: 'top bottom', end: 'bottom top', scrub: 0.8 }
                });
            });

            // Ligne de progression de la timeline
            const timeline = $('[data-timeline]');
            const bar = $('.timeline__progress', timeline || doc);
            if (timeline && bar) {
                gsap.to(bar, {
                    height: '100%',
                    ease: 'none',
                    scrollTrigger: {
                        trigger: timeline,
                        start: 'top 72%',
                        end: 'bottom 76%',
                        scrub: 0.4
                    }
                });
            }

            // Étapes : apparition décalée
            const steps = $$('.step');
            if (steps.length) {
                ScrollTrigger.batch(steps, {
                    start: 'top 86%',
                    onEnter: (batch) => gsap.fromTo(batch,
                        { y: 26, opacity: 0 },
                        { y: 0, opacity: 1, duration: 0.75, stagger: 0.08, ease: 'power3.out', overwrite: true }
                    ),
                    once: true
                });
            }

            // Légère rotation 3D de la maquette au mouvement de la souris
            const stage = $('[data-tilt]');
            if (stage && finePointer) {
                const rotateX = gsap.quickTo(stage, 'rotationX', { duration: 0.7, ease: 'power3.out' });
                const rotateY = gsap.quickTo(stage, 'rotationY', { duration: 0.7, ease: 'power3.out' });

                gsap.set(stage, { transformPerspective: 1100 });

                window.addEventListener('pointermove', (event) => {
                    const rect = stage.getBoundingClientRect();
                    const x = (event.clientX - rect.left) / rect.width - 0.5;
                    const y = (event.clientY - rect.top) / rect.height - 0.5;
                    rotateY(x * 9);
                    rotateX(-y * 7);
                }, { passive: true });
            }

            // Boutons magnétiques
            if (finePointer) {
                $$('.btn--magnetic').forEach((button) => {
                    const strength = 7;

                    button.addEventListener('pointermove', (event) => {
                        const rect = button.getBoundingClientRect();
                        const x = (event.clientX - rect.left) / rect.width - 0.5;
                        const y = (event.clientY - rect.top) / rect.height - 0.5;
                        button.style.setProperty('--mx', (x * strength).toFixed(2) + 'px');
                        button.style.setProperty('--my', (y * strength).toFixed(2) + 'px');
                    });

                    button.addEventListener('pointerleave', () => {
                        button.style.setProperty('--mx', '0px');
                        button.style.setProperty('--my', '0px');
                    });
                });
            }

            ScrollTrigger.refresh();
        }
    };

    /* ── Amorçage ───────────────────────────────────────────────────────── */
    const boot = () => {
        Loader.init();
        Cursor.init();
        Nav.init();
        Motion.init();
    };

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // Filet de sécurité : le voile de chargement ne doit jamais bloquer la page.
    window.setTimeout(() => {
        const loader = $('.loader');
        if (loader && !loader.classList.contains('is-done')) {
            loader.classList.add('is-done');
            root.classList.remove('is-locked');
        }
    }, 3000);
})();
