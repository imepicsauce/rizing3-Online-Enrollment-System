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
</head>

<?php

?>

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
	<div style='float:left; margin-left: 350px;'><center><b><h1>Subjects List</h1></b></center></div>
	<div style='float:right; margin-right:25px; margin-bottom:20px;'><a href='addnewsubj.php'><img src='../assets/img/iconadd2.png' width='50' ></a></div>
		<div class="niceform">

		
		 <table id="datatables" class="display">
             <thead><tr>
				<th>Subject Code</th>
             	<th>Subject Name</th>
             	<th># of Units</th>
				<th>Action</th>
              </tr></thead>    
              <tbody>
			  <?php
				$getsubjects = $conn->query("SELECT ID, subjectcode, subjecttitle, numofunits FROM subjecttbl");
				if ($getsubjects->num_rows == 0) {
					echo "<tr><td colspan='4' align='center'>No Entries</td></tr>";
				}
				while ($r = $getsubjects->fetch_assoc()) { ?>
				<tr>
					<td><?php echo h($r['subjectcode']); ?></td>
					<td><?php echo h($r['subjecttitle']); ?></td>
					<td><?php echo h($r['numofunits']); ?></td>
					<td>
						<a href="editsubject.php?id=<?php echo (int)$r['ID']; ?>">EDIT</a> |
						<form method="post" action="deletecurrsubj.php" style="display:inline" onsubmit="return confirm('Are you sure?');">
							<input type="hidden" name="accid" value="<?php echo (int)$r['ID']; ?>">
							<?php echo csrf_field(); ?>
							<button type="submit" class="link-button">DELETE</button>
						</form>
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
			Copyright 2014 | Online Enrollment
		</p> 
		<!-- /Copyrights --> 
		
		<div class="cl">&nbsp;</div> 
	</div>
</div>




</body>
</html>