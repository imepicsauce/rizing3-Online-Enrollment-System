from helpers import al, check, client, finish, login, sql, token

check("all seven programs offer six subjects in each semester",
      sql("SELECT COUNT(*) FROM (SELECT course_con, semester FROM subjecttbl WHERE semester IN ('1st Sem','2nd Sem') GROUP BY course_con,semester HAVING COUNT(*)=6) terms") == "14")
student = client()
login(student, "2014-052", "123456")
page = student("student/enroll.php?sem=2nd%20Sem")[1]
check("second semester lists its own BSIT offerings",
      "RU-BSIT-201" in page and "RU-BSIT-101" not in page and "RU-BSCS-201" not in page)
response = student("student/showsubs.php", {"subject[]": ["RU-BSIT-101"], "sem": "2nd Sem",
                                             "reg": "1", "token": token(page)})[1]
check("a subject from the wrong term cannot be submitted", "not offered" in al(response))
sql("UPDATE courses_tbl SET is_offered=0 WHERE course_code='bsit'")
check("retired program disappears from public catalog and registration",
      "Bachelor of Science in Information Technology" not in client()("index.php")[1]
      and 'value="bsit"' not in client()("register.php")[1].replace("'", '"'))
page = student("student/enroll.php?sem=2nd%20Sem")[1]
check("retired program students retain curriculum access without new offerings",
      "RU-BSIT-201" not in page and "RU-BSIT-201" in student("student/curriculum.php")[1])
response = student("student/showsubs.php", {"subject[]": ["RU-BSIT-201"], "sem": "2nd Sem",
                                             "reg": "1", "token": token(page)})[1]
check("previously offered subjects cannot be submitted after retirement", "not offered" in al(response))
finish()
