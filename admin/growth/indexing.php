<?php
/**
 * Indexing Manager — lightweight GET (no gateway timeout), batched POST.
 */
require_once __DIR__ . '/init.php';

use Growth\Models\LandingPage;
use Growth\Models\IndexingQueue;
use Growth\Engines\IndexingEngine;
use Growth\Engines\DiscoveryEngine;
use Growth\Engines\SeoRefreshEngine;

/** Heavy work belongs in cron — web UI runs one safe batch per click. */
function ge_indexing_invalidate_stats_cache(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        unset($_SESSION['ge_landing_index_stats'], $_SESSION['ge_landing_index_stats_at']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    @set_time_limit(120);
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_indexed' && !empty($_POST['page_id'])) {
        $db = ge_conn();
        $id = (int)$_POST['page_id'];
        $db->query("UPDATE ge_landing_pages SET index_status='indexed', is_indexed=1, index_verified_at=NOW() WHERE id=$id");
        ge_admin_flash('success', 'Marked as indexed.');
        ge_indexing_invalidate_stats_cache();
    }

    if ($action === 'queue_all_pending') {
        $r = IndexingEngine::queueAllPending(2000, false);
        ge_admin_flash('success', "Queued {$r['queued']} URLs. Use Process One Batch or set up cron to submit.");
        ge_indexing_invalidate_stats_cache();
    }

    if ($action === 'process_queue') {
        $r = IndexingEngine::processWebBatch();
        $n = (int)($r['processed'] ?? 0);
        ge_admin_flash('success', "Submitted {$n} URLs (one batch). Run cron or click again for more. Failed: " . (int)($r['failed'] ?? 0));
        ge_indexing_invalidate_stats_cache();
    }

    if ($action === 'ping_sitemap') {
        $r = IndexingEngine::pingSitemap();
        ge_admin_flash($r['ok'] ? 'success' : 'error', $r['ok'] ? 'Sitemap ping sent.' : 'Sitemap ping failed.');
    }

    if ($action === 'submit_all_indexnow') {
        $r = IndexingEngine::submitAllPublishedUrls(true, false);
        $n = (int)($r['urls_submitted'] ?? 0);
        $total = (int)($r['urls_total'] ?? 0);
        ge_admin_flash($r['ok'] ? 'success' : 'warning', $r['ok']
            ? "IndexNow: first {$n} URL batches submitted (of ~{$total} with languages). Run cron for full queue."
            : 'IndexNow batch failed — check IndexNow key in Settings.');
        ge_indexing_invalidate_stats_cache();
    }

    if ($action === 'queue_and_process') {
        $q = IndexingEngine::queueAllPending(2000, false);
        $r = IndexingEngine::processWebBatch();
        ge_admin_flash('success', "Queued {$q['queued']} URLs + submitted " . (int)($r['processed'] ?? 0) . " in one batch. Cron handles the rest.");
        ge_indexing_invalidate_stats_cache();
    }

    if ($action === 'publish_all') {
        $r = DiscoveryEngine::publishAll(500, 2);
        ge_admin_flash('success', "Discovery push started: {$r['processed']} URLs in cron-sized batches. Queued: {$r['queued']}.");
        ge_indexing_invalidate_stats_cache();
    }

    if ($action === 'refresh_seo') {
        @set_time_limit(300);
        $r = SeoRefreshEngine::refreshAll(true);
        ge_admin_flash('success', "Refreshed SEO on {$r['updated']} pages.");
        ge_indexing_invalidate_stats_cache();
    }

    if ($action === 'bing_submit_sitemap') {
        $urls = [rtrim(SITE_URL, '/') . '/sitemap.xml', rtrim(SITE_URL, '/') . '/news-sitemap.xml'];
        $r = IndexingEngine::submitBingWebmasterUrls($urls, false);
        ge_admin_flash(
            !empty($r['ok']) ? 'success' : 'error',
            !empty($r['ok']) ? 'Bing Webmaster API: sitemap URLs submitted.' : ('Bing API failed — ' . ($r['message'] ?? 'check API key'))
        );
    }

    if ($action === 'bing_test') {
        $siteUrl = rtrim(trim((string)ge_setting('bing_webmaster_site_url', SITE_URL)), '/');
        $r = IndexingEngine::submitBingWebmasterUrls([$siteUrl . '/'], false);
        ge_admin_flash(
            !empty($r['ok']) ? 'success' : 'error',
            !empty($r['ok']) ? 'Bing Webmaster API connected — homepage submitted.' : ('Bing test failed — ' . ($r['message'] ?? 'verify key + site URL'))
        );
    }

    if ($action === 'refresh_stats') {
        ge_indexing_invalidate_stats_cache();
        ge_admin_flash('success', 'Stats refreshed.');
    }

    if ($action === 'reset_stale') {
        $r = IndexingEngine::resetStaleSubmitted(30);
        ge_admin_flash('success', "Reset {$r['reset']} stale submitted pages and re-queued {$r['queued']} URLs.");
        ge_indexing_invalidate_stats_cache();
    }

    header('Location: indexing.php');
    exit;
}

// --- Fast GET load (must finish before nginx 504) ---
$dash = ['pages' => ['total' => 0, 'indexed' => 0, 'pending' => 0, 'submitted' => 0, 'failed' => 0, 'stale_submitted' => 0], 'queue' => ['pending' => 0, 'completed' => 0, 'failed' => 0, 'total' => 0], 'queue_progress_pct' => 0, 'queue_idle' => true, 'last_cron_at' => null, 'last_queue_processed_at' => null, 'last_cron_meta' => []];
$stats = $dash['pages'];
$queueStats = $dash['queue'];
$queuePending = 0;
$queueProgressPct = 0;
$queueIdle = true;
$pendingPages = [];
$activity = [];
$idxInfo = ['key' => '', 'key_url' => '', 'host' => parse_url(SITE_URL, PHP_URL_HOST) ?: ''];
$loadError = null;

try {
    if (ge_is_ready()) {
        $dash = IndexingEngine::dashboardStats(true);
        $stats = $dash['pages'];
        $queueStats = $dash['queue'];
        $queuePending = (int)($queueStats['pending'] ?? 0);
        $queueProgressPct = (int)($dash['queue_progress_pct'] ?? 0);
        $queueIdle = !empty($dash['queue_idle']);
        $pendingPages = LandingPage::pendingSample(15);
        $activity = IndexingQueue::recentActivity(25);
    }

    $key = IndexingEngine::readApiKey();
    if ($key === '') {
        $key = IndexingEngine::apiKey();
    }
    $idxInfo = [
        'key' => $key,
        'key_url' => IndexingEngine::keyFileUrl($key),
        'host' => IndexingEngine::host(),
    ];
} catch (Throwable $e) {
    $loadError = $e->getMessage();
    error_log('growth/indexing load: ' . $e->getMessage());
}

$bingKey = trim((string)ge_setting('bing_webmaster_api_key', ''));
$bingSite = rtrim(trim((string)ge_setting('bing_webmaster_site_url', SITE_URL)), '/');
$bingApiOn = ge_setting('index_engine_bing_api', '1') === '1';
$cronToken = trim((string)ge_setting('cron_token', ''));
if ($cronToken === '' && function_exists('ge_ensure_cron_token')) {
    $cronToken = ge_ensure_cron_token();
}
$cronBase = rtrim(SITE_URL, '/');
$cronUrls = [
    'indexing' => $cronBase . '/cron/process-indexing.php' . ($cronToken ? '?token=' . urlencode($cronToken) : ''),
    'i18n_index' => $cronBase . '/cron/i18n-indexnow.php' . ($cronToken ? '?token=' . urlencode($cronToken) : ''),
    'discovery' => $cronBase . '/cron/publish-discovery.php' . ($cronToken ? '?token=' . urlencode($cronToken) : ''),
];

ge_admin_layout();
ge_admin_layout_start('Indexing Manager', 'indexing');
?>

<?php if ($loadError): ?>
<div class="alert alert-warning">Partial load: <?php echo htmlspecialchars($loadError); ?></div>
<?php endif; ?>

<div id="indexingLiveRoot" data-api-url="indexing-api.php" data-poll-ms="8000">

<div id="idxToast" class="alert alert-success small d-none position-fixed top-0 end-0 m-3 shadow" style="z-index:2000;max-width:320px;"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <span id="idxLiveStatus" class="small text-muted"><i class="fas fa-circle text-success me-1" style="font-size:0.55rem;"></i> Live · loading…</span>
    <div class="small text-muted">
        <span id="idxLastCron"><?php echo $dash['last_cron_at'] ? 'Last cron: ' . htmlspecialchars($dash['last_cron_at']) : 'Last cron: never'; ?></span>
        · <span id="idxLastQueue"><?php echo $dash['last_queue_processed_at'] ? 'Last queue batch: ' . htmlspecialchars($dash['last_queue_processed_at']) : 'Last queue batch: none yet'; ?></span>
    </div>
</div>

<div id="idxQueueAlert" class="alert alert-warning small mb-4 <?php echo $queuePending > 0 ? '' : 'd-none'; ?>">
    <strong><span id="idx-queue-alert-count"><?php echo number_format($queuePending); ?></span> URLs waiting in queue.</strong>
    Cron is not processing them yet — add cron jobs below or click <strong>Process Batch (Live)</strong>.
</div>

<div class="alert alert-secondary small mb-4">
    <strong>Important:</strong> <em>Submitted</em> means IndexNow/Bing accepted the URL — not confirmed in Google/Bing search results.
    Use <em>Mark Indexed</em> after verifying in Search Console, or rely on sitemap + cron. Indexed count only updates when manually marked or verified.
</div>

<div class="row g-4 mb-2">
    <div class="col-6 col-md-4 col-xl-2"><div class="ge-stat-card"><div class="ge-stat-value" id="idx-stat-total"><?php echo number_format((int)($stats['total'] ?? 0)); ?></div><div class="ge-stat-label">Total Pages</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="ge-stat-card"><div class="ge-stat-value text-success" id="idx-stat-indexed"><?php echo number_format((int)($stats['indexed'] ?? 0)); ?></div><div class="ge-stat-label">Indexed ✓</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="ge-stat-card"><div class="ge-stat-value text-warning" id="idx-stat-pending"><?php echo number_format((int)($stats['pending'] ?? 0)); ?></div><div class="ge-stat-label">Page Pending</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="ge-stat-card"><div class="ge-stat-value" id="idx-stat-submitted"><?php echo number_format((int)($stats['submitted'] ?? 0)); ?></div><div class="ge-stat-label">Submitted</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="ge-stat-card"><div class="ge-stat-value text-danger" id="idx-stat-failed"><?php echo number_format((int)($stats['failed'] ?? 0)); ?></div><div class="ge-stat-label">Failed</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="ge-stat-card"><div class="ge-stat-value text-info" id="idx-stat-stale"><?php echo number_format((int)($stats['stale_submitted'] ?? 0)); ?></div><div class="ge-stat-label">Stale 30d+</div></div></div>
</div>

<div class="ge-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h2 class="h6 mb-0">Queue Progress</h2>
        <span class="small text-muted">Completed + failed vs total · <strong id="idx-queue-pct"><?php echo $queueIdle ? 'Idle' : ($queueProgressPct . '%'); ?></strong></span>
    </div>
    <div class="progress mb-3" style="height:12px;">
        <div id="idxQueueProgress" class="progress-bar bg-info<?php echo $queueIdle ? ' progress-bar-striped' : ''; ?>" role="progressbar" style="width:<?php echo $queueIdle ? '100' : $queueProgressPct; ?>%; opacity:<?php echo $queueIdle ? '0.25' : '1'; ?>;" aria-valuenow="<?php echo $queueProgressPct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="row g-3 text-center">
        <div class="col-4"><div class="small text-muted">Queue Pending</div><div class="fw-bold text-warning" id="idx-queue-pending"><?php echo number_format((int)($queueStats['pending'] ?? 0)); ?></div></div>
        <div class="col-4"><div class="small text-muted">Queue Done</div><div class="fw-bold text-success" id="idx-queue-completed"><?php echo number_format((int)($queueStats['completed'] ?? 0)); ?></div></div>
        <div class="col-4"><div class="small text-muted">Queue Failed</div><div class="fw-bold text-danger" id="idx-queue-failed"><?php echo number_format((int)($queueStats['failed'] ?? 0)); ?></div></div>
    </div>
</div>

<form method="POST" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="hidden" name="action" value="refresh_stats">
    <button type="submit" class="btn btn-sm btn-outline-secondary">Refresh now</button>
    <button type="button" id="idxProcessAjax" class="btn btn-sm btn-success"><i class="fas fa-bolt"></i> Process Batch (Live)</button>
</form>
<?php if ((int)($stats['stale_submitted'] ?? 0) > 0): ?>
<form method="POST" class="mb-4">
    <input type="hidden" name="action" value="reset_stale">
    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Re-queue <?php echo (int)$stats['stale_submitted']; ?> stale submitted pages (30+ days)?');"><i class="fas fa-redo"></i> Re-queue Stale Submitted Pages</button>
</form>
<?php endif; ?>

<div class="alert alert-info small mb-4">
    <strong>Hostinger tip:</strong> Set cron every 5–15 min to drain the queue automatically. Manual buttons process one safe batch to avoid 504 timeout.
</div>

<div class="alert alert-info small">
    <strong>Engines:</strong> IndexNow · Bing IndexNow · Bing Webmaster API · Yandex · DuckDuckGo
    <br>IndexNow key: <a href="<?php echo htmlspecialchars($idxInfo['key_url']); ?>" target="_blank"><?php echo htmlspecialchars($idxInfo['key_url']); ?></a>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ge-card h-100">
            <h2 class="h6 mb-3"><i class="fab fa-microsoft me-1"></i> Bing Webmaster API</h2>
            <?php if ($bingKey !== '' && $bingApiOn): ?>
                <p class="small text-success mb-2"><i class="fas fa-check-circle"></i> API key saved · Site: <code><?php echo htmlspecialchars($bingSite); ?></code></p>
                <form method="POST" class="d-inline"><input type="hidden" name="action" value="bing_test"><button type="submit" class="btn btn-sm btn-outline-success me-2">Test Bing API</button></form>
                <form method="POST" class="d-inline"><input type="hidden" name="action" value="bing_submit_sitemap"><button type="submit" class="btn btn-sm btn-outline-info">Submit Sitemaps to Bing</button></form>
            <?php elseif ($bingKey !== '' && !$bingApiOn): ?>
                <p class="small text-warning mb-0">Key saved but disabled — <a href="settings.php">enable Bing URL API</a>.</p>
            <?php else: ?>
                <p class="small text-muted mb-2">Add Bing API key in <a href="settings.php">Growth Settings</a>.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ge-card h-100">
            <h2 class="h6 mb-3"><i class="fas fa-clock me-1"></i> Cron (full auto indexing)</h2>
            <p class="small text-muted">Add in Hostinger → Cron Jobs (every 5–15 min):</p>
            <code class="small d-block mb-1 text-break"><?php echo htmlspecialchars($cronUrls['indexing']); ?></code>
            <code class="small d-block mb-1 text-break"><?php echo htmlspecialchars($cronUrls['i18n_index']); ?></code>
            <code class="small d-block text-break"><?php echo htmlspecialchars($cronUrls['discovery']); ?></code>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="ge-card">
            <h2 class="h6 mb-3">Indexing Actions</h2>
            <form method="POST" class="d-grid gap-2"><input type="hidden" name="action" value="queue_all_pending"><button type="submit" class="btn btn-ge-primary">Queue Pending URLs</button></form>
            <form method="POST" class="d-grid gap-2 mt-2"><input type="hidden" name="action" value="process_queue"><button type="submit" class="btn btn-success">Process One Batch → IndexNow + Bing</button></form>
            <form method="POST" class="d-grid gap-2 mt-2"><input type="hidden" name="action" value="ping_sitemap"><button type="submit" class="btn btn-outline-secondary">Ping Sitemap</button></form>
            <form method="POST" class="d-grid gap-2 mt-2"><input type="hidden" name="action" value="submit_all_indexnow"><button type="submit" class="btn btn-warning">Submit First Batches → IndexNow</button></form>
            <form method="POST" class="d-grid gap-2 mt-2"><input type="hidden" name="action" value="queue_and_process"><button type="submit" class="btn btn-outline-success">Queue + One Batch</button></form>
            <p class="small text-muted mt-3 mb-0"><a href="../export-urls.php?type=all">Download URLs for Google Search Console</a></p>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="ge-card mb-4">
            <h2 class="h6 mb-3">Pending Pages (sample)</h2>
            <div class="table-responsive"><table class="table ge-table table-sm"><thead><tr><th>Slug</th><th></th></tr></thead><tbody>
            <?php foreach ($pendingPages as $p): ?>
            <tr><td><code><?php echo htmlspecialchars($p['slug'] ?? ''); ?></code></td><td>
                <form method="POST" class="d-inline"><input type="hidden" name="action" value="mark_indexed"><input type="hidden" name="page_id" value="<?php echo (int)($p['id'] ?? 0); ?>"><button type="submit" class="btn btn-sm btn-outline-success">Mark Indexed</button></form>
            </td></tr>
            <?php endforeach; ?>
            <?php if (empty($pendingPages)): ?>
            <tr><td colspan="2" class="text-muted text-center small">No pending sample (or stats cached).</td></tr>
            <?php endif; ?>
            </tbody></table></div>
        </div>
        <div class="ge-card">
            <h2 class="h6 mb-3">Queue Activity <span class="text-muted fw-normal small">(auto-updates every 8s)</span></h2>
            <div class="table-responsive"><table class="table ge-table table-sm"><thead><tr><th>URL</th><th>Status</th><th>Queued</th><th>Processed</th></tr></thead><tbody id="idxActivityBody">
            <?php foreach ($activity as $q):
                $pathLabel = trim((string)($q['url'] ?? ''));
                if ($pathLabel !== '') {
                    $parsed = parse_url($pathLabel, PHP_URL_PATH);
                    if (is_string($parsed) && $parsed !== '') {
                        $pathLabel = $parsed;
                    }
                } else {
                    $pathLabel = '—';
                }
                $st = (string)($q['status'] ?? '');
                $badgeClass = $st === 'completed' ? 'ge-badge-indexed' : ($st === 'failed' ? 'ge-badge-failed' : 'ge-badge-pending');
            ?>
            <tr><td class="small"><code><?php echo htmlspecialchars($pathLabel); ?></code></td><td><span class="ge-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($st); ?></span></td><td class="small text-muted"><?php echo htmlspecialchars((string)($q['created_at'] ?? '')); ?></td><td class="small text-muted"><?php echo htmlspecialchars((string)($q['processed_at'] ?? '—')); ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($activity)): ?>
            <tr><td colspan="4" class="text-muted text-center small">No queue activity yet.</td></tr>
            <?php endif; ?>
            </tbody></table></div>
        </div>
    </div>
</div>

</div>
<script src="../../assets/js/indexing-admin.js?v=1"></script>

<?php ge_admin_layout_end(); ?>
