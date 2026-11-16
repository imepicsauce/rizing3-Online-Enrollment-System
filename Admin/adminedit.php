<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

$adminID = (int)$_SESSION['userID'];

if (isset($_POST['register'])) {
	$username = trim($_POST['username'] ?? '');
	$password = (string)($_POST['password'] ?? '');

	if ($username === '' || $password === '') {
		js_alert('Username and password are required.');
	} else {
		try {
			// Only the logged-in administrator's own account is changed.
			$stmt = $conn->prepare("UPDATE usertbl SET username = ?, password = ? WHERE ID = ? AND user_type = 'admin'");
			$stmt->bind_param("ssi", $username, $password, $adminID);
			$stmt->execute();
			$_SESSION['username'] = $username;
			js_alert('Successfully updated the admin account');
			js_redirect('userreg.php');
			exit;
		} catch (mysqli_sql_exception $e) {
			js_alert(($e->getCode() == 1062) ? 'That username is already taken.' : 'The account could not be saved.');
		}
	}
}

$stmt = $conn->prepare("SELECT username, password FROM usertbl WHERE ID = ? AND user_type = 'admin' LIMIT 1");
$stmt->bind_param("i", $adminID);
$stmt->execute();
$rowfetch = $stmt->get_result()->fetch_assoc();
if (!$rowfetch) {
	header('Location: ../index.php');
	exit;
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
include('includes/menu1.php');
?>
</nav>





<div id="main">
	<div class="shell">
	<center>
	<form class='cmxform' method='POST'>
	<fieldset>
			<legend>Account Details</legend>
			  <ol>
			  <li>
				  <label for="name">UserName<em>*</em></label>
				  <input type='text' required='required' maxlength='50' id="name" name='username' value="<?php echo h($rowfetch['username']); ?>"/>
				  <label for="name">Password<em>*</em></label>
				  <input type='text' required='required' maxlength='50' id="name" name='password' value="<?php echo h($rowfetch['password']); ?>"/>
				</li>
				
  </ol>
			
	</fieldset>
	
	
<div align='center'><input type='submit' name='register' value='Save changes'></div>
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
			Copyright 2014 | Online Enrollment
		</p> 
		<!-- /Copyrights --> 
		
		<div class="cl">&nbsp;</div> 
	</div>
</div>




</body>
</html>
