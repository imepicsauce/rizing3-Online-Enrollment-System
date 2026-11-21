<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { notify_user('Session expired. Please try again.'); }
    elseif (isset($_POST['create'])) {
        $name = trim($_POST['name'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $pass = $_POST['password'] ?? '';
        if (!$name || !$login || strlen($pass) < 10) { notify_user('Enter a name, username, and password of at least 10 characters.'); }
        else {
            try {
                $hash = password_make($pass);
                $stmt = $conn->prepare("INSERT INTO usertbl (fullname, username, password, user_type) VALUES (?,?,?,'faculty')");
                $stmt->bind_param('sss',$name,$login,$hash);
                $stmt->execute();
                notify_user('Faculty account created.',true);
            } catch (mysqli_sql_exception $e) { notify_user('Could not create account. Check the username and details.'); }
        }
    } else {
        $faculty = (int)($_POST['faculty'] ?? 0);
        $subject = (int)($_POST['subject'] ?? 0);
        if ($faculty === 0) {
            $stmt = $conn->prepare('DELETE FROM faculty_subject WHERE subjectID=?');
            $stmt->bind_param('i',$subject);
        } else {
            $stmt = $conn->prepare("INSERT INTO faculty_subject (subjectID,facultyID) SELECT s.ID,u.ID FROM subjecttbl s JOIN usertbl u ON u.ID=? AND u.user_type='faculty' WHERE s.ID=? ON DUPLICATE KEY UPDATE facultyID=VALUES(facultyID)");
            $stmt->bind_param('ii',$faculty,$subject);
        }
        $stmt->execute();
        notify_user($stmt->affected_rows ? 'Assignment updated.' : 'No assignment changed.', (bool)$stmt->affected_rows);
    }
    redirect_to('faculty.php');
}
$staff = $conn->query("SELECT ID,fullname,username FROM usertbl WHERE user_type='faculty' ORDER BY fullname")->fetch_all(MYSQLI_ASSOC);
$subjects = $conn->query("SELECT s.ID,s.subjectcode,s.subjecttitle,f.facultyID FROM subjecttbl s LEFT JOIN faculty_subject f ON f.subjectID=s.ID ORDER BY subjectcode");
$pageTitle = 'Faculty access';
require __DIR__ . '/../includes/workspace_top.php';
?>
<p>Faculty can view schedules for assigned subjects. Only registrars can manage records or approve enrollments.</p>
<h2>Add faculty member</h2><form class="toolbar registrar-filters" method="post"><?= csrf_field() ?>
<label>Full name <input name="name" required maxlength="100"></label><label>Username <input name="login" maxlength="50" required></label>
<label>Password <input type="password" name="password" minlength="10" required autocomplete="new-password"></label><button name="create">Add faculty</button></form>
<h2>Teaching assignments</h2>
<?php while ($subject = $subjects->fetch_assoc()) { ?>
<form method="post" class="term faculty-assignment"><?= csrf_field() ?><input type="hidden" name="subject" value="<?= (int)$subject['ID'] ?>">
<label><?= h($subject['subjectcode'].' · '.$subject['subjecttitle']) ?> <select name="faculty"><option value="0">Unassigned</option>
<?php foreach ($staff as $person) { ?><option value="<?= (int)$person['ID'] ?>" <?= $person['ID']==$subject['facultyID']?'selected':'' ?>><?= h($person['fullname'].' ('.$person['username'].')') ?></option><?php } ?>
</select></label><button aria-label="Save assignment for <?= h($subject['subjectcode']) ?>">Save assignment</button></form><?php } require __DIR__ . '/../includes/workspace_bottom.php'; ?>
