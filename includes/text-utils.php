<?php
/**
 * Text normalization — fixes &amp; and other HTML entities showing literally sitewide.
 */

if (defined('NECTRA_TEXT_UTILS_LOADED')) {
    return;
}
define('NECTRA_TEXT_UTILS_LOADED', true);

require_once __DIR__ . '/crawler_access.php';

function nectra_decode_entities(?string $text, int $maxPasses = 6): string
{
    if ($text === null || $text === '') {
        return '';
    }
    $prev = null;
    $current = $text;
    $passes = 0;
    while ($current !== $prev && $passes < $maxPasses) {
        $prev = $current;
        $current = html_entity_decode($current, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $passes++;
    }
    return $current;
}

function nectra_utf8_mojibake_score(string $text): int
{
    return substr_count($text, 'Ã') + substr_count($text, 'Â') + substr_count($text, 'â€') + substr_count($text, '�');
}

/** Undo UTF-8 bytes misread as Windows-1252 / Latin-1 (common with emojis and smart punctuation). */
function nectra_repair_utf8_layers(string $text): string
{
    $previous = null;
    $current = $text;

    for ($pass = 0; $pass < 6 && $current !== $previous; $pass++) {
        $previous = $current;
        if (!preg_match('/(?:Ã|Â|â€|â€™|â€œ|â€"|â€"|ðŸ|�)/u', $current)) {
            break;
        }

        $best = $current;
        $bestScore = nectra_utf8_mojibake_score($current);

        foreach (['Windows-1252', 'ISO-8859-1'] as $from) {
            $attempt = @mb_convert_encoding($current, 'UTF-8', $from);
            if (!is_string($attempt) || $attempt === '' || !mb_check_encoding($attempt, 'UTF-8')) {
                continue;
            }
            $score = nectra_utf8_mojibake_score($attempt);
            if ($score < $bestScore) {
                $best = $attempt;
                $bestScore = $score;
            }
        }

        $current = $best;
    }

    return $current;
}

function nectra_clean_mojibake_artifacts(string $text): string
{
    $text = str_replace(
        ["\xc2\xa0", "\xe2\x80\xaf", '&#160;', '&#xA0;', '&nbsp;'],
        ' ',
        $text
    );

    $map = [
        'â€™' => "'",
        'â€˜' => "'",
        'â€œ' => '"',
        'â€' => '"',
        'â€“' => '–',
        'â€”' => '—',
        'â€¦' => '…',
        'Letâ' => "Let's",
        'donâ€™t' => "don't",
        'Donâ€™t' => "Don't",
        'wonâ€™t' => "won't",
        'itâ€™s' => "it's",
        'Itâ€™s' => "It's",
        'Indiax' => 'India',
        'indiax' => 'India',
    ];
    $text = str_replace(array_keys($map), array_values($map), $text);

    // Broken quote + nbsp sequences from Word exports (e.g. systems""Â like).
    $text = preg_replace('/"\s*"Â\s*/u', ' ', $text) ?? $text;
    $text = preg_replace('/"\s*Â(?=\s*[a-zA-Z])/u', '" ', $text) ?? $text;
    $text = preg_replace('/(?<=[a-zA-Z0-9])\s*Â(?=\s*[a-zA-Z])/u', ' ', $text) ?? $text;

    $text = preg_replace('/\x{FFFD}+/u', '', $text) ?? $text;
    $text = preg_replace('/\s{2,}/u', ' ', $text) ?? $text;

    return $text;
}

/** Regex for double/triple-encoded emoji/icon byte sequences in content. */
function nectra_icon_mojibake_pattern(): string
{
    return '/(?:'
        . '(?:Ã|Ãƒ)(?:°|Â°)?(?:Å|Ã…)?(?:¸|Â¸)?[^\s<]{0,28}'
        . '|(?:ð|Ã°)(?:Ÿ|Å¸)[^\s<]{0,22}'
        . '|(?:â|Ã¢)[^\s<]{2,14}'
        . '|(?:Ã[^\s<]){2,}(?:Â[^\s<]{0,16})+'
        . ')/u';
}

function nectra_extract_emoji_from_mojibake(string $blob): string
{
    $blob = trim($blob);
    if ($blob === '') {
        return '';
    }

    $candidates = [$blob];
    $cur = $blob;

    for ($pass = 0; $pass < 5; $pass++) {
        foreach (['Windows-1252', 'ISO-8859-1'] as $enc) {
            $attempt = @mb_convert_encoding($cur, 'UTF-8', $enc);
            if (!is_string($attempt) || $attempt === '' || !mb_check_encoding($attempt, 'UTF-8')) {
                continue;
            }
            $candidates[] = $attempt;
            if (preg_match('/\p{Extended_Pictographic}/u', $attempt, $m)) {
                return $m[0];
            }
            $cur = $attempt;
        }

        if (function_exists('iconv')) {
            $latin = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $cur);
            if (is_string($latin) && $latin !== '') {
                $attempt = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $latin);
                if (is_string($attempt) && $attempt !== '') {
                    $candidates[] = $attempt;
                    if (preg_match('/\p{Extended_Pictographic}/u', $attempt, $m)) {
                        return $m[0];
                    }
                }
            }
        }
    }

    foreach ($candidates as $candidate) {
        if (preg_match('/\p{Extended_Pictographic}/u', $candidate, $m)) {
            return $m[0];
        }
    }

    return '';
}

function nectra_icon_html_from_context(string $afterText): string
{
    $ctx = strtolower($afterText);
    if (str_contains($ctx, 'whatsapp') || str_contains($ctx, 'wa.me')) {
        return '<i class="fab fa-whatsapp nd-txt-icon nd-txt-icon--wa" aria-hidden="true"></i>';
    }
    if (str_contains($ctx, 'learn') || str_contains($ctx, 'journey') || str_contains($ctx, 'shuru')) {
        return '<i class="fas fa-rocket nd-txt-icon nd-txt-icon--accent" aria-hidden="true"></i>';
    }
    if (str_contains($ctx, 'click here') || str_contains($ctx, 'talk to')) {
        return '<i class="fas fa-comment-dots nd-txt-icon nd-txt-icon--accent" aria-hidden="true"></i>';
    }
    if (str_contains($ctx, 'revenue') || str_contains($ctx, 'roi') || str_contains($ctx, 'growth')) {
        return '<i class="fas fa-chart-line nd-txt-icon nd-txt-icon--accent" aria-hidden="true"></i>';
    }
    return '<i class="fas fa-circle-check nd-txt-icon nd-txt-icon--accent" aria-hidden="true"></i>';
}

function nectra_icon_html_for_emoji(string $emoji, string $afterText = ''): string
{
    $ctx = strtolower($afterText);
    if ($emoji === '💬' || $emoji === '📱' || $emoji === '📲'
        || str_contains($ctx, 'whatsapp') || str_contains($ctx, 'wa.me')) {
        return '<i class="fab fa-whatsapp nd-txt-icon nd-txt-icon--wa" aria-hidden="true"></i>';
    }

    static $map = [
        '🚀' => 'fa-rocket',
        '💡' => 'fa-lightbulb',
        '✅' => 'fa-circle-check',
        '✔' => 'fa-check',
        '☑' => 'fa-check-square',
        '📈' => 'fa-chart-line',
        '💰' => 'fa-coins',
        '💼' => 'fa-briefcase',
        '🎯' => 'fa-bullseye',
        '🔥' => 'fa-fire',
        '⭐' => 'fa-star',
        '🌟' => 'fa-star',
        '👉' => 'fa-hand-point-right',
        '👇' => 'fa-hand-point-down',
        '👆' => 'fa-hand-point-up',
        '👋' => 'fa-hand',
        '📞' => 'fa-phone',
        '☎' => 'fa-phone',
        '💬' => 'fa-comment-dots',
        '📱' => 'fa-mobile-screen',
        '📲' => 'fa-mobile-screen-button',
        '🛒' => 'fa-cart-shopping',
        '🎓' => 'fa-graduation-cap',
        '🔗' => 'fa-link',
        '⚡' => 'fa-bolt',
        '🏆' => 'fa-trophy',
    ];

    $icon = $map[$emoji] ?? 'fa-circle-check';
    return '<i class="fas ' . $icon . ' nd-txt-icon nd-txt-icon--accent" aria-hidden="true"></i>';
}

/** Turn broken emoji byte blobs into Unicode (plain text) or remove garbage. */
function nectra_repair_icon_blobs_to_unicode(string $text): string
{
    $pattern = nectra_icon_mojibake_pattern();
    return preg_replace_callback(
        $pattern,
        static function (array $m): string {
            $blob = $m[0];
            $emoji = nectra_extract_emoji_from_mojibake($blob);
            if ($emoji !== '') {
                return $emoji . ' ';
            }
            return ' ';
        },
        $text
    ) ?? $text;
}

/** Replace broken blobs + Unicode emojis with Font Awesome icons (HTML content only). */
function nectra_apply_inline_icons_to_html(string $html): string
{
    if ($html === '') {
        return '';
    }

    $pattern = nectra_icon_mojibake_pattern();
    $html = preg_replace_callback(
        $pattern,
        static function (array $m) use ($html): string {
            $blob = $m[0];
            $pos = strpos($html, $blob);
            $after = $pos !== false ? substr($html, $pos + strlen($blob), 120) : '';
            $emoji = nectra_extract_emoji_from_mojibake($blob);
            if ($emoji !== '') {
                return nectra_icon_html_for_emoji($emoji, strip_tags($after));
            }
            return nectra_icon_html_from_context(strip_tags($after));
        },
        $html
    ) ?? $html;

    return preg_replace_callback(
        '/\p{Extended_Pictographic}/u',
        static function (array $m): string {
            return nectra_icon_html_for_emoji($m[0], '');
        },
        $html
    ) ?? $html;
}

function nectra_fix_mojibake(?string $text): string
{
    if ($text === null || $text === '') {
        return '';
    }

    $text = nectra_decode_entities($text);
    $text = nectra_repair_utf8_layers($text);
    $text = nectra_clean_mojibake_artifacts($text);
    $text = nectra_repair_icon_blobs_to_unicode($text);

    $text = preg_replace('/Letâ[\x00-\xFF]{0,3}s/u', "Let's", $text) ?? $text;
    $text = preg_replace('/\s{2,}/u', ' ', $text) ?? $text;

    return trim($text);
}

/** Blog HTML: fix encoding on output and on admin save. */
function nectra_sanitize_blog_html(?string $html): string
{
    if ($html === null || $html === '') {
        return '';
    }

    $html = nectra_fix_mojibake($html);
    $html = nectra_apply_inline_icons_to_html($html);

    if (!mb_check_encoding($html, 'UTF-8')) {
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    }

    return $html;
}

function nectra_display_text(?string $text): string
{
    return htmlspecialchars(nectra_fix_mojibake($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Visible author on Insights list and blog post header. */
function nectra_blog_byline(): string
{
    return 'admin';
}

/** Canonical URL for a blog post (pretty slug + post.php fallback). */
function nectra_blog_post_href(string $slug): string
{
    $slug = trim($slug, '/');
    if ($slug === '') {
        return function_exists('nectra_nav_path') ? nectra_nav_path('/insights') : '/insights';
    }
    return function_exists('nectra_nav_path') ? nectra_nav_path('/' . $slug) : '/' . $slug;
}

function sanitize_db_text(?string $data): string
{
    return stripslashes(nectra_fix_mojibake(trim((string) $data)));
}

/** True when the app runs on a developer machine (XAMPP, LAN IP, *.test, or local Apache bind). */
function nectra_is_local_environment(): bool
{
    if (php_sapi_name() === 'cli') {
        return true;
    }

    $env = strtolower((string)(getenv('NECTRA_ENV') ?: ($_SERVER['NECTRA_ENV'] ?? '')));
    if (in_array($env, ['local', 'dev', 'development'], true)) {
        return true;
    }
    if (defined('NECTRA_LOCAL_DEV') && NECTRA_LOCAL_DEV) {
        return true;
    }

    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
        return true;
    }
    if ($host !== '' && (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1'))) {
        return true;
    }
    foreach (['.local', '.test', '.localhost', '.invalid'] as $suffix) {
        if ($host !== '' && str_ends_with($host, $suffix)) {
            return true;
        }
    }
    if ($host !== '' && preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/', $host)) {
        return true;
    }

    $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
    if (in_array($serverAddr, ['127.0.0.1', '::1'], true)) {
        return true;
    }

    return false;
}

/** Root-relative path with install prefix (e.g. /nectradigital_final/about). */
function nectra_web_path(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');
    $base = nectra_site_base_path();
    if ($base === '') {
        return $path;
    }
    if ($path === '/') {
        return $base . '/';
    }
    return $base . $path;
}

/** Absolute site URL for a path (works in subfolder installs). */
function nectra_url(string $path = '/'): string
{
    $path = ltrim($path, '/');
    return rtrim(SITE_URL, '/') . ($path === '' ? '' : '/' . $path);
}

/** True when a root-relative path segment already includes the install base (avoid double prefix). */
function nectra_path_already_has_base(string $pathAfterSlash, string $base): bool
{
    $baseTrim = trim($base, '/');
    if ($baseTrim === '') {
        return false;
    }
    if ($pathAfterSlash === $baseTrim || str_starts_with($pathAfterSlash, $baseTrim . '/')) {
        return true;
    }
    return false;
}

/**
 * Prefix root-relative href/action/fetch URLs in HTML for subdirectory installs (XAMPP).
 * Skips paths already prefixed (e.g. from nectra_web_path / nectra_nav_path).
 */
function nectra_prefix_root_relative_urls(string $html): string
{
    $base = nectra_site_base_path();
    if ($base === '') {
        return $html;
    }

    $prefixPath = static function (string $rest) use ($base): string {
        if (nectra_path_already_has_base($rest, $base)) {
            return '/' . $rest;
        }
        return $base . '/' . $rest;
    };

    $html = preg_replace_callback(
        '/\s(href|action)=(["\'])\/(?!\/)([^"\']*)/',
        static function (array $m) use ($prefixPath): string {
            return ' ' . $m[1] . '=' . $m[2] . $prefixPath($m[3]);
        },
        $html
    ) ?? $html;

    $html = preg_replace_callback(
        '/\bfetch\s*\(\s*(["\'])\/(?!\/)([^"\']*)/',
        static function (array $m) use ($prefixPath): string {
            return 'fetch(' . $m[1] . $prefixPath($m[2]);
        },
        $html
    ) ?? $html;

    $html = preg_replace_callback(
        '/(\.location\.(?:href|assign)\s*=\s*|location\.replace\s*\(\s*)(["\'])\/(?!\/)([^"\']*)/',
        static function (array $m) use ($prefixPath): string {
            return $m[1] . $m[2] . $prefixPath($m[3]);
        },
        $html
    ) ?? $html;

    return $html;
}

/** Web path prefix when the site lives in a subdirectory (e.g. /nectradigital_final). */
function nectra_site_base_path(): string
{
    if (!defined('SITE_URL')) {
        return '';
    }
    $path = parse_url(SITE_URL, PHP_URL_PATH);
    if (!$path || $path === '/') {
        return '';
    }
    return rtrim($path, '/');
}

/** Strip install prefix and .php for canonical paths. */
function nectra_uri_path_for_canonical(string $requestUri): string
{
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
    $path = preg_replace('#\.php$#i', '', $path);
    $base = nectra_site_base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base)) ?: '/';
    }
    return $path ?: '/';
}

/**
 * Dark fixed navbar (over navy hero). Pages may set $isDarkHeader before header.php.
 */
function nectra_page_uses_dark_header(?string $pagePath = null): bool
{
    global $isDarkHeader;
    if (!empty($isDarkHeader)) {
        return true;
    }

    if ($pagePath === null || $pagePath === '') {
        $requestUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $pagePath = nectra_uri_path_for_canonical($requestUri);
    }

    $path = rtrim($pagePath, '/') ?: '/';

    if ($path === '/') {
        return true;
    }

    $exactDark = [
        '/portfolio',
        '/insights',
        '/aeo',
        '/about',
        '/services',
        '/solutions',
        '/contact',
        '/careers',
        '/hire-experts',
        '/privacy',
        '/terms',
        '/editorial-guidelines',
        '/content-strategy',
        '/disclaimer',
        '/tools',
    ];
    if (in_array($path, $exactDark, true)) {
        return true;
    }

    if (preg_match('#^/digital-agency-[a-z0-9-]+$#', $path)) {
        return true;
    }

    if (str_starts_with($path, '/tools/')) {
        return true;
    }

    return false;
}

/**
 * Switch navbar to light variant after scrolling past the page hero (hybrid layouts).
 * Service landings set $navAdaptive = false before header.php.
 */
function nectra_nav_adapts_to_scroll(?string $pagePath = null): bool
{
    return false;
}

/** Build SITE_URL from document root → project folder (stable under Apache rewrites). */
function nectra_compute_site_url(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = nectra_is_https_request() ? 'https://' : 'http://';

    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
    $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)) ?: '');

    $basePath = '';
    if ($docRoot !== '' && $projectRoot !== '' && str_starts_with($projectRoot, $docRoot)) {
        $basePath = substr($projectRoot, strlen($docRoot));
    } else {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = preg_replace('#/(includes|admin|growth|api|tools).*$#', '', $scriptDir);
    }

    return rtrim($scheme . $host . rtrim($basePath, '/'), '/');
}

function nectra_preferred_host(): string
{
    if (defined('SITE_URL')) {
        $host = parse_url(SITE_URL, PHP_URL_HOST);
        if ($host) {
            return $host;
        }
    }
    return 'www.nectradigital.com';
}

function nectra_is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    $proto = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
    return $proto === 'https' || strtolower($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on';
}

/** 301 to https://www.nectradigital.com when Hostinger/CDN bypasses .htaccess. */
function nectra_enforce_preferred_host(): void
{
    if (php_sapi_name() === 'cli' || headers_sent()) {
        return;
    }

    if (nectra_is_local_environment()) {
        return;
    }

    $preferred = strtolower(nectra_preferred_host());
    $host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));

    if ($host === $preferred && nectra_is_https_request()) {
        return;
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path = $parts['path'] ?? '/';
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        unset($query['lang']);
    }
    $qs = $query ? '?' . http_build_query($query) : '';

    $target = 'https://' . $preferred . $path . $qs;
    $current = (nectra_is_https_request() ? 'https://' : 'http://') . $host . $uri;
    if (rtrim($target, '/') === rtrim(strtok($current, '#') ?: $current, '/')) {
        return;
    }

    header('Location: ' . $target, true, 301);
    exit;
}
