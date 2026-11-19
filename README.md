# Rizing University enrollment portal

PHP and MySQL enrollment portal for students, faculty, and the registrar.

## Setup (XAMPP)
1. Copy this folder into `htdocs`.
2. Start Apache and MySQL, then open phpMyAdmin and use **Import** on the server's start page (not inside a database).
3. Choose `database/online_enroll.sql`. It creates the `online_enroll` database and all tables.
   Importing it again drops and recreates the tables, so it also resets the data to the sample set.
4. Open `http://localhost/<folder-name>/` and log in with one of the sample accounts in the SQL file.

Database settings live in one place: `config/db.php`.

## Upgrading an existing installation

Registration and sign-in check for the password-change column and the faculty and enrollment calendar tables. If any are missing, the application applies `database/migrations/2026-enrollment-access.sql` when its database user has schema permissions. On restricted installations, select the existing enrollment database in phpMyAdmin and import that migration with a database administrator account. The migration can be rerun after a partial upgrade. In the registrar workspace, open **Enrollment schedule** and publish the registration, adjustment, and closing dates for each semester. Existing installations start with unpublished dates. A fresh sample installation includes illustrative dates for 2026–27.

The first registration creates the student ID counter if it is missing and the application database user has permission to create tables. If your installation uses a restricted database user, import `database/migrations/2026-student-id-format.sql` into the existing enrollment database using phpMyAdmin or an account with schema permissions. Existing student IDs remain valid.

If a registration displays a `REG-` reference, check the PHP error log for the matching MySQL number and source line. For `REG-1142`, import both migrations listed above with a database administrator account. For `REG-1054` on an older installation, import the enrollment access migration to add `usertbl.must_change_password`. The full sample schema resets existing records and is for fresh installations only.

## Accounts and enrollment

- **Students** can create an account from the homepage. The system assigns a student ID such as `26-0001`. The first two digits are the registration year in Manila. The four-digit sequence starts at 0001 each year. Sign in with that ID and the birthdate entered during registration, in `YYYY-MM-DD` format. The first sign-in opens the password change page. Returning students can use their existing ID and current password.
- **Faculty** sign in with accounts issued under **Faculty access**. They can view the subject, program, units, room and meeting time for each assigned class.
- **Registrars** publish the dates in Manila time. Registration and adjustment both allow submissions through the last day inclusive. During these windows students can replace a pending form. Changes to approved forms go through the registrar.
- The sample calendar adapts the phased registration and adjustment approach used by [UC Berkeley](https://registrar.berkeley.edu/registration/enrollment/). Rizing sets its own deadlines.

## Folder structure
```
index.php            university homepage
portal.php           portal sign-in
register.php         student registration
faculty/             faculty teaching schedule
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

## Notes on passwords and settings

- Passwords are stored hashed. The sample accounts in `database/online_enroll.sql` still have plain-text passwords; each is converted to a hash automatically the first time that user logs in.
- School year, per-unit fee, entrance/internet/misc/lab/tutorial fees and the semester names are in `config/settings.php`.
- Admins approve student enrollment forms from the **Approvals** page. A student can have one form per semester per school year; a pending form can be cancelled by the student, an approved one is locked.
- Admins manage courses from the **Courses** page, and can edit any student (including resetting the password) or subject from the lists.
- Database login: defaults suit XAMPP (`root`, no password). For other settings copy `config/db.local.example.php` to `config/db.local.php` (git-ignored) and edit it.
- Login is limited to 5 wrong passwords per username and address, then blocked for 15 minutes (table `login_attempts`).

## Tests

Automated tests live in `tests/`. Run `python tests/run_tests.py` after every change; see `tests/README.md` for setup (XAMPP) and how to read the results. They use a separate throwaway database, never your real one.
