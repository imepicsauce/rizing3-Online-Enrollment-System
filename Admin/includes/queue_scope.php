<?php
require_once __DIR__ . '/../../includes/enrollment_calendar.php';

function registrar_default_semester(array $windows): string {
    $open = array_values(array_filter(SEMESTERS, fn($sem) => enrollment_open($windows[$sem] ?? null)));
    return count($open) === 1 ? $open[0] : '';
}
