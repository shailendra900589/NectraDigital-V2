<?php
/**
 * Shared database migration runner (CLI + authenticated admin).
 */

function nectra_migration_skip_error(string $err): bool
{
    $needles = [
        'Duplicate', 'already exists', "Can't DROP", 'check that it exists',
        'Unknown column', 'Duplicate column', 'Duplicate key name',
        'Multiple primary key', 'Duplicate entry',
    ];
    foreach ($needles as $needle) {
        if (stripos($err, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function nectra_migration_run_sql_file(mysqli $conn, string $path): array
{
    if (!file_exists($path)) {
        return ['ok' => 0, 'fail' => 0, 'msg' => "Missing: $path\n"];
    }
    $sql = file_get_contents($path);
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $ok = 0;
    $fail = 0;
    $out = '=== ' . basename($path) . " ===\n";
    foreach ($statements as $statement) {
        if (strlen($statement) < 5) {
            continue;
        }
        try {
            if ($conn->query($statement)) {
                $ok++;
                if (preg_match('/CREATE TABLE.*?(\w+)/i', $statement, $m)) {
                    $out .= "OK: Table {$m[1]}\n";
                }
            } else {
                $err = $conn->error;
                if (nectra_migration_skip_error($err)) {
                    $ok++;
                    $out .= 'SKIP: ' . substr($statement, 0, 50) . "... ($err)\n";
                } else {
                    $fail++;
                    $out .= "FAIL: $err\n  " . substr($statement, 0, 80) . "...\n";
                }
            }
        } catch (mysqli_sql_exception $e) {
            $err = $e->getMessage();
            if (nectra_migration_skip_error($err)) {
                $ok++;
                $out .= 'SKIP: ' . substr($statement, 0, 50) . "... ($err)\n";
            } else {
                $fail++;
                $out .= "FAIL: $err\n  " . substr($statement, 0, 80) . "...\n";
            }
        }
    }
    $out .= "Done: $ok ok, $fail failed\n\n";
    return ['ok' => $ok, 'fail' => $fail, 'msg' => $out];
}

/**
 * @return array{ok:bool,log:string,ok_count:int,fail_count:int,catalog:array|null,error:?string}
 */
function nectra_run_database_migrations(mysqli $conn): array
{
    $base = dirname(__DIR__) . '/database';
    $r1 = nectra_migration_run_sql_file($conn, $base . '/schema.sql');
    $r2 = nectra_migration_run_sql_file($conn, $base . '/schema-v2.sql');

    $log = "Nectra Digital — Migration\nDB connected OK\n\n";
    $log .= $r1['msg'] . $r2['msg'];
    $okCount = $r1['ok'] + $r2['ok'];
    $failCount = $r1['fail'] + $r2['fail'];
    $log .= "Total: {$okCount} ok, {$failCount} failed\n";

    $catalog = null;
    $bootstrap = dirname(__DIR__) . '/includes/growth/bootstrap.php';
    if (file_exists($bootstrap)) {
        require_once $bootstrap;
        if (function_exists('ge_is_ready') && ge_is_ready()) {
            require_once dirname(__DIR__) . '/includes/growth/engines/CatalogSyncEngine.php';
            $sync = \Growth\Engines\CatalogSyncEngine::syncAll();
            $catalog = $sync;
            $log .= "\nCatalog sync: {$sync['services']} services, {$sync['cities']} cities\n";
        }
    }

    return [
        'ok' => $failCount === 0,
        'log' => $log,
        'ok_count' => $okCount,
        'fail_count' => $failCount,
        'catalog' => $catalog,
        'error' => null,
    ];
}
