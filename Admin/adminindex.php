<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
require_once __DIR__ . '/includes/queue_scope.php';
$year = SCHOOL_YEAR;
$semester = registrar_default_semester(term_windows($conn));
$stmt = $conn->prepare("SELECT COUNT(*) FROM student_subject WHERE approved=0 AND school_year=? AND (?='' OR semester=?)");
$stmt->bind_param('sss', $year, $semester, $semester);
$stmt->execute();
$pendingForms = (int)$stmt->get_result()->fetch_row()[0];

$pageTitle = 'Registrar overview';
require __DIR__ . '/../includes/workspace_top.php';
?>
<section class="feature"><h2><?= $pendingForms ?> pending <?= $pendingForms === 1 ? 'form' : 'forms' ?></h2>
<p><?= h($year) ?> · <?= h($semester ?: 'All semesters') ?></p>
<p><?= $pendingForms ? 'Review submitted subjects and units before approving each enrollment.' : 'Your approval queue is clear.' ?></p>
<a class="btn" href="approvals.php">Review pending forms</a></section>
<h2>Registrar tasks</h2>
<div class="term"><strong>Enrollment calendar</strong><span>Registration, adjustment and closing dates</span><a href="calendar.php">Manage dates</a></div>
<div class="term"><strong>Student records</strong><span>Accounts, profiles and password resets</span><a href="userreg.php">Manage students</a></div>
<div class="term"><strong>Academic catalog</strong><span>Subject offerings and degree programs</span><a href="subjectreg.php">Manage subjects</a><a href="courses.php">Courses</a></div>
<div class="term"><strong>Enrollment report</strong><span>Submitted forms across semesters</span><a href="report.php">View report</a></div>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
