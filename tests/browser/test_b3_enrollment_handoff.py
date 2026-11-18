"""Exercise a new student's enrollment and a registrar acting on a stale approval page."""
from playwright.sync_api import sync_playwright
from helpers import BASE, check, finish, sql


def sign_in(page, username, password, destination):
    page.goto(BASE)
    page.fill("[name=username]", username)
    page.fill("[name=password]", password)
    page.click("[name=submit]")
    page.wait_for_url("**/" + destination)


def submit_enrollment(student):
    student.goto(BASE + "student/enroll.php")
    student.check("input[value='0']")
    student.click("[name=register]")
    check("preview charges three units and one lab fee", "4,638.00" in student.inner_text("body"))
    student.click("[name=reg]")
    student.wait_for_url("**/studentindex.php")


with sync_playwright() as playwright:
    browser = playwright.chromium.launch()
    admin = browser.new_page()
    student = browser.new_page()
    messages = []

    def accept_admin_dialog(dialog):
        messages.append(dialog.message)
        dialog.accept()

    admin.on("dialog", accept_admin_dialog)
    student.on("dialog", lambda dialog: dialog.accept())
    sign_in(admin, "admins", "admins", "adminindex.php")
    admin.goto(BASE + "Admin/addnewuser.php")
    for field, value in {"username": "registrant", "password": "sample123", "snum": "QA-01",
                         "yrlvlsec": "I-1", "fname": "Sam", "lname": "Rivera", "address": "Roxas"}.items():
        admin.fill("[name=" + field + "]", value)
    admin.select_option("[name=course]", "bsit")
    admin.check("input[value=female]")
    admin.click("[name=register]")
    admin.wait_for_url("**/userreg.php")
    check("registration preserves unknown graduation years", sql(
        "SELECT COUNT(*) FROM studenttbl WHERE studnum='QA-01' AND dategradelem='' AND datehsgrad='' AND datelastgrad=''"
    ) == "1")

    admin.goto(BASE + "Admin/addnewsubj.php")
    admin.select_option("[name=course]", "bsit")
    admin.fill("[name=scode]", "A,B")
    admin.fill("[name=stitle]", "Introductory lab")
    admin.select_option("[name=numunits]", "3")
    admin.check("[name=islab]")
    with admin.expect_navigation(wait_until="load"):
        admin.click("[name=register]")
    check("a comma in the subject code gets a specific correction", "comma" in messages[-1])
    admin.fill("[name=scode]", "0")
    admin.click("[name=register]")
    admin.wait_for_url("**/subjectreg.php")

    sign_in(student, "registrant", "sample123", "studentindex.php")
    submit_enrollment(student)
    admin.goto(BASE + "Admin/approvals.php")
    row = admin.locator("tr", has_text="QA-01")
    check("approval queue keeps the complete subject code", row.locator("td").nth(4).inner_text() == "0")
    student.goto(BASE + "student/report.php")
    check("student report resolves the subject title", "0 - Introductory lab" in student.inner_text("body"))
    student.get_by_role("button", name="Cancel").click()
    student.wait_for_selector("text=You have not submitted an enrollment form yet.")
    row.get_by_role("button", name="Approve").click()
    admin.wait_for_selector("table.data-table")
    check("approving a cancelled form explains the stale result", "No change was made" in messages[-1])

    submit_enrollment(student)
    admin.reload()
    row.get_by_role("button", name="Approve").click()
    row.get_by_role("button", name="Set to pending").wait_for()
    student.goto(BASE + "student/report.php")
    check("student sees approval and cancellation is locked",
          "Approved" in student.inner_text("body") and student.get_by_role("button", name="Cancel").count() == 0)
    admin.goto(BASE + "Admin/report.php")
    row = admin.locator("tr", has_text="QA-01")
    check("registrar report agrees on the subject, units and status",
          row.locator("td").nth(4).inner_text() == "0" and row.locator("td").nth(5).inner_text() == "3"
          and row.locator("td").last.inner_text() == "Approved")
    browser.close()
finish()
