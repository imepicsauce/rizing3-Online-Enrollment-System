<?php
// Keep accepting sign-in submissions from older clients and bookmarked forms.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/portal.php';
    exit;
}
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/connection.php';
$programs = null;
try {
    $catalog = enrollment_connection();
    $programs = $catalog->query('SELECT course_code, course_title FROM courses_tbl ORDER BY course_title')->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    // The public entrance remains usable while the catalog is unavailable.
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rizing University · Enrollment</title>
<link rel="stylesheet" href="assets/css/public.css">
<link rel="stylesheet" href="assets/css/motion.css">
</head>
<body class="home">
<a class="skip" href="#main">Skip to content</a>
<header class="mast">
    <div class="wrap">
        <a class="brand" href="./"><span class="mark" aria-hidden="true">RU</span><span>Rizing University</span></a>
        <nav aria-label="Main navigation">
            <a href="#programs">Academics</a>
            <a href="#university">The university</a>
            <a href="#enrollment">Enrollment guide</a>
            <a class="portal-link" href="portal.php">Portal sign in</a>
        </nav>
    </div>
</header>
<main id="main">
    <section class="campus-hero" aria-labelledby="welcome-title">
        <div class="hero-image"><img src="assets/img/university-campus.webp" alt="" width="1180" height="773" fetchpriority="high"></div>
        <div class="wrap"><div class="hero-panel">
            <h1 id="welcome-title">Enrollment at<br>Rizing University</h1>
            <p>Explore the programs at Rizing, plan your semester, and follow your enrollment through approval.</p>
            <div class="actions">
                <a class="button arrow" href="portal.php">Sign in to the portal</a>
                <a class="hero-link" href="#enrollment">How to enroll</a>
            </div>
        </div></div>
    </section>
    <section class="programs wrap" id="programs" aria-labelledby="program-heading">
        <div><h2 id="program-heading">Academic programs</h2>
        <p>Once enrolled in a program, use the student portal to view its curriculum and available subjects.</p></div>
        <?php if ($programs) { ?>
        <ul class="program-list">
        <?php foreach ($programs as $program) { ?>
            <li><span><?= h(strtoupper($program['course_code'])) ?></span><strong><?= h($program['course_title']) ?></strong></li>
        <?php } ?>
        </ul>
        <?php } else { ?><p><?= $programs === null ? 'The program catalog is temporarily unavailable.' : 'The registrar has not listed any programs yet.' ?></p><?php } ?>
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
