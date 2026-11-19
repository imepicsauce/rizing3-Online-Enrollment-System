<?php
require_once __DIR__ . '/../Admin/includes/student_form_lib.php';

function register_student(mysqli $conn, array $v): string {
    $fullname = trim(implode(' ', array_filter([$v['fname'], $v['minitial'], $v['lname']], 'strlen')));
    $temporaryID = bin2hex(random_bytes(10));
    $passwordHash = password_make($v['bdate']);

    // Reserve the database-generated student record number before publishing it as the login.
    $conn->begin_transaction();
    try {
        $account = $conn->prepare("INSERT INTO usertbl (username, password, fullname, user_type, must_change_password)
            VALUES (?, ?, ?, 'student', 1)");
        $account->bind_param("sss", $temporaryID, $passwordHash, $fullname);
        $account->execute();
        $accountID = $conn->insert_id;

        $profile = $conn->prepare("INSERT INTO studenttbl
            (accountID, studnum, fname, minitial, lname, address, contact, emailaddress, birthdate, course, yearlvl, gender,
             fathername, mothername, guardianname, birthplace, civilstatus, elemschool, dategradelem, hsschool, datehsgrad,
             lastgrad, datelastgrad, addresslastgrad)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $profile->bind_param("isssssssssssssssssssssss", $accountID, $temporaryID, $v['fname'], $v['minitial'], $v['lname'],
            $v['address'], $v['cnumber'], $v['email'], $v['bdate'], $v['course'], $v['yrlvlsec'], $v['gender'],
            $v['fathername'], $v['mothername'], $v['guardianname'], $v['bplace'], $v['cstatus'], $v['elemschool'],
            $v['yrgradelem'], $v['hschool'], $v['yrgradhs'], $v['lastschool'], $v['yrlast'], $v['address2']);
        $profile->execute();
        $recordID = $conn->insert_id;
        $studentID = sprintf('RU-%08d', $recordID);

        $identity = $conn->prepare("UPDATE studenttbl s JOIN usertbl u ON u.ID=s.accountID
            SET s.studnum=?, u.username=? WHERE s.ID=?");
        $identity->bind_param("ssi", $studentID, $studentID, $recordID);
        $identity->execute();
        $conn->commit();
        return $studentID;
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
}
