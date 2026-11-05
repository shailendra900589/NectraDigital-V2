<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) exit;
require_once '../includes/db.php';
require_once '../includes/ckeditor.php';
require_once '../includes/blog_orphan.php';
require_once '../includes/blog_faq.php';
require_once '../includes/blog_schema.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php?page=blog');
    exit;
}

$result = $conn->query("SELECT * FROM blog_posts WHERE id=" . $id);
$post = $result ? $result->fetch_assoc() : null;
if (!$post) {
    header('Location: dashboard.php?page=blog');
    exit;
}

function upload_image($file) {
    $target_dir = "../assets/uploads/";
    if ($file["size"] > 512000) return ["error" => "Error: File too large (Max 500KB)."];
    $allowed_types = ['image/webp', 'image/svg+xml'];
    $mime = blog_admin_mime_type($file["tmp_name"]);
    if ($mime === null || !in_array($mime, $allowed_types, true)) {
        return ["error" => "Error: Only WEBP or SVG."];
    }
    $new_name = uniqid("blog_", true) . "." . pathinfo($file["name"], PATHINFO_EXTENSION);
    if (move_uploaded_file($file["tmp_name"], $target_dir . $new_name)) return ["success" => "assets/uploads/" . $new_name];
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
        $img_path = $post['image'];
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
        }

        if ($error === null) {
            $result = blog_update_post($conn, $id, [
                'title' => $title,
                'category' => $cat,
                'image' => $img_path,
                'content' => $content,
                'slug' => $slug,
                'meta_description' => $meta_desc,
                'faq_json' => $faq_json,
                'is_orphan' => $is_orphan,
            ]);

            if (!empty($result['ok'])) {
                header("Location: dashboard.php?page=blog");
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
                blog_signal_post_indexed($slug, $post['created_at'] ?? null);
                exit;
            }
            $error = 'Could not update post: ' . ($result['error'] ?? 'Unknown database error');
        }
    } catch (Throwable $e) {
        error_log('edit_post POST: ' . $e->getMessage());
        $error = 'Server error while saving. Please try again. (' . $e->getMessage() . ')';
    }
}

$post_is_orphan = blog_is_orphan($post);
$blog_faqs = blog_faq_decode($post['faq_json'] ?? null);

require_once __DIR__ . '/includes/admin-layout.php';
admin_layout_start('Edit Blog Post', 'blog', ['ckeditor' => true]);
?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <div class="ge-card ge-editor-form">
        <p class="text-muted small mb-4">Editing: <strong><?php echo nectra_display_text($post['title']); ?></strong></p>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="post_title" value="<?php echo nectra_display_text($post['title']); ?>" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Custom URL slug</label>
                    <input type="text" name="slug" id="post_slug" value="<?php echo isset($post['slug']) ? htmlspecialchars($post['slug']) : ''; ?>" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" value="<?php echo nectra_display_text($post['category']); ?>" class="form-control" required>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Featured image</label><br>
                    <?php if (!empty($post['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($post['image']); ?>" height="50" class="mb-2 border rounded" style="object-fit: cover;" alt="">
                    <?php endif; ?>
                    <label class="d-block text-muted small">Upload new to replace</label>
                    <input type="file" name="image" class="form-control" accept=".webp,.svg,image/webp,image/svg+xml">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">SEO meta description (max 160 chars)</label>
                    <textarea name="meta_description" class="form-control" rows="2" maxlength="160"><?php echo isset($post['meta_description']) ? nectra_display_text($post['meta_description']) : ''; ?></textarea>
                </div>
                <div class="col-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_orphan" id="is_orphan" value="1" <?php echo !empty($post_is_orphan) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_orphan">
                            <strong>Orphan post</strong> — direct URL only, hidden from Insights listings, still indexed
                        </label>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Content</label>
                    <textarea id="post_content" name="content" class="ckeditor-full"><?php echo $post['content']; ?></textarea>
                </div>
                <?php include __DIR__ . '/includes/blog-faq-form.php'; ?>
            </div>
            <div class="ge-form-actions">
                <button type="submit" class="btn btn-ge-primary"><i class="fas fa-save me-2"></i>Update post</button>
                <a href="dashboard.php?page=blog" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
        </div>
        <script>
        document.getElementById('post_slug').addEventListener('input', function() { this.dataset.manual = '1'; });
        </script>
<?php
admin_layout_end(['ckeditor' => true]);
