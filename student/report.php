<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');

// This student's record
$accountID = (string)($_SESSION['userID'] ?? '');
$stmt = $conn->prepare("SELECT studnum, fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
$stmt->bind_param("s", $accountID);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Subject code => title lookup, so the report shows names and not just codes
$titles = [];
$subRes = $conn->query("SELECT subjectcode, subjecttitle FROM subjecttbl");
while ($r = $subRes->fetch_assoc()) {
	$titles[$r['subjectcode']] = $r['subjecttitle'];
}

// Cancel a form that is still waiting for approval (approved forms are locked).
if ($student && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_form'])) {
	if (!csrf_valid()) {
		js_alert('Your session expired. Please try again.');
	} else {
		$fid = (int)$_POST['cancel_form'];
		$del = $conn->prepare("DELETE FROM student_subject WHERE ID = ? AND studentID = ? AND approved = 0");
		$del->bind_param("is", $fid, $student['studnum']);
		$del->execute();
		js_alert($del->affected_rows > 0 ? 'The enrollment form was cancelled. You can submit a new one.' : 'That form cannot be cancelled (it is already approved or no longer exists).');
	}
	js_redirect('report.php');
	exit;
}

// This student's submitted enrollment forms
$forms = null;
if ($student) {
	$stmt2 = $conn->prepare("SELECT ID, subjects, totalunits, approved, semester, school_year FROM student_subject WHERE studentID = ? ORDER BY ID DESC");
	$stmt2->bind_param("s", $student['studnum']);
	$stmt2->execute();
	$forms = $stmt2->get_result();
}

$pageTitle = 'My enrollment report';
require __DIR__ . '/../includes/workspace_top.php';
?>
<div class="actions no-print"><button class="secondary" onclick="window.print()">Print report</button></div><?php if (!$student) { ?>
		<p>No student record is linked to this account yet.</p>
	<?php } else { ?>
	<div>
		<b>Student:</b> <?php echo h(trim($student['fname'] . ' ' . $student['minitial'] . ' ' . $student['lname'])); ?>
		 ·  <b>Student No.:</b> <?php echo h($student['studnum']); ?>
		 ·  <b>Course:</b> <?php echo h(strtoupper($student['course'])); ?>
		 ·  <b>Year Level:</b> <?php echo h($student['yearlvl']); ?>
	</div>

	<div>
		<div class="scroll"><table>
			<thead><tr>
				<th>Form #</th>
				<th>Subjects</th>
				<th>Units</th>
				<th>Semester</th>
				<th>Status</th>
				<th class="no-print">Action</th>
			</tr></thead><tbody>
			<?php if ($forms->num_rows == 0) { ?>
				<tr><td colspan="6">You have not submitted an enrollment form yet.</td></tr>
			<?php } ?>
			<?php while ($f = $forms->fetch_assoc()) {
				$codes = array_filter(array_map('trim', explode(',', $f['subjects'])), 'strlen');
				$lines = [];
				foreach ($codes as $c) {
					$lines[] = h($c) . (isset($titles[$c]) ? ' - ' . h($titles[$c]) : '');
				}
				$status = ($f['approved'] == '0') ? 'Pending' : 'Approved';
			?>
			<tr>
				<td><?php echo h($f['ID']); ?></td>
				<td><?php echo implode('<br>', $lines); ?></td>
				<td><?php echo h($f['totalunits']); ?></td>
				<td><?php echo h($f['semester'] . ' ' . $f['school_year']); ?></td>
				<td><span class="status <?= $status === 'Approved' ? 'approved' : '' ?>"><?= $status ?></span></td>
				<td class="no-print">
					<?php if ($f['approved'] == '0') { ?>
					<form method="post" action="report.php" onsubmit="return confirm('Cancel this enrollment form?');">
						<?php echo csrf_field(); ?>
						<input type="hidden" name="cancel_form" value="<?php echo (int)$f['ID']; ?>">
						<button type="submit">Cancel</button>
					</form>
					<?php } else { echo '&mdash;'; } ?>
				</td>
			</tr>
			<?php } ?>
		</tbody></table></div>
	</div>
	<?php } ?>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
