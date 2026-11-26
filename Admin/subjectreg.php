<?php
require_once(__DIR__ . '/../includes/auth.php');
require_role('admin');
require_once(__DIR__ . '/../config/db.php');
$pageTitle = 'Subject directory';
require __DIR__ . '/../includes/workspace_top.php';
?>
<div class="actions"><a class="btn" href="addnewsubj.php">Add subject</a></div>
<div class="scroll directory">
<table id="datatables" class="display">
             <thead><tr>
				<th>Subject Code</th>
             	<th>Subject Name</th>
                <th>Semester</th><th># of Units</th>
				<th>Action</th>
              </tr></thead>    
              <tbody>
			  <?php
				$getsubjects = $conn->query("SELECT ID, subjectcode, subjecttitle, semester, numofunits FROM subjecttbl");
				while ($r = $getsubjects->fetch_assoc()) { ?>
				<tr>
					<td><?php echo h($r['subjectcode']); ?></td>
					<td><?php echo h($r['subjecttitle']); ?></td>
					<td><?= h($r['semester'] ?: 'Both semesters') ?></td><td><?php echo h($r['numofunits']); ?></td>
					<td>
						<a href="editsubject.php?id=<?php echo (int)$r['ID']; ?>">EDIT</a> |
						<details class="confirm-action"><summary>Delete</summary>
<p>Delete subject <?= h($r['subjectcode']) ?>?</p>
<form method="post" action="deletecurrsubj.php" style="display:inline">
							<input type="hidden" name="accid" value="<?php echo (int)$r['ID']; ?>">
							<?php echo csrf_field(); ?>
							<button type="submit" class="link-button">Delete subject</button>
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
