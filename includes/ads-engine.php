<?php
/**
 * Site ad units — custom image banners + Google AdSense snippets.
 */
if (defined('NECTRA_ADS_ENGINE_LOADED')) {
    return;
}
define('NECTRA_ADS_ENGINE_LOADED', true);

if (!defined('NECTRA_ADSENSE_CLIENT')) {
    define('NECTRA_ADSENSE_CLIENT', 'ca-pub-7886338089253374');
}

function ads_ensure_schema(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS ads (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL DEFAULT '',
        type VARCHAR(32) NOT NULL DEFAULT 'image',
        placement VARCHAR(32) NOT NULL DEFAULT 'sidebar',
        image_path VARCHAR(512) NOT NULL DEFAULT '',
        ad_code MEDIUMTEXT NULL,
        link VARCHAR(512) NOT NULL DEFAULT '',
        status VARCHAR(32) NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_placement_status (placement, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function ads_table_exists(mysqli $conn): bool
{
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }
    $check = $conn->query("SHOW TABLES LIKE 'ads'");
    $exists = ($check && $check->num_rows > 0);
    return $exists;
}

function ad_resolve_image_url(?string $path): string
{
    $path = trim((string)$path);
    if ($path === '') {
        return '';
    }
    if (strpos($path, 'http') === 0) {
        return $path;
    }
    $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    return $base . '/' . ltrim($path, '/');
}

function ad_image_file_available(?string $path): bool
{
    $path = trim((string)$path);
    if ($path === '') {
        return false;
    }
    if (preg_match('#^https?://#i', $path)) {
        return true;
    }
    $root = dirname(__DIR__);
    $local = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/'));
    return is_file($local);
}

/** True when pasted code includes a real ad unit, not loader-only snippets. */
function ad_code_has_ad_unit(string $code): bool
{
    $code = trim($code);
    if ($code === '') {
        return false;
    }

    if (stripos($code, '<ins') !== false && stripos($code, 'adsbygoogle') !== false) {
        return true;
    }
    if (stripos($code, '<iframe') !== false) {
        return true;
    }

    // Loader script alone (common bad paste) is not an impression unit.
    if (stripos($code, 'adsbygoogle.js') !== false && stripos($code, '<ins') === false) {
        return false;
    }

    return (bool) preg_match('/<(ins|iframe|embed|object|img)\b/i', $code);
}

function ad_normalize_ad_code(string $code): string
{
    $code = trim($code);
    if ($code === '') {
        return '';
    }
    // Site loads adsbygoogle.js once in head/footer — strip duplicate loaders from snippets.
    $code = preg_replace('/<script\b[^>]*\bsrc=["\'][^"\']*adsbygoogle\.js[^"\']*["\'][^>]*>\s*<\/script>/is', '', $code) ?? $code;
    $code = preg_replace('/<script\b[^>]*\bsrc=["\'][^"\']*adsbygoogle\.js[^"\']*["\'][^>]*\/>/is', '', $code) ?? $code;
    return trim($code);
}

function ad_has_displayable_content(array $ad): bool
{
    $type = strtolower(trim((string)($ad['type'] ?? '')));
    $imagePath = trim((string)($ad['image_path'] ?? ''));
    $adCode = ad_normalize_ad_code((string)($ad['ad_code'] ?? ''));

    if ($type === 'code') {
        return ad_code_has_ad_unit($adCode);
    }

    if ($type === 'image' || ($type === '' && $imagePath !== '')) {
        return ad_image_file_available($imagePath);
    }

    if ($adCode !== '') {
        return ad_code_has_ad_unit($adCode);
    }

    return false;
}

function ad_track_adsense_units(string $html): void
{
    if (stripos($html, 'adsbygoogle') === false) {
        return;
    }
    $count = substr_count(strtolower($html), '<ins');
    if ($count < 1) {
        $count = 1;
    }
    $GLOBALS['nectra_adsense_units'] = (int)($GLOBALS['nectra_adsense_units'] ?? 0) + $count;
    $GLOBALS['nectra_adsense_needs_loader'] = true;
}

function ad_fingerprint(array $ad): string
{
    $type = strtolower(trim((string)($ad['type'] ?? '')));
    $imagePath = trim((string)($ad['image_path'] ?? ''));
    if ($type === 'image' || ($type === '' && $imagePath !== '')) {
        return 'img:' . $imagePath;
    }
    return 'code:' . md5(ad_normalize_ad_code((string)($ad['ad_code'] ?? '')));
}

/** Drop duplicate DB rows that share the same image or AdSense snippet. */
function ad_dedupe_ads(array $ads): array
{
    $seen = [];
    $out = [];
    foreach ($ads as $ad) {
        $fp = ad_fingerprint($ad);
        if (isset($seen[$fp])) {
            continue;
        }
        $seen[$fp] = true;
        $out[] = $ad;
    }
    return $out;
}

function ad_slot_open(array $ad, string $variant): string
{
    $id = (int)($ad['id'] ?? 0);
    $type = strtolower(trim((string)($ad['type'] ?? 'image')));
    $data = ' data-nd-ad-id="' . $id . '" data-nd-ad-type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"';

    if ($variant === 'sidebar') {
        return '<div class="sidebar-ad-card border rounded overflow-hidden bg-white shadow-sm nd-ad-slot nd-ad-pending"' . $data . '>'
            . '<span class="badge bg-secondary sidebar-ad-badge nd-ad-badge">Sponsored</span>';
    }
    if ($variant === 'inline' || $variant === 'banner-light') {
        return '<div class="nd-inline-ad-card border rounded bg-white shadow-sm my-4 nd-ad-slot nd-ad-pending"' . $data . '>'
            . '<span class="badge bg-secondary nd-ad-badge nd-inline-ad-badge">Sponsored</span>';
    }

    return '<div class="ad-container my-5 position-relative p-3 border border-secondary rounded nd-ad-slot nd-ad-pending"' . $data . '>';
}

function ad_render_unit(array $ad, string $variant = 'banner'): string
{
    if (!ad_has_displayable_content($ad)) {
        return '';
    }

    $isSidebar = ($variant === 'sidebar');
    $isInline = ($variant === 'inline' || $variant === 'banner-light');
    $html = '';
    $type = strtolower(trim((string)($ad['type'] ?? '')));
    $isImageAd = ($type === 'image' || ($type === '' && trim($ad['image_path'] ?? '') !== ''));

    if ($isImageAd) {
        $adImg = ad_resolve_image_url($ad['image_path'] ?? '');
        $link = htmlspecialchars(trim($ad['link'] ?? '') ?: '#', ENT_QUOTES, 'UTF-8');
        $title = function_exists('nectra_display_text')
            ? nectra_display_text($ad['title'] ?? '')
            : htmlspecialchars((string)($ad['title'] ?? ''), ENT_QUOTES, 'UTF-8');

        if ($adImg !== '') {
            if ($isSidebar) {
                $html .= '<a href="' . $link . '" target="_blank" rel="noopener sponsored" class="sidebar-ad-link d-block text-decoration-none">';
                $html .= '<div class="sidebar-ad-img-wrap"><img src="' . htmlspecialchars($adImg, ENT_QUOTES, 'UTF-8') . '" alt="' . $title . '" class="sidebar-ad-img nd-ad-img" loading="lazy" decoding="async"></div>';
                if ($title !== '') {
                    $html .= '<div class="sidebar-ad-caption px-3 py-2 border-top"><span class="small fw-semibold d-block">' . $title . '</span></div>';
                }
                $html .= '</a>';
            } else {
                $html .= '<a href="' . $link . '" target="_blank" rel="noopener sponsored" class="d-block">';
                $html .= '<img src="' . htmlspecialchars($adImg, ENT_QUOTES, 'UTF-8') . '" class="img-fluid rounded nectra-ad-img nd-ad-img" alt="' . $title . '" loading="lazy" decoding="async">';
                $html .= '</a>';
            }
        }
    } else {
        $code = ad_normalize_ad_code((string)($ad['ad_code'] ?? ''));
        if ($code !== '' && ad_code_has_ad_unit($code)) {
            ad_track_adsense_units($code);
            if ($isSidebar || $isInline) {
                $html .= '<div class="sidebar-ad-code p-3">' . $code . '</div>';
            } else {
                $html .= '<div class="mt-3" style="margin-top:15px; color:#fff;">' . $code . '</div>';
            }
        }
    }

    if ($html === '') {
        return '';
    }

    if ($isSidebar || $isInline) {
        return ad_slot_open($ad, $variant) . $html . '</div>';
    }

    return ad_slot_open($ad, 'banner')
        . '<span class="position-absolute top-0 start-0 badge bg-secondary nd-ad-badge" style="font-size:10px; opacity:0.8;">SPONSORED</span>'
        . $html
        . '</div>';
}

function ad_get_html(mysqli $conn, string $placement, string $variant = 'banner'): string
{
    if (!ads_table_exists($conn)) {
        return '';
    }

    $placement = $conn->real_escape_string($placement);
    $sql = "SELECT * FROM ads
            WHERE placement='$placement'
            AND (status='active' OR status IS NULL OR status='')
            ORDER BY RAND()
            LIMIT 1";
    $res = $conn->query($sql);
    if (!$res || $res->num_rows === 0) {
        return '';
    }

    return ad_render_unit($res->fetch_assoc(), $variant);
}

function ad_get_active(mysqli $conn, array $placements, int $limit, array $excludeIds = []): array
{
    if ($limit <= 0 || !ads_table_exists($conn)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($placements), '?'));
    $types = str_repeat('s', count($placements));
    $params = $placements;

    $excludeSql = '';
    if (!empty($excludeIds)) {
        $excludeIds = array_map('intval', $excludeIds);
        $excludeSql = ' AND id NOT IN (' . implode(',', $excludeIds) . ')';
    }

    $sql = "SELECT * FROM ads
            WHERE (status = 'active' OR status IS NULL OR status = '')
            AND placement IN ($placeholders)
            $excludeSql
            ORDER BY id DESC
            LIMIT " . (int)$limit;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $ads = [];
    while ($row = $res->fetch_assoc()) {
        $ads[] = $row;
    }
    return $ads;
}

/**
 * Sidebar column: every unique active ad that can render (sidebar first, then header/content).
 * One database row = one sponsored card — never duplicated.
 */
function ad_get_sidebar_displayable(mysqli $conn): array
{
    if (!ads_table_exists($conn)) {
        return [];
    }

    $ads = [];
    $seen = [];
    $placementOrder = ['sidebar', 'header', 'content'];

    foreach ($placementOrder as $placement) {
        foreach (ad_get_active($conn, [$placement], 500) as $ad) {
            $id = (int)($ad['id'] ?? 0);
            if ($id > 0 && isset($seen[$id])) {
                continue;
            }
            if (!ad_has_displayable_content($ad)) {
                continue;
            }
            if ($id > 0) {
                $seen[$id] = true;
            }
            $ads[] = $ad;
        }
    }

    return ad_dedupe_ads($ads);
}

function ad_count_sidebar_displayable(mysqli $conn): int
{
    return count(ad_get_sidebar_displayable($conn));
}

/**
 * Spread inline ad HTML blocks between paragraphs in blog content.
 *
 * @param list<string> $units
 */
function ad_inject_inline_ad_units(string $html, array $units): string
{
    if ($units === []) {
        return $html;
    }

    $parts = preg_split('/(<\/p>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts) || count($parts) < 3) {
        return $html . implode('', $units);
    }

    $paragraphEnds = (int) (count($parts) / 2);
    $interval = max(2, (int) floor($paragraphEnds / (count($units) + 1)));
    $unitIndex = 0;
    $paraIndex = 0;
    $out = '';

    foreach ($parts as $part) {
        $out .= $part;
        if ($part === '</p>') {
            $paraIndex++;
            if ($unitIndex < count($units) && $paraIndex % $interval === 0) {
                $out .= $units[$unitIndex];
                $unitIndex++;
            }
        }
    }

    while ($unitIndex < count($units)) {
        $out .= $units[$unitIndex];
        $unitIndex++;
    }

    return $out;
}

/**
 * Plan ads for blog post: header banner (optional), sidebar stack, inline units for article body.
 *
 * @return array{header_html:string,sidebar_html:string,sidebar_count:int,inline_units:list<string>}
 */
function ad_prepare_post_page_ads(mysqli $conn, int $sidebarMax = 6): array
{
    $empty = ['header_html' => '', 'sidebar_html' => '', 'sidebar_count' => 0, 'inline_units' => []];
    $ads = ad_get_sidebar_displayable($conn);
    if ($ads === []) {
        return $empty;
    }

    $headerHtml = '';
    foreach ($ads as $index => $ad) {
        if (($ad['placement'] ?? '') === 'header') {
            $headerHtml = ad_render_unit($ad, 'banner-light');
            unset($ads[$index]);
            break;
        }
    }
    $ads = array_values($ads);

    $sidebarAds = array_slice($ads, 0, max(0, $sidebarMax));
    $inlineAds = array_slice($ads, $sidebarMax);

    $sidebarChunks = [];
    foreach ($sidebarAds as $ad) {
        $chunk = ad_render_unit($ad, 'sidebar');
        if ($chunk !== '') {
            $sidebarChunks[] = $chunk;
        }
    }

    $inlineUnits = [];
    foreach ($inlineAds as $ad) {
        $chunk = ad_render_unit($ad, 'inline');
        if ($chunk !== '') {
            $inlineUnits[] = $chunk;
        }
    }

    $sidebarHtml = '';
    $sidebarCount = count($sidebarChunks);
    if ($sidebarCount > 0) {
        $sidebarHtml = '<div class="sidebar-ad-stack" data-nd-sidebar-ad-count="' . $sidebarCount . '">'
            . implode('', $sidebarChunks)
            . '</div>';
    }

    return [
        'header_html' => $headerHtml,
        'sidebar_html' => $sidebarHtml,
        'sidebar_count' => $sidebarCount,
        'inline_units' => $inlineUnits,
    ];
}

function ad_count_active(mysqli $conn): int
{
    if (!ads_table_exists($conn)) {
        return 0;
    }
    $res = $conn->query("SELECT COUNT(*) AS c FROM ads WHERE (status='active' OR status IS NULL OR status='')");
    if (!$res) {
        return 0;
    }
    return (int)($res->fetch_assoc()['c'] ?? 0);
}

function ads_has_code_units(mysqli $conn): bool
{
    if (!ads_table_exists($conn)) {
        return false;
    }
    $res = $conn->query("SELECT id FROM ads
        WHERE (status='active' OR status IS NULL OR status='')
        AND TRIM(COALESCE(ad_code, '')) <> ''
        LIMIT 1");
    return ($res && $res->num_rows > 0);
}

function ad_count_displayable(mysqli $conn): int
{
    if (!ads_table_exists($conn)) {
        return 0;
    }
    $res = $conn->query("SELECT type, image_path, ad_code FROM ads WHERE (status='active' OR status IS NULL OR status='')");
    if (!$res) {
        return 0;
    }
    $count = 0;
    while ($row = $res->fetch_assoc()) {
        if (ad_has_displayable_content($row)) {
            $count++;
        }
    }
    return $count;
}

function render_sidebar_ads(mysqli $conn): int
{
    $plan = ad_prepare_post_page_ads($conn);
    if ($plan['sidebar_html'] !== '') {
        echo $plan['sidebar_html'];
    }
    return $plan['sidebar_count'];
}

function nectra_render_adsense_head(): void
{
    if (empty($GLOBALS['nectra_adsense_needs_loader'])) {
        return;
    }
    echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client='
        . htmlspecialchars(NECTRA_ADSENSE_CLIENT, ENT_QUOTES, 'UTF-8')
        . '" crossorigin="anonymous"></script>' . "\n";
}

function nectra_inject_adsense_head(string $html): string
{
    if (empty($GLOBALS['nectra_adsense_needs_loader'])) {
        return $html;
    }
    $script = '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client='
        . htmlspecialchars(NECTRA_ADSENSE_CLIENT, ENT_QUOTES, 'UTF-8')
        . '" crossorigin="anonymous"></script>';
    if (stripos($html, 'pagead2.googlesyndication.com/pagead/js/adsbygoogle.js') !== false) {
        return $html;
    }
    return str_replace('</head>', $script . "\n</head>", $html);
}

function nectra_render_adsense_init(): void
{
    if (empty($GLOBALS['nectra_adsense_units'])) {
        return;
    }
    ?>
<script>
(function () {
    function initNectraAdsense() {
        var units = document.querySelectorAll('ins.adsbygoogle');
        if (!units.length) {
            return;
        }
        units.forEach(function () {
            try {
                (window.adsbygoogle = window.adsbygoogle || []).push({});
            } catch (e) {}
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNectraAdsense);
    } else {
        initNectraAdsense();
    }
})();
</script>
    <?php
}
