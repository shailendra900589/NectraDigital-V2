<?php
/**
 * Run database migrations (v1 + v2)
 * CLI: php database/migrate.php
 * Web: blocked by .htaccess — use admin/run-migration.php when logged in.
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8', true, 403);
    echo "Forbidden. Run from CLI (php database/migrate.php) or use Admin → Run migration.\n";
    exit;
}

echo "Nectra Digital — Migration starting...\n";

try {
    require_once __DIR__ . '/../includes/db.php';
} catch (Throwable $e) {
    die('DB load failed: ' . $e->getMessage() . "\n");
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("ERROR: \$conn not set. Create includes/db.local.php with Hostinger MySQL credentials.\n");
}

if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error . "\n");
}

require_once __DIR__ . '/../includes/db-migrate.php';
$result = nectra_run_database_migrations($conn);
echo $result['log'];
echo "\nAdmin: https://www.nectradigital.com/admin/growth/\n";
exit($result['fail_count'] > 0 ? 1 : 0);
