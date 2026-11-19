<?php
// Called before registration starts its transaction. MySQL commits schema changes implicitly.
function ensure_enrollment_access(mysqli $conn): void {
    $probe = 'SELECT u.must_change_password FROM usertbl u, faculty_subject f, enrollment_window w LIMIT 0';
    try {
        $conn->query($probe);
        return;
    } catch (mysqli_sql_exception $error) {
        if (!in_array($error->getCode(), [1054, 1146], true)) {
            throw $error;
        }
    }

    // Run the same additive migration used by administrators on restricted installations.
    $migration = file_get_contents(__DIR__ . '/../database/migrations/2026-enrollment-access.sql');
    try {
        $conn->multi_query($migration);
        do {
            $result = $conn->store_result();
            if ($result instanceof mysqli_result) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    } catch (mysqli_sql_exception $error) {
        // Another first request may have added the password column after the migration checked.
        if ($error->getCode() !== 1060) {
            throw $error;
        }
    }
    $conn->query($probe);
}
