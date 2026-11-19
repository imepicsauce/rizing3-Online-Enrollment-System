<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
$registrationDestination = 'userreg.php';
require __DIR__ . '/../includes/student_registration.php';
$pageTitle = 'Add student';
require __DIR__ . '/../includes/workspace_top.php';
?>


	<form class='cmxform' method='POST'>
		<?php include(__DIR__ . '/includes/student_form.php'); ?>
		<div class="actions"><input type='submit' name='register' value='Add new user'>&nbsp;&nbsp;
		<a href="userreg.php">Cancel</a></div>
	</form>

<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
