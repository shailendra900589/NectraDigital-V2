<?php

function ge_static_service_url_prefix(string $slug): string {
    if (str_ends_with($slug, '-services')) {
        return substr($slug, 0, -strlen('-services'));
    }
    return $slug;
}

function ge_static_service_name(string $slug, array $data): string {
    if (!empty($data['silo'])) {
        return $data['silo'];
    }
    if (!empty($data['title'])) {
        return trim(explode('|', $data['title'])[0]);
    }
    return ucwords(str_replace('-', ' ', ge_static_service_url_prefix($slug)));
}

function ge_slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function ge_replace_tokens(string $template, array $vars): string {
    foreach ($vars as $key => $value) {
        $template = str_replace('{' . $key . '}', (string)$value, $template);
    }
    return $template;
}

function ge_json_decode($json, $default = []) {
    if (empty($json)) return $default;
    if (is_array($json)) return $json;
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : $default;
}

function ge_json_encode($data): string {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function ge_hash_content(string $content): string {
    return hash('sha256', $content);
}

function ge_format_population(int $pop): string {
    if ($pop >= 10000000) return round($pop / 10000000, 1) . ' Crore';
    if ($pop >= 100000) return round($pop / 100000, 1) . ' Lakh';
    if ($pop >= 1000) return number_format($pop);
    return (string)$pop;
}

function ge_pick_variant(array $variants, int $seed): mixed {
    if (empty($variants)) {
        return '';
    }
    return $variants[$seed % count($variants)];
}

function ge_admin_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['ge_flash'] = ['type' => $type, 'message' => $message];
}

function ge_admin_get_flash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['ge_flash'])) {
        $flash = $_SESSION['ge_flash'];
        unset($_SESSION['ge_flash']);
        return $flash;
    }
    return null;
}

function ge_upload_image(array $file, string $prefix = 'ge_'): array {
    $target_dir = dirname(__DIR__, 2) . '/assets/uploads/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload error.'];
    if ($file['size'] > 2097152) return ['error' => 'Max 2MB.'];
    $allowed = ['image/webp', 'image/png', 'image/jpeg', 'image/svg+xml'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) return ['error' => 'Invalid image type.'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid($prefix, true) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $target_dir . $name)) {
        return ['success' => 'assets/uploads/' . $name];
    }
    return ['error' => 'Move failed.'];
}

function ge_build_landing_slug(string $url_prefix, string $city_slug, ?array $extra = null): string {
    $extra = $extra ?? [];
    $industry_slug = $extra['industry_slug'] ?? '';
    $hasIndustry = !empty($industry_slug);

    $pattern = $hasIndustry
        ? ge_setting('url_pattern_industry', '{url_prefix}-company-in-{city_slug}-for-{industry_slug}')
        : ge_setting('url_pattern_city', ge_setting('url_pattern', '{url_prefix}-company-in-{city_slug}'));

    return ge_slugify(ge_replace_tokens($pattern, [
        'url_prefix' => $url_prefix,
        'city_slug' => $city_slug,
        'industry_slug' => $industry_slug,
        'service_slug' => $url_prefix,
    ]));
}

function ge_default_ctas(): array {
    return [
        ['label' => 'Free Software Discovery Call', 'url' => '/contact?service=Software+Development', 'icon' => 'fa-code'],
        ['label' => 'Get Free Consultation', 'url' => '/contact?service=Consultation', 'icon' => 'fa-calendar-check'],
        ['label' => 'Request Proposal', 'url' => '/contact?service=Proposal', 'icon' => 'fa-file-alt'],
        ['label' => 'Talk To Expert', 'url' => '/contact?service=Expert+Call', 'icon' => 'fa-headset'],
        ['label' => 'Schedule Strategy Call', 'url' => '/contact?service=Strategy+Call', 'icon' => 'fa-phone'],
        ['label' => 'Get Free Website Audit', 'url' => '/contact?service=Website+Audit', 'icon' => 'fa-globe'],
    ];
}

/** Format title to SEO range (default 30–60 characters). */
function ge_trim_seo_title(string $title, string $brand = 'Nectra Digital', int $min = 30, int $max = 60): string
{
    $title = trim(preg_replace('/\s+/u', ' ', $title));
    if ($title === '') {
        $title = 'Software Development Company India';
    }

    $suffix = ' | ' . $brand;
    $hasBrand = stripos($title, $brand) !== false;

    if (!$hasBrand && !str_ends_with($title, $suffix)) {
        if (mb_strlen($title . $suffix) <= $max) {
            $title .= $suffix;
        } else {
            $title = ge_trim_at_word_boundary($title, max(18, $max - mb_strlen($suffix)), false) . $suffix;
        }
    }

    if (mb_strlen($title) > $max) {
        if (str_ends_with($title, $suffix)) {
            $core = rtrim(mb_substr($title, 0, -mb_strlen($suffix)), ' |');
            $title = ge_trim_at_word_boundary($core, max(18, $max - mb_strlen($suffix)), false) . $suffix;
        } else {
            $title = ge_trim_at_word_boundary($title, $max, false);
        }
    }

    if (mb_strlen($title) < $min) {
        $title = ge_expand_seo_title($title, $brand, $min, $max);
    }

    if (mb_strlen($title) > $max) {
        if (str_ends_with($title, $suffix)) {
            $core = rtrim(mb_substr($title, 0, -mb_strlen($suffix)), ' |');
            $title = ge_trim_at_word_boundary($core, max(18, $max - mb_strlen($suffix)), false) . $suffix;
        } else {
            $title = ge_trim_at_word_boundary($title, $max, false);
        }
    }

    return $title;
}

function ge_expand_seo_title(string $title, string $brand, int $min, int $max): string
{
    $suffix = ' | ' . $brand;
    $candidates = [$title];

    if (str_ends_with($title, $suffix)) {
        $core = rtrim(mb_substr($title, 0, -mb_strlen($suffix)), ' |');
        if (ge_title_has_local_qualifier($core)) {
            return $title;
        }
        $candidates = array_merge($candidates, ge_seo_title_variants($core));
        foreach ($candidates as $coreVariant) {
            $coreVariant = trim(preg_replace('/\s+/u', ' ', $coreVariant));
            if (stripos($coreVariant, $brand) !== false) {
                $try = $coreVariant;
            } else {
                $try = $coreVariant . $suffix;
            }
            if (mb_strlen($try) >= $min && mb_strlen($try) <= $max) {
                return $try;
            }
            if (mb_strlen($try) > $max && stripos($coreVariant, $brand) === false) {
                $trimmed = ge_trim_at_word_boundary($coreVariant, $max - mb_strlen($suffix), false) . $suffix;
                if (mb_strlen($trimmed) >= $min && mb_strlen($trimmed) <= $max) {
                    return $trimmed;
                }
            } elseif (mb_strlen($try) > $max) {
                $trimmed = ge_trim_at_word_boundary($try, $max, false);
                if (mb_strlen($trimmed) >= $min) {
                    return $trimmed;
                }
            }
        }
    }

    $candidates = array_merge($candidates, ge_seo_title_variants($title));
    foreach ($candidates as $variant) {
        $variant = trim(preg_replace('/\s+/u', ' ', $variant));
        if (mb_strlen($variant) >= $min && mb_strlen($variant) <= $max) {
            return $variant;
        }
    }

    return $title;
}

function ge_seo_title_variants(string $core): array
{
    $variants = [];

    if (preg_match('/\bin\s+([A-Za-z][A-Za-z\s]{1,24})$/u', $core, $m)) {
        $city = trim($m[1]);
        if (strcasecmp($city, 'India') !== 0) {
            $variants = [
                "Software Development Company in {$city} India",
                "Custom Software Development {$city} India",
                "Web & App Development Company {$city}",
                "SaaS Development Company {$city} India",
                $core,
            ];
            return array_unique($variants);
        }
    }

    $variants = [
        'Custom Software Development Company India',
        'Software Development Company India Agency',
        'SaaS & Web App Development India',
        'Top Software Developers India · Nectra',
        $core . ' India',
        $core . ' Agency',
        $core . ' Services',
        $core . ' Co',
    ];

    if (stripos($core, 'contact') !== false) {
        $variants[] = 'Contact Nectra Digital Software India HQ';
        $variants[] = 'Contact Nectra Digital · Custom Software';
    }

    return array_unique($variants);
}

function ge_title_has_local_qualifier(string $core): bool
{
    return (bool) preg_match('/\bin\s+(?!India\b)[A-Za-z][A-Za-z\s]{1,24}(?:\s|$)/u', trim($core));
}

function ge_pad_seo_title(string $title, string $brand, int $min, int $max): string
{
    return ge_expand_seo_title($title, $brand, $min, $max);
}

/** Trim description to 120–160 chars for meta tags. */
function ge_trim_seo_description(string $desc, int $min = 120, int $max = 160): string
{
    $desc = trim(preg_replace('/\s+/u', ' ', strip_tags($desc)));
    if ($desc === '') {
        return '';
    }

    if (mb_strlen($desc) > $max) {
        $desc = ge_trim_at_word_boundary($desc, $max, true);
    }

    if (mb_strlen($desc) < $min) {
        $pad = ' Free software discovery call.';
        if (mb_strlen($desc . $pad) <= $max) {
            $desc .= $pad;
        }
    }

    return $desc;
}

function ge_trim_at_word_boundary(string $text, int $max, bool $ellipsis): string
{
    if (mb_strlen($text) <= $max) {
        return $text;
    }

    $room = $ellipsis ? $max - 1 : $max;
    $cut = mb_substr($text, 0, $room);
    $lastSpace = mb_strrpos($cut, ' ');
    if ($lastSpace !== false && $lastSpace > (int)($room * 0.55)) {
        $cut = mb_substr($cut, 0, $lastSpace);
    }

    $cut = rtrim($cut, ' |,;-');

    return $ellipsis ? $cut . '…' : $cut;
}

function ge_table_exists_check(string $table): bool {
    return ge_table_exists($table);
}

function ge_paginate(int $total, int $page, int $per_page = 25): array {
    $pages = max(1, (int)ceil($total / $per_page));
    $page = max(1, min($page, $pages));
    $offset = ($page - 1) * $per_page;
    return ['page' => $page, 'per_page' => $per_page, 'total' => $total, 'pages' => $pages, 'offset' => $offset];
}

/** Auto-generate and persist cron token if missing. */
function ge_ensure_cron_token(): string
{
    if (!function_exists('ge_table_exists') || !ge_table_exists('ge_settings')) {
        return '';
    }
    $token = trim((string)ge_setting('cron_token', ''));
    if ($token !== '') {
        return $token;
    }
    $token = bin2hex(random_bytes(20));
    $db = ge_conn();
    $stmt = $db->prepare("INSERT INTO ge_settings (setting_key, setting_value) VALUES ('cron_token', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
    }
    return $token;
}

/** Protect web cron endpoints; CLI always allowed. */
function ge_cron_auth_or_exit(): void
{
    if (php_sapi_name() === 'cli') {
        return;
    }
    $token = ge_ensure_cron_token();
    $provided = (string)($_GET['token'] ?? '');
    if ($token === '' || !hash_equals($token, $provided)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error'   => 'Invalid or missing cron token.',
            'hint'    => 'Open Admin → Growth → Settings to copy your auto-generated cron token.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function ge_cron_urls(): array
{
    $base = rtrim(defined('SITE_URL') ? SITE_URL : '', '/');
    $token = ge_ensure_cron_token();
    $q = $token !== '' ? ('?token=' . urlencode($token)) : '';
    return [
        'indexing'   => $base . '/cron/process-indexing.php' . $q,
        'i18n_index' => $base . '/cron/i18n-indexnow.php' . $q,
        'discovery'  => $base . '/cron/publish-discovery.php' . $q,
        'token'      => $token,
    ];
}
