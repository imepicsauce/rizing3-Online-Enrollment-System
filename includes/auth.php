<?php
// Shared login, role and CSRF helpers.
// At the top of every protected page (before any output):
//     require_once(__DIR__ . '/../includes/auth.php');
//     require_role('admin');        // or 'student'

// Start the session with safer cookie settings (HttpOnly, SameSite, Secure on https).
function start_secure_session() {
	if (session_status() !== PHP_SESSION_NONE) { return; }
	$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
	ini_set('session.use_strict_mode', '1');
	ini_set('session.use_only_cookies', '1');
	session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
	session_start();
}
start_secure_session();

// Send visitors without the right role back to the login page, and stop the page from running.
function require_role($role) {
	if (empty($_SESSION['logcheck']) || ($_SESSION['type'] ?? '') !== $role) {
		header('Location: ../index.php');
		exit;
	}
}

// Escape a value for safe output inside HTML.
function h($v) {
	return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Carry feedback through the redirect and render it once on the destination page.
function notify_user($message, $success = false) {
    $_SESSION['notices'][] = ['message' => (string)$message, 'success' => $success];
}

function render_notices() {
    foreach ($_SESSION['notices'] ?? [] as $notice) {
        $role = $notice['success'] ? 'status' : 'alert';
        echo '<p class="portal-notice" role="' . $role . '">' . h($notice['message']) . '</p>';
    }
    unset($_SESSION['notices']);
}

function redirect_to($url) {
    header('Location: ' . $url, true, 303);
    exit;
}

// CSRF protection for forms that change data.
function csrf_token() {
	if (empty($_SESSION['csrf'])) {
		$_SESSION['csrf'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf'];
}
function csrf_field() {
	return '<input type="hidden" name="token" value="' . h(csrf_token()) . '">';
}
function csrf_valid() {
	return isset($_POST['token'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['token']);
}

// Passwords: stored hashed. Old plain-text passwords still work once and are re-saved as hashes.
function password_matches($input, $stored) {
	$stored = (string)$stored;
	if (preg_match('/^\$(2y|argon2id?)\$/', $stored)) {
		return password_verify((string)$input, $stored);
	}
	return $stored !== '' && hash_equals($stored, (string)$input);
}
function password_is_legacy($stored) {
	return !preg_match('/^\$(2y|argon2id?)\$/', (string)$stored);
}
function password_make($plain) {
	return password_hash((string)$plain, PASSWORD_DEFAULT);
}
