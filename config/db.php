<?php
// Single database connection used by every page.
// Include it with:  require_once __DIR__ . '/../config/db.php';   (from Admin/ or student/)
//
// Default settings suit XAMPP (user "root", no password). To use other settings, copy
// config/db.local.example.php to config/db.local.php and edit it. That file is not committed to git.

require_once __DIR__ . '/connection.php';
try {
    $conn = enrollment_connection();
} catch (mysqli_sql_exception $e) {
	die("Could not connect to the database. Make sure MySQL is running and that "
	  . "database/online_enroll.sql has been imported. (Settings: config/db.php or config/db.local.php.)");
}
