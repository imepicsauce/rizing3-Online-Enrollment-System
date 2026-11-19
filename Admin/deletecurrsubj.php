<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accid'])) {
	if (!csrf_valid()) {
		notify_user('Your session expired. Please try again.');
	} else {
		$subjectID = (int)$_POST['accid'];
		// Enrollment forms keep subject codes as text, so check them before deleting.
		$stmt = $conn->prepare("SELECT COUNT(*) FROM student_subject ss JOIN subjecttbl sj ON sj.ID = ? WHERE FIND_IN_SET(sj.subjectcode, ss.subjects) > 0");
		$stmt->bind_param("i", $subjectID);
		$stmt->execute();
		$stmt->bind_result($used);
		$stmt->fetch();
		$stmt->close();
		if ($used > 0) {
			notify_user("This subject is used in $used enrollment form(s), so it cannot be deleted. Remove or cancel those forms first.");
		} else {
			$stmt = $conn->prepare("DELETE FROM subjecttbl WHERE ID = ?");
			$stmt->bind_param("i", $subjectID);
			$stmt->execute();
			notify_user($stmt->affected_rows > 0 ? 'Successfully Removed the selected subject' : 'That subject no longer exists', $stmt->affected_rows > 0);
		}
	}
}
redirect_to('subjectreg.php');
