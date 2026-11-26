-- ============================================================
--  online_enroll  (revised schema with working relationships)
--
--  WARNING: this file DROPS and recreates every table, so
--  importing it replaces all existing data in `online_enroll`.
--
--  Relationships
--    usertbl.ID            <- studenttbl.accountID        (CASCADE)
--    studenttbl.studnum    <- student_subject.studentID   (CASCADE)
--    courses_tbl.course_code <- studenttbl.course
--                            <- subjecttbl.course_con
--                            <- sections_tbl.section_course
--    sections_tbl.section_ID <- sched_tbl.section_ID      (CASCADE)
--    subjecttbl.ID         <- sched_tbl.subject_ID        (CASCADE)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `online_enroll`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `online_enroll`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS faculty_subject, enrollment_window, student_id_sequence;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `sched_tbl`;
DROP TABLE IF EXISTS `student_subject`;
DROP TABLE IF EXISTS `studenttbl`;
DROP TABLE IF EXISTS `sections_tbl`;
DROP TABLE IF EXISTS `subjecttbl`;
DROP TABLE IF EXISTS `courses_tbl`;
DROP TABLE IF EXISTS `usertbl`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- usertbl : login accounts (admin and student)
-- ------------------------------------------------------------
CREATE TABLE `usertbl` (
  `ID`        int(11)      NOT NULL AUTO_INCREMENT,
  `username`  varchar(50)  NOT NULL,
  `password`  varchar(255) NOT NULL,
  `fullname`  varchar(100) NOT NULL,
  `user_type` varchar(20)  NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=7;

INSERT INTO `usertbl` (`ID`, `username`, `password`, `fullname`, `user_type`) VALUES
(2, 'admins', 'admins', 'Dennis O. Andes', 'admin'),
(6, 'dennis', '123456', 'Dennis D. Tech', 'student');

-- ------------------------------------------------------------
-- courses_tbl : one row per course. course_code is the short
-- code ('bsit', 'bscs') that the other tables point to.
-- (course_code is a new column; no PHP page inserts into this table.)
-- ------------------------------------------------------------
CREATE TABLE `courses_tbl` (
  `course_ID`    int(11)      NOT NULL AUTO_INCREMENT,
  `course_code`  varchar(20)  NOT NULL,
  `course_title` varchar(100) NOT NULL,
  `is_offered`   TINYINT NOT NULL DEFAULT 1,
  `course_desc`  longtext     NOT NULL,
  PRIMARY KEY (`course_ID`),
  UNIQUE KEY `uq_course_code` (`course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3;

INSERT INTO `courses_tbl` (`course_ID`, `course_code`, `course_title`, `course_desc`) VALUES
(1, 'bsit', 'Bachelor of Science in Information Technology', ''),
(2, 'bscs', 'Bachelor of Science in Computer Science', '');

-- ------------------------------------------------------------
-- studenttbl : one student profile per student account
-- ------------------------------------------------------------
CREATE TABLE `studenttbl` (
  `ID`            int(11)      NOT NULL AUTO_INCREMENT,
  `accountID`     int(11)      NOT NULL,
  `studnum`       varchar(20)  NOT NULL,
  `fname`         varchar(100) NOT NULL,
  `minitial`      varchar(100) NOT NULL,
  `lname`         varchar(100) NOT NULL,
  `address`       varchar(500) NOT NULL,
  `contact`       varchar(20)  NOT NULL,
  `emailaddress`  varchar(100) NOT NULL,
  `birthdate`     varchar(100) NOT NULL,
  `course`        varchar(20)  NOT NULL,
  `yearlvl`       varchar(100) NOT NULL,
  `gender`        varchar(11)  NOT NULL,
  `fathername`    varchar(100) NOT NULL,
  `mothername`    varchar(100) NOT NULL,
  `guardianname`  varchar(100) NOT NULL,
  `birthplace`    varchar(100) NOT NULL,
  `civilstatus`   varchar(100) NOT NULL,
  `elemschool`    varchar(100) NOT NULL,
  `dategradelem`  varchar(100) NOT NULL,
  `hsschool`      varchar(100) NOT NULL,
  `datehsgrad`    varchar(100) NOT NULL,
  `lastgrad`      varchar(100) NOT NULL,
  `datelastgrad`  varchar(100) NOT NULL,
  `addresslastgrad` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_student_account` (`accountID`),
  UNIQUE KEY `uq_student_studnum` (`studnum`),
  KEY `idx_student_course` (`course`),
  CONSTRAINT `fk_student_account` FOREIGN KEY (`accountID`)
    REFERENCES `usertbl` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_course` FOREIGN KEY (`course`)
    REFERENCES `courses_tbl` (`course_code`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3;

INSERT INTO `studenttbl` (`ID`, `accountID`, `studnum`, `fname`, `minitial`, `lname`, `address`, `contact`, `emailaddress`, `birthdate`, `course`, `yearlvl`, `gender`, `fathername`, `mothername`, `guardianname`, `birthplace`, `civilstatus`, `elemschool`, `dategradelem`, `hsschool`, `datehsgrad`, `lastgrad`, `datelastgrad`, `addresslastgrad`) VALUES
(2, 6, '2014-052', 'Karl Macniel', 'E', 'Flores', 'asdadasdasd', '09312312313', 'karloemilflore@example.com', '2015-01-14', 'bsit', 'II-2', 'male', 'Lopez Alcadia', 'Mauricia Magalpok', 'Arutro Manansala', 'Manila', 'single', 'Lacs', '1990', 'Mals', '1990', 'Navs', '1990', 'Navs');

-- ------------------------------------------------------------
-- subjecttbl : subjects offered, grouped by course
-- ------------------------------------------------------------
CREATE TABLE `subjecttbl` (
  `ID`           int(11)      NOT NULL AUTO_INCREMENT,
  `subjectcode`  varchar(100) NOT NULL,
  `subjecttitle` varchar(100) NOT NULL,
  `numofunits`   varchar(100) NOT NULL,
  `room`         varchar(100) NOT NULL,
  `time`         varchar(100) NOT NULL,
  `instructor`   varchar(100) NOT NULL,
  `day`          varchar(100) NOT NULL,
  `islab`        int(11)      NOT NULL,
  `istutorial`   int(11)      NOT NULL,
  `description`  varchar(100) NOT NULL,
  `semester`     varchar(10) NOT NULL DEFAULT '',
  `course_con`   varchar(20)  NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_subject_code` (`subjectcode`),
  KEY `idx_subject_course` (`course_con`),
  CONSTRAINT `fk_subject_course` FOREIGN KEY (`course_con`)
    REFERENCES `courses_tbl` (`course_code`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=5;

INSERT INTO `subjecttbl` (`ID`, `subjectcode`, `subjecttitle`, `numofunits`, `room`, `time`, `instructor`, `day`, `islab`, `istutorial`, `description`, `course_con`) VALUES
(1, 'IT201', 'File Organization', '2', '', '', '', '', 0, 0, 'asdasdasd', 'bsit'),
(3, 'IT202', 'File Manage', '2', '', '', '', '', 0, 0, 'asdsadsad', 'bsit'),
(4, 'CS 103', 'Farenheit', '3', '', '', '', '', 1, 0, 'sadadsdsad', 'bsit');

-- ------------------------------------------------------------
-- student_subject : one row per submitted enrollment form.
-- studentID is the student number (studenttbl.studnum).
-- `subjects` is still a comma-separated list of subject codes,
-- so it cannot carry a foreign key (see the notes after this file).
-- ------------------------------------------------------------
CREATE TABLE `student_subject` (
  `ID`          int(11)      NOT NULL AUTO_INCREMENT,
  `studentID`   varchar(20)  NOT NULL,
  `subjects`    longtext     NOT NULL,
  `totalunits`  int(11)      NOT NULL,
  `approved`    tinyint(1)   NOT NULL DEFAULT 0,
  `semester`    varchar(10)  NOT NULL DEFAULT '1st Sem',
  `school_year` varchar(20)  NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  -- one enrollment form per student per semester per school year
  UNIQUE KEY `uq_enroll_term` (`studentID`, `semester`, `school_year`),
  CONSTRAINT `fk_enroll_student` FOREIGN KEY (`studentID`)
    REFERENCES `studenttbl` (`studnum`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3;

-- The original test data had seven duplicate forms for one student; one sample form is kept.
INSERT INTO `student_subject` (`ID`, `studentID`, `subjects`, `totalunits`, `approved`, `semester`, `school_year`) VALUES
(2, '2014-052', 'IT201,IT202', 4, 0, '1st Sem', '2015 - 2016');

-- ------------------------------------------------------------
-- sections_tbl : class sections, tied to a course
-- ------------------------------------------------------------
CREATE TABLE `sections_tbl` (
  `section_ID`     int(11)      NOT NULL AUTO_INCREMENT,
  `section_name`   varchar(100) NOT NULL,
  `section_course` varchar(20)  NOT NULL,
  `yr_level`       varchar(100) NOT NULL,
  `max_num`        int(11)      NOT NULL,
  `min_num`        int(11)      NOT NULL,
  PRIMARY KEY (`section_ID`),
  KEY `idx_section_course` (`section_course`),
  CONSTRAINT `fk_section_course` FOREIGN KEY (`section_course`)
    REFERENCES `courses_tbl` (`course_code`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- ------------------------------------------------------------
-- sched_tbl : which subject is taught in which section.
-- Instructor_ID and room_ID have no FK because there is no
-- instructors or rooms table yet.
-- ------------------------------------------------------------
CREATE TABLE `sched_tbl` (
  `sched_ID`      int(11)      NOT NULL AUTO_INCREMENT,
  `section_ID`    int(11)      NOT NULL,
  `subject_ID`    int(11)      NOT NULL,
  `Instructor_ID` int(11)      NOT NULL,
  `time_from`     varchar(100) NOT NULL,
  `time_to`       varchar(100) NOT NULL,
  `room_ID`       int(11)      NOT NULL,
  PRIMARY KEY (`sched_ID`),
  KEY `idx_sched_section` (`section_ID`),
  KEY `idx_sched_subject` (`subject_ID`),
  CONSTRAINT `fk_sched_section` FOREIGN KEY (`section_ID`)
    REFERENCES `sections_tbl` (`section_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sched_subject` FOREIGN KEY (`subject_ID`)
    REFERENCES `subjecttbl` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- ------------------------------------------------------------
-- login_attempts : failed logins, used to slow down password guessing
-- (5 failures for one username from one address block logins for 15 minutes)
-- ------------------------------------------------------------
CREATE TABLE `login_attempts` (
  `ID`           int(11)      NOT NULL AUTO_INCREMENT,
  `username`     varchar(50)  NOT NULL,
  `ip`           varchar(45)  NOT NULL,
  `attempted_at` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `idx_login_lookup` (`username`, `ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portal roles and enrollment windows.
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

-- Demonstration dates for the sample campus. Registrars can replace these in Enrollment schedule.
INSERT INTO enrollment_window (school_year, semester, starts, adjustment, ends) VALUES
('2026 - 2027', '1st Sem', '2026-07-20', '2026-08-03', '2026-08-14'),
('2026 - 2027', '2nd Sem', '2026-11-16', '2026-11-30', '2026-12-11');

CREATE TABLE IF NOT EXISTS student_id_sequence (
    registration_year SMALLINT UNSIGNED PRIMARY KEY,
    last_issued SMALLINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

-- Retain the original subjects for historical reports.
UPDATE subjecttbl SET semester='Archived' WHERE ID IN (1,3,4);

-- Illustrative first-year programs and class meetings for Rizing University.
-- Record keys are stable. Re-imports preserve registrar edits.
START TRANSACTION;
INSERT INTO courses_tbl (course_code, course_title, course_desc) VALUES
('bsit', 'Bachelor of Science in Information Technology', ''),
('bscs', 'Bachelor of Science in Computer Science', ''),
('bsba', 'Bachelor of Science in Business Administration', ''),
('bsa', 'Bachelor of Science in Accountancy', ''),
('bsce', 'Bachelor of Science in Civil Engineering', ''),
('beed', 'Bachelor of Elementary Education', ''),
('bspsych', 'Bachelor of Science in Psychology', '')
ON DUPLICATE KEY UPDATE course_ID=course_ID;

INSERT INTO subjecttbl (subjectcode, subjecttitle, numofunits, room, time, instructor, day, islab, istutorial, description, course_con, semester) VALUES
('RU-BSIT-101', 'Introduction to Computing', '3', 'R-101', '08:00–11:00', '', 'Monday', '0', '0', '', 'bsit', '1st Sem'),
('RU-BSIT-102', 'Computer Programming 1', '3', 'R-101', '13:00–16:00', '', 'Monday', '1', '0', '', 'bsit', '1st Sem'),
('RU-BSIT-103', 'Computer Systems Fundamentals', '3', 'R-101', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bsit', '1st Sem'),
('RU-BSIT-104', 'Understanding the Self', '3', 'R-101', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsit', '1st Sem'),
('RU-BSIT-105', 'NSTP 1: Civic Welfare Training Service', '3', 'R-101', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsit', '1st Sem'),
('RU-BSIT-106', 'PATHFit 1: Movement Competency', '2', 'R-101', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsit', '1st Sem'),
('RU-BSIT-201', 'Computer Programming 2', '3', 'R-101', '08:00–11:00', '', 'Monday', '1', '0', '', 'bsit', '2nd Sem'),
('RU-BSIT-202', 'Networking Fundamentals', '3', 'R-101', '13:00–16:00', '', 'Monday', '0', '0', '', 'bsit', '2nd Sem'),
('RU-BSIT-203', 'Web Systems and Technologies', '3', 'R-101', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bsit', '2nd Sem'),
('RU-BSIT-204', 'Readings in Philippine History', '3', 'R-101', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsit', '2nd Sem'),
('RU-BSIT-205', 'NSTP 2: Civic Welfare Training Service', '3', 'R-101', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsit', '2nd Sem'),
('RU-BSIT-206', 'PATHFit 2: Exercise-Based Fitness', '2', 'R-101', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsit', '2nd Sem'),
('RU-BSCS-101', 'Foundations of Computing', '3', 'R-102', '08:00–11:00', '', 'Monday', '0', '0', '', 'bscs', '1st Sem'),
('RU-BSCS-102', 'Programming Fundamentals', '3', 'R-102', '13:00–16:00', '', 'Monday', '0', '0', '', 'bscs', '1st Sem'),
('RU-BSCS-103', 'Discrete Structures 1', '3', 'R-102', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bscs', '1st Sem'),
('RU-BSCS-104', 'Understanding the Self', '3', 'R-102', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bscs', '1st Sem'),
('RU-BSCS-105', 'NSTP 1: Civic Welfare Training Service', '3', 'R-102', '08:00–11:00', '', 'Friday', '0', '0', '', 'bscs', '1st Sem'),
('RU-BSCS-106', 'PATHFit 1: Movement Competency', '2', 'R-102', '13:00–16:00', '', 'Friday', '0', '0', '', 'bscs', '1st Sem'),
('RU-BSCS-201', 'Object-Oriented Programming', '3', 'R-102', '08:00–11:00', '', 'Monday', '0', '0', '', 'bscs', '2nd Sem'),
('RU-BSCS-202', 'Discrete Structures 2', '3', 'R-102', '13:00–16:00', '', 'Monday', '0', '0', '', 'bscs', '2nd Sem'),
('RU-BSCS-203', 'Data Structures', '3', 'R-102', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bscs', '2nd Sem'),
('RU-BSCS-204', 'Readings in Philippine History', '3', 'R-102', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bscs', '2nd Sem'),
('RU-BSCS-205', 'NSTP 2: Civic Welfare Training Service', '3', 'R-102', '08:00–11:00', '', 'Friday', '0', '0', '', 'bscs', '2nd Sem'),
('RU-BSCS-206', 'PATHFit 2: Exercise-Based Fitness', '2', 'R-102', '13:00–16:00', '', 'Friday', '0', '0', '', 'bscs', '2nd Sem'),
('RU-BSBA-101', 'Management and Organization', '3', 'R-103', '08:00–11:00', '', 'Monday', '0', '0', '', 'bsba', '1st Sem'),
('RU-BSBA-102', 'Principles of Marketing', '3', 'R-103', '13:00–16:00', '', 'Monday', '0', '0', '', 'bsba', '1st Sem'),
('RU-BSBA-103', 'Business Mathematics', '3', 'R-103', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bsba', '1st Sem'),
('RU-BSBA-104', 'Understanding the Self', '3', 'R-103', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsba', '1st Sem'),
('RU-BSBA-105', 'NSTP 1: Civic Welfare Training Service', '3', 'R-103', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsba', '1st Sem'),
('RU-BSBA-106', 'PATHFit 1: Movement Competency', '2', 'R-103', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsba', '1st Sem'),
('RU-BSBA-201', 'Financial Management', '3', 'R-103', '08:00–11:00', '', 'Monday', '0', '0', '', 'bsba', '2nd Sem'),
('RU-BSBA-202', 'Business Communication', '3', 'R-103', '13:00–16:00', '', 'Monday', '0', '0', '', 'bsba', '2nd Sem'),
('RU-BSBA-203', 'Microeconomics', '3', 'R-103', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bsba', '2nd Sem'),
('RU-BSBA-204', 'Readings in Philippine History', '3', 'R-103', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsba', '2nd Sem'),
('RU-BSBA-205', 'NSTP 2: Civic Welfare Training Service', '3', 'R-103', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsba', '2nd Sem'),
('RU-BSBA-206', 'PATHFit 2: Exercise-Based Fitness', '2', 'R-103', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsba', '2nd Sem'),
('RU-BSA-101', 'Fundamentals of Accounting', '3', 'R-104', '08:00–11:00', '', 'Monday', '0', '0', '', 'bsa', '1st Sem'),
('RU-BSA-102', 'Business Mathematics', '3', 'R-104', '13:00–16:00', '', 'Monday', '0', '0', '', 'bsa', '1st Sem'),
('RU-BSA-103', 'Economics for Accountants', '3', 'R-104', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bsa', '1st Sem'),
('RU-BSA-104', 'Understanding the Self', '3', 'R-104', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsa', '1st Sem'),
('RU-BSA-105', 'NSTP 1: Civic Welfare Training Service', '3', 'R-104', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsa', '1st Sem'),
('RU-BSA-106', 'PATHFit 1: Movement Competency', '2', 'R-104', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsa', '1st Sem'),
('RU-BSA-201', 'Financial Accounting and Reporting', '3', 'R-104', '08:00–11:00', '', 'Monday', '0', '0', '', 'bsa', '2nd Sem'),
('RU-BSA-202', 'Conceptual Framework and Accounting Standards', '3', 'R-104', '13:00–16:00', '', 'Monday', '0', '0', '', 'bsa', '2nd Sem'),
('RU-BSA-203', 'Business Law', '3', 'R-104', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bsa', '2nd Sem'),
('RU-BSA-204', 'Readings in Philippine History', '3', 'R-104', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsa', '2nd Sem'),
('RU-BSA-205', 'NSTP 2: Civic Welfare Training Service', '3', 'R-104', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsa', '2nd Sem'),
('RU-BSA-206', 'PATHFit 2: Exercise-Based Fitness', '2', 'R-104', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsa', '2nd Sem'),
('RU-BSCE-101', 'Calculus 1', '3', 'R-108', '08:00–11:00', '', 'Monday', '0', '0', '', 'bsce', '1st Sem'),
('RU-BSCE-102', 'Engineering Drawing', '3', 'R-108', '13:00–16:00', '', 'Monday', '0', '0', '', 'bsce', '1st Sem'),
('RU-BSCE-103', 'Chemistry for Engineers', '3', 'R-108', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bsce', '1st Sem'),
('RU-BSCE-104', 'Understanding the Self', '3', 'R-108', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsce', '1st Sem'),
('RU-BSCE-105', 'NSTP 1: Civic Welfare Training Service', '3', 'R-108', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsce', '1st Sem'),
('RU-BSCE-106', 'PATHFit 1: Movement Competency', '2', 'R-108', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsce', '1st Sem'),
('RU-BSCE-201', 'Calculus 2', '3', 'R-108', '08:00–11:00', '', 'Monday', '0', '0', '', 'bsce', '2nd Sem'),
('RU-BSCE-202', 'Physics for Engineers', '3', 'R-108', '13:00–16:00', '', 'Monday', '0', '0', '', 'bsce', '2nd Sem'),
('RU-BSCE-203', 'Computer-Aided Drafting', '3', 'R-108', '08:00–11:00', '', 'Wednesday', '1', '0', '', 'bsce', '2nd Sem'),
('RU-BSCE-204', 'Readings in Philippine History', '3', 'R-108', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bsce', '2nd Sem'),
('RU-BSCE-205', 'NSTP 2: Civic Welfare Training Service', '3', 'R-108', '08:00–11:00', '', 'Friday', '0', '0', '', 'bsce', '2nd Sem'),
('RU-BSCE-206', 'PATHFit 2: Exercise-Based Fitness', '2', 'R-108', '13:00–16:00', '', 'Friday', '0', '0', '', 'bsce', '2nd Sem'),
('RU-BEED-101', 'The Teaching Profession', '3', 'R-110', '08:00–11:00', '', 'Monday', '0', '0', '', 'beed', '1st Sem'),
('RU-BEED-102', 'Child and Adolescent Development', '3', 'R-110', '13:00–16:00', '', 'Monday', '0', '0', '', 'beed', '1st Sem'),
('RU-BEED-103', 'Foundations of Education', '3', 'R-110', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'beed', '1st Sem'),
('RU-BEED-104', 'Understanding the Self', '3', 'R-110', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'beed', '1st Sem'),
('RU-BEED-105', 'NSTP 1: Civic Welfare Training Service', '3', 'R-110', '08:00–11:00', '', 'Friday', '0', '0', '', 'beed', '1st Sem'),
('RU-BEED-106', 'PATHFit 1: Movement Competency', '2', 'R-110', '13:00–16:00', '', 'Friday', '0', '0', '', 'beed', '1st Sem'),
('RU-BEED-201', 'Facilitating Learner-Centered Teaching', '3', 'R-110', '08:00–11:00', '', 'Monday', '0', '0', '', 'beed', '2nd Sem'),
('RU-BEED-202', 'Assessment of Learning 1', '3', 'R-110', '13:00–16:00', '', 'Monday', '0', '0', '', 'beed', '2nd Sem'),
('RU-BEED-203', 'Teaching Mathematics in Primary Grades', '3', 'R-110', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'beed', '2nd Sem'),
('RU-BEED-204', 'Readings in Philippine History', '3', 'R-110', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'beed', '2nd Sem'),
('RU-BEED-205', 'NSTP 2: Civic Welfare Training Service', '3', 'R-110', '08:00–11:00', '', 'Friday', '0', '0', '', 'beed', '2nd Sem'),
('RU-BEED-206', 'PATHFit 2: Exercise-Based Fitness', '2', 'R-110', '13:00–16:00', '', 'Friday', '0', '0', '', 'beed', '2nd Sem'),
('RU-BSPSYCH-101', 'Introduction to Psychology', '3', 'R-111', '08:00–11:00', '', 'Monday', '0', '0', '', 'bspsych', '1st Sem'),
('RU-BSPSYCH-102', 'General Biology', '3', 'R-111', '13:00–16:00', '', 'Monday', '0', '0', '', 'bspsych', '1st Sem'),
('RU-BSPSYCH-103', 'Basic Statistics', '3', 'R-111', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bspsych', '1st Sem'),
('RU-BSPSYCH-104', 'Understanding the Self', '3', 'R-111', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bspsych', '1st Sem'),
('RU-BSPSYCH-105', 'NSTP 1: Civic Welfare Training Service', '3', 'R-111', '08:00–11:00', '', 'Friday', '0', '0', '', 'bspsych', '1st Sem'),
('RU-BSPSYCH-106', 'PATHFit 1: Movement Competency', '2', 'R-111', '13:00–16:00', '', 'Friday', '0', '0', '', 'bspsych', '1st Sem'),
('RU-BSPSYCH-201', 'Developmental Psychology', '3', 'R-111', '08:00–11:00', '', 'Monday', '0', '0', '', 'bspsych', '2nd Sem'),
('RU-BSPSYCH-202', 'Psychological Statistics', '3', 'R-111', '13:00–16:00', '', 'Monday', '0', '0', '', 'bspsych', '2nd Sem'),
('RU-BSPSYCH-203', 'Social Psychology', '3', 'R-111', '08:00–11:00', '', 'Wednesday', '0', '0', '', 'bspsych', '2nd Sem'),
('RU-BSPSYCH-204', 'Readings in Philippine History', '3', 'R-111', '13:00–16:00', '', 'Wednesday', '0', '0', '', 'bspsych', '2nd Sem'),
('RU-BSPSYCH-205', 'NSTP 2: Civic Welfare Training Service', '3', 'R-111', '08:00–11:00', '', 'Friday', '0', '0', '', 'bspsych', '2nd Sem'),
('RU-BSPSYCH-206', 'PATHFit 2: Exercise-Based Fitness', '2', 'R-111', '13:00–16:00', '', 'Friday', '0', '0', '', 'bspsych', '2nd Sem')
ON DUPLICATE KEY UPDATE ID=ID;
COMMIT;
