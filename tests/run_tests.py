#!/usr/bin/env python3
"""Runs the automated tests for the Online Enrollment System.

What it does (it never touches your real `online_enroll` database):
  1. creates a separate TEST database (default name: online_enroll_test) from database/online_enroll.sql
  2. starts PHP's built-in web server on a spare port, pointed at that test database
  3. runs every suite in tests/suites (resetting the test database before each one)
  4. stops the server and deletes the test database

Examples:
  python tests/run_tests.py
  python tests/run_tests.py --php C:\\xampp\\php\\php.exe --mysql C:\\xampp\\mysql\\bin\\mysql.exe
  python tests/run_tests.py --only 3          (run just suite 3)
  python tests/run_tests.py --browser         (also run the real-browser tests; needs Playwright)
"""
import argparse, glob, os, shutil, subprocess, sys, time, urllib.request

HERE = os.path.dirname(os.path.abspath(__file__))
REPO = os.path.abspath(os.path.join(HERE, ".."))

XAMPP_PHP = [r"C:\xampp\php\php.exe", "/opt/lampp/bin/php", "/Applications/XAMPP/xamppfiles/bin/php"]
XAMPP_MYSQL = [r"C:\xampp\mysql\bin\mysql.exe", "/opt/lampp/bin/mysql", "/Applications/XAMPP/xamppfiles/bin/mysql"]


def find(explicit, names, fallbacks):
    if explicit:
        return explicit if os.path.isfile(explicit) else shutil.which(explicit)
    for n in names:
        p = shutil.which(n)
        if p:
            return p
    for p in fallbacks:
        if os.path.isfile(p):
            return p
    return None


def mysql_run(args, sql_text=None, input_text=None):
    cmd = [args.mysql_bin, "-h", args.host, "-P", str(args.port), "-u", args.user, "--protocol=TCP"]
    if args.password:
        cmd.append("-p" + args.password)
    if sql_text is not None:
        cmd += ["-e", sql_text]
    return subprocess.run(cmd, input=input_text, capture_output=True, text=True, encoding="utf-8")


def reset_db(args, sql_script):
    r = mysql_run(args, input_text=sql_script)
    if r.returncode != 0:
        sys.exit("Could not create the test database:\n" + r.stderr)


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--php", help="path to php (default: found on PATH or in XAMPP)")
    ap.add_argument("--mysql", help="path to the mysql/mariadb client (default: found on PATH or in XAMPP)")
    ap.add_argument("--host", default="127.0.0.1")
    ap.add_argument("--port", type=int, default=3306, help="MySQL port (default 3306)")
    ap.add_argument("--user", default="root")
    ap.add_argument("--password", default="")
    ap.add_argument("--db", default="online_enroll_test", help="name of the throwaway test database; must end with _test")
    ap.add_argument("--http-port", type=int, default=8123, help="port for the temporary web server (default 8123)")
    ap.add_argument("--only", help="run only suites whose file name contains this text, e.g. 3")
    ap.add_argument("--browser", action="store_true", help="also run the real-browser tests (needs: pip install playwright; playwright install chromium)")
    ap.add_argument("--keep", action="store_true", help="do not delete the test database at the end")
    args = ap.parse_args()

    if not args.db.endswith("_test"):
        sys.exit("The test database name must end with _test (so a real database can never be wiped).")
    args.php_bin = find(args.php, ["php"], XAMPP_PHP)
    args.mysql_bin = find(args.mysql, ["mysql", "mariadb"], XAMPP_MYSQL)
    if not args.php_bin:
        sys.exit("Could not find php. Use --php PATH (XAMPP: C:\\xampp\\php\\php.exe).")
    if not args.mysql_bin:
        sys.exit("Could not find the mysql client. Use --mysql PATH (XAMPP: C:\\xampp\\mysql\\bin\\mysql.exe).")

    r = mysql_run(args, "SELECT 1")
    if r.returncode != 0:
        sys.exit("Cannot connect to MySQL at %s:%s as %s. Is MySQL running (XAMPP Control Panel)?\n%s" % (args.host, args.port, args.user, r.stderr))

    # The schema file creates/uses `online_enroll`; point it at the test database instead.
    with open(os.path.join(REPO, "database", "online_enroll.sql"), encoding="utf-8") as f:
        schema = f.read()
    assert "`online_enroll`" in schema
    schema = schema.replace("`online_enroll`", "`%s`" % args.db)

    # Most workflow suites exercise open enrollment irrespective of today's date.
    schema += """
REPLACE INTO enrollment_window (school_year, semester, starts, adjustment, ends)
VALUES ('2026 - 2027','1st Sem','2000-01-01','2099-01-01','2099-01-31'),
       ('2026 - 2027','2nd Sem','2000-01-01','2099-01-01','2099-01-31');
"""

    base = "http://127.0.0.1:%d/" % args.http_port
    env = dict(os.environ, E2E_BASE=base, ENROLL_MYSQL=args.mysql_bin, ENROLL_DB_HOST=args.host, ENROLL_DB_PORT=str(args.port),
               ENROLL_DB_USER=args.user, ENROLL_DB_PASS=args.password, ENROLL_DB_NAME=args.db, PYTHONIOENCODING="utf-8", PYTHONPATH=HERE)
    # The web server (and the tests) read these from the environment, so they only ever see the test database.
    server_env = dict(env)

    suites = sorted(glob.glob(os.path.join(HERE, "suites", "test_*.py")))
    if args.browser:
        suites += sorted(glob.glob(os.path.join(HERE, "browser", "test_*.py")))
    if args.only:
        suites = [s for s in suites if args.only in os.path.basename(s)]
    if not suites:
        sys.exit("No test files matched.")

    reset_db(args, schema)
    server = subprocess.Popen([args.php_bin, "-S", "127.0.0.1:%d" % args.http_port, "-t", REPO], env=server_env,
                              stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, cwd=REPO)
    results = []
    try:
        for _ in range(50):
            try:
                urllib.request.urlopen(base + "index.php", timeout=2)
                break
            except Exception:
                time.sleep(0.2)
        else:
            sys.exit("The test web server did not start on port %d (is the port in use? try --http-port 8200)." % args.http_port)

        for path in suites:
            name = os.path.basename(path)
            print("\n" + "=" * 70 + "\n" + name + "\n" + "=" * 70, flush=True)
            # Legacy workflow suites use the original BSIT offerings in both terms.
            fixture = "" if name == "test_program_catalog.py" else "UPDATE subjecttbl SET semester='' WHERE subjectcode IN ('IT201','IT202','CS 103');"
            reset_db(args, schema + fixture)
            p = subprocess.run([sys.executable, "-u", path], env=env, cwd=HERE)
            results.append((name, p.returncode == 0))
    finally:
        server.terminate()
        try:
            server.wait(timeout=5)
        except Exception:
            server.kill()
        if not args.keep:
            mysql_run(args, "DROP DATABASE IF EXISTS `%s`" % args.db)

    print("\n" + "=" * 70)
    for name, ok in results:
        print("%-45s %s" % (name, "PASSED" if ok else "FAILED"))
    allok = all(ok for _, ok in results)
    print("\n" + ("ALL TEST FILES PASSED" if allok else "SOME TESTS FAILED - scroll up for the [FAIL] lines"))
    sys.exit(0 if allok else 1)


if __name__ == "__main__":
    main()
