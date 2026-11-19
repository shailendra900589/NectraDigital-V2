<?php 
require_once 'includes/seo-data.php';
require_once 'includes/seo-components.php';
require_once 'includes/i18n.php';
require_once 'includes/eeat-copy.php';
require_once 'includes/nav-data.php';

$page_title = "About Nectra Digital | Software Development Company India";
$page_desc = "Meet Nectra Digital — custom software, web & mobile engineering company in Lucknow. 200+ products shipped with Agile delivery, full code ownership, and growth-ready launches.";
$page_keys = "About Nectra Digital, software development company India, custom software Lucknow, SaaS development agency";
$isDarkHeader = true;
$canonical_url = nectra_page_canonical('/about');

$page_schema = [
    get_breadcrumb_schema([
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'About', 'url' => SITE_URL . '/about']
    ]),
    get_founder_schema(),
];

include 'includes/header.php';
?>

<main style="background: #F8FAFC; min-height: 100vh;">
    <!-- HERO -->
    <header class="nd-page-hero nd-section-dark text-center position-relative overflow-hidden">
        <div class="nd-page-hero-glow" aria-hidden="true"></div>
        <div class="container position-relative z-1 py-2 py-md-3">
            <?php render_breadcrumbs([['name' => 'Home', 'url' => '/'], ['name' => 'About', 'url' => '/about']]); ?>
            <h1 class="fw-bold text-white mb-3 nd-hero-title">About Nectra Digital — <span class="nd-text-gradient">Software &amp; Product Partner</span></h1>
            <p class="lead text-white-50 mx-auto nd-prose-width" style="line-height: 1.6;">
                A founder-led software development company combining SaaS engineering, web and mobile apps, AI automation, and SEO — so products you ship are fast, secure, and built to acquire users in India and worldwide.
            </p>
        </div>
    </header>

    <!-- TRUST BAR -->
    <?php include __DIR__ . '/includes/ui/trust-bar.php'; ?>

    <!-- FOUNDER & LEADERSHIP STORY -->
    <section class="py-5 bg-white border-bottom">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-4 text-center">
                    <div class="p-4 rounded-4 shadow-sm bg-white border d-inline-block">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary mb-3" style="width:160px; height:160px;">
                            <i class="fas fa-user-tie fa-4x"></i>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-1"><?php echo FOUNDER_NAME; ?></h2>
                        <span class="badge bg-primary px-3 py-1 mb-2"><?php echo FOUNDER_TITLE; ?></span>
                        <p class="text-muted small mb-3"><?php echo FOUNDER_EXPERIENCE; ?> Practical Industry Experience</p>
                        <a href="<?php echo FOUNDER_LINKEDIN; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fab fa-linkedin me-2"></i>Connect on LinkedIn
                        </a>
                    </div>
                </div>

                <div class="col-lg-8">
                    <h2 class="h3 fw-bold text-dark mb-3 nd-section-title">Engineering Discipline Meets Growth Marketing</h2>
                    <p class="text-muted" style="line-height: 1.7;">
                        Ravindra Kumar Chauhan founded Nectra Digital with a clear objective: eliminate the disconnect between technical engineering and revenue-generating digital marketing. With <?php echo FOUNDER_EXPERIENCE; ?> of hands-on experience across SEO architecture, algorithmic search optimization, AI agent pipelines, and web engineering, he has scaled Nectra Digital into a trusted partner for over 200+ organizations across India and international markets.
                    </p>
                    <blockquote class="p-3 rounded-3 bg-light border-start border-primary border-4 my-4">
                        <p class="fst-italic text-dark mb-0 small" style="line-height: 1.6;">
                            "We do not sell cookie-cutter templates or empty retainer hours. We build digital assets engineered for speed, security, and algorithmic authority. Every SEO strategy, line of code, and automation workflow is designed with a direct link to ROI."
                        </p>
                    </blockquote>
                    <h3 class="h6 fw-bold text-dark mb-2">Core Competencies</h3>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php foreach (FOUNDER_EXPERTISE as $skill): ?>
                        <span class="badge bg-light text-secondary border px-3 py-2" style="font-size: 0.8rem;"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="<?php echo nectra_nav_path('/contact'); ?>" class="btn btn-primary px-4 fw-medium">Schedule Strategy Session</a>
                        <a href="<?php echo nectra_nav_path('/portfolio'); ?>" class="btn btn-outline-secondary px-4">View Case Studies</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CORE COMPANY VALUES / CODE -->
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="h3 fw-bold text-dark nd-section-title">Our Engineering Code</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">The non-negotiable principles governing every single client deliverable.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="nd-card p-4 h-100 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary mb-3" style="width: 48px; height: 48px;">
                            <i class="fas fa-gauge-high fa-lg"></i>
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-2">Velocity &amp; Precision</h3>
                        <p class="text-muted small mb-0">Sub-second Core Web Vitals, organic search traction within measurable windows, and zero technical bloat in our builds.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nd-card p-4 h-100 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary mb-3" style="width: 48px; height: 48px;">
                            <i class="fas fa-shield-halved fa-lg"></i>
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-2">Transparency &amp; E-E-A-T</h3>
                        <p class="text-muted small mb-0">Verified expert attribution, published editorial standards, and transparent reporting on KPIs tied directly to business cash flow.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nd-card p-4 h-100 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary mb-3" style="width: 48px; height: 48px;">
                            <i class="fas fa-brain fa-lg"></i>
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-2">Automation First</h3>
                        <p class="text-muted small mb-0">We actively automate manual lead routing, client CRM updates, and technical auditing so our clients scale without proportional headcounts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-2">
            <div class="text-center mb-5">
                <h2 class="h3 fw-bold text-dark nd-section-title">How We Work</h2>
                <p class="text-muted mx-auto mb-0" style="max-width: 36rem;">Our full delivery model — discover, build, test, launch, and scale with you.</p>
            </div>
            <?php include __DIR__ . '/includes/ui/process.php'; ?>
        </div>
    </section>

    <!-- FINAL CTA -->
    <?php include __DIR__ . '/includes/ui/cta.php'; ?>
</main>

<?php include 'includes/footer.php'; ?>
