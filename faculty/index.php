<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('faculty');
require_once __DIR__ . '/../config/db.php';
$stmt = $conn->prepare("SELECT s.subjectcode,s.subjecttitle,s.course_con,s.semester,s.day,s.time,s.room,s.numofunits FROM subjecttbl s JOIN faculty_subject f ON f.subjectID=s.ID WHERE f.facultyID=? AND s.course_con IN (SELECT course_code FROM courses_tbl WHERE is_offered=1) ORDER BY s.subjectcode");
$stmt->bind_param('i',$_SESSION['userID']);
$stmt->execute();
$subjects = $stmt->get_result();
$pageTitle = 'My teaching schedule';
require __DIR__ . '/../includes/workspace_top.php';
?>
<p>Your assigned subjects and class meeting details.</p>
<div class="scroll"><table><thead><tr><th>Subject</th><th>Program</th><th>Semester</th><th>Units</th><th>Day</th><th>Time</th><th>Room</th></tr></thead><tbody>
<?php if (!$subjects->num_rows) { ?><tr><td colspan="7">The registrar has not assigned any subjects yet.</td></tr><?php } ?>
<?php while ($s=$subjects->fetch_assoc()) { ?><tr><td><?= h($s['subjectcode'].' · '.$s['subjecttitle']) ?></td><td><?= h(strtoupper($s['course_con'])) ?></td><td><?= h($s['semester'] ?: 'Both semesters') ?></td><td><?= (int)$s['numofunits'] ?></td>
<?php foreach (['day','time','room'] as $field) { ?><td><?= h($s[$field] ?: 'To be announced') ?></td><?php } ?></tr><?php } ?></tbody></table></div>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
