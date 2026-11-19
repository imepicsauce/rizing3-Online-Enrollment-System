<?php
	require_once(__DIR__ . '/../includes/auth.php');
	require_role('student');
	require_once(__DIR__ . '/../config/db.php');
	require_once(__DIR__ . '/../includes/enrollment_calendar.php');

	$stmt = $conn->prepare("SELECT studnum, fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
	$stmt->bind_param("i", $_SESSION['userID']);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_assoc();
	if (!$rows) {
		notify_user('Your student profile was not found. Please contact the administrator.');
		redirect_to('studentindex.php');
	}
	$_SESSION['studnum'] = $rows['studnum'];

	// Only the subjects of the student's own course can be chosen.
	$stmt = $conn->prepare("SELECT subjectcode, subjecttitle, description, numofunits, day, time, room FROM subjecttbl WHERE course_con = ? ORDER BY subjectcode");
	$stmt->bind_param("s", $rows['course']);
	$stmt->execute();
	$subjects = $stmt->get_result();
$windows = term_windows($conn);
$available = array_values(array_filter(SEMESTERS, fn($term) => enrollment_open($windows[$term] ?? null)));
$semester = $_POST['sem'] ?? $_GET['sem'] ?? ($available[0] ?? SEMESTERS[0]);
if (!in_array($semester, $available, true)) {
    $semester = $available[0] ?? SEMESTERS[0];
}
$selected = is_array($_POST['subject'] ?? null) ? $_POST['subject'] : [];
$pageTitle = 'Subject enrollment';
require __DIR__ . '/../includes/workspace_top.php';
?>
<p>Choose subjects for your program, review the fees, then send your form to the registrar.</p>
<h2>Enrollment calendar</h2>
<div class="scroll"><table><caption>Academic year <?= h(SCHOOL_YEAR) ?> · Dates follow Manila time</caption>
<thead><tr><th>Semester</th><th>Registration opens</th><th>Adjustment begins</th><th>Last day</th><th>Status</th></tr></thead><tbody>
<?php foreach (SEMESTERS as $term) { $w = $windows[$term] ?? null; ?>
<tr><th scope="row"><?= h($term) ?></th>
<?php foreach (['starts','adjustment','ends'] as $field) { ?><td><?= $w ? h((new DateTimeImmutable($w[$field]))->format('M j, Y')) : '—' ?></td><?php } ?>
<td><?= h(enrollment_phase($w)) ?></td></tr><?php } ?></tbody></table></div>
<p>During adjustment, you can still enroll or replace a pending form. Contact the registrar to change an approved form.</p>
<h2>Choose subjects</h2>
<form id="selection" method="post" action="showsubs.php">
<?= csrf_field() ?>
<div class="toolbar"><strong><?= h(strtoupper($rows['course'])) ?> · <?= h($rows['yearlvl']) ?></strong>
<label for="sem">Semester</label><select id="sem" name="sem">
<?php foreach (SEMESTERS as $s) { ?><option value="<?= h($s) ?>" <?= !in_array($s, $available, true) ? 'disabled' : '' ?> <?= $s === $semester ? 'selected' : '' ?>><?= h($s) ?></option><?php } ?>
</select></div>
<?php if (!$available) { ?><p role="status">Subject enrollment is currently closed. You can still browse the subjects below.</p><?php } ?>
<div class="scroll"><table><thead><tr><th>Select</th><th>Subject code</th><th>Subject title</th><th>Class meeting</th><th>Room</th><th>Units</th></tr></thead><tbody>
<?php if (!$subjects->num_rows) { ?><tr><td colspan="6">No subjects have been added for your course yet.</td></tr><?php } ?>
<?php while ($s = $subjects->fetch_assoc()) { ?>
<tr><td><input <?= !$available ? 'disabled' : '' ?> type="checkbox" name="subject[]" aria-label="Select <?= h($s['subjectcode']) ?>" value="<?= h($s['subjectcode']) ?>" data-units="<?= (int)$s['numofunits'] ?>" <?= in_array($s['subjectcode'], $selected, true) ? 'checked' : '' ?>></td><td><?= h($s['subjectcode']) ?></td><td><?= h($s['subjecttitle']) ?><?php if ($s['description']) { ?><details><summary>Description</summary><?= h($s['description']) ?></details><?php } ?></td><td><?= h(trim($s['day'].' '.$s['time']) ?: 'To be announced') ?></td><td><?= h($s['room'] ?: 'To be announced') ?></td><td><?= (int)$s['numofunits'] ?></td></tr>
<?php } ?></tbody></table></div>
<div class="summary"><output id="units" aria-live="polite"></output><input type="submit" name="register" value="Review enrollment" <?= !$available || !$subjects->num_rows ? 'disabled' : '' ?>></div>
</form>
<script>
const selection = document.getElementById('selection');
const boxes = Array.from(selection.querySelectorAll('[data-units]'));
function updateUnits() {
    const selected = boxes.filter(box => box.checked);
    const units = selected.reduce((sum, box) => sum + Number(box.dataset.units), 0);
    document.getElementById('units').textContent = selected.length + ' selected · ' + units + ' units';
    selection.elements.register.disabled = selected.length === 0 || !selection.elements.sem.selectedOptions.length || selection.elements.sem.selectedOptions[0].disabled;
}
selection.addEventListener('change', updateUnits);
window.addEventListener('pageshow', updateUnits);
updateUnits();
</script>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
