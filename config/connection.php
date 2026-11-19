<?php
function enrollment_connection(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $dbConfig = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'online_enroll', 'port' => 3306];
    if (is_file(__DIR__ . '/db.local.php')) {
        $dbConfig = array_merge($dbConfig, (array)require __DIR__ . '/db.local.php');
    }
    // The automated tests (tests/run_tests.py) point the app at a separate test database with these
    // environment variables, so they never touch your real data. Leave them unset in normal use.
    foreach (['host' => 'ENROLL_DB_HOST', 'user' => 'ENROLL_DB_USER', 'pass' => 'ENROLL_DB_PASS', 'name' => 'ENROLL_DB_NAME', 'port' => 'ENROLL_DB_PORT'] as $key => $env) {
        if (getenv($env) !== false) { $dbConfig[$key] = getenv($env); }
    }

    $conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['name'], (int)$dbConfig['port']);
    $conn->set_charset("utf8mb4");
    return $conn;
}
