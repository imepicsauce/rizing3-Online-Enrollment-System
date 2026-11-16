<?php
if(isset($_GET['accid']))
{
	$num=$_GET['accid'];
	require_once(__DIR__ . '/../config/db.php');
	
	$query2=mysql_query("DELETE FROM subjecttbl WHERE ID = '$num'");
	echo"<script>window.alert('Successfully Removed the selected subject')</script>";
	echo"<script>window.location='subjectreg.php'</script>";
}
else
{
	echo"<script>window.location='subjectreg.php'</script>";
}

?>