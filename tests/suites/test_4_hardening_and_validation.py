"""Suite 4: session cookies, login attempt limit, student field validation, database settings file, home pages."""
from helpers import *
import datetime
LOGIN = login

section("session cookie")
s,h,hd=client()("index.php"); sc=hd.get_all("Set-Cookie") or []
check("session cookie is HttpOnly + SameSite=Lax", sc and all("httponly" in x.lower() and "samesite=lax" in x.lower() for x in sc if "PHPSESSID" in x), sc)
c=client(); s,h,hd=LOGIN(c,"admins","admins"); sc2=" ".join(hd.get_all("Set-Cookie") or [])
check("login (new session id) keeps the flags", "httponly" in sc2.lower() and "samesite=lax" in sc2.lower(), sc2)
s,h,hd=c("includes/logout.php"); check("logout still works and clears the session", s==302 and c("Admin/userreg.php")[0]==302)

section("login throttle")
sql("delete from login_attempts")
v=client()
for i in range(5): s,h,_=LOGIN(v,"admins","bad%d"%i); assert "doesn't exist" in al(h)
check("5 wrong tries are recorded", sql("select count(*) from login_attempts where username='admins'")=="5")
s,h,_=LOGIN(v,"admins","bad6"); check("6th try is blocked with a wait message", "Too many failed attempts" in al(h), al(h))
s,h,_=LOGIN(client(),"admins","admins"); check("even the CORRECT password is refused while blocked", "Too many failed attempts" in al(h) and "Administrator" not in h, al(h))
s,h,_=LOGIN(client(),"dennis","123456"); check("other usernames are not affected", "Student" in h, al(h))
sql("update login_attempts set attempted_at = NOW() - INTERVAL 16 MINUTE")
s,h,_=LOGIN(client(),"admins","admins"); check("after the window passes, login works again", "Administrator" in h, al(h))
check("successful login clears the counter", sql("select count(*) from login_attempts where username='admins'")=="0")
sql("delete from login_attempts"); LOGIN(client(),"nobody","x'; DROP TABLE usertbl;--")
check("long/odd usernames are stored safely, table intact", sql("select count(*) from login_attempts")=="1" and sql("select count(*) from usertbl")!="0")
LOGIN(client(),"x"*300,"y"); check("300-char username does not crash", sql("select max(char_length(username)) from login_attempts")=="50")
sql("delete from login_attempts")

section("student validation")
a=client(); LOGIN(a,"admins","admins")
def sp(**kw):
    d={"username":"val1","password":"pw1111","snum":"2026-101","course":"bsit","yrlvlsec":"I-1","fname":"V","minitial":"V","lname":"Val","gender":"male","bdate":"2005-01-02","address":"Roxas","cnumber":"09123456789","email":"v@e.com","fathername":"F","mothername":"M","guardianname":"G","bplace":"x","cstatus":"single","elemschool":"E","yrgradelem":"2016","hschool":"H","yrgradhs":"2020","lastschool":"L","yrlast":"2022","address2":"x","register":"x"}
    d.update(kw); return d
tk=token(a("Admin/addnewuser.php")[1])
def tryadd(msg,**kw):
    s,h,_=a("Admin/addnewuser.php",sp(token=tk,**kw)); ok=(msg in al(h)) and sql("select count(*) from usertbl where username='val1'")=="0"; return ok,al(h)
for name,kw,msg in [("bad email",{"email":"not-an-email"},"email address is not valid"),("short phone",{"cnumber":"123"},"7 to 11 digits"),("letters in phone",{"cnumber":"0912abc4567"},"7 to 11 digits"),
    ("future birth date",{"bdate":"2999-01-01"},"valid birth date"),("impossible date",{"bdate":"2005-02-31"},"valid birth date"),("no first name",{"fname":""},"First name"),("no address",{"address":""},"Address is required"),
    ("long address", {"address":"字"*501}, "500 characters"), ("long full name", {"fname":"a"*50,"lname":"b"*50}, "100 characters"),
    ("no gender",{"gender":""},"gender"),("bad civil status",{"cstatus":"x"},"civil status"),("no year level",{"yrlvlsec":""},"Year Level")]:
    ok,m=tryadd(msg,**kw); check("rejected: "+name,ok,m)
s,h,_=a("Admin/addnewuser.php",sp(token=tk,email="",cnumber="",bdate="")); check("birthdate is required", "Enter a birthdate" in al(h), al(h))
s,h,_=a("Admin/addnewuser.php",sp(token=tk,email="",cnumber="")); check("email and phone are optional", "Student ID:" in al(h), al(h))
student_id = re.search(r"Student ID: ([0-9]{2}-[0-9]{4})\.", al(h)).group(1)
sid=sql(f"select ID from studenttbl where studnum='{student_id}'")
e=token(a("Admin/edituser.php?id="+sid)[1])
s,h,_=a("Admin/edituser.php",sp(id=sid,save="x",token=e,email="bad@",password="")); check("edit page applies the same rules", "email address is not valid" in al(h))
s,h,_=a("Admin/edituser.php",sp(id=sid,save="x",token=e,email="ok@e.com",password="")); check("valid edit still saves", "updated" in al(h))
s,h,_=a("Admin/edituser.php?id=2"); e2=token(h)
s,h,_=a("Admin/edituser.php",{"id":2,"save":"x","token":e2,"username":"dennis","snum":"2014-052","course":"bsit","yrlvlsec":"II-2","fname":"Karl Macniel","minitial":"E","lname":"Flores","gender":"male","bdate":"2015-01-14","address":"a","cnumber":"09312312313","email":"karloemilflore@example.com","cstatus":"single","password":""})
check("the seeded sample student can be edited (seed data passes the rules)", "updated" in al(h), al(h))
s,h,_=a("Admin/addnewuser.php"); check("form has HTML5 checks (email type, required fields)", "type='email'" in h and "required='required' maxlength='50' name='fname'" in h)

section("database settings file")
check("config/db.local.php is git-ignored", "config/db.local.php" in open(os.path.join(REPO, ".gitignore")).read())
check("an example settings file ships with the project", os.path.isfile(os.path.join(REPO, "config", "db.local.example.php")))

section("home pages")
s,h,_=a("Admin/adminindex.php"); pend=sql("select count(*) from student_subject where approved=0 and school_year='2026 - 2027'")
check("admin home: no lorem ipsum, shows the real pending count, no warnings", "Nam scelerisque" not in h and ("%s pending form"%pend) in h and not warns(h), warns(h))
check("default approvals exclude the historical pending form", "2014-052" not in a("Admin/approvals.php")[1])
check("historical forms can be found by ID across years", "File Organization" in a("Admin/approvals.php?year=&semester=&q=2014-052")[1])
check("queue search can return an empty result", "No forms match" in a("Admin/approvals.php?year=&semester=&q=missing-student")[1])
d=client(); LOGIN(d,"dennis","123456"); s,h,_=d("student/studentindex.php")
check("student home: how-to-enroll steps with working links", "How to Enroll" in h and 'href="curriculum.php"' in h and 'href="enroll.php"' in h and 'href="report.php"' in h and "Nam scelerisque" not in h and 'href="#"' not in h)
check("footer shows the current year", ("Copyright %s"%datetime.date.today().year) in h)
finish()
