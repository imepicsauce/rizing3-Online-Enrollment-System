<?php
	require_once(__DIR__ . '/../includes/auth.php');
	require_role('student');
	require_once(__DIR__ . '/../config/db.php');
	require_once(__DIR__ . '/../config/settings.php');

	$stmt = $conn->prepare("SELECT studnum, fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
	$stmt->bind_param("i", $_SESSION['userID']);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_assoc();
	if (!$rows) {
		js_alert('Your student profile was not found. Please contact the administrator.');
		js_redirect('studentindex.php');
		exit;
	}
	$_SESSION['studnum'] = $rows['studnum'];

	// Only the subjects of the student's own course can be chosen.
	$stmt = $conn->prepare("SELECT subjectcode, subjecttitle, description, numofunits FROM subjecttbl WHERE course_con = ? ORDER BY subjectcode");
	$stmt->bind_param("s", $rows['course']);
	$stmt->execute();
	$subjects = $stmt->get_result();
$semester = $_POST['sem'] ?? $_GET['sem'] ?? SEMESTERS[0];
$selected = is_array($_POST['subject'] ?? null) ? $_POST['subject'] : [];
$pageTitle = 'Select your subjects';
require __DIR__ . '/../includes/workspace_top.php';
?>
<form id="selection" method="post" action="showsubs.php">
<?= csrf_field() ?>
<div class="toolbar"><strong><?= h(strtoupper($rows['course'])) ?> · <?= h($rows['yearlvl']) ?></strong>
<label for="sem">Semester</label><select id="sem" name="sem">
<?php foreach (SEMESTERS as $s) { ?><option <?= $s === $semester ? 'selected' : '' ?>><?= h($s) ?></option><?php } ?>
</select></div>
<p>Select the subjects you want to take. You can review the fees before you submit.</p>
<div class="scroll"><table><thead><tr><th>Select</th><th>Subject code</th><th>Subject title</th><th>Description</th><th>Units</th></tr></thead><tbody>
<?php if (!$subjects->num_rows) { ?><tr><td colspan="5">No subjects have been added for your course yet.</td></tr><?php } ?>
<?php while ($s = $subjects->fetch_assoc()) { ?>
<tr><td><input type="checkbox" name="subject[]" aria-label="Select <?= h($s['subjectcode']) ?>" value="<?= h($s['subjectcode']) ?>" data-units="<?= (int)$s['numofunits'] ?>" <?= in_array($s['subjectcode'], $selected, true) ? 'checked' : '' ?>></td><td><?= h($s['subjectcode']) ?></td><td><?= h($s['subjecttitle']) ?></td><td><?= h($s['description']) ?></td><td><?= (int)$s['numofunits'] ?></td></tr>
<?php } ?></tbody></table></div>
<div class="summary"><output id="units" aria-live="polite"></output><input type="submit" name="register" value="Review enrollment" <?= !$subjects->num_rows ? 'disabled' : '' ?>></div>
</form>
<script>
const selection = document.getElementById('selection');
const boxes = Array.from(selection.querySelectorAll('[data-units]'));
function updateUnits() {
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
