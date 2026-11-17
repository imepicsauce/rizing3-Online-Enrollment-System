<?php
require_once(__DIR__ . '/includes/auth.php');   // starts the session
require_once(__DIR__ . '/config/db.php');

// Too many wrong passwords for one username from one address: wait before trying again.
const LOGIN_MAX_FAILS = 5;
const LOGIN_WINDOW_MINUTES = 15;

if (isset($_POST['submit'])) {
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
<html>
<head>
<title>LOGIN PAGE</title>

<link href="assets/css/style.css" rel="stylesheet" type="text/css"/>

</head>
<body class="login">




	<div class = "loginForm" align="center">
	
	<h1>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspSystem Login</h1>
	
<form method="POST">	
<div style="float:left; margin-top: -40px; margin-left: 40px;">
<img src="assets/img/logo-login.jpg" width="160px" width="110px">
</div>
<div style="margin-left: auto; margin-right: auto;">
	
	</br>
	<b>Username:</b>
	<input type="text" class="text" required='required'  name="username"/>
	</br></br>
	<b>Password :</b>

	<input type="password" class="text"  required='required' name="password"/>
	</br></br>
<input type="submit" name="submit" value="Login" class="btn"/>
	</div>
</form>
</body>
</html>