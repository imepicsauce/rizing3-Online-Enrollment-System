# Online Enrollment System (John Paul College)

PHP + MySQL (mysqli) enrollment system with an Admin area and a student area.

## Setup (XAMPP)
1. Copy this folder into `htdocs`.
2. Start Apache and MySQL, open phpMyAdmin and create an empty database named `online_enroll`.
3. Import `database/online_enroll.sql` into it.
4. Open `http://localhost/<folder-name>/` and log in with one of the sample accounts in the SQL file.

Database settings live in one place: `config/db.php`.

## Folder structure
```
index.php            login page
config/db.php        the one database connection
includes/            code shared by Admin and student (logcheck.php, logout.php)
assets/css/          style.css (the one stylesheet) + vendor/ (DataTables, jQuery UI)
assets/js/           jQuery, DataTables, easing
assets/img/          all images
Admin/               admin pages; Admin/includes/ holds the admin menus
student/             student pages; student/includes/ holds the student menus
database/            online_enroll.sql
```

## Original notes
```
event driven programming:
jpcollege, most pages working na

Missing:
- File Structure
- CSS
- Iba pang pages (pa-double check)
- ALL content

Done:
- Report Generator + Student Curriculum/Report
- All pages working (DOUBLE CHECK)
```
