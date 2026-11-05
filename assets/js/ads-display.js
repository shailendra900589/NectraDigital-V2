/**
 * Show ad slots when Google/image content loads; remove only confirmed empty units.
 */
(function () {
    'use strict';

    var pruneGraceUntil = 0;
    var sidebarGraceUntil = 0;

    function isSidebarSlot(slot) {
        return !!(slot && slot.closest && slot.closest('.sidebar-ad-stack'));
    }

    function isTextFormatSlot(slot) {
        return !!(slot && slot.classList && slot.classList.contains('nd-ad-text-format'));
    }

    function adSlotFilled(slot) {
        if (!slot) {
            return false;
        }

        var ins = slot.querySelector('ins.adsbygoogle');
        if (ins) {
            var status = (ins.getAttribute('data-ad-status') || '').toLowerCase();
            if (status === 'filled') {
                return true;
            }
            if (status === 'unfilled') {
                return false;
            }

            var iframe = ins.querySelector('iframe');
            if (iframe) {
                var ih = iframe.offsetHeight;
                var iw = iframe.offsetWidth;
                if (ih > 8 && iw > 8) {
                    return true;
                }
            }

            var insH = ins.offsetHeight;
            var insW = ins.offsetWidth;
            if (isTextFormatSlot(slot) && insH > 20 && insW > 40) {
                return true;
            }
            if (insH > 40 && insW > 40 && iframe) {
                return true;
            }

            return null;
        }

        var img = slot.querySelector('img.nd-ad-img, img.sidebar-ad-img, img.nectra-ad-img');
        if (img) {
            if (!img.complete) {
                return null;
            }
            return img.naturalWidth > 12 && img.naturalHeight > 12;
        }

        if (slot.querySelector('iframe, embed, object')) {
            return null;
        }

        return false;
    }

    function markFilled(slot) {
        slot.classList.remove('nd-ad-pending');
        slot.classList.add('nd-ad-filled');
        var badge = slot.querySelector('.nd-ad-badge');
        if (badge) {
            badge.style.display = '';
        }
    }

    function removeSlot(slot) {
        if (slot && slot.parentNode) {
            slot.parentNode.removeChild(slot);
        }
    }

    function refreshSidebarLayout() {
        var stack = document.querySelector('.sidebar-ad-stack');
        if (stack && stack.querySelectorAll('.nd-ad-slot').length === 0) {
            var asideCol = document.querySelector('.nd-post-sidebar-col');
            if (asideCol) {
                asideCol.remove();
            }
            var main = document.querySelector('.nd-post-main-col');
            if (main) {
                main.classList.remove('col-lg-8');
                main.classList.add('col-12');
            }
            var row = document.querySelector('.nd-post-row-with-sidebar');
            if (row) {
                row.classList.remove('nd-post-row-with-sidebar');
            }
            var heading = document.querySelector('.nd-post-sponsored-heading');
            if (heading) {
                heading.remove();
            }
        } else if (stack) {
            var n = stack.querySelectorAll('.nd-ad-slot.nd-ad-filled').length;
            stack.setAttribute('data-nd-sidebar-ad-count', String(n));
        }
    }

    function canPruneSlot(slot, state) {
        if (state !== false) {
            return false;
        }
        var now = Date.now();
        if (isSidebarSlot(slot) && now < sidebarGraceUntil) {
            return false;
        }
        if (now < pruneGraceUntil) {
            return false;
        }
        return true;
    }

    function evaluateSlots() {
        document.querySelectorAll('.nd-ad-slot').forEach(function (slot) {
            var state = adSlotFilled(slot);
            if (state === true) {
                markFilled(slot);
                return;
            }
            if (state === null) {
                return;
            }
            if (canPruneSlot(slot, state)) {
                removeSlot(slot);
            }
        });
        refreshSidebarLayout();
    }

    function watchAdSense(ins) {
        if (!ins || ins.__ndAdWatched) {
            return;
        }
        ins.__ndAdWatched = true;
        var obs = new MutationObserver(function () {
            evaluateSlots();
        });
        obs.observe(ins, { attributes: true, attributeFilter: ['data-ad-status', 'style', 'class'] });
        var parent = ins.parentElement;
        if (parent) {
            var childObs = new MutationObserver(function () {
                evaluateSlots();
            });
            childObs.observe(parent, { childList: true, subtree: true });
        }
    }

    function bindImages() {
        document.querySelectorAll('.nd-ad-slot img').forEach(function (img) {
            if (img.__ndAdBound) {
                return;
            }
            img.__ndAdBound = true;
            img.addEventListener('load', evaluateSlots);
            img.addEventListener('error', function () {
                var slot = img.closest('.nd-ad-slot');
                if (canPruneSlot(slot, false)) {
                    removeSlot(slot);
                    refreshSidebarLayout();
                }
            });
        });
    }

    function bindAllIns() {
        document.querySelectorAll('ins.adsbygoogle').forEach(watchAdSense);
    }

    function init() {
        var now = Date.now();
        pruneGraceUntil = now + 22000;
        sidebarGraceUntil = now + 22000;

        bindAllIns();
        bindImages();

        window.addEventListener('nectra-adsense-init', function () {
            bindAllIns();
            setTimeout(evaluateSlots, 400);
        });

        var passes = 0;
        var maxPasses = 30;
        var timer = setInterval(function () {
            evaluateSlots();
            passes++;
            if (passes >= maxPasses) {
                clearInterval(timer);
            }
        }, 500);

        window.addEventListener('load', function () {
            bindAllIns();
            setTimeout(evaluateSlots, 800);
            setTimeout(evaluateSlots, 2000);
            setTimeout(evaluateSlots, 4500);
            setTimeout(evaluateSlots, 8000);
            setTimeout(function () {
                pruneGraceUntil = 0;
                sidebarGraceUntil = 0;
                evaluateSlots();
            }, 22000);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
