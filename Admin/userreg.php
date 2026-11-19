<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>John Paul College</title>

	

<link href="../assets/css/style.css" rel="stylesheet" type="text/css"/>
<script src="../assets/js/jquery-1.7.min.js"></script>

	<script src="../assets/js/jquery.dataTables.js" type="text/javascript"></script>
    <script src="../assets/js/jquery.easing.1.3.js"></script>

  <style type="text/css">
			@import "../assets/css/vendor/demo_table_jui.css";
            @import "../assets/css/vendor/jquery-ui-1.8.4.custom.css";
    </style>
        
        <script type="text/javascript" charset="utf-8">
            $(document).ready(function(){
                $('#datatables').dataTable({
                    "sPaginationType":"full_numbers",
                    "aaSorting":[[2, "desc"]],
                    "bJQueryUI":true
                });
            })
        </script>
<link rel="stylesheet" href="../assets/css/feedback.css">
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
<?php render_notices(); ?>
	<div style='float:left; margin-left: 450px;'><center><b><h1>Registered User List</h1></b></center></div>
	<div style='float:right; margin-right:25px; margin-bottom:20px;'><a href='adminedit.php'><img src='../assets/img/adminedit.png' title='edit admin details' width='50' ></a><a href='addnewuser.php'><img title='Add new user' src='../assets/img/iconadd.png' width='50' ></a></div>
		<div class="niceform">

		
		 <table id="datatables" class="display">
             <thead><tr>
				<th>Student ID</th>
             	<th>Student Name</th>
             	<th>Year Level</th>
             	<th>Course</th>
				<th>Action</th>
              </tr></thead>    
              <tbody>
			  <?php
				$getusers = $conn->query("SELECT ID, studnum, fname, minitial, lname, course, yearlvl FROM studenttbl");
				if ($getusers->num_rows == 0) {
					echo "<tr><td colspan='5' align='center'>No Entries</td></tr>";
				}
				while ($r = $getusers->fetch_assoc()) { ?>
				<tr>
					<td><?php echo h($r['studnum']); ?></td>
					<td><?php echo h($r['fname'] . ' ' . $r['minitial'] . ' ' . $r['lname']); ?></td>
					<td><?php echo h($r['yearlvl']); ?></td>
					<td><?php echo h($r['course']); ?></td>
					<td>
						<a href="edituser.php?id=<?php echo (int)$r['ID']; ?>">EDIT</a> |
						<details class="confirm-action"><summary>Delete</summary>
<p>Delete student <?= h($r['studnum']) ?>? This also removes their profile and enrollment forms.</p>
<form method="post" action="deletecurruser.php" style="display:inline">
							<input type="hidden" name="accid" value="<?php echo (int)$r['ID']; ?>">
							<?php echo csrf_field(); ?>
							<button type="submit" class="link-button">Delete student</button>
						</form>
</details>
					</td>
				</tr>
				<?php } ?>
				</tbody>
		</table>
</div>
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