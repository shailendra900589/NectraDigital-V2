<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) exit;
require_once '../includes/db.php';
require_once '../includes/ckeditor.php';
require_once '../includes/blog_orphan.php';
require_once '../includes/blog_faq.php';
require_once '../includes/blog_schema.php';

date_default_timezone_set('Asia/Kolkata');

function upload_image($file) {
    $target_dir = "../assets/uploads/";
    if ($file["size"] > 512000) return ["error" => "Error: File too large (Max 500KB)."];
    $allowed_types = ['image/webp', 'image/svg+xml'];
    $mime = blog_admin_mime_type($file["tmp_name"]);
    if ($mime === null || !in_array($mime, $allowed_types, true)) {
        return ["error" => "Error: Only WEBP or SVG."];
    }
    $new_name = uniqid("blog_", true) . "." . pathinfo($file["name"], PATHINFO_EXTENSION);
    if (move_uploaded_file($file["tmp_name"], $target_dir . $new_name)) {
        return ["success" => "assets/uploads/" . $new_name];
    }
    return ["error" => "Upload failed."];
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $title = sanitize_db_text($_POST['title'] ?? '');
        $cat = sanitize_db_text($_POST['category'] ?? '');
        $content = nectra_sanitize_blog_html($_POST['content'] ?? '');
        $input_slug = sanitize_db_text($_POST['slug'] ?? '');
        $meta_desc = sanitize_db_text($_POST['meta_description'] ?? '');
        $is_orphan = !empty($_POST['is_orphan']) ? 1 : 0;
        $faq_json = blog_faq_encode(blog_faq_parse_request());
        $img_path = "";
        $scheduled_time = blog_normalize_datetime(!empty($_POST['scheduled_time']) ? clean_input($_POST['scheduled_time']) : null);
        $raw_slug = !empty($input_slug) ? $input_slug : $title;
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $raw_slug), '-'));

        if ($slug === '') {
            $error = 'Invalid slug — please enter a title or slug.';
        }

        if ($error === null && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload = upload_image($_FILES['image']);
            if (isset($upload['error'])) {
                $error = $upload['error'];
            } else {
                $img_path = $upload['success'];
            }
        } elseif ($error === null) {
            $error = "Featured Image is required.";
        }

        if ($error === null) {
            $result = blog_insert_post($conn, [
                'title' => $title,
                'category' => $cat,
                'image' => $img_path,
                'content' => $content,
                'slug' => $slug,
                'meta_description' => $meta_desc,
                'faq_json' => $faq_json,
                'created_at' => $scheduled_time,
                'is_orphan' => $is_orphan,
            ]);

            if (!empty($result['ok'])) {
                header("Location: dashboard.php?page=blog");
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
                blog_signal_post_indexed($slug, $scheduled_time);
                exit;
            }
            $error = 'Could not save post: ' . ($result['error'] ?? 'Unknown database error');
        }
    } catch (Throwable $e) {
        error_log('create_post POST: ' . $e->getMessage());
        $error = 'Server error while saving. Please try again. (' . $e->getMessage() . ')';
    }
}

require_once __DIR__ . '/includes/admin-layout.php';
admin_layout_start('Create Blog Post', 'blog', ['ckeditor' => true]);
?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <div class="ge-card ge-editor-form">
        <p class="text-muted small mb-4">Publish to Insights with SEO fields, optional schedule, and FAQ schema.</p>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="post_title" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Custom URL slug (optional)</label>
                    <input type="text" name="slug" id="post_slug" class="form-control" placeholder="Leave blank to auto-generate">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. AI, SEO" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Featured image (max 500KB — WebP/SVG)</label>
                    <input type="file" name="image" class="form-control" accept=".webp,.svg,image/webp,image/svg+xml" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Schedule (optional)</label>
                    <input type="datetime-local" name="scheduled_time" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">SEO meta description (max 160 chars)</label>
                    <textarea name="meta_description" class="form-control" rows="2" maxlength="160" placeholder="Plain text summary for search engines…"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_orphan" id="is_orphan" value="1">
                        <label class="form-check-label" for="is_orphan">
                            <strong>Orphan post</strong> — direct URL only, hidden from Insights listings, still indexed
                        </label>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Content</label>
                    <textarea id="post_content" name="content" class="ckeditor-full"></textarea>
                </div>
                <?php $blog_faqs = []; include __DIR__ . '/includes/blog-faq-form.php'; ?>
            </div>
            <div class="ge-form-actions">
                <button type="submit" class="btn btn-ge-primary"><i class="fas fa-paper-plane me-2"></i>Publish / schedule</button>
                <a href="dashboard.php?page=blog" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
        </div>
        <script>
        document.getElementById('post_title').addEventListener('input', function() {
            var slug = document.getElementById('post_slug');
            if (!slug.dataset.manual) {
                slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
        });
        document.getElementById('post_slug').addEventListener('input', function() {
            this.dataset.manual = '1';
        });
        </script>
<?php
admin_layout_end(['ckeditor' => true]);
