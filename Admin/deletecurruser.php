<?php
if(isset($_GET['accid']))
{
	$num=$_GET['accid'];
	require_once(__DIR__ . '/../config/db.php');
	$getval=mysql_query("SELECT * FROM studenttbl WHERE ID = $num");
	$rowval=mysql_fetch_array($getval);
	$accountID	= $rowval['accountID'];
	$query2=mysql_query("DELETE FROM usertbl WHERE ID = $accountID");
	$query=mysql_query("DELETE FROM studenttbl WHERE ID = $num");
	
	echo"<script>window.alert('Successfully Removed the selected account')</script>";
	echo"<script>window.location='userreg.php'</script>";
}
else
{
	echo"<script>window.location='userreg.php'</script>";
}

?>