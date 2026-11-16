<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');

// Keep what was typed so the two account fields are never "undefined" on the first visit.
$username = '';
$password = '';

if (isset($_POST['register'])) {
	$in = [];
	foreach (["username", "password", "course", "yrlvlsec", "snum", "fname", "minitial", "lname", "gender", "bdate", "address", "cnumber", "email", "fathername", "mothername", "guardianname", "cstatus", "bplace", "elemschool", "yrgradelem", "hschool", "yrgradhs", "lastschool", "yrlast", "address2"] as $k) {
		$in[$k] = trim($_POST[$k] ?? '');
	}
	$username = $in['username'];
	$password = $in['password'];
	$fullname = $in['fname'] . " " . $in['minitial'] . " " . $in['lname'];

	if ($username === '' || $password === '' || $in['snum'] === '') {
		js_alert('Username, password and student number are required.');
	} else {
		try {
			$conn->begin_transaction();

			$stmt = $conn->prepare("INSERT INTO usertbl (username, password, fullname, user_type) VALUES (?, ?, ?, 'student')");
			$stmt->bind_param("sss", $username, $password, $fullname);
			$stmt->execute();
			$accountID = $conn->insert_id;

			$stmt2 = $conn->prepare("INSERT INTO studenttbl
				(accountID, studnum, fname, minitial, lname, address, contact, emailaddress, birthdate, course, yearlvl, gender,
				 fathername, mothername, guardianname, birthplace, civilstatus, elemschool, dategradelem, hsschool, datehsgrad,
				 lastgrad, datelastgrad, addresslastgrad)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt2->bind_param("isssssssssssssssssssssss", $accountID, $in['snum'], $in['fname'], $in['minitial'], $in['lname'],
				$in['address'], $in['cnumber'], $in['email'], $in['bdate'], $in['course'], $in['yrlvlsec'], $in['gender'],
				$in['fathername'], $in['mothername'], $in['guardianname'], $in['bplace'], $in['cstatus'], $in['elemschool'],
				$in['yrgradelem'], $in['hschool'], $in['yrgradhs'], $in['lastschool'], $in['yrlast'], $in['address2']);
			$stmt2->execute();

			$conn->commit();
			js_alert('Successfully Created new account');
			js_redirect('addnewuser.php');
			exit;
		} catch (mysqli_sql_exception $e) {
			$conn->rollback();
			$msg = $e->getMessage();
			if (strpos($msg, 'uq_user_username') !== false) {
				js_alert('That username is already taken.');
			} elseif (strpos($msg, 'uq_student_studnum') !== false) {
				js_alert('That student number is already registered.');
			} else {
				js_alert('The account could not be saved. Please check the details and try again.');
			}
		}
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
				  <input type='text' required='required' maxlength='50' id="name" name='username' value="<?php echo h($username); ?>"/>
				  <label for="name">Password<em>*</em></label>
				  <input type='text' required='required' maxlength='50' id="name" name='password' value="<?php echo h($password); ?>" />
				</li>
				
  </ol>
			
	</fieldset>
	
	<fieldset>
			<legend>College Details</legend>
			  <ol>
			  <li>
				  <label for="name">Student Number:<em>*</em></label>
				  <input type='text' maxlength='50' id="name" required='required' name='snum' />
				  <label for="name">Course<em>*</em></label>
				  <select name='course'>
				  <option value='bsit'>BSIT</option>
				  <option value='bscs'>BSCS</option></select>
				  <label for="name">Section/Year Level:<em>*</em></label>
				  <input type='text' required='required' maxlength='50' id="name" name='yrlvlsec' />
				</li>
				
			</ol>
			
	</fieldset>
	
	<fieldset>	
			  <legend>Personal Details</legend>
			  <ol>
			  <li>
					<table>
						<tr>
							<td>First Name:</td><td><input type='text' maxlength='50' id="name" name='fname' /></td><td>Civil Status:</td><td><select name='cstatus'>
																																				<option value='single'>Single</option>
																																				<option value='married'>Married</option>
																																				<option value='widow'>Widow</option>
																																				</select></td>
						</tr>
						<tr>
							<td>Middle Initial:</td><td align='left'><input type='text' style='width:20px;' maxlength='5' id="name" name='minitial' /></td>
							<td>Guardian's Name:</td><td align='left'><input type='text'  maxlength='50' id="name" name='guardianname' /></td>
						</tr>
						<tr>
							<td>Last Name:</td><td><input type='text' maxlength='50' id="name" name='lname' /></td>
							<td>Father's Name:</td><td align='left'><input type='text'  maxlength='50' id="name" name='fathername' /></td>
						</tr>
						<tr>
							<td><label>Gender:</label></td><td align='left'><input type='radio' id='name' name='gender' value='male'>Male &nbsp;&nbsp;<input type='radio' id='name' name='gender' value='female'>Female</td>
							<td>Mother's Name:</td><td align='left'><input type='text'  maxlength='50' id="name" name='mothername' /></td>
						</tr>
						<tr>
							<td>BirthDate:</td><td><input type="date" name="bdate" /></td><td>Birth Place:</td><td align='left'><input type='text'  maxlength='50' id="name" name='bplace' /></td>
						</tr>
					</table>
					
				</li>
						
				
			  <li>
				  <label for="address1">Address<em>*</em></label>
				  <textarea name='address' height='500' width='200'></textarea>
			</li>
			  <li>
				  <label for="address2">Contact Number</label>
				  <input type='text' name='cnumber' maxlength='11'/>
				</li>
			  <li>
				  <label for="town-city">Email Address</label>
				  <input type='text' name='email' id="town-city" />
				</li>
				  </ol>
				  </fieldset>
				<fieldset>
		<legend>Educational Background</legend>
			  <ol>
			  <li>
				  <table>
			
						<tr>
							<td>Elementary School:</td><td align='left'><input type='text'  id="name" name='elemschool' /></td>
							<td>Year Graduated:</td><td align='left'><select name='yrgradelem'>
																	<?php
																	 $num=1990;
																	 while($num<=2010)
																	 {
																		echo"<option value='$num'>$num</option>";
																		$num=$num + 1;
																	 }
																	?>
																	</select></td>
						</tr>
						<tr>
							<td>High School:</td><td><input type='text' id="name" name='hschool' /></td>
							<td>Year Graduated:</td><td align='left'><select name='yrgradhs'>
																	<?php
																	 $num=1990;
																	 while($num<=2010)
																	 {
																		echo"<option value='$num'>$num</option>";
																		$num=$num + 1;
																	 }
																	?>
																	</select></td>
						</tr>
						<tr>
							<td>School Last Attended:</td><td><input type='text' id="name" name='lastschool' /></td>
							<td>Year Graduated:</td><td align='left'><select name='yrlast'>
																	<?php
																	 $num=1990;
																	 while($num<=2010)
																	 {
																		echo"<option value='$num'>$num</option>";
																		$num=$num + 1;
																	 }
																	?>
																	</select></td>
						</tr>
						<tr>
							<td colspan='4' align='center'><label for="address1">Address<em>*</em></label>
				  <textarea name='address2' height='500' width='200'></textarea></td>
						</tr>
					</table>
			</li>
				
			</ol>
			
	</fieldset>
			  
			  


<div align='center'><input type='submit' name='register' value='Add new user'>&nbsp;&nbsp;
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
			Copyright 2014 | Online Enrollment
		</p> 
		<!-- /Copyrights --> 
		
		<div class="cl">&nbsp;</div> 
	</div>
</div>




</body>
</html>
