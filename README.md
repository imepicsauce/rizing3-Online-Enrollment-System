# Online Enrollment System (John Paul College)

PHP + MySQL (mysqli) enrollment system with an Admin area and a student area.

## Setup (XAMPP)
1. Copy this folder into `htdocs`.
2. Start Apache and MySQL, then open phpMyAdmin and use **Import** on the server's start page (not inside a database).
3. Choose `database/online_enroll.sql`. It creates the `online_enroll` database and all tables.
   Importing it again drops and recreates the tables, so it also resets the data to the sample set.
4. Open `http://localhost/<folder-name>/` and log in with one of the sample accounts in the SQL file.

Database settings live in one place: `config/db.php`.

## Folder structure
```
index.php            login page
config/db.php        the one database connection
includes/            code shared by Admin and student (auth.php: login/role/CSRF helpers, logout.php)
assets/css/          style.css (the one stylesheet) + vendor/ (DataTables, jQuery UI)
assets/js/           jQuery, DataTables, easing
assets/img/          all images
Admin/               admin pages; Admin/includes/ holds the admin menus
student/             student pages; student/includes/ holds the student menus
database/            online_enroll.sql (InnoDB tables with foreign keys)
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
