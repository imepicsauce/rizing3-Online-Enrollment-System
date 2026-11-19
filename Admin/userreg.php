<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
$pageTitle = 'Student directory';
require __DIR__ . '/../includes/workspace_top.php';
?>
<div class="actions"><a class="btn" href="addnewuser.php">Add student</a><a href="adminedit.php">Account settings</a></div>
<div class="scroll directory">
<table id="datatables" class="display">
             <thead><tr>
				<th>Student ID</th>
             	<th>Student Name</th>
             	<th>Year Level</th>
             	<th>Course</th>
				<th>Action</th>
              </tr></thead>    
              <tbody>
			  <?php
				$getusers = $conn->query("SELECT ID, studnum, fname, minitial, lname, course, yearlvl FROM studenttbl");
				while ($r = $getusers->fetch_assoc()) { ?>
				<tr>
					<td><?php echo h($r['studnum']); ?></td>
					<td><?php echo h($r['fname'] . ' ' . $r['minitial'] . ' ' . $r['lname']); ?></td>
					<td><?php echo h($r['yearlvl']); ?></td>
					<td><?php echo h($r['course']); ?></td>
					<td>
						<a href="edituser.php?id=<?php echo (int)$r['ID']; ?>">EDIT</a> |
						<details class="confirm-action"><summary>Delete</summary>
<p>Delete student <?= h($r['studnum']) ?>? This also removes their profile and enrollment forms.</p>
<form method="post" action="deletecurruser.php" style="display:inline">
							<input type="hidden" name="accid" value="<?php echo (int)$r['ID']; ?>">
							<?php echo csrf_field(); ?>
							<button type="submit" class="link-button">Delete student</button>
						</form>
</details>
					</td>
				</tr>
				<?php } ?>
				</tbody>
		</table>
</div>
<script src="../assets/js/jquery-1.7.min.js"></script>
<script src="../assets/js/jquery.dataTables.js"></script>
<script src="../assets/js/directory.js"></script>
<?php require __DIR__ . '/../includes/workspace_bottom.php'; ?>
