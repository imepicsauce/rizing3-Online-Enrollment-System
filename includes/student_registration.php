<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/register_student.php';

$courses = array_column($conn->query("SELECT course_code FROM courses_tbl ORDER BY course_code")->fetch_all(MYSQLI_ASSOC), 'course_code');
$v = [];
$isEdit = false;

if (isset($_POST['register'])) {
    foreach (STUDENT_FIELDS as $key) {
        $value = $_POST[$key] ?? '';
        $v[$key] = is_string($value) ? trim($value) : '';
    }
    if (!empty($publicRegistration) && $v['yrlvlsec'] === '') {
        $v['yrlvlsec'] = 'Pending assignment';
    }
    if (!csrf_valid()) {
        notify_user('Your session expired. Please try again.');
    } elseif ($v['bdate'] === '') {
        notify_user('Enter a birthdate to set the initial password.');
    } elseif (!in_array($v['course'], $courses, true)) {
        notify_user('Please select a valid course.');
    } elseif (($err = student_validate($v)) !== '') {
        notify_user($err);
    } else {
        try {
            $studentID = register_student($conn, $v);
            if (!empty($publicRegistration)) {
                $_SESSION['new_student_id'] = $studentID;
                unset($_SESSION['csrf']);
            }
            notify_user("Student ID: $studentID. The initial password is the birthdate in YYYY-MM-DD format.", true);
            redirect_to($registrationDestination);
        } catch (mysqli_sql_exception $error) {
            notify_user('Registration could not be saved. Check the form or contact the registrar.');
        }
    }
}
