<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
$pendingForms = (int)$conn->query("SELECT COUNT(*) FROM student_subject WHERE approved = 0")->fetch_row()[0];

$pageTitle = 'Registrar overview';
require __DIR__ . '/../includes/workspace_top.php';
?>
<section class="feature"><h2><?= $pendingForms ?> pending <?= $pendingForms === 1 ? 'form' : 'forms' ?></h2>
<p><?= $pendingForms ? 'Review submitted subjects and units before approving each enrollment.' : 'Your approval queue is clear.' ?></p>
<a class="btn" href="approvals.php">Open approvals</a></section>
<h2>Registrar tasks</h2>
<div class="term"><strong>Student records</strong><span>Accounts, profiles and password resets</span><a href="userreg.php">Manage students</a></div>
<div class="term"><strong>Academic catalog</strong><span>Subject offerings and degree programs</span><a href="subjectreg.php">Manage subjects</a><a href="courses.php">Courses</a></div>
<div class="term"><strong>Enrollment report</strong><span>Submitted forms across semesters</span><a href="report.php">View report</a></div>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
