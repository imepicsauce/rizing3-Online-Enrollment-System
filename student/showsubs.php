<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../config/settings.php');

// This page only works when reached from the enrollment form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: enroll.php');
	exit;
}

function back_to_enroll($msg) {
	js_alert($msg);
	js_redirect('enroll.php');
	exit;
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
$stmt = $conn->prepare("SELECT subjectcode, numofunits, room, day, time, instructor, islab, istutorial FROM subjecttbl WHERE subjectcode = ? AND course_con = ?");
foreach ($codes as $code) {
	$stmt->bind_param("ss", $code, $rows['course']);
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
		$ins = $conn->prepare("INSERT INTO student_subject (studentID, subjects, totalunits, approved, semester, school_year) VALUES (?, ?, ?, 0, ?, ?)");
		$ins->bind_param("ssiss", $stid, $subjectList, $totalunits, $sem, $schoolYear);
		$ins->execute();
	} catch (mysqli_sql_exception $e) {
		// Two quick clicks: the database allows only one form per semester.
		back_to_enroll("You already have an enrollment form for $sem, $schoolYear.");
	}
	js_alert('Enrollment form was successfully sent to the system');
	js_redirect('studentindex.php');
	exit;
}

function money($n) { return number_format($n, 2); }
?>
<!DOCTYPE html>
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Enrollment Form</title>
	<style>@media print { .no-print { display: none; } }</style>
	</head>

	<body>
		<div align='center'><img width='130' height='100' src='../assets/img/logo-login.jpg'></div>
		<div align='center' style='margin-top: 20px; font-family: Georgia, "Times New Roman", Times, serif;'>John Paul College<br>MG ANDAYA COMPOUND<br>Odiong, Roxas, Oriental Mindoro, Philippines</div><br>
		<div align='center' style='margin-top: 20px; font-family: Georgia, "Times New Roman", Times, serif;'>UNOFFICIAL ENROLLMENT FORM</div>
		<div align='center' style='margin-top: 0px; font-family: Georgia, "Times New Roman", Times, serif;'><?php echo h($sem); ?> School Year <?php echo h(SCHOOL_YEAR); ?></div>
		<div align='center' style='margin-top:30px;'>
			<b>Student Name: </b><?php echo h($rows['fname'] . ' ' . $rows['minitial'] . ' ' . $rows['lname']); ?> ||
			<b>Course: </b><?php echo h($rows['course']); ?> ||
			<b>Year Level: </b><?php echo h($rows['yearlvl']); ?>

			<table border="2" align='center' width='800'>
				<tr>
					<td align='left' colspan='9'><b>ENTRANCE FEE</b></td>
					<td align='right'><?php echo money(ENTRANCE_FEE); ?></td>
				</tr>
				<tr>
					<td align="center"><b>Subject</b></td>
					<td align="center"><b>Number of Units</b></td>
					<td align="center"><b>Per Unit</b></td>
					<td align="center"><b>Tutorial</b></td>
					<td align="center"><b>Lab</b></td>
					<td align="center"><b>Room</b></td>
					<td align="center"><b>Day</b></td>
					<td align="center"><b>Time</b></td>
					<td align="center"><b>Instructor</b></td>
					<td align="center"><b>Total</b></td>
				</tr>
				<?php foreach ($subs as $s) { ?>
				<tr>
					<td align='center'><?php echo h($s['subjectcode']); ?></td>
					<td align='center'><?php echo h($s['numofunits']); ?></td>
					<td align='center'><?php echo PER_UNIT_FEE; ?></td>
					<td align='center'><?php echo ((int)$s['istutorial'] === 1) ? TUTORIAL_FEE : ''; ?></td>
					<td align='center'><?php echo ((int)$s['islab'] === 1) ? LAB_FEE : ''; ?></td>
					<td align='center'><?php echo h($s['room']); ?></td>
					<td align='center'><?php echo h($s['day']); ?></td>
					<td align='center'><?php echo h($s['time']); ?></td>
					<td align='center'><?php echo h($s['instructor']); ?></td>
					<td align='right'><?php echo money((int)$s['numofunits'] * PER_UNIT_FEE); ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td align='left' colspan='9'><b>INTERNET</b></td><td align='right'><?php echo money(INTERNET_FEE); ?></td>
				</tr>
				<tr>
					<td align='left' colspan='9'><b>MISC</b></td><td align='right'><?php echo money(MISC_FEE); ?></td>
				</tr>
				<?php if ($extras > 0) { ?>
				<tr>
					<td align='left' colspan='9'><b>LAB / TUTORIAL</b></td><td align='right'><?php echo money($extras); ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td align='left'><b>Total units</b></td>
					<td colspan='8' align='left'><b><?php echo (int)$totalunits; ?></b></td>
					<td align='right' bgcolor='#00EE00'><b>P&nbsp;&nbsp;<?php echo money($total); ?></b></td>
				</tr>
			</table>

			<form method="POST" class="no-print" action="showsubs.php">
				<?php echo csrf_field(); ?>
				<input type='hidden' name='sem' value="<?php echo h($sem); ?>">
				<?php foreach ($codes as $c) { echo "<input type='hidden' name='subject[]' value=\"" . h($c) . "\">"; } ?>
				<div style='margin-top:20px;'>
					<input type='submit' name='reg' value='Submit Enrollment Form' onclick="return confirm('Are you sure? The form will be submitted to the system.');">
					<input type='button' value='Print' onclick='window.print()'>
					<input type='button' value='Back' onclick="window.location='enroll.php'">
				</div>
			</form>
		</div>
	</body>
</html>
