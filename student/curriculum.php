<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');

$accountID = (int)$_SESSION['userID'];
$stmt = $conn->prepare("SELECT fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
$stmt->bind_param("i", $accountID);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$subjects = null;
if ($student) {
	$stmt = $conn->prepare("SELECT subjectcode, subjecttitle, numofunits, islab, istutorial FROM subjecttbl WHERE course_con = ? ORDER BY subjectcode");
	$stmt->bind_param("s", $student['course']);
	$stmt->execute();
	$subjects = $stmt->get_result();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>John Paul College</title>

<link href="../assets/css/style.css" rel="stylesheet" type="text/css"/>

</head>

<body class="app">

<div class='header'>
</div>

<nav class='container'>
<?php
include('includes/menu.php');
?>
</nav>

<div id="main">
	<div class="shell">
	<center>
	<h2>Curriculum</h2>
	<?php if (!$student) { ?>
		<p>No student record is linked to this account yet.</p>
	<?php } else { ?>
		<p><b><?php echo h(trim($student['fname'] . ' ' . $student['minitial'] . ' ' . $student['lname'])); ?></b>
		&mdash; <?php echo h(strtoupper($student['course'])); ?>, <?php echo h($student['yearlvl']); ?></p>
		<table class="data-table" width="700">
			<tr>
				<th align="left">Code</th>
				<th align="left">Title</th>
				<th>Units</th>
				<th>Lab</th>
				<th>Tutorial</th>
			</tr>
			<?php
			$total = 0;
			if ($subjects->num_rows == 0) { ?>
				<tr><td colspan="5" align="center">No subjects have been added for this course yet.</td></tr>
			<?php }
			while ($sub = $subjects->fetch_assoc()) {
				$total += (int)$sub['numofunits']; ?>
				<tr>
					<td><?php echo h($sub['subjectcode']); ?></td>
					<td><?php echo h($sub['subjecttitle']); ?></td>
					<td align="center"><?php echo h($sub['numofunits']); ?></td>
					<td align="center"><?php echo $sub['islab'] ? 'Yes' : '-'; ?></td>
					<td align="center"><?php echo $sub['istutorial'] ? 'Yes' : '-'; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="2"><b>Total units</b></td>
				<td align="center"><b><?php echo $total; ?></b></td>
				<td colspan="2"></td>
			</tr>
		</table>
	<?php } ?>
	</center>
	</div>
	<div><br></div>
</div>
<!-- /Main -->

<!-- Footer -->
<div id="footer">
	<div class="shell">
		<!-- Mini Nav -->
		<div class="footer-navigation">
		<?php
			include('includes/menu2.php');
		?>
		</div>
		<!-- /Mini Nav -->

		<!-- Copyrights -->
		<p class="right">
			Copyright <?php echo date("Y"); ?> | Online Enrollment
		</p>
		<!-- /Copyrights -->

		<div class="cl">&nbsp;</div>
	</div>
</div>

</body>
</html>
