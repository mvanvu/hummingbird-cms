<?php

use MaiVu\Php\Form\Field;

/**
 * @var Field $this
 * @var array $displayData
 */

$fieldClass = 'form-group ' . ($displayData['horizontal'] ? 'row ' : '') . $displayData['class'];

?>
<div class="<?php echo $fieldClass; ?>"<?php echo $displayData['showOn'] ? ' data-show-on="' . htmlspecialchars(json_encode($displayData['showOn']), ENT_COMPAT, 'UTF-8') . '"' : ''; ?>>
	<?php if ($displayData['label']): ?>
        <label for="<?php echo $displayData['id']; ?>"
               class="<?php echo $displayData['horizontal'] ? 'col-sm-2 ' : ''; ?>col-form-label control-label">
			<?php echo $this->_($displayData['label']) . ($displayData['required'] ? '*' : ''); ?>
        </label>
	<?php endif; ?>
    <div class="<?php echo $displayData['horizontal'] ? 'col-sm-10' : 'col-form-control'; ?>">
		<?php echo $this->input; ?>

        <div id="<?php echo $displayData['id'] . '-errors-msg'; ?>"<?php echo $displayData['errors'] ? '' : ' hidden' ?>>
            <small class="form-text text-danger errors-msg">
				<?php echo implode('<br/>', $displayData['errors']); ?>
            </small>
        </div>

		<?php if ($displayData['description']): ?>
            <div id="<?php echo $displayData['id'] . '-desc'; ?>">
                <small class="form-text text-muted">
					<?php echo $this->_($displayData['description']); ?>
                </small>
            </div>
		<?php endif; ?>
    </div>
</div>