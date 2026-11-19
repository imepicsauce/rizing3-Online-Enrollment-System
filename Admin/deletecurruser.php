<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accid'])) {
	if (!csrf_valid()) {
		notify_user('Your session expired. Please try again.');
	} else {
		$studentRowID = (int)$_POST['accid'];
		// Delete the login account of that student. The student profile and enrollment
		// forms are removed with it (foreign key ON DELETE CASCADE).
		$stmt = $conn->prepare("DELETE u FROM usertbl u JOIN studenttbl s ON s.accountID = u.ID
		                        WHERE s.ID = ? AND u.user_type = 'student'");
		$stmt->bind_param("i", $studentRowID);
		$stmt->execute();
		notify_user($stmt->affected_rows > 0 ? 'Successfully Removed the selected account' : 'That account no longer exists', $stmt->affected_rows > 0);
	}
}
redirect_to('userreg.php');
