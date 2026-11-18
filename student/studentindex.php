<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
$stmt = $conn->prepare("SELECT studnum, fname, course, yearlvl FROM studenttbl WHERE accountID = ?");
$stmt->bind_param("i", $_SESSION['userID']);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$forms = [];
if ($record) {
    $year = SCHOOL_YEAR;
    $stmt = $conn->prepare("SELECT semester, approved FROM student_subject WHERE studentID = ? AND school_year = ?");
    $stmt->bind_param("ss", $record['studnum'], $year);
    $stmt->execute();
    $forms = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'approved', 'semester');
}
$pageTitle = 'Enrollment overview';
require __DIR__ . '/../includes/workspace_top.php';
?>
<?php if (!$record) { ?>
<p>Ask the registrar to link your student record before enrolling.</p>
<?php } else { ?>
<section class="feature"><h2><?= h($record['fname']) ?>’s enrollment</h2>
<p><?= h(strtoupper($record['course'])) ?> · Year and section <?= h($record['yearlvl']) ?></p>
<a class="btn" href="report.php">View enrollment records</a></section>
<h2>Semester status</h2>
<?php foreach (SEMESTERS as $semester) {
    $submitted = array_key_exists($semester, $forms); ?>
<div class="term"><strong><?= h($semester) ?></strong><span><?= $submitted ? ($forms[$semester] ? 'Approved' : 'Pending approval') : 'No form submitted' ?></span>
<?php if ($submitted) { ?><a href="report.php">View form</a><?php } else { ?><a href="enroll.php?sem=<?= rawurlencode($semester) ?>">Start enrollment</a><?php } ?></div>
<?php } } ?>
<h2>How to Enroll</h2><p>Check your <a href="curriculum.php">curriculum</a>, select subjects in <a href="enroll.php">Enroll Now</a>, then review the fees and submit your form for approval.</p>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
