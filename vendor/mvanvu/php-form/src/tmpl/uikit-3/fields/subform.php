<?php

use MaiVu\Php\Form\Field\SubForm;
use MaiVu\Php\Form\Form;

/**
 * @var SubForm $this
 * @var array   $displayData
 * @var array   $options
 * @var Form    $form
 * @var integer $columns
 * @var boolean $repeatable
 */

extract($displayData);
$colCls = $columns === 4 ? 'uk-child-width-1-2@s uk-child-width-1-4@m' : 'uk-child-width-1-' . $columns . '@m';

?>

<div class="uk-grid uk-grid-small <?php echo $colCls; ?>" uk-margin>
	<?php foreach ($form->getFields() as $field): ?>
        <div data-field-name="<?php echo $field->getName(); ?>">
			<?php echo $field->render($options); ?>
        </div>
	<?php endforeach; ?>
</div>