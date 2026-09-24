/**
 * AFFECTA — noyau d'interactions partagé (site public, console, administration).
 *
 * Aucune dépendance externe. Chaque module s'active uniquement si les éléments
 * correspondants existent dans la page : pas de code mort exécuté inutilement.
 */
(function () {
    'use strict';

    const doc = document;
    const root = doc.documentElement;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const $ = (selector, scope) => (scope || doc).querySelector(selector);
    const $$ = (selector, scope) => Array.from((scope || doc).querySelectorAll(selector));

    /* ─────────────────────────────────────────────────────────────────────
       1. Thème clair / sombre — préférence locale puis système
       ──────────────────────────────────────────────────────────────────── */
    const Theme = {
        key: 'affecta-theme',

        init() {
            const stored = this.read();
            const theme = stored || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
            this.apply(theme, false);

            $$('[data-theme-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const next = this.current() === 'dark' ? 'light' : 'dark';
                    this.apply(next, true);
                    window.localStorage.setItem(this.key, next);
                });
            });

            // Suit le système tant que l'utilisateur n'a pas choisi manuellement.
            window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (event) => {
                if (this.read()) return;
                this.apply(event.matches ? 'light' : 'dark', false);
            });
        },

        read() {
            try {
                const value = window.localStorage.getItem(this.key);
                return value === 'light' || value === 'dark' ? value : null;
            } catch (error) {
                return null;
            }
        },

        current() {
            return root.getAttribute('data-theme') || 'dark';
        },

        apply(theme, animate) {
            if (animate && !reduceMotion.matches) {
                // Voile radial : transition courte et non bloquante.
                const veil = doc.createElement('div');
                veil.className = 'theme-veil';
                veil.setAttribute('aria-hidden', 'true');
                doc.body.appendChild(veil);
                window.requestAnimationFrame(() => veil.classList.add('is-active'));
                window.setTimeout(() => veil.remove(), 480);
            }

            root.setAttribute('data-theme', theme);
            $$('[data-theme-toggle]').forEach((button) => {
                button.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
                button.setAttribute('aria-label', theme === 'light' ? 'Activer le thème sombre' : 'Activer le thème clair');
            });

            const meta = $('meta[name="theme-color"]');
            if (meta) meta.setAttribute('content', theme === 'light' ? '#f6f7f9' : '#04050a');
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       2. Révélation au scroll + compteurs animés
       ──────────────────────────────────────────────────────────────────── */
    const Reveal = {
        init() {
            const items = $$('[data-reveal], [data-counter], .stat, .step, [data-chart]');
            if (items.length === 0) return;

            if (!('IntersectionObserver' in window)) {
                items.forEach((item) => item.classList.add('is-visible', 'is-done'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    const element = entry.target;
                    element.classList.add('is-visible');

                    const delay = Number(element.dataset.revealDelay || 0);
                    if (delay > 0) element.style.setProperty('--reveal-delay', delay + 'ms');

                    if (element.dataset.counter !== undefined) Counters.run(element);
                    if (element.dataset.chart !== undefined) Charts.run(element);

                    observer.unobserve(element);
                });
            }, { threshold: 0.16, rootMargin: '0px 0px -6% 0px' });

            items.forEach((item) => observer.observe(item));
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       3. Compteurs
       ──────────────────────────────────────────────────────────────────── */
    const Counters = {
        run(element) {
            const target = parseFloat(element.dataset.counter || '0');
            const decimals = Number(element.dataset.counterDecimals || 0);
            const duration = Number(element.dataset.counterDuration || 1600);
            const prefix = element.dataset.counterPrefix || '';
            const suffix = element.dataset.counterSuffix || '';

            if (reduceMotion.matches) {
                element.textContent = prefix + target.toFixed(decimals) + suffix;
                return;
            }

            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                // easeOutExpo : rapide au début, très doux à l'arrivée.
                const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const value = target * eased;

                element.textContent = prefix + this.format(value, decimals) + suffix;

                if (progress < 1) window.requestAnimationFrame(tick);
            };

            window.requestAnimationFrame(tick);
        },

        format(value, decimals) {
            const fixed = value.toFixed(decimals);
            const [integer, decimal] = fixed.split('.');

            // Espace insécable fine comme séparateur de milliers (France).
            const spaced = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '\u202F');

            return decimal ? spaced + ',' + decimal : spaced;
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       4. Graphiques à barres (console)
       ──────────────────────────────────────────────────────────────────── */
    const Charts = {
        run(container) {
            const bars = $$('.chart__bar', container);
            const max = Math.max(...bars.map((bar) => Number(bar.dataset.value || 0)), 1);

            bars.forEach((bar, index) => {
                const value = Number(bar.dataset.value || 0);
                const height = Math.max((value / max) * 100, 2);

                window.setTimeout(() => {
                    bar.style.height = height + '%';
                }, reduceMotion.matches ? 0 : index * 45);
            });
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       5. Notifications (toasts)
       ──────────────────────────────────────────────────────────────────── */
    const Toast = {
        icons: {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16.5v.5"/></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 11v5M12 7.5v.5"/></svg>'
        },

        stack() {
            let stack = $('.toast-stack');
            if (!stack) {
                stack = doc.createElement('div');
                stack.className = 'toast-stack';
                stack.setAttribute('role', 'status');
                stack.setAttribute('aria-live', 'polite');
                doc.body.appendChild(stack);
            }
            return stack;
        },

        show(type, message, timeout) {
            if (!message) return;
            const stack = this.stack();
            const toast = doc.createElement('div');
            toast.className = 'toast toast--' + (this.icons[type] ? type : 'info');
            toast.innerHTML = '<span class="toast__icon" aria-hidden="true" style="width:18px;height:18px;flex-shrink:0">'
                + (this.icons[type] || this.icons.info)
                + '</span><span></span>';
            toast.lastElementChild.textContent = message;
            stack.appendChild(toast);

            const remove = () => {
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 260);
            };

            window.setTimeout(remove, timeout || 5200);
            toast.addEventListener('click', remove);
        },

        flush() {
            const payload = doc.body.dataset.flash || '[]';

            try {
                JSON.parse(payload).forEach((flash) => this.show(flash.type, flash.message));
            } catch (error) {
                /* payload invalide : ignoré */
            }
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       6. Formulaires AJAX (contact, console)
       ──────────────────────────────────────────────────────────────────── */
    const AjaxForm = {
        init() {
            $$('form[data-ajax]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.submit(form);
                });

                // Le honeypot horodaté renseigne le délai de remplissage.
                const stamp = $('input[name="form_started_at"]', form);
                if (stamp && !stamp.value) stamp.value = String(Math.floor(Date.now() / 1000));

                $$('input, select, textarea', form).forEach((input) => {
                    input.addEventListener('input', () => this.clearError(input));
                });
            });
        },

        async submit(form) {
            const submitter = $('[type="submit"]', form);
            const original = submitter ? submitter.innerHTML : '';
            const action = form.getAttribute('action') || window.location.pathname;

            this.clearErrors(form);

            if (submitter) {
                submitter.disabled = true;
                submitter.innerHTML = '<span class="spinner" aria-hidden="true"></span><span>Envoi en cours…</span>';
                submitter.classList.add('is-loading');
            }

            try {
                const response = await fetch(action, {
                    method: (form.getAttribute('method') || 'POST').toUpperCase(),
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const payload = await response.json().catch(() => ({
                    success: false,
                    message: 'Réponse inattendue du serveur.'
                }));

                if (response.ok && payload.success) {
                    this.onSuccess(form, payload);
                } else {
                    this.onFailure(form, payload);
                }
            } catch (error) {
                Toast.show('error', 'Connexion impossible. Vérifiez votre réseau puis réessayez.');
            } finally {
                if (submitter) {
                    submitter.disabled = false;
                    submitter.innerHTML = original;
                    submitter.classList.remove('is-loading');
                }
            }
        },

        onSuccess(form, payload) {
            Toast.show('success', payload.message || 'Opération réussie.');

            if (form.dataset.ajaxReset !== 'false') form.reset();
            $$('.field', form).forEach((field) => field.classList.remove('is-invalid'));

            if (payload.redirect) {
                window.setTimeout(() => { window.location.href = payload.redirect; }, 700);
            } else if (payload.reload !== false) {
                const target = form.dataset.ajaxReload;
                if (target) {
                    window.setTimeout(() => window.location.reload(), payload.delay || 900);
                }
            }
        },

        onFailure(form, payload) {
            const errors = payload.errors || {};
            let firstInvalid = null;

            Object.keys(errors).forEach((name) => {
                const input = form.querySelector('[name="' + name + '"]');
                if (!input) return;

                const field = input.closest('.field') || input.parentElement;
                field.classList.add('is-invalid');

                let message = field.querySelector('.field__error');
                if (!message) {
                    message = doc.createElement('p');
                    message.className = 'field__error';
                    field.appendChild(message);
                }
                message.textContent = errors[name][0] || errors[name];
                input.setAttribute('aria-invalid', 'true');

                if (!firstInvalid) firstInvalid = input;
            });

            Toast.show('error', payload.message || 'Merci de corriger les champs signalés.');

            if (firstInvalid) {
                firstInvalid.focus({ preventScroll: true });
                firstInvalid.scrollIntoView({ behavior: reduceMotion.matches ? 'auto' : 'smooth', block: 'center' });
            }
        },

        clearError(input) {
            const field = input.closest('.field');
            if (!field || !field.classList.contains('is-invalid')) return;

            field.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
            const message = field.querySelector('.field__error');
            if (message) message.remove();
        },

        clearErrors(form) {
            $$('.field.is-invalid', form).forEach((field) => {
                field.classList.remove('is-invalid');
                const message = field.querySelector('.field__error');
                if (message) message.remove();
            });
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       7. Modales accessibles
       ──────────────────────────────────────────────────────────────────── */
    const Modal = {
        init() {
            $$('[data-modal-open]').forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.open(trigger.dataset.modalOpen, trigger);
                });
            });

            $$('.modal').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal || event.target.closest('[data-modal-close]')) {
                        this.close(modal);
                    }
                });
            });

            doc.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                const open = $('.modal.is-open');
                if (open) this.close(open);
            });

            // Ancre directe : /app/affectations#modal-affectation ouvre la modale au chargement.
            if (window.location.hash.length > 1 && doc.getElementById(window.location.hash.slice(1))?.classList.contains('modal')) {
                this.open(window.location.hash.slice(1));
            }
        },

        open(id, trigger) {
            const modal = doc.getElementById(id);
            if (!modal) return;

            modal.classList.add('is-open');
            modal.removeAttribute('aria-hidden');
            root.classList.add('is-locked');

            const focusable = $('input, select, textarea, button', modal);
            if (focusable) window.setTimeout(() => focusable.focus(), 120);

            modal.dataset.trigger = trigger ? trigger.id || '' : '';
        },

        close(modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            root.classList.remove('is-locked');

            const trigger = modal.dataset.trigger ? doc.getElementById(modal.dataset.trigger) : null;
            if (trigger) trigger.focus();
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       8. Console : barre latérale mobile
       ──────────────────────────────────────────────────────────────────── */
    const Sidebar = {
        init() {
            const sidebar = $('.sidebar');
            const backdrop = $('.sidebar-backdrop');
            if (!sidebar) return;

            const toggle = () => {
                sidebar.classList.toggle('is-open');
                if (backdrop) backdrop.classList.toggle('is-open');
            };

            const close = () => {
                sidebar.classList.remove('is-open');
                if (backdrop) backdrop.classList.remove('is-open');
            };

            const burger = $('.console-burger');
            if (burger) burger.addEventListener('click', toggle);
            if (backdrop) backdrop.addEventListener('click', close);
            doc.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') close();
            });
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       9. Accordéons (FAQ, blocs repliables)
       ──────────────────────────────────────────────────────────────────── */
    const Accordion = {
        init() {
            $$('[data-accordion]').forEach((group) => {
                const items = $$('[data-accordion-item]', group);

                items.forEach((item) => {
                    const button = $('.faq__question, [data-accordion-trigger]', item);
                    if (!button) return;

                    button.addEventListener('click', () => {
                        const isOpen = item.classList.contains('is-open');

                        // Une seule question ouverte à la fois.
                        items.forEach((other) => {
                            other.classList.remove('is-open');
                            const otherButton = $('.faq__question, [data-accordion-trigger]', other);
                            if (otherButton) otherButton.setAttribute('aria-expanded', 'false');
                        });

                        if (!isOpen) {
                            item.classList.add('is-open');
                            button.setAttribute('aria-expanded', 'true');
                        }
                    });

                    button.setAttribute('aria-expanded', item.classList.contains('is-open') ? 'true' : 'false');
                });
            });
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       10. Onglets (démonstration produit)
       ──────────────────────────────────────────────────────────────────── */
    const Tabs = {
        init() {
            $$('[data-tabs]').forEach((group) => {
                const tabs = $$('[role="tab"]', group);
                const panels = $$('[role="tabpanel"]', group);

                const activate = (index) => {
                    tabs.forEach((tab, i) => {
                        const selected = i === index;
                        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                        tab.tabIndex = selected ? 0 : -1;
                    });

                    panels.forEach((panel, i) => {
                        panel.hidden = i !== index;
                        if (i === index) panel.classList.add('is-entering');
                    });
                };

                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', () => activate(index));
                    tab.addEventListener('keydown', (event) => {
                        const offset = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
                        if (!offset) return;
                        event.preventDefault();
                        const next = (index + offset + tabs.length) % tabs.length;
                        tabs[next].focus();
                        activate(next);
                    });
                });

                activate(Math.max(tabs.findIndex((tab) => tab.getAttribute('aria-selected') === 'true'), 0));
            });
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       11. Carrousel (témoignages)
       ──────────────────────────────────────────────────────────────────── */
    const Carousel = {
        init() {
            $$('[data-carousel]').forEach((carousel) => {
                const track = $('.carousel__track', carousel);
                if (!track) return;

                const slides = Array.from(track.children);
                const dots = $$('.carousel__dots button', carousel);
                const prev = $('[data-carousel-prev]', carousel);
                const next = $('[data-carousel-next]', carousel);
                let index = 0;
                let autoplayId = null;

                const perView = () => {
                    const width = window.innerWidth;
                    if (width > 1100) return 3;
                    if (width > 760) return 2;
                    return 1;
                };

                const maxIndex = () => Math.max(slides.length - perView(), 0);

                const update = () => {
                    index = Math.min(index, maxIndex());
                    const slide = slides[0];
                    if (!slide) return;

                    const step = slide.getBoundingClientRect().width + 17.6; // largeur + gap
                    track.style.transform = 'translate3d(' + (-index * step) + 'px, 0, 0)';

                    dots.forEach((dot, i) => dot.setAttribute('aria-current', i === index ? 'true' : 'false'));
                    if (prev) prev.disabled = index === 0;
                    if (next) next.disabled = index >= maxIndex();
                };

                const go = (direction) => {
                    index = Math.min(Math.max(index + direction, 0), maxIndex());
                    update();
                };

                if (prev) prev.addEventListener('click', () => go(-1));
                if (next) next.addEventListener('click', () => go(1));
                dots.forEach((dot, i) => dot.addEventListener('click', () => { index = Math.min(i, maxIndex()); update(); }));

                carousel.addEventListener('keydown', (event) => {
                    if (event.key === 'ArrowRight') go(1);
                    if (event.key === 'ArrowLeft') go(-1);
                });

                // Glisser-déposer tactile / souris
                let startX = 0;
                let dragging = false;

                track.addEventListener('pointerdown', (event) => {
                    dragging = true;
                    startX = event.clientX;
                    track.style.transition = 'none';
                });

                window.addEventListener('pointerup', (event) => {
                    if (!dragging) return;
                    dragging = false;
                    track.style.transition = '';
                    const delta = event.clientX - startX;
                    if (Math.abs(delta) > 48) go(delta < 0 ? 1 : -1);
                    else update();
                });

                // Défilement automatique, interrompu au survol ou au focus
                const start = () => {
                    if (reduceMotion.matches) return;
                    autoplayId = window.setInterval(() => {
                        index = index >= maxIndex() ? 0 : index + 1;
                        update();
                    }, 6200);
                };

                const stop = () => window.clearInterval(autoplayId);

                carousel.addEventListener('mouseenter', stop);
                carousel.addEventListener('mouseleave', start);
                carousel.addEventListener('focusin', stop);
                carousel.addEventListener('focusout', start);
                doc.addEventListener('visibilitychange', () => (doc.hidden ? stop() : start()));

                window.addEventListener('resize', update, { passive: true });
                update();
                start();
            });
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       12. Tarifs : bascule mensuel / annuel animée
       ──────────────────────────────────────────────────────────────────── */
    const Billing = {
        init() {
            const toggle = $('[data-billing-toggle]');
            if (!toggle) return;

            const buttons = $$('[data-billing]', toggle);
            const thumb = $('.switch__thumb', toggle);
            const prices = $$('[data-price-monthly]');

            const moveThumb = (button) => {
                if (!thumb) return;
                thumb.style.width = button.offsetWidth + 'px';
                thumb.style.transform = 'translateX(' + (button.offsetLeft - 4) + 'px)';
            };

            const setPeriod = (period) => {
                buttons.forEach((button) => {
                    const active = button.dataset.billing === period;
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                    if (active) moveThumb(button);
                });

                prices.forEach((price) => {
                    const monthly = Number(price.dataset.priceMonthly);
                    const yearly = Number(price.dataset.priceYearly);
                    const value = period === 'yearly' ? yearly : monthly;
                    const card = price.closest('.plan');

                    card.classList.remove('is-animating');
                    void card.offsetWidth; // force le redémarrage de l'animation
                    card.classList.add('is-animating');

                    price.textContent = Counters.format(value, 0);
                });

                $$('[data-billing-note]').forEach((note) => {
                    note.textContent = period === 'yearly'
                        ? 'soit ' + note.dataset.billingNoteYearly + ' — facturé annuellement'
                        : note.dataset.billingNote || '';
                });

                $$('[data-billing-period]').forEach((label) => {
                    label.textContent = period === 'yearly' ? '/mois' : '/mois';
                });
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => setPeriod(button.dataset.billing));
            });

            const initial = buttons.find((button) => button.getAttribute('aria-pressed') === 'true') || buttons[0];
            if (initial) {
                window.setTimeout(() => moveThumb(initial), 260);
                window.addEventListener('resize', () => moveThumb(buttons.find((b) => b.getAttribute('aria-pressed') === 'true') || initial), { passive: true });
            }
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       13. Services : aperçu synchronisé au survol
       ──────────────────────────────────────────────────────────────────── */
    const Services = {
        init() {
            const list = $('[data-services]');
            const previews = $$('[data-preview]');
            if (!list || previews.length === 0) return;

            const caption = $('[data-preview-caption]');
            const subCaption = $('[data-preview-subcaption]');

            const activate = (item) => {
                const key = item.dataset.service;

                $$('.service', list).forEach((service) => {
                    service.classList.toggle('is-active', service === item);
                });

                previews.forEach((preview) => {
                    preview.classList.toggle('is-active', preview.dataset.preview === key);
                });

                if (caption) caption.textContent = item.dataset.title || '';
                if (subCaption) subCaption.textContent = item.dataset.subtitle || '';
            };

            $$('.service', list).forEach((item) => {
                item.addEventListener('mouseenter', () => activate(item));
                item.addEventListener('focus', () => activate(item));
                item.addEventListener('click', () => activate(item));
            });

            const first = $('.service', list);
            if (first) activate(first);
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       14. Divers : mot de passe, confirmations, copie, champs de filtre
       ──────────────────────────────────────────────────────────────────── */
    const Misc = {
        init() {
            // Impression déclenchée par un bouton (aucun attribut inline, compatible CSP stricte)
            $$('[data-print]').forEach((button) => {
                button.addEventListener('click', () => window.print());
            });

            // Bascule d'affichage du mot de passe
            $$('[data-toggle-password]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = doc.getElementById(button.dataset.togglePassword);
                    if (!input) return;

                    const visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    button.setAttribute('aria-pressed', visible ? 'false' : 'true');
                });
            });

            // Confirmation avant action destructive
            $$('form[data-confirm], [data-confirm]').forEach((element) => {
                element.addEventListener('submit', (event) => {
                    if (!window.confirm(element.dataset.confirm)) event.preventDefault();
                });

                if (element.tagName === 'BUTTON' || element.tagName === 'A') {
                    element.addEventListener('click', (event) => {
                        if (!window.confirm(element.dataset.confirm)) event.preventDefault();
                    });
                }
            });

            // Copie dans le presse-papiers
            $$('[data-copy]').forEach((button) => {
                button.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(button.dataset.copy);
                        Toast.show('success', 'Copié dans le presse-papiers.');
                    } catch (error) {
                        Toast.show('info', button.dataset.copy);
                    }
                });
            });

            // Filtres : soumission automatique après changement
            $$('[data-auto-submit]').forEach((field) => {
                field.addEventListener('change', () => field.form && field.form.requestSubmit());
            });

            // Effet projecteur sur les cartes (position du curseur)
            if (window.matchMedia('(hover: hover)').matches && !reduceMotion.matches) {
                $$('.card--spotlight, .feature').forEach((card) => {
                    card.addEventListener('pointermove', (event) => {
                        const rect = card.getBoundingClientRect();
                        card.style.setProperty('--px', ((event.clientX - rect.left) / rect.width * 100) + '%');
                        card.style.setProperty('--py', ((event.clientY - rect.top) / rect.height * 100) + '%');
                    });
                });
            }
        }
    };

    /* ─────────────────────────────────────────────────────────────────────
       Amorçage
       ──────────────────────────────────────────────────────────────────── */
    const boot = () => {
        Theme.init();
        Reveal.init();
        Toast.flush();
        AjaxForm.init();
        Modal.init();
        Sidebar.init();
        Accordion.init();
        Tabs.init();
        Carousel.init();
        Billing.init();
        Services.init();
        Misc.init();

        doc.body.classList.add('is-ready');
    };

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // API publique minimale (utilisée par les vues et landing.js)
    window.Affecta = { Toast, Theme, Counters, Charts, reveal: Reveal };
})();
