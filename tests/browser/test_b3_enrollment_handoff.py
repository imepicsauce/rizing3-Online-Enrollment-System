"""Exercise a new student's enrollment and a registrar acting on a stale approval page."""
import re
from playwright.sync_api import sync_playwright
from helpers import BASE, check, finish, sql


def sign_in(page, username, password, destination):
    page.goto(BASE)
    page.get_by_role("link", name="Portal sign in", exact=True).click()
    page.fill("[name=username]", username)
    page.fill("[name=password]", password)
    page.click("[name=submit]")
    page.wait_for_url("**/" + destination)


def submit_enrollment(student):
    student.goto(BASE + "student/enroll.php")
    student.select_option("#sem", label="2nd Sem")
    student.check("input[value='0']")
    student.click("[name=register]")
    check("preview charges three units and one lab fee", "4,638.00" in student.inner_text("body"))
    student.get_by_role("button", name="Edit selection").click()
    student.wait_for_url("**/enroll.php")
    check("editing the review preserves the selected subject and live units",
          student.input_value("#sem") == "2nd Sem" and student.is_checked("input[value='0']") and "3 units" in student.inner_text("#units"))
    student.click("[name=register]")
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
    student.goto(BASE)
    student.emulate_media(reduced_motion="no-preference")
    student.get_by_role("link", name="Enrollment guide", exact=True).click()
    student.wait_for_url("**/#enrollment")
    check("enrollment guide anchor uses smooth scrolling", student.evaluate("getComputedStyle(document.documentElement).scrollBehavior") == "smooth")
    student.emulate_media(reduced_motion="reduce")
    check("reduced motion uses immediate anchor navigation", student.evaluate("getComputedStyle(document.documentElement).scrollBehavior") == "auto")
    # Reproduce an installation that has the enrollment portal but predates the ID migration.
    sql("DROP TABLE student_id_sequence")
    student.get_by_role("link", name="Create student account", exact=True).click()
    for field, value in {"bdate":"2004-05-06", "yrlvlsec":"I-1", "fname":"Sam", "lname":"Rivera", "address":"Roxas"}.items():
        student.fill("[name=" + field + "]", value)
    student.select_option("[name=course]", "bsit")
    student.check("input[value=female]")
    student.click("[name=register]")
    student.wait_for_url("**/portal.php")
    student_id = re.search(r"Student ID: ([0-9]{2}-[0-9]{4})\.", student.locator(".portal-notice").inner_text()).group(1)
    check("student ID is ready to use at sign-in", student.input_value("[name=username]") == student_id)
    check("registration preserves unknown graduation years", sql(
        f"SELECT COUNT(*) FROM studenttbl WHERE studnum='{student_id}' AND dategradelem='' AND datehsgrad='' AND datelastgrad=''"
    ) == "1")

    admin.goto(BASE + "Admin/addnewsubj.php")
    admin.select_option("[name=course]", "bsit")
    admin.fill("[name=scode]", "A,B")
    admin.fill("[name=stitle]", "Introductory lab")
    admin.select_option("[name=numunits]", "3")
    admin.check("[name=islab]")
    with admin.expect_navigation(wait_until="load"):
        admin.click("[name=register]")
    check("a comma in the subject code gets a specific correction", "comma" in admin.locator(".portal-notice").inner_text())
    admin.fill("[name=scode]", "0")
    admin.click("[name=register]")
    admin.wait_for_url("**/subjectreg.php")

    sign_in(student, student_id, "2004-05-06", "changepass.php")
    student.fill("[name=opassword]", "2004-05-06")
    student.fill("[name=password]", "my-private-password")
    student.fill("[name=password2]", "my-private-password")
    student.click("[name=cpass]")
    student.wait_for_url("**/studentindex.php")
    submit_enrollment(student)
    admin.goto(BASE + "Admin/approvals.php")
    row = admin.locator("tr", has_text=student_id)
    check("approval queue keeps the complete subject code", row.locator("td").nth(4).inner_text() == "0")
    student.goto(BASE + "student/report.php")
    check("student report resolves the subject title", "0 - Introductory lab" in student.inner_text("body"))
    student.locator("summary", has_text="Cancel").click()
    student.get_by_role("button", name="Cancel enrollment").click()
    student.wait_for_selector("text=You have not submitted an enrollment form yet.")
    row.get_by_role("button", name="Approve").click()
    admin.wait_for_selector("table.data-table")
    check("approving a cancelled form explains the stale result", "No change was made" in admin.locator(".portal-notice").inner_text())

    admin.reload()
    check("approval feedback clears on reload", admin.locator(".portal-notice").count() == 0)
    submit_enrollment(student)
    admin.reload()
    row.get_by_role("button", name="Approve").click()
    row.get_by_role("button", name="Set to pending").wait_for()
    student.goto(BASE + "student/report.php")
    check("student sees approval and cancellation is locked",
          "Approved" in student.inner_text("body") and student.locator(".confirm-action").count() == 0)
    admin.goto(BASE + "Admin/report.php")
    row = admin.locator("tr", has_text=student_id)
    check("registrar report agrees on the subject, units and status",
          row.locator("td").nth(4).inner_text() == "0" and row.locator("td").nth(5).inner_text() == "3"
          and row.locator("td").last.inner_text() == "Approved")
    admin.select_option("[name=status]", "pending")
    admin.get_by_role("button", name="Apply filters").click()
    admin.wait_for_url("**/report.php?**")
    check("pending filter excludes the approved form", admin.locator("tr", has_text=student_id).count() == 0)
    admin.select_option("[name=status]", "approved")
    admin.select_option("[name=semester]", label="2nd Sem")
    admin.get_by_role("button", name="Apply filters").click()
    admin.locator("tr", has_text=student_id).wait_for()
    admin.emulate_media(media="print")
    student.emulate_media(media="print")
    for report in (admin, student):
        check("print view includes the Rizing University masthead",
              report.locator(".print-mast").is_visible() and "Rizing University" in report.locator(".print-mast").inner_text())
    check("printed report retains its filter context", "2nd Sem · Approved" in admin.inner_text("main")
          and not admin.get_by_role("button", name="Apply filters").is_visible())
    check("registrar workflow uses no native dialogs", not messages)
    browser.close()
finish()
