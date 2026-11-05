<?php

function admin_render_dashboard_home(mysqli $conn, array $growthStats): void
{
    $leadCount = 0;
    $hireCount = 0;
    $r = $conn->query('SELECT COUNT(*) AS c FROM leads');
    if ($r) {
        $leadCount = (int)($r->fetch_assoc()['c'] ?? 0);
    }
    $r = $conn->query("SELECT COUNT(*) AS c FROM hire_requests WHERE status='new'");
    if ($r) {
        $hireCount = (int)($r->fetch_assoc()['c'] ?? 0);
    }

    $indexed = (int)($growthStats['indexed'] ?? 0);
    $pending = (int)($growthStats['pending_index'] ?? 0);
    $pages = (int)($growthStats['pages'] ?? 0);
    $cities = (int)($growthStats['cities'] ?? 0);
    $potential = (int)($growthStats['potential'] ?? 0);
    $pct = $potential > 0 ? min(100, (int)round(($pages / $potential) * 100)) : 0;
    $ready = !empty($growthStats['ready']);

    echo '<div class="ge-page-intro mb-4">';
    echo '<p class="ge-page-intro__eyebrow mb-1">Control center</p>';
    echo '<h2 class="ge-page-intro__title h4 mb-2">Today at a glance</h2>';
    echo '<p class="ge-page-intro__sub mb-0">Leads, content, and SEO tools — use the sidebar for full modules.</p>';
    echo '</div>';

    echo '<div class="row g-3 mb-4">';
    admin_render_stat_tile('Contact leads', $leadCount, 'fa-inbox', 'primary', 'dashboard.php?page=leads');
    admin_render_stat_tile('New hire requests', $hireCount, 'fa-user-tie', 'warning', 'dashboard.php?page=hire_requests');
    admin_render_stat_tile('Landing pages', $pages, 'fa-file-alt', 'info', 'growth/landing-pages.php');
    admin_render_stat_tile('Indexed URLs', $indexed, 'fa-check-circle', 'success', 'growth/indexing.php');
    echo '</div>';

    echo '<div class="ge-card mb-4"><div class="ge-section-head mb-3">Quick actions</div><div class="ge-quick-grid ge-quick-grid--compact">';
    $actions = [
        ['create_post.php', 'fa-pen-to-square', 'New blog post'],
        ['dashboard.php?page=leads', 'fa-inbox', 'View leads'],
        ['growth/indexing.php', 'fa-search-plus', 'Indexing'],
        ['growth/generate.php', 'fa-wand-magic-sparkles', 'Generate pages'],
        ['?page=blog', 'fa-newspaper', 'All posts'],
    ];
    foreach ($actions as [$href, $icon, $label]) {
        $url = str_starts_with($href, '?') ? 'dashboard.php' . $href : $href;
        echo '<a href="' . htmlspecialchars($url) . '" class="ge-quick-link"><i class="fas ' . htmlspecialchars($icon) . '"></i><span>' . htmlspecialchars($label) . '</span></a>';
    }
    echo '</div></div>';

    echo '<div class="row g-4">';
    echo '<div class="col-lg-6"><div class="ge-card ge-card--flat h-100">';
    echo '<h3 class="ge-section-head"><i class="fas fa-chart-line text-primary me-2"></i>Indexing</h3>';
    echo '<ul class="ge-metric-list mb-3">';
    echo '<li><span>Indexed</span><strong class="text-success">' . number_format($indexed) . '</strong></li>';
    echo '<li><span>Pending</span><strong class="text-warning">' . number_format($pending) . '</strong></li>';
    echo '<li><span>Queue</span><strong>' . number_format((int)($growthStats['queue_pending'] ?? 0)) . '</strong></li>';
    echo '</ul>';
    echo '<a href="growth/indexing.php" class="btn btn-ge-primary btn-sm"><i class="fas fa-arrow-right me-1"></i>Open indexing panel</a>';
    echo '</div></div>';

    echo '<div class="col-lg-6"><div class="ge-card ge-card--flat h-100">';
    echo '<h3 class="ge-section-head"><i class="fas fa-globe text-primary me-2"></i>Programmatic SEO</h3>';
    if ($ready) {
        echo '<p class="text-muted small mb-2">' . number_format($pages) . ' pages live · ' . number_format($cities) . ' cities · ' . $pct . '% of estimated capacity</p>';
        echo '<div class="progress mb-3" style="height:8px;"><div class="progress-bar" role="progressbar" style="width:' . $pct . '%;" aria-valuenow="' . $pct . '" aria-valuemin="0" aria-valuemax="100"></div></div>';
        echo '<div class="d-flex flex-wrap gap-2">';
        echo '<a href="growth/generate.php" class="btn btn-sm btn-ge-primary">Generate</a>';
        echo '<a href="?page=cities" class="btn btn-sm btn-outline-secondary">Cities</a>';
        echo '<a href="export-urls.php?type=all" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i>Export URLs</a>';
        echo '</div>';
    } else {
        echo '<p class="text-muted small mb-3">Growth database is not ready. Run migration once to unlock cities and landing pages.</p>';
        echo '<a href="run-migration.php" class="btn btn-sm btn-outline-secondary">Run migration</a>';
    }
    echo '</div></div></div>';
}

function admin_render_stat_tile(string $label, int $value, string $icon, string $tone, string $href): void
{
    echo '<div class="col-6 col-lg-3">';
    echo '<a href="' . htmlspecialchars($href) . '" class="ge-stat-card ge-stat-card--link ge-stat-card--' . htmlspecialchars($tone) . ' text-decoration-none">';
    echo '<div class="ge-stat-card__icon"><i class="fas ' . htmlspecialchars($icon) . '"></i></div>';
    echo '<div class="ge-stat-card__body">';
    echo '<div class="ge-stat-value">' . number_format($value) . '</div>';
    echo '<div class="ge-stat-label">' . htmlspecialchars($label) . '</div>';
    echo '</div></a></div>';
}
