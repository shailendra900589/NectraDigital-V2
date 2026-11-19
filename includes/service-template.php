<?php
/**
 * Premium service page template — broad content, conversion-focused layout
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/seo-data.php';
require_once __DIR__ . '/seo-components.php';
require_once __DIR__ . '/service-content.php';
require_once __DIR__ . '/local-page-seo.php';
require_once __DIR__ . '/site-contact.php';
require_once __DIR__ . '/service-city-unique.php';
require_once __DIR__ . '/eeat-copy.php';
require_once __DIR__ . '/i18n.php';

if (!isset($service_slug)) {
    header('Location: ' . SITE_URL . '/services');
    exit;
}

$is_city_page = !empty($is_city_page);

if (empty($service) || !is_array($service) || !isset($service['process'])) {
    $services = get_services_data();
    $serviceBase = $services[$service_slug] ?? null;

    if ($serviceBase === null) {
        if (is_file(__DIR__ . '/db.local.php') && file_exists(__DIR__ . '/growth/bootstrap.php')) {
            require_once __DIR__ . '/growth/bootstrap.php';
            if (function_exists('ge_is_ready') && ge_is_ready()) {
                $dbService = \Growth\Models\Service::findBySlug($service_slug);
                if ($dbService) {
                    $serviceBase = ge_minimal_service_from_record($dbService);
                }
            }
        }
    }

    if ($serviceBase === null) {
        header('Location: ' . SITE_URL . '/404.php');
        exit;
    }

    $service = get_service_extended($service_slug, $serviceBase);
}

if ($is_city_page) {
    $service['h1'] = $localized_h1 ?? $service['h1'];
    $service['intro'] = $localized_intro ?? $service['intro'];
    $page_title = $page_title ?? $service['title'];
    $page_desc = $page_desc ?? $service['meta_desc'];
    $page_keys = $page_keys ?? ($service['keywords'] ?? '');
    $canonical_url = !empty($canonical_url) ? nectra_normalize_canonical_url($canonical_url) : nectra_page_canonical('/' . $service_slug);
    $og_type = $og_type ?? 'website';
    $overview = ($service['overview'] ?? '<p>' . htmlspecialchars($service['intro']) . '</p>');
    if (!empty($localized_overview_extra)) {
        $overview .= $localized_overview_extra;
    }
    $service['faqs'] = $localized_faqs ?? $service['faqs'];
    $city_quick_answer = $quick_answer ?? $service['intro'];
    $form_service = ($service['silo'] ?? $service['h1']) . ' — ' . ($city_name ?? '');
    $form_city = $city_name ?? '';
    if (empty($breadcrumbs)) {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => SITE_URL . '/'],
            ['name' => 'Locations', 'url' => ge_locations_url()],
            ['name' => $city_name ?? 'Location', 'url' => ge_city_hub_url($city_slug ?? '')],
            ['name' => $service['silo'] ?? $service['h1'], 'url' => SITE_URL . ge_service_city_landing_url($service_slug, $city_slug ?? '')],
        ];
    }
} else {
    $page_title = $service['title'];
    $page_desc = $service['meta_desc'];
    $canonical_url = nectra_page_canonical('/' . $service_slug);
    $overview = $service['overview'] ?? '<p>' . htmlspecialchars($service['intro']) . '</p>';
    $city_quick_answer = $service['intro'];
    $form_service = $service['h1'];
    $form_city = '';
    $breadcrumbs = [
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Services', 'url' => SITE_URL . '/services'],
        ['name' => $service['h1'], 'url' => SITE_URL . '/' . $service_slug],
    ];
}
$page_schema = [get_breadcrumb_schema($breadcrumbs)];
$isDarkHeader = true;
$navAdaptive = false;
if ($is_city_page && !empty($canonical_url)) {
    $page_schema[] = [
        '@type' => 'WebPage',
        '@id' => rtrim($canonical_url, '/') . '#webpage',
        'url' => rtrim($canonical_url, '/'),
        'name' => $service['h1'] ?? $page_title,
        'description' => $page_desc ?? '',
        'isPartOf' => ['@id' => SITE_URL . '/#website'],
        'about' => ['@id' => SITE_URL . '/#organization'],
    ];
}

require_once __DIR__ . '/growth/engines/IntentKeywordEngine.php';
if ($is_city_page) {
    // page_keys set by resolver via IntentKeywordEngine
} else {
    $page_keys = \Growth\Engines\IntentKeywordEngine::forStaticPage($service_slug, $service['keywords'] ?? '');
}
$tagline = $is_city_page && !empty($localized_h2)
    ? $localized_h2
    : ($service['tagline'] ?? 'Results-Driven ' . ($service['silo'] ?? 'Digital') . ' by Nectra Digital');
$benefits = $service['benefits'] ?? [];
$paa = array_merge($service['paa'] ?? [], array_slice($service['faqs'] ?? [], 0, 2));

include __DIR__ . '/header.php';
require_once __DIR__ . '/ui/section-head.php';
render_service_schema($service_slug, $service);
if ($is_city_page && !empty($city)) {
    render_local_business_schema($city, $city_slug ?? '');
}
?>

<main class="svc-page">
    <!-- HERO (High-End Dark Navy Hero) -->
    <header class="svc-hero nd-page-hero">
        <div class="nd-hero-mesh" aria-hidden="true"></div>
        <div class="svc-hero-glow"></div>
        <div class="container position-relative z-1 py-4">
            <?php render_breadcrumbs($breadcrumbs); ?>
            <div class="row svc-hero-row g-4 g-lg-5 py-3">
                <div class="col-lg-7 svc-hero-copy">
                    <?php if ($is_city_page && !empty($city_name)): ?>
                    <h1 class="svc-hero-title fw-bold text-white mb-3 nd-hero-title"><?php echo htmlspecialchars($service['silo']); ?> in <span class="nd-text-gradient"><?php echo htmlspecialchars($city_name); ?></span></h1>
                    <?php else: ?>
                    <h1 class="svc-hero-title fw-bold text-white mb-3 nd-hero-title"><?php echo htmlspecialchars($service['h1']); ?></h1>
                    <?php endif; ?>
                    <?php if (!empty($tagline)): ?>
                    <p class="svc-hero-deck mb-3"><?php echo htmlspecialchars($tagline); ?></p>
                    <?php endif; ?>
                    <p class="lead text-white-50 mb-4 svc-hero-intro"><?php echo htmlspecialchars($service['intro']); ?></p>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="#serviceCityForm" class="btn btn-nectra-primary btn-lg nd-btn-motion"><i class="fas fa-paper-plane me-2"></i><?php echo $is_city_page ? 'Get Free Proposal in ' . htmlspecialchars($city_name) : 'Get Free Proposal'; ?></a>
                        <a href="<?php echo htmlspecialchars(nectra_web_path('/contact?service=Strategy+Call' . ($is_city_page ? '&city=' . urlencode($city_name) : ''))); ?>" class="btn btn-nectra-outline-light btn-lg nd-btn-motion"><i class="fas fa-phone-alt me-2"></i>Book Strategy Call</a>
                    </div>
                    <div class="d-flex flex-wrap gap-4 text-white-50 small">
                        <span><i class="fas fa-check-circle text-success me-1"></i> Free Consultation</span>
                        <span><i class="fas fa-check-circle text-success me-1"></i> Zero Long-Term Lock-in</span>
                        <span><i class="fas fa-check-circle text-success me-1"></i> ROI-Focused Delivery</span>
                    </div>
                </div>
                <div class="col-lg-5 svc-hero-aside">
                    <div class="svc-hero-stats-panel">
                        <div class="svc-hero-icon mb-3 nd-icon-float"><i class="fas <?php echo $service['icon']; ?>"></i></div>
                        <div class="row g-3">
                            <?php foreach ($service['stats'] as $stat): ?>
                            <div class="col-6">
                                <div class="svc-stat-box text-center p-3">
                                    <div class="svc-stat-val"><?php echo htmlspecialchars($stat['value']); ?></div>
                                    <div class="svc-stat-lbl"><?php echo htmlspecialchars($stat['label']); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- TRUST STRIP -->
    <?php include __DIR__ . '/ui/trust-bar.php'; ?>

    <!-- QUICK ANSWER BLOCK (Optimized for Featured Snippets, AEO & AI Search) -->
    <section class="py-4 bg-white border-bottom svc-quick-answer-section">
        <div class="container">
            <div class="svc-quick-answer">
                <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">
                    <i class="fas fa-bolt nd-icon-float"></i> Quick Answer
                </div>
                <p class="text-dark mb-0 fw-medium nd-text-lead">
                    <?php echo htmlspecialchars($city_quick_answer); ?>
                </p>
            </div>
        </div>
    </section>

    <!-- OVERVIEW & SIDEBAR CONVERSION FORM -->
    <section class="py-5 svc-section-overview">
        <div class="container py-2 py-md-3">
            <div class="svc-overview-layout">
                <div class="svc-overview-main">
                    <?php nd_render_section_head(
                        'Why Choose Our <span class="text-primary">' . htmlspecialchars($service['silo']) . '</span>' . ($is_city_page && !empty($city_name) ? ' in <span class="text-primary">' . htmlspecialchars($city_name) . '</span>' : '') . '?',
                        null,
                        'left'
                    ); ?>
                    <div class="svc-overview"><?php echo $overview; ?></div>
                    <?php include __DIR__ . '/partials/service-overview-extras.php'; ?>
                </div>
                <aside class="svc-overview-aside">
                    <div class="svc-sidebar-card p-4 svc-sidebar-sticky nd-interactive-zone" id="serviceCityForm" data-nd-interactive>
                        <h3 class="h6 fw-bold text-dark mb-3"><?php echo $is_city_page ? 'Start Your Project in ' . htmlspecialchars($city_name) : 'Start Your Project'; ?></h3>
                        <?php $form_instance = 'sidebar'; include __DIR__ . '/partials/contact-form-inline.php'; ?>
                        <hr class="my-3 svc-sidebar-divider">
                        <ul class="list-unstyled small mb-0 svc-checklist">
                            <li><i class="fas fa-check"></i> Free initial consultation</li>
                            <li><i class="fas fa-check"></i> Custom strategy proposal</li>
                            <li><i class="fas fa-check"></i> Transparent monthly reporting</li>
                            <li><i class="fas fa-check"></i> Dedicated account manager</li>
                            <li><i class="fas fa-check"></i> No long-term lock-in</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- BUSINESS CHALLENGES WE SOLVE -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container py-3">
            <?php nd_render_section_head(
                'Problems We Eliminate',
                'Most growing businesses face these critical roadblocks before partnering with Nectra Digital.',
                'center'
            ); ?>
            <div class="row g-4" data-nd-stagger>
                <?php foreach ($service['challenges'] as $ch): ?>
                <div class="col-md-4">
                    <div class="svc-challenge-card p-4 h-100">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary mb-3" style="width: 44px; height: 44px;">
                            <i class="fas <?php echo $ch['icon']; ?> fa-lg"></i>
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-2"><?php echo htmlspecialchars($ch['title']); ?></h3>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($ch['desc']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- KEY BENEFITS -->
    <?php if (!empty($benefits)): ?>
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container py-3">
            <?php nd_render_section_head('Key Value Deliverables', null, 'center'); ?>
            <div class="row g-4" data-nd-stagger>
                <?php foreach ($benefits as $b): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="svc-benefit-card p-4 h-100">
                        <div class="svc-benefit-icon mb-3"><i class="fas <?php echo $b['icon']; ?>"></i></div>
                        <h3 class="h6 fw-bold text-dark mb-2"><?php echo htmlspecialchars($b['title']); ?></h3>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($b['desc']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- PROCESS FRAMEWORK -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container py-3">
            <?php nd_render_section_head(
                'Our 5-Step Delivery Framework',
                'A structured, reproducible engineering process built for predictable ROI.',
                'center'
            ); ?>
            <div class="row g-4" data-nd-stagger>
                <?php foreach ($service['process'] as $step): ?>
                <div class="col-md-6 col-lg">
                    <div class="svc-process-step p-4 h-100 text-center">
                        <div class="svc-step-num mb-2"><?php echo $step['step']; ?></div>
                        <h3 class="h6 fw-bold text-dark mb-2"><?php echo htmlspecialchars($step['title']); ?></h3>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($step['desc']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- DELIVERABLES + TOOLS (stacked — no uneven column gaps) -->
    <section class="py-5 svc-section-scope">
        <div class="container py-2 py-md-3">
            <?php nd_render_section_head('What Is Included', 'Deliverables included in a typical engagement with Nectra Digital.', 'left'); ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 svc-deliverables-grid" data-nd-stagger>
                <?php foreach ($service['deliverables'] as $d): ?>
                <div class="col d-flex">
                    <div class="svc-deliverable d-flex align-items-center p-3 w-100">
                        <i class="fas fa-check-circle text-primary me-2 flex-shrink-0"></i>
                        <span class="text-dark small fw-medium"><?php echo htmlspecialchars($d); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 mt-lg-5">
                <?php include __DIR__ . '/partials/service-scope-engagement-strip.php'; ?>
            </div>

            <div class="mt-4 mt-lg-5">
                <?php include __DIR__ . '/partials/service-scope-tech-panel.php'; ?>
            </div>
        </div>
    </section>

    <!-- INDUSTRIES SERVED -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container py-3">
            <?php nd_render_section_head(
                'Industries We Accelerate',
                'Specialized workflows and regulatory knowledge across key sectors.',
                'center'
            ); ?>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php foreach ($service['industries'] as $ind): ?>
                <span class="svc-industry-pill"><i class="fas fa-building text-primary"></i> <?php echo htmlspecialchars($ind); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- COMPARISON MATRIX -->
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container py-3">
            <?php nd_render_section_head('Nectra Digital vs. Traditional Agencies', null, 'center'); ?>
            <div class="row g-4 justify-content-center" data-nd-stagger>
                <div class="col-md-5">
                    <div class="p-4 rounded-3 h-100 bg-white border border-primary border-opacity-50 shadow-sm">
                        <div class="d-flex align-items-center gap-2 mb-3 text-primary fw-bold">
                            <i class="fas fa-circle-check"></i>
                            <h3 class="h6 mb-0 fw-bold text-primary">Nectra Digital Standard</h3>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($service['comparison']['us'] as $item): ?>
                            <li class="text-dark small mb-2 d-flex align-items-start gap-2">
                                <i class="fas fa-check text-primary mt-1"></i>
                                <span><?php echo htmlspecialchars($item); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="p-4 rounded-3 h-100 bg-white border text-muted">
                        <div class="d-flex align-items-center gap-2 mb-3 text-secondary fw-bold">
                            <i class="fas fa-circle-xmark text-danger"></i>
                            <h3 class="h6 mb-0 fw-bold text-secondary">Typical Agencies</h3>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($service['comparison']['others'] as $item): ?>
                            <li class="text-muted small mb-2 d-flex align-items-start gap-2">
                                <i class="fas fa-times text-danger mt-1"></i>
                                <span><?php echo htmlspecialchars($item); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <?php render_faq_section($service['faqs'], $service['h1'] . ' — FAQ', 'light'); ?>

    <!-- LOCALIZED CITY CONTENT (If on city landing page) -->
    <?php if ($is_city_page && !empty($city_name)): ?>
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container py-3">
            <div class="row g-5">
                <div class="col-lg-8">
                    <h2 class="h3 fw-bold text-dark mb-4 nd-section-title"><?php echo htmlspecialchars($service['silo']); ?> for Businesses in <span class="text-primary"><?php echo htmlspecialchars($city_name); ?></span></h2>
                    <p class="text-muted"><?php echo htmlspecialchars(ge_service_city_unique_block($service_slug, $city_slug ?? '', $city ?? [], $service)); ?></p>
                    <p class="text-muted">Nectra Digital supports companies in <?php echo htmlspecialchars($city_name); ?>, <?php echo htmlspecialchars($city_state ?? ''); ?> with <?php echo htmlspecialchars(strtolower($service['silo'])); ?> strategy, execution, and reporting — plus web development and AI automation when your growth plan requires custom software.</p>
                    <h3 class="h6 fw-bold text-dark mt-4 mb-2">What we deliver locally</h3>
                    <ul class="text-muted small">
                        <li class="mb-2">Market-specific search research and campaign calibration for <?php echo htmlspecialchars($city_name); ?></li>
                        <li class="mb-2">Dedicated account management with transparent monthly reporting</li>
                        <li class="mb-2">Integrated engineering across SEO, paid media, and software platforms</li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <?php render_nap_block('service-city'); ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SERVICE CITY LINKS -->
    <?php
    try {
        render_service_city_links($service_slug, $service, $is_city_page ? ($city_slug ?? null) : null);
    } catch (\Throwable $e) {
        // Safe fallback
    }
    ?>

    <!-- RELATED SERVICES -->
    <?php render_internal_links_service($service['related'], 'light'); ?>

    <!-- CALL TO ACTION BANNER -->
    <section class="nd-section nd-section-alt py-4 py-md-5">
        <div class="container">
            <?php include __DIR__ . '/ui/cta.php'; ?>
        </div>
    </section>

    <!-- AUTHOR CREDENTIALS -->
    <section class="nd-section bg-white border-top py-4">
        <div class="container">
            <?php include __DIR__ . '/ui/author-box.php'; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/footer.php'; ?>
