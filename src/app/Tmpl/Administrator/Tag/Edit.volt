{% set form = formsManager.get('general') %}
<form id="admin-edit-form" action="{{ uri.routeTo('edit/' ~ form.getField('id').getValue()) }}" method="post">
    {{ form.renderFields() }}
    {{ helper('Form::tokenInput') }}
</form>