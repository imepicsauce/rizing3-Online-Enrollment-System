-- ============================================================
--  online_enroll  (revised schema with working relationships)
--
--  WARNING: this file DROPS and recreates every table, so
--  importing it replaces all existing data in `online_enroll`.
--
--  Column names and column ORDER are unchanged for usertbl,
--  studenttbl, subjecttbl and student_subject, because the PHP
--  pages use INSERT ... VALUES (...) without column names.
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
(2, 6, '2014-052', 'Karl Macniel', 'E', 'Flores', 'asdadasdasd', '09312312313', 'karloemilflore', '2015-01-14', 'bsit', 'II-2', 'male', 'Lopez Alcadia', 'Mauricia Magalpok', 'Arutro Manansala', 'Manila', 'single', 'Lacs', '1990', 'Mals', '1990', 'Navs', '1990', 'Navs');

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
  `ID`         int(11)      NOT NULL AUTO_INCREMENT,
  `studentID`  varchar(20)  NOT NULL,
  `subjects`   longtext     NOT NULL,
  `totalunits` int(11)      NOT NULL,
  `approved`   tinyint(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `idx_enroll_student` (`studentID`),
  CONSTRAINT `fk_enroll_student` FOREIGN KEY (`studentID`)
    REFERENCES `studenttbl` (`studnum`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=9;

-- Original row 1 had an empty studentID (an orphan), so it is not included.
INSERT INTO `student_subject` (`ID`, `studentID`, `subjects`, `totalunits`, `approved`) VALUES
(2, '2014-052', 'IT201,IT201,', 2, 0),
(3, '2014-052', 'IT202,IT201,', 2, 0),
(4, '2014-052', 'IT202,IT201,', 2, 0),
(5, '2014-052', 'IT202,IT201,', 2, 0),
(6, '2014-052', 'IT202,IT201,', 0, 0),
(7, '2014-052', 'IT202,IT201,', 2, 0),
(8, '2014-052', ',IT201,IT202', 2, 0);

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
