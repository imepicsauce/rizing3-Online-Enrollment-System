"""Registrar, faculty and student paths through subject assignments and enrollment windows."""
from datetime import datetime, timedelta, timezone
from helpers import al, check, client, finish, login, sql, token

admin = client()
login(admin, "admins", "admins")
student = client()
check("returning student can sign in with the existing student ID and password",
      login(student, "2014-052", "123456")[2] == "student/studentindex.php")

faculty_page = "Admin/faculty.php"
csrf = token(admin(faculty_page)[1])
for login_name in ["faculty_one", "faculty_two"]:
    _, html, _ = admin(faculty_page, {"token": csrf, "name": login_name, "login": login_name,
                                    "password": "faculty-password", "create": "1"})
    check("registrar creates " + login_name, "Faculty account created" in al(html))

faculty = client()
_, html, destination = login(faculty, "faculty_one", "faculty-password")
check("faculty land on an empty teaching schedule", destination == "faculty/index.php" and "has not assigned" in html)
for page in [faculty_page, "Admin/calendar.php", "Admin/approvals.php", "Admin/userreg.php", "student/enroll.php"]:
    check("faculty cannot access " + page, faculty(page)[0] == 302)
check("student cannot access faculty schedule", student("faculty/index.php")[0] == 302)

subject = sql("SELECT ID FROM subjecttbl WHERE subjectcode='IT201'")
for name in ["faculty_one", "faculty_two"]:
    account = sql(f"SELECT ID FROM usertbl WHERE username='{name}'")
    _, html, _ = admin(faculty_page, {"token": csrf, "subject": subject, "faculty": account})
    check("assignment saved for " + name, "Assignment updated" in al(html))
    html = faculty("faculty/index.php")[1]
    check("faculty schedule reflects current assignment to " + name, ("IT201" in html) == (name == "faculty_one"))
    check("other subjects stay private", "IT202" not in html)
admin(faculty_page, {"token": csrf, "subject": subject, "faculty": "0"})
check("registrar can remove an assignment", sql("SELECT COUNT(*) FROM faculty_subject") == "0")

today = datetime.now(timezone(timedelta(hours=8))).date()
def offset(days):
    return (today + timedelta(days=days)).isoformat()

calendar = "Admin/calendar.php"
calendar_token = token(admin(calendar)[1])
def publish(start, adjustment, end):
    return admin(calendar, {"semester":"1st Sem", "starts":offset(start), "adjustment":offset(adjustment),
                            "ends":offset(end), "token":calendar_token})[1]

sql("DELETE FROM student_subject")
sql("DELETE FROM enrollment_window")
check("unpublished semester shows a waiting state", "Awaiting schedule" in student("student/enroll.php")[1])
request = {"sem":"1st Sem", "subject[]":["IT201"], "token":token(student("student/enroll.php")[1])}
review = "student/showsubs.php"
check("unpublished semester blocks submission", "closed" in al(student(review, dict(request, reg="1"))[1]))
check("invalid date order is rejected", "valid dates" in al(publish(3, 2, 1)))
for start, adjustment, end, phase in [(1, 3, 4, "Upcoming"), (0, 1, 2, "Registration"), (-2, 0, 0, "Adjustment"), (-4, -3, -1, "Closed")]:
    check("registrar publishes " + phase, "published" in al(publish(start, adjustment, end)))
    check("calendar shows " + phase, f"<td>{phase}</td>" in student("student/enroll.php")[1])
    html = student(review, dict(request, register="1"))[1]
    check("review availability during " + phase, ("Fee breakdown" in html) == (phase in ["Registration", "Adjustment"]))
check("submission from an earlier review is refused after closing",
      "closed" in al(student(review, dict(request, reg="1"))[1]) and sql("SELECT COUNT(*) FROM student_subject") == "0")
publish(-2, -1, 0)
student(review, dict(request, reg="1"))
check("closing day accepts submissions", sql("SELECT COUNT(*) FROM student_subject") == "1")
record = sql("SELECT ID FROM student_subject")
publish(-5, -3, -1)
report = "student/report.php"
student(report, {"token":token(student(report)[1]), "cancel_form":record})
check("closed period preserves a pending form", sql("SELECT COUNT(*) FROM student_subject") == "1" and "cancel_form" not in student(report)[1])
finish()
