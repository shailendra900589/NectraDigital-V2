<?php
/** Restored homepage sections (capabilities, services, outcomes, AI, portfolio cards). */
require_once dirname(__DIR__) . '/nav-data.php';
require_once dirname(__DIR__) . '/ui/section-head.php';
?>
<section class="nd-section nd-section-alt">
    <div class="container">
        <?php nd_render_section_head(
            'Engineered Solutions for Modern Businesses',
            'Software engineering first — then SEO, paid media, and AI automation on the products we build for you.',
            'center'
        ); ?>
        <div class="row g-4" data-nd-stagger>
            <div class="col-md-6 col-lg-3">
                <div class="nd-card p-4 h-100">
                    <div class="nd-card-icon mb-3"><i class="fas fa-code"></i></div>
                    <h3 class="h6 fw-bold">Software &amp; Web</h3>
                    <p class="text-muted small mb-3">High-performance web apps, custom software platforms, and headless e-commerce.</p>
                    <a href="<?php echo nectra_nav_path('/web-development-services'); ?>" class="small fw-semibold text-primary-blue text-decoration-none">Explore Engineering →</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="nd-card p-4 h-100 nd-card-ai">
                    <div class="nd-card-icon nd-card-icon-ai mb-3"><i class="fas fa-robot"></i></div>
                    <h3 class="h6 fw-bold">AI &amp; Automation</h3>
                    <p class="text-muted small mb-3">Custom AI agents, CRM bots, and WhatsApp APIs.</p>
                    <a href="<?php echo nectra_nav_path('/ai-automation-services'); ?>" class="small fw-semibold text-primary-blue text-decoration-none">Explore AI →</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="nd-card p-4 h-100">
                    <div class="nd-card-icon mb-3"><i class="fas fa-magnifying-glass-chart"></i></div>
                    <h3 class="h6 fw-bold">Search Engine SEO</h3>
                    <p class="text-muted small mb-3">Technical SEO, local search, link building, and AEO.</p>
                    <a href="<?php echo nectra_nav_path('/seo-services'); ?>" class="small fw-semibold text-primary-blue text-decoration-none">Explore SEO →</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="nd-card p-4 h-100">
                    <div class="nd-card-icon mb-3"><i class="fas fa-bullseye"></i></div>
                    <h3 class="h6 fw-bold">Paid Media &amp; PPC</h3>
                    <p class="text-muted small mb-3">Google Ads, Meta campaigns, and conversion optimization.</p>
                    <a href="<?php echo nectra_nav_path('/performance-marketing-services'); ?>" class="small fw-semibold text-primary-blue text-decoration-none">Explore Paid Media →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="nd-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 nd-reveal">
                <?php nd_render_section_head('Why Industry Leaders Partner with Nectra Digital', 'Developers and marketers in one team — engineering discipline with commercial acumen.', 'left'); ?>
            </div>
            <div class="col-lg-7">
                <div class="row g-3" data-nd-stagger>
                    <?php
                    $whys = [
                        ['Strategy First', 'Keyword forensics, competitive analysis, and unit-economic validation.'],
                        ['Scalable Architecture', 'Clean codebases and Core Web Vitals–friendly delivery.'],
                        ['Search-First Mindset', 'Sites built to index cleanly and satisfy search intent.'],
                        ['Automation Ready', 'Lead routing, CRM sync, and workflow automation.'],
                    ];
                    foreach ($whys as $w): ?>
                    <div class="col-sm-6">
                        <div class="nd-card p-4 h-100">
                            <h3 class="h6 fw-bold"><?php echo htmlspecialchars($w[0]); ?></h3>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($w[1]); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="nd-section nd-section-alt">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-4 gap-3 nd-reveal">
            <div class="flex-grow-1">
                <?php nd_render_section_head('Featured Specialized Services', null, 'left'); ?>
            </div>
            <a href="<?php echo nectra_nav_path('/services'); ?>" class="btn btn-nectra-secondary nd-btn-motion">View All 17 Services →</a>
        </div>
        <div class="row g-4" data-nd-stagger>
            <?php
            $home_services = get_services_data();
            $featured_slugs = [
                'software-development-services', 'web-development-services', 'mobile-app-development',
                'ai-automation-services', 'seo-services', 'ecommerce-development',
            ];
            foreach ($featured_slugs as $slug):
                if (!isset($home_services[$slug])) continue;
                $hs = $home_services[$slug];
            ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?php echo nectra_nav_path('/' . $slug); ?>" class="text-decoration-none">
                    <div class="nd-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="nd-card-icon mb-0"><i class="fas <?php echo htmlspecialchars($hs['icon']); ?>"></i></div>
                            <span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($hs['silo']); ?></span>
                        </div>
                        <h3 class="h6 fw-bold text-dark"><?php echo htmlspecialchars($hs['h1']); ?></h3>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars(mb_substr($hs['intro'], 0, 115)); ?>…</p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="nd-section">
    <div class="container pb-2">
        <?php nd_render_section_head('How We Work', 'Seven clear phases — from discovery to scale — without the jargon.', 'center'); ?>
        <?php include dirname(__DIR__) . '/ui/process.php'; ?>
    </div>
</section>

<section class="nd-section-dark nd-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 nd-reveal">
                <h2 class="h3 fw-bold text-white mb-3">Enterprise AI That Works 24/7 For Your Bottom Line</h2>
                <p class="text-white-50">Intelligent agents, WhatsApp bots, and workflow connectors for prospects and CRM sync.</p>
                <a href="<?php echo nectra_nav_path('/ai-automation-services'); ?>" class="btn btn-nectra-primary mt-2 nd-btn-motion"><i class="fas fa-robot me-2"></i>Explore Automation Architecture →</a>
            </div>
            <div class="col-lg-6 nd-reveal">
                <div class="nd-home-dashboard nd-home-dashboard--compact">
                    <div class="nd-home-pipeline p-3">
                        <p class="text-white small fw-bold mb-2">Autonomous Agent Pipeline</p>
                        <p class="text-white-50 small mb-0">Inbound → Intent → Calendar &amp; CRM — automated handoff.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="nd-section nd-section-alt">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h2 class="nd-section-title h3 fw-bold text-dark mb-0">Featured Case Studies &amp; Outcomes</h2>
            </div>
            <a href="<?php echo nectra_nav_path('/portfolio'); ?>" class="btn btn-nectra-secondary">View Portfolio →</a>
        </div>
        <div class="row g-4" data-nd-stagger>
            <div class="col-md-4">
                <div class="nd-card p-4 h-100">
                    <span class="badge bg-light border text-secondary mb-2">FINTECH</span>
                    <h3 class="h6 fw-bold">NOVA PAY Dashboard</h3>
                    <p class="small text-muted">Payment gateway dashboard with real-time settlements.</p>
                    <a href="<?php echo nectra_nav_path('/portfolio'); ?>" class="small text-primary-blue fw-semibold text-decoration-none">View case study →</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nd-card p-4 h-100">
                    <span class="badge bg-light border text-secondary mb-2">D2C E-COMMERCE</span>
                    <h3 class="h6 fw-bold">Oudh Organics D2C</h3>
                    <p class="small text-muted">Headless storefront and conversion-focused checkout.</p>
                    <a href="<?php echo nectra_nav_path('/portfolio'); ?>" class="small text-primary-blue fw-semibold text-decoration-none">View case study →</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nd-card p-4 h-100">
                    <span class="badge bg-light border text-secondary mb-2">AI AGENT</span>
                    <h3 class="h6 fw-bold">Auto-Rep Voice AI</h3>
                    <p class="small text-muted">Voice sales agent for lead qualification and booking.</p>
                    <a href="<?php echo nectra_nav_path('/portfolio'); ?>" class="small text-primary-blue fw-semibold text-decoration-none">View case study →</a>
                </div>
            </div>
        </div>
    </div>
</section>
