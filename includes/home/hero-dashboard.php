<?php
/** Homepage hero — original copy + upgraded product dashboard (responsive). */
?>
<section class="nd-hero-home nd-section-dark nd-home-hero">
    <div class="nd-hero-mesh" aria-hidden="true"></div>
    <div class="nd-hero-glow"></div>
    <div class="container position-relative z-1 py-3 py-lg-4">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6 text-start">
                <h1 class="fw-bold text-white mb-3 nd-home-h1 nd-hero-title">
                    Custom Software Development for <span class="nd-text-gradient">SaaS, Web &amp; Mobile</span>
                </h1>
                <p class="lead text-white-50 mb-4 nd-home-lead">
                    We design and ship production-ready software — APIs, dashboards, marketplaces, and mobile apps — with SEO-ready architecture and AI automation when your product needs to scale.
                </p>
                <div class="d-flex flex-wrap gap-2 gap-md-3 mb-4">
                    <a href="<?php echo nectra_nav_path('/contact?service=Start+Project'); ?>" class="btn btn-nectra-primary btn-lg px-4 nd-btn-motion"><i class="fas fa-rocket me-2"></i>Start Your Project</a>
                    <a href="<?php echo nectra_nav_path('/services'); ?>" class="btn btn-nectra-outline-light btn-lg px-4 nd-btn-motion"><i class="fas fa-layer-group me-2"></i>Explore Services</a>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 pt-3 border-top border-secondary border-opacity-25 text-white-50 small">
                    <span><i class="fas fa-check-circle text-success me-1"></i> 200+ Projects</span>
                    <span><i class="fas fa-check-circle text-success me-1"></i> Full-Stack Engineering</span>
                    <span><i class="fas fa-check-circle text-success me-1"></i> No Long-Term Lock-in</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="nd-home-dashboard mx-auto">
                    <div class="nd-home-dashboard-head">
                        <div class="nd-hero-visual-bar"><span></span><span></span><span></span></div>
                        <span class="text-white-50 small">nectra.growth.matrix — Live Console</span>
                        <span class="badge bg-success bg-opacity-25 text-success nd-home-badge-sm">Active</span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="nd-home-kpi">
                                <div class="text-white-50 nd-home-kpi-label">Products Shipped</div>
                                <div class="text-white fw-bold h6 mb-0">200+</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="nd-home-kpi">
                                <div class="text-white-50 nd-home-kpi-label">Stack</div>
                                <div class="text-white fw-bold h6 mb-0">React / Laravel</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="nd-home-kpi">
                                <div class="text-white-50 nd-home-kpi-label">Automations</div>
                                <div class="text-white fw-bold h6 mb-0">24/7</div>
                            </div>
                        </div>
                    </div>
                    <div class="nd-home-pipeline p-3 rounded-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white small fw-semibold"><i class="fas fa-diagram-project text-primary me-2"></i>AI Automation Pipeline</span>
                            <span class="text-white-50 small">Latency: 0.2s</span>
                        </div>
                        <div class="progress mb-2 nd-home-progress">
                            <div class="progress-bar bg-primary" style="width: 88%;" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between text-white-50 small">
                            <span>Lead → Qualify → CRM</span>
                            <span class="text-success"><i class="fas fa-check"></i> Synced</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
