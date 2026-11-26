<?php
require_once __DIR__ . "/../../config/settings.php";
// Reading and checking the subject form (used by addnewsubj.php and editsubject.php).
function subject_input() {
	$v = [];
	foreach (['semester', 'course', 'scode', 'stitle', 'cdescription', 'sroom', 'stime', 'sday', 'sinstructor'] as $k) {
		$v[$k] = trim($_POST[$k] ?? '');
	}
	$v['numunits']   = (int)($_POST['numunits'] ?? 0);
	$v['islab']      = isset($_POST['islab']) ? 1 : 0;
	$v['istutorial'] = isset($_POST['istutorial']) ? 1 : 0;
	return $v;
}
function subject_validate($v, $courses) {
	if (!in_array($v['semester'], ['', ...SEMESTERS, 'Archived'], true)) { return 'Please choose a valid semester.'; }
 if ($v['scode'] === '' || $v['stitle'] === '') { return 'Subject code and subject title are required.'; }
	if (strpos($v['scode'], ',') !== false) { return 'Subject codes cannot contain a comma.'; }
	if ($v['numunits'] < 1 || $v['numunits'] > 6) { return 'Number of units must be from 1 to 6.'; }
	if (!in_array($v['course'], $courses, true)) { return 'Please select a valid course.'; }
	return '';
}
