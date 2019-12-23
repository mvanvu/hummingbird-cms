{% set generalForm = formsManager.get('general') %}
{% set paramsForm = paramsFormsManager.get('params') %}
{% set id = generalForm.getField('id').getValue() %}
{% set fields = ['id', 'name', 'email', 'username', 'password', 'confirmPassword'] %}

<form id="admin-edit-form" action="{{ uri.routeTo('save/' ~ (id ? id : 0)) }}" method="post">
    <div class="uk-grid-divider" uk-grid>
        <div class="uk-width-2-3@m uk-width-1-2@s uk-form-horizontal">
            {% for field in fields %}
                {{ generalForm.renderField(field) }}
            {% endfor %}

            {{ paramsForm.renderField('timezone') }}
        </div>

        <div class="uk-width-1-3@m uk-width-1-2@s">
            {{ paramsForm.renderField('avatar') }}

            {% for field in ['role', 'active'] %}
                {{ generalForm.renderField(field) }}
            {% endfor %}
        </div>

    </div>
    {{ helper('Form::tokenInput') }}
</form>