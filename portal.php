<?php
require_once(__DIR__ . '/includes/auth.php');   // starts the session


// Too many wrong passwords for one username from one address: wait before trying again.
const LOGIN_MAX_FAILS = 5;
const LOGIN_WINDOW_MINUTES = 15;

if (isset($_POST['submit'])) {
	require_once __DIR__ . '/config/db.php';
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
		$stmt = $conn->prepare("SELECT ID, username, password, user_type FROM usertbl WHERE username = ? LIMIT 1");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
	}

	if ($recentFails >= LOGIN_MAX_FAILS) {
		js_alert("Too many failed attempts. Please wait " . LOGIN_WINDOW_MINUTES . " minutes and try again.");
	} elseif (!$row || !password_matches($password, $row['password'])) {
		$ins = $conn->prepare("INSERT INTO login_attempts (username, ip) VALUES (?, ?)");
		$ins->bind_param("ss", $attemptName, $ip);
		$ins->execute();
		$conn->query("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");
		js_alert("User doesn't exist please try again.");
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

		if ($row['user_type'] == 'admin') {
			$_SESSION['type'] = 'admin';
			js_alert('Successfully log in as Administrator');
			js_redirect('Admin/adminindex.php');
		} else {
			$_SESSION['type'] = 'student';
			js_alert('Successfully log in as Student');
			js_redirect('student/studentindex.php');
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
</head>
<body class="portal">
<header class="mast"><div class="wrap"><a class="brand" href="./"><span class="mark" aria-hidden="true">RU</span>Rizing University</a><a href="./">University home</a></div></header>
<main class="login-spread">
<section class="signin" aria-labelledby="login-title">
<h1 id="login-title">Portal sign in</h1>
<p>Students and staff, sign in with the account issued by the registrar.</p>
<form method="post" action="portal.php">
<label for="username">Username</label><input id="username" name="username" value="<?= h($username ?? '') ?>" autocapitalize="none" spellcheck="false" autocomplete="username" required>
<label for="password">Password</label><div class="password-input"><input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="button" id="visibility" aria-controls="password" hidden>Show password</button></div>
<input class="button" type="submit" name="submit" value="Sign in">
</form>
<div class="account-help">New to Rizing? <a href="./#enrollment">How to get an account</a></div>
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
