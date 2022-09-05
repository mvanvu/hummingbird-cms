<?php

use MaiVu\Php\Form\Field\CheckList;

/**
 * @var CheckList $this
 * @var array     $displayData
 */

$class = trim('label-check ' . ($displayData['inline'] ? '' : 'uk-display-block ' . $displayData['labelClass']));

?>
<label class="<?php echo $class; ?>"
       for="<?php echo $displayData['id']; ?>">
    <input type="<?php echo $displayData['type']; ?>"
           class="<?php echo trim('uk-' . $displayData['type'] . ' ' . $displayData['class']); ?>"
           name="<?php echo $displayData['name']; ?>"
           value="<?php echo $displayData['value']; ?>"
           id="<?php echo $displayData['id']; ?>"
		<?php echo $displayData['required'] ? ' required' : '' ?>
		<?php echo $displayData['readonly'] ? ' readonly' : '' ?>
		<?php echo $displayData['disabled'] ? ' disabled' : '' ?>
		<?php echo $displayData['checked'] ? ' checked' : '' ?>
    />
	<?php echo '&nbsp;' . $displayData['label']; ?>
</label>