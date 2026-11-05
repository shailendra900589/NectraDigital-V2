<?php
require_once __DIR__ . '/init.php';

use Growth\Models\Industry;

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ge_is_ready() && ge_table_exists('ge_industries')) {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => ge_slugify($_POST['slug'] ?? $_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'icon' => trim($_POST['icon'] ?? 'fa-industry'),
        'meta_title_template' => trim($_POST['meta_title_template'] ?? ''),
        'meta_description_template' => trim($_POST['meta_description_template'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
    ];

    if ($action === 'edit' && $id) {
        Industry::update($id, $data);
        ge_admin_flash('success', 'Industry updated.');
    } else {
        Industry::create($data);
        ge_admin_flash('success', 'Industry created.');
    }
    header('Location: industries.php');
    exit;
}

if ($action === 'delete' && $id && ge_table_exists('ge_industries')) {
    Industry::delete($id);
    ge_admin_flash('success', 'Industry deleted.');
    header('Location: industries.php');
    exit;
}

$item = ($id && ge_table_exists('ge_industries')) ? Industry::find($id) : null;
$items = (ge_is_ready() && ge_table_exists('ge_industries')) ? Industry::all() : [];

ge_admin_layout();
ge_admin_layout_start('Industries Manager', 'industries');

if (!ge_table_exists('ge_industries')): ?>
<div class="alert alert-warning">Run <a href="../run-migration.php">Database migration</a> to enable Industries module.</div>
<?php elseif ($action === 'add' || ($action === 'edit' && $item)): ?>
<div class="ge-card">
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Industry Name *</label><input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"></div>
            <div class="col-md-6"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($item['slug'] ?? ''); ?>"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control ckeditor-basic" rows="3"><?php echo $item['description'] ?? ''; ?></textarea></div>
            <div class="col-md-4"><label class="form-label">Icon (FontAwesome)</label><input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($item['icon'] ?? 'fa-industry'); ?>"></div>
            <div class="col-md-8"><label class="form-label">Meta Title Template</label><input type="text" name="meta_title_template" class="form-control" placeholder="{service_name} for {industry_name} in {city_name}" value="<?php echo htmlspecialchars($item['meta_title_template'] ?? ''); ?>"></div>
            <div class="col-12"><label class="form-label">Meta Description Template</label><textarea name="meta_description_template" class="form-control ckeditor-basic" rows="2"><?php echo htmlspecialchars($item['meta_description_template'] ?? ''); ?></textarea></div>
            <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?php echo ($item['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive">Inactive</option></select></div>
            <div class="col-12"><button type="submit" class="btn btn-ge-primary">Save Industry</button><a href="industries.php" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div>
    </form>
</div>
<?php else: ?>
<div class="d-flex justify-content-between mb-4">
    <span class="text-muted"><?php echo count($items); ?> industries — used in Service × City × Industry landing pages</span>
    <a href="?action=add" class="btn btn-ge-primary"><i class="fas fa-plus"></i> Add Industry</a>
</div>
<div class="ge-card"><div class="table-responsive"><table class="table ge-table table-sm"><thead><tr><th>Industry</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($items as $row): ?>
<tr><td><strong><?php echo htmlspecialchars($row['name']); ?></strong><br><code><?php echo htmlspecialchars($row['slug']); ?></code></td><td><?php echo $row['status']; ?></td>
<td><a href="?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
<a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php endif;
ge_admin_layout_end();
