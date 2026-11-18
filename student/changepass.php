<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');

$getID = (int)$_SESSION['userID'];

$stmt = $conn->prepare("SELECT * FROM studenttbl WHERE accountID = ?");
$stmt->bind_param("i", $getID);
$stmt->execute();
$rows = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM usertbl WHERE ID = ?");
$stmt->bind_param("i", $getID);
$stmt->execute();
$rowaccs = $stmt->get_result()->fetch_assoc();

if (isset($_POST['cpass'])) {
	$oldpass  = (string)($_POST['opassword'] ?? '');
	$newpass  = (string)($_POST['password'] ?? '');
	$newpass2 = (string)($_POST['password2'] ?? '');

	if (!$rowaccs || !password_matches($oldpass, $rowaccs['password'])) {
		js_alert('Inputted password is wrong please try again');
	} elseif ($newpass === '') {
		js_alert('The new password cannot be empty.');
	} elseif ($newpass !== $newpass2) {
		js_alert('The new passwords do not match. Please try again.');
	} else {
		$stmt = $conn->prepare("UPDATE usertbl SET password = ? WHERE ID = ?");
		$newHash = password_make($newpass);
		$stmt->bind_param("si", $newHash, $getID);
		$stmt->execute();
		js_alert('Successfully updated the password');
		js_redirect('studentprofile.php');
		exit;
	}
}

$pageTitle = 'Change password';
require __DIR__ . '/../includes/workspace_top.php';
?>
<form class="stack" method="post">
<label for="current">Current password</label><input id="current" type="password" autocomplete="current-password" name="opassword" required>
<label for="new">New password</label><input id="new" type="password" autocomplete="new-password" name="password" required>
<label for="repeat">Confirm new password</label><input id="repeat" type="password" autocomplete="new-password" name="password2" required>
<div class="actions"><input type="submit" name="cpass" value="Change password"><a href="studentprofile.php">Return to profile</a></div>
</form>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
