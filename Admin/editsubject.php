<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/includes/subject_save.php');

$courses = array_column($conn->query("SELECT course_code FROM courses_tbl WHERE is_offered=1 ORDER BY course_code")->fetch_all(MYSQLI_ASSOC), 'course_code');
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM subjecttbl WHERE ID = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
	notify_user('That subject was not found.');
	redirect_to('subjectreg.php');
}
// Allow an existing historical record to retain its own retired program.
if (!in_array($row['course_con'], $courses, true)) { $courses[] = $row['course_con']; }
$oldCode = $row['subjectcode'];
$v = ['semester' => $row['semester'], 'course' => $row['course_con'], 'scode' => $row['subjectcode'], 'stitle' => $row['subjecttitle'], 'cdescription' => $row['description'],
      'numunits' => (int)$row['numofunits'], 'sroom' => $row['room'], 'stime' => $row['time'], 'sday' => $row['day'],
      'sinstructor' => $row['instructor'], 'islab' => (int)$row['islab'], 'istutorial' => (int)$row['istutorial']];

if (isset($_POST['save'])) {
	$v = subject_input();
	$error = subject_validate($v, $courses);
	if (!csrf_valid()) { $error = 'Your session expired. Please try again.'; }
	// Enrollment forms store subject codes as text, so a code that is already used in a form cannot be renamed.
	if ($error === '' && $v['scode'] !== $oldCode) {
		$c = $conn->prepare("SELECT COUNT(*) FROM student_subject WHERE FIND_IN_SET(?, subjects) > 0");
		$c->bind_param("s", $oldCode);
		$c->execute();
		$c->bind_result($used);
		$c->fetch();
		$c->close();
		if ($used > 0) { $error = "The code $oldCode is used in $used enrollment form(s), so it cannot be changed. You can still change the other details."; }
	}
	if ($error === '') {
		try {
			$units = (string)$v['numunits'];
			$islab = $v['islab']; $istut = $v['istutorial'];
			$stmt = $conn->prepare("UPDATE subjecttbl SET subjectcode = ?, subjecttitle = ?, numofunits = ?, room = ?, time = ?, instructor = ?, day = ?,
				islab = ?, istutorial = ?, description = ?, course_con = ?, semester = ? WHERE ID = ?");
			$stmt->bind_param("sssssssiisssi", $v['scode'], $v['stitle'], $units, $v['sroom'], $v['stime'], $v['sinstructor'], $v['sday'], $islab, $istut, $v['cdescription'], $v['course'], $v['semester'], $id);
			$stmt->execute();
			notify_user('Successfully updated the subject', true);
			redirect_to('subjectreg.php');
		} catch (mysqli_sql_exception $e) {
			$error = ($e->getCode() == 1062) ? 'That subject code already exists.' : 'The subject could not be saved.';
		}
	}
	notify_user($error);
}

$pageTitle = 'Edit subject';
require __DIR__ . '/../includes/workspace_top.php';
?>


	<form class='cmxform' method='POST'>
		<input type="hidden" name="id" value="<?php echo (int)$id; ?>">
		<?php include(__DIR__ . '/includes/subject_form.php'); ?>
		<div class="actions"><input type='submit' name='save' value='Save changes'>&nbsp;&nbsp;
		<a href="subjectreg.php">Cancel</a></div>
	</form>

<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
