/**
 * Nectra Digital — Production Client-side Application Scripts
 * Lightweight, Performant, WCAG 2.2 AA Accessible
 */

document.addEventListener("DOMContentLoaded", function() {
    "use strict";

    // 1. Sticky Navigation Scroll Effect
    const navbar = document.querySelector('.nd-navbar, .navbar');
    if (navbar) {
        const handleScroll = function() {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();

        if (document.body.getAttribute('data-nd-nav-theme') === 'dark') {
            navbar.classList.add('nd-navbar-dark');
        }
    }

    // 2. Scroll reveal handled by assets/js/motion.js

    // 3. Lead Contact Form AJAX Handling
    const forms = document.querySelectorAll('#contactForm, .nd-ajax-form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const responseDiv = form.querySelector('.response-msg') || document.getElementById('responseMsg');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Submit';
            
            // Set loading state
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
                submitBtn.disabled = true;
            }

            const formData = new FormData(form);

            fetch(typeof nectraWebPath === 'function' ? nectraWebPath('/process') : '/process', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                if (responseDiv) {
                    if (data.status === 'success') {
                        responseDiv.innerHTML = '<div class="alert alert-success d-flex align-items-center gap-2"><i class="fas fa-check-circle"></i> ' + (data.message || 'Thank you! We will get in touch shortly.') + '</div>';
                        form.reset();
                    } else {
                        responseDiv.innerHTML = '<div class="alert alert-danger d-flex align-items-center gap-2"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Please check your inputs and try again.') + '</div>';
                    }
                }
            })
            .catch(function(err) {
                if (responseDiv) {
                    responseDiv.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please try contacting us directly by phone or email.</div>';
                }
            })
            .finally(function() {
                if (submitBtn) {
                    submitBtn.innerHTML = originalBtnHtml;
                    submitBtn.disabled = false;
                }
            });
        });
    });

    function collapseFooterWhitespace() {
        document.querySelectorAll('.skiptranslate, .goog-te-banner-frame, iframe.goog-te-banner-frame, #goog-gt-tt').forEach(function (el) {
            el.style.cssText = 'display:none!important;height:0!important;max-height:0!important;overflow:hidden!important;visibility:hidden!important;margin:0!important;padding:0!important;border:0!important;';
        });

        var footer = document.querySelector('footer.nd-footer');
        if (!footer) return;

        var node = footer.nextElementSibling;
        while (node) {
            var next = node.nextElementSibling;
            var tag = node.tagName;
            if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'LINK') {
                node = next;
                continue;
            }
            if (tag === 'DIV' && !node.classList.contains('nectra-floating-wrap') && !node.id && (node.className || '').indexOf('goog-te') !== -1) {
                node.style.display = 'none';
                node.style.height = '0';
            }
            if (node.id === 'nectra-chatbot-panel') {
                break;
            }
            node = next;
        }
    }

    collapseFooterWhitespace();
    window.setTimeout(collapseFooterWhitespace, 800);
    window.setTimeout(collapseFooterWhitespace, 2500);

    // 4. Smooth Anchor Link Scrolling
    document.querySelectorAll('a[href^="#"]:not([href="#"]):not([data-bs-toggle])').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const navHeight = navbar ? navbar.offsetHeight : 0;
                const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = elementPosition - navHeight - 16;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});