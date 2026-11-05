<?php
namespace Growth\Engines;

use Growth\Models\IndexingQueue;
use Growth\Models\LandingPage;

/**
 * Multi-engine URL indexing: IndexNow (Bing/Yandex/DDG), sitemap pings (Google/Bing).
 */
class IndexingEngine
{
    private const INDEXNOW_ENDPOINTS = [
        'indexnow' => 'https://api.indexnow.org/indexnow',
        'bing' => 'https://www.bing.com/indexnow',
        'yandex' => 'https://yandex.com/indexnow',
    ];

    public static function apiKey(): string
    {
        $key = self::readApiKey();
        if ($key === '') {
            $key = self::generateAndStoreKey();
        }
        self::ensureKeyFile($key);
        return $key;
    }

    /** Read IndexNow key from settings only (no file I/O) — safe for admin page display. */
    public static function readApiKey(): string
    {
        $key = trim((string)ge_setting('indexnow_api_key', ''));
        if ($key === '') {
            $key = trim((string)ge_setting('indexnow_key', ''));
        }
        return $key;
    }

    public static function host(): string
    {
        $host = parse_url(SITE_URL, PHP_URL_HOST);
        return $host ?: 'www.nectradigital.com';
    }

    public static function keyFileUrl(?string $key = null): string
    {
        $key = $key ?? self::apiKey();
        return rtrim(SITE_URL, '/') . '/' . $key . '.txt';
    }

    private static function generateAndStoreKey(): string
    {
        $key = bin2hex(random_bytes(16));
        if (ge_table_exists('ge_settings')) {
            $db = ge_conn();
            $stmt = $db->prepare("INSERT INTO ge_settings (setting_key, setting_value) VALUES ('indexnow_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            if ($stmt) {
                $stmt->bind_param('s', $key);
                $stmt->execute();
            }
        }
        return $key;
    }

    public static function siteRoot(): string
    {
        if (php_sapi_name() !== 'cli' && !empty($_SERVER['DOCUMENT_ROOT'])) {
            $root = realpath($_SERVER['DOCUMENT_ROOT']);
            if ($root !== false) {
                return $root;
            }
        }
        return dirname(__DIR__, 3);
    }

    public static function ensureKeyFile(?string $key = null): bool
    {
        if ($key === null || trim((string)$key) === '') {
            return false;
        }

        $key = trim((string)$key);
        if (!preg_match('/^[a-zA-Z0-9_-]{8,128}$/', $key)) {
            return false;
        }

        try {
            $root = self::siteRoot();
            if ($root === '' || !is_dir($root) || !is_writable($root)) {
                return false;
            }

            $path = $root . DIRECTORY_SEPARATOR . $key . '.txt';
            if (is_file($path)) {
                return true;
            }

            return @file_put_contents($path, $key) !== false;
        } catch (\Throwable $e) {
            error_log('IndexNow ensureKeyFile: ' . $e->getMessage());
            return false;
        }
    }

    public static function isEngineEnabled(string $engine): bool
    {
        return ge_setting('index_engine_' . $engine, '1') === '1';
    }

    /** Submit one or many URLs via IndexNow to all enabled endpoints. */
    public static function submitIndexNow(array $urls, bool $expandI18n = false): array
    {
        if ($expandI18n) {
            require_once __DIR__ . '/../../i18n.php';
            $urls = nectra_expand_urls_for_languages($urls);
        }
        $urls = array_values(array_unique(array_filter($urls)));
        if (empty($urls)) {
            return ['ok' => false, 'message' => 'No URLs provided', 'engines' => []];
        }

        $key = self::apiKey();
        $host = self::host();
        $results = [];
        $ok = false;
        $submitted = 0;
        $batches = 0;

        foreach (array_chunk($urls, 10000) as $chunk) {
            $payload = json_encode([
                'host' => $host,
                'key' => $key,
                'keyLocation' => self::keyFileUrl(),
                'urlList' => $chunk,
            ]);

            $batchResults = [];
            foreach (self::INDEXNOW_ENDPOINTS as $name => $endpoint) {
                if (!self::isEngineEnabled($name)) {
                    $batchResults[$name] = ['skipped' => true];
                    continue;
                }
                $batchResults[$name] = self::httpPost($endpoint, $payload, ['Content-Type: application/json; charset=utf-8']);
            }

            if (self::isEngineEnabled('duckduckgo')) {
                $batchResults['duckduckgo'] = $batchResults['bing'] ?? ['note' => 'Uses IndexNow via Bing network'];
            }

            $batchOk = false;
            foreach ($batchResults as $r) {
                if (!empty($r['ok'])) {
                    $batchOk = true;
                }
            }

            if ($batchOk) {
                $submitted += count($chunk);
                $ok = true;
            }

            $results = $batchResults;
            $batches++;
        }

        return [
            'ok' => $ok,
            'urls' => count($urls),
            'urls_submitted' => $submitted,
            'batches' => $batches,
            'engines' => $results,
        ];
    }

    /** Submit URLs via Bing Webmaster Tools URL Submission API (requires API key). */
    public static function submitBingWebmasterUrls(array $urls, bool $expandI18n = false): array
    {
        if (ge_setting('index_engine_bing_api', '1') !== '1') {
            return ['ok' => false, 'skipped' => true, 'message' => 'Bing API disabled'];
        }

        $apiKey = trim((string)ge_setting('bing_webmaster_api_key', ''));
        if ($apiKey === '') {
            return ['ok' => false, 'skipped' => true, 'message' => 'No Bing Webmaster API key'];
        }

        if ($expandI18n) {
            require_once __DIR__ . '/../../i18n.php';
            $urls = nectra_expand_urls_for_languages($urls);
        }
        $urls = array_values(array_unique(array_filter($urls)));
        if (empty($urls)) {
            return ['ok' => false, 'message' => 'No URLs provided'];
        }

        $siteUrl = rtrim(trim((string)ge_setting('bing_webmaster_site_url', SITE_URL)), '/');
        $endpoint = 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey=' . urlencode($apiKey);
        $ok = false;
        $submitted = 0;
        $last = [];

        foreach (array_chunk($urls, 500) as $chunk) {
            $payload = json_encode([
                'siteUrl' => $siteUrl,
                'urlList' => $chunk,
            ]);
            $last = self::httpPost($endpoint, $payload, ['Content-Type: application/json; charset=utf-8']);
            if (!empty($last['ok'])) {
                $ok = true;
                $submitted += count($chunk);
            }
        }

        return [
            'ok' => $ok,
            'urls' => count($urls),
            'urls_submitted' => $submitted,
            'message' => $ok ? 'Bing API accepted URLs' : ('Bing API HTTP ' . ($last['code'] ?? 0) . ': ' . substr((string)($last['body'] ?? ''), 0, 200)),
            'engines' => ['bing_webmaster_api' => $last],
        ];
    }

    /** Ping sitemap to Google and Bing webmaster endpoints. */
    public static function pingSitemap(): array
    {
        return self::pingAllSitemaps();
    }

    /** Ping XML sitemaps (Yandex). Google/Bing ping deprecated — use IndexNow for URLs. */
    public static function pingAllSitemaps(): array
    {
        $sitemaps = [
            SITE_URL . '/sitemap.xml',
            SITE_URL . '/news-sitemap.xml',
        ];
        $results = [];
        $indexNow = self::submitIndexNow($sitemaps);

        foreach ($sitemaps as $sitemap) {
            $encoded = urlencode($sitemap);
            if (self::isEngineEnabled('yandex')) {
                $results['yandex_' . basename($sitemap)] = self::httpGet("https://webmaster.yandex.com/ping?sitemap={$encoded}");
            }
        }

        $ok = !empty($indexNow['ok']);
        foreach ($results as $r) {
            if (!empty($r['ok'])) {
                $ok = true;
            }
        }
        return [
            'ok' => $ok,
            'sitemaps' => $sitemaps,
            'indexnow' => $indexNow,
            'engines' => $results,
            'note' => 'Google/Bing sitemap ping deprecated; URLs submitted via IndexNow + submit sitemap in Search Console.',
        ];
    }

    /** Legacy single sitemap ping — delegates to pingAllSitemaps. */
    public static function pingSitemapLegacy(): array
    {
        $sitemap = SITE_URL . '/sitemap.xml';
        $encoded = urlencode($sitemap);
        $results = [];

        if (self::isEngineEnabled('google_sitemap')) {
            $results['google_sitemap'] = self::httpGet("https://www.google.com/ping?sitemap={$encoded}");
        }
        if (self::isEngineEnabled('bing_sitemap')) {
            $results['bing_sitemap'] = self::httpGet("https://www.bing.com/ping?sitemap={$encoded}");
        }

        $ok = false;
        foreach ($results as $r) {
            if (!empty($r['ok'])) {
                $ok = true;
            }
        }
        return ['ok' => $ok, 'sitemap' => $sitemap, 'engines' => $results];
    }

    /** Drain the indexing queue in repeated batches until empty or max batches reached. */
    public static function processAllQueue(int $batchSize = 100, int $maxBatches = 100): array
    {
        $totalProcessed = 0;
        $totalFailed = 0;
        $batches = 0;

        while ($batches < $maxBatches) {
            $r = self::processQueue($batchSize, false);
            $totalProcessed += (int)($r['processed'] ?? 0);
            $totalFailed += (int)($r['failed'] ?? 0);
            $batches++;

            if (($r['processed'] ?? 0) === 0 && ($r['failed'] ?? 0) === 0) {
                break;
            }
        }

        return [
            'processed' => $totalProcessed,
            'failed' => $totalFailed,
            'batches' => $batches,
        ];
    }

    /** Unified stats for admin dashboard + live API polling. */
    public static function dashboardStats(bool $noCache = false): array
    {
        $pages = LandingPage::indexStatsFast(!$noCache);
        $queue = IndexingQueue::stats();
        $lastQueueAt = IndexingQueue::lastProcessedAt();
        $lastRun = trim((string)ge_setting('indexing_last_run_at', ''));
        $lastRunMeta = json_decode((string)ge_setting('indexing_last_run_meta', ''), true);
        $lastSummary = self::lastSubmissionSummary();

        $queueDone = (int)($queue['completed'] ?? 0) + (int)($queue['failed'] ?? 0);
        $queueTotal = (int)($queue['total'] ?? 0);
        $queuePct = $queueTotal > 0 ? min(100, (int)round(($queueDone / $queueTotal) * 100)) : 0;

        return [
            'pages' => $pages,
            'queue' => $queue,
            'queue_progress_pct' => $queuePct,
            'queue_idle' => $queueTotal === 0,
            'last_queue_processed_at' => $lastQueueAt,
            'last_cron_at' => $lastRun !== '' ? $lastRun : null,
            'last_cron_meta' => is_array($lastRunMeta) ? $lastRunMeta : [],
            'last_submission' => $lastSummary,
            'updated_at' => date('c'),
        ];
    }

    /** Re-queue landing pages stuck in submitted state for 30+ days. */
    public static function resetStaleSubmitted(int $days = 30): array
    {
        if (!ge_table_exists('ge_landing_pages')) {
            return ['reset' => 0, 'queued' => 0];
        }
        $days = max(7, min(90, $days));
        $db = ge_conn();
        $res = $db->query(
            "SELECT id, slug FROM ge_landing_pages
             WHERE status='published' AND index_status='submitted'
             AND index_submitted_at IS NOT NULL
             AND index_submitted_at < DATE_SUB(NOW(), INTERVAL {$days} DAY)
             LIMIT 2000"
        );
        $reset = 0;
        $queued = 0;
        require_once __DIR__ . '/../../i18n.php';
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $pid = (int)$row['id'];
                $db->query("UPDATE ge_landing_pages SET index_status='pending', is_indexed=0 WHERE id={$pid}");
                $reset++;
                foreach (nectra_language_url_variants(SITE_URL . '/' . $row['slug']) as $variant) {
                    if (IndexingQueue::enqueueUnique($variant, $pid)) {
                        $queued++;
                    }
                }
            }
        }
        self::logRun('reset_stale', ['reset' => $reset, 'queued' => $queued, 'days' => $days]);
        return ['reset' => $reset, 'queued' => $queued];
    }

    public static function logRun(string $action, array $meta = []): void
    {
        if (!ge_table_exists('ge_settings')) {
            return;
        }
        $payload = json_encode(array_merge(['action' => $action], $meta), JSON_UNESCAPED_SLASHES);
        $now = date('Y-m-d H:i:s');
        $db = ge_conn();
        $stmt = $db->prepare("INSERT INTO ge_settings (setting_key, setting_value) VALUES ('indexing_last_run_at', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if ($stmt) {
            $stmt->bind_param('s', $now);
            $stmt->execute();
        }
        $stmt2 = $db->prepare("INSERT INTO ge_settings (setting_key, setting_value) VALUES ('indexing_last_run_meta', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if ($stmt2) {
            $stmt2->bind_param('s', $payload);
            $stmt2->execute();
        }
    }

    /** Process one admin-safe batch (web UI — avoids gateway timeout). */
    public static function processWebBatch(?int $batchSize = null): array
    {
        $batchSize = $batchSize ?? (int)ge_setting('index_batch_size', 50);
        $batchSize = max(10, min(100, $batchSize));
        return self::processQueue($batchSize, false);
    }

    /**
     * Submit ALL published landing pages (+ optional city hubs) via IndexNow in batches.
     * Use from dashboard "Submit All" — not limited to pending status.
     */
    public static function submitAllPublishedUrls(bool $includeCityHubs = true, bool $includeCoreUrls = false): array
    {
        if (!ge_table_exists('ge_landing_pages')) {
            return ['ok' => false, 'message' => 'Landing pages table missing', 'urls_total' => 0];
        }

        $base = rtrim(SITE_URL, '/');
        $urls = [];

        $res = ge_conn()->query("SELECT id, slug FROM ge_landing_pages WHERE status='published' ORDER BY id ASC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $urls[] = $base . '/' . $row['slug'];
            }
        }

        if ($includeCityHubs) {
            require_once __DIR__ . '/../../seo-data.php';
            foreach (array_keys(get_cities_data()) as $citySlug) {
                $urls[] = $base . '/digital-agency-' . $citySlug;
            }
        }

        if ($includeCoreUrls) {
            require_once __DIR__ . '/DiscoveryEngine.php';
            $urls = array_merge($urls, DiscoveryEngine::coreUrls());
        }

        $urls = array_values(array_unique(array_filter($urls)));
        if (empty($urls)) {
            return ['ok' => false, 'message' => 'No URLs to submit', 'urls_total' => 0];
        }

        $submitted = 0;
        $batchCount = 0;
        $lastBatch = ['ok' => false];

        foreach (array_chunk($urls, 500) as $chunk) {
            $lastBatch = self::submitIndexNow($chunk, false);
            $batchCount++;
            if (!empty($lastBatch['ok'])) {
                $submitted += (int)($lastBatch['urls_submitted'] ?? count($chunk));
            }
            if ($batchCount >= 3) {
                break;
            }
        }

        if ($submitted > 0) {
            ge_conn()->query("UPDATE ge_landing_pages SET index_status='submitted', index_submitted_at=NOW() WHERE status='published' AND index_status IN ('pending','failed') LIMIT 5000");
        }

        $sitemap = ($batchCount <= 2) ? self::pingAllSitemaps() : ['ok' => true, 'skipped' => true, 'note' => 'Sitemap ping skipped after large batch — use Ping Sitemap button'];

        return [
            'ok' => $submitted > 0,
            'urls_total' => count($urls),
            'urls_submitted' => $submitted,
            'batches' => $batchCount,
            'indexnow' => $lastBatch,
            'sitemap' => $sitemap,
            'i18n' => false,
        ];
    }

    /** Process pending ge_indexing_queue items with real HTTP submissions. */
    public static function processQueue(int $limit = 50, bool $pingSitemap = false): array
    {
        if (!ge_table_exists('ge_indexing_queue')) {
            return ['processed' => 0, 'failed' => 0, 'message' => 'Indexing queue table missing'];
        }

        $limit = max(1, min(100, $limit));
        $pending = IndexingQueue::pending($limit);
        if (empty($pending)) {
            return ['processed' => 0, 'failed' => 0, 'message' => 'Queue empty'];
        }

        $urls = array_column($pending, 'url');
        $batch = self::submitIndexNow($urls, false);
        $bingApi = self::submitBingWebmasterUrls($urls, false);
        $sitemap = $pingSitemap ? self::pingSitemap() : ['skipped' => true];

        $processed = 0;
        $failed = 0;
        $responseLog = json_encode(['indexnow' => $batch, 'bing_api' => $bingApi, 'sitemap' => $sitemap], JSON_UNESCAPED_SLASHES);

        foreach ($pending as $item) {
            $id = (int)$item['id'];
            $pageId = (int)($item['landing_page_id'] ?? 0);
            $success = !empty($batch['ok']) || !empty($bingApi['ok']);
            if ($success) {
                IndexingQueue::markProcessed($id, 'completed', $responseLog);
                if ($pageId > 0) {
                    ge_conn()->query("UPDATE ge_landing_pages SET index_status='submitted', index_submitted_at=NOW() WHERE id={$pageId}");
                }
                $processed++;
            } else {
                IndexingQueue::markProcessed($id, 'failed', $responseLog);
                if ($pageId > 0) {
                    ge_conn()->query("UPDATE ge_landing_pages SET index_status='failed' WHERE id={$pageId}");
                }
                $failed++;
            }
        }

        self::logRun('process_queue', [
            'processed' => $processed,
            'failed' => $failed,
            'pending_remaining' => IndexingQueue::stats()['pending'] ?? 0,
        ]);

        return [
            'processed' => $processed,
            'failed' => $failed,
            'indexnow' => $batch,
            'sitemap' => $sitemap,
        ];
    }

    /** Queue landing pages for indexing and optionally process the full queue. */
    public static function queueAllPending(int $limit = 5000, bool $processNow = false, bool $onlyPending = true): array
    {
        if (!ge_table_exists('ge_landing_pages')) {
            return ['queued' => 0, 'process' => ['processed' => 0, 'failed' => 0]];
        }

        $db = ge_conn();
        $where = $onlyPending
            ? "status='published' AND index_status IN ('pending','failed')"
            : "status='published'";
        $res = $db->query("SELECT id, slug FROM ge_landing_pages WHERE {$where} ORDER BY id ASC LIMIT " . (int)$limit);
        $queued = 0;
        require_once __DIR__ . '/../../i18n.php';
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                foreach (nectra_language_url_variants(SITE_URL . '/' . $row['slug']) as $variant) {
                    if (IndexingQueue::enqueueUnique($variant, (int)$row['id'])) {
                        $queued++;
                    }
                }
            }
        }

        $batchSize = (int)ge_setting('index_batch_size', 100);
        $processResult = $processNow
            ? self::processAllQueue(min($batchSize, 50), 2)
            : ['processed' => 0, 'failed' => 0];

        return ['queued' => $queued, 'process' => $processResult];
    }

    /** Queue every published page + city hubs, then submit entire queue via IndexNow. */
    public static function queueAndSubmitAll(int $pageLimit = 10000): array
    {
        $queue = self::queueAllPending($pageLimit, false, false);
        $batchSize = (int)ge_setting('index_batch_size', 100);

        $base = rtrim(SITE_URL, '/');
        require_once __DIR__ . '/../../seo-data.php';
        require_once __DIR__ . '/../../i18n.php';
        $hubsQueued = 0;
        foreach (array_keys(get_cities_data()) as $citySlug) {
            foreach (nectra_language_url_variants($base . '/digital-agency-' . $citySlug) as $variant) {
                if (IndexingQueue::enqueueUnique($variant, 0)) {
                    $hubsQueued++;
                }
            }
        }

        $process = self::processAllQueue(min($batchSize, 50), 2);
        $direct = self::submitAllPublishedUrls(true, false);

        return [
            'queued' => $queue['queued'] + $hubsQueued,
            'process' => $process,
            'direct' => $direct,
        ];
    }

    public static function lastSubmissionSummary(): array
    {
        if (!ge_table_exists('ge_indexing_queue')) {
            return [];
        }
        $row = ge_conn()->query("SELECT response, processed_at FROM ge_indexing_queue WHERE response IS NOT NULL ORDER BY id DESC LIMIT 1");
        if (!$row || !$row->num_rows) {
            return [];
        }
        $data = $row->fetch_assoc();
        $decoded = json_decode($data['response'] ?? '', true);
        return is_array($decoded) ? $decoded : ['raw' => $data['response'], 'at' => $data['processed_at']];
    }

    private static function httpPost(string $url, string $body, array $headers = []): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $response = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ['ok' => in_array($code, [200, 202], true), 'code' => $code, 'body' => substr((string)$response, 0, 500)];
        }

        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'timeout' => 20,
            'ignore_errors' => true,
        ]]);
        $response = @file_get_contents($url, false, $ctx);
        $code = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $code = (int)$m[1];
        }
        return ['ok' => in_array($code, [200, 202], true), 'code' => $code, 'body' => substr((string)$response, 0, 500)];
    }

    private static function httpGet(string $url): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_FOLLOWLOCATION => true]);
            $response = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ['ok' => $code >= 200 && $code < 400, 'code' => $code, 'body' => substr((string)$response, 0, 300)];
        }
        $response = @file_get_contents($url);
        return ['ok' => $response !== false, 'code' => 200, 'body' => substr((string)$response, 0, 300)];
    }
}
