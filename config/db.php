<?php
// Single database connection used by every page.
// Include it with:  require_once __DIR__ . '/../config/db.php';   (from Admin/ or student/)

// Make every MySQL error throw an exception on every PHP version, so pages can catch them.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	$conn = new mysqli("localhost", "root", "", "online_enroll");
	$conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
	die("Could not connect to the database. Make sure MySQL is running and that "
	  . "database/online_enroll.sql has been imported. (Settings are in config/db.php.)");
}
