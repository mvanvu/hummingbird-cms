<div class="widget-item<?= $widget->get('id') ?> widget-<?= $widget->get('manifest.name') ?>">
    <?php if (!empty($title)) { ?>
        <div class="widget-title">
            <?= $title ?>
        </div>
    <?php } ?>

    <?php if (!empty($content)) { ?>
        <div class="widget-title">
            <?= $content ?>
        </div>
    <?php } ?>
</div>