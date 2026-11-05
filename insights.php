<?php 
require_once 'includes/db.php';
require_once 'includes/seo-data.php';
require_once 'includes/seo-components.php';
require_once 'includes/i18n.php';
require_once 'includes/blog_orphan.php';
require_once 'includes/text-utils.php';
require_once 'includes/nav-data.php';

$dbUnavailable = !nectra_db_is_connected();
if (!$dbUnavailable) {
    blog_orphan_ensure_schema($conn);
}
$page_title = "SEO & Digital Marketing Blog | Nectra Digital Intel";
$page_desc = "Expert SEO guides, Google algorithm updates, AI automation tips, and digital marketing strategies from India's leading SEO agency.";
$canonical_url = nectra_page_canonical('/insights');

$page_schema = [
    get_breadcrumb_schema([
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Insights', 'url' => SITE_URL . '/insights']
    ])
];

include 'includes/header.php'; 

// Pagination Engine
$posts_per_page = 8;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$offset = ($current_page - 1) * $posts_per_page;
$listWhere = blog_listable_sql();

$total_rows = 0;
$result = null;
if (!$dbUnavailable) {
    $total_result = $conn->query("SELECT COUNT(*) as total FROM blog_posts WHERE {$listWhere}");
    if ($total_result) {
        $total_rows = (int)($total_result->fetch_assoc()['total'] ?? 0);
    }
}
$total_pages = max(1, (int)ceil($total_rows / $posts_per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $posts_per_page;
}

if (!$dbUnavailable) {
    $sql = "SELECT * FROM blog_posts WHERE {$listWhere} ORDER BY created_at DESC LIMIT $posts_per_page OFFSET $offset";
    $result = $conn->query($sql);
}
?>

<main style="background: #F8FAFC; min-height: 100vh;">
    <!-- HERO (Dark Navy) -->
    <header class="nd-page-hero nd-section-dark text-center position-relative overflow-hidden">
        <div class="nd-page-hero-glow" aria-hidden="true"></div>
        <div class="container position-relative z-1 py-2 py-md-3">
            <?php render_breadcrumbs([['name' => 'Home', 'url' => '/'], ['name' => 'Insights', 'url' => '/insights']]); ?>
            <h1 class="fw-bold text-white mb-3 nd-hero-title">Nectra <span class="nd-text-gradient">Intel</span></h1>
            <p class="lead text-white-50 mx-auto nd-prose-width" style="line-height: 1.6;">
                Actionable SEO guides, algorithm updates, AI automation playbooks, and growth strategies for Indian and global brands.
            </p>
        </div>
    </header>

    <!-- TRUST STRIP -->
    <?php include __DIR__ . '/includes/ui/trust-bar.php'; ?>

    <!-- ARTICLES GRID (no section motion — keeps links clickable) -->
    <section class="py-5 nd-no-section-animate nd-insights-grid" data-nd-insights-grid>
        <div class="container py-3">
            <?php if (!empty($dbUnavailable) && function_exists('nectra_is_local_environment') && nectra_is_local_environment()): ?>
            <div class="alert alert-warning border-0 shadow-sm mb-4" role="status">
                <strong>Local database offline.</strong> Start MySQL in XAMPP Control Panel. If you still see connection errors, run
                <code>database/repair-xampp-mysql.bat</code> once (see instructions inside the file).
            </div>
            <?php endif; ?>
            <div class="row g-4" data-nd-stagger>
                <?php 
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $excerpt = nectra_fix_mojibake(strip_tags($row['content'] ?? ''));
                        $excerpt = mb_substr($excerpt, 0, 140) . '...';
                        $postDate = !empty($row['created_at'])
                            ? date('M j, Y', strtotime($row['created_at']))
                            : '';
                        
                        $post_title = nectra_display_text($row['title']);
                        $post_category = nectra_display_text($row['category']);
                        $slug = ltrim((string)($row['slug'] ?? ''), '/');
                        $post_href = nectra_blog_post_href($slug);
                        $byline = nectra_blog_byline();
                ?>
                <div class="col-md-6">
                    <article class="blog-card nd-card nd-insights-card p-4 h-100 d-flex flex-column bg-white shadow-sm position-relative">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-light text-primary border fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <?php echo $post_category; ?>
                            </span>
                            <span class="text-muted small" style="font-size: 0.75rem;">
                                <i class="far fa-calendar me-1"></i> <?php echo htmlspecialchars($postDate); ?>
                            </span>
                        </div>

                        <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                        <div class="mb-3 rounded-3 overflow-hidden border" style="height: 200px;">
                            <img src="<?php echo htmlspecialchars($row['image']); ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo strip_tags($post_title); ?>">
                        </div>
                        <?php endif; ?>

                        <h2 class="h5 fw-bold text-dark mb-2">
                            <?php echo $post_title; ?>
                        </h2>

                        <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.6;">
                            <?php echo htmlspecialchars($excerpt); ?>
                        </p>

                        <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-auto">
                            <span class="text-muted small" style="font-size: 0.8rem;">
                                <i class="far fa-user me-1 text-primary"></i> <?php echo htmlspecialchars($byline); ?>
                            </span>
                            <span class="text-primary small fw-semibold">
                                Read Article <i class="fas fa-arrow-right ms-1"></i>
                            </span>
                        </div>
                        <a href="<?php echo htmlspecialchars($post_href, ENT_QUOTES, 'UTF-8'); ?>"
                           class="stretched-link nd-insights-post-link"
                           aria-label="<?php echo htmlspecialchars('Read article: ' . strip_tags($post_title), ENT_QUOTES, 'UTF-8'); ?>"></a>
                    </article>
                </div>
                <?php
                    }
                } else {
                    echo "<div class='col-12 text-center py-5'><p class='text-muted'>No articles currently published in this section.</p></div>";
                }
                ?>
            </div>

            <!-- SERVER-SIDE PAGINATION -->
            <?php if ($total_pages > 1): ?>
            <div class="row mt-5 pt-3">
                <div class="col-12 d-flex justify-content-center">
                    <nav aria-label="Insights Pagination">
                        <ul class="pagination mb-0 gap-2">
                            <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 px-3 py-2 text-dark border fw-medium" href="?page=<?php echo ($current_page - 1); ?>">
                                    <i class="fas fa-chevron-left me-1"></i> Previous
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link rounded-3 px-3 py-2 border text-muted">Previous</span>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link rounded-3 px-3 py-2 fw-semibold <?php echo $i == $current_page ? 'bg-primary text-white border-primary' : 'bg-white text-dark border'; ?>" href="?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 px-3 py-2 text-dark border fw-medium" href="?page=<?php echo ($current_page + 1); ?>">
                                    Next <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link rounded-3 px-3 py-2 border text-muted">Next</span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FINAL CTA -->
    <?php include __DIR__ . '/includes/ui/cta.php'; ?>
</main>

<script src="<?php echo SITE_URL; ?>/assets/js/insights-cards.js?v=2" defer></script>
<?php include 'includes/footer.php'; ?>