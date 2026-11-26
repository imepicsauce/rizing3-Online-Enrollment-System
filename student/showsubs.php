<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/enrollment_calendar.php');

// This page only works when reached from the enrollment form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: enroll.php');
	exit;
}

function back_to_enroll($msg) {
	notify_user($msg);
	redirect_to('enroll.php');
}

if (!csrf_valid()) {
	back_to_enroll('Your session expired. Please choose your subjects again.');
}

$stmt = $conn->prepare("SELECT studnum, fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['userID']);
$stmt->execute();
$rows = $stmt->get_result()->fetch_assoc();
if (!$rows) {
	back_to_enroll('Your student profile was not found. Please contact the administrator.');
}

$schoolYear = SCHOOL_YEAR;
$sem = (string)($_POST['sem'] ?? '');
if (!in_array($sem, SEMESTERS, true)) {
	back_to_enroll('Please choose a semester.');
}

if (!enrollment_open(term_windows($conn)[$sem] ?? null)) {
    back_to_enroll('Enrollment is closed for this semester. Check the enrollment calendar for published dates.');
}

// One enrollment form per student per semester and school year.
$chk = $conn->prepare("SELECT approved FROM student_subject WHERE studentID = ? AND semester = ? AND school_year = ? LIMIT 1");
$chk->bind_param("sss", $rows['studnum'], $sem, $schoolYear);
$chk->execute();
$existing = $chk->get_result()->fetch_assoc();
$chk->close();
if ($existing) {
	if ((int)$existing['approved'] === 1) {
		back_to_enroll("You already have an approved enrollment form for $sem, $schoolYear. Please contact the administrator if it needs to change.");
	}
	back_to_enroll("You already submitted an enrollment form for $sem, $schoolYear, and it is waiting for approval. Cancel it in \"My Enrollment Report\" if you want to submit a different one.");
}

$codes = $_POST['subject'] ?? [];
$codes = is_array($codes) ? array_values(array_unique(array_map('strval', $codes))) : [];
if (count($codes) === 0) {
	back_to_enroll('Please tick at least one subject.');
}

// Everything on the form is re-read from the database. Nothing about units or fees is trusted from the browser.
$subs = [];
$stmt = $conn->prepare("SELECT subjectcode, subjecttitle, numofunits, room, day, time, COALESCE(u.fullname, s.instructor) AS instructor, islab, istutorial FROM subjecttbl s LEFT JOIN faculty_subject f ON f.subjectID=s.ID LEFT JOIN usertbl u ON u.ID=f.facultyID WHERE subjectcode = ? AND course_con = ? AND course_con IN (SELECT course_code FROM courses_tbl WHERE is_offered=1) AND (s.semester = ? OR s.semester = '')");
foreach ($codes as $code) {
	$stmt->bind_param("sss", $code, $rows['course'], $sem);
	$stmt->execute();
	$sub = $stmt->get_result()->fetch_assoc();
	if (!$sub) {
		back_to_enroll('One of the chosen subjects is not offered for your course. Please choose again.');
	}
	$subs[] = $sub;
}
usort($subs, fn($a, $b) => strcmp($a['subjectcode'], $b['subjectcode']));
$codes = array_column($subs, 'subjectcode');
$subjectList = implode(',', $codes);

$totalunits = 0;
$extras = 0;
foreach ($subs as $s) {
	$totalunits += (int)$s['numofunits'];
	if ((int)$s['islab'] === 1) { $extras += LAB_FEE; }
	if ((int)$s['istutorial'] === 1) { $extras += TUTORIAL_FEE; }
}
$total = ($totalunits * PER_UNIT_FEE) + ENTRANCE_FEE + INTERNET_FEE + MISC_FEE + $extras;

// Step 2: the student pressed "Submit Enrollment Form".
if (isset($_POST['reg'])) {
	$stid = $rows['studnum'];
	try {
		$ins = $conn->prepare("INSERT INTO student_subject (studentID, subjects, totalunits, approved, semester, school_year) SELECT ?, ?, ?, 0, semester, school_year FROM enrollment_window
            WHERE semester=? AND school_year=? AND ? BETWEEN starts AND ends");
		$today = enrollment_today();
        $ins->bind_param("ssisss", $stid, $subjectList, $totalunits, $sem, $schoolYear, $today);
		$ins->execute();
        if ($ins->affected_rows !== 1) {
            back_to_enroll('Enrollment has closed for this semester. Your form was not submitted.');
        }
	} catch (mysqli_sql_exception $e) {
		if ($e->getCode() !== 1062) { throw $e; }
        // The database allows only one form per semester.
		back_to_enroll("You already have an enrollment form for $sem, $schoolYear.");
	}
	notify_user('Enrollment form was successfully sent to the system', true);
	redirect_to('studentindex.php');
}

function money($n) { return number_format($n, 2); }

$pageTitle = 'Review enrollment';
require __DIR__ . '/../includes/workspace_top.php';
?>
<p><strong>Unofficial enrollment form</strong> · <?= h($sem) ?> · <?= h($schoolYear) ?></p>
<dl><div><dt>Student</dt><dd><?= h(trim($rows['fname'].' '.$rows['minitial'].' '.$rows['lname'])) ?></dd></div><div><dt>Program</dt><dd><?= h(strtoupper($rows['course'])) ?></dd></div><div><dt>Year and section</dt><dd><?= h($rows['yearlvl']) ?></dd></div></dl>
<h2>Selected subjects</h2>
<div class="scroll"><table><thead><tr><th>Subject</th><th>Units</th><th>Room</th><th>Day / time</th><th>Instructor</th><th>Tuition</th><th>Lab</th><th>Tutorial</th></tr></thead><tbody>
<?php foreach ($subs as $s) { ?><tr><td class="review-subject"><strong><?= h($s['subjecttitle']) ?></strong><small><?= h($s['subjectcode']) ?></small></td><td><?= (int)$s['numofunits'] ?></td><td><?= h($s['room'] ?: '—') ?></td><td><?= h(trim($s['day'].' '.$s['time']) ?: '—') ?></td><td><?= h($s['instructor'] ?: '—') ?></td><td>₱<?= money($s['numofunits'] * PER_UNIT_FEE) ?></td><td><?= $s['islab'] ? '₱'.money(LAB_FEE) : '—' ?></td><td><?= $s['istutorial'] ? '₱'.money(TUTORIAL_FEE) : '—' ?></td></tr><?php } ?>
</tbody></table></div>
<section class="fee-review"><div>
<h2>Fee breakdown</h2>
<dl><?php foreach (['Tuition (₱'.money(PER_UNIT_FEE).' per unit)'=>$totalunits*PER_UNIT_FEE,'Entrance'=>ENTRANCE_FEE,'Internet'=>INTERNET_FEE,'Miscellaneous'=>MISC_FEE,'Lab and tutorial'=>$extras] as $label=>$amount) { ?><div><dt><?= h($label) ?></dt><dd>₱<?= money($amount) ?></dd></div><?php } ?></dl>
</div><div class="fee-decision">
<h2>Enrollment total</h2><p><?= count($subs) ?> <?= count($subs) === 1 ? 'subject' : 'subjects' ?> · <?= $totalunits ?> units</p>
<strong class="fee-total">₱<?= money($total) ?></strong>
<p>Your form goes to the registrar for approval.</p>
<form method="post" class="fee-actions no-print" action="showsubs.php">
<?= csrf_field() ?><input type="hidden" name="sem" value="<?= h($sem) ?>">
<?php foreach ($codes as $c) { ?><input type="hidden" name="subject[]" value="<?= h($c) ?>"><?php } ?>
<input type="submit" name="reg" value="Submit enrollment form">
<button class="secondary" formaction="enroll.php">Edit selection</button>
<button type="button" class="secondary" onclick="window.print()">Print</button>
</form></div></section>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
