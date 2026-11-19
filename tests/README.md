# Automated tests

These tests act like a user: they log in as the admin and as a student, add, edit and delete records, try forbidden
things (a student opening admin pages, SQL injection, wrong tokens, a 6th wrong password...), and check that the system
answers correctly. **Run them after every change** so you know right away if something that worked has broken.

They never touch your real data. The runner creates a throwaway database called `online_enroll_test`, starts a temporary
web server on port 8123, runs everything against that, then deletes the test database and stops the server.

## What you need

| Need | Notes |
|---|---|
| **Python 3.8+** | Download from python.org. On Windows tick "Add python.exe to PATH" in the installer. Nothing else to install for the main tests. |
| **XAMPP with MySQL running** | Start MySQL in the XAMPP Control Panel. Apache does not need to be running. |
| PHP and the `mysql` command | Already part of XAMPP (`C:\xampp\php\php.exe`, `C:\xampp\mysql\bin\mysql.exe`). The runner finds them by itself. |

## Run the tests

Open a terminal (Windows: Command Prompt or PowerShell) **in the project folder** (the one that contains `index.php`) and run:

```
python tests/run_tests.py
```

On Mac/Linux use `python3 tests/run_tests.py`. It takes about 15 seconds.

If Windows asks whether to allow `php.exe` through the firewall (first run only), allow it. It is only talking to your own computer.

### Reading the result

Every check prints one line:

```
[PASS] logged-out GET Admin/userreg.php -> redirect to login, no page body
[FAIL] approved form cannot be cancelled by the student   -- <details that help find the cause>
```

Each file ends with `SUMMARY: 95/95 checks passed`, and the very last line says `ALL TEST FILES PASSED` when everything is fine.
Any `[FAIL]` line names the behaviour that broke. Scroll up to find it, or pipe the output through a filter, e.g. on
Windows PowerShell: `python tests\run_tests.py | Select-String FAIL`. To see only the failing lines on Mac/Linux:
`python3 tests/run_tests.py | grep FAIL`.

### Useful options

| Option | Use |
|---|---|
| `--only 3` | run only the suite whose file name contains `3` (faster while you work on one area) |
| `--browser` | also run the real-browser tests (see below) |
| `--password SECRET` | if your MySQL `root` user has a password (XAMPP's default is none) |
| `--port 3307` | if MySQL is not on the default port 3306 |
| `--http-port 8200` | if port 8123 is already used by something else |
| `--php PATH` / `--mysql PATH` | if the runner cannot find them, e.g. `--php D:\xampp\php\php.exe --mysql D:\xampp\mysql\bin\mysql.exe` |
| `--keep` | keep the `online_enroll_test` database afterwards so you can look at it in phpMyAdmin |

## What each file checks

| File | Covers |
|---|---|
| `suites/test_1_access_and_basics.py` | Login and logout, SQL injection attempts, logged-out and student access to admin pages, add/delete users and subjects (including the CSRF token and the duplicate/rollback cases), change password, admin account edit |
| `suites/test_2_enrollment_and_passwords.py` | Password hashing (and the automatic upgrade of old plain-text passwords), enrollment form checks (course filter, tampered units, bad semester), server-side totals, approvals page, HTML escaping |
| `suites/test_3_edit_pages_and_courses.py` | Edit student/subject, admin password reset, semester saved, one form per semester, cancel rules, subjects used in forms, the Courses page |
| `suites/test_4_hardening_and_validation.py` | Session cookie flags, the 5-wrong-passwords limit, student field validation (email, phone, birth date...), database settings file, home pages |
| `browser/test_b1_clicks.py`, `browser/test_b2_pages.py` | The same flows with a real Chrome-like browser, clicking buttons, checking inline feedback and confirming destructive actions in the affected row. Also catches JavaScript errors and saves screenshots to `tests/screenshots/` |

The browser tests are optional. To enable them once:

```
pip install playwright
playwright install chromium
python tests/run_tests.py --browser
```

## If something goes wrong

| Message | Fix |
|---|---|
| `Cannot connect to MySQL ... Is MySQL running` | Start MySQL in the XAMPP Control Panel. If you set a root password use `--password`. |
| `Could not find php` / `Could not find the mysql client` | Pass `--php` / `--mysql` with the full path. |
| `The test web server did not start on port 8123` | Something else uses that port: add `--http-port 8200`. |
| `Could not create the test database` | Your MySQL user may lack permission to create databases. XAMPP's `root` has it. |
| Lots of `[FAIL]` right after you changed code | That is the tests doing their job. Read the first `[FAIL]` line: it names the page or behaviour that broke. |

## Adding your own test

Open any file in `tests/suites/` and copy a pattern. The helpers (in `tests/helpers.py`) are:

```python
a = client(); login(a, "admins", "admins")        # a browser-like session, logged in as admin
status, html, location = a("Admin/courses.php")   # open a page   (add a dict to POST: a(path, {"field": "value"}))
token(html)                                       # the hidden security token on a page
al(html)                                          # the inline feedback messages on a page
warns(html)                                       # PHP warnings/errors that leaked into the page
sql("select count(*) from usertbl")               # look at the TEST database
check("what should be true", condition)           # prints PASS/FAIL
```

Files named `test_*.py` in `tests/suites/` run automatically, in name order, each on a freshly reset test database.

## Safety

* The database name must end in `_test`, otherwise the runner and the tests refuse to start.
* The temporary web server gets the test database settings through environment variables (`ENROLL_DB_*`, read in
  `config/db.php`). They override `config/db.local.php`, so even if you have your own settings file the tests cannot reach your real data.
* Leave those variables unset when you use the system normally.
