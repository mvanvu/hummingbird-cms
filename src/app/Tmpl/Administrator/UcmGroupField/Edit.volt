{% set form = formsManager.get('general') %}
<form id="admin-edit-form" action="{{ uri.routeTo('edit/' ~ form.getField('id').getValue()) }}" method="post">
    <div class="uk-width-large">
        {{ form.renderFields() }}
    </div>
    {{ helper('Form::tokenInput') }}
</form>