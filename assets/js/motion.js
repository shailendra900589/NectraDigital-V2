/**
 * Nectra Digital — site-wide scroll reveal & section motion
 */
(function () {
    'use strict';

    function reducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    var interactiveRootSelector =
        '.nd-interactive-zone, .nd-contact-section, .nd-cta-band, .nd-insights-grid, ' +
        '.nd-no-section-animate, .svc-inline-form-wrap, #contactPageFormWrap, [data-nd-interactive], ' +
        'form, .nd-ajax-form, .svc-inline-form';

    var interactiveDescendantSelector =
        'form, .nd-ajax-form, .svc-inline-form, input, select, textarea, button, label, ' +
        'a[href], .btn, .nd-cta-tile, .accordion-button, .dropdown-item, .nav-link';

    function isInteractiveRoot(el) {
        if (!el || el.nodeType !== 1) return false;
        if (el.matches && el.matches(interactiveRootSelector)) return true;
        return !!(el.closest && el.closest(interactiveRootSelector));
    }

    function containsInteractive(el) {
        if (!el || el.nodeType !== 1) return false;
        if (el.matches && el.matches(interactiveDescendantSelector)) return true;
        return !!(el.querySelector && el.querySelector(interactiveDescendantSelector));
    }

    function stripMotionClasses(el) {
        if (!el || !el.classList) return;
        el.classList.remove('nd-motion-div', 'nd-motion-surface', 'nd-depth-block', 'nd-reveal', 'nd-reveal-fade');
        el.style.opacity = '';
        el.style.transform = '';
        el.style.willChange = '';
    }

    /** Remove auto-motion from forms, CTAs, and any block that wraps links/inputs. */
    function neutralizeInteractiveMotion() {
        document.querySelectorAll(interactiveRootSelector).forEach(function (root) {
            var node = root;
            while (node && node !== document.body) {
                stripMotionClasses(node);
                node = node.parentElement;
            }
            root.querySelectorAll('.nd-motion-div, .nd-motion-surface, .nd-reveal, .nd-reveal-fade').forEach(stripMotionClasses);
        });

        document.querySelectorAll('.nd-motion-div, .nd-reveal, .nd-reveal-fade').forEach(function (el) {
            if (containsInteractive(el)) {
                stripMotionClasses(el);
            }
        });
    }

    function markVisible(el) {
        el.classList.add('nd-is-visible');
        el.classList.add('nd-section-visible');
    }

    function applyStagger(el, parent) {
        if (!parent) return;
        var items = parent.querySelectorAll('[data-nd-stagger] > *');
        if (!items.length) return;
        var idx = Array.prototype.indexOf.call(items, el.closest('[data-nd-stagger] > *') || el);
        if (idx < 0) {
            idx = Array.prototype.indexOf.call(items, el);
        }
        if (idx >= 0) {
            el.style.setProperty('--nd-reveal-delay', Math.min(idx * 75, 525) + 'ms');
        }
    }

    var skipSectionMatch = '.nd-hero-home, .svc-hero, .sticky-top, header.nd-page-hero, header.svc-hero';

    function revealSectionContents(sec) {
        sec.querySelectorAll('.nd-reveal, .nd-reveal-fade, .nd-motion-div').forEach(function (el) {
            markVisible(el);
        });
    }

    function initSections(sectionObserver) {
        document.querySelectorAll('body > section, main section').forEach(function (sec) {
            if (sec.matches(skipSectionMatch)) return;
            if (sec.closest('.nd-navbar, header.svc-hero')) return;

            if (sec.classList.contains('nd-trust-strip')) {
                sec.querySelectorAll('.nd-trust-item').forEach(function (item, i) {
                    item.style.setProperty('--nd-reveal-delay', i * 60 + 'ms');
                    markVisible(item);
                });
                return;
            }

            if (sec.classList.contains('nd-no-section-animate') || sec.classList.contains('svc-quick-answer-section')) {
                revealSectionContents(sec);
                return;
            }

            sec.classList.add('nd-section-animate');
            sectionObserver.observe(sec);
        });
    }

    function staggerContainerChildren(sec) {
        if (sec.classList.contains('nd-footer')) {
            sec.querySelectorAll('.nd-footer-grid > [class*="col"]').forEach(function (col, i) {
                col.style.setProperty('--nd-child-i', String(i));
                col.classList.add('nd-section-child');
            });
            return;
        }

        var roots = sec.querySelectorAll(':scope > .container, :scope > .container-fluid');
        if (!roots.length) {
            roots = [sec];
        }
        roots.forEach(function (root) {
            Array.prototype.forEach.call(root.children, function (child, i) {
                child.style.setProperty('--nd-child-i', String(i));
                child.classList.add('nd-section-child');
            });
        });
    }

    var divSkipSelector =
        'script, style, .collapse, .dropdown-menu, .modal, .offcanvas, .navbar, .nd-navbar, ' +
        '.breadcrumb, .spinner-border, .accordion-collapse, .nd-hero-mesh, .svc-hero-glow, ' +
        '.nd-hero-glow, .nd-cta-glow, .visually-hidden, .d-none, [aria-hidden="true"], ' +
        '.nd-reveal, .nd-motion-div, .row, .container, .container-fluid, form, label';

    function shouldSkipDiv(div) {
        if (!div || div.nodeType !== 1) return true;
        if (div.closest && div.closest('a.blog-card--link, article.nd-insights-card, article.blog-card')) return true;
        if (div.closest && div.closest('footer.nd-footer, .nd-navbar, .navbar')) return true;
        if (div.matches(divSkipSelector)) return true;
        if (div.closest(divSkipSelector)) return true;
        if (div.closest('header.svc-hero, .nd-hero-home') && !div.closest('.svc-hero-card, .nd-home-dashboard')) {
            return true;
        }
        if (div.closest('.nd-contact-section') || div.classList.contains('nd-contact-panel') || div.classList.contains('nd-contact-shell')) {
            return true;
        }
        if (isInteractiveRoot(div) || containsInteractive(div)) {
            return true;
        }
        var h = div.offsetHeight;
        var w = div.offsetWidth;
        if (h < 18 && w < 18) return true;
        return false;
    }

    function qualifiesDiv(div) {
        if (shouldSkipDiv(div)) return false;
        var cn = div.className;
        if (typeof cn !== 'string') cn = '';
        if (div.parentElement && div.parentElement.classList.contains('row') && /\bcol(-|$)/.test(cn)) {
            return true;
        }
        if (/\b(nd-|svc-|rounded|shadow-sm|shadow|border|p-[0-9]|p-md|bg-|card|accordion|alert|badge|list-group|flex-grow|nd-card|nd-trust|nd-process|nd-section|nd-cta|nd-author|form-control|input-group|mb-[0-9]|mt-[0-9]|py-[0-9]|px-[0-9]|d-flex|grid|nd-home|nd-motion)/.test(cn)) {
            return true;
        }
        if (div.id && div.id.length > 2) return true;
        return false;
    }

  /** Optional opt-in only — broad auto-tagging broke clicks on forms and link columns. */
    function initDivMotion(observer) {
        document.querySelectorAll('[data-nd-motion-block]').forEach(function (div) {
            if (isInteractiveRoot(div) || containsInteractive(div)) return;
            div.classList.add('nd-motion-div');
            if (/\b(nd-card|rounded|border|shadow|svc-|p-[34]|p-md-[45])\b/.test(div.className)) {
                div.classList.add('nd-motion-surface');
            }
            observer.observe(div);
        });
    }

    var componentSelector = [
        '.nd-reveal',
        '.nd-reveal-fade',
        '.nd-card',
        '.nd-process-step',
        '.nd-cta-card',
        '.nd-section-head',
        '.nd-author-box',
        '.svc-deliverable',
        '.svc-engagement-card',
        '.svc-challenge-card',
        '.svc-benefit-card',
        '.svc-process-step',
        '.svc-glance-stat',
        '.svc-capability-item',
        '.svc-tech-panel',
        '.svc-quick-answer',
        '.svc-sidebar-card',
        '.svc-industry-pill',
        '.accordion-item',
        '.project-item',
        '.nd-trust-item',
        '.nd-social-link',
        '.nd-home-tech-icons i',
        '.nd-home-kpi',
        '.nd-home-dashboard',
        '.btn-nectra-primary',
        '.btn-nectra-secondary',
        '.btn-nectra-outline-light',
        '.nd-card-dark',
        '.svc-stat-box',
        '.aeo-card',
        'article.blog-card:not(.nd-insights-card)',
        'article.card',
        '.nd-city-chips .btn',
        '.filter-btn'
    ].join(',');

    function init() {
        if (!('IntersectionObserver' in window)) {
            document.querySelectorAll('.nd-section-animate, .nd-reveal').forEach(markVisible);
            return;
        }

        if (reducedMotion()) {
            document.querySelectorAll('.nd-section-animate, .nd-reveal, .nd-reveal-fade, .nd-motion-div').forEach(function (el) {
                markVisible(el);
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
            document.querySelectorAll('.nd-section-child').forEach(function (el) {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
            neutralizeInteractiveMotion();
            document.body.classList.add('nd-motion-ready');
            return;
        }

        var sectionObserver = new IntersectionObserver(
            function (entries, obs) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var target = entry.target;

                    if (target.classList.contains('nd-section-animate') || target.tagName === 'FOOTER') {
                        markVisible(target);
                        staggerContainerChildren(target);
                        revealSectionContents(target);
                        obs.unobserve(target);
                        return;
                    }

                    applyStagger(target, target.closest('[data-nd-stagger]'));
                    markVisible(target);
                    obs.unobserve(target);
                });
            },
            { threshold: 0.08, rootMargin: '0px 0px -24px 0px' }
        );

        initSections(sectionObserver);

        document.querySelectorAll(componentSelector).forEach(function (el) {
            if (el.closest('footer.nd-footer, .nd-navbar, .navbar')) return;
            if (el.closest('.nd-contact-section')) return;
            if (isInteractiveRoot(el) || containsInteractive(el)) return;
            if (el.closest('[data-nd-insights-grid]')) return;
            if (el.closest('.svc-hero-card, .nd-hero-home .nd-home-dashboard')) return;
            if (el.matches('.btn, .btn-nectra-primary') && el.closest('.svc-hero, .nd-hero-home, header.nd-page-hero, header.svc-hero')) return;
            if (!el.classList.contains('nd-reveal') && !el.classList.contains('nd-reveal-fade')) {
                el.classList.add('nd-reveal');
            }
            sectionObserver.observe(el);
        });

        document.querySelectorAll('[data-nd-stagger]').forEach(function (wrap) {
            Array.prototype.forEach.call(wrap.children, function (child, i) {
                child.querySelectorAll('.nd-reveal, .nd-card, .svc-deliverable, .project-item, .nd-motion-div').forEach(function (inner) {
                    inner.style.setProperty('--nd-reveal-delay', Math.min(i * 75, 525) + 'ms');
                });
            });
        });

        initDivMotion(sectionObserver);
        neutralizeInteractiveMotion();

        document.body.classList.add('nd-motion-ready');

        requestAnimationFrame(function () {
            var vh = window.innerHeight || document.documentElement.clientHeight;
            document.querySelectorAll('.nd-section-animate, .nd-reveal, .nd-reveal-fade').forEach(function (el) {
                var r = el.getBoundingClientRect();
                if (r.top < vh * 0.92 && r.bottom > 0) {
                    markVisible(el);
                    if (el.classList.contains('nd-section-animate')) {
                        staggerContainerChildren(el);
                        revealSectionContents(el);
                    }
                }
            });
            neutralizeInteractiveMotion();
        });

        var glows = document.querySelectorAll('.svc-hero-glow, .nd-hero-glow');
        window.addEventListener(
            'scroll',
            function () {
                var y = window.scrollY;
                glows.forEach(function (g) {
                    g.style.transform = 'translateY(' + y * 0.1 + 'px)';
                });
            },
            { passive: true }
        );

        document.querySelectorAll('.nd-home-tech-icons i').forEach(function (icon, i) {
            icon.classList.add('nd-tech-icon');
            icon.style.setProperty('--nd-tech-delay', i * 0.15 + 's');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
