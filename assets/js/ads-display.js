/**
 * Hide empty AdSense/image ad slots; show cards only when content actually loaded.
 */
(function () {
    'use strict';

    var sidebarGraceUntil = 0;

    function isSidebarSlot(slot) {
        return !!(slot && slot.closest && slot.closest('.sidebar-ad-stack'));
    }

    function adSlotFilled(slot) {
        if (!slot) return false;

        var ins = slot.querySelector('ins.adsbygoogle');
        if (ins) {
            var status = ins.getAttribute('data-ad-status');
            if (status === 'filled') return true;
            var iframe = ins.querySelector('iframe');
            if (iframe && iframe.offsetHeight > 28 && iframe.offsetWidth > 28) return true;
            if (ins.offsetHeight > 48 && ins.offsetWidth > 48 && iframe) return true;
            return false;
        }

        var img = slot.querySelector('img.nd-ad-img, img.sidebar-ad-img, img.nectra-ad-img');
        if (img) {
            if (!img.complete) return null;
            return img.naturalWidth > 12 && img.naturalHeight > 12;
        }

        return false;
    }

    function markFilled(slot) {
        slot.classList.remove('nd-ad-pending');
        slot.classList.add('nd-ad-filled');
        var badge = slot.querySelector('.nd-ad-badge');
        if (badge) badge.style.display = '';
    }

    function removeSlot(slot) {
        if (slot && slot.parentNode) slot.parentNode.removeChild(slot);
    }

    function refreshSidebarLayout() {
        var stack = document.querySelector('.sidebar-ad-stack');
        if (stack && stack.querySelectorAll('.nd-ad-slot').length === 0) {
            var asideCol = document.querySelector('.nd-post-sidebar-col');
            if (asideCol) asideCol.remove();
            var main = document.querySelector('.nd-post-main-col');
            if (main) {
                main.classList.remove('col-lg-8');
                main.classList.add('col-12');
            }
            var heading = document.querySelector('.nd-post-sponsored-heading');
            if (heading) heading.remove();
        } else if (stack) {
            var n = stack.querySelectorAll('.nd-ad-slot.nd-ad-filled').length;
            stack.setAttribute('data-nd-sidebar-ad-count', String(n));
        }
    }

    function evaluateSlots() {
        document.querySelectorAll('.nd-ad-slot').forEach(function (slot) {
            var state = adSlotFilled(slot);
            if (state === true) {
                markFilled(slot);
                return;
            }
            if (state === false) {
                if (isSidebarSlot(slot) && Date.now() < sidebarGraceUntil) {
                    return;
                }
                removeSlot(slot);
            }
        });
        refreshSidebarLayout();
    }

    function watchAdSense(ins) {
        if (!ins || ins.__ndAdWatched) return;
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
            if (img.__ndAdBound) return;
            img.__ndAdBound = true;
            img.addEventListener('load', evaluateSlots);
            img.addEventListener('error', function () {
                var slot = img.closest('.nd-ad-slot');
                removeSlot(slot);
                refreshSidebarLayout();
            });
        });
    }

    function init() {
        sidebarGraceUntil = Date.now() + 18000;

        document.querySelectorAll('ins.adsbygoogle').forEach(watchAdSense);
        bindImages();

        var passes = 0;
        var maxPasses = 24;
        var timer = setInterval(function () {
            evaluateSlots();
            passes++;
            if (passes >= maxPasses) clearInterval(timer);
        }, 500);

        window.addEventListener('load', function () {
            setTimeout(evaluateSlots, 1200);
            setTimeout(evaluateSlots, 3500);
            setTimeout(evaluateSlots, 6500);
            setTimeout(function () {
                sidebarGraceUntil = 0;
                evaluateSlots();
            }, 18000);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
