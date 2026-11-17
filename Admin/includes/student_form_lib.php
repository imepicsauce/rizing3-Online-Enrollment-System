<?php
// Helpers for the shared student form (add and edit pages).
const STUDENT_FIELDS = ["username", "course", "yrlvlsec", "snum", "fname", "minitial", "lname", "gender", "bdate", "address", "cnumber", "email", "fathername", "mothername", "guardianname", "cstatus", "bplace", "elemschool", "yrgradelem", "hschool", "yrgradhs", "lastschool", "yrlast", "address2"];

function sf_val($v, $k) { return h($v[$k] ?? ''); }
function sf_year_select($name, $v) {
	$sel = (string)($v[$name] ?? '');
	echo "<select name='" . h($name) . "'>";
	for ($y = (int)date('Y'); $y >= 1970; $y--) {
		echo "<option value='$y'" . ((string)$y === $sel ? ' selected' : '') . ">$y</option>";
	}
	// keep an old stored value that is outside the list
	if ($sel !== '' && !(ctype_digit($sel) && (int)$sel >= 1970 && (int)$sel <= (int)date('Y'))) {
		echo "<option value='" . h($sel) . "' selected>" . h($sel) . "</option>";
	}
	echo "</select>";
}
