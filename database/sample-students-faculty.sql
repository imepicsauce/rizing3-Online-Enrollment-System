-- Optional fictional accounts for a demo installation. Import the catalog first.
-- Run as one script in the selected enrollment database (requires CREATE ROUTINE).
-- One transaction for the batch. Existing usernames and faculty assignments are preserved.
DROP PROCEDURE IF EXISTS ru_seed_demo_person_2026;
DELIMITER //
CREATE PROCEDURE ru_seed_demo_person_2026(IN prog VARCHAR(20), IN given_name VARCHAR(40),
 IN surname VARCHAR(40), IN prof_name VARCHAR(100), IN dob DATE, IN student_hash VARCHAR(255),
 IN faculty_hash VARCHAR(255))
BEGIN
 DECLARE yr INT DEFAULT YEAR(UTC_TIMESTAMP() + INTERVAL 8 HOUR);
 DECLARE seq INT;
 DECLARE sid VARCHAR(20);
 DECLARE uid INT;
 DECLARE email VARCHAR(100);
 DECLARE prof_login VARCHAR(50);
 DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
 SET email=CONCAT('ru-demo-',prog,'@example.invalid');
 SET prof_login=CONCAT('demo.prof.',prog);
 IF NOT EXISTS (SELECT 1 FROM studenttbl WHERE emailaddress=email) THEN
   SELECT last_issued INTO seq FROM student_id_sequence WHERE registration_year=yr FOR UPDATE;
   allocate_id: LOOP
     SET seq=seq+1;
     IF seq>9999 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Student ID range exhausted'; END IF;
     SET sid=CONCAT(RIGHT(yr,2),'-',LPAD(seq,4,'0'));
     IF NOT EXISTS (SELECT 1 FROM usertbl WHERE username=sid) AND
        NOT EXISTS (SELECT 1 FROM studenttbl WHERE studnum=sid) THEN LEAVE allocate_id; END IF;
   END LOOP;
   UPDATE student_id_sequence SET last_issued=seq WHERE registration_year=yr;
   INSERT INTO usertbl (username,password,fullname,user_type,must_change_password)
   VALUES (sid,student_hash,CONCAT(given_name,' ',surname),'student',1);
   SET uid=LAST_INSERT_ID();
   INSERT INTO studenttbl (accountID,studnum,fname,minitial,lname,address,contact,emailaddress,
    birthdate,course,yearlvl,gender,fathername,mothername,guardianname,birthplace,civilstatus,
    elemschool,dategradelem,hsschool,datehsgrad,lastgrad,datelastgrad,addresslastgrad)
   VALUES (uid,sid,given_name,'',surname,'','',email,dob,prog,'I-1','','','','','','',
    '','','','','','','');
 END IF;
 IF NOT EXISTS (SELECT 1 FROM usertbl WHERE username=prof_login) THEN
   INSERT INTO usertbl (username,password,fullname,user_type) VALUES (prof_login,faculty_hash,prof_name,'faculty');
   SET uid=LAST_INSERT_ID();
   INSERT INTO faculty_subject (subjectID,facultyID)
    SELECT s.ID,uid FROM subjecttbl s LEFT JOIN faculty_subject f ON s.ID=f.subjectID
    WHERE s.course_con=prog AND s.subjectcode LIKE CONCAT('RU-',UPPER(prog),'-%') AND f.subjectID IS NULL;
 END IF;
END//
DELIMITER ;
START TRANSACTION;
INSERT INTO student_id_sequence (registration_year,last_issued)
VALUES (YEAR(UTC_TIMESTAMP() + INTERVAL 8 HOUR),0) ON DUPLICATE KEY UPDATE last_issued=last_issued;
CALL ru_seed_demo_person_2026('bsit', 'Mia', 'Reyes', 'Pilar Garcia', '2007-01-15', '$2y$12$8v9vF.yNRJre40Df/O1OiOERTrgwHFNUs.t7AY6qI64q1/VeVcD6a', '$2y$12$M/4Xic4uIaOl4kJZJ7AUuuZCw5UNOMLVSb5r1M4NaVhsx6dXLF8JO');
CALL ru_seed_demo_person_2026('bscs', 'Nico', 'Santos', 'Arturo Ramos', '2007-02-15', '$2y$12$YPjXPvT61/kzZ7V/aQA4kulTMNyZgWyFUt5cPV5/VLdUCM6/QIYgi', '$2y$12$cRNPaXzhyXCh7nSAw0kHMes3PgoYGr.gfiPnqqtkRI2JPSdZH14Dy');
CALL ru_seed_demo_person_2026('bsba', 'Bea', 'Flores', 'Elena Lim', '2007-03-15', '$2y$12$tQ5ApSSt2gdSje9/7VC.ne0ildiPi9MQnDKKmNiGfN7/pfnUMVuzO', '$2y$12$X5OXBwL1HhIsCkFkg.AVxOfi/XHgBFg93BYyaCmlndvQI0/5ipcPO');
CALL ru_seed_demo_person_2026('bsa', 'Luis', 'Cruz', 'Ramon Mendoza', '2007-04-15', '$2y$12$X/aK.gthPfvLphSvlYi39OMCxuTY27Qku/AKYaxE/p6g1T852wela', '$2y$12$EeNYeW0V4osJEhVL1UrDhOv3cPkQaZDc1Rab/11dxSxbYf3MUY3Ii');
CALL ru_seed_demo_person_2026('bsce', 'Rina', 'Mendoza', 'Sonia Reyes', '2007-08-15', '$2y$12$q8zkNeq7bX8VWbGuZxlY3eeQmza7KaErU0WXDGg5iTpJ4Fk3tVsV.', '$2y$12$uRCyNTEbga6bPYNTedSz0eC.pp835VpE7Jak5ENEWh2dKHtGs39my');
CALL ru_seed_demo_person_2026('beed', 'Ella', 'Aquino', 'Cora Flores', '2007-10-15', '$2y$12$oAIdib7mJnJpLqUdHYyxH.2N8A19Z4q3Kghh1LWBMi7kAnj0U85Ba', '$2y$12$.ze5ZZQisZXyp5K8FD6pnu/sdoBxvjuBlyiq01/JR8OJc.ahB0NVe');
CALL ru_seed_demo_person_2026('bspsych', 'Tala', 'Bautista', 'Rafael Cruz', '2007-11-15', '$2y$12$mTNWmjNCAsMAnEHaqu3eC.74WMl3FFqO92KKNv1ecc8oie3s.GxcW', '$2y$12$j/02UMRfXLppmqJuziPe7uY0A78hBL00ouusj07crzE8IzINNHfQe');
COMMIT;
DROP PROCEDURE ru_seed_demo_person_2026;
-- Student usernames and initial passwords (changes required at first sign-in).
SELECT course, studnum AS username, birthdate AS initial_password
FROM studenttbl WHERE course IN (SELECT course_code FROM courses_tbl WHERE is_offered=1)
  AND emailaddress=CONCAT('ru-demo-',course,'@example.invalid') ORDER BY course;
-- Faculty usernames follow demo.prof.<program>. Initial password: RizingDemo-<UPPERCASE PROGRAM>!
