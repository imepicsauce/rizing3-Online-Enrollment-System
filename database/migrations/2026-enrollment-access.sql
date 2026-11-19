-- Run once against the existing enrollment database (no data reset).
ALTER TABLE usertbl ADD COLUMN must_change_password TINYINT NOT NULL DEFAULT 0;
CREATE TABLE faculty_subject (
    subjectID INT PRIMARY KEY,
    facultyID INT NOT NULL,
    FOREIGN KEY (subjectID) REFERENCES subjecttbl(ID) ON DELETE CASCADE,
    FOREIGN KEY (facultyID) REFERENCES usertbl(ID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE enrollment_window (
    school_year VARCHAR(20) NOT NULL,
    semester VARCHAR(10) NOT NULL,
    starts DATE NOT NULL,
    adjustment DATE NOT NULL,
    ends DATE NOT NULL,
    PRIMARY KEY (school_year, semester)
) ENGINE=InnoDB;
