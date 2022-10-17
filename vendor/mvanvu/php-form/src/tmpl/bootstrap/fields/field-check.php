<?php

use MaiVu\Php\Form\Field\CheckList;

/**
 * @var CheckList $this
 * @var array     $displayData
 */

?>
<div class="form-check<?php echo $displayData['inline'] ? ' form-check-inline' : '' ?>">
    <input type="<?php echo $displayData['type']; ?>"
           class="<?php echo trim('form-check-input ' . $displayData['class']); ?>"
           name="<?php echo $displayData['name']; ?>"
           value="<?php echo $displayData['value']; ?>"
           id="<?php echo $displayData['id']; ?>"
		<?php echo $displayData['required'] ? ' required' : '' ?>
		<?php echo $displayData['readonly'] ? ' readonly' : '' ?>
		<?php echo $displayData['disabled'] ? ' disabled' : '' ?>
		<?php echo $displayData['checked'] ? ' checked' : '' ?>
    />
    <label class="<?php echo trim('form-check-label ' . $displayData['labelClass']); ?>"
           for="<?php echo $displayData['id']; ?>">
		<?php echo $displayData['label']; ?>
    </label>
</div>