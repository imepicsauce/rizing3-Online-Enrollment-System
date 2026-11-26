<?php
	require_once(__DIR__ . '/../includes/auth.php');
	require_role('student');
	require_once(__DIR__ . '/../config/db.php');
	require_once(__DIR__ . '/../includes/student_enrollment_terms.php');

	$stmt = $conn->prepare("SELECT studnum, fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
	$stmt->bind_param("i", $_SESSION['userID']);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_assoc();
	if (!$rows) {
		notify_user('Your student profile was not found. Please contact the administrator.');
		redirect_to('studentindex.php');
	}
	$_SESSION['studnum'] = $rows['studnum'];

$terms = student_enrollment_terms($conn, $rows['studnum']);
$requested = $_POST['sem'] ?? $_GET['sem'] ?? null;
$semester = is_string($requested) && isset($terms[$requested]) ? $requested : featured_enrollment_term($terms);
$term = $terms[$semester];
	// Only the subjects of the student's own course can be chosen.
	$stmt = $conn->prepare("SELECT subjectcode, subjecttitle, description, numofunits, day, time, room FROM subjecttbl WHERE course_con = ? AND course_con IN (SELECT course_code FROM courses_tbl WHERE is_offered=1) AND (semester = ? OR semester = '') ORDER BY subjectcode");
	$stmt->bind_param("ss", $rows['course'], $semester);
	$stmt->execute();
	$subjects = $stmt->get_result();
$canChoose = $term['eligible'];
$selected = is_array($_POST['subject'] ?? null) ? $_POST['subject'] : [];
$pageTitle = 'Subject enrollment';
require __DIR__ . '/../includes/workspace_top.php';
?>
<form class="enrollment-controls no-print" method="get" action="enroll.php">
<label for="sem">Semester</label><select id="sem" name="sem">
<?php foreach ($terms as $name => $item) { ?><option value="<?= h($name) ?>" <?= $name === $semester ? 'selected' : '' ?>><?= h($name) ?></option><?php } ?>
</select><button class="secondary" id="switch-term">View semester</button>
</form>
<section class="enrollment-context">
<div><strong><?= h(strtoupper($rows['course'])) ?></strong><span class="term-state"><?= h($term['status']) ?></span></div>
<p><?= h(student_term_timing($term)) ?></p>
<?php if ($term['formID']) { [$label, $link] = student_term_action($semester, $term); ?>
<p>You already submitted an enrollment form for this semester. <a href="<?= h($link) ?>"><?= h($label) ?></a></p>
<?php } ?>
<details id="calendar" class="term-calendar" <?= ($_GET['calendar'] ?? '') === '1' ? 'open' : '' ?>><summary>Full enrollment calendar</summary>
<div class="scroll"><table><caption>Academic year <?= h(SCHOOL_YEAR) ?> · Manila time</caption>
<thead><tr><th>Semester</th><th>Registration opens</th><th>Adjustment begins</th><th>Last day</th><th>Status</th></tr></thead><tbody>
<?php foreach ($terms as $name => $item) { $w = $item['window']; ?>
<tr><th scope="row"><?= h($name) ?></th>
<?php foreach (['starts','adjustment','ends'] as $field) { ?><td><?= $w ? h((new DateTimeImmutable($w[$field]))->format('M j, Y')) : '—' ?></td><?php } ?>
<td><?= h($item['phase']) ?></td></tr><?php } ?></tbody></table></div></details>
</section>
<h2><?= $canChoose ? 'Choose subjects' : 'Subjects offered' ?></h2>
<form id="selection" method="post" action="showsubs.php">
<?= csrf_field() ?><input type="hidden" name="sem" value="<?= h($semester) ?>">
<div class="scroll"><table><thead><tr><th>Select</th><th>Subject code</th><th>Subject title</th><th>Class meeting</th><th>Room</th><th>Units</th></tr></thead><tbody>
<?php if (!$subjects->num_rows) { ?><tr><td colspan="6">No subjects are offered for your program this semester.</td></tr><?php } ?>
<?php while ($s = $subjects->fetch_assoc()) { ?>
<tr><td><input <?= !$canChoose ? 'disabled' : '' ?> type="checkbox" name="subject[]" aria-label="Select <?= h($s['subjectcode']) ?>" value="<?= h($s['subjectcode']) ?>" data-units="<?= (int)$s['numofunits'] ?>" <?= in_array($s['subjectcode'], $selected, true) ? 'checked' : '' ?>></td><td><?= h($s['subjectcode']) ?></td><td><?= h($s['subjecttitle']) ?><?php if ($s['description']) { ?><details><summary>Description</summary><?= h($s['description']) ?></details><?php } ?></td><td><?= h(trim($s['day'].' '.$s['time']) ?: 'To be announced') ?></td><td><?= h($s['room'] ?: 'To be announced') ?></td><td><?= (int)$s['numofunits'] ?></td></tr>
<?php } ?></tbody></table></div>
<?php if ($canChoose) { ?><div class="summary"><output id="units" aria-live="polite"></output><input type="submit" name="register" value="Review enrollment" <?= !$subjects->num_rows ? 'disabled' : '' ?>></div><?php } ?>
</form>
<script>
const semesterSelect = document.getElementById('sem');
document.getElementById('switch-term').hidden = true;
semesterSelect.addEventListener('change', () => semesterSelect.form.requestSubmit());
const selection = document.getElementById('selection');
const boxes = Array.from(selection.querySelectorAll('[data-units]'));
function updateUnits() {
    if (!selection.elements.register) return;
    const selected = boxes.filter(box => box.checked);
    const units = selected.reduce((sum, box) => sum + Number(box.dataset.units), 0);
    document.getElementById('units').textContent = selected.length + ' selected · ' + units + ' units';
    selection.elements.register.disabled = selected.length === 0;
}
selection.addEventListener('change', updateUnits);
window.addEventListener('pageshow', updateUnits);
updateUnits();
</script>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
