<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

$edit = null;   // course being edited, if any

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	$msg = '';
	$saved = false;
	if (!csrf_valid()) {
		$msg = 'Your session expired. Please try again.';
	} elseif ($action === 'add' || $action === 'save') {
		$code  = strtolower(trim($_POST['course_code'] ?? ''));
		$title = trim($_POST['course_title'] ?? '');
		$desc  = trim($_POST['course_desc'] ?? '');
		$cid   = (int)($_POST['course_id'] ?? 0);
		if ($code === '' || $title === '') {
			$msg = 'Course code and course title are required.';
		} elseif (!preg_match('/^[a-z0-9_-]{1,20}$/', $code)) {
			$msg = 'The course code can only use letters, numbers, dash and underscore (up to 20 characters).';
		} else {
			try {
				if ($action === 'add') {
					$stmt = $conn->prepare("INSERT INTO courses_tbl (course_code, course_title, course_desc) VALUES (?, ?, ?)");
					$stmt->bind_param("sss", $code, $title, $desc);
					$msg = 'Successfully added the course';
				} else {
					// A changed code is carried over to students and subjects by the database (ON UPDATE CASCADE).
					$stmt = $conn->prepare("UPDATE courses_tbl SET course_code = ?, course_title = ?, course_desc = ? WHERE course_ID = ?");
					$stmt->bind_param("sssi", $code, $title, $desc, $cid);
					$msg = 'Successfully updated the course';
				}
				$stmt->execute();
				$saved = true;
			} catch (mysqli_sql_exception $e) {
				$msg = ($e->getCode() == 1062) ? 'That course code already exists.' : 'The course could not be saved.';
			}
		}
	} elseif ($action === 'delete') {
		$cid = (int)($_POST['course_id'] ?? 0);
		$stmt = $conn->prepare("SELECT c.course_code,
				(SELECT COUNT(*) FROM studenttbl s WHERE s.course = c.course_code),
				(SELECT COUNT(*) FROM subjecttbl j WHERE j.course_con = c.course_code)
			FROM courses_tbl c WHERE c.course_ID = ?");
		$stmt->bind_param("i", $cid);
		$stmt->execute();
		$stmt->bind_result($ccode, $nStudents, $nSubjects);
		$found = $stmt->fetch();
		$stmt->close();
		if (!$found) {
			$msg = 'That course no longer exists.';
		} elseif ($nStudents > 0 || $nSubjects > 0) {
			$msg = "The course " . strtoupper($ccode) . " cannot be deleted: it has $nStudents student(s) and $nSubjects subject(s). Move or delete those first.";
		} else {
			$stmt = $conn->prepare("DELETE FROM courses_tbl WHERE course_ID = ?");
			$stmt->bind_param("i", $cid);
			$stmt->execute();
			$saved = true;
			$msg = 'Successfully deleted the course';
		}
	}
	if ($msg !== '') { notify_user($msg, $saved); }
	redirect_to('courses.php');
}

if (isset($_GET['edit'])) {
	$stmt = $conn->prepare("SELECT course_ID, course_code, course_title, course_desc FROM courses_tbl WHERE course_ID = ?");
	$eid = (int)$_GET['edit'];
	$stmt->bind_param("i", $eid);
	$stmt->execute();
	$edit = $stmt->get_result()->fetch_assoc();
}

$courses = $conn->query("SELECT c.course_ID, c.course_code, c.course_title, c.course_desc, c.is_offered,
		(SELECT COUNT(*) FROM studenttbl s WHERE s.course = c.course_code) AS students,
		(SELECT COUNT(*) FROM subjecttbl j WHERE j.course_con = c.course_code) AS subjects
	FROM courses_tbl c ORDER BY c.course_code");

$pageTitle = 'Courses';
require __DIR__ . '/../includes/workspace_top.php';
?>


	<div class="scroll">
		<table class="data-table">
			<thead>
				<tr><th>Code</th><th>Title</th><th>Description</th><th>Students</th><th>Subjects</th><th>Action</th></tr>
			</thead>
			<tbody>
			<?php if ($courses->num_rows == 0) { ?>
				<tr><td colspan="6" align="center">No courses yet.</td></tr>
			<?php }
			while ($c = $courses->fetch_assoc()) { ?>
				<tr>
					<td><?php echo h(strtoupper($c['course_code'])); ?></td>
					<td><?php echo h($c['course_title']); ?><?= $c['is_offered'] ? '' : ' (retired)' ?></td>
					<td><?php echo h($c['course_desc']); ?></td>
					<td align="center"><?php echo (int)$c['students']; ?></td>
					<td align="center"><?php echo (int)$c['subjects']; ?></td>
					<td align="center">
						<a href="courses.php?edit=<?php echo (int)$c['course_ID']; ?>#course-form">EDIT</a> |
						<details class="confirm-action"><summary>Delete</summary>
<p>Delete course <?= h(strtoupper($c['course_code'])) ?>?</p>
<form method="post" action="courses.php" style="display:inline">
							<?php echo csrf_field(); ?>
							<input type="hidden" name="action" value="delete">
							<input type="hidden" name="course_id" value="<?php echo (int)$c['course_ID']; ?>">
							<button type="submit" class="link-button">Delete course</button>
						</form>
</details>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>


	<form id='course-form' class='cmxform' method='POST' action='courses.php'>
		<?php echo csrf_field(); ?>
		<input type="hidden" name="action" value="<?php echo $edit ? 'save' : 'add'; ?>">
		<?php if ($edit) { ?><input type="hidden" name="course_id" value="<?php echo (int)$edit['course_ID']; ?>"><?php } ?>
		<fieldset>
			<legend><?php echo $edit ? 'Edit Course' : 'Add a Course'; ?></legend>
			<div class="fields">
            <label>Course code *
                <input type="text" id="course_code" name="course_code" required="required" maxlength="20" value="<?php echo h($edit['course_code'] ?? ''); ?>" />
            </label>
            <label>Course title *
                <input type="text" id="course_title" name="course_title" required="required" maxlength="100" value="<?php echo h($edit['course_title'] ?? ''); ?>" />
            </label>
            <label>Description
                <textarea id="course_desc" name="course_desc"><?php echo h($edit['course_desc'] ?? ''); ?></textarea>
            </label>
        </div>
		</fieldset>
		<div class="actions"><input type="submit" value="<?php echo $edit ? 'Save changes' : 'Add course'; ?>">
		<?php if ($edit) { ?>&nbsp;&nbsp;<a href="courses.php">Cancel</a><?php } ?></div>
	</form>

<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
