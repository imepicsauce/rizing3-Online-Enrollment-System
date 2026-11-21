<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/student_enrollment_terms.php';
$stmt = $conn->prepare('SELECT studnum, course, yearlvl FROM studenttbl WHERE accountID=?');
$stmt->bind_param('i', $_SESSION['userID']);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$pageTitle = 'Enrollment overview';
require __DIR__ . '/../includes/workspace_top.php';
?>
<?php if (!$record) { ?>
<p>Ask the registrar to link your student record before enrolling.</p>
<?php } else {
    $terms = student_enrollment_terms($conn, $record['studnum']);
    $semester = featured_enrollment_term($terms);
    $term = $terms[$semester];
    [$label, $url] = student_term_action($semester, $term);
?>
<section class="feature enrollment-focus">
    <div><h2><?= h($semester) ?></h2><span class="term-state"><?= h($term['status']) ?></span></div>
    <p><?= h(strtoupper($record['course'])) ?> · <?= h($record['yearlvl']) ?></p>
    <?php if ($term['status'] === 'Pending approval') { ?><p>The registrar is reviewing your submitted subjects.</p>
    <?php } elseif ($term['status'] === 'Approved') { ?><p>The registrar approved your enrollment for this semester.</p><?php } ?>
    <p><?= h(student_term_timing($term)) ?></p>
    <a class="btn" href="<?= h($url) ?>"><?= h($label) ?></a>
</section>
<h2>Other semesters</h2>
<?php foreach ($terms as $name => $item) {
    if ($name === $semester) { continue; }
    [$linkText, $link] = student_term_action($name, $item); ?>
<div class="term"><strong><?= h($name) ?></strong><span><?= h($item['status']) ?></span>
<a href="<?= h($link) ?>"><?= h($linkText) ?></a></div>
<?php } ?>
<details class="enrollment-guide"><summary>How to Enroll</summary>
<p>Check your <a href="curriculum.php">curriculum</a>, choose subjects during an open semester, then review the fees and submit your form to the registrar.</p></details>
<?php } require __DIR__ . '/../includes/workspace_bottom.php'; ?>
