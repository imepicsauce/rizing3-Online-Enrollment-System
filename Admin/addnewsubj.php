<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/includes/subject_save.php');

$courses = array_column($conn->query("SELECT course_code FROM courses_tbl WHERE is_offered=1 ORDER BY course_code")->fetch_all(MYSQLI_ASSOC), 'course_code');
$v = ['numunits' => 3];

if (isset($_POST['register'])) {
	$v = subject_input();
	$error = subject_validate($v, $courses);
	if (!csrf_valid()) { $error = 'Your session expired. Please try again.'; }
	if ($error === '') {
		try {
			$units = (string)$v['numunits'];
			$islab = $v['islab']; $istut = $v['istutorial'];
			$stmt = $conn->prepare("INSERT INTO subjecttbl
				(subjectcode, subjecttitle, numofunits, room, time, instructor, day, islab, istutorial, description, course_con, semester)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("sssssssiisss", $v['scode'], $v['stitle'], $units, $v['sroom'], $v['stime'], $v['sinstructor'], $v['sday'], $islab, $istut, $v['cdescription'], $v['course'], $v['semester']);
			$stmt->execute();
			notify_user('Successfully Created new Subject', true);
			redirect_to('subjectreg.php');
		} catch (mysqli_sql_exception $e) {
			$error = ($e->getCode() == 1062) ? 'That subject code already exists.' : 'The subject could not be saved.';
		}
	}
	notify_user($error);
}

$pageTitle = 'Add subject';
require __DIR__ . '/../includes/workspace_top.php';
?>


	<form class='cmxform' method='POST'>
		<?php include(__DIR__ . '/includes/subject_form.php'); ?>
		<div class="actions"><input type='submit' name='register' value='Add new subject'>&nbsp;&nbsp;
		<a href="subjectreg.php">Cancel</a></div>
	</form>

<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
