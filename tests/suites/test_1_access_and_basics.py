"""Suite 1: access control, SQL injection, login/logout, add/delete users and subjects, change password, admin edit."""
from helpers import *
import json
php_problems = warns
alerts_raw = alerts

ADMIN_PAGES = ["Admin/adminindex.php", "Admin/userreg.php", "Admin/subjectreg.php", "Admin/addnewuser.php", "Admin/addnewsubj.php", "Admin/adminedit.php", "Admin/report.php"]
STUDENT_PAGES = ["student/studentindex.php", "student/studentprofile.php", "student/enroll.php", "student/changepass.php", "student/report.php", "student/curriculum.php"]

# ============================================================================
section("A. LOGGED-OUT VISITOR")
v = client(auto_token=True)
s, h, _ = v("index.php"); clean_page("login page loads without PHP warnings", s, h)
s, h, _ = login(v, "admins", "WRONG"); a = alerts(h)
import json
check("wrong password shows a well-formed alert saying: User doesn't exist please try again.", len(a) == 1 and a[0] == "User doesn't exist please try again.", str(a))
s, h, loc = login(v, "x' OR '1'='1", "x' OR '1'='1")
check("SQL-injection login is rejected", s == 200 and len(alerts(h)) == 1, str(alerts(h)))
s, h, loc = login(v, "admins' -- ", "")
check("SQL-injection (comment) login is rejected", s == 200)

before_users, before_subj = sql("select count(*) from usertbl"), sql("select count(*) from subjecttbl")
for pth in ADMIN_PAGES + STUDENT_PAGES + ["Admin/deletecurruser.php", "Admin/deletecurrsubj.php"]:
    s, h, loc = v(pth)
    check("logged-out GET %-30s -> redirect to login, no page body" % pth, s == 302 and loc and loc.endswith("index.php") and "<html" not in h and "Manage" not in h, "HTTP %s loc=%s len=%d" % (s, loc, len(h)))
s, h, loc = v("Admin/deletecurruser.php", {"accid": "2", "token": "x"}); s2, h2, loc2 = v("Admin/deletecurrsubj.php", {"accid": "1", "token": "x"})
check("logged-out POST to both delete pages is refused", s == 302 and s2 == 302)
check("...and nothing was deleted", sql("select count(*) from usertbl") == before_users and sql("select count(*) from subjecttbl") == before_subj)

# ============================================================================
print("\n=== B. STUDENT (dennis) ===")
st = client(auto_token=True)
s, h, loc = login(st, "dennis", "123456")
check("student login goes to student home", loc == "student/studentindex.php" and "Successfully log in as Student" in h)
for pth in STUDENT_PAGES:
    s, h, _ = st(pth); clean_page("student page %s" % pth, s, h)
s, h, _ = st("student/studentprofile.php")
check("profile page: <form> balanced", h.count("<form") == h.count("</form>"), "%d open / %d close" % (h.count("<form"), h.count("</form>")))
check("profile page: <div> balanced", h.count("<div") == h.count("</div>"), "%d open / %d close" % (h.count("<div"), h.count("</div>")))
check("profile page: <center> balanced", h.count("<center") == h.count("</center>"))
check("profile page has Print / Change Password / My Enrollment Report / Back buttons", all(x in h for x in ["Print Profile", "Change Password", "My Enrollment Report", "Back to Home"]))
s, h, _ = st("student/curriculum.php")
check("curriculum lists the BSIT subjects", all(x in h for x in ["IT201", "IT202", "CS 103"]) and "Total units" in h)
s, h, _ = st("student/includes/menu.php") if False else (0, "", None)
s, h, _ = st("student/studentindex.php")
check("student menu: Curriculum -> curriculum.php", 'href="curriculum.php">Curriculum' in h)

print("-- student tries every Admin page and both delete endpoints --")
for pth in ADMIN_PAGES:
    s, h, loc = st(pth)
    check("student GET %-30s -> redirected away" % pth, s == 302 and loc and loc.endswith("index.php") and "<html" not in h, "HTTP %s" % s)
st_tok = token(st("student/enroll.php")[1]) or "x"
st("Admin/deletecurruser.php", {"accid": "2", "token": st_tok}); st("Admin/deletecurrsubj.php", {"accid": "1", "token": st_tok})
check("student POST to delete endpoints deletes nothing", sql("select count(*) from usertbl") == before_users and sql("select count(*) from subjecttbl") == before_subj)

print("-- change password --")
s, h, _ = st("student/changepass.php", {"opassword": "nope", "password": "a", "password2": "a", "cpass": "x"})
check("wrong old password -> alert", any("wrong" in x for x in alerts(h)), str(alerts(h)))
s, h, _ = st("student/changepass.php", {"opassword": "123456", "password": "aaa", "password2": "bbb", "cpass": "x"})
check("mismatched new passwords -> visible alert (was silent)", any("do not match" in x for x in alerts(h)), str(alerts(h)))
check("mismatch did not change the password", can_login("dennis", "123456"))
s, h, loc = st("student/changepass.php", {"opassword": "123456", "password": "newpass1", "password2": "newpass1", "cpass": "x"})
check("correct change -> alert + clean redirect to profile (no stray ')')", s == 303 and loc == "studentprofile.php" and "updated" in al(h), loc)
check("password really changed (new one logs in, old one does not)", can_login("dennis", "newpass1") and not can_login("dennis", "123456"))
st("student/changepass.php", {"opassword": "newpass1", "password": "123456", "password2": "123456", "cpass": "x"})
check("password restored for later tests", can_login("dennis", "123456"))

print("-- logout --")
s, h, loc = st("includes/logout.php")
check("logout redirects to login", s == 302 and loc and loc.endswith("index.php"))
s, h, loc = st("student/studentindex.php")
check("after logout the student pages are locked again", s == 302)

# ============================================================================
print("\n=== C. ADMIN ===")
ad = client(auto_token=True)
s, h, loc = login(ad, "admins", "admins")
check("admin login goes to admin home", loc == "Admin/adminindex.php" and "Administrator" in h)
for pth in ADMIN_PAGES:
    s, h, _ = ad(pth); clean_page("admin page %s" % pth, s, h)
s, h, _ = ad("Admin/addnewuser.php")
check("addnewuser: no undefined-variable warnings on first load (C5)", not php_problems(h))
check("admin pages do not show the student Curriculum page", ad("student/curriculum.php")[0] == 302)

s, h, _ = ad("Admin/userreg.php")
hdr = re.findall(r"<th[^>]*>(.*?)</th>", h); cells = re.search(r"<tbody>\s*<tr>\s*<td>(.*?)</td>\s*<td>(.*?)</td>\s*<td>(.*?)</td>\s*<td>(.*?)</td>", h, re.S)
ok = bool(cells) and hdr[2] == "Year Level" and cells.group(3).strip() == "II-2" and cells.group(4).strip() == "bsit"
check("user list: Year Level and Course columns are in the right order", ok, "%s | %s" % (hdr, cells.groups() if cells else None))
check("user list: no stray ';' and no nested forms", ">;" not in h.split("<tbody>")[1] and h.count("<form") == h.count("</form>"))

print("-- add subject --")
sub = {"course": "bsit", "stitle": "Test Subject", "scode": "TS101", "cdescription": "d", "numunits": "3", "sroom": "R1", "stime": "8-9", "sday": "MWF", "sinstructor": "Mr X", "register": "x"}
s, h, _ = ad("Admin/addnewsubj.php"); check("add-subject course list comes from the database (bsit + bscs)", "value='bsit'" in h and "value='bscs'" in h)
s, h, _ = ad("Admin/addnewsubj.php", sub); check("add subject succeeds (was a fatal error)", "Successfully Created new Subject" in h and not php_problems(h), str(php_problems(h)[:1]))
check("...and the row is saved correctly", sql("select concat(subjecttitle,'|',numofunits,'|',course_con,'|',islab) from subjecttbl where subjectcode='TS101'") == "Test Subject|3|bsit|0")
s, h, _ = ad("Admin/addnewsubj.php", sub); check("duplicate subject code -> friendly alert, no fatal error", any("already exists" in x for x in alerts(h)) and not php_problems(h), str(alerts(h)))
bad = dict(sub, scode="TS102", numunits="99"); s, h, _ = ad("Admin/addnewsubj.php", bad); check("units out of range -> rejected", any("1 to 6" in x for x in alerts(h)))
bad = dict(sub, scode="TS103", course="nope"); s, h, _ = ad("Admin/addnewsubj.php", bad); check("unknown course -> rejected", any("valid course" in x for x in alerts(h)))
xss = dict(sub, scode="TS104", stitle="<script>alert(1)</script>"); ad("Admin/addnewsubj.php", xss)
s, h, _ = ad("Admin/subjectreg.php"); check("subject list escapes HTML in titles (no injected <script>)", "<script>alert(1)</script>" not in h and "&lt;script&gt;" in h)

print("-- delete subject --")
sid = sql("select ID from subjecttbl where subjectcode='TS101'"); tok = token(ad("Admin/subjectreg.php")[1])
check("subject list renders a POST form + token per row", 'action="deletecurrsubj.php"' in h and bool(tok))
ad("Admin/deletecurrsubj.php?accid=" + sid)
check("GET delete does nothing", sql("select count(*) from subjecttbl where subjectcode='TS101'") == "1")
ad("Admin/deletecurrsubj.php", {"accid": sid})
check("POST without token does nothing", sql("select count(*) from subjecttbl where subjectcode='TS101'") == "1")
ad("Admin/deletecurrsubj.php", {"accid": sid, "token": "0" * 64})
check("POST with a wrong token does nothing", sql("select count(*) from subjecttbl where subjectcode='TS101'") == "1")
s, h, _ = ad("Admin/deletecurrsubj.php", {"accid": sid, "token": tok})
check("POST with the right token deletes it (was a fatal error)", sql("select count(*) from subjecttbl where subjectcode='TS101'") == "0" and "Successfully Removed" in h and not php_problems(h))

print("-- add user --")
u = {"username": "tester1", "password": "pw1234", "snum": "2026-001", "course": "bsit", "yrlvlsec": "I-1", "fname": "Test", "minitial": "T", "lname": "Student",
     "gender": "male", "bdate": "2004-05-06", "address": "Roxas", "cnumber": "09123456789", "email": "t@example.com", "fathername": "F", "mothername": "M",
     "guardianname": "G", "bplace": "Roxas", "cstatus": "single", "elemschool": "E", "yrgradelem": "2016", "hschool": "H", "yrgradhs": "2020",
     "lastschool": "L", "yrlast": "2022", "address2": "Roxas", "register": "x"}
s, h, _ = ad("Admin/addnewuser.php", u); check("add user succeeds", "Successfully Created new account" in h and not php_problems(h), str(php_problems(h)[:1]))
check("account creation shows a clean success message", al(h) == "Successfully Created new account")
check("account + profile are linked correctly", sql("select count(*) from usertbl u join studenttbl s on s.accountID=u.ID where u.username='tester1' and s.studnum='2026-001' and s.lname='Student' and s.course='bsit' and s.birthdate='2004-05-06'") == "1")
nu, ns = sql("select count(*) from usertbl"), sql("select count(*) from studenttbl")
s, h, _ = ad("Admin/addnewuser.php", u); check("duplicate username -> friendly alert, no fatal error", any("username is already taken" in x for x in alerts(h)) and not php_problems(h), str(alerts(h)) + str(php_problems(h)[:1]))
check("...and no half-created account is left behind", sql("select count(*) from usertbl") == nu and sql("select count(*) from studenttbl") == ns)
u2 = dict(u, username="tester2"); s, h, _ = ad("Admin/addnewuser.php", u2)
check("duplicate student number -> friendly alert, no fatal error", any("student number is already registered" in x for x in alerts(h)) and not php_problems(h), str(alerts(h)))
check("...and the new login account was rolled back (no orphan 'tester2')", sql("select count(*) from usertbl where username='tester2'") == "0" and sql("select count(*) from usertbl") == nu)
u3 = dict(u, username="o'brien \"x\" <b>", snum="2026-777", fname="O'Brien"); s, h, _ = ad("Admin/addnewuser.php", u3)
check("names with quotes and HTML are stored safely", sql("select count(*) from studenttbl where studnum='2026-777' and fname=\"O'Brien\"") == "1" and not php_problems(h))

print("-- delete user --")
sql("insert into student_subject (studentID,subjects,totalunits,approved) values ('2026-001','IT201',2,0)")
sid = sql("select ID from studenttbl where studnum='2026-001'"); tok = token(ad("Admin/userreg.php")[1])
ad("Admin/deletecurruser.php?accid=" + sid); check("GET delete does nothing", sql("select count(*) from studenttbl where studnum='2026-001'") == "1")
ad("Admin/deletecurruser.php", {"accid": sid}); check("POST without token does nothing", sql("select count(*) from studenttbl where studnum='2026-001'") == "1")
s, h, _ = ad("Admin/deletecurruser.php", {"accid": sid, "token": tok})
check("POST with the right token deletes the account (was a fatal error)", sql("select count(*) from usertbl where username='tester1'") == "0" and "Successfully Removed" in h and not php_problems(h))
check("...its profile and enrollment forms are removed with it", sql("select count(*) from studenttbl where studnum='2026-001'") == "0" and sql("select count(*) from student_subject where studentID='2026-001'") == "0")
s, h, _ = ad("Admin/deletecurruser.php", {"accid": "999999", "token": tok}); check("deleting a non-existent account is handled", "no longer exists" in h and not php_problems(h))
admin_id = sql("select ID from usertbl where username='admins'")
ad("Admin/deletecurruser.php", {"accid": admin_id, "token": tok}); check("an admin account can never be deleted through this endpoint", sql("select count(*) from usertbl where username='admins'") == "1")

print("-- edit admin account --")
sql("insert into usertbl (username,password,fullname,user_type) values ('admin2','pw','Second Admin','admin')")
s, h, _ = ad("Admin/adminedit.php"); check("admin edit shows 'Save changes' and no Clear button", "Save changes" in h and "name='clear'" not in h)
s, h, _ = ad("Admin/adminedit.php", {"username": "admins", "password": "admins2", "register": "x"})
check("edit changes ONLY the logged-in admin (was: every admin)", can_login("admins", "admins2") and sql("select password from usertbl where username='admin2'") == "pw")
s, h, _ = ad("Admin/adminedit.php", {"username": "admin2", "password": "x", "register": "x"})
check("renaming to an existing username -> friendly alert, no fatal error", any("already taken" in x for x in alerts(h)) and not php_problems(h), str(alerts(h)))
ad("Admin/adminedit.php", {"username": "admins", "password": "admins", "register": "x"})
check("admin password restored", can_login("admins", "admins"))

print("-- admin logout --")
s, h, loc = ad("includes/logout.php"); check("admin logout redirects", s == 302)
s, h, loc = ad("Admin/userreg.php"); check("after logout admin pages are locked again", s == 302)

print("\n=== D. RE-LOGIN WORKS AFTER LOGOUT (session regeneration) ===")
a2 = client(auto_token=True); login(a2, "admins", "admins"); s, h, _ = a2("Admin/adminindex.php"); check("fresh admin session works", s == 200 and not php_problems(h))

finish()
