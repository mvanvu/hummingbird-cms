{% set menuForm = formsManager.get('Menu') %}
<div class="uk-form-horizontal" id="menu-item-container">
    <div class="uk-margin">
        <a class="uk-button uk-button-small uk-button-primary btn-add-menu-item"
           uk-icon="icon: check"></a>
        <a class="uk-button uk-button-small uk-button-default btn-close-menu-item" uk-icon="icon: close"></a>
    </div>

    <div class="uk-margin">
        <div class="uk-grid-small uk-child-width-1-2" uk-grid>
            <div class="uk-width-2-3@s">
                {{ menuForm.renderField('title') }}
            </div>
            <div class="uk-width-1-3@s">
                {{ menuForm.renderField('icon') }}
            </div>
        </div>
    </div>

    {% if formsManager.get('params').count() %}
        <div class="uk-margin item-params">
            {{ formsManager.renderFormFields('params') }}
        </div>
    {% endif %}

    <div class="uk-margin">
        <div class="uk-grid-small uk-child-width-1-3" uk-grid>
            <div>
                {{ menuForm.renderField('target') }}
            </div>
            <div>
                {{ menuForm.renderField('nofollow') }}
            </div>
            <div>
                {{ menuForm.renderField('templateId') }}
            </div>
        </div>
    </div>

    <div class="uk-hidden">
        {{ menuForm.getField('id') }}
        {{ menuForm.getField('menu') }}
        {{ menuForm.getField('type') }}
    </div>
</div>