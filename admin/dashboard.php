<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/blog_orphan.php';
require_once __DIR__ . '/../includes/ads-engine.php';
ads_ensure_schema($conn);
try {
    blog_orphan_ensure_schema($conn);
} catch (Throwable $e) {
    error_log('blog_orphan schema: ' . $e->getMessage());
}

/** Auto Indexing lives in Growth Engine (stable init + error handling). */
$pageReq = isset($_GET['page']) ? (string)$_GET['page'] : 'home';
if ($pageReq === 'seo') {
    if (is_file(__DIR__ . '/includes/admin-growth.php')) {
        require_once __DIR__ . '/includes/admin-growth.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['growth_action'])) {
            $seoMsg = admin_handle_growth_post('seo');
            if ($seoMsg !== null && $seoMsg !== '') {
                $_SESSION['ge_flash'] = ['type' => 'success', 'message' => $seoMsg];
            }
        }
    }
    header('Location: growth/indexing.php');
    exit;
}

$growthMsg = null;
$growthStats = [
    'ready' => false,
    'services' => 0,
    'cities' => 0,
    'industries' => 0,
    'keywords' => 0,
    'pages' => 0,
    'indexed' => 0,
    'pending_index' => 0,
    'submitted_index' => 0,
    'failed_index' => 0,
    'queue_pending' => 0,
];
if (is_file(__DIR__ . '/includes/admin-growth.php')) {
    try {
        require_once __DIR__ . '/includes/admin-growth.php';
        $pageForPost = $_GET['page'] ?? 'home';
        $growthMsg = admin_handle_growth_post($pageForPost);
        $growthStats = admin_growth_stats();
    } catch (Throwable $e) {
        error_log('admin dashboard growth: ' . $e->getMessage());
        $growthStats['error'] = $e->getMessage();
    }
}

// --- HELPER: IMAGE UPLOAD FUNCTION ---
function upload_ad_image($file) {
    $target_dir = "../assets/uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

    if ($file["size"] > 512000) return ["error" => "Error: File too large. Max 500KB."];

    $allowed_types = ['image/webp', 'image/svg+xml', 'image/png', 'image/jpeg'];
    if (!in_array(mime_content_type($file["tmp_name"]), $allowed_types)) return ["error" => "Error: Only WEBP, SVG, PNG, JPG allowed."];

    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $new_name = uniqid("ad_", true) . "." . $ext;
    $target_file = $target_dir . $new_name;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => "assets/uploads/" . $new_name];
    }
    return ["error" => "Upload failed."];
}

// --- GLOBAL ACTIONS ---
if (isset($_GET['delete_post'])) {
    $delId = intval($_GET['delete_post']);
    $res = $conn->query("SELECT slug FROM blog_posts WHERE id={$delId} LIMIT 1");
    if ($res && ($row = $res->fetch_assoc()) && !empty($row['slug'])) {
        require_once __DIR__ . '/../includes/blog_static.php';
        blog_static_delete((string)$row['slug']);
    }
    $conn->query("DELETE FROM blog_posts WHERE id={$delId}");
    header("Location: dashboard.php?page=blog&msg=deleted"); exit;
}
if (isset($_GET['toggle_orphan'])) {
    $oid = (int)$_GET['toggle_orphan'];
    if ($oid > 0) {
        $conn->query("UPDATE blog_posts SET is_orphan = IF(COALESCE(is_orphan, 0) = 1, 0, 1) WHERE id = {$oid}");
        $res = $conn->query("SELECT slug, created_at FROM blog_posts WHERE id = {$oid} LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            blog_signal_post_indexed($row['slug'], $row['created_at']);
        }
    }
    header("Location: dashboard.php?page=blog&msg=orphan_toggled"); exit;
}
if (isset($_GET['index_post'])) {
    $oid = (int)$_GET['index_post'];
    if ($oid > 0) {
        $res = $conn->query("SELECT slug, created_at FROM blog_posts WHERE id = {$oid} LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            blog_signal_post_indexed($row['slug'], $row['created_at']);
        }
    }
    header("Location: dashboard.php?page=blog&msg=index_sent"); exit;
}
if (isset($_GET['rebuild_blog_static'])) {
    require_once __DIR__ . '/../includes/blog_static.php';
    $built = blog_static_rebuild_all($conn);
    header("Location: dashboard.php?page=blog&msg=static_rebuilt&count={$built}"); exit;
}
if (isset($_GET['fix_blog_encoding'])) {
    require_once __DIR__ . '/../includes/blog-encoding.php';
    $fixed = blog_encoding_repair_all($conn);
    require_once __DIR__ . '/../includes/blog_static.php';
    blog_static_rebuild_all($conn);
    header("Location: dashboard.php?page=blog&msg=encoding_fixed&count={$fixed}"); exit;
}
if (isset($_GET['del_ad'])) {
    $conn->query("DELETE FROM ads WHERE id=".intval($_GET['del_ad']));
    header("Location: dashboard.php?page=ads&msg=ad_deleted"); exit;
}
if (isset($_GET['del_comment'])) {
    $conn->query("DELETE FROM comments WHERE id=".intval($_GET['del_comment']));
    header("Location: dashboard.php?page=comments&msg=com_deleted"); exit;
}
if (isset($_GET['approve_comment'])) {
    $conn->query("UPDATE comments SET status='approved' WHERE id=".intval($_GET['approve_comment']));
    header("Location: dashboard.php?page=comments&msg=approved"); exit;
}
if (isset($_GET['del_career'])) {
    $conn->query("DELETE FROM careers WHERE id=".intval($_GET['del_career']));
    header("Location: dashboard.php?page=careers&msg=job_deleted"); exit;
}

// --- NEW HIRE REQUESTS ACTIONS ---
if (isset($_GET['del_hire'])) {
    $conn->query("DELETE FROM hire_requests WHERE id=".intval($_GET['del_hire']));
    header("Location: dashboard.php?page=hire_requests&msg=deleted"); exit;
}
if (isset($_GET['del_lead'])) {
    $lid = (int)$_GET['del_lead'];
    if ($lid > 0) {
        $conn->query("DELETE FROM leads WHERE id={$lid}");
    }
    header("Location: dashboard.php?page=leads&msg=deleted"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete_leads'])) {
    $ids = array_values(array_filter(array_map('intval', (array)($_POST['lead_ids'] ?? []))));
    if ($ids) {
        $conn->query('DELETE FROM leads WHERE id IN (' . implode(',', $ids) . ')');
    }
    header('Location: dashboard.php?page=leads&msg=bulk_deleted'); exit;
}
if (isset($_POST['update_hire_status'])) {
    $req_id = intval($_POST['request_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE hire_requests SET status='$new_status' WHERE id=$req_id");
    header("Location: dashboard.php?page=hire_requests&msg=status_updated"); exit;
}

$page = isset($_GET['page']) ? (string)$_GET['page'] : 'home';
$pageTitles = [
    'home' => 'Dashboard Overview',
    'cities' => 'City Manager',
    'blog' => 'Blog Posts',
    'ads' => 'Ad Manager',
    'comments' => 'Comments',
    'careers' => 'Careers',
    'hire_requests' => 'Hire Requests',
    'leads' => 'Contact Leads',
];
if (!isset($pageTitles[$page])) {
    header('Location: dashboard.php?page=home');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
require_once __DIR__ . '/includes/admin-dashboard-home.php';

$dashboardAlerts = [];
if ($growthMsg) {
    $dashboardAlerts[] = ['type' => 'success', 'message' => $growthMsg, 'dismiss' => true];
}
if (!empty($growthStats['error'])) {
    $dashboardAlerts[] = ['type' => 'warning', 'message' => 'Growth Engine: ' . $growthStats['error']];
}

admin_layout_start($pageTitles[$page], $page, [
    'alerts' => $dashboardAlerts,
    'growth_warning' => empty($growthStats['ready']),
]);
?>
    <?php

    // ==========================================
    // HOME — Unified Dashboard Overview
    // ==========================================
    if ($page == 'home') {
        admin_render_dashboard_home($conn, $growthStats);
    }

    // ==========================================
    // CITIES MANAGEMENT
    // ==========================================
    elseif ($page == 'cities') {
        $cities = admin_growth_cities();
        echo '<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">';
        echo '<p class="text-muted mb-0">Manage cities for programmatic landing pages.</p>';
        echo '<div><a href="growth/cities.php?action=import" class="btn btn-outline-secondary me-2"><i class="fas fa-file-import"></i> Bulk Import</a>';
        echo '<a href="growth/cities.php?action=add" class="btn btn-ge-primary"><i class="fas fa-plus"></i> Advanced Add</a></div></div>';

        echo '<div class="ge-card mb-4"><h5 class="ge-section-head">Add New City</h5><form method="POST"><input type="hidden" name="growth_action" value="add_city">';
        echo '<div class="row g-3"><div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="City Name *" required></div>';
        echo '<div class="col-md-2"><input type="text" name="slug" class="form-control" placeholder="Slug (auto)"></div>';
        echo '<div class="col-md-2"><input type="text" name="state" class="form-control" placeholder="State"></div>';
        echo '<div class="col-md-2"><input type="text" name="country" class="form-control" value="India"></div>';
        echo '<div class="col-md-2"><input type="number" name="population" class="form-control" placeholder="Population"></div>';
        echo '<div class="col-md-1"><button type="submit" class="btn btn-success w-100">Add</button></div></div></form></div>';

        echo '<div class="ge-card"><div class="ge-table-wrap"><table class="table ge-table table-hover table-sm mb-0"><thead><tr><th>City</th><th>State</th><th>Population</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        foreach ($cities as $c) {
            echo '<tr><td><strong>'.htmlspecialchars($c['name']).'</strong><br><code class="small">'.htmlspecialchars($c['slug']).'</code></td>';
            echo '<td>'.htmlspecialchars($c['state']).'</td><td>'.number_format($c['population']).'</td><td>'.$c['status'].'</td><td>';
            echo '<a href="growth/cities.php?action=edit&id='.$c['id'].'" class="btn btn-sm btn-outline-warning me-1"><i class="fas fa-edit"></i></a>';
            echo '<form method="POST" class="d-inline" onsubmit="return confirm(\'Delete city?\')"><input type="hidden" name="growth_action" value="delete_city"><input type="hidden" name="city_id" value="'.$c['id'].'"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>';
            echo '</td></tr>';
        }
        if (empty($cities)) echo '<tr><td colspan="5" class="text-center text-muted py-4">No cities yet. Add above or run migration.</td></tr>';
        echo '</tbody></table></div><p class="small text-muted mt-2 mb-0">'.count($cities).' cities · Used for programmatic landing pages</p></div>';

        if ($growthStats['ready']) {
            echo '<div class="ge-card mt-4 ge-card--muted">';
            echo '<p class="text-muted small mb-2">Export city hub and landing URLs for Search Console bulk indexing.</p>';
            echo '<a href="export-urls.php?type=cities" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-download me-1"></i>City URLs</a>';
            echo '<a href="export-urls.php?type=landing" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-download me-1"></i>Landing URLs</a>';
            echo '<a href="export-urls.php?type=all" class="btn btn-sm btn-ge-primary"><i class="fas fa-download me-1"></i>All URLs</a>';
            echo '</div>';
        }
    }

    // ==========================================
    // 1. BLOG MANAGEMENT
    // ==========================================
    elseif ($page == 'blog') {
        if (!defined('SITE_URL')) {
            require_once __DIR__ . '/../includes/config.php';
        }
        $siteBase = rtrim(SITE_URL, '/');

        echo '<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <p class="text-muted mb-0">Manage Insights posts, visibility, and indexing.</p>
                <div class="d-flex gap-2 align-items-center">
                <div class="dropdown">
                  <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Maintenance</button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?page=blog&rebuild_blog_static=1">Rebuild crawler snapshots</a></li>
                    <li><a class="dropdown-item" href="?page=blog&fix_blog_encoding=1" onclick="return confirm(\'Repair UTF-8 encoding in all posts?\');">Fix text encoding</a></li>
                  </ul>
                </div>
                <a href="create_post.php" class="btn btn-ge-primary"><i class="fas fa-plus"></i> New post</a>
                </div>
              </div>';
        if(isset($_GET['msg']) && $_GET['msg'] == 'deleted') echo "<div class='alert alert-danger'>Post deleted.</div>";
        if(isset($_GET['msg']) && $_GET['msg'] == 'orphan_toggled') echo "<div class='alert alert-info'>Visibility updated. Orphan posts stay live + indexed but hidden from site listings.</div>";
        if(isset($_GET['msg']) && $_GET['msg'] == 'index_sent') echo "<div class='alert alert-success'>URL sent to Bing via IndexNow + Bing API.</div>";
        if(isset($_GET['msg']) && $_GET['msg'] == 'static_rebuilt') echo "<div class='alert alert-success'>Crawler snapshots rebuilt for " . intval($_GET['count'] ?? 0) . " blog post(s).</div>";
        if(isset($_GET['msg']) && $_GET['msg'] == 'encoding_fixed') echo "<div class='alert alert-success'>Encoding repaired in " . intval($_GET['count'] ?? 0) . " blog post(s). Static snapshots rebuilt.</div>";

        $sql = "SELECT * FROM blog_posts ORDER BY created_at DESC";
        $result = $conn->query($sql);

        echo '<div class="ge-card"><div class="ge-table-wrap"><table class="table ge-table table-hover align-middle mb-0">
                <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Visibility</th><th>Post URL</th><th>Actions</th></tr></thead><tbody>';
        while($row = $result->fetch_assoc()) {
            $isOrphan = blog_is_orphan($row);
            $visBadge = $isOrphan
                ? '<span class="badge bg-warning text-dark"><i class="fas fa-unlink me-1"></i>Orphan</span>'
                : '<span class="badge bg-success"><i class="fas fa-list me-1"></i>Listed</span>';
            $toggleTitle = $isOrphan ? 'Show on website listings' : 'Make orphan (direct link only)';
            $toggleIcon = $isOrphan ? 'fa-eye' : 'fa-eye-slash';
            $postUrl = $siteBase . '/' . ltrim((string)($row['slug'] ?? ''), '/');
            $postUrlAttr = htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8');
            echo "<tr>
                    <td class='text-white-50 small'>".date('M d', strtotime($row['created_at']))."</td>
                    <td class='fw-bold text-white'><a href='{$postUrlAttr}' target='_blank' rel='noopener' class='text-white text-decoration-none'>" . nectra_display_text($row['title']) . " <i class='fas fa-external-link-alt small text-info'></i></a></td>
                    <td><span class='badge bg-secondary'>" . nectra_display_text($row['category']) . "</span></td>
                    <td>{$visBadge}</td>
                    <td class='small' style='max-width:220px;'>
                        <div class='d-flex align-items-center gap-1'>
                            <a href='{$postUrlAttr}' target='_blank' rel='noopener' class='text-info text-truncate text-decoration-none' title='{$postUrlAttr}'>{$postUrlAttr}</a>
                            <button type='button' class='btn btn-sm btn-outline-secondary flex-shrink-0 blog-copy-url' data-url='{$postUrlAttr}' title='Copy URL for Google Search Console'><i class='fas fa-copy'></i></button>
                        </div>
                    </td>
                    <td>
                        <a href='?page=blog&index_post={$row['id']}' class='btn btn-sm btn-outline-success me-2' title='Send to Bing (IndexNow + API)'><i class='fab fa-microsoft'></i></a>
                        <a href='?page=blog&toggle_orphan={$row['id']}' class='btn btn-sm btn-outline-info me-2' title='" . htmlspecialchars($toggleTitle) . "' onclick='return confirm(\"" . ($isOrphan ? 'Show this post on Insights and site listings?' : 'Make orphan? Post stays live + indexed but hidden from all listings.') . "\")'><i class='fas {$toggleIcon}'></i></a>
                        <a href='edit_post.php?id={$row['id']}' class='btn btn-sm btn-outline-warning me-2'><i class='fas fa-pen'></i></a>
                        <a href='?page=blog&delete_post={$row['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Delete this post?\")'><i class='fas fa-trash'></i></a>
                    </td>
                  </tr>";
        }
        echo '</tbody></table></div></div>';
        echo "<script>
        document.querySelectorAll('.blog-copy-url').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var url = btn.getAttribute('data-url') || '';
                if (!url) return;
                var done = function() {
                    btn.classList.add('btn-success');
                    btn.classList.remove('btn-outline-secondary');
                    btn.title = 'Copied!';
                    setTimeout(function() {
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                        btn.title = 'Copy URL for Google Search Console';
                    }, 1500);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(done).catch(function() {
                        window.prompt('Copy this URL:', url);
                    });
                } else {
                    window.prompt('Copy this URL:', url);
                }
            });
        });
        </script>";
    } 

    // ==========================================
    // 2. ADS MANAGEMENT (With Image Upload)
    // ==========================================
    elseif ($page == 'ads') {
        ads_ensure_schema($conn);

        if(isset($_POST['save_ad'])){
            $title = clean_input($_POST['title']);
            $typeRaw = (string)($_POST['type'] ?? 'image');
            $type = in_array($typeRaw, ['code', 'text'], true) ? $typeRaw : 'image';
            $placement = in_array($_POST['placement'] ?? '', ['header', 'sidebar', 'content'], true)
                ? $_POST['placement']
                : 'sidebar';
            $code = $conn->real_escape_string(trim((string)($_POST['ad_code'] ?? '')));
            $link = clean_input($_POST['link'] ?? '');
            $img_path = "";

            if ($type === 'image' && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload = upload_ad_image($_FILES['image']);
                if (isset($upload['error'])) {
                    echo "<div class='alert alert-danger'>{$upload['error']}</div>";
                } else {
                    $img_path = $upload['success'];
                }
            }

            $canSave = !isset($upload) || isset($upload['success']);
            if ($canSave && in_array($type, ['code', 'text'], true) && $code === '') {
                echo "<div class='alert alert-danger'>Paste your Google AdSense snippet in the code box.</div>";
                $canSave = false;
            }
            if ($canSave && $type === 'image' && $img_path === '') {
                echo "<div class='alert alert-danger'>Upload an image for image ads.</div>";
                $canSave = false;
            }

            if ($canSave) {
                $conn->query("INSERT INTO ads (title, type, placement, image_path, ad_code, link, status) VALUES ('$title', '$type', '$placement', '$img_path', '$code', '$link', 'active')");
                echo "<div class='alert alert-success'>Ad Campaign Launched!</div>";
            }
        }

        echo "<div class='ge-card mb-4'>
            <h5 class='ge-section-head'>Create New Ad Unit</h5>
            <p class='text-muted small mb-3'>Blog detail pages show <strong>every valid active ad</strong> (sidebar, in-article, mid + footer bands). Use separate AdSense units for display vs text/in-feed (<code>data-ad-format=&quot;autorelaxed&quot;</code> etc.) — each unique <code>data-ad-slot</code> counts as its own unit. Paste full <code>&lt;ins class=\"adsbygoogle\"&gt;</code>, not the loader script alone.</p>
            <form method='POST' enctype='multipart/form-data'>
                <div class='row g-3'>
                    <div class='col-md-4'><input type='text' name='title' class='form-control' placeholder='Ad Name' required></div>
                    <div class='col-md-2'><select name='placement' class='form-select'><option value='header'>Top Banner</option><option value='sidebar'>Sidebar</option><option value='content'>In-Article</option></select></div>
                    <div class='col-md-2'><select name='type' class='form-select' onchange='toggleAdType(this.value)'><option value='image'>Custom Image</option><option value='code'>Display AdSense</option><option value='text'>Text / In-feed AdSense</option></select></div>
                    <div class='col-md-4 ad-image-group'><input type='file' name='image' class='form-control mb-2'><input type='text' name='link' class='form-control' placeholder='Link URL'></div>
                    <div class='col-12 ad-code-group' style='display:none;'><textarea name='ad_code' class='form-control' rows='3' placeholder='Paste Google Adsense Code here'></textarea></div>
                    <div class='col-12'><button type='submit' name='save_ad' class='btn btn-ge-primary w-100'>Create Ad</button></div>
                </div>
            </form>
        </div>";

        $res = $conn->query("SELECT * FROM ads ORDER BY id DESC");
        echo "<div class='ge-card'><div class='ge-table-wrap'><table class='table ge-table mb-0'><thead><tr><th>Preview</th><th>Name</th><th>Type</th><th>Placement</th><th>Live</th><th>Action</th></tr></thead><tbody>";
        while($row = $res->fetch_assoc()){
            $preview = ($row['type'] == 'image' && !empty($row['image_path'])) ? "<img src='../{$row['image_path']}' width='50' height='50' class='ad-preview'>" : "<i class='fas fa-code fa-lg'></i>";
            $ok = ad_has_displayable_content($row);
            $liveBadge = $ok
                ? "<span class='badge bg-success'>Shows on site</span>"
                : "<span class='badge bg-warning text-dark' title='Fix image path or paste full AdSense &lt;ins&gt; unit'>Hidden (invalid)</span>";
            echo "<tr><td>$preview</td><td>" . nectra_display_text($row['title']) . "</td><td><span class='badge bg-secondary'>{$row['type']}</span></td><td><span class='badge bg-info'>{$row['placement']}</span></td><td>$liveBadge</td><td><a href='?page=ads&del_ad={$row['id']}' class='text-danger' onclick='return confirm(\"Delete Ad?\")'>Delete</a></td></tr>";
        }
        echo "</tbody></table></div></div>";
        echo "<script>
        function toggleAdType(val) {
            var imgGroup = document.querySelector('.ad-image-group');
            var codeGroup = document.querySelector('.ad-code-group');
            if (!imgGroup || !codeGroup) return;
            if (val === 'code' || val === 'text') {
                imgGroup.style.display = 'none';
                codeGroup.style.display = 'block';
            } else {
                imgGroup.style.display = 'block';
                codeGroup.style.display = 'none';
            }
        }
        </script>";
    }

    // ==========================================
    // 3. COMMENTS MANAGEMENT
    // ==========================================
    elseif ($page == 'comments') {
        $res = $conn->query("SELECT * FROM comments ORDER BY created_at DESC");
        echo "<div class='ge-card'><div class='ge-table-wrap'><table class='table ge-table mb-0'><thead><tr><th>User</th><th>Comment</th><th>Status</th><th>Action</th></tr></thead><tbody>";
        while($row = $res->fetch_assoc()){
            $status = $row['status'] == 'pending' ? "<a href='?page=comments&approve_comment={$row['id']}' class='btn btn-sm btn-success'>Approve</a>" : "<span class='text-success'>Live</span>";
            echo "<tr><td>{$row['name']}</td><td><small>{$row['comment']}</small></td><td>$status</td><td><a href='?page=comments&del_comment={$row['id']}' class='text-danger' onclick='return confirm(\"Delete Comment?\")'><i class='fas fa-trash'></i></a></td></tr>";
        }
        echo "</tbody></table></div></div>";
    }

    // ==========================================
    // 4. CAREERS & APPLICATIONS
    // ==========================================
    elseif ($page == 'careers') {
        echo '<ul class="nav nav-tabs mb-4 border-secondary">
                <li class="nav-item"><a class="nav-link active bg-dark text-white border-secondary" href="#">Active Jobs</a></li>
                <li class="nav-item"><a class="nav-link text-info" href="#applications">Applications</a></li>
              </ul>';

        echo '<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <p class="text-muted mb-0">Manage open positions and applications.</p>
                <a href="create_job.php" class="btn btn-ge-primary"><i class="fas fa-plus"></i> New Position</a>
              </div>';

        $res = $conn->query("SELECT * FROM careers ORDER BY created_at DESC");
        echo "<div class='ge-card mb-4'><div class='ge-table-wrap'><table class='table ge-table table-hover mb-0'><thead><tr><th>Position</th><th>Stack</th><th>Status</th><th>Action</th></tr></thead><tbody>";
        while($row = $res->fetch_assoc()){
            echo "<tr>
                <td>{$row['position']}</td>
                <td>{$row['stack']}</td>
                <td><span class='badge bg-success'>{$row['status']}</span></td>
                <td><a href='?page=careers&del_career={$row['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Close Position?\")'>Close</a></td>
            </tr>";
        }
        echo "</tbody></table></div></div>";

        echo '<h5 class="mt-4 mb-3" id="applications"><i class="fas fa-file-pdf text-warning me-2"></i>Received Applications</h5>';
        $app_res = $conn->query("SELECT a.*, c.position FROM applications a JOIN careers c ON a.job_id = c.id ORDER BY a.created_at DESC");
        
        echo "<div class='ge-card'><div class='ge-table-wrap'><table class='table ge-table mb-0'>
                <thead><tr><th>Candidate</th><th>Applying For</th><th>Cover Letter</th><th>Resume</th></tr></thead><tbody>";
        
        if($app_res->num_rows > 0){
            while($app = $app_res->fetch_assoc()){
                echo "<tr>
                    <td>
                        <strong class='text-white'>{$app['name']}</strong><br>
                        <small class='text-info'>{$app['email']}</small><br>
                        <small class='text-muted'>{$app['created_at']}</small>
                    </td>
                    <td>{$app['position']}</td>
                    <td><div style='max-height:80px; overflow-y:auto; font-size:0.85rem;' class='text-white-50'>{$app['cover_letter']}</div></td>
                    <td>
                        <a href='../{$app['resume_path']}' target='_blank' class='btn btn-sm btn-success'>
                            <i class='fas fa-download'></i> Download PDF
                        </a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center text-white-50'>No applications received yet.</td></tr>";
        }
        echo "</tbody></table></div></div>";
    }

    // ==========================================
    // 5. HIRE REQUESTS MANAGEMENT
    // ==========================================
    elseif ($page == 'hire_requests') {
        if(isset($_GET['msg'])) {
            if($_GET['msg'] == 'deleted') echo "<div class='alert alert-danger'>Request deleted.</div>";
            if($_GET['msg'] == 'status_updated') echo "<div class='alert alert-success'>Status updated successfully.</div>";
        }

        $sql = "SELECT * FROM hire_requests ORDER BY created_at DESC";
        $result = $conn->query($sql);

        echo '<div class="ge-card"><div class="ge-table-wrap"><table class="table ge-table ge-table-inbox table-hover align-top mb-0">
                <thead><tr>
                    <th class="col-date">Date</th>
                    <th class="col-contact">Client Info</th>
                    <th class="col-service">Service & Budget</th>
                    <th class="col-status">Status</th>
                    <th class="col-actions text-end">Actions</th>
                </tr></thead><tbody>';

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $hid = (int)$row['id'];
                $status = (string)($row['status'] ?? 'new');
                $statusClass = $status === 'closed' ? 'ge-badge-failed' : ($status === 'contacted' ? 'ge-badge-pending' : 'ge-badge-indexed');
                $statusLabel = ucfirst($status);
                echo "<tr>
                        <td class='small text-nowrap ge-cell-date'><strong>" . date('M d, Y', strtotime($row['created_at'])) . "</strong></td>
                        <td class='ge-cell-contact'>
                            <strong>" . htmlspecialchars($row['full_name']) . "</strong><br>
                            <a href='mailto:" . htmlspecialchars($row['email']) . "' class='small ge-link-mail text-decoration-none'>" . htmlspecialchars($row['email']) . "</a><br>
                            <a href='https://wa.me/" . preg_replace('/[^0-9]/', '', $row['phone']) . "' target='_blank' rel='noopener' class='small ge-link-wa text-decoration-none'><i class='fab fa-whatsapp'></i> " . htmlspecialchars($row['phone']) . "</a>
                        </td>
                        <td class='ge-cell-service'>
                            <span class='badge bg-secondary ge-service-badge'>" . htmlspecialchars($row['service_needed']) . "</span>
                            <div class='small text-muted mt-1'>Budget: " . htmlspecialchars($row['budget']) . "</div>
                        </td>
                        <td class='ge-cell-status'>
                            <span class='ge-badge {$statusClass} mb-2 d-inline-block'>{$statusLabel}</span>
                            <form method='POST' class='ge-status-form'>
                                <input type='hidden' name='request_id' value='{$hid}'>
                                <input type='hidden' name='update_hire_status' value='1'>
                                <label class='visually-hidden' for='hire-status-{$hid}'>Update status</label>
                                <select id='hire-status-{$hid}' name='status' class='form-select form-select-sm ge-status-select' onchange='this.form.submit()'>
                                    <option value='new' " . ($status === 'new' ? 'selected' : '') . ">New</option>
                                    <option value='contacted' " . ($status === 'contacted' ? 'selected' : '') . ">Contacted</option>
                                    <option value='closed' " . ($status === 'closed' ? 'selected' : '') . ">Closed</option>
                                </select>
                            </form>
                        </td>
                        <td class='text-end ge-cell-actions'>
                            <div class='ge-action-group'>
                                <button type='button' class='btn btn-sm btn-outline-secondary' onclick='toggleDetails({$hid})' title='View project details'><i class='fas fa-eye'></i><span class='d-none d-xl-inline ms-1'>View</span></button>
                                <a href='?page=hire_requests&del_hire={$hid}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Delete this request?\")' title='Delete'><i class='fas fa-trash'></i></a>
                            </div>
                        </td>
                      </tr>
                      <tr id='details-{$hid}' class='ge-detail-row' style='display:none;'>
                          <td colspan='5' class='p-4'>
                              <h6 class='mb-2'>Project details</h6>
                              <p class='mb-0 text-muted small ge-detail-body'>" . nl2br(htmlspecialchars((string)$row['project_details'])) . "</p>
                          </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No hire requests found.</td></tr>";
        }
        echo '</tbody></table></div></div>';
    }

    // ==========================================
    // LEADS
    // ==========================================
    elseif ($page == 'leads') {
        $filter = isset($_GET['filter']) ? (string)$_GET['filter'] : 'all';
        if (!in_array($filter, ['all', 'clean', 'spam'], true)) {
            $filter = 'all';
        }
        $search = trim((string)($_GET['q'] ?? ''));

        $statsRow = $conn->query("SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN is_spam = 0 THEN 1 ELSE 0 END) AS clean_count,
            SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) AS spam_count,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count
            FROM leads")->fetch_assoc() ?: [];

        if (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'deleted') {
                echo "<div class='alert alert-success alert-dismissible fade show'>Lead deleted.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            }
            if ($_GET['msg'] === 'bulk_deleted') {
                echo "<div class='alert alert-success alert-dismissible fade show'>Selected leads deleted.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            }
        }

        echo '<div class="row g-3 mb-4">';
        echo '<div class="col-6 col-md-3"><div class="ge-stat-card"><div class="ge-stat-value">' . number_format((int)($statsRow['total'] ?? 0)) . '</div><div class="ge-stat-label">Total Leads</div></div></div>';
        echo '<div class="col-6 col-md-3"><div class="ge-stat-card"><div class="ge-stat-value text-success">' . number_format((int)($statsRow['clean_count'] ?? 0)) . '</div><div class="ge-stat-label">Clean</div></div></div>';
        echo '<div class="col-6 col-md-3"><div class="ge-stat-card"><div class="ge-stat-value text-danger">' . number_format((int)($statsRow['spam_count'] ?? 0)) . '</div><div class="ge-stat-label">Spam / Bot</div></div></div>';
        echo '<div class="col-6 col-md-3"><div class="ge-stat-card"><div class="ge-stat-value text-info">' . number_format((int)($statsRow['today_count'] ?? 0)) . '</div><div class="ge-stat-label">Today</div></div></div>';
        echo '</div>';

        echo '<div class="ge-card mb-4"><form method="GET" class="row g-3 align-items-end">';
        echo '<input type="hidden" name="page" value="leads">';
        echo '<div class="col-md-5"><label class="form-label small text-muted">Search</label>';
        echo '<input type="search" name="q" value="' . htmlspecialchars($search) . '" class="form-control form-control-sm" placeholder="Name, email, service, message…"></div>';
        echo '<div class="col-md-3"><label class="form-label small text-muted">Filter</label>';
        echo '<select name="filter" class="form-select form-select-sm">';
        foreach (['all' => 'All leads', 'clean' => 'Clean only', 'spam' => 'Spam only'] as $val => $label) {
            echo '<option value="' . $val . '"' . ($filter === $val ? ' selected' : '') . '>' . $label . '</option>';
        }
        echo '</select></div>';
        echo '<div class="col-md-4 d-flex gap-2"><button type="submit" class="btn btn-sm btn-ge-primary">Apply</button>';
        echo '<a href="dashboard.php?page=leads" class="btn btn-sm btn-outline-secondary">Reset</a></div>';
        echo '</form></div>';

        $where = ['1=1'];
        if ($filter === 'clean') {
            $where[] = 'is_spam = 0';
        } elseif ($filter === 'spam') {
            $where[] = 'is_spam = 1';
        }
        if ($search !== '') {
            $esc = $conn->real_escape_string($search);
            $where[] = "(name LIKE '%{$esc}%' OR email LIKE '%{$esc}%' OR service LIKE '%{$esc}%' OR message LIKE '%{$esc}%' OR budget LIKE '%{$esc}%')";
        }
        $whereSql = implode(' AND ', $where);

        $result = $conn->query("SELECT * FROM leads WHERE {$whereSql} ORDER BY created_at DESC LIMIT 200");
        $leadCount = $result ? $result->num_rows : 0;

        echo '<form method="POST" id="leadsBulkForm">';
        echo '<input type="hidden" name="bulk_delete_leads" value="1">';
        echo '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">';
        echo '<p class="text-muted small mb-0">Showing <strong>' . number_format($leadCount) . '</strong> lead(s)' . ($search !== '' || $filter !== 'all' ? ' (filtered)' : '') . '</p>';
        echo '<button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete all selected leads?\')"><i class="fas fa-trash"></i> Delete Selected</button>';
        echo '</div>';

        echo '<div class="ge-card"><div class="ge-table-wrap"><table class="table ge-table ge-table-inbox table-hover align-top mb-0">';
        echo '<thead><tr>';
        echo '<th class="col-check"><input type="checkbox" class="form-check-input" id="leadSelectAll" title="Select all"></th>';
        echo '<th class="col-date">Date</th><th class="col-status">Status</th><th class="col-contact">Contact</th>';
        echo '<th class="col-service">Service & Budget</th><th class="col-message">Message</th><th class="col-actions text-end">Actions</th>';
        echo '</tr></thead><tbody>';

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id = (int)$row['id'];
                $name = htmlspecialchars((string)$row['name']);
                $email = htmlspecialchars((string)$row['email']);
                $service = htmlspecialchars((string)$row['service']);
                $budget = htmlspecialchars((string)($row['budget'] ?? ''));
                $message = htmlspecialchars((string)$row['message']);
                $ip = htmlspecialchars((string)($row['ip_address'] ?? ''));
                $date = !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : '—';
                $time = !empty($row['created_at']) ? date('g:i A', strtotime($row['created_at'])) : '';
                $isSpam = !empty($row['is_spam']);
                $statusBadge = $isSpam
                    ? '<span class="ge-badge ge-badge-failed">Spam</span>'
                    : '<span class="ge-badge ge-badge-indexed">Clean</span>';

                $msgPreview = mb_strlen(strip_tags($row['message'])) > 220
                    ? mb_substr(strip_tags($row['message']), 0, 220) . '…'
                    : $message;

                echo "<tr>
                    <td class='align-middle'><input type='checkbox' class='form-check-input lead-row-check' name='lead_ids[]' value='{$id}'></td>
                    <td class='small text-nowrap ge-cell-date'><strong>{$date}</strong>" . ($time ? "<br><span class='text-muted'>{$time}</span>" : '') . "</td>
                    <td class='align-middle'>{$statusBadge}</td>
                    <td class='ge-cell-contact'>
                        <strong>{$name}</strong><br>
                        <a href='mailto:{$email}' class='small ge-link-mail text-decoration-none'>{$email}</a><br>
                        <span class='small ge-ip-label'>IP {$ip}</span>
                    </td>
                    <td class='ge-cell-service'>
                        <span class='badge bg-secondary ge-service-badge'>{$service}</span>" .
                        ($budget !== '' ? "<div class='small text-muted mt-1'>Budget: {$budget}</div>" : '') . "
                    </td>
                    <td class='ge-cell-message'>
                        <div class='lead-msg-preview text-muted small'>" . nl2br($msgPreview) . "</div>
                    </td>
                    <td class='text-end ge-cell-actions'>
                        <div class='ge-action-group'>
                            <button type='button' class='btn btn-sm btn-outline-secondary' onclick='toggleDetails({$id})' title='View full message'><i class='fas fa-eye'></i></button>
                            <a href='mailto:{$email}?subject=" . rawurlencode('Re: Your inquiry — Nectra Digital') . "' class='btn btn-sm btn-outline-secondary' title='Reply'><i class='fas fa-reply'></i></a>
                            <a href='?page=leads&del_lead={$id}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Delete this lead?\")' title='Delete'><i class='fas fa-trash'></i></a>
                        </div>
                    </td>
                </tr>
                <tr id='details-{$id}' class='ge-detail-row' style='display:none;'>
                    <td colspan='7' class='p-4'>
                        <h6 class='mb-2'>Full message</h6>
                        <p class='mb-3 text-muted small ge-detail-body' style='white-space:pre-wrap;'>{$message}</p>
                        <div class='d-flex flex-wrap gap-2'>
                            <a href='mailto:{$email}' class='btn btn-sm btn-ge-primary'><i class='fas fa-envelope me-1'></i> Email {$name}</a>
                            <a href='?page=leads&del_lead={$id}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Delete this lead?\")'><i class='fas fa-trash me-1'></i> Delete</a>
                        </div>
                    </td>
                </tr>";
            }
        } else {
            echo '<tr><td colspan="7" class="text-center py-5 text-muted">No contact leads found.</td></tr>';
        }

        echo '</tbody></table></div></div></form>';
        echo "<script>
        (function() {
            var selectAll = document.getElementById('leadSelectAll');
            if (!selectAll) return;
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.lead-row-check').forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
            });
        })();
        </script>";
    }
    ?>
<?php admin_layout_end(); ?>