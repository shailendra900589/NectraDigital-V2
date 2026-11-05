<?php 
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/brand-config.php'; 
require_once __DIR__ . '/text-utils.php';
require_once __DIR__ . '/seo-data.php';
require_once __DIR__ . '/growth/helpers.php';
require_once __DIR__ . '/i18n.php';

global $page_title, $page_desc, $page_img, $page_keys, $noindex, $og_type, $page_schema, $canonical_url, $seo_title_min, $seo_title_max;

$nectra_lang = nectra_get_user_lang();
$title_min = isset($seo_title_min) ? (int) $seo_title_min : 30;
$title_max = isset($seo_title_max) ? (int) $seo_title_max : 60;

$request_uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$page_path = nectra_uri_path_for_canonical($request_uri);

if (!empty($canonical_url)) {
    $canonical_href = nectra_normalize_canonical_url($canonical_url);
} else {
    $canonical_href = nectra_normalize_canonical_url($page_path ?: '/');
}

$nectra_base_path = nectra_site_base_path();

if (!headers_sent()) {
    header('Link: <' . $canonical_href . '>; rel="canonical"', false);
}

$default_title = "Best SEO & Digital Marketing in India | Nectra Digital";
$default_desc  = "Best SEO company in India — search engine optimization, AI automation, digital marketing & software development. 200+ projects. Free audit.";
$default_img   = SITE_URL . "/assets/images/logo.png";

$site_name_safe = defined('SITE_NAME') ? SITE_NAME : 'Nectra Digital';

if (isset($page_img) && !empty($page_img)) {
    if (strpos($page_img, 'http') === 0) {
        $meta_img = $page_img;
    } elseif (strpos($page_img, 'assets/') !== false) {
        $meta_img = SITE_URL . '/' . ltrim($page_img, '/');
    } else {
        $meta_img = SITE_URL . '/assets/uploads/' . $page_img;
    }
} else {
    $meta_img = $default_img;
}

if (!empty($page_title)) {
    $meta_title = ge_trim_seo_title(trim($page_title), $site_name_safe, $title_min, $title_max);
} else {
    $meta_title = ge_trim_seo_title($default_title, $site_name_safe, $title_min, $title_max);
}

$meta_desc = ge_trim_seo_description((!empty($page_desc)) ? $page_desc : $default_desc, 120, 160);
$meta_og_type = (!empty($og_type)) ? $og_type : 'website';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(nectra_html_lang()); ?>" dir="<?php echo nectra_html_dir(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($nectra_base_path !== ''): ?>
    <base href="<?php echo htmlspecialchars(rtrim(SITE_URL, '/') . '/'); ?>">
    <?php endif; ?>

    <title><?php echo nectra_display_text($meta_title); ?></title>
    <meta name="description" content="<?php echo nectra_display_text($meta_desc); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_href); ?>">
    <meta name="author" content="<?php echo FOUNDER_NAME; ?>">
    <meta name="robots" content="<?php echo !empty($noindex) ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'; ?>">
    
    <?php if (!empty($noindex)): ?>
    <meta name="googlebot" content="noindex, nofollow">
    <?php endif; ?>

    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/favicon_io/favicon-32x32.png">
    <link rel="manifest" href="<?php echo SITE_URL; ?>/assets/favicon_io/site.webmanifest">
    <link rel="alternate" type="text/plain" href="<?php echo SITE_URL; ?>/llms.txt" title="LLM Context">
    <link rel="alternate" type="application/rss+xml" title="Nectra Digital RSS Feed" href="<?php echo SITE_URL; ?>/rss.xml" />
    <link rel="alternate" type="application/rss+xml" title="Nectra Digital Discover Feed" href="<?php echo SITE_URL; ?>/discover-feed.xml" />
    <link rel="alternate" type="application/atom+xml" title="Nectra Digital Atom Feed" href="<?php echo SITE_URL; ?>/atom.xml" />
    <link rel="alternate" type="application/rss+xml" title="Nectra Digital News" href="<?php echo SITE_URL; ?>/news-sitemap.xml" />

    <?php if (empty($noindex)): ?>
    <meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="bingbot" content="index, follow">
    <?php endif; ?>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=25">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/i18n.css?v=3">
    <script>window.NectraSite = { basePath: <?php echo json_encode(nectra_site_base_path(), JSON_UNESCAPED_UNICODE); ?> };
    window.nectraWebPath = function (p) {
        if (!p || p.charAt(0) !== '/') return p;
        var b = (window.NectraSite && window.NectraSite.basePath) || '';
        return b + p;
    };</script>
    <script>window.NectraI18n = <?php echo json_encode(nectra_i18n_config_js(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;</script>
    <?php if ($nectra_lang !== 'en'):
        $nectra_gcode = nectra_google_lang_code($nectra_lang);
        $nectra_cdom = nectra_cookie_domain();
    ?>
    <script>
    (function(g,d){
        var v='/en/'+g;
        document.cookie='googtrans='+v+';path=/;max-age=31536000;SameSite=Lax';
        document.cookie='googtrans='+v+';path=/;domain='+d+';max-age=31536000;SameSite=Lax';
        document.cookie='nectra_lang='+encodeURIComponent(<?php echo json_encode($nectra_lang); ?>)+';path=/;max-age=31536000;SameSite=Lax';
    })(<?php echo json_encode($nectra_gcode); ?>, <?php echo json_encode($nectra_cdom); ?>);
    </script>
    <?php endif; ?>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    
    <meta property="og:site_name" content="<?php echo $site_name_safe; ?>" />
    <meta property="og:type" content="<?php echo $meta_og_type; ?>" />
    <meta property="og:title" content="<?php echo nectra_display_text($meta_title); ?>" />
    <meta property="og:description" content="<?php echo nectra_display_text($meta_desc); ?>" />
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_href); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($meta_img); ?>" />
    <meta property="og:image:alt" content="<?php echo $site_name_safe; ?> - SEO & Digital Marketing Agency India" />
    <meta property="og:locale" content="en_IN" />
    <meta property="og:locale:alternate" content="hi_IN" />
    <meta property="og:locale:alternate" content="bn_IN" />
    <meta property="og:locale:alternate" content="ta_IN" />
    <?php if (defined('NECTRA_FACEBOOK_URL')): ?>
    <meta property="article:publisher" content="<?php echo NECTRA_FACEBOOK_URL; ?>" />
    <?php endif; ?>

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@nectradigital">
    <meta name="twitter:title" content="<?php echo nectra_display_text($meta_title); ?>">
    <meta name="twitter:description" content="<?php echo nectra_display_text($meta_desc); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($meta_img); ?>">
    <meta name="twitter:image:alt" content="<?php echo $site_name_safe; ?>">

    <?php
    require_once __DIR__ . '/site-contact.php';
    $global_schemas = [get_organization_schema(), get_website_schema(), get_founder_schema()];
    if (!empty($page_schema) && is_array($page_schema)) {
        $global_schemas = array_merge($global_schemas, $page_schema);
    }
    output_schema_graph($global_schemas);
    ?>

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1S5L5N87KR"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-1S5L5N87KR');
    </script>
    <?php if (defined('NECTRA_FB_PIXEL_ID') && NECTRA_FB_PIXEL_ID !== ''): ?>
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?php echo htmlspecialchars(NECTRA_FB_PIXEL_ID, ENT_QUOTES); ?>');
    fbq('track', 'PageView');
    </script>
    <?php endif; ?>
</head>
<?php
$isDarkHeader = nectra_page_uses_dark_header($page_path);

$ndBodyClass = 'nd-has-fixed-nav';
if ($nectra_lang !== 'en') {
    $ndBodyClass .= ' nectra-translated nectra-lang-' . htmlspecialchars($nectra_lang);
}
if ($isDarkHeader) {
    $ndBodyClass .= ' nd-nav-dark-context';
}
?>
<body class="<?php echo $ndBodyClass; ?>"<?php echo $isDarkHeader ? ' data-nd-nav-theme="dark"' : ''; ?>>

<?php include __DIR__ . '/ui/navbar.php'; ?>

