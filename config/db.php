<?php
// Single database connection used by every page.
// Include it with:  require_once __DIR__ . '/../config/db.php';   (from Admin/ or student/)
$conn = new mysqli("localhost", "root", "");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

mysqli_select_db($conn, 'online_enroll');
?>