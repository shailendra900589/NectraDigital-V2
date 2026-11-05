/**
 * Insights grid — ensure card clicks always navigate (fallback if overlays intercept).
 */
(function () {
    'use strict';

    function init() {
        document.querySelectorAll('[data-nd-insights-grid]').forEach(function (grid) {
            grid.addEventListener('click', function (e) {
                if (e.defaultPrevented) return;
                if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                var card = e.target.closest('article.nd-insights-card');
                if (!card) {
                    var col = e.target.closest('[data-nd-insights-grid] .col-md-6');
                    card = col ? col.querySelector('article.nd-insights-card') : null;
                }
                if (!card) return;

                var link = card.querySelector('a.nd-insights-post-link');
                if (!link) return;

                var href = link.getAttribute('href');
                if (!href || href === '#') return;

                e.preventDefault();
                window.location.assign(link.href);
            }, true);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
