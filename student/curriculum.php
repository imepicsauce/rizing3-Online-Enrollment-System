<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');

$accountID = (int)$_SESSION['userID'];
$stmt = $conn->prepare("SELECT fname, minitial, lname, course, yearlvl FROM studenttbl WHERE accountID = ? LIMIT 1");
$stmt->bind_param("i", $accountID);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$subjects = null;
if ($student) {
	$stmt = $conn->prepare("SELECT semester, subjectcode, subjecttitle, numofunits, islab, istutorial FROM subjecttbl WHERE course_con = ? AND semester <> 'Archived' ORDER BY semester, subjectcode");
	$stmt->bind_param("s", $student['course']);
	$stmt->execute();
	$subjects = $stmt->get_result();
}

$pageTitle = 'Curriculum';
require __DIR__ . '/../includes/workspace_top.php';
?>
<?php if (!$student) { ?>
		<p>No student record is linked to this account yet.</p>
	<?php } else { ?>
		<p><b><?php echo h(trim($student['fname'] . ' ' . $student['minitial'] . ' ' . $student['lname'])); ?></b>
		&mdash; <?php echo h(strtoupper($student['course'])); ?>, <?php echo h($student['yearlvl']); ?></p>
		<div class="scroll"><table>
			<tr>
				<th align="left">Code</th>
				<th align="left">Title</th>
				<th>Semester</th><th>Units</th>
				<th>Lab</th>
				<th>Tutorial</th>
			</tr>
			<?php
			$total = 0;
			if ($subjects->num_rows == 0) { ?>
				<tr><td colspan="6" align="center">No subjects have been added for this course yet.</td></tr>
			<?php }
			while ($sub = $subjects->fetch_assoc()) {
				$total += (int)$sub['numofunits']; ?>
				<tr>
					<td><?php echo h($sub['subjectcode']); ?></td>
					<td><?php echo h($sub['subjecttitle']); ?></td>
					<td><?= h($sub['semester'] ?: 'Both semesters') ?></td><td align="center"><?php echo h($sub['numofunits']); ?></td>
					<td align="center"><?php echo $sub['islab'] ? 'Yes' : '-'; ?></td>
					<td align="center"><?php echo $sub['istutorial'] ? 'Yes' : '-'; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="3"><b>Total units</b></td>
				<td align="center"><b><?php echo $total; ?></b></td>
				<td colspan="2"></td>
			</tr>
		</table></div>
	<?php } ?>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
