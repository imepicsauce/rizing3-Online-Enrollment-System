<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

$adminID = (int)$_SESSION['userID'];

if (isset($_POST['register'])) {
	$username = trim($_POST['username'] ?? '');
	$password = (string)($_POST['password'] ?? '');

	if ($username === '') {
		notify_user('Username is required.');
	} else {
		try {
			// Only the logged-in administrator's own account is changed.
			if ($password === '') {
				// Blank password box = keep the current password.
				$stmt = $conn->prepare("UPDATE usertbl SET username = ? WHERE ID = ? AND user_type = 'admin'");
				$stmt->bind_param("si", $username, $adminID);
			} else {
				$stmt = $conn->prepare("UPDATE usertbl SET username = ?, password = ? WHERE ID = ? AND user_type = 'admin'");
				$hash = password_make($password);
				$stmt->bind_param("ssi", $username, $hash, $adminID);
			}
			$stmt->execute();
			$_SESSION['username'] = $username;
			notify_user('Successfully updated the admin account', true);
			redirect_to('userreg.php');
		} catch (mysqli_sql_exception $e) {
			notify_user(($e->getCode() == 1062) ? 'That username is already taken.' : 'The account could not be saved.');
		}
	}
}

$stmt = $conn->prepare("SELECT username FROM usertbl WHERE ID = ? AND user_type = 'admin' LIMIT 1");
$stmt->bind_param("i", $adminID);
$stmt->execute();
$rowfetch = $stmt->get_result()->fetch_assoc();
if (!$rowfetch) {
	header('Location: ../index.php');
	exit;
}
$pageTitle = 'Registrar account';
require __DIR__ . '/../includes/workspace_top.php';
?>
<form class="stack" method="post">
<label for="username">Username</label>
<input type="text" id="username" required maxlength="50" name="username" value="<?= h($rowfetch['username']) ?>">
<label for="password">New password</label>
<input type='password' id="password" maxlength="50" name="password" autocomplete="new-password" aria-describedby="password-help">
<p id="password-help">Leave this blank to keep your current password.</p>
<div class="actions"><input type="submit" name="register" value="Save changes"><a href="userreg.php">Cancel</a></div>
</form>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
