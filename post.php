<?php 
// 1. SYSTEM FIXES: BUFFERING & CHARSET
ob_start();
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/blog_orphan.php';
require_once __DIR__ . '/includes/blog_faq.php';
require_once __DIR__ . '/includes/ads-engine.php';
ads_ensure_schema($conn);

// FORCE DB CHARSET
if(isset($conn)) {
    $conn->set_charset("utf8mb4");
}

// 2. DEFINE FUNCTIONS FIRST
if (!function_exists('clean_input')) {
    function clean_input($data) {
        global $conn;
        if (function_exists('sanitize_db_text')) {
            $data = sanitize_db_text($data);
        } else {
            $data = trim(stripslashes($data));
        }
        return ($conn) ? $conn->real_escape_string($data) : $data;
    }
}

// 3. FETCH POST DATA
if(isset($_GET['slug'])) {
    $slug = clean_input($_GET['slug']);
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    } else {
        header("Location: " . SITE_URL . "/404.php"); exit;
    }
} else {
    header("Location: " . SITE_URL . "/insights.php"); exit;
}

// 4. *** ADVANCED MULTI-PHRASE SEO KEYWORD GENERATOR ***
// FIX: Fully decode HTML entities (&amp; → &) including double-encoded titles
$decoded_title = nectra_decode_entities($post['title']);
$decoded_category = nectra_decode_entities($post['category']);

$page_title = $decoded_title;
$seo_title_min = 25;
$seo_title_max = 65;

// NEW: Check if manual meta description exists, else auto-generate
if (!empty($post['meta_description'])) {
    $page_desc = nectra_decode_entities(strip_tags($post['meta_description']));
} else {
    $page_desc = nectra_decode_entities(mb_substr(strip_tags($post['content']), 0, 160)) . "...";
}

// Clean the title of special characters
$clean_title = strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $decoded_title));
$words_array = array_values(array_filter(explode(' ', $clean_title)));

// Expanded stop-word dictionary (Removes useless words)
$stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'to', 'in', 'on', 'at', 'by', 'for', 'with', 'about', 'as', 'of', 'how', 'why', 'what', 'when', 'where', 'who', 'will', 'it', 'that', 'this', 'you', 'your', 'need', 'both', 'vs', 'can', 'do', 'does', 'from', 'get', 'we', 'us', 'amp', 'nbsp', 'quot'];

// Filter to get strong single words
$filtered_words = [];
foreach ($words_array as $word) {
    if (strlen($word) > 2 && !in_array($word, $stop_words)) {
        $filtered_words[] = $word;
    }
}

$seo_keywords_array = [];

// A. Add the full exact match phrase (Highly SEO attractive)
$seo_keywords_array[] = $clean_title;

// B. Generate 2-word phrases (Long-tail keywords)
$count = count($filtered_words);
for ($i = 0; $i < $count - 1; $i++) {
    $seo_keywords_array[] = $filtered_words[$i] . ' ' . $filtered_words[$i+1];
}

// C. Generate 3-word phrases (Very specific long-tail)
for ($i = 0; $i < $count - 2; $i++) {
    $seo_keywords_array[] = $filtered_words[$i] . ' ' . $filtered_words[$i+1] . ' ' . $filtered_words[$i+2];
}

// D. Add the strong single words back in
$seo_keywords_array = array_merge($seo_keywords_array, $filtered_words);

// E. Remove duplicates and slice the top 15 most relevant
$seo_keywords_array = array_unique($seo_keywords_array);
$auto_generated_keys = implode(', ', array_slice($seo_keywords_array, 0, 15));

// Final Balanced Keywords: Category + Defaults + Auto-Generated Phrases
$page_keys  = $decoded_category . ", Nectra Digital, Tech Insights, " . $auto_generated_keys;

// Smart Image Path Fix
$display_img = $post['image'];
if (!empty($display_img) && strpos($display_img, 'http') === false) {
    $display_img = SITE_URL . '/' . ltrim($display_img, '/');
}
$page_img = $display_img;
$og_type = 'article';
require_once 'includes/i18n.php';
$canonical_url = nectra_page_canonical('/' . $post['slug']);

if (!headers_sent()) {
    header('X-Robots-Tag: index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
}

$post_date = date('c', strtotime($post['created_at']));
$post_modified = !empty($post['updated_at']) ? date('c', strtotime($post['updated_at'])) : $post_date;

require_once 'includes/seo-data.php';

$page_schema = [
    get_breadcrumb_schema([
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Insights', 'url' => SITE_URL . '/insights'],
        ['name' => $decoded_title, 'url' => SITE_URL . '/' . $post['slug']]
    ]),
    [
        '@type' => 'BlogPosting',
        '@id' => SITE_URL . '/' . $post['slug'] . '#article',
        'headline' => $decoded_title,
        'description' => $page_desc,
        'image' => $display_img ?: SITE_URL . '/assets/images/logo.png',
        'datePublished' => $post_date,
        'dateModified' => $post_modified,
        'author' => get_founder_schema(),
        'publisher' => ['@id' => SITE_URL . '/#organization'],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => SITE_URL . '/' . $post['slug']],
        'articleSection' => $decoded_category,
        'inLanguage' => 'en-IN'
    ]
];

$isDarkHeader = true;

// 5. INJECT HEADER & BASE TAG
include 'includes/header.php';
require_once 'includes/seo-components.php'; 
$header_html = ob_get_clean();
$base_tag = '<base href="' . SITE_URL . '/">';
$meta_charset = '<meta charset="UTF-8">'; 

$header_html = str_replace('<head>', "<head>\n" . $meta_charset . "\n" . $base_tag, $header_html);
if (ads_has_code_units($conn)) {
    $GLOBALS['nectra_adsense_needs_loader'] = true;
}
$header_html = nectra_inject_adsense_head($header_html);
echo $header_html;

ob_start();
$sidebar_ad_count = render_sidebar_ads($conn);
$sidebar_ad_html = ob_get_clean();
$has_sidebar = $sidebar_ad_count > 0;
$main_col_class = $has_sidebar ? 'col-lg-8' : 'col-12';

// 7. COMMENT LOGIC
$msg = "";
$error_msg = "";
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $c_name = clean_input($_POST['c_name']);
    $c_email = clean_input($_POST['c_email']);
    $c_msg = clean_input($_POST['c_msg']);
    $post_id = $post['id'];
    
    $contains_link = false;
    $patterns = ["http:", "https:", "www.", ".com", "href="];
    foreach ($patterns as $p) { if (stripos($c_msg, $p) !== false) $contains_link = true; }

    if ($contains_link) {
        $error_msg = "Security: Links are prohibited.";
    } elseif (!empty($_POST['website_url'])) {
        // Bot
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, name, email, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $post_id, $c_name, $c_email, $c_msg);
        if($stmt->execute()) $msg = "Comment submitted.";
        else $error_msg = "System error.";
    }
}
?>

<style>
    .post-hero-header {
        position: relative;
        padding-top: 48px;
        padding-bottom: 48px;
        background: var(--nd-navy, #081426);
        color: #FFFFFF;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-top: 0;
    }
    .post-hero-header .blog-post-title {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        font-size: var(--nd-heading-page);
        font-weight: 700;
        line-height: 1.28;
        letter-spacing: -0.02em;
        max-width: 860px;
        margin: 0 auto 1.25rem;
        color: #FFFFFF !important;
        text-wrap: balance;
    }
    .post-hero-header .blog-post-category {
        font-family: 'Inter', sans-serif;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.08em;
    }
    .post-hero-header .blog-post-meta {
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.7);
    }
    .post-hero-header .blog-post-meta i { color: #38BDF8; opacity: 0.9; }
    main { position: relative; background: #F8FAFC; min-height: 100vh; }
    .post-article-wrap {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(15,23,42,0.04);
    }
    .blog-content {
        font-family: 'Inter', sans-serif;
        font-size: 1.125rem;
        line-height: 1.85;
        color: #334155;
    }
    .blog-content h1, .blog-content h2, .blog-content h3, .blog-content h4 {
        color: #0F172A !important;
        font-weight: 700;
        margin-top: 2.25rem;
        margin-bottom: 0.75rem;
    }
    .blog-content p {
        margin-bottom: 1.5rem;
    }
    .blog-content ul, .blog-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }
    .blog-content li {
        margin-bottom: 0.5rem;
    }
    img.img-fluid {
        border-radius: 12px;
        max-width: 100%;
        height: auto;
    }
    .card-img-top-wrapper { height: 200px; overflow: hidden; position: relative; }
    .card-img-top-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .recent-intel-section { width: 100%; background: #F1F5F9; }
    .recent-intel-section .card-img-top-wrapper {
        height: auto;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        position: relative;
        background: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .recent-intel-section .card-img-top-wrapper img {
        width: 100%;
        height: 100%;
        max-height: none;
        object-fit: contain;
        object-position: center center;
        border: none !important;
        border-radius: 0 !important;
        background: #F1F5F9;
    }
    .recent-intel-section .card:hover img {
        transform: scale(1.02);
    }
    .sidebar-ad-stack { display: flex; flex-direction: column; gap: 1.25rem; width: 100%; }
    .sidebar-ad-column { width: 100%; height: auto; overflow: visible; }
    .nd-post-body-full { width: 100%; max-width: none; }
    .nd-post-related { border-color: #E2E8F0 !important; background: #F8FAFC; border-radius: 12px; padding: 1.25rem 1.5rem !important; }
    .nd-post-faq { width: 100%; }
    .nd-post-related-link { color: #1E293B; font-weight: 500; }
    .nd-post-related-link:hover { color: #2563EB; }
    .nd-post-section-title { color: #0F172A !important; border-color: #2563EB !important; }
    .nd-post-actions { border-color: #E2E8F0 !important; }
    .nd-post-discuss-form { background: #F8FAFC; border-color: #E2E8F0 !important; }
    .sidebar-ad-column h6 { color: #64748B !important; border-color: #E2E8F0 !important; }
    .sidebar-ad-card { position: relative; background: #FFFFFF !important; border-color: #E2E8F0 !important; }
    .sidebar-ad-badge {
        position: absolute; top: 8px; left: 8px; z-index: 2;
        font-size: 10px; opacity: 0.9; letter-spacing: 0.05em;
    }
    .sidebar-ad-img-wrap {
        aspect-ratio: 4 / 3;
        background: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-top: 0;
    }
    .sidebar-ad-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        display: block;
        border: none !important;
        border-radius: 0 !important;
        background: #F1F5F9;
    }
    .sidebar-ad-caption { background: #FFFFFF; color: #0F172A; }
    .sidebar-ad-code { color: #334155; font-size: 0.875rem; overflow: hidden; min-height: 0; background: #F8FAFC; }
    .sidebar-ad-card:hover { border-color: #93C5FD !important; box-shadow: 0 8px 24px rgba(37, 99, 235, 0.12); }
    .ad-container ins.adsbygoogle { display: block; min-height: 90px; }
    .sidebar-ad-code ins.adsbygoogle { display: block; min-height: 90px; }
    .sidebar-ad-code:not(:has(ins.adsbygoogle)) { min-height: 0; padding: 0 !important; }
</style>

<main>
    <header class="post-hero-header text-center">
        <div class="container position-relative">
            <span class="badge border border-secondary text-white-50 mb-4 blog-post-category text-uppercase px-3 py-2">
                <?php echo nectra_display_text($decoded_category); ?>
            </span>
            <h1 class="blog-post-title">
                <?php echo nectra_display_text($decoded_title); ?>
            </h1>
            
            <div class="blog-post-meta d-flex justify-content-center gap-4 flex-wrap">
                <span><i class="far fa-user me-2 text-neon"></i> <?php echo htmlspecialchars(nectra_blog_byline()); ?></span>
                <span><i class="far fa-calendar me-2 text-neon"></i> Published <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                <?php if (!empty($post['updated_at']) && date('Y-m-d', strtotime($post['updated_at'])) !== date('Y-m-d', strtotime($post['created_at']))): ?>
                <span><i class="far fa-clock me-2 text-neon"></i> Updated <?php echo date('M j, Y', strtotime($post['updated_at'])); ?></span>
                <?php endif; ?>
                <span><i class="fas fa-book-open me-2 text-neon"></i> <?php echo max(1, round(str_word_count(strip_tags($post['content'])) / 200)); ?> min read</span>
            </div>

        </div>
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 1px; background: radial-gradient(circle, #00f2ff 0%, transparent 100%); opacity: 0.5;"></div>
    </header>

    <article class="py-5">
        <div class="container">
            <?php
            $header_ad_html = ad_get_html($conn, 'header', 'banner');
            if ($header_ad_html !== ''):
            ?>
            <div class="row justify-content-center"><div class="col-12"><?php echo $header_ad_html; ?></div></div>
            <?php endif; ?>

            <div class="row mt-4 align-items-start g-4">
                <div class="<?php echo $main_col_class; ?>">
                    <?php if(!empty($display_img)): ?>
                    <div class="mb-5 rounded overflow-hidden border border-secondary shadow-lg position-relative" style="z-index: 3;">
                        <img src="<?php echo $display_img; ?>" alt="<?php echo nectra_display_text($decoded_title); ?>" class="img-fluid w-100 d-block">
                    </div>
                    <?php endif; ?>

                    <?php
                        $content_plain = strip_tags($post['content']);
                        render_geo_blocks($decoded_title, $content_plain);
                    ?>

                    <div class="blog-content">
                        <?php
                            $content = nectra_fix_mojibake($post['content']);
                            $ad_html = ad_get_html($conn, 'content', 'banner');

                            if($ad_html !== '') {
                                $paragraphs = explode('</p>', $content);
                                if(count($paragraphs) > 2) {
                                    array_splice($paragraphs, 2, 0, $ad_html);
                                    echo implode('</p>', $paragraphs);
                                } else { echo $content . $ad_html; }
                            } else { echo $content; }
                        ?>
                    </div>
                </div>

                <?php if ($has_sidebar): ?>
                <div class="col-lg-4">
                    <aside class="sidebar-ad-column sticky-lg-top" style="top: 96px;" aria-label="Sponsored content">
                        <h6 class="text-uppercase small mb-3 border-bottom pb-2 text-center fw-semibold">Sponsored</h6>
                        <?php echo $sidebar_ad_html; ?>
                    </aside>
                </div>
                <?php endif; ?>
            </div>

            <div class="row mt-2">
                <div class="col-12 nd-post-body-full">
                    <?php
                        render_geo_summary($decoded_title);

                        $post_faqs = blog_faq_for_post($post);
                        render_faq_section($post_faqs, 'Article FAQ', 'light', 'embedded');

                        render_post_internal_links($conn, $post, $decoded_category);
                    ?>

                    <div class="mt-4 pt-4 border-top nd-post-actions d-flex justify-content-between flex-wrap gap-2">
                        <a href="/insights" class="btn btn-outline-primary btn-sm px-4">
                            <i class="fas fa-arrow-left me-2"></i> Back to Intel
                        </a>
                        <a href="/contact" class="btn btn-nectra-primary btn-sm">Get Free SEO Audit</a>
                    </div>

                    <div class="mt-5 pt-4">
                        <h3 class="h5 mb-4 border-start border-4 ps-3 nd-post-section-title">Discussions</h3>
                        <?php if($msg) echo "<div class='alert alert-success mb-4'>".htmlspecialchars($msg)."</div>"; ?>
                        <?php if($error_msg) echo "<div class='alert alert-danger mb-4'>".htmlspecialchars($error_msg)."</div>"; ?>

                        <div class="p-4 border rounded mb-5 nd-post-discuss-form">
                            <form method="POST">
                                <input type="text" name="website_url" style="display:none;" autocomplete="off">
                                <div class="row g-3">
                                    <div class="col-md-6"><input type="text" name="c_name" class="form-control" placeholder="Name" required></div>
                                    <div class="col-md-6"><input type="email" name="c_email" class="form-control" placeholder="Email (not shown publicly)" required></div>
                                    <div class="col-12"><textarea name="c_msg" class="form-control" rows="3" placeholder="Your comment..." required></textarea></div>
                                    <div class="col-12"><button type="submit" name="submit_comment" class="btn btn-primary w-100">Post comment</button></div>
                                </div>
                            </form>
                        </div>

                        <?php
                        $c_sql = "SELECT * FROM comments WHERE post_id={$post['id']} AND status='approved' ORDER BY created_at DESC";
                        $c_res = $conn->query($c_sql);
                        if($c_res->num_rows > 0) {
                            while($com = $c_res->fetch_assoc()) {
                                echo '<div class="d-flex mb-4 border-bottom pb-3" style="border-color:#E2E8F0!important">';
                                echo '<div class="flex-shrink-0"><div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" ';
                                echo 'style="width:40px;height:40px;background:#2563EB;">'.htmlspecialchars(mb_substr($com['name'], 0, 1)).'</div></div>';
                                echo '<div class="flex-grow-1 ms-3">';
                                echo '<h6 class="text-dark mb-1">'.htmlspecialchars($com['name']).'</h6>';
                                echo '<p class="text-muted small mb-0">'.htmlspecialchars($com['comment']).'</p></div></div>';
                            }
                        } else {
                            echo "<p class='text-muted small mb-0'>No discussions yet. Be the first to comment.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <section class="recent-intel-section mt-5 py-5 border-top">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <h3 class="h5 mb-4 border-start border-4 ps-3 nd-post-section-title">Recent Intel</h3>
                <div class="row g-4">
                    <?php
                    $pid = $post['id'];
                    $listWhere = blog_listable_sql();
                    $sql = "SELECT * FROM blog_posts WHERE id != ? AND {$listWhere} ORDER BY created_at DESC LIMIT 9";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $pid);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $rel_title = nectra_display_text($row['title']);
                            $img = $row['image'];
                            if (strpos($img, 'http') === false) {
                                $img = SITE_URL . '/' . ltrim($img, '/');
                            }
                            if (empty($img)) {
                                $img = SITE_URL . '/assets/images/logo.png';
                            }
                            echo '
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 border bg-white overflow-hidden shadow-sm" style="transition:0.3s;">
                                    <div class="card-img-top-wrapper">
                                        <img src="' . htmlspecialchars($img) . '" class="recent-intel-img" alt="' . $rel_title . '">
                                    </div>
                                    <div class="card-body d-flex flex-column justify-content-center">
                                        <h5 class="card-title text-dark mb-0 h6" style="line-height: 1.5; font-weight: 600;">
                                            <a href="/' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none stretched-link">' . $rel_title . '</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<p class="text-muted small col-12">No other articles published yet.</p>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </article>
</main>

<?php include 'includes/footer.php'; ?>