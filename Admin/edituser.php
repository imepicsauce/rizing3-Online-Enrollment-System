<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/includes/student_form_lib.php');

$courses = array_column($conn->query("SELECT course_code FROM courses_tbl WHERE is_offered=1 ORDER BY course_code")->fetch_all(MYSQLI_ASSOC), 'course_code');
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT s.*, u.username FROM studenttbl s JOIN usertbl u ON u.ID = s.accountID WHERE s.ID = ? AND u.user_type = 'student' LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
	notify_user('That student account was not found.');
	redirect_to('userreg.php');
}
// Allow an existing historical record to retain its own retired program.
if (!in_array($row['course'], $courses, true)) { $courses[] = $row['course']; }
$accountID = (int)$row['accountID'];
$isEdit = true;
$fixedID = $row['username'] === $row['studnum'] &&
    (preg_match('/^[0-9]{2}-[0-9]{4}$/D', $row['studnum']) || $row['studnum'] === sprintf('RU-%08d', $row['ID']));

$v = [
	'username' => $row['username'], 'course' => $row['course'], 'yrlvlsec' => $row['yearlvl'], 'snum' => $row['studnum'],
	'fname' => $row['fname'], 'minitial' => $row['minitial'], 'lname' => $row['lname'], 'gender' => $row['gender'],
	'bdate' => $row['birthdate'], 'address' => $row['address'], 'cnumber' => $row['contact'], 'email' => $row['emailaddress'],
	'fathername' => $row['fathername'], 'mothername' => $row['mothername'], 'guardianname' => $row['guardianname'],
	'cstatus' => $row['civilstatus'], 'bplace' => $row['birthplace'], 'elemschool' => $row['elemschool'],
	'yrgradelem' => $row['dategradelem'], 'hschool' => $row['hsschool'], 'yrgradhs' => $row['datehsgrad'],
	'lastschool' => $row['lastgrad'], 'yrlast' => $row['datelastgrad'], 'address2' => $row['addresslastgrad'],
];

if (isset($_POST['save'])) {
	foreach (STUDENT_FIELDS as $k) { $v[$k] = trim($_POST[$k] ?? ''); }
	if ($fixedID) {
        $v['username'] = $v['snum'] = $row['studnum'];
    }
    $newPassword = (string)($_POST['password'] ?? '');
	$fullname = student_fullname($v);

	if (!csrf_valid()) {
		notify_user('Your session expired. Please try again.');
	} elseif ($v['username'] === '' || $v['snum'] === '') {
		notify_user('Username and student number are required.');
	} elseif (!in_array($v['course'], $courses, true)) {
		notify_user('Please select a valid course.');
	} elseif (($err = student_validate($v)) !== '') {
		notify_user($err);
	} else {
		try {
			$conn->begin_transaction();
			if ($newPassword === '') {
				$u = $conn->prepare("UPDATE usertbl SET username = ?, fullname = ? WHERE ID = ? AND user_type = 'student'");
				$u->bind_param("ssi", $v['username'], $fullname, $accountID);
			} else {
				$u = $conn->prepare("UPDATE usertbl SET username = ?, fullname = ?, password = ? WHERE ID = ? AND user_type = 'student'");
				$hash = password_make($newPassword);
				$u->bind_param("sssi", $v['username'], $fullname, $hash, $accountID);
			}
			$u->execute();

			$s = $conn->prepare("UPDATE studenttbl SET studnum = ?, fname = ?, minitial = ?, lname = ?, address = ?, contact = ?, emailaddress = ?,
				birthdate = ?, course = ?, yearlvl = ?, gender = ?, fathername = ?, mothername = ?, guardianname = ?, birthplace = ?, civilstatus = ?,
				elemschool = ?, dategradelem = ?, hsschool = ?, datehsgrad = ?, lastgrad = ?, datelastgrad = ?, addresslastgrad = ?
				WHERE ID = ?");
			$s->bind_param("sssssssssssssssssssssssi", $v['snum'], $v['fname'], $v['minitial'], $v['lname'], $v['address'], $v['cnumber'],
				$v['email'], $v['bdate'], $v['course'], $v['yrlvlsec'], $v['gender'], $v['fathername'], $v['mothername'], $v['guardianname'],
				$v['bplace'], $v['cstatus'], $v['elemschool'], $v['yrgradelem'], $v['hschool'], $v['yrgradhs'], $v['lastschool'], $v['yrlast'],
				$v['address2'], $id);
			$s->execute();
			$conn->commit();
			notify_user('Successfully updated the account', true);
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

$pageTitle = 'Edit student';
require __DIR__ . '/../includes/workspace_top.php';
?>


	<form class='cmxform' method='POST'>
		<input type="hidden" name="id" value="<?php echo (int)$id; ?>">
		<?php include(__DIR__ . '/includes/student_form.php'); ?>
		<div class="actions"><input type='submit' name='save' value='Save changes'>&nbsp;&nbsp;
		<a href="userreg.php">Cancel</a></div>
	</form>

<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
