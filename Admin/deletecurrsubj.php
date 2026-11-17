<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accid'])) {
	if (!csrf_valid()) {
		js_alert('Your session expired. Please try again.');
	} else {
		$subjectID = (int)$_POST['accid'];
		$stmt = $conn->prepare("DELETE FROM subjecttbl WHERE ID = ?");
		$stmt->bind_param("i", $subjectID);
		$stmt->execute();
		js_alert($stmt->affected_rows > 0 ? 'Successfully Removed the selected subject' : 'That subject no longer exists');
	}
}
js_redirect('subjectreg.php');
