<?php
require_once __DIR__ . '/enrollment_calendar.php';

function student_enrollment_terms(mysqli $conn, string $studentID): array {
    $windows = term_windows($conn);
    $year = SCHOOL_YEAR;
    $stmt = $conn->prepare("SELECT ID, semester, approved FROM student_subject WHERE studentID=? AND school_year=?");
    $stmt->bind_param('ss', $studentID, $year);
    $stmt->execute();
    $forms = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), null, 'semester');

    $terms = [];
    foreach (SEMESTERS as $semester) {
        $window = $windows[$semester] ?? null;
        $phase = enrollment_phase($window);
        $form = $forms[$semester] ?? null;
        $status = $form ? ($form['approved'] ? 'Approved' : 'Pending approval') : $phase;
        $terms[$semester] = [
            'window' => $window, 'phase' => $phase, 'status' => $status,
            'formID' => $form ? (int)$form['ID'] : null,
            'eligible' => !$form && enrollment_open($window),
        ];
    }
    return $terms;
}

function featured_enrollment_term(array $terms): string {
    // Offer an available semester first, then an open semester's existing form.
    foreach ($terms as $semester => $term) {
        if ($term['eligible']) { return $semester; }
    }
    foreach (['Pending approval', 'Approved'] as $status) {
        foreach ($terms as $semester => $term) {
            if ($term['status'] === $status && enrollment_open($term['window'])) { return $semester; }
        }
    }
    foreach (['Pending approval', 'Upcoming', 'Approved', 'Awaiting schedule'] as $status) {
        foreach ($terms as $semester => $term) {
            if ($term['status'] === $status) { return $semester; }
        }
    }
    return array_key_last($terms);
}

function student_term_timing(array $term): string {
    $window = $term['window'];
    if (!$window) { return 'The registrar hasn’t published enrollment dates yet.'; }
    if ($term['phase'] === 'Upcoming') {
        return 'Registration opens ' . (new DateTimeImmutable($window['starts']))->format('F j, Y') . '.';
    }
    $lastDay = (new DateTimeImmutable($window['ends']))->format('F j, Y');
    return $term['phase'] === 'Closed' ? "Enrollment closed $lastDay." : "Last day to enroll: $lastDay (Manila time).";
}

function student_term_action(string $semester, array $term): array {
    if ($term['formID']) {
        return [$term['status'] === 'Approved' ? 'View approved enrollment' : 'View submitted form', 'report.php#form-' . $term['formID']];
    }
    $url = 'enroll.php?sem=' . rawurlencode($semester);
    return $term['eligible'] ? ['Choose subjects', $url] : ['View enrollment calendar', $url . '&calendar=1#calendar'];
}
