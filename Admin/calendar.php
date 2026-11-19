<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/enrollment_calendar.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sem = $_POST['semester'] ?? '';
    $dates = [];
    foreach (['starts','adjustment','ends'] as $key) {
        $value = $_POST[$key] ?? '';
        $date = is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
        if ($date && $date->format('Y-m-d') === $value) { $dates[] = $value; }
    }
    if (!csrf_valid() || !in_array($sem, SEMESTERS, true) || count($dates) !== 3 || $dates[0] >= $dates[1] || $dates[1] > $dates[2]) {
        notify_user('Choose a semester and valid dates in registration, adjustment, and closing order.');
    } else {
        [$start,$adjust,$end] = $dates;
        $year = SCHOOL_YEAR;
        $stmt = $conn->prepare("INSERT INTO enrollment_window (school_year, semester, starts, adjustment, ends) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE starts=VALUES(starts), adjustment=VALUES(adjustment), ends=VALUES(ends)");
        $stmt->bind_param('sssss',$year,$sem,$start,$adjust,$end);
        $stmt->execute();
        notify_user('Enrollment schedule published.', true);
    }
    redirect_to('calendar.php');
}
$windows = term_windows($conn);
$pageTitle = 'Enrollment schedule';
require __DIR__ . '/../includes/workspace_top.php';
?>
<p>Publish dates for <?= h(SCHOOL_YEAR) ?>. Students can submit during registration and adjustment, through the closing date in Manila time.</p>
<?php foreach (SEMESTERS as $sem) { $w = $windows[$sem] ?? []; ?>
<h2><?= h($sem) ?></h2><form class="toolbar" method="post"><?= csrf_field() ?>
<input type="hidden" name="semester" value="<?= h($sem) ?>">
<?php foreach (['starts'=>'Registration opens','adjustment'=>'Adjustment begins','ends'=>'Closes'] as $key=>$label) { ?>
<label><?= $label ?><input type="date" required name="<?= $key ?>" value="<?= h($w[$key] ?? '') ?>"></label>
<?php } ?><button>Publish schedule</button></form>
<?php } require __DIR__ . '/../includes/workspace_bottom.php'; ?>
