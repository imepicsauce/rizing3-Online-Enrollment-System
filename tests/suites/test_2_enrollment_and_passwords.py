"""Suite 2: password hashing, admin edit, enrollment form rules, approvals, output escaping."""
from helpers import *
LOGIN = login

# ---- passwords
section("password hashing")
check("seed admin password is plaintext before first login", sql("select password from usertbl where username='admins'")=="admins")
a=client(); s,h,_=LOGIN(a,"admins","admins")
check("legacy plaintext login still works", "Administrator" in h, h[:200])
pw=sql("select password from usertbl where username='admins'")
check("...and is re-saved as a bcrypt hash", pw.startswith("$2y$"), pw)
a2=client(); s,h,_=LOGIN(a2,"admins","admins"); check("hashed admin can log in again", "Administrator" in h)
s,h,_=LOGIN(client(),"admins",pw); check("the hash itself is NOT accepted as a password", "doesn't exist" in "".join(al(h)))
s,h,_=LOGIN(client(),"admins","wrong"); check("wrong password rejected", "doesn't exist" in "".join(al(h)))

# ---- admin edit
section("admin edit")
s,h,_=a("Admin/adminedit.php"); check("password field is not pre-filled with the stored hash", pw not in h and "admins</" not in h and 'type=\'password\'' in h)
s,h,_=a("Admin/adminedit.php",{"username":"admins","password":"","register":"Save changes"})
check("blank password keeps current password", sql("select password from usertbl where username='admins'")==pw and "Successfully updated" in "".join(al(h)), al(h))
s,h,_=a("Admin/adminedit.php",{"username":"admins","password":"newadmin1","register":"Save changes"})
check("new password saved as hash", sql("select password from usertbl where username='admins'").startswith("$2y$") and sql("select password from usertbl where username='admins'")!=pw)
s,h,_=LOGIN(client(),"admins","newadmin1"); check("...and works for login", "Administrator" in h)
a=client(); LOGIN(a,"admins","newadmin1")

# ---- add user hashed
section("add student")
nu={"username":"tester2","password":"pw2222","snum":"2026-002","course":"bsit","yrlvlsec":"I-1","fname":"T","minitial":"T","lname":"Two","gender":"male","bdate":"2004-05-06","address":"x","cnumber":"09123456789","email":"t@e.com","fathername":"F","mothername":"M","guardianname":"G","bplace":"x","cstatus":"single","elemschool":"E","yrgradelem":"2016","hschool":"H","yrgradhs":"2020","lastschool":"L","yrlast":"2022","address2":"x","register":"Add new user"}
nu["token"]=token(a("Admin/addnewuser.php")[1]); s,h,_=a("Admin/addnewuser.php",nu); check("add user works, no PHP warnings", "Successfully" in "".join(al(h)) and not warns(h), (al(h),warns(h)))
check("new student's password stored hashed", sql("select password from usertbl where username='tester2'").startswith("$2y$"))
t=client(); s,h,_=LOGIN(t,"tester2","pw2222"); check("new student can log in", "Student" in h)

# ---- change password
section("change password")
s,h,_=t("student/changepass.php",{"opassword":"pw2222","password":"n3w","password2":"n3w","cpass":"x"}); check("change with hashed old password works", "Successfully" in "".join(al(h)), al(h))
check("new stored hash", sql("select password from usertbl where username='tester2'").startswith("$2y$"))
s,h,_=t("student/changepass.php",{"opassword":"pw2222","password":"z","password2":"z","cpass":"x"}); check("old password no longer accepted", "wrong" in "".join(al(h)))
s,h,_=LOGIN(client(),"tester2","n3w"); check("login with changed password", "Student" in h)

# ---- enrollment
section("enrollment")
d=client(); LOGIN(d,"dennis","123456")
sql("delete from student_subject")
sql("insert into subjecttbl (subjectcode,subjecttitle,numofunits,room,time,instructor,day,islab,istutorial,description,course_con) values ('XX1','Other Course','3','','','','',0,0,'','bscs')") if sql("select count(*) from courses_tbl where course_code='bscs'")=="1" else None
s,h,_=d("student/enroll.php"); tok=token(h)
check("enroll page ok, no warnings", s==200 and not warns(h), warns(h))
check("lists only the student's course subjects", "IT201" in h and "XX1" not in h)
check("form carries a CSRF token", bool(tok))
s,h,_=d("student/showsubs.php"); check("showsubs via GET redirects to enroll", s==302 and "enroll.php" in (_ or ""), (s,_))
s,h,_=d("student/showsubs.php",{"sem":"1st Sem","register":"x","token":tok}); check("no subject ticked: message + back to enroll, no warnings", "at least one" in "".join(al(h)) and not warns(h), (al(h),warns(h)))
s,h,_=d("student/showsubs.php",{"subject[]":["IT201"],"sem":"1st Sem","register":"x"}); check("missing token rejected", "expired" in "".join(al(h)))
s,h,_=d("student/showsubs.php",{"subject[]":["IT201"],"sem":"BAD","register":"x","token":tok}); check("invalid semester rejected", "semester" in "".join(al(h)))
s,h,_=d("student/showsubs.php",{"subject[]":["XX1"],"sem":"1st Sem","register":"x","token":tok}); check("subject from another course rejected", "not offered" in "".join(al(h)), al(h))
s,h,_=d("student/showsubs.php",{"subject[]":["NOPE"],"sem":"1st Sem","register":"x","token":tok}); check("unknown subject rejected", "not offered" in "".join(al(h)))
s,h,_=d("student/showsubs.php",{"subject[]":["IT201","IT202","CS 103"],"sem":"2nd Sem","register":"x","token":tok})
check("preview renders without warnings", s==200 and not warns(h), warns(h))
units=2+2+3; total=units*296+2050+150+50+1500
check("preview total is computed on the server (7 units, 1 lab)", "7" in h and ("%s.00"%format(total,",")) in h, re.findall(r"P&nbsp;&nbsp;([\d,\.]+)",h))
check("preview shows semester and configured school year", "2nd Sem" in h and "2026 - 2027" in h)
check("preview has no units/contents hidden fields to tamper with", "name='units'" not in h and "name='contents'" not in h)
check("exactly one Print button", h.count("window.print()")==1, h.count("window.print()"))
s,h,_=d("student/showsubs.php",{"subject[]":["IT201","IT202"],"sem":"1st Sem","reg":"x","token":tok,"units":"99","contents":"HACK"})
check("submit stores server-computed units (tampered units=99 ignored)", sql("select subjects,totalunits,approved from student_subject")=="IT201,IT202\t4\t0", sql("select * from student_subject"))
check("submit success message", "successfully" in "".join(al(h)))
s,h,_=d("student/showsubs.php",{"subject[]":["IT202","IT201"],"sem":"1st Sem","reg":"x","token":tok}); check("duplicate form (same subjects, any order) is blocked", sql("select count(*) from student_subject")=="1" and "already" in "".join(al(h)), al(h))
s,h,_=d("student/report.php"); check("student report shows the form as Pending", "Pending" in h and "IT201" in h and not warns(h), warns(h))

# ---- approvals
section("approvals")
s,h,loc=client()("Admin/approvals.php"); check("logged-out -> redirect", s==302)
s,h,loc=d("Admin/approvals.php"); check("student cannot open approvals", s==302)
s,h,_=a("Admin/approvals.php"); check("admin sees the pending form with Approve", s==200 and "Pending" in h and "Approve" in h and "2014-052" in h and not warns(h), warns(h))
fid=sql("select ID from student_subject"); tk=token(h)
s,h,_=a("Admin/approvals.php",{"form_id":fid,"action":"approve"}); check("approve without token rejected", sql("select approved from student_subject")=="0")
s,h,_=a("Admin/approvals.php",{"form_id":fid,"action":"approve","token":tk}); check("approve with token works", sql("select approved from student_subject")=="1")
s,h,_=d("student/report.php"); check("student now sees Approved", "Approved" in h)
s,h,_=a("Admin/report.php"); check("admin report shows Approved", "Approved" in h and not warns(h))
s,h,_=a("Admin/approvals.php"); s2,h2,_=a("Admin/approvals.php",{"form_id":fid,"action":"pending","token":token(h)}); check("can be set back to pending", sql("select approved from student_subject")=="0")
s,h,_=a("Admin/adminindex.php"); check("admin menu links to Approvals; home has no dead # links", "approvals.php" in h and 'href="#"' not in h)
s,h,_=d("student/studentindex.php"); check("student home has no dead # links", 'href="#"' not in h)

# ---- escaping
section("escaping")
sql("update studenttbl set fname='<script>alert(1)</script>' where studnum='2014-052'")
s,h,_=d("student/studentprofile.php"); check("profile escapes stored HTML", "<script>alert(1)</script>" not in h and "&lt;script&gt;" in h)
s,h,_=d("student/enroll.php"); check("enroll page escapes name too / no warnings", "<script>alert(1)</script>" not in h and not warns(h))
sql("update studenttbl set fname='Karl Macniel' where studnum='2014-052'")
finish()
