<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

$years = array_column($conn->query("SELECT DISTINCT school_year FROM student_subject ORDER BY school_year DESC")->fetch_all(MYSQLI_ASSOC), 'school_year');
$semesters = array_column($conn->query("SELECT DISTINCT semester FROM student_subject ORDER BY semester")->fetch_all(MYSQLI_ASSOC), 'semester');
$year = in_array($_GET['year'] ?? '', $years, true) ? ($_GET['year'] ?? '') : '';
$semester = in_array($_GET['semester'] ?? '', $semesters, true) ? ($_GET['semester'] ?? '') : '';
$statusFilter = $_GET['status'] ?? '';
$approved = $statusFilter === 'approved' ? 1 : ($statusFilter === 'pending' ? 0 : -1);
$stmt = $conn->prepare("
    SELECT ss.ID, ss.studentID, ss.subjects, ss.totalunits, ss.approved, ss.semester, ss.school_year,
           s.fname, s.minitial, s.lname, s.course, s.yearlvl
    FROM student_subject ss LEFT JOIN studenttbl s ON s.studnum = ss.studentID
    WHERE (? = '' OR ss.school_year = ?) AND (? = '' OR ss.semester = ?) AND (? = -1 OR ss.approved = ?)
    ORDER BY ss.ID DESC");
$stmt->bind_param('ssssii', $year, $year, $semester, $semester, $approved, $approved);
$stmt->execute();
$result = $stmt->get_result();

$pageTitle = 'Enrollment report';
require __DIR__ . '/../includes/workspace_top.php';
?>
<form class="toolbar no-print" method="get">
<label>School year <select name="year"><option value="">All years</option>
<?php foreach ($years as $value) { ?><option value="<?= h($value) ?>" <?= $value === $year ? 'selected' : '' ?>><?= h($value) ?></option><?php } ?>
</select></label>
<label>Semester <select name="semester"><option value="">All semesters</option>
<?php foreach ($semesters as $value) { ?><option value="<?= h($value) ?>" <?= $value === $semester ? 'selected' : '' ?>><?= h($value) ?></option><?php } ?>
</select></label>
<label>Status <select name="status"><option value="">All statuses</option><option value="pending" <?= $approved === 0 ? 'selected' : '' ?>>Pending</option><option value="approved" <?= $approved === 1 ? 'selected' : '' ?>>Approved</option></select></label>
<button>Apply filters</button><a href="report.php">Clear</a>
</form>
<p><?= h($year ?: 'All years') ?> · <?= h($semester ?: 'All semesters') ?> · <?= $approved === -1 ? 'All statuses' : ($approved ? 'Approved' : 'Pending') ?></p>
<div class="actions no-print"><button class="secondary" onclick="window.print()">Print report</button></div>
<p>Generated <?= h(date('F j, Y g:i A')) ?></p>

	<div>
		<div class="scroll"><table>
			<thead><tr>
				<th>#</th>
				<th>Student No.</th>
				<th>Student Name</th>
				<th>Course / Year</th>
				<th>Subjects</th>
				<th>Units</th>
				<th>Semester</th>
				<th>Status</th>
			</tr></thead><tbody>
			<?php if ($result->num_rows == 0) { ?>
				<tr><td colspan="8">No enrollment forms match this selection.</td></tr>
			<?php } ?>
			<?php
			$totalForms = 0;
			while ($row = $result->fetch_assoc()) {
				$totalForms++;
				$subjects = implode(', ', array_filter(array_map('trim', explode(',', $row['subjects'])), 'strlen'));
				$name = trim($row['fname'] . ' ' . $row['minitial'] . ' ' . $row['lname']);
				if ($name === '') { $name = '(student record not found)'; }
				$status = ($row['approved'] == '0') ? 'Pending' : 'Approved';
			?>
			<tr>
				<td><?php echo h($row['ID']); ?></td>
				<td><?php echo h($row['studentID']); ?></td>
				<td><?php echo h($name); ?></td>
				<td><?php echo h(strtoupper($row['course'] ?? '') . ' ' . ($row['yearlvl'] ?? '')); ?></td>
				<td><?php echo h($subjects); ?></td>
				<td><?php echo h($row['totalunits']); ?></td>
				<td><?php echo h($row['semester'] . ' ' . $row['school_year']); ?></td>
				<td><span class="status <?= $status === 'Approved' ? 'approved' : '' ?>"><?= $status ?></span></td>
			</tr>
			<?php } ?>
		</tbody></table></div>
		<p>Total enrollment forms: <b><?php echo $totalForms; ?></b></p>
	</div>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
