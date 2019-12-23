{% set form = formsManager.get('general') %}
<form id="admin-edit-form" action="{{ uri.routeTo('edit/' ~ form.getField('id').getValue()) }}" method="post">
    <div class="uk-grid-divider uk-child-width-1-2@s" uk-grid>
        <div>
            {{ form.renderFields() }}
        </div>
        <div>
            {{ paramsFormsManager.get('params').renderFields() }}
        </div>
    </div>

    {{ helper('Form::tokenInput') }}
</form>