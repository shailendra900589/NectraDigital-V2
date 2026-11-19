<?php 
require_once 'includes/seo-data.php';
require_once 'includes/seo-components.php';
require_once 'includes/i18n.php';
require_once 'includes/eeat-copy.php';

$page_title = "Software Development FAQ | AEO Knowledge Base | Nectra Digital";
$page_desc = "Direct answers on custom software development, SaaS cost in India, web & mobile engineering, AI automation, and SEO — optimized for Google AI Overviews and answer engines.";
$page_keys = "custom software development, software development cost India, SaaS development, web development company, What is SEO, AI automation";
$canonical_url = nectra_page_canonical('/aeo');

$aeo_answers = get_aeo_answers();
$all_faqs = [];
foreach ($aeo_answers as $a) {
    $all_faqs[] = ['q' => $a['question'], 'a' => $a['quick_answer'] . ' ' . $a['detailed']];
}

$page_schema = [
    get_breadcrumb_schema([
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'AEO Answers', 'url' => SITE_URL . '/aeo'],
    ]),
];

include 'includes/header.php';
output_faq_schema($all_faqs);
?>

<main style="background: #F8FAFC; min-height: 100vh;">
    <!-- HERO -->
    <header class="nd-page-hero nd-section-dark text-center position-relative overflow-hidden">
        <div class="nd-page-hero-glow" aria-hidden="true"></div>
        <div class="container position-relative z-1 py-2 py-md-3">
            <?php render_breadcrumbs([['name' => 'Home', 'url' => '/'], ['name' => 'AEO Answers', 'url' => '/aeo']]); ?>
            <h1 class="fw-bold text-white mb-3 nd-hero-title">Knowledge Base &amp; <span class="nd-text-gradient">Direct Answers</span></h1>
            <p class="lead text-white-50 mx-auto" style="max-width: 680px; line-height: 1.6;">
                Direct, expert-verified answers calibrated for Google AI Overviews, ChatGPT, Gemini, Perplexity, and conversational search engines.
            </p>
        </div>
    </header>

    <!-- TRUST STRIP -->
    <?php include __DIR__ . '/includes/ui/trust-bar.php'; ?>

    <!-- AEO DIRECT ANSWERS LIST -->
    <section class="py-5">
        <div class="container py-3">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <?php foreach ($aeo_answers as $key => $aeo): ?>
                    <article id="<?php echo htmlspecialchars($key); ?>" class="aeo-card nd-reveal mb-5 p-4 p-md-5 rounded-4 bg-white border shadow-sm">
                        <h2 class="h4 fw-bold text-dark mb-4 nd-section-title"><?php echo htmlspecialchars($aeo['question']); ?></h2>
                        
                        <!-- Quick Answer Box -->
                        <div class="p-4 rounded-3 mb-4" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid var(--nd-primary) !important;">
                            <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fas fa-bolt"></i> Quick Answer
                            </div>
                            <p class="text-dark mb-0 fw-medium" style="font-size: 1.05rem; line-height: 1.6;">
                                <?php echo htmlspecialchars($aeo['quick_answer']); ?>
                            </p>
                        </div>

                        <!-- Detailed Explanation -->
                        <div class="mb-4">
                            <h3 class="h6 fw-bold text-dark mb-2">Comprehensive Context</h3>
                            <p class="text-muted" style="line-height: 1.7;"><?php echo htmlspecialchars($aeo['detailed']); ?></p>
                        </div>

                        <!-- Expert Insight -->
                        <div class="p-3 rounded-3 bg-light border mb-4">
                            <h3 class="h6 fw-bold text-dark mb-1"><i class="fas fa-lightbulb text-primary me-2"></i>Expert Perspective</h3>
                            <p class="text-muted small fst-italic mb-0">"<?php echo FOUNDER_NAME; ?>, <?php echo FOUNDER_TITLE; ?>: Great products start with clear requirements, maintainable architecture, and shippable milestones — SEO and paid media amplify software that already solves a real job-to-be-done."</p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="/contact?service=<?php echo urlencode($aeo['question']); ?>" class="btn btn-sm btn-primary fw-semibold px-3 py-2">Get Strategic Consultation</a>
                            <?php
                            $aeoServiceUrl = (strpos($key, 'seo') !== false || strpos($key, 'digital-marketing') !== false || strpos($key, 'lead') !== false)
                                ? '/seo-services'
                                : '/software-development-services';
                            $aeoServiceLabel = $aeoServiceUrl === '/seo-services' ? 'Explore SEO Services' : 'Explore Software Development';
                            ?>
                            <a href="<?php echo htmlspecialchars($aeoServiceUrl); ?>" class="btn btn-sm btn-outline-secondary px-3 py-2"><?php echo htmlspecialchars($aeoServiceLabel); ?></a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CTA & AUTHOR -->
    <?php include __DIR__ . '/includes/ui/cta.php'; ?>
    <?php include __DIR__ . '/includes/ui/author-box.php'; ?>
</main>

<?php include 'includes/footer.php'; ?>
