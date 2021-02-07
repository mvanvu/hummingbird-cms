{% set id = formsManager.get(general).getField('id').getValue() %}
<form id="admin-edit-form" action="{{ uri.routeTo('save/' ~ (id ? id : 0)) }}" method="post">
    <div class="uk-grid-divider" uk-grid>
        <div class="uk-width-2-3@m uk-width-3-5@l uk-width-1-2@s uk-form-horizontal">
            {{ formsManager.renderFormFields(general) }}
        </div>

        {% if formsManager.has('params') %}
            <div class="uk-width-1-3@m uk-width-2-5@l uk-width-1-2@s">
                {{ formsManager.renderFormFields('params') }}
            </div>
        {% endif %}
    </div>
    <div id="file-modal" class="uk-modal-full" style="z-index: 999" uk-modal="container: #admin-edit-form">
        <div class="uk-modal-dialog">
            {{ formsManager.get('files').getField('file').toString() }}
            <div class="uk-modal-footer uk-background-secondary uk-light uk-text-center">
                <button class="uk-button uk-button-default uk-modal-close" type="button">{{ _('close') }}</button>
                <button class="uk-button uk-button-primary btn-save" type="button">{{ _('save') }}</button>
            </div>
        </div>
    </div>
    {{ csrfInput() }}
</form>