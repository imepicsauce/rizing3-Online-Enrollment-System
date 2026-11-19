<?php
require_once __DIR__ . '/includes/auth.php';
$publicRegistration = true;
$registrationDestination = 'portal.php';
require __DIR__ . '/includes/student_registration.php';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create student account · Rizing University</title><link rel="stylesheet" href="assets/css/workspace.css"><link rel="stylesheet" href="assets/css/feedback.css"><link rel="stylesheet" href="assets/css/motion.css"></head>
<body><header class="header"><div class="bar"><a class="brand" href="./"><span class="mark" aria-hidden="true">RU</span>Rizing University</a><span class="identity"><a href="portal.php">Portal sign in</a></span></div></header>
<main class="registration"><h1>Create student account</h1>
<p>Complete your student profile to receive your student ID. Your initial password is your birthdate in YYYY-MM-DD format.</p>
<?php render_notices(); ?>
<form class="cmxform" method="post"><?php require __DIR__ . '/Admin/includes/student_form.php'; ?>
<button name="register">Create student account</button></form></main></body></html>
