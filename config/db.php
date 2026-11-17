<?php
// Single database connection used by every page.
// Include it with:  require_once __DIR__ . '/../config/db.php';   (from Admin/ or student/)
//
// Default settings suit XAMPP (user "root", no password). To use other settings, copy
// config/db.local.example.php to config/db.local.php and edit it. That file is not committed to git.

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbConfig = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'online_enroll'];
if (is_file(__DIR__ . '/db.local.php')) {
	$dbConfig = array_merge($dbConfig, (array)require __DIR__ . '/db.local.php');
}

try {
	$conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['name']);
	$conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
	die("Could not connect to the database. Make sure MySQL is running and that "
	  . "database/online_enroll.sql has been imported. (Settings: config/db.php or config/db.local.php.)");
}
unset($dbConfig);
