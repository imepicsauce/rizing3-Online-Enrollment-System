<?php
// Keep accepting sign-in submissions from older clients and bookmarked forms.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/portal.php';
    exit;
}
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/settings.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rizing University · Enrollment</title>
<link rel="stylesheet" href="assets/css/public.css">
</head>
<body class="home">
<a class="skip" href="#main">Skip to content</a>
<header class="mast">
    <div class="wrap">
        <a class="brand" href="./"><span class="mark" aria-hidden="true">RU</span><span>Rizing University</span></a>
        <nav aria-label="Main navigation">
            <a href="#university">About Rizing</a>
            <a href="#enrollment">Enrollment guide</a>
            <a class="portal-link" href="portal.php">Portal sign in</a>
        </nav>
    </div>
</header>
<main id="main">
    <section class="campus-hero" aria-labelledby="welcome-title">
        <img src="assets/img/university-campus.webp" alt="" width="1180" height="773" fetchpriority="high">
        <div class="wrap">
            <h1 id="welcome-title">Welcome to<br>Rizing University<span>.</span></h1>
            <p>Plan your semester, explore your subjects, and follow your enrollment from selection to approval.</p>
            <div class="actions">
                <a class="button arrow" href="portal.php">Sign in to the portal</a>
                <a class="hero-link" href="#enrollment">How to enroll</a>
            </div>
        </div>
    </section>
    <section class="guide wrap" id="enrollment" aria-labelledby="guide-heading">
        <div class="guide-title">
            <h2 id="guide-heading">Enrollment guide</h2>
            <p>Academic year <strong><?php echo h(SCHOOL_YEAR); ?></strong></p>
        </div>
        <ol class="steps">
            <li><h3>Get your account</h3><p>New to Rizing? Ask the registrar for your student account and sign-in details.</p></li>
            <li><h3>Choose your subjects</h3><p><a href="portal.php">Sign in to the portal</a> to select your semester and the subjects offered for your program.</p></li>
            <li><h3>Review and submit</h3><p>Check your units and fees, then submit your form. Track approval in My Enrollment Report.</p></li>
        </ol>
    </section>
    <section class="about" id="university" aria-labelledby="about-heading">
        <div class="wrap about-layout">
            <img src="assets/img/library.webp" alt="" width="900" height="600" loading="lazy">
            <div>
                <h2 id="about-heading">About Rizing</h2>
                <h3>Our vision</h3><p>Vision statement forthcoming.</p>
                <h3>Our mission</h3><p>Mission statement forthcoming.</p>
            </div>
        </div>
    </section>
</main>
<footer><div class="wrap"><span>Rizing University</span><a href="portal.php">Student &amp; staff portal</a></div></footer>
</body></html>
