<?php
// Helpers for the shared student form (add and edit pages).
const STUDENT_FIELDS = ["username", "course", "yrlvlsec", "snum", "fname", "minitial", "lname", "gender", "bdate", "address", "cnumber", "email", "fathername", "mothername", "guardianname", "cstatus", "bplace", "elemschool", "yrgradelem", "hschool", "yrgradhs", "lastschool", "yrlast", "address2"];

function sf_val($v, $k) { return h($v[$k] ?? ''); }
function sf_year_select($name, $v) {
	$sel = (string)($v[$name] ?? '');
	echo "<select name='" . h($name) . "'>";
	echo "<option value=''>Select year</option>";
	for ($y = (int)date('Y'); $y >= 1970; $y--) {
		echo "<option value='$y'" . ((string)$y === $sel ? ' selected' : '') . ">$y</option>";
	}
	// keep an old stored value that is outside the list
	if ($sel !== '' && !(ctype_digit($sel) && (int)$sel >= 1970 && (int)$sel <= (int)date('Y'))) {
		echo "<option value='" . h($sel) . "' selected>" . h($sel) . "</option>";
	}
	echo "</select>";
}

// Check the student fields. Returns an error message, or '' when everything is acceptable.
function student_validate($v) {
	if ($v['fname'] === '' || $v['lname'] === '') { return 'First name and last name are required.'; }
	if ($v['yrlvlsec'] === '') { return 'Section/Year Level is required.'; }
	if ($v['address'] === '') { return 'Address is required.'; }
	if (!in_array($v['gender'], ['male', 'female'], true)) { return 'Please choose a gender.'; }
	if (!in_array($v['cstatus'], ['single', 'married', 'widow'], true)) { return 'Please choose a civil status.'; }
	if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) { return 'That email address is not valid.'; }
	if ($v['cnumber'] !== '' && !preg_match('/^[0-9]{7,11}$/', $v['cnumber'])) { return 'The contact number must be 7 to 11 digits (numbers only).'; }
	if ($v['bdate'] !== '') {
		$d = DateTime::createFromFormat('Y-m-d', $v['bdate']);
		if (!$d || $d->format('Y-m-d') !== $v['bdate'] || $d > new DateTime('today')) { return 'Please enter a valid birth date (not in the future).'; }
	}
	return '';
}
