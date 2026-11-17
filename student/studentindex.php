<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('student');
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
		<!-- Box -->
		<div class="box">
			<h2>Welcome Student</h2>
			
			<div class="entry">
				 <p><b>John Paul College</b></font></p>
		<br><b>Mission:</b></font>
        <p> A center of learning and academic excellence in the Province of Oriental Mindoro</p>
        <br><b>Vision:</b></font>
        <p>John Paul College is committed to achieve lasting improvements in education and in the lives of the people by producing students who have the ability to think critically, create, lead, compete and excel towards full development of the family, community and global environment</p>
			</div>
			
		</div>
		<!-- /Box -->
		<!-- Box -->
		<div class="box">
			<h2>How to Enroll</h2>
			
			<div class="entry">
				<div class="news">
					<ul>
						<li>
							<div class="post-data">
								<h5>1. Check your curriculum</h5>
								<p>Open <a href="curriculum.php">Curriculum</a> to see the subjects offered for your course.</p>
							</div>
							<div class="cl">&nbsp;</div>
						</li>
						<li>
							<div class="post-data">
								<h5>2. Fill in your enrollment form</h5>
								<p>Go to <a href="enroll.php">Enroll Now</a>, choose the semester and tick your subjects. Review the form, then submit it. You get one form per semester.</p>
							</div>
							<div class="cl">&nbsp;</div>
						</li>
						<li class="last">
							<div class="post-data">
								<h5>3. Wait for approval</h5>
								<p>The administrator approves your form. Check its status, print it, or cancel a pending form in <a href="report.php">My Enrollment Report</a> on your profile page.</p>
							</div>
							<div class="cl">&nbsp;</div>
						</li>
					</ul>
				</div>
			</div>
			
			
		</div>
		<!-- /Box -->
		<!-- Box -->
		<div class="box last-box">
			<h2>Programs</h2>
			
			<div class="entry bullet-list">
				<ul>
					  <li>CHED COURSES</li>
	                    <li>TESDA COURSES</li>
				</ul>
				<div class="post-image">
								<img src="../assets/img/news3.jpg" width='100' height='100' alt="" />
								<img src="../assets/img/news4.jpg" width='100' height='100' alt="" />
							</div>
			</div>
		
		</div>
		<!-- /Box -->
		<div class="cl">&nbsp;</div>
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