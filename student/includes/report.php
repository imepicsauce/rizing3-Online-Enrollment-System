<?php
session_start();

if (!isset($_SESSION['logcheck']) || ($_SESSION['type'] ?? '') !== 'student') {
	header('Location: ../../index.php');
	exit;
}

include('conn.php');

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// This student's record
$accountID = (string)($_SESSION['userID'] ?? '');
$stmt = $conn->prepare("SELECT studnum, fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
$stmt->bind_param("s", $accountID);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Subject code => title lookup, so the report shows names and not just codes
$titles = [];
$subRes = $conn->query("SELECT subjectcode, subjecttitle FROM subjecttbl");
while ($r = $subRes->fetch_assoc()) {
	$titles[$r['subjectcode']] = $r['subjecttitle'];
}

// This student's submitted enrollment forms
$forms = null;
if ($student) {
	$stmt2 = $conn->prepare("SELECT ID, subjects, totalunits, approved FROM student_subject WHERE studentID = ? ORDER BY ID DESC");
	$stmt2->bind_param("s", $student['studnum']);
	$stmt2->execute();
	$forms = $stmt2->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>My Enrollment Report</title>
<style>
	body { font-family: Georgia, "Times New Roman", Times, serif; }
	table { border-collapse: collapse; }
	th, td { border: 1px solid #333; padding: 6px 8px; }
	@media print { .no-print { display: none; } }
</style>
</head>

<body>

	<div class="no-print" align="center" style="margin:10px;">
		<a href="../studentindex.php">&larr; Back to Home</a> &nbsp;|&nbsp;
		<button onclick="window.print()">Print</button>
	</div>

	<div align="center"><img width="130" height="100" src="logo.jpg"></div>
	<div align="center" style="margin-top:20px;">
		John Paul College<br>MG ANDAYA COMPOUND<br>Odiong, Roxas, Oriental Mindoro, Philippines
	</div>
	<br>
	<div align="center"><b>MY ENROLLMENT REPORT</b></div>

	<?php if (!$student) { ?>
		<p align="center">No student record is linked to this account yet.</p>
	<?php } else { ?>
	<div align="center" style="margin-top:15px;">
		<b>Student:</b> <?php echo h(trim($student['fname'] . ' ' . $student['minitial'] . ' ' . $student['lname'])); ?>
		&nbsp;||&nbsp; <b>Student No.:</b> <?php echo h($student['studnum']); ?>
		&nbsp;||&nbsp; <b>Course:</b> <?php echo h(strtoupper($student['course'])); ?>
		&nbsp;||&nbsp; <b>Year Level:</b> <?php echo h($student['yearlvl']); ?>
	</div>

	<div align="center" style="margin-top:30px;">
		<table align="center" width="800">
			<tr>
				<td width="60" align="center"><b>Form #</b></td>
				<td width="500" align="center"><b>Subjects</b></td>
				<td width="80" align="center"><b>Units</b></td>
				<td width="100" align="center"><b>Status</b></td>
			</tr>
			<?php if ($forms->num_rows == 0) { ?>
				<tr><td colspan="4" align="center">You have not submitted an enrollment form yet.</td></tr>
			<?php } ?>
			<?php while ($f = $forms->fetch_assoc()) {
				$codes = array_filter(array_map('trim', explode(',', $f['subjects'])));
				$lines = [];
				foreach ($codes as $c) {
					$lines[] = h($c) . (isset($titles[$c]) ? ' - ' . h($titles[$c]) : '');
				}
				$status = ($f['approved'] == '0') ? 'Pending' : 'Approved';
			?>
			<tr>
				<td align="center"><?php echo h($f['ID']); ?></td>
				<td><?php echo implode('<br>', $lines); ?></td>
				<td align="center"><?php echo h($f['totalunits']); ?></td>
				<td align="center"><?php echo $status; ?></td>
			</tr>
			<?php } ?>
		</table>
	</div>
	<?php } ?>

</body>
</html>
