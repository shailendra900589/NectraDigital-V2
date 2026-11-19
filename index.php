<?php
require_once 'includes/seo-data.php';
require_once 'includes/seo-components.php';
require_once 'includes/i18n.php';
require_once 'includes/eeat-copy.php';
require_once __DIR__ . '/includes/site-contact.php';
require_once __DIR__ . '/includes/nav-data.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Software Development Company India | Web, Mobile & SaaS | Nectra Digital';
$page_desc = 'Nectra Digital builds custom software, SaaS, web & mobile apps for India and global clients. Agile engineering, secure APIs, SEO-ready launches — 200+ products shipped.';
$page_keys = 'software development company India, custom software development, SaaS development India, web development company, mobile app development, software engineering agency India';
$canonical_url = nectra_page_canonical('/');
$isDarkHeader = true;

$page_schema = [
    get_breadcrumb_schema([['name' => 'Home', 'url' => SITE_URL . '/']]),
    get_home_local_business_schema(),
];

include 'includes/header.php';
output_faq_schema(get_homepage_faqs());

include __DIR__ . '/includes/home/hero-dashboard.php';
include __DIR__ . '/includes/ui/trust-bar.php';
?>

<section class="nd-section nd-section-alt py-4">
    <div class="container">
        <div class="row align-items-center text-center text-lg-start g-3">
            <div class="col-lg-2"><span class="text-muted small text-uppercase fw-semibold">Trusted Tech</span></div>
            <div class="col-lg-10">
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-4 nd-home-tech-icons">
                    <i class="fab fa-react fa-2x" title="React"></i>
                    <i class="fab fa-aws fa-2x" title="AWS"></i>
                    <i class="fab fa-python fa-2x" title="Python"></i>
                    <i class="fab fa-google fa-2x" title="Google"></i>
                    <i class="fab fa-shopify fa-2x" title="Shopify"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/includes/home/enterprise-sections.php';
?>

<section class="nd-section-dark nd-section">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-7">
                <h2 class="h3 fw-bold text-white mb-4">Build Products That <span class="nd-text-gradient">Scale &amp; Rank</span></h2>
                <p class="text-white-50">Nectra Digital is a <a href="<?php echo nectra_nav_path('/software-development-services'); ?>" class="text-tech-blue text-decoration-none">software development company</a> that ships SaaS platforms, internal tools, APIs, and customer-facing apps — then layers <a href="<?php echo nectra_nav_path('/seo-services'); ?>" class="text-tech-blue text-decoration-none">SEO</a>, <a href="<?php echo nectra_nav_path('/web-development-services'); ?>" class="text-tech-blue text-decoration-none">web development</a>, and <a href="<?php echo nectra_nav_path('/ai-automation-services'); ?>" class="text-tech-blue text-decoration-none">AI automation</a> when you need measurable growth.</p>
                <p class="text-white-50">One squad owns discovery, engineering, QA, deployment, and post-launch optimization so you are not juggling separate vendors for code and marketing.</p>
                <ul class="text-white-50">
                    <li class="mb-2"><strong class="text-white">Engineering-first delivery</strong> — Python, Laravel, React, Next.js, cloud-native architecture</li>
                    <li class="mb-2"><strong class="text-white">200+ projects shipped</strong> — MVPs, enterprise portals, mobile apps, marketplaces</li>
                    <li class="mb-2"><strong class="text-white">Growth-ready launches</strong> — technical SEO, Core Web Vitals, analytics, and conversion paths built in</li>
                </ul>
            </div>
            <div class="col-lg-5">
                <?php render_nap_block('home'); ?>
                <div class="nd-card nd-card-dark p-4 mt-4">
                    <h3 class="h6 text-white mb-2">Free Product &amp; SEO Review</h3>
                    <p class="text-white-50 small mb-3">Architecture snapshot, speed checks, and a prioritized build + visibility plan.</p>
                    <a href="<?php echo nectra_nav_path('/contact?service=Book+Consultation'); ?>" class="btn btn-nectra-primary btn-sm w-100 nd-btn-motion"><i class="fas fa-search me-1"></i>Book Free Consultation</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="nd-section-dark nd-section border-top border-secondary border-opacity-25">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h3 text-white">Answer Engine <span class="nd-text-gradient">Optimized</span> Insights</h2>
            <p class="text-white-50 small">Direct answers on software development, SaaS, and growth — optimized for Google AI Overviews, ChatGPT, and Perplexity.</p>
        </div>
        <div class="row g-4">
            <?php foreach (array_slice(get_aeo_answers(), 0, 3, true) as $key => $aeo): ?>
            <div class="col-md-4">
                <div class="nd-card nd-card-dark p-4 h-100">
                    <h3 class="h6 text-white mb-2"><?php echo htmlspecialchars($aeo['question']); ?></h3>
                    <p class="text-white-50 small mb-3"><?php echo htmlspecialchars($aeo['quick_answer']); ?></p>
                    <a href="<?php echo nectra_nav_path('/aeo#' . $key); ?>" class="text-tech-blue small text-decoration-none">Read full answer →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo nectra_nav_path('/aeo'); ?>" class="btn btn-nectra-outline-light btn-sm">View All AEO Answers</a>
        </div>
    </div>
</section>

<section class="nd-section">
    <div class="container text-center">
        <h2 class="nd-section-title">Serving Businesses Across <span class="text-primary-blue">India</span></h2>
        <p class="text-muted small mb-4">Software engineering &amp; digital delivery in 20+ cities across India</p>
        <div class="d-flex flex-wrap justify-content-center gap-2 nd-city-chips" data-nd-stagger>
            <?php foreach (get_cities_data() as $slug => $c): ?>
            <a href="<?php echo nectra_nav_path('/digital-agency-' . $slug); ?>" class="btn btn-sm btn-outline-secondary rounded-pill"><?php echo htmlspecialchars($c['name']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_founder_section(); ?>

<section id="contact" class="nd-contact-section nd-no-section-animate" aria-labelledby="contact-heading">
    <div class="container">
        <div class="nd-contact-shell">
            <header class="nd-contact-head text-center">
                <h2 id="contact-heading" class="nd-section-title">Ready to Build Your Next Product?</h2>
                <p class="text-muted mb-0 nd-section-sub">Tell us your software, web, or mobile goals — we reply within 24 hours with next steps.</p>
            </header>
            <div class="nd-contact-panel">
                <form id="contactForm" class="nd-ajax-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="d-none"><input type="text" name="website_url" autocomplete="off"></div>
                    <div class="mb-3">
                        <label class="form-label" for="contact-name">Full Name</label>
                        <input type="text" name="name" id="contact-name" class="form-control" required autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contact-email">Business Email</label>
                        <input type="email" name="email" id="contact-email" class="form-control" required autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contact-service">Requirement</label>
                        <select name="service" id="contact-service" class="form-select" required>
                            <option value="" disabled selected>Select Requirement</option>
                            <option value="Software Development">Software Development</option>
                            <option value="Web Development">Website Development</option>
                            <option value="Mobile App Development">Mobile App Development</option>
                            <option value="Free SEO Audit">Get Free SEO Audit</option>
                            <option value="Book Consultation">Book Free Consultation</option>
                            <option value="Request Proposal">Request Proposal</option>
                            <option value="SEO Services">SEO Services</option>
                            <option value="AI Automation">AI Automation</option>
                            <option value="Digital Marketing">Digital Marketing</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-nectra-primary w-100 nd-btn-motion"><i class="fas fa-headset me-2"></i>Talk To Expert</button>
                    <div id="responseMsg" class="response-msg mt-3 text-center small" aria-live="polite"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
render_faq_section(get_homepage_faqs(), 'Software Development & Digital Growth FAQ');
render_cta_blocks();
include 'includes/footer.php';
