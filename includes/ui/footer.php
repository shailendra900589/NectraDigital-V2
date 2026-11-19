<?php
if (!function_exists('render_nap_block')) {
    require_once dirname(__DIR__) . '/site-contact.php';
}
require_once dirname(__DIR__) . '/nav-data.php';
?>
<footer class="nd-footer nd-no-section-animate">
    <div class="container">
        <div class="row g-4 g-lg-5 nd-footer-grid">
            <div class="col-lg-4 col-md-6">
                <a href="<?php echo htmlspecialchars(nectra_nav_path('/')); ?>" class="nd-navbar-brand text-white notranslate d-inline-block mb-3">
                    <span>NECTRA</span><span class="text-tech-blue">DIGITAL</span>
                </a>
                <p class="text-white-50 small mb-4 nd-footer-lead">
                    Software development, website design &amp; development, and mobile app development — with AI automation and digital growth support when you need it.
                </p>
                <?php if (function_exists('render_nap_block')) { render_nap_block('footer'); } ?>
                <div class="d-flex gap-2 mt-4">
                    <a href="<?php echo defined('NECTRA_FACEBOOK_URL') ? NECTRA_FACEBOOK_URL : 'https://www.facebook.com/nectradigital'; ?>" target="_blank" rel="noopener noreferrer" class="nd-social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.linkedin.com/company/nectradigital" target="_blank" rel="noopener noreferrer" class="nd-social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://twitter.com/nectradigital" target="_blank" rel="noopener noreferrer" class="nd-social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/nectradigital" target="_blank" rel="noopener noreferrer" class="nd-social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-12">
                <button class="nd-footer-toggle d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#ftDev" aria-expanded="false">Development</button>
                <div class="nd-footer-title d-none d-md-block">Development</div>
                <div class="collapse d-md-block" id="ftDev">
                    <ul class="nd-footer-links">
                        <li><a href="<?php echo nectra_nav_path('/software-development-services'); ?>">Software Development</a></li>
                        <li><a href="<?php echo nectra_nav_path('/web-development-services'); ?>">Web Development</a></li>
                        <li><a href="<?php echo nectra_nav_path('/mobile-app-development'); ?>">Mobile App Development</a></li>
                        <li><a href="<?php echo nectra_nav_path('/software-development-services'); ?>">SaaS Development</a></li>
                        <li><a href="<?php echo nectra_nav_path('/web-development-services'); ?>">Web Applications</a></li>
                        <li><a href="<?php echo nectra_nav_path('/ecommerce-development'); ?>">E-commerce</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-12">
                <button class="nd-footer-toggle d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#ftTech" aria-expanded="false">Technology</button>
                <div class="nd-footer-title d-none d-md-block">Technology</div>
                <div class="collapse d-md-block" id="ftTech">
                    <ul class="nd-footer-links">
                        <li><a href="<?php echo nectra_nav_path('/ai-automation-services'); ?>">AI &amp; Automation</a></li>
                        <li><a href="<?php echo nectra_nav_path('/software-development-services'); ?>">API Development</a></li>
                        <li><a href="<?php echo nectra_nav_path('/web-development-services'); ?>">UI/UX Design</a></li>
                        <li><a href="<?php echo nectra_nav_path('/web-development-services'); ?>">Maintenance</a></li>
                        <li><a href="<?php echo nectra_nav_path('/software-development-services'); ?>">Enterprise Solutions</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-12">
                <button class="nd-footer-toggle d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#ftGrowth" aria-expanded="false">Growth</button>
                <div class="nd-footer-title d-none d-md-block">Growth</div>
                <div class="collapse d-md-block" id="ftGrowth">
                    <ul class="nd-footer-links">
                        <li><a href="<?php echo nectra_nav_path('/seo-services'); ?>">SEO</a></li>
                        <li><a href="<?php echo nectra_nav_path('/technical-seo-services'); ?>">Technical SEO</a></li>
                        <li><a href="<?php echo nectra_nav_path('/digital-marketing-services'); ?>">Digital Marketing</a></li>
                        <li><a href="<?php echo nectra_nav_path('/google-ads-management'); ?>">Google Ads</a></li>
                        <li><a href="<?php echo nectra_nav_path('/performance-marketing-services'); ?>">Performance Marketing</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-12">
                <button class="nd-footer-toggle d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#ftCo" aria-expanded="false">Company</button>
                <div class="nd-footer-title d-none d-md-block">Company</div>
                <div class="collapse d-md-block" id="ftCo">
                    <ul class="nd-footer-links">
                        <li><a href="<?php echo nectra_nav_path('/about'); ?>">About</a></li>
                        <li><a href="<?php echo nectra_nav_path('/portfolio'); ?>">Portfolio</a></li>
                        <li><a href="<?php echo nectra_nav_path('/insights'); ?>">Insights</a></li>
                        <li><a href="<?php echo nectra_nav_path('/careers'); ?>">Careers</a></li>
                        <li><a href="<?php echo nectra_nav_path('/contact'); ?>">Contact</a></li>
                        <li><a href="<?php echo nectra_nav_path('/privacy'); ?>">Privacy</a></li>
                        <li><a href="<?php echo nectra_nav_path('/terms'); ?>">Terms</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="nd-footer-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>&copy; <?php echo date('Y'); ?> Nectra Digital. All rights reserved.</div>
            <div class="d-flex flex-wrap gap-3">
                <a href="<?php echo nectra_nav_path('/sitemap.xml'); ?>" class="text-white-50 text-decoration-none">Sitemap</a>
                <a href="<?php echo nectra_nav_path('/llms.txt'); ?>" class="text-white-50 text-decoration-none">LLMs.txt</a>
            </div>
        </div>
    </div>
</footer>
