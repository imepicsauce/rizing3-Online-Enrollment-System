<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

// Approve or un-approve one enrollment form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!csrf_valid()) {
		js_alert('Your session expired. Please try again.');
	} elseif (!in_array($_POST['action'] ?? '', ['approve', 'pending'], true)) {
		js_alert('Please choose a valid enrollment action.');
	} else {
		$formID = (int)($_POST['form_id'] ?? 0);
		$approve = (($_POST['action'] ?? '') === 'approve') ? 1 : 0;
		$stmt = $conn->prepare("UPDATE student_subject SET approved = ? WHERE ID = ?");
		$stmt->bind_param("ii", $approve, $formID);
		$stmt->execute();
		if ($stmt->affected_rows === 1) {
			js_alert($approve ? 'Enrollment form approved.' : 'Enrollment form set back to pending.');
		} else {
			js_alert('No change was made. The form was cancelled or its status already changed.');
		}
	}
	js_redirect('approvals.php');
	exit;
}

$forms = $conn->query("
	SELECT ss.ID, ss.studentID, ss.subjects, ss.totalunits, ss.approved, ss.semester, ss.school_year,
	       s.fname, s.minitial, s.lname, s.course, s.yearlvl
	FROM student_subject ss
	LEFT JOIN studenttbl s ON s.studnum = ss.studentID
	ORDER BY ss.approved ASC, ss.ID DESC");

$pageTitle = 'Enrollment approvals';
require __DIR__ . '/../includes/workspace_top.php';
?>
<p>Pending forms appear first. Review the subjects and units before approving a form.</p><div class="scroll"><table class="data-table">
			<thead>
				<tr>
					<th>Form</th><th>Student No.</th><th>Name</th><th>Course / Year</th><th>Subjects</th><th>Units</th><th>Semester</th><th>Status</th><th>Action</th>
				</tr>
			</thead>
			<tbody>
			<?php if ($forms->num_rows == 0) { ?>
				<tr><td colspan="9" align="center">No enrollment forms have been submitted yet.</td></tr>
			<?php }
			while ($f = $forms->fetch_assoc()) {
				$isApproved = ((int)$f['approved'] === 1);
				$subjects = implode(', ', array_filter(array_map('trim', explode(',', $f['subjects'])), 'strlen')); ?>
				<tr>
					<td align="center"><?php echo (int)$f['ID']; ?></td>
					<td><?php echo h($f['studentID']); ?></td>
					<td><?php echo h(trim($f['fname'] . ' ' . $f['minitial'] . ' ' . $f['lname'])); ?></td>
					<td><?php echo h($f['course'] . ' / ' . $f['yearlvl']); ?></td>
					<td><?php echo h($subjects); ?></td>
					<td align="center"><?php echo (int)$f['totalunits']; ?></td>
					<td><?php echo h($f['semester'] . ' ' . $f['school_year']); ?></td>
					<td align="center"><span class="status <?= $isApproved ? 'approved' : '' ?>"><?= $isApproved ? 'Approved' : 'Pending' ?></span></td>
					<td align="center">
						<form method="POST" action="approvals.php" style="display:inline">
							<?php echo csrf_field(); ?>
							<input type="hidden" name="form_id" value="<?php echo (int)$f['ID']; ?>">
							<input type="hidden" name="action" value="<?php echo $isApproved ? 'pending' : 'approve'; ?>">
							<button type="submit" class="link-button"><?php echo $isApproved ? 'Set to pending' : 'Approve'; ?></button>
						</form>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table></div>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
