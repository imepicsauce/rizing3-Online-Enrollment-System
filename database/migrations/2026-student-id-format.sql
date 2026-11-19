CREATE TABLE IF NOT EXISTS student_id_sequence (
    registration_year SMALLINT UNSIGNED PRIMARY KEY,
    last_issued SMALLINT UNSIGNED NOT NULL
) ENGINE=InnoDB;
