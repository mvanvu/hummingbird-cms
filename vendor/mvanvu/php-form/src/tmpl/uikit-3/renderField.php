<?php

use MaiVu\Php\Form\Field;

/**
 * @var Field $this
 * @var array $displayData
 */

$fieldClass = 'uk-margin ' . $displayData['class'];

if ($displayData['horizontal'])
{
	$fieldClass .= ' uk-grid uk-grid-small';
	$lblClass   = 'uk-width-1-4@s';
	$ctlClass   = 'uk-width-3-4@s';
}
else
{
	$lblClass = 'uk-form-label';
	$ctlClass = 'uk-form-controls';
}

?>
<div class="<?php echo $fieldClass; ?>"<?php echo $displayData['showOn'] ? ' data-show-on="' . htmlspecialchars(json_encode($displayData['showOn']), ENT_COMPAT, 'UTF-8') . '"' : ''; ?>>
	<?php if (!empty($displayData['label'])): ?>
        <label class="<?php echo $lblClass; ?>" for="<?php echo $displayData['id']; ?>">
			<?php echo $this->_($displayData['label']) . ($displayData['required'] ? '*' : ''); ?>
        </label>
	<?php endif; ?>
    <div class="<?php echo $ctlClass; ?>">
		<?php echo $this->input; ?>

        <div id="<?php echo $displayData['id'] . '-errors-msg'; ?>"<?php echo $displayData['errors'] ? '' : ' hidden' ?>>
            <small class="uk-form-controls-text uk-text-danger errors-msg">
				<?php echo implode('<br/>', $displayData['errors']); ?>
            </small>
        </div>

		<?php if ($displayData['description']): ?>
            <div id="<?php echo $displayData['id'] . '-desc'; ?>">
                <small class="uk-form-controls-text uk-text-muted">
					<?php echo $this->_($displayData['description']); ?>
                </small>
            </div>
		<?php endif; ?>
    </div>
    <div class="uk-clearfix"></div>
</div>