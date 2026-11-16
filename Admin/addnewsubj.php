<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

$courses = $conn->query("SELECT course_code FROM courses_tbl ORDER BY course_code")->fetch_all(MYSQLI_ASSOC);

if (isset($_POST['register'])) {
	$course       = trim($_POST['course'] ?? '');
	$scode        = trim($_POST['scode'] ?? '');
	$stitle       = trim($_POST['stitle'] ?? '');
	$sdescription = trim($_POST['cdescription'] ?? '');
	$numunits     = (int)($_POST['numunits'] ?? 0);
	$sroom        = trim($_POST['sroom'] ?? '');
	$stime        = trim($_POST['stime'] ?? '');
	$sday         = trim($_POST['sday'] ?? '');
	$sinstructor  = trim($_POST['sinstructor'] ?? '');
	$islab        = isset($_POST['islab']) ? 1 : 0;
	$istutorial   = isset($_POST['istutorial']) ? 1 : 0;

	$validCourses = array_column($courses, 'course_code');
	$error = '';
	if ($scode === '' || $stitle === '') {
		$error = 'Subject code and subject title are required.';
	} elseif ($numunits < 1 || $numunits > 6) {
		$error = 'Number of units must be from 1 to 6.';
	} elseif (!in_array($course, $validCourses, true)) {
		$error = 'Please select a valid course.';
	}

	if ($error === '') {
		try {
			$units = (string)$numunits;
			$stmt = $conn->prepare("INSERT INTO subjecttbl
				(subjectcode, subjecttitle, numofunits, room, time, instructor, day, islab, istutorial, description, course_con)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("sssssssiiss", $scode, $stitle, $units, $sroom, $stime, $sinstructor, $sday, $islab, $istutorial, $sdescription, $course);
			$stmt->execute();
			js_alert('Successfully Created new Subject');
			js_redirect('addnewsubj.php');
			exit;
		} catch (mysqli_sql_exception $e) {
			$error = ($e->getCode() == 1062) ? 'That subject code already exists.' : 'The subject could not be saved.';
		}
	}
	js_alert($error);
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
include('includes/menu1.php');
?>
</nav>





<div id="main">
	<div class="shell">
	<center>
	<form class='cmxform' method='POST'>
	<fieldset>
			<legend>Course Details</legend>
			  <ol>
			  <li>
				  <label for="name">Select Course:<em>*</em></label>
				  <select name='course'>
					<?php foreach ($courses as $c) { ?>
					<option value='<?php echo h($c['course_code']); ?>'><?php echo h(strtoupper($c['course_code'])); ?></option>
					<?php } ?>
				  </select>
				
  </ol>
			
	</fieldset>
	
	<fieldset>	
			  <legend>Subject Details</legend>
			  <ol>
			  <li>
					<table>
						<tr>
							<td>Subject Code:</td><td align='left'><input type='text' required='required' maxlength='50' id="name" name='scode' /></td>
						</tr>
						<tr>
							<td>Subject Title:</td><td align='left'><input type='text' required='required' id="name" name='stitle' /></td>
						</tr>
						<tr>
							<td>Number of Units:</td><td align='left'><select name='numunits'>
														<option value='1'>1</option>
														<option value='2'>2</option>
														<option value='3'>3</option>
														<option value='4'>4</option>
														<option value='5'>5</option>
														<option value='6'>6</option></select></td>
						</tr>
						<tr>
							<td><label>Subject Description:</label></td><td align='left'><textarea name='cdescription'></textarea></td>
						</tr>
						<tr>
							<td><label>Room:</label></td><td align='left'><input type='text' required='required' id="name" name='sroom' /></td>
						</tr>
						<tr>
							<td><label>Day:</label></td><td align='left'><input type='text' required='required' id="name" name='sday' /></td>
						</tr>
						<tr>
							<td><label>Time:</label></td><td align='left'><input type='text' required='required' id="name" name='stime' /></td>
						</tr>
						<tr>
							<td><label>Instructor:</label></td><td align='left'><input type='text' required='required' id="name" name='sinstructor' /></td>
						</tr>
						<tr>
							<td><label>With Lab:</label></td><td align='left'><input type='checkbox' name='islab'></td>
						</tr>
						<tr>
							<td><label>With Tutorial:</label></td><td align='left'><input type='checkbox' name='istutorial'></td>
						</tr>
					</table>
					
				</li>
						
				
			 
			  
			  
  </ol>
</fieldset>
<div align='center'><input type='submit' name='register' value='Add new subject'>&nbsp;&nbsp;
<input type='reset' name='clear' value='Clear'></div>
</form>
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
			Copyright 2014 | Online Enrollment
		</p> 
		<!-- /Copyrights --> 
		
		<div class="cl">&nbsp;</div> 
	</div>
</div>




</body>
</html>
