<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/includes/student_form_lib.php');

$courses = array_column($conn->query("SELECT course_code FROM courses_tbl ORDER BY course_code")->fetch_all(MYSQLI_ASSOC), 'course_code');
$v = ['password' => ''];
$isEdit = false;

if (isset($_POST['register'])) {
	foreach (STUDENT_FIELDS as $k) { $v[$k] = trim($_POST[$k] ?? ''); }
	$password = trim($_POST['password'] ?? '');
	$v['password'] = $password;
	$fullname = trim($v['fname'] . " " . $v['minitial'] . " " . $v['lname']);

	if (!csrf_valid()) {
		notify_user('Your session expired. Please try again.');
	} elseif ($v['username'] === '' || $password === '' || $v['snum'] === '') {
		notify_user('Username, password and student number are required.');
	} elseif (!in_array($v['course'], $courses, true)) {
		notify_user('Please select a valid course.');
	} elseif (($err = student_validate($v)) !== '') {
		notify_user($err);
	} else {
		try {
			$conn->begin_transaction();

			$stmt = $conn->prepare("INSERT INTO usertbl (username, password, fullname, user_type) VALUES (?, ?, ?, 'student')");
			$passwordHash = password_make($password);
			$stmt->bind_param("sss", $v['username'], $passwordHash, $fullname);
			$stmt->execute();
			$accountID = $conn->insert_id;

			$stmt2 = $conn->prepare("INSERT INTO studenttbl
				(accountID, studnum, fname, minitial, lname, address, contact, emailaddress, birthdate, course, yearlvl, gender,
				 fathername, mothername, guardianname, birthplace, civilstatus, elemschool, dategradelem, hsschool, datehsgrad,
				 lastgrad, datelastgrad, addresslastgrad)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt2->bind_param("isssssssssssssssssssssss", $accountID, $v['snum'], $v['fname'], $v['minitial'], $v['lname'],
				$v['address'], $v['cnumber'], $v['email'], $v['bdate'], $v['course'], $v['yrlvlsec'], $v['gender'],
				$v['fathername'], $v['mothername'], $v['guardianname'], $v['bplace'], $v['cstatus'], $v['elemschool'],
				$v['yrgradelem'], $v['hschool'], $v['yrgradhs'], $v['lastschool'], $v['yrlast'], $v['address2']);
			$stmt2->execute();

			$conn->commit();
			notify_user('Successfully Created new account', true);
			redirect_to('userreg.php');
		} catch (mysqli_sql_exception $e) {
			$conn->rollback();
			$msg = $e->getMessage();
			if (strpos($msg, 'uq_user_username') !== false) {
				notify_user('That username is already taken.');
			} elseif (strpos($msg, 'uq_student_studnum') !== false) {
				notify_user('That student number is already registered.');
			} else {
				notify_user('The account could not be saved. Please check the details and try again.');
			}
		}
	}
}

$pageTitle = 'Add student';
require __DIR__ . '/../includes/workspace_top.php';
?>


	<form class='cmxform' method='POST'>
		<?php include(__DIR__ . '/includes/student_form.php'); ?>
		<div class="actions"><input type='submit' name='register' value='Add new user'>&nbsp;&nbsp;
		<a href="userreg.php">Cancel</a></div>
	</form>

<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
