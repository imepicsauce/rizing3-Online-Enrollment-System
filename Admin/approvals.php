<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

require_once __DIR__ . '/includes/queue_scope.php';
$years = array_unique(array_merge([SCHOOL_YEAR], array_column($conn->query("SELECT DISTINCT school_year FROM student_subject ORDER BY school_year DESC")->fetch_all(MYSQLI_ASSOC), 'school_year')));
$year = $_GET['year'] ?? SCHOOL_YEAR;
if ($year !== '' && !in_array($year, $years, true)) { $year = SCHOOL_YEAR; }
$semester = $_GET['semester'] ?? registrar_default_semester(term_windows($conn));
if (!in_array($semester, SEMESTERS, true)) { $semester = ''; }
$status = $_GET['status'] ?? 'pending';
if (!in_array($status, ['pending','approved','all'], true)) { $status = 'pending'; }
$search = is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '';
$queueUrl = 'approvals.php?' . http_build_query(['year'=>$year,'semester'=>$semester,'status'=>$status,'q'=>$search]);

// Approve or un-approve one enrollment form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!csrf_valid()) {
		notify_user('Your session expired. Please try again.');
	} elseif (!in_array($_POST['action'] ?? '', ['approve', 'pending'], true)) {
		notify_user('Please choose a valid enrollment action.');
	} else {
		$formID = (int)($_POST['form_id'] ?? 0);
		$approve = (($_POST['action'] ?? '') === 'approve') ? 1 : 0;
		$stmt = $conn->prepare("UPDATE student_subject SET approved = ? WHERE ID = ?");
		$stmt->bind_param("ii", $approve, $formID);
		$stmt->execute();
		if ($stmt->affected_rows === 1) {
			notify_user($approve ? 'Enrollment form approved.' : 'Enrollment form set back to pending.', true);
		} else {
			notify_user('No change was made. The form was cancelled or its status already changed.');
		}
	}
	redirect_to($queueUrl);
}

$state = $status === 'all' ? -1 : (int)($status === 'approved');
$query = $conn->prepare("
	SELECT ss.ID, ss.studentID, ss.subjects, ss.totalunits, ss.approved, ss.semester, ss.school_year,
	       s.fname, s.minitial, s.lname, s.course, s.yearlvl
	FROM student_subject ss
	LEFT JOIN studenttbl s ON s.studnum = ss.studentID
	WHERE (?='' OR ss.school_year=?) AND (?='' OR ss.semester=?) AND (?=-1 OR ss.approved=?)
      AND (?='' OR LOCATE(?,ss.studentID)>0 OR LOCATE(?,CONCAT_WS(' ',s.fname,s.lname))>0)
    ORDER BY ss.approved ASC, ss.ID DESC");
$query->bind_param('ssssiisss',$year,$year,$semester,$semester,$state,$state,$search,$search,$search);
$query->execute();
$forms = $query->get_result();
$titles = array_column($conn->query("SELECT subjectcode,subjecttitle FROM subjecttbl")->fetch_all(MYSQLI_ASSOC),'subjecttitle','subjectcode');

$pageTitle = 'Enrollment approvals';
require __DIR__ . '/../includes/workspace_top.php';
?>
<form method="get" class="toolbar registrar-filters">
<label>Academic year<select name="year"><option value="">All years</option><?php foreach ($years as $y) { ?><option <?= $y === $year ? 'selected' : '' ?>><?= h($y) ?></option><?php } ?></select></label>
<label>Semester<select name="semester"><option value="">All semesters</option><?php foreach (SEMESTERS as $sem) { ?><option <?= $sem === $semester ? 'selected' : '' ?>><?= h($sem) ?></option><?php } ?></select></label>
<label>Status<select name="status"><?php foreach (['pending'=>'Pending','approved'=>'Approved','all'=>'All statuses'] as $key=>$label) { ?><option value="<?= $key ?>" <?= $key === $status ? 'selected' : '' ?>><?= $label ?></option><?php } ?></select></label>
<label>Student name or ID<input type="search" name="q" value="<?= h($search) ?>"></label>
<button>Apply filters</button><a href="approvals.php">Reset</a></form>
<p><?= $forms->num_rows ?> matching <?= $forms->num_rows === 1 ? 'form' : 'forms' ?> · <?= h($year ?: 'All years') ?> · <?= h($semester ?: 'All semesters') ?></p><div class="scroll"><table class="data-table">
			<thead>
				<tr>
					<th>Form</th><th>Student No.</th><th>Name</th><th>Course / Year</th><th>Subjects</th><th>Units</th><th>Semester</th><th>Status</th><th>Action</th>
				</tr>
			</thead>
			<tbody>
			<?php if ($forms->num_rows == 0) { ?>
				<tr><td colspan="9" align="center">No forms match these filters. Choose another semester, status or academic year.</td></tr>
			<?php }
			while ($f = $forms->fetch_assoc()) {
				$isApproved = ((int)$f['approved'] === 1);
				$subjects = array_filter(array_map('trim', explode(',', $f['subjects'])), 'strlen'); ?>
				<tr>
					<td align="center"><?php echo (int)$f['ID']; ?></td>
					<td><?php echo h($f['studentID']); ?></td>
					<td><?php echo h(trim($f['fname'] . ' ' . $f['minitial'] . ' ' . $f['lname'])); ?></td>
					<td><?php echo h($f['course'] . ' / ' . $f['yearlvl']); ?></td>
					<td><?php foreach ($subjects as $code) { ?><div><?= h($code) ?><?= isset($titles[$code]) ? ' · '.h($titles[$code]) : '' ?></div><?php } ?></td>
					<td align="center"><?php echo (int)$f['totalunits']; ?></td>
					<td><?php echo h($f['semester'] . ' ' . $f['school_year']); ?></td>
					<td align="center"><span class="status <?= $isApproved ? 'approved' : '' ?>"><?= $isApproved ? 'Approved' : 'Pending' ?></span></td>
					<td align="center">
						<form method="POST" action="<?= h($queueUrl) ?>" style="display:inline">
							<?php echo csrf_field(); ?>
							<input type="hidden" name="form_id" value="<?php echo (int)$f['ID']; ?>">
							<input type="hidden" name="action" value="<?php echo $isApproved ? 'pending' : 'approve'; ?>">
							<button type="submit" class="link-button" aria-label="<?= h(($isApproved ? 'Set to pending' : 'Approve').' · '.$f['studentID'].' · '.$f['semester'].' '.$f['school_year']) ?>"><?php echo $isApproved ? 'Set to pending' : 'Approve'; ?></button>
						</form>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table></div>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
