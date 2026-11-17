<?php
// Shared student form (add and edit). Expects:
//   $v         array of current values, keyed by the form field names
//   $courses   list of course codes
//   $isEdit    true when editing an existing account
// Field names the pages read: see STUDENT_FIELDS in student_form_lib.php
?>
<?php echo csrf_field(); ?>
<fieldset>
	<legend>Account Details</legend>
	<ol>
	<li>
		<label for="username">UserName<em>*</em></label>
		<input type='text' required='required' maxlength='50' id="username" name='username' value="<?php echo sf_val($v, 'username'); ?>"/>
		<label for="password"><?php echo $isEdit ? 'Reset Password' : 'Password'; ?><?php echo $isEdit ? '' : '<em>*</em>'; ?></label>
		<?php if ($isEdit) { ?>
			<input type='password' maxlength='50' id="password" name='password' value="" placeholder="Leave blank to keep current" autocomplete="new-password"/>
		<?php } else { ?>
			<input type='text' required='required' maxlength='50' id="password" name='password' value="<?php echo sf_val($v, 'password'); ?>" />
		<?php } ?>
	</li>
	</ol>
</fieldset>

<fieldset>
	<legend>College Details</legend>
	<ol>
	<li>
		<label for="snum">Student Number:<em>*</em></label>
		<input type='text' maxlength='20' id="snum" required='required' name='snum' value="<?php echo sf_val($v, 'snum'); ?>" />
		<label for="course">Course<em>*</em></label>
		<select name='course' id="course">
			<?php foreach ($courses as $c) { ?>
			<option value="<?php echo h($c); ?>"<?php echo (($v['course'] ?? '') === $c) ? ' selected' : ''; ?>><?php echo h(strtoupper($c)); ?></option>
			<?php } ?>
		</select>
		<label for="yrlvlsec">Section/Year Level:<em>*</em></label>
		<input type='text' required='required' maxlength='50' id="yrlvlsec" name='yrlvlsec' value="<?php echo sf_val($v, 'yrlvlsec'); ?>" />
	</li>
	</ol>
</fieldset>

<fieldset>
	<legend>Personal Details</legend>
	<ol>
	<li>
		<table>
			<tr>
				<td>First Name:</td><td><input type='text' maxlength='50' name='fname' value="<?php echo sf_val($v, 'fname'); ?>" /></td>
				<td>Civil Status:</td><td><select name='cstatus'>
					<?php foreach (['single' => 'Single', 'married' => 'Married', 'widow' => 'Widow'] as $k => $label) { ?>
					<option value='<?php echo $k; ?>'<?php echo (($v['cstatus'] ?? '') === $k) ? ' selected' : ''; ?>><?php echo $label; ?></option>
					<?php } ?>
				</select></td>
			</tr>
			<tr>
				<td>Middle Initial:</td><td align='left'><input type='text' style='width:20px;' maxlength='5' name='minitial' value="<?php echo sf_val($v, 'minitial'); ?>" /></td>
				<td>Guardian's Name:</td><td align='left'><input type='text' maxlength='50' name='guardianname' value="<?php echo sf_val($v, 'guardianname'); ?>" /></td>
			</tr>
			<tr>
				<td>Last Name:</td><td><input type='text' maxlength='50' name='lname' value="<?php echo sf_val($v, 'lname'); ?>" /></td>
				<td>Father's Name:</td><td align='left'><input type='text' maxlength='50' name='fathername' value="<?php echo sf_val($v, 'fathername'); ?>" /></td>
			</tr>
			<tr>
				<td><label>Gender:</label></td><td align='left'>
					<input type='radio' name='gender' value='male'<?php echo (($v['gender'] ?? '') === 'male') ? ' checked' : ''; ?>>Male &nbsp;&nbsp;
					<input type='radio' name='gender' value='female'<?php echo (($v['gender'] ?? '') === 'female') ? ' checked' : ''; ?>>Female</td>
				<td>Mother's Name:</td><td align='left'><input type='text' maxlength='50' name='mothername' value="<?php echo sf_val($v, 'mothername'); ?>" /></td>
			</tr>
			<tr>
				<td>BirthDate:</td><td><input type="date" name="bdate" value="<?php echo sf_val($v, 'bdate'); ?>" /></td>
				<td>Birth Place:</td><td align='left'><input type='text' maxlength='50' name='bplace' value="<?php echo sf_val($v, 'bplace'); ?>" /></td>
			</tr>
		</table>
	</li>
	<li>
		<label for="address">Address<em>*</em></label>
		<textarea name='address' id="address"><?php echo sf_val($v, 'address'); ?></textarea>
	</li>
	<li>
		<label for="cnumber">Contact Number</label>
		<input type='text' name='cnumber' id="cnumber" maxlength='11' value="<?php echo sf_val($v, 'cnumber'); ?>"/>
	</li>
	<li>
		<label for="email">Email Address</label>
		<input type='text' name='email' id="email" value="<?php echo sf_val($v, 'email'); ?>" />
	</li>
	</ol>
</fieldset>

<fieldset>
	<legend>Educational Background</legend>
	<ol>
	<li>
		<table>
			<tr>
				<td>Elementary School:</td><td align='left'><input type='text' name='elemschool' value="<?php echo sf_val($v, 'elemschool'); ?>" /></td>
				<td>Year Graduated:</td><td align='left'><?php sf_year_select('yrgradelem', $v); ?></td>
			</tr>
			<tr>
				<td>High School:</td><td><input type='text' name='hschool' value="<?php echo sf_val($v, 'hschool'); ?>" /></td>
				<td>Year Graduated:</td><td align='left'><?php sf_year_select('yrgradhs', $v); ?></td>
			</tr>
			<tr>
				<td>School Last Attended:</td><td><input type='text' name='lastschool' value="<?php echo sf_val($v, 'lastschool'); ?>" /></td>
				<td>Year Graduated:</td><td align='left'><?php sf_year_select('yrlast', $v); ?></td>
			</tr>
			<tr>
				<td colspan='4' align='center'><label for="address2">Address<em>*</em></label>
				<textarea name='address2' id="address2"><?php echo sf_val($v, 'address2'); ?></textarea></td>
			</tr>
		</table>
	</li>
	</ol>
</fieldset>
