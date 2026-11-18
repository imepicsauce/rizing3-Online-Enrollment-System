<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');
	$getID = (int)$_SESSION['userID'];

	$stmt = $conn->prepare("SELECT * FROM studenttbl WHERE accountID = ?");
	$stmt->bind_param("i", $getID);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_assoc();
	if (!$rows) { header('Location: studentindex.php'); exit; }

	$stmt = $conn->prepare("SELECT username FROM usertbl WHERE ID = ?");
	$stmt->bind_param("i", $getID);
	$stmt->execute();
	$rowacc = $stmt->get_result()->fetch_assoc();
$rows['username'] = $rowacc['username'];
$sections = [
    'Personal and contact details' => ['username'=>'Username','gender'=>'Gender','birthdate'=>'Birth date','birthplace'=>'Birthplace','civilstatus'=>'Civil status','address'=>'Address','contact'=>'Phone','emailaddress'=>'Email'],
    'Family information' => ['guardianname'=>'Guardian','fathername'=>'Father','mothername'=>'Mother'],
    'Education history' => ['elemschool'=>'Elementary school','dategradelem'=>'Elementary graduation','hsschool'=>'High school','datehsgrad'=>'High school graduation','lastgrad'=>'Last school attended','datelastgrad'=>'Graduation year','addresslastgrad'=>'School address']
];
$pageTitle = 'Student profile';
require __DIR__ . '/../includes/workspace_top.php';
?>
<section class="profile">
<div class="feature"><h2><?= h(trim($rows['fname'].' '.$rows['minitial'].' '.$rows['lname'])) ?></h2>
<p><?= h($rows['studnum']) ?> · <?= h(strtoupper($rows['course'])) ?> · <?= h($rows['yearlvl']) ?></p></div>
<div class="actions no-print">
<input type="button" value="Print Profile" onclick="window.print()">
<a class="btn secondary" href="changepass.php">Change Password</a>
<a class="btn secondary" href="report.php">My Enrollment Report</a>
<a class="btn secondary" href="studentindex.php">Back to Home</a>
</div>
<?php foreach ($sections as $heading => $fields) { ?>
<h2><?= h($heading) ?></h2><dl>
<?php foreach ($fields as $field => $label) { ?><div><dt><?= h($label) ?></dt><dd><?= h($rows[$field] === '' ? '—' : $rows[$field]) ?></dd></div><?php } ?>
</dl><?php } ?>
</section>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
