"""Shared helpers for the test suites. Uses only the Python standard library.

The runner (run_tests.py) sets these environment variables before starting a suite:
  E2E_BASE        address of the test web server, e.g. http://127.0.0.1:8123/
  ENROLL_MYSQL    path to the mysql/mariadb command-line client
  ENROLL_DB_HOST / ENROLL_DB_PORT / ENROLL_DB_USER / ENROLL_DB_PASS / ENROLL_DB_NAME   (the TEST database)
"""
from html import unescape
import http.cookiejar, json, os, re, subprocess, sys, urllib.error, urllib.parse, urllib.request

BASE = os.environ["E2E_BASE"]
REPO = os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), ".."))
RESULTS = []


# ---------- database ----------
def sql(query):
    """Run one SQL statement on the TEST database and return the output as text (rows tab-separated)."""
    db = os.environ["ENROLL_DB_NAME"]
    assert db.endswith("_test"), "refusing to run against a database not named *_test: " + db
    cmd = [os.environ["ENROLL_MYSQL"], "-h", os.environ["ENROLL_DB_HOST"], "-P", os.environ["ENROLL_DB_PORT"],
           "-u", os.environ["ENROLL_DB_USER"], "--protocol=TCP", "-N", "-B", db, "-e", "SET SESSION sql_mode=''; " + query]
    if os.environ.get("ENROLL_DB_PASS"):
        cmd.insert(5, "-p" + os.environ["ENROLL_DB_PASS"])
    out = subprocess.run(cmd, capture_output=True, text=True)
    return out.stdout.strip()


# ---------- web client ----------
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


class Loc(str):
    """The Location header (empty string when there is none). Also gives access to the other headers."""
    headers = None
    def get(self, name, default=None):
        return self.headers.get(name, default) if self.headers else default
    def get_all(self, name):
        return self.headers.get_all(name) if self.headers else None


def client(auto_token=False):
    """A browser-like session with its own cookies. Redirects are NOT followed, so tests can see them.
    req(path, data=None) -> (status, html, Location). POSTs when data is given."""
    jar = http.cookiejar.CookieJar()
    op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect())

    def req(path, data=None):
        if auto_token and data is not None and path in ("Admin/addnewsubj.php", "Admin/addnewuser.php") and "token" not in data:
            data = dict(data, token=token(req(path)[1]))
        body = urllib.parse.urlencode(data, doseq=True).encode() if data is not None else None
        try:
            r = op.open(BASE + path, body, timeout=30)
            status, text, hdr = r.status, r.read().decode("utf-8", "ignore"), r.headers
        except urllib.error.HTTPError as e:
            status, text, hdr = e.code, e.read().decode("utf-8", "ignore"), e.headers
        loc = Loc(hdr.get("Location") or "")
        loc.headers = hdr
        if status == 303:
            target = urllib.parse.urljoin(BASE + path, loc)
            with op.open(target) as result:
                text = result.read().decode("utf-8")
        return status, text, loc
    return req


def login(c, user, password):
    return c("index.php", {"username": user, "password": password, "submit": "Login"})


def can_login(user, password):
    """True if these credentials log in (checked with a fresh session)."""
    s, h, _ = login(client(), user, password)
    return s == 303 and "Successfully log in" in h


# ---------- reading pages ----------
def warns(html):
    """PHP warnings/notices/fatal errors that leaked into a page."""
    return re.findall(r"<b>(Fatal error|Warning|Notice|Deprecated)</b>:\s*(.*?)(?: in <b>|<br)", html, re.S)


def token(html):
    m = re.search(r'name="token" value="([^"]+)"', html)
    return m.group(1) if m else ""


def alerts(html):
    return [unescape(message) for message in re.findall(r'<p class="portal-notice" role="(?:alert|status)">(.*?)</p>', html, re.S)]


def al(html):
    return " | ".join(alerts(html))


# ---------- reporting ----------
def check(name, ok, detail=""):
    RESULTS.append(bool(ok))
    print("[%s] %s%s" % ("PASS" if ok else "FAIL", name, ("  -- " + str(detail)[:300]) if (detail and not ok) else ""))


def clean_page(name, status, html):
    p = warns(html)
    check(name, status == 200 and not p, "HTTP %s %s" % (status, [x[0] + ": " + re.sub(r"\s+", " ", x[1])[:80] for x in p][:2]))


def section(title):
    print("\n== " + title)


def finish():
    print("\nSUMMARY: %d/%d checks passed" % (sum(RESULTS), len(RESULTS)))
    sys.exit(0 if all(RESULTS) else 1)
