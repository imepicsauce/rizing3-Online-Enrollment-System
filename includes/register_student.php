<?php
require_once __DIR__ . '/../Admin/includes/student_form_lib.php';

// The caller holds the transaction until both the account and student profile are saved.
function next_student_id(mysqli $conn): string {
    $year = (int)(new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y');
    $lock = $conn->prepare("INSERT INTO student_id_sequence (registration_year, last_issued)
        VALUES (?, 0) ON DUPLICATE KEY UPDATE last_issued=last_issued");
    $lock->bind_param('i', $year);
    $lock->execute();

    $read = $conn->prepare("SELECT last_issued FROM student_id_sequence WHERE registration_year=? FOR UPDATE");
    $read->bind_param('i', $year);
    $read->execute();
    $number = (int)$read->get_result()->fetch_row()[0];
    $used = $conn->prepare("SELECT EXISTS(SELECT 1 FROM studenttbl WHERE studnum=?)
        OR EXISTS(SELECT 1 FROM usertbl WHERE username=?)");
    $prefix = sprintf('%02d-', $year % 100);
    while ($number < 9999) {
        $studentID = $prefix . sprintf('%04d', ++$number);
        $used->bind_param('ss', $studentID, $studentID);
        $used->execute();
        if (!$used->get_result()->fetch_row()[0]) {
            $save = $conn->prepare("UPDATE student_id_sequence SET last_issued=? WHERE registration_year=?");
            $save->bind_param('ii', $number, $year);
            $save->execute();
            return $studentID;
        }
    }
    throw new OverflowException("All student IDs for this registration year have been issued. Please contact the registrar.");
}

function register_student(mysqli $conn, array $v): string {
    $fullname = trim(implode(' ', array_filter([$v['fname'], $v['minitial'], $v['lname']], 'strlen')));
    $passwordHash = password_make($v['bdate']);

    $conn->begin_transaction();
    try {
        $studentID = next_student_id($conn);
        $account = $conn->prepare("INSERT INTO usertbl (username, password, fullname, user_type, must_change_password)
            VALUES (?, ?, ?, 'student', 1)");
        $account->bind_param("sss", $studentID, $passwordHash, $fullname);
        $account->execute();
        $accountID = $conn->insert_id;

        $profile = $conn->prepare("INSERT INTO studenttbl
            (accountID, studnum, fname, minitial, lname, address, contact, emailaddress, birthdate, course, yearlvl, gender,
             fathername, mothername, guardianname, birthplace, civilstatus, elemschool, dategradelem, hsschool, datehsgrad,
             lastgrad, datelastgrad, addresslastgrad)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $profile->bind_param("isssssssssssssssssssssss", $accountID, $studentID, $v['fname'], $v['minitial'], $v['lname'],
            $v['address'], $v['cnumber'], $v['email'], $v['bdate'], $v['course'], $v['yrlvlsec'], $v['gender'],
            $v['fathername'], $v['mothername'], $v['guardianname'], $v['bplace'], $v['cstatus'], $v['elemschool'],
            $v['yrgradelem'], $v['hschool'], $v['yrgradhs'], $v['lastschool'], $v['yrlast'], $v['address2']);
        $profile->execute();
        $conn->commit();
        return $studentID;
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
}
