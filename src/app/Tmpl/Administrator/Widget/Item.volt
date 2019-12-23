<div class="widget-item toggle-parent">
    <div class="uk-padding-small uk-background-muted widget-toggle">
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-expand">
                <span class="uk-text-uppercase widget-type">{{ widgetConfig.get('manifest.name') }}</span>
                <span class="uk-text-meta uk-margin-small-left widget-title">{{ widgetConfig.get('title') }}</span>
            </div>
            <div class="uk-width-auto">
                <span uk-icon="icon: chevron-down"></span>
            </div>
        </div>
    </div>
    <div class="widget-config uk-position-relative uk-padding-small toggle-body uk-sortable-nodrag" hidden>
        <div class="uk-flex uk-flex-right">
            <a class="uk-text-danger uk-button uk-button-link widget-delete" href="#">
                {{ _('delete') }}
            </a>
            <span class="uk-margin-small-left uk-margin-small-right uk-text-meta">|</span>
            <a class="uk-button uk-button-link uk-text-primary widget-save" href="#">
                {{ _('save') }}
            </a>
        </div>
        <div class="uk-margin">
            {{ helper('Widget::renderForm', widgetConfig) }}
        </div>
    </div>
</div>
