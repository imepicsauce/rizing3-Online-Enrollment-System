<?php
require_once __DIR__ . '/../config/settings.php';
$registrar = $_SESSION['type'] === 'admin';
$workspaceName = $registrar ? 'Registrar workspace' : 'Student portal';
$home = $registrar ? 'adminindex.php' : 'studentindex.php';
$navigation = $registrar
    ? [$home => 'Home', 'approvals.php' => 'Approvals', 'userreg.php' => 'Manage Users', 'subjectreg.php' => 'Manage Subjects', 'courses.php' => 'Courses', 'calendar.php' => 'Enrollment schedule', 'faculty.php' => 'Faculty access', 'report.php' => 'Generate Report']
    : [$home => 'Home', 'enroll.php' => 'Enroll Now', 'report.php' => 'My Enrollment Report', 'curriculum.php' => 'Curriculum', 'studentprofile.php' => 'Profile'];
if ($_SESSION['type'] === 'faculty') {
    $workspaceName = 'Faculty portal';
    $home = 'index.php';
    $navigation = [$home => 'Teaching schedule'];
}
$headerStudent = null;
if ($_SESSION['type'] === 'student') {
    require_once __DIR__ . '/../Admin/includes/student_form_lib.php';
    $headerQuery = $conn->prepare('SELECT fname, minitial, lname, studnum FROM studenttbl WHERE accountID = ?');
    $headerQuery->bind_param('i', $_SESSION['userID']);
    $headerQuery->execute();
    $headerStudent = $headerQuery->get_result()->fetch_assoc();
    $headerQuery->close();
}
$current = basename($_SERVER['SCRIPT_NAME']);
$parentPages = ['showsubs.php'=>'enroll.php', 'changepass.php'=>'studentprofile.php',
    'addnewuser.php'=>'userreg.php', 'edituser.php'=>'userreg.php',
    'addnewsubj.php'=>'subjectreg.php', 'editsubject.php'=>'subjectreg.php'];
$activePage = $parentPages[$current] ?? $current;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?> · Rizing University</title>
<link rel="stylesheet" href="../assets/css/workspace.css">
<link rel="stylesheet" href="../assets/css/feedback.css">
<link rel="stylesheet" href="../assets/css/motion.css">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="header"><div class="bar">
<a class="brand" href="<?= $home ?>"><span class="mark" aria-hidden="true">RU</span>Rizing University</a>
<span class="identity">
<?php if ($headerStudent) { ?>
<span class="student-identity"><strong><?= h(student_fullname($headerStudent)) ?></strong><span>Student ID <?= h($headerStudent['studnum']) ?></span></span>
<?php } else { echo h($_SESSION['username']); } ?>
<a class="nav-link" href="../includes/logout.php">Log Out</a></span>
</div></header>
<div class="layout">
<nav class="container" aria-label="<?= $workspaceName ?>">
<strong><?= $workspaceName ?></strong>
<?php foreach ($navigation as $url => $label) { ?>
<a class="nav-link" href="<?= $url ?>"<?= ($activePage === $url) ? ' aria-current="location"' : '' ?>><?= $label ?></a>
<?php } ?>
</nav>
<main id="main">
<header class="print-mast"><b>RU</b> Rizing University</header>
<div class="context"><?= $workspaceName ?> <span>Academic year <?= h(SCHOOL_YEAR) ?></span></div>
<h1><?= h($pageTitle) ?></h1>
<?php render_notices(); ?>
