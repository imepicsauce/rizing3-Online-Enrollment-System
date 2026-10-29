<?php
session_start();

// Admins only
if (($_SESSION['type'] ?? '') !== 'admin') {
	header('Location: ../../index.php');
	exit;
}

include('conn.php');

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$result = $conn->query("
	SELECT ss.ID, ss.studentID, ss.subjects, ss.totalunits, ss.approved,
	       s.fname, s.minitial, s.lname, s.course, s.yearlvl
	FROM student_subject ss
	LEFT JOIN studenttbl s ON s.studnum = ss.studentID
	ORDER BY ss.ID DESC
");
if (!$result) {
	die("Query error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Enrollment Report</title>
<style>
	body { font-family: Georgia, "Times New Roman", Times, serif; }
	table { border-collapse: collapse; }
	th, td { border: 1px solid #333; padding: 6px 8px; }
	@media print { .no-print { display: none; } }
</style>
</head>

<body>

	<div class="no-print" align="center" style="margin:10px;">
		<a href="../adminindex.php">&larr; Back to Home</a> &nbsp;|&nbsp;
		<button onclick="window.print()">Print</button>
	</div>

	<div align="center"><img width="130" height="100" src="logo.jpg"></div>
	<div align="center" style="margin-top:20px;">
		John Paul College<br>MG ANDAYA COMPOUND<br>Odiong, Roxas, Oriental Mindoro, Philippines
	</div>
	<br>
	<div align="center"><b>ENROLLMENT REPORT</b></div>
	<div align="center" style="font-size:13px;">Generated <?php echo date('F j, Y g:i A'); ?></div>

	<div align="center" style="margin-top:30px;">
		<table align="center" width="1000">
			<tr>
				<td width="40" align="center"><b>#</b></td>
				<td width="110" align="center"><b>Student No.</b></td>
				<td width="220" align="center"><b>Student Name</b></td>
				<td width="120" align="center"><b>Course / Year</b></td>
				<td width="280" align="center"><b>Subjects</b></td>
				<td width="60" align="center"><b>Units</b></td>
				<td width="90" align="center"><b>Status</b></td>
			</tr>
			<?php if ($result->num_rows == 0) { ?>
				<tr><td colspan="7" align="center">No enrollment forms have been submitted yet.</td></tr>
			<?php } ?>
			<?php
			$totalForms = 0;
			while ($row = $result->fetch_assoc()) {
				$totalForms++;
				$subjects = implode(', ', array_filter(array_map('trim', explode(',', $row['subjects']))));
				$name = trim($row['fname'] . ' ' . $row['minitial'] . ' ' . $row['lname']);
				if ($name === '') { $name = '(student record not found)'; }
				$status = ($row['approved'] == '0') ? 'Pending' : 'Approved';
			?>
			<tr>
				<td align="center"><?php echo h($row['ID']); ?></td>
				<td><?php echo h($row['studentID']); ?></td>
				<td><?php echo h($name); ?></td>
				<td><?php echo h(strtoupper($row['course'] ?? '') . ' ' . ($row['yearlvl'] ?? '')); ?></td>
				<td><?php echo h($subjects); ?></td>
				<td align="center"><?php echo h($row['totalunits']); ?></td>
				<td align="center"><?php echo $status; ?></td>
			</tr>
			<?php } ?>
		</table>
		<p>Total enrollment forms: <b><?php echo $totalForms; ?></b></p>
	</div>

</body>
</html>
