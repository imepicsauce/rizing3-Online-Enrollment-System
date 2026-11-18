<?php
require_once __DIR__ . '/../config/settings.php';
$registrar = $_SESSION['type'] === 'admin';
$workspaceName = $registrar ? 'Registrar workspace' : 'Student portal';
$home = $registrar ? 'adminindex.php' : 'studentindex.php';
$navigation = $registrar
    ? [$home => 'Home', 'approvals.php' => 'Approvals', 'userreg.php' => 'Manage Users', 'subjectreg.php' => 'Manage Subjects', 'courses.php' => 'Courses', 'report.php' => 'Generate Report']
    : [$home => 'Home', 'enroll.php' => 'Enroll Now', 'report.php' => 'My Enrollment Report', 'curriculum.php' => 'Curriculum', 'studentprofile.php' => 'Profile'];
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?> · Rizing University</title>
<link rel="stylesheet" href="../assets/css/workspace.css">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="header"><div class="bar">
<a class="brand" href="<?= $home ?>"><span class="mark" aria-hidden="true">RU</span>Rizing University</a>
<span class="identity"><?= h($_SESSION['username']) ?> <a class="nav-link" href="../includes/logout.php">Log Out</a></span>
</div></header>
<div class="layout">
<nav class="container" aria-label="<?= $workspaceName ?>">
<strong><?= $workspaceName ?></strong>
<?php foreach ($navigation as $url => $label) { ?>
<a class="nav-link" href="<?= $url ?>"<?= ($current === $url || ($current === 'showsubs.php' && $url === 'enroll.php') || ($current === 'changepass.php' && $url === 'studentprofile.php')) ? ' aria-current="location"' : '' ?>><?= $label ?></a>
<?php } ?>
</nav>
<main id="main">
<div class="context"><?= $workspaceName ?> <span>Academic year <?= h(SCHOOL_YEAR) ?></span></div>
<h1><?= h($pageTitle) ?></h1>
