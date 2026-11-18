<?php
// Shared subject form (add and edit). Expects $v (current values), $courses (list of course codes).
function subj_val($v, $k) { return h($v[$k] ?? ''); }
?>
<?php echo csrf_field(); ?>
<fieldset>
	<legend>Course Details</legend>
	<ol>
	<li>
		<label for="course">Select Course:<em>*</em></label>
		<select name='course' id="course">
			<?php foreach ($courses as $c) { ?>
			<option value='<?php echo h($c); ?>'<?php echo (($v['course'] ?? '') === $c) ? ' selected' : ''; ?>><?php echo h(strtoupper($c)); ?></option>
			<?php } ?>
		</select>
	</li>
	</ol>
</fieldset>

<fieldset>
	<legend>Subject Details</legend>
	<ol>
	<li>
		<table>
			<tr><td>Subject Code:</td><td align='left'><input type='text' required='required' maxlength='50' name='scode' value="<?php echo subj_val($v, 'scode'); ?>" /></td></tr>
			<tr><td>Subject Title:</td><td align='left'><input type='text' required='required' maxlength='100' name='stitle' value="<?php echo subj_val($v, 'stitle'); ?>" /></td></tr>
			<tr><td>Number of Units:</td><td align='left'><select name='numunits'>
				<?php for ($u = 1; $u <= 6; $u++) { ?>
				<option value='<?php echo $u; ?>'<?php echo ((int)($v['numunits'] ?? 3) === $u) ? ' selected' : ''; ?>><?php echo $u; ?></option>
				<?php } ?></select></td></tr>
			<tr><td><label>Subject Description:</label></td><td align='left'><textarea name='cdescription' maxlength='100'><?php echo subj_val($v, 'cdescription'); ?></textarea></td></tr>
			<tr><td><label>Room:</label></td><td align='left'><input type='text' maxlength='100' name='sroom' value="<?php echo subj_val($v, 'sroom'); ?>" /></td></tr>
			<tr><td><label>Day:</label></td><td align='left'><input type='text' maxlength='100' name='sday' value="<?php echo subj_val($v, 'sday'); ?>" /></td></tr>
			<tr><td><label>Time:</label></td><td align='left'><input type='text' maxlength='100' name='stime' value="<?php echo subj_val($v, 'stime'); ?>" /></td></tr>
			<tr><td><label>Instructor:</label></td><td align='left'><input type='text' maxlength='100' name='sinstructor' value="<?php echo subj_val($v, 'sinstructor'); ?>" /></td></tr>
			<tr><td><label>With Lab:</label></td><td align='left'><input type='checkbox' name='islab'<?php echo !empty($v['islab']) ? ' checked' : ''; ?>></td></tr>
			<tr><td><label>With Tutorial:</label></td><td align='left'><input type='checkbox' name='istutorial'<?php echo !empty($v['istutorial']) ? ' checked' : ''; ?>></td></tr>
		</table>
	</li>
	</ol>
</fieldset>
