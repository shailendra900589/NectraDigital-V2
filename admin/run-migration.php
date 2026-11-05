<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/db-migrate.php';
require_once __DIR__ . '/includes/admin-layout.php';

$log = '';
$ran = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run_migration') {
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        $error = 'Database connection failed. Check includes/db.local.php on the server.';
    } else {
        $result = nectra_run_database_migrations($conn);
        $log = $result['log'];
        $ran = true;
        if ($result['fail_count'] > 0) {
            $_SESSION['ge_flash'] = ['type' => 'warning', 'message' => 'Migration finished with ' . $result['fail_count'] . ' failed statement(s). See log below.'];
        } else {
            $_SESSION['ge_flash'] = ['type' => 'success', 'message' => 'Database migration completed successfully.'];
        }
    }
}

admin_layout_start('Database Migration', 'home');
?>

<div class="ge-card mb-4">
    <h2 class="h6 mb-2">Run schema migration</h2>
    <p class="text-muted small mb-3">
        Applies <code>database/schema.sql</code> and <code>schema-v2.sql</code> (Growth Engine tables, leads, ads, etc.).
        Safe to run multiple times — duplicate errors are skipped.
    </p>
    <p class="small text-warning mb-3">
        <i class="fas fa-lock me-1"></i> Public URL <code>/database/migrate.php</code> is blocked on production for security. Use this page while logged in as admin.
    </p>
    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" onsubmit="return confirm('Run database migration now?');">
        <input type="hidden" name="action" value="run_migration">
        <button type="submit" class="btn btn-ge-primary"><i class="fas fa-database me-1"></i> Run migration</button>
        <a href="growth/indexing.php" class="btn btn-outline-secondary ms-2">Open Indexing Manager</a>
    </form>
</div>

<?php if ($ran && $log !== ''): ?>
<div class="ge-card">
    <h2 class="h6 mb-3">Migration log</h2>
    <pre class="ge-migration-log mb-0"><?php echo htmlspecialchars($log); ?></pre>
</div>
<?php endif; ?>

<?php admin_layout_end(); ?>
