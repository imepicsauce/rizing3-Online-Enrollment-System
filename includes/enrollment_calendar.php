<?php
require_once __DIR__ . '/../config/settings.php';

function term_windows($conn) {
    $year = SCHOOL_YEAR;
    $stmt = $conn->prepare("SELECT semester, starts, adjustment, ends FROM enrollment_window WHERE school_year=?");
    $stmt->bind_param('s', $year);
    $stmt->execute();
    return array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), null, 'semester');
}

function enrollment_today() {
    return (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d');
}

function enrollment_phase($window, $day = null) {
    $day = $day ?? enrollment_today();
    if (!$window) { return 'Awaiting schedule'; }
    if ($day < $window['starts']) { return 'Upcoming'; }
    if ($day > $window['ends']) { return 'Closed'; }
    return $day < $window['adjustment'] ? 'Registration' : 'Adjustment';
}

function enrollment_open($window) {
    return in_array(enrollment_phase($window), ['Registration', 'Adjustment'], true);
}
