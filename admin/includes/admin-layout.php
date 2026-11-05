<?php
require_once __DIR__ . '/admin-nav.php';

function admin_page_title(string $title): string
{
    return htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' — Nectra Admin';
}

function admin_site_url(): string
{
    if (defined('SITE_URL')) {
        return rtrim(SITE_URL, '/');
    }
    return 'https://www.nectradigital.com';
}

function admin_platform_version(): string
{
    if (function_exists('ge_setting')) {
        return (string)ge_setting('platform_version', '2.0');
    }
    return '2.0';
}

function admin_get_flash(): ?array
{
    if (function_exists('ge_admin_get_flash')) {
        $flash = ge_admin_get_flash();
        if ($flash) {
            return $flash;
        }
    }
    if (!empty($_SESSION['ge_flash']) && is_array($_SESSION['ge_flash'])) {
        $flash = $_SESSION['ge_flash'];
        unset($_SESSION['ge_flash']);
        return $flash;
    }
    return null;
}

function admin_layout_start(string $title, string $activePage = '', array $options = []): void
{
    $ctx = admin_layout_context();
    $flash = admin_get_flash();
    $includeCkeditor = !empty($options['ckeditor']);
    $alerts = $options['alerts'] ?? [];
    $showGrowthWarning = !empty($options['growth_warning']);
    $growthReady = function_exists('ge_is_ready') ? ge_is_ready() : null;
    ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo admin_page_title($title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($ctx['assets']); ?>css/growth-admin.css?v=10">
    <?php if ($includeCkeditor): ?>
        <?php require_once dirname(__DIR__, 2) . '/includes/ckeditor.php'; nectra_ckeditor_styles(); ?>
    <?php endif; ?>
</head>
<body class="ge-admin">
<div class="ge-layout">
    <div class="ge-sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
    <aside class="ge-sidebar" id="adminSidebar">
        <div class="ge-brand ge-sidebar-brand">
            <span class="ge-brand-text">NECTRA</span><span class="ge-brand-accent">ADMIN</span>
            <small class="d-block ge-sidebar-version mt-1">Nectra Digital</small>
        </div>
        <nav class="ge-nav ge-sidebar-scroll" aria-label="Admin navigation">
            <?php foreach (admin_nav_items() as $item): ?>
                <?php if (!empty($item['section'])): ?>
            <div class="ge-nav-section"><?php echo htmlspecialchars($item['section']); ?></div>
                <?php else: ?>
            <a href="<?php echo htmlspecialchars(admin_nav_resolve_url($item['url'])); ?>"
               class="ge-nav-link <?php echo ($activePage === ($item['id'] ?? '')) ? 'active' : ''; ?>">
                <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                <?php echo htmlspecialchars($item['label']); ?>
            </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="ge-sidebar-footer">
            <a href="<?php echo htmlspecialchars(admin_site_url()); ?>" target="_blank" rel="noopener" class="ge-nav-link">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
            <a href="<?php echo htmlspecialchars($ctx['root']); ?>logout.php" class="ge-nav-link text-danger">
                <i class="fas fa-power-off"></i> Logout
            </a>
        </div>
    </aside>
    <main class="ge-main">
        <header class="ge-topbar">
            <div class="ge-topbar-left">
                <button type="button" class="btn btn-sm ge-mobile-toggle d-md-none" id="sidebarToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="ge-page-title"><?php echo htmlspecialchars($title); ?></h1>
            </div>
            <div class="ge-topbar-actions">
                <?php if ($activePage === 'home'): ?>
                <a href="<?php echo htmlspecialchars($ctx['root']); ?>create_post.php" class="btn btn-sm btn-ge-primary d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="fas fa-plus"></i> New post
                </a>
                <?php elseif ($activePage === 'blog'): ?>
                <a href="<?php echo htmlspecialchars($ctx['root']); ?>create_post.php" class="btn btn-sm btn-ge-primary d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="fas fa-plus"></i> New post
                </a>
                <?php endif; ?>
                <button type="button" class="btn btn-sm ge-theme-toggle" id="themeToggle" title="Toggle theme" aria-label="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="<?php echo htmlspecialchars(admin_site_url()); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1">
                    <i class="fas fa-external-link-alt"></i> Website
                </a>
            </div>
        </header>
        <div class="ge-content">
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo ($flash['type'] ?? '') === 'error' ? 'danger' : (($flash['type'] ?? '') === 'warning' ? 'warning' : 'success'); ?> alert-dismissible fade show">
                <?php echo htmlspecialchars((string)($flash['message'] ?? '')); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php foreach ($alerts as $alert): ?>
            <div class="alert alert-<?php echo htmlspecialchars($alert['type'] ?? 'info'); ?> <?php echo !empty($alert['dismiss']) ? 'alert-dismissible fade show' : ''; ?>">
                <?php echo $alert['html'] ?? htmlspecialchars((string)($alert['message'] ?? '')); ?>
                <?php if (!empty($alert['dismiss'])): ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if ($showGrowthWarning && $growthReady === false && $activePage !== 'home'): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <strong>Growth database not ready.</strong>
                Run <a href="<?php echo htmlspecialchars($ctx['root']); ?>run-migration.php" class="alert-link">Database migration</a> once to enable cities and landing pages.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
    <?php
}

function admin_pagination_range(int $current, int $totalPages, int $adjacent = 2): array
{
    if ($totalPages <= 1) {
        return [];
    }
    if ($totalPages <= 7) {
        return range(1, $totalPages);
    }
    $pages = [1];
    $left = max(2, $current - $adjacent);
    $right = min($totalPages - 1, $current + $adjacent);
    if ($left > 2) {
        $pages[] = 0;
    }
    for ($i = $left; $i <= $right; $i++) {
        $pages[] = $i;
    }
    if ($right < $totalPages - 1) {
        $pages[] = 0;
    }
    $pages[] = $totalPages;
    return $pages;
}

function admin_pagination(array $pg, array $queryParams = []): void
{
    $current = (int)($pg['page'] ?? 1);
    $totalPages = (int)($pg['pages'] ?? 1);
    $total = (int)($pg['total'] ?? 0);
    if ($totalPages <= 1) {
        return;
    }
    unset($queryParams['page']);
    $buildUrl = static function (int $p) use ($queryParams): string {
        return '?' . http_build_query(array_merge($queryParams, ['page' => $p]));
    };
    $from = ($current - 1) * (int)($pg['per_page'] ?? 50) + 1;
    $to = min($total, $current * (int)($pg['per_page'] ?? 50));
    ?>
    <div class="ge-pagination-wrap">
        <div class="ge-pagination-meta text-muted small">
            Showing <?php echo number_format($from); ?>–<?php echo number_format($to); ?> of <?php echo number_format($total); ?>
            · Page <?php echo $current; ?> / <?php echo $totalPages; ?>
        </div>
        <nav aria-label="Pagination">
            <ul class="pagination pagination-sm ge-pagination mb-0">
                <li class="page-item <?php echo $current <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $current <= 1 ? '#' : htmlspecialchars($buildUrl($current - 1)); ?>" aria-label="Previous">&laquo;</a>
                </li>
                <?php foreach (admin_pagination_range($current, $totalPages) as $p): ?>
                    <?php if ($p === 0): ?>
                <li class="page-item disabled"><span class="page-link ge-page-ellipsis">…</span></li>
                    <?php else: ?>
                <li class="page-item <?php echo $p === $current ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo htmlspecialchars($buildUrl($p)); ?>"><?php echo $p; ?></a>
                </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="page-item <?php echo $current >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $current >= $totalPages ? '#' : htmlspecialchars($buildUrl($current + 1)); ?>" aria-label="Next">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php
}

function admin_layout_end(array $options = []): void
{
    $ctx = admin_layout_context();
    $includeCkeditor = !empty($options['ckeditor']);
    ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars($ctx['assets']); ?>js/growth-admin.js?v=3"></script>
<?php if ($includeCkeditor): ?>
    <?php nectra_ckeditor_scripts($ctx['assets']); ?>
<?php endif; ?>
</body>
</html>
    <?php
}
