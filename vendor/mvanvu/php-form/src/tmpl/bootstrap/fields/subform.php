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
$cols   = 12 / $columns;
$count  = $form->count();
$colCls = $columns === 4 ? 'col-sm-2 col-md-4' : 'col-md-' . $cols;
$i      = 0;
?>

<?php foreach ($form->getFields() as $field): ?>
	<?php echo $i % $columns === 0 ? '<div class="row">' : ''; ?>
    <div class="<?php echo $colCls; ?>" data-field-name="<?php echo $field->getName(); ?>">
		<?php echo $field->render($options); ?>
    </div>
	<?php echo ($i + 1) % $columns === 0 || $i + 1 === $count ? '</div>' : ''; ?>
	<?php $i++; ?>
<?php endforeach; ?>