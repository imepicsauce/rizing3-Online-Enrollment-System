<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accid'])) {
	if (!csrf_valid()) {
		js_alert('Your session expired. Please try again.');
	} else {
		$studentRowID = (int)$_POST['accid'];
		// Delete the login account of that student. The student profile and enrollment
		// forms are removed with it (foreign key ON DELETE CASCADE).
		$stmt = $conn->prepare("DELETE u FROM usertbl u JOIN studenttbl s ON s.accountID = u.ID
		                        WHERE s.ID = ? AND u.user_type = 'student'");
		$stmt->bind_param("i", $studentRowID);
		$stmt->execute();
		js_alert($stmt->affected_rows > 0 ? 'Successfully Removed the selected account' : 'That account no longer exists');
	}
}
js_redirect('userreg.php');
