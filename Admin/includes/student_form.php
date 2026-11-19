<?php
// Shared student form (add and edit). Expects:
//   $v         array of current values, keyed by the form field names
//   $courses   list of course codes
//   $isEdit    true when editing an existing account
// Field names the pages read: see STUDENT_FIELDS in student_form_lib.php
?>
<?php echo csrf_field(); ?>
<?php if ($isEdit) { ?>
<fieldset>
    <legend>Account details</legend>
    <div class="fields">
        <label>Username *
            <input type='text' required='required' maxlength='50' <?= $fixedID ? 'readonly' : '' ?> id="username"  name='username' value="<?php echo sf_val($v, 'username'); ?>"/>
        </label>
        <label>Reset Password (leave blank to keep current)
            <input type='password' maxlength='50' id="password" name='password' autocomplete="new-password"/>
        </label>
    </div>
</fieldset>
<?php } ?>
<fieldset>
    <legend>Academic record</legend>
    <div class="fields">
        <?php if ($isEdit) { ?>
        <label>Student number *
            <input type='text' maxlength='20' <?= $fixedID ? 'readonly' : '' ?> id="snum"  required='required' name='snum' value="<?php echo sf_val($v, 'snum'); ?>" />
        </label>
        <?php } ?>
        <label>Course *
            <select name='course' id="course">
			<?php foreach ($courses as $c) { ?>
			<option value="<?php echo h($c); ?>"<?php echo (($v['course'] ?? '') === $c) ? ' selected' : ''; ?>><?php echo h(strtoupper($c)); ?></option>
			<?php } ?>
		</select>
        </label>
        <label><?= empty($publicRegistration) ? 'Section / year level *' : 'Year and section (if assigned)' ?>
            <input type='text' <?= empty($publicRegistration) ? 'required' : '' ?> maxlength='50' id="yrlvlsec" name='yrlvlsec' value="<?php echo sf_val($v, 'yrlvlsec'); ?>" />
        </label>
    </div>
</fieldset>
<fieldset>
    <legend>Personal details</legend>
    <div class="fields">
        <label>First name *
            <input type='text' required='required' maxlength='50' name='fname' value="<?php echo sf_val($v, 'fname'); ?>" />
        </label>
        <label>Middle initial
            <input type='text' maxlength='5' name='minitial' value="<?php echo sf_val($v, 'minitial'); ?>" />
        </label>
        <label>Last name *
            <input type='text' required='required' maxlength='50' name='lname' value="<?php echo sf_val($v, 'lname'); ?>" />
        </label>
        <div class="field" role="group" aria-label="Gender">Gender *<label class="choice"><input type='radio' name='gender' required='required' value='male'<?php echo (($v['gender'] ?? '') === 'male') ? ' checked' : ''; ?>>Male</label> &nbsp;&nbsp;
					<label class="choice"><input type='radio' name='gender' value='female'<?php echo (($v['gender'] ?? '') === 'female') ? ' checked' : ''; ?>>Female</label></div>
        <label>Civil status
            <select name='cstatus'>
					<?php foreach (['single' => 'Single', 'married' => 'Married', 'widow' => 'Widow'] as $k => $label) { ?>
					<option value='<?php echo $k; ?>'<?php echo (($v['cstatus'] ?? '') === $k) ? ' selected' : ''; ?>><?php echo $label; ?></option>
					<?php } ?>
				</select>
        </label>
        <label>Birth date<?= $isEdit ? '' : ' *' ?>
            <input type="date" name="bdate" <?= $isEdit ? '' : 'required' ?> value="<?php echo sf_val($v, 'bdate'); ?>" />
        </label>
        <label>Birthplace
            <input type='text' maxlength='50' name='bplace' value="<?php echo sf_val($v, 'bplace'); ?>" />
        </label>
    </div>
</fieldset>
<fieldset>
    <legend>Contact details</legend>
    <div class="fields">
        <label>Address *
            <textarea name='address' id="address" required='required'><?php echo sf_val($v, 'address'); ?></textarea>
        </label>
        <label>Contact number
            <input type='text' name='cnumber' id="cnumber" maxlength='11' inputmode='numeric' pattern='[0-9]{7,11}' title='7 to 11 digits' value="<?php echo sf_val($v, 'cnumber'); ?>"/>
        </label>
        <label>Email address
            <input type='email' name='email' id="email" value="<?php echo sf_val($v, 'email'); ?>" />
        </label>
    </div>
</fieldset>
<?php if (empty($publicRegistration)) { ?>
<fieldset>
    <legend>Family information</legend>
    <div class="fields">
        <label>Guardian’s name
            <input type='text' maxlength='50' name='guardianname' value="<?php echo sf_val($v, 'guardianname'); ?>" />
        </label>
        <label>Father’s name
            <input type='text' maxlength='50' name='fathername' value="<?php echo sf_val($v, 'fathername'); ?>" />
        </label>
        <label>Mother’s name
            <input type='text' maxlength='50' name='mothername' value="<?php echo sf_val($v, 'mothername'); ?>" />
        </label>
    </div>
</fieldset>
<fieldset>
    <legend>Education history</legend>
    <div class="fields">
        <label>Elementary school
            <input type='text' name='elemschool' value="<?php echo sf_val($v, 'elemschool'); ?>" />
        </label>
        <label>Elementary graduation year
            <?php sf_year_select('yrgradelem', $v); ?>
        </label>
        <label>High school
            <input type='text' name='hschool' value="<?php echo sf_val($v, 'hschool'); ?>" />
        </label>
        <label>High school graduation year
            <?php sf_year_select('yrgradhs', $v); ?>
        </label>
        <label>Last school attended
            <input type='text' name='lastschool' value="<?php echo sf_val($v, 'lastschool'); ?>" />
        </label>
        <label>Last graduation year
            <?php sf_year_select('yrlast', $v); ?>
        </label>
        <label>Last school address
            <textarea name='address2' id="address2"><?php echo sf_val($v, 'address2'); ?></textarea>
        </label>
    </div>
</fieldset>
<?php } ?>
