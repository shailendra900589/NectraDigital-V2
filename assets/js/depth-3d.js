/**
 * Nectra Digital — 3D depth layer (classes + card tilt)
 */
(function () {
    'use strict';

    function reduced() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function markVisible(el) {
        el.classList.add('nd-depth-visible');
    }

    function initTilt() {
        if (reduced()) return;

        var cards = document.querySelectorAll('.nd-depth-card, .nd-depth-tilt');
        cards.forEach(function (card) {
            var max = 10;
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                card.classList.add('nd-depth-tilt-active');
                card.style.transform =
                    'perspective(1000px) rotateY(' + x * max + 'deg) rotateX(' + -y * max + 'deg) translateZ(12px)';
            });
            card.addEventListener('mouseleave', function () {
                card.classList.remove('nd-depth-tilt-active');
                card.style.transform = '';
            });
        });
    }

    function tagElements() {
        document.body.classList.add('nd-depth-active');

        document.querySelectorAll('body > section, main section, footer.nd-footer, .nd-trust-strip').forEach(function (sec) {
            if (sec.matches('.sticky-top')) return;
            sec.classList.add('nd-depth-section');
        });

        document.querySelectorAll('main .container, main .container-fluid, footer .container').forEach(function (c) {
            c.classList.add('nd-depth-container');
        });

        document.querySelectorAll(
            'main h1, main h2:not(.nd-section-title), main h3.nd-section-title, main .display-4, main .display-5, main .display-6'
        ).forEach(function (h) {
            if (h.closest('.nd-navbar, .navbar, .nd-section-head, .svc-hero, .nd-page-hero, .nd-hero-home')) return;
            if (h.classList.contains('nd-hero-title')) return;
            h.classList.add('nd-depth-heading');
        });

        var cardSel =
            '.nd-card, .svc-hero-card, .nd-cta-card, .nd-card-dark, .svc-deliverable, .svc-engagement-card, ' +
            '.svc-challenge-card, .svc-benefit-card, .svc-tech-panel, .svc-sidebar-card, .svc-quick-answer, ' +
            '.aeo-card, .blog-card:not(.blog-card--link):not(.nd-insights-card), .nd-home-dashboard, .svc-stat-box, .svc-glance-stat, .nd-process-step';
        document.querySelectorAll(cardSel).forEach(function (el) {
            if (el.classList.contains('blog-card--link') || el.classList.contains('nd-insights-card')) return;
            if (el.closest('[data-nd-insights-grid]')) return;
            el.classList.add('nd-depth-card');
        });

        document.querySelectorAll(
            '.btn-nectra-primary, .btn-nectra-secondary, .btn-nectra-outline-light, .btn-nectra, ' +
            'main .btn-primary:not(.filter-btn), main .btn-outline-light, main .btn-outline-primary'
        ).forEach(function (btn) {
            if (btn.closest('.navbar, .nd-navbar, .filter-btn')) return;
            btn.classList.add('nd-depth-btn');
        });

        document.querySelectorAll('.nd-card-icon, .svc-hero-icon, .nd-trust-item').forEach(function (wrap) {
            wrap.classList.add('nd-depth-icon-wrap');
            wrap.querySelectorAll('i').forEach(function (icon) {
                icon.classList.add('nd-depth-icon');
            });
        });

        document.querySelectorAll('.nd-motion-div').forEach(function (div) {
            div.classList.add('nd-depth-block');
        });

        document.querySelectorAll('.nd-depth-btn i, .nd-home-tech-icons i').forEach(function (icon) {
            icon.classList.add('nd-depth-icon');
        });
    }

    function observeDepth() {
        if (!('IntersectionObserver' in window)) {
            document.querySelectorAll('.nd-depth-section, .nd-depth-heading, .nd-depth-card, .nd-depth-block').forEach(markVisible);
            return;
        }

        if (reduced()) {
            document.querySelectorAll('.nd-depth-section, .nd-depth-heading, .nd-depth-card, .nd-depth-block').forEach(markVisible);
            return;
        }

        var obs = new IntersectionObserver(
            function (entries, o) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    markVisible(entry.target);
                    o.unobserve(entry.target);
                });
            },
            { threshold: 0.06, rootMargin: '0px 0px -20px 0px' }
        );

        document.querySelectorAll('.nd-depth-section, .nd-depth-heading, .nd-depth-card, .nd-depth-block').forEach(function (el) {
            obs.observe(el);
        });
    }

    function init() {
        tagElements();
        observeDepth();
        initTilt();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
