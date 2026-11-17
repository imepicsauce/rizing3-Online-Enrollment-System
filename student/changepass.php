<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
require_once(__DIR__ . '/../config/db.php');

$getID = (int)$_SESSION['userID'];

$stmt = $conn->prepare("SELECT * FROM studenttbl WHERE accountID = ?");
$stmt->bind_param("i", $getID);
$stmt->execute();
$rows = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM usertbl WHERE ID = ?");
$stmt->bind_param("i", $getID);
$stmt->execute();
$rowaccs = $stmt->get_result()->fetch_assoc();

if (isset($_POST['cpass'])) {
	$oldpass  = (string)($_POST['opassword'] ?? '');
	$newpass  = (string)($_POST['password'] ?? '');
	$newpass2 = (string)($_POST['password2'] ?? '');

	if (!$rowaccs || !password_matches($oldpass, $rowaccs['password'])) {
		js_alert('Inputted password is wrong please try again');
	} elseif ($newpass === '') {
		js_alert('The new password cannot be empty.');
	} elseif ($newpass !== $newpass2) {
		js_alert('The new passwords do not match. Please try again.');
	} else {
		$stmt = $conn->prepare("UPDATE usertbl SET password = ? WHERE ID = ?");
		$newHash = password_make($newpass);
		$stmt->bind_param("si", $newHash, $getID);
		$stmt->execute();
		js_alert('Successfully updated the password');
		js_redirect('studentprofile.php');
		exit;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>John Paul College</title>


<link href="../assets/css/style.css" rel="stylesheet" type="text/css"/>

</head>

<body class="app">

<div class='header'>
</div>
	
<nav class='container'>
<?php
include('includes/menu.php');
?>
</nav>





<div id="main">
	<div class="shell">
	<center>
	<form class='cmxform' method='POST'>
	<fieldset>
			<legend><h4>Account Details</h4></legend>
			  <ol>
			  <li>
			  <table>
			  <tr>
				<td align='right'><label for="name" > Input Old Password: </label></td>
				<td><input type='password' name='opassword'></td>
			  </tr>
			  <tr>
				<td align='right'><label for="name" > Input New Password: </label></td>
				<td><input type='password' name='password'></td>
			  </tr>
			  <tr>
				<td align='right'><label for="name"> Input Password Again: </label></td>
				<td><input type='password' name='password2'></td>
			  </tr>
					</table>
				</li>
				
  </ol>
			
	</fieldset>
	<br>
	
<div align='center'><input type='submit' name='cpass' value='Change Password'>&nbsp;&nbsp;
<input type='reset' name='clear' value='Clear'></div>
</form>
</center>
	</div>
	<div><br></div>
</div>
<!-- /Main -->

<!-- Footer -->
<div id="footer">
	<div class="shell">
		<!-- Mini Nav --> 
		<div class="footer-navigation"> 
		<?php
			include('includes/menu2.php');
		?>
		</div> 
		<!-- /Mini Nav --> 
		
		<!-- Copyrights --> 
		<p class="right"> 
			Copyright <?php echo date("Y"); ?> | Online Enrollment
		</p> 
		<!-- /Copyrights --> 
		
		<div class="cl">&nbsp;</div> 
	</div>
</div>




</body>
</html>
