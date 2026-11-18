<?php
// Keep accepting sign-in submissions from older clients and bookmarked forms.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/portal.php';
    exit;
}
require_once __DIR__ . '/includes/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rizing University · Enrollment</title>
<link rel="stylesheet" href="assets/css/public.css">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="mast"><div class="wrap">
<a class="brand" href="./"><span class="mark" aria-hidden="true">RU</span>Rizing University</a>
<nav aria-label="Main navigation"><a href="#enrollment">Enrollment guide</a><a href="#university">About</a><a class="portal-link" href="portal.php">Portal sign in</a></nav>
</div></header>
<main id="main">
<section class="welcome wrap" aria-labelledby="welcome-title">
<div class="intro">
<h1 id="welcome-title">Rizing<br><em>University</em></h1>
<p>Your semester starts here. Find out how to enroll, review your subjects and fees, and follow your enrollment through approval.</p>
<div class="actions"><a class="button arrow" href="#enrollment">How to enroll</a><a class="textlink" href="portal.php">Sign in to the portal</a></div>
</div>
<div class="campus"><img src="assets/img/university-campus.webp" alt="" width="1180" height="773" fetchpriority="high"></div>
</section>
<section class="guide" id="enrollment"><div class="wrap columns">
<div><h2>Enrollment guide</h2><p>New to the university? Begin with the registrar. Returning students can go straight to subject selection.</p></div>
<ol>
<li><h3>Get your account</h3><p>Ask the registrar to create your student account and issue your sign-in details.</p></li>
<li><h3>Choose your subjects</h3><p><a href="portal.php">Sign in</a>, choose your semester, and select the subjects offered for your program.</p></li>
<li><h3>Review and submit</h3><p>Check your units and fees before submitting. Open My Enrollment Report to see when the registrar approves your form.</p></li>
</ol>
</div></section>
<section class="about wrap columns" id="university"><h2>About the university</h2><div class="statements"><div><h3>Our vision</h3><p>Vision statement forthcoming.</p></div><div><h3>Our mission</h3><p>Mission statement forthcoming.</p></div></div></section>
</main>
<footer><div class="wrap"><span>Rizing University</span><a href="portal.php">Student &amp; staff portal</a></div></footer>
</body></html>
