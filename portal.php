<?php
require_once(__DIR__ . '/includes/auth.php');   // starts the session


$username = $_SESSION['new_student_id'] ?? '';
unset($_SESSION['new_student_id']);

// Too many wrong passwords for one username from one address: wait before trying again.
const LOGIN_MAX_FAILS = 5;
const LOGIN_WINDOW_MINUTES = 15;

if (isset($_POST['submit'])) {
	require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/includes/enrollment_access_schema.php';
    try {
        ensure_enrollment_access($conn);
    } catch (mysqli_sql_exception $error) {
        error_log('Enrollment sign-in schema check failed (MySQL ' . $error->getCode() . ')');
        notify_user('Sign-in is temporarily unavailable. Please contact the registrar. Reference: LOGIN-' . $error->getCode() . '.');
        redirect_to('portal.php');
    }
	$username = trim($_POST['username'] ?? '');
	$password = (string)($_POST['password'] ?? '');
	$attemptName = substr($username, 0, 50);
	$ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

	$stmt = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip = ?
	                        AND attempted_at > (NOW() - INTERVAL " . LOGIN_WINDOW_MINUTES . " MINUTE)");
	$stmt->bind_param("ss", $attemptName, $ip);
	$stmt->execute();
	$stmt->bind_result($recentFails);
	$stmt->fetch();
	$stmt->close();

	$row = null;
	if ($recentFails < LOGIN_MAX_FAILS) {
		$stmt = $conn->prepare("SELECT u.ID,u.username,u.password,u.user_type,u.must_change_password FROM usertbl u LEFT JOIN studenttbl s ON s.accountID=u.ID AND u.user_type='student' WHERE u.username=? OR s.studnum=? ORDER BY u.username=? DESC LIMIT 1");
		$stmt->bind_param("sss", $username, $username, $username);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
	}

	if ($recentFails >= LOGIN_MAX_FAILS) {
		notify_user("Too many failed attempts. Please wait " . LOGIN_WINDOW_MINUTES . " minutes and try again.");
	} elseif (!$row || !in_array($row['user_type'], ['admin', 'faculty', 'student'], true) || !password_matches($password, $row['password'])) {
		$ins = $conn->prepare("INSERT INTO login_attempts (username, ip) VALUES (?, ?)");
		$ins->bind_param("ss", $attemptName, $ip);
		$ins->execute();
		$conn->query("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");
		notify_user("User doesn't exist please try again.");
	} else {
		$clr = $conn->prepare("DELETE FROM login_attempts WHERE username = ? AND ip = ?");
		$clr->bind_param("ss", $attemptName, $ip);
		$clr->execute();
		// Old plain-text passwords are upgraded to a hash the first time their owner logs in.
		if (password_is_legacy($row['password'])) {
			$up = $conn->prepare("UPDATE usertbl SET password = ? WHERE ID = ?");
			$hash = password_make($password);
			$up->bind_param("si", $hash, $row['ID']);
			$up->execute();
		}
		session_regenerate_id(true);
		$_SESSION['username'] = $row['username'];
		$_SESSION['userID']   = (int)$row['ID'];
		$_SESSION['logcheck'] = 1;

		$_SESSION['must_change_password'] = (bool)$row['must_change_password'];
        if ($row['user_type'] === 'faculty') {
            $_SESSION['type'] = 'faculty';
            redirect_to('faculty/index.php');
        }
        if ($row['user_type'] == 'admin') {
			$_SESSION['type'] = 'admin';
			notify_user('Successfully log in as Administrator', true);
			redirect_to('Admin/adminindex.php');
		} else {
			$_SESSION['type'] = 'student';
			if ($_SESSION['must_change_password']) {
                notify_user('Choose a private password to replace your birthdate.');
                redirect_to('student/changepass.php');
            }
            notify_user('Successfully log in as Student', true);
			redirect_to('student/studentindex.php');
		}
		exit;
	}
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Portal sign in · Rizing University</title>
<link rel="stylesheet" href="assets/css/public.css">
<link rel="stylesheet" href="assets/css/feedback.css">
<link rel="stylesheet" href="assets/css/motion.css">
</head>
<body class="portal">
<header class="mast"><div class="wrap"><a class="brand" href="./"><span class="mark" aria-hidden="true">RU</span>Rizing University</a><a href="./">University home</a></div></header>
<main class="login-spread">
<section class="signin" aria-labelledby="login-title">
<h1 id="login-title">Portal sign in</h1>
<p>Students sign in with their student ID and current password. New students use their birthdate (YYYY-MM-DD) for the first sign-in. Staff use their assigned username.</p>
<?php render_notices(); ?>
<form method="post" action="portal.php">
<label for="username">Student ID or staff username</label><input id="username" name="username" value="<?= h($username ?? '') ?>" autocapitalize="none" spellcheck="false" autocomplete="username" required>
<label for="password">Password</label><div class="password-input"><input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="button" id="visibility" aria-controls="password" hidden>Show password</button></div>
<input class="button" type="submit" name="submit" value="Sign in">
</form>
<div class="account-help">New to Rizing? <a href="register.php">Create student account</a></div>
</section>
<aside class="login-photo" aria-label="Rizing University library illustration">
<img src="assets/img/library.webp" alt="">
<span>Rizing University</span>
</aside>
</main>
<script>
const toggle = document.getElementById('visibility');
const password = document.getElementById('password');
toggle.hidden = false;
toggle.addEventListener('click', () => {
    const reveal = password.type === 'password';
    password.type = reveal ? 'text' : 'password';
    toggle.textContent = reveal ? 'Hide password' : 'Show password';
});
</script>
</body></html>
