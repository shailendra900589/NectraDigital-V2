<?php 
require_once 'includes/seo-data.php';
require_once 'includes/seo-components.php';
require_once 'includes/service-content.php';
require_once 'includes/i18n.php';
require_once 'includes/eeat-copy.php';

$page_title = "Software Development Services India | Web, Mobile & SaaS";
$page_desc = "Custom software development, SaaS, web & mobile apps, APIs, AI automation, and SEO — one engineering-led team. 17 specialized services from MVP to enterprise scale.";
$page_keys = "Software Development Services India, Custom Software, SaaS Development, Web Development, Mobile App Development, API Development";
$canonical_url = nectra_page_canonical('/services');
$isDarkHeader = true;
require_once __DIR__ . '/includes/nav-data.php';

$page_schema = [get_breadcrumb_schema([
    ['name' => 'Home', 'url' => SITE_URL . '/'],
    ['name' => 'Services', 'url' => SITE_URL . '/services']
])];

$all_services = get_services_data();
$silo_groups = [
    'Engineering & Development' => [
        'software-development-services',
        'web-development-services',
        'mobile-app-development',
        'ecommerce-development',
    ],
    'AI & Automation' => [
        'ai-automation-services',
        'ai-chatbot-development',
        'whatsapp-ai-bot-development',
    ],
    'Growth & Marketing' => [
        'seo-services',
        'local-seo-services',
        'technical-seo-services',
        'enterprise-seo-services',
        'digital-marketing-services',
        'performance-marketing-services',
        'ppc-management',
        'google-ads-management',
        'meta-ads-services',
        'social-media-marketing-services',
    ],
];

include 'includes/header.php';
?>

<!-- =====================================================================
     HERO: Service Directory
===================================================================== -->
<header class="svc-hero nd-page-hero">
    <div class="svc-hero-glow"></div>
    <div class="container text-center position-relative z-1 py-2 py-md-4">
        <?php render_breadcrumbs([['name' => 'Home', 'url' => '/'], ['name' => 'Services', 'url' => '/services']]); ?>
        <h1 class="fw-bold text-white mb-3 nd-hero-title">Software Engineering &amp; <span style="color: var(--nd-primary-blue);">Digital Product Services</span></h1>
        <p class="svc-hero-deck mx-auto mb-4" style="max-width: 640px;">17 specialized services — custom software, web &amp; mobile apps, AI automation, then SEO and paid growth.</p>
        <p class="lead text-white-50 mx-auto mb-4 nd-prose-width">Build SaaS, APIs, portals, and storefronts with a team that also handles technical SEO, performance marketing, and automation when you need go-to-market support.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?php echo nectra_nav_path('/contact?service=Consultation'); ?>" class="btn btn-primary btn-lg px-4 fw-semibold">Book Free Consultation</a>
            <a href="<?php echo nectra_nav_path('/contact?service=Free+Audit'); ?>" class="btn btn-outline-light btn-lg px-4">Get Free Audit</a>
        </div>
    </div>
</header>

<!-- =====================================================================
     TRUST STRIP: Verified Numbers
===================================================================== -->
<section class="py-4 border-top border-bottom" style="background: var(--nd-brand-navy);">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3">
                <div class="svc-stat-val"  style="color: var(--nd-primary);">17</div>
                <div class="svc-stat-lbl text-white-50 small">Services</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="svc-stat-val" style="color: var(--nd-primary);">200+</div>
                <div class="svc-stat-lbl text-white-50 small">Projects</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="svc-stat-val" style="color: var(--nd-primary);">340%</div>
                <div class="svc-stat-lbl text-white-50 small">Avg. Growth</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="svc-stat-val" style="color: var(--nd-primary);"><?php echo htmlspecialchars(nectra_trust_rating_label()); ?></div>
                <div class="svc-stat-lbl text-white-50 small">Client Feedback</div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     ALL SERVICES GRID (Light Background)
===================================================================== -->
<section class="py-5" style="background: var(--nd-bg);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h3 fw-bold text-dark nd-section-title">Explore Our <span style="color: var(--nd-primary);">Services</span></h2>
            <p class="text-muted mx-auto" style="max-width: 650px;">Every service page includes detailed process, deliverables, FAQs, and industry expertise — so you know exactly what you're getting.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($all_services as $slug => $service):
                $ext = get_service_extended($slug, $service);
                $tagline = $ext['tagline'] ?? $service['silo'];
            ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?php echo nectra_nav_path('/' . $slug); ?>" class="text-decoration-none d-block h-100">
                    <div class="nd-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background: rgba(37,99,235,0.1); color: var(--nd-primary);">
                                <i class="fas <?php echo $service['icon']; ?> fa-lg"></i>
                            </div>
                            <div>
                                <span class="badge bg-light text-secondary border mb-1" style="font-size: 0.65rem;"><?php echo htmlspecialchars($service['silo']); ?></span>
                                <h2 class="h6 fw-bold text-dark mb-0"><?php echo htmlspecialchars($service['h1']); ?></h2>
                                <p class="small mb-0 mt-1" style="color: var(--nd-primary); font-size: 0.75rem;"><?php echo htmlspecialchars($tagline); ?></p>
                            </div>
                        </div>
                        <p class="text-muted small mb-3 flex-grow-1"><?php echo htmlspecialchars(mb_substr($service['intro'], 0, 130)); ?>...</p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php foreach (array_slice($service['features'] ?? [], 0, 3) as $feat): ?>
                            <span class="badge bg-light text-dark border" style="font-size: 0.7rem;"><?php echo htmlspecialchars($feat); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <span class="text-primary small fw-semibold">View Full Service <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =====================================================================
     SILO CATEGORY GROUPS
===================================================================== -->
<?php $groupIdx = 0; foreach ($silo_groups as $group_name => $slugs): $groupIdx++; ?>
<section class="py-5 <?php echo ($groupIdx % 2 === 0) ? '' : 'bg-white'; ?> border-top" style="background: <?php echo ($groupIdx % 2 === 0) ? 'var(--nd-bg)' : '#FFFFFF'; ?>;">
    <div class="container">
        <h2 class="h4 fw-bold text-dark mb-2"><?php
            if (strpos($group_name, ' & ') !== false) {
                [$a, $b] = explode(' & ', $group_name, 2);
                echo htmlspecialchars($a) . ' <span style="color: var(--nd-primary);">' . htmlspecialchars($b) . '</span>';
            } else {
                echo '<span style="color: var(--nd-primary);">' . htmlspecialchars($group_name) . '</span>';
            }
        ?></h2>
        <p class="text-muted small mb-4">Specialized <?php echo strtolower($group_name); ?> solutions for Indian and global businesses.</p>
        <div class="row g-3">
            <?php foreach ($slugs as $slug):
                if (!isset($all_services[$slug])) continue;
                $s = $all_services[$slug];
            ?>
            <div class="col-md-6 col-lg-3">
                <a href="<?php echo nectra_nav_path('/' . $slug); ?>" class="d-flex align-items-center p-3 border rounded text-decoration-none h-100 nd-card">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0 me-3" style="width: 36px; height: 36px; background: rgba(37,99,235,0.1); color: var(--nd-primary);">
                        <i class="fas <?php echo $s['icon']; ?>"></i>
                    </div>
                    <span class="text-dark small fw-medium"><?php echo htmlspecialchars($s['h1']); ?></span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<!-- =====================================================================
     CITY LOCATIONS
===================================================================== -->
<section id="locations" class="py-5 nd-section-dark">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-white h3 fw-bold mb-2">Service Locations Across <span style="color: var(--nd-primary);">India</span></h2>
            <p class="text-white-50 small mx-auto mb-0" style="max-width: 620px;">Local SEO, digital marketing, and development services in major cities — each location hub links to every service page for that city.</p>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <?php foreach (get_cities_data() as $slug => $c): ?>
            <a href="<?php echo nectra_nav_path('/digital-agency-' . $slug); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-white" style="border-color: rgba(255,255,255,0.15); font-size: 0.8rem;">
                <i class="fas fa-location-dot me-1" style="color: var(--nd-primary); font-size: 0.75rem;"></i>
                <?php echo htmlspecialchars($c['name']); ?><?php if (!empty($c['state'])): ?> <span class="opacity-75">· <?php echo htmlspecialchars($c['state']); ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =====================================================================
     NOT SURE CTA
===================================================================== -->
<section class="py-5 bg-white border-top">
    <div class="container text-center">
        <h2 class="h4 fw-bold text-dark mb-3">Not Sure Which Service You Need?</h2>
        <p class="text-muted mb-4 mx-auto" style="max-width: 550px;">Book a free strategy call. We'll audit your current performance and recommend the right mix of SEO, ads, AI, and development.</p>
        <a href="<?php echo nectra_nav_path('/contact?service=Strategy+Call'); ?>" class="btn btn-primary btn-lg px-5 fw-semibold">Book Free Strategy Call</a>
    </div>
</section>

<?php include __DIR__ . '/includes/ui/cta.php'; ?>
<?php include __DIR__ . '/includes/ui/author-box.php'; ?>
<?php include __DIR__ . '/includes/ui/trust-bar.php'; ?>

<?php include 'includes/footer.php'; ?>
