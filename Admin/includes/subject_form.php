<?php
// Shared subject form (add and edit). Expects $v (current values), $courses (list of course codes).
function subj_val($v, $k) { return h($v[$k] ?? ''); }
?>
<?php echo csrf_field(); ?>
<fieldset>
    <legend>Subject details</legend>
    <div class="fields">
        <label>Course *
            <select name='course' id="course">
			<?php foreach ($courses as $c) { ?>
			<option value='<?php echo h($c); ?>'<?php echo (($v['course'] ?? '') === $c) ? ' selected' : ''; ?>><?php echo h(strtoupper($c)); ?></option>
			<?php } ?>
		</select>
        </label>
        <label>Semester
            <select name="semester">
                <?php foreach (['' => 'Both semesters', '1st Sem' => '1st Sem', '2nd Sem' => '2nd Sem', 'Archived' => 'Archived'] as $value => $label) { ?>
                <option value="<?= h($value) ?>" <?= ($v['semester'] ?? '') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php } ?>
            </select>
        </label>
        <label>Subject code *
            <input type='text' required='required' maxlength='50' name='scode' value="<?php echo subj_val($v, 'scode'); ?>" />
        </label>
        <label>Subject title *
            <input type='text' required='required' maxlength='100' name='stitle' value="<?php echo subj_val($v, 'stitle'); ?>" />
        </label>
        <label>Units
            <select name='numunits'>
				<?php for ($u = 1; $u <= 6; $u++) { ?>
				<option value='<?php echo $u; ?>'<?php echo ((int)($v['numunits'] ?? 3) === $u) ? ' selected' : ''; ?>><?php echo $u; ?></option>
				<?php } ?></select>
        </label>
        <label>Description
            <textarea name='cdescription' maxlength='100'><?php echo subj_val($v, 'cdescription'); ?></textarea>
        </label>
    </div>
</fieldset>
<fieldset>
    <legend>Schedule and instruction</legend>
    <div class="fields">
        <label>Room
            <input type='text' maxlength='100' name='sroom' value="<?php echo subj_val($v, 'sroom'); ?>" />
        </label>
        <label>Day
            <input type='text' maxlength='100' name='sday' value="<?php echo subj_val($v, 'sday'); ?>" />
        </label>
        <label>Time
            <input type='text' maxlength='100' name='stime' value="<?php echo subj_val($v, 'stime'); ?>" />
        </label>
        <label>Instructor
            <input type='text' maxlength='100' name='sinstructor' value="<?php echo subj_val($v, 'sinstructor'); ?>" />
        </label>
    </div>
</fieldset>
<fieldset>
    <legend>Additional fees</legend>
    <div class="fields">
        <label class="choice">Lab fee applies
            <input type='checkbox' name='islab'<?php echo !empty($v['islab']) ? ' checked' : ''; ?>>
        </label>
        <label class="choice">Tutorial fee applies
            <input type='checkbox' name='istutorial'<?php echo !empty($v['istutorial']) ? ' checked' : ''; ?>>
        </label>
    </div>
</fieldset>
