<?php
/**
 * Dynamic city landing page template
 * Accessed via /digital-agency-{city}
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/seo-data.php';
require_once __DIR__ . '/includes/seo-components.php';
require_once __DIR__ . '/includes/local-page-seo.php';
require_once __DIR__ . '/includes/eeat-copy.php';
require_once __DIR__ . '/includes/i18n.php';

$city_slug = isset($_GET['city']) ? preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['city'])) : '';
$cities = get_cities_data();

if (!$city_slug) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

$city = $cities[$city_slug] ?? null;
if (!$city && function_exists('ge_city_from_slug')) {
    require_once __DIR__ . '/includes/service-city-resolver.php';
    $city = ge_city_from_slug($city_slug);
}

if (!$city) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

$city_name = $city['name'];
$city_state = $city['state'] ?? '';
$is_hq = !empty($city['is_hq']);

$hubSeo = ge_city_hub_seo($city, $city_slug);
$page_title = $hubSeo['page_title'];
$page_desc = $hubSeo['page_desc'];
$page_keys = $hubSeo['page_keys'];
$canonical_url = nectra_normalize_canonical_url($hubSeo['canonical_url']);
$og_type = $hubSeo['og_type'];
$breadcrumbs = $hubSeo['breadcrumbs'];
$city_faqs = ge_city_hub_faqs($city, $city_slug);

$page_schema = [
    get_breadcrumb_schema($breadcrumbs),
    [
        '@type' => 'WebPage',
        '@id' => $canonical_url . '#webpage',
        'url' => $canonical_url,
        'name' => $hubSeo['h1'],
        'description' => $page_desc,
        'isPartOf' => ['@id' => SITE_URL . '/#website'],
        'about' => ['@id' => SITE_URL . '/#organization'],
    ],
];

include __DIR__ . '/includes/header.php';
render_local_business_schema($city, $city_slug);
output_faq_schema($city_faqs);
?>

<main class="svc-page">
    <header class="svc-hero">
        <div class="svc-hero-glow"></div>
        <div class="container position-relative z-1 py-4">
            <?php render_breadcrumbs($breadcrumbs); ?>
            <h1 class="fw-bold text-white mb-3 nd-hero-title"><?php echo htmlspecialchars($hubSeo['h1']); ?></h1>
            <p class="lead text-white-50 mb-4" style="max-width: 820px; line-height: 1.6;">
                <?php echo htmlspecialchars($hubSeo['hero_intro']); ?>
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="/contact?city=<?php echo urlencode($city_name); ?>&amp;service=Software+Development" class="btn btn-primary btn-lg px-4 fw-semibold">Free Software Consultation in <?php echo htmlspecialchars($city_name); ?></a>
                <a href="/contact?service=Consultation&amp;city=<?php echo urlencode($city_name); ?>" class="btn btn-outline-light btn-lg px-4">Book Free Consultation</a>
            </div>
        </div>
    </header>

    <!-- TRUST STRIP -->
    <?php include __DIR__ . '/includes/ui/trust-bar.php'; ?>

    <!-- QUICK ANSWER (AEO / GEO / AI Search Optimized) -->
    <section class="py-4 bg-white border-bottom svc-quick-answer-section">
        <div class="container">
            <div class="svc-quick-answer">
                <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">
                    <i class="fas fa-bolt"></i> Quick Answer
                </div>
                <p class="text-dark mb-0 fw-medium" style="font-size: 1.05rem; line-height: 1.6;">
                    Nectra Digital provides comprehensive search engine optimization, paid media management, AI automation, and bespoke software engineering to companies in <?php echo htmlspecialchars($city_name); ?>, <?php echo htmlspecialchars($city_state); ?>. Every client strategy is anchored in proven technical SEO audits, transparent reporting, and measurable commercial ROI.
                </p>
            </div>
        </div>
    </section>

    <!-- LOCAL SERVICES CATALOG -->
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container py-3">
            <div class="text-center mb-5">
                <h2 class="h3 fw-bold text-dark mb-2 nd-section-title">Digital Services in <span class="text-primary"><?php echo htmlspecialchars($city_name); ?></span></h2>
                <p class="text-muted small mx-auto mb-0" style="max-width:640px;">Browse every specialized service we deliver in <?php echo htmlspecialchars($city_name); ?> — each page includes process, deliverables, local context, and proposal requests.</p>
            </div>
            <div class="row g-4">
                <?php
                $all_services = get_services_data();
                if (is_file(__DIR__ . '/includes/db.local.php') && file_exists(__DIR__ . '/includes/growth/bootstrap.php')) {
                    try {
                        require_once __DIR__ . '/includes/growth/bootstrap.php';
                        if (function_exists('ge_is_ready') && ge_is_ready()) {
                            require_once __DIR__ . '/includes/service-content.php';
                            foreach (\Growth\Models\Service::all(true) as $dbSvc) {
                                if (!isset($all_services[$dbSvc['slug']])) {
                                    $all_services[$dbSvc['slug']] = ge_minimal_service_from_record($dbSvc);
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // static catalog only
                    }
                }
                foreach ($all_services as $slug => $s):
                    $serviceHref = ge_service_city_landing_url($slug, $city_slug);
                    $silo = $s['silo'] ?? ($s['h1'] ?? 'Service');
                ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo htmlspecialchars($serviceHref); ?>" class="text-decoration-none d-block h-100">
                        <div class="nd-card p-4 h-100 d-flex flex-column">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="fas <?php echo htmlspecialchars($s['icon'] ?? 'fa-rocket'); ?> fa-lg"></i>
                                </div>
                                <div>
                                    <span class="badge bg-light text-secondary border mb-1" style="font-size:0.65rem;"><?php echo htmlspecialchars($silo); ?></span>
                                    <h3 class="h6 fw-bold text-dark mb-1"><?php echo htmlspecialchars($silo . ' in ' . $city_name); ?></h3>
                                </div>
                            </div>
                            <p class="text-muted small mb-3 flex-grow-1"><?php echo htmlspecialchars(ge_city_service_card_teaser($slug, $city_slug, $city, $s)); ?></p>
                            <span class="text-primary small fw-semibold">View City Page <i class="fas fa-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- WHY CITY BUSINESSES CHOOSE US -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container py-3">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <h2 class="h3 fw-bold text-dark mb-4 nd-section-title">Why <?php echo htmlspecialchars($city_name); ?> Businesses Choose Nectra Digital</h2>
                    <ul class="list-unstyled">
                        <?php foreach (ge_city_why_choose_bullets($city, $city_slug) as $bullet): ?>
                        <li class="mb-3 d-flex align-items-start gap-2 text-muted">
                            <i class="fas fa-check-circle text-primary mt-1"></i>
                            <span class="text-dark small"><?php echo htmlspecialchars($bullet); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-lg-5">
                    <div class="p-4 rounded-3 shadow-sm bg-white border" style="border-left: 4px solid var(--nd-primary) !important;">
                        <h3 class="h6 fw-bold text-dark mb-2"><i class="fas fa-chart-line text-primary me-2"></i>Performance Guarantee</h3>
                        <p class="text-muted small mb-0">Our local campaigns are tailored to local search intent, Google Maps 3-pack dominance, and localized conversion funnels with no cookie-cutter templates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PAN-INDIA HUBS -->
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container py-2">
            <div class="text-center mb-4">
                <h2 class="h5 fw-bold text-dark mb-2 nd-section-title">Other Cities We Serve</h2>
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php
                $allCities = get_cities_data();
                foreach ($allCities as $slug => $c):
                    if ($slug === $city_slug) continue;
                ?>
                <a href="<?php echo ge_city_hub_path($slug); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-dark bg-white" style="border-color: #E2E8F0; font-size: 0.8rem;">
                    <i class="fas fa-location-dot text-primary me-1" style="font-size: 0.75rem;"></i> <?php echo htmlspecialchars($c['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- INLINE CONTACT FORM -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container py-3">
            <div class="row g-5 align-items-center justify-content-center">
                <div class="col-lg-6">
                    <h2 class="h3 fw-bold text-dark mb-3 nd-section-title">Get a Free Audit in <span class="text-primary"><?php echo htmlspecialchars($city_name); ?></span></h2>
                    <p class="text-muted mb-0">Tell us about your business in <?php echo htmlspecialchars($city_name); ?> — we will share a tailored SEO and digital marketing roadmap with clear next steps within 24 hours.</p>
                </div>
                <div class="col-lg-5">
                    <div class="p-4 rounded-4 shadow-sm bg-white border">
                        <?php
                        $form_service = 'Digital Marketing';
                        $form_city = $city_name;
                        $form_instance = 'city-hub';
                        include __DIR__ . '/includes/partials/contact-form-inline.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQS & CTAS -->
    <?php render_faq_section($city_faqs, "Software Development & Growth FAQ — {$city_name}"); ?>
    <?php include __DIR__ . '/includes/ui/cta.php'; ?>
    <?php include __DIR__ . '/includes/ui/author-box.php'; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
