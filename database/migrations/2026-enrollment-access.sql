-- Safe to apply to existing or partially upgraded enrollment databases.
CREATE TABLE IF NOT EXISTS faculty_subject (
    subjectID INT PRIMARY KEY,
    facultyID INT NOT NULL,
    FOREIGN KEY (subjectID) REFERENCES subjecttbl(ID) ON DELETE CASCADE,
    FOREIGN KEY (facultyID) REFERENCES usertbl(ID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS enrollment_window (
    school_year VARCHAR(20) NOT NULL,
    semester VARCHAR(10) NOT NULL,
    starts DATE NOT NULL,
    adjustment DATE NOT NULL,
    ends DATE NOT NULL,
    PRIMARY KEY (school_year, semester)
) ENGINE=InnoDB;

-- MySQL and MariaDB both support this conditional ALTER.
SET @rizing_password_ddl = IF(
    EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE()
           AND table_name = 'usertbl' AND column_name = 'must_change_password'),
    'SELECT 1',
    'ALTER TABLE usertbl ADD COLUMN must_change_password TINYINT NOT NULL DEFAULT 0'
);
PREPARE rizing_password_upgrade FROM @rizing_password_ddl;
EXECUTE rizing_password_upgrade;
DEALLOCATE PREPARE rizing_password_upgrade;
