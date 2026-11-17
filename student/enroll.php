<?php
	require_once(__DIR__ . '/../includes/auth.php');
	require_role('student');
	require_once(__DIR__ . '/../config/db.php');
	require_once(__DIR__ . '/../config/settings.php');

	$stmt = $conn->prepare("SELECT studnum, fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
	$stmt->bind_param("i", $_SESSION['userID']);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_assoc();
	if (!$rows) {
		js_alert('Your student profile was not found. Please contact the administrator.');
		js_redirect('studentindex.php');
		exit;
	}
	$_SESSION['studnum'] = $rows['studnum'];

	// Only the subjects of the student's own course can be chosen.
	$stmt = $conn->prepare("SELECT subjectcode, subjecttitle, description, numofunits FROM subjecttbl WHERE course_con = ? ORDER BY subjectcode");
	$stmt->bind_param("s", $rows['course']);
	$stmt->execute();
	$subjects = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>John Paul College</title>
<link href="../assets/css/style.css" rel="stylesheet" type="text/css"/>
</head>

<body class="app">

<div class='header'>
</div>
	<div align='left'>
<nav class='container'>
<?php include('includes/menu.php'); ?>
</nav>
</div>

<div id="main">
	<div class="shell">
	<center>
	<form class='cmxform' method='POST' action='showsubs.php' onsubmit="if(!document.querySelector('input[name=&quot;subject[]&quot;]:checked')){alert('Please tick at least one subject.');return false;}">
		<?php echo csrf_field(); ?>
		<fieldset>
			<legend>Student Details</legend>
			<ol>
			<li>
				<label><b>Year Level:</b> <?php echo h($rows['yearlvl']); ?> ||</label>
				<label><b>Course:</b> <?php echo h($rows['course']); ?></label>
				<label><b>Sem:</b></label>
				<select name='sem'>
					<?php foreach (SEMESTERS as $s) { echo "<option value='" . h($s) . "'>" . h($s) . "</option>"; } ?>
				</select>
			</li>
			</ol>
			<div><br></div>
		</fieldset>
		<fieldset>
			<legend>List of Subjects</legend>
			<ol>
			<li>
				<table border='1'>
					<thead>
						<tr>
							<th>Subject Code</th>
							<th>Subject Title</th>
							<th>Description</th>
							<th>Number of Units</th>
							<th>Select</th>
						</tr>
					</thead>
					<tbody>
					<?php if ($subjects->num_rows == 0) { ?>
						<tr><td colspan="5" align="center">No subjects have been added for your course yet.</td></tr>
					<?php }
					while ($s = $subjects->fetch_assoc()) { ?>
						<tr>
							<td width='120' align='center'><?php echo h($s['subjectcode']); ?></td>
							<td width='120' align='center'><?php echo h($s['subjecttitle']); ?></td>
							<td width='120' align='center'><?php echo h($s['description']); ?></td>
							<td width='120' align='center'><?php echo h($s['numofunits']); ?></td>
							<td width='120' align='center'><input type='checkbox' name='subject[]' value="<?php echo h($s['subjectcode']); ?>"></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<div><br></div>
			</li>
			</ol>
		</fieldset>
		<div align='center'><input type='submit' name='register' value='Enroll Selected'>&nbsp;&nbsp;
		<input type='reset' name='clear' value='Clear'></div>
	</form>
	</center>
	</div>
	<div><br></div>
</div>

<div id="footer">
	<div class="shell">
		<div class="footer-navigation">
		<?php include('includes/menu2.php'); ?>
		</div>
		<p class="right">Copyright 2014 | Online Enrollment</p>
		<div class="cl">&nbsp;</div>
	</div>
</div>

</body>
</html>
