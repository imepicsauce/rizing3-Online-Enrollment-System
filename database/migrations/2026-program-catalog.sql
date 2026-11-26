-- Select the enrollment database before importing.
SET @ru_ddl = IF(EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='subjecttbl' AND column_name='semester'), 'SELECT 1', 'ALTER TABLE subjecttbl ADD semester VARCHAR(10) NOT NULL DEFAULT ''''');
PREPARE ru_stmt FROM @ru_ddl;
EXECUTE ru_stmt;
DEALLOCATE PREPARE ru_stmt;

-- Retire programs on existing installations while retaining their academic records.
SET @ru_ddl = IF(EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='courses_tbl' AND column_name='is_offered'), 'SELECT 1', 'ALTER TABLE courses_tbl ADD is_offered TINYINT NOT NULL DEFAULT 1');
PREPARE ru_stmt FROM @ru_ddl;
EXECUTE ru_stmt;
DEALLOCATE PREPARE ru_stmt;

-- Illustrative first-year programs and class meetings for Rizing University.
-- Record keys are stable. Re-imports preserve registrar edits.
START TRANSACTION;
UPDATE courses_tbl SET is_offered=0 WHERE course_code IN ('bsn','bscrim','bstm','bshm');
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
