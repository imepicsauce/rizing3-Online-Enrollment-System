<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');

$firstLogin = !empty($_SESSION['must_change_password']);
$getID = (int)$_SESSION['userID'];

$stmt = $conn->prepare("SELECT * FROM usertbl WHERE ID = ?");
$stmt->bind_param("i", $getID);
$stmt->execute();
$rowaccs = $stmt->get_result()->fetch_assoc();

if (isset($_POST['cpass'])) {
	$oldpass  = (string)($_POST['opassword'] ?? '');
	$newpass  = (string)($_POST['password'] ?? '');
	$newpass2 = (string)($_POST['password2'] ?? '');

	if (!$rowaccs || !password_matches($oldpass, $rowaccs['password'])) {
		notify_user('Inputted password is wrong please try again');
	} elseif (!csrf_valid()) {
        notify_user('Your session expired. Please try again.');
    } elseif ($firstLogin && ($newpass === $oldpass || strlen($newpass) < 10)) {
        notify_user('Choose a new password with at least 10 characters.');
    } elseif ($newpass === '') {
		notify_user('The new password cannot be empty.');
	} elseif ($newpass !== $newpass2) {
		notify_user('The new passwords do not match. Please try again.');
	} else {
		$stmt = $conn->prepare("UPDATE usertbl SET must_change_password=0, password = ? WHERE ID = ?");
		$newHash = password_make($newpass);
		$stmt->bind_param("si", $newHash, $getID);
		$stmt->execute();
		$_SESSION['must_change_password'] = false;
		notify_user('Successfully updated the password', true);
		redirect_to($firstLogin ? 'studentindex.php' : 'studentprofile.php');
	}
}

$pageTitle = 'Change password';
require __DIR__ . '/../includes/workspace_top.php';
?>
<?php if ($firstLogin) { ?><p>Enter your birthdate as the current password. Choose a private password of at least 10 characters to continue to enrollment.</p><?php } ?>
<form class="stack" method="post"><?= csrf_field() ?>
<label for="current">Current password</label><input id="current" type="password" autocomplete="current-password" name="opassword" required>
<label for="new">New password</label><input id="new" type="password" autocomplete="new-password" name="password" required>
<label for="repeat">Confirm new password</label><input id="repeat" type="password" autocomplete="new-password" name="password2" required>
<div class="actions"><input type="submit" name="cpass" value="Change password"><?php if (!$firstLogin) { ?><a href="studentprofile.php">Return to profile</a><?php } ?></div>
</form>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
