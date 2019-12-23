{% set form = formsManager.get('general') %}
{% set itemId = model.id %}
{% set hasMetadata = formsManager.has('metadata') %}
{% set hasParams = paramsFormsManager.count() %}

<form id="admin-edit-form" action="{{ uri.routeTo('edit/' ~ (itemId ? itemId : 0)) }}" method="post">
    {{ form.getField('id').toString() }}
    {{ form.getField('context').toString() }}
    {{ form.getField('level').toString() }}
    {{ form.getField('ordering').toString() }}
    <ul uk-tab>
        <li>
            <a href="#">
                {{ _('general') }}
            </a>
        </li>

        {% if model.parentId > 0 %}
            {% set formsFields = helper('UcmField::buildUcmFormsFields', model.context, model.parentId) %}
            {% set formFields = helper('UcmField::buildUcmFormFields', model.context, model.parentId) %}
            {% for group, gFormFields in formsFields.getForms() %}
                <li>
                    <a href="#">
                        {{ group }}
                    </a>
                </li>
            {% endfor %}

            {% for field in formFields.getFields() %}
                <li>
                    <a href="#">
                        {{ field.label }}
                    </a>
                </li>
            {% endfor %}
        {% endif %}

        {% if hasParams %}
            {% for paramName, paramForm in paramsFormsManager.getForms() %}
                <li>
                    <a href="#">
                        {{ _('ucm-form-' ~ paramName ~ '-label') }}
                    </a>
                </li>
            {% endfor %}
        {% endif %}
        {% if hasMetadata %}
            <li>
                <a href="#">
                    {{ _('metadata') }}
                </a>
            </li>
        {% endif %}
    </ul>
    <ul class="uk-switcher">
        <li>
            <div class="uk-grid-small uk-grid-divider" uk-grid>
                <div class="uk-with-3-4@m uk-width-2-3@s">
                    <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-2-3@s">
                            {{ form.renderField('title') }}
                        </div>
                        <div class="uk-width-1-3@s">
                            {{ form.renderField('state') }}
                        </div>
                    </div>

                    {{ trigger('onAfterUcmEditTitle') | j2nl }}
                    {{ form.renderField('summary') }}
                    {{ form.renderField('description') }}
                </div>

                <div class="uk-with-1-4@m uk-width-1-3@s">
                    {% if formsManager.has('aside') %}
                        {{ formsManager.get('aside').renderFields() }}
                    {% endif %}

                    {{ trigger('onAfterUcmEditAside') | j2nl }}
                </div>
            </div>

            {{ trigger('onAfterUcmEditGeneral') | j2nl }}
        </li>

        {% if model.parentId > 0 %}
            {% set fieldsData = model.getFieldsData() %}
            {% set transData = model.getTranslationsFieldsData() %}

            {% for group, gFormFields in formsFields.getForms() %}
                <li class="uk-form-horizontal">
                    {{ gFormFields.bind(fieldsData, transData) | void }}
                    {{ gFormFields.renderFields() }}
                </li>
            {% endfor %}

            {{ formFields.bind(fieldsData, transData) | void }}
            {% for name, field in formFields.getFields() %}
                <li>
                    {{ partial('Form/FieldToString', ['field': field]) }}
                </li>
            {% endfor %}
        {% endif %}

        {% if hasParams %}
            <li class="uk-form-horizontal">
                {% for paramName, paramForm in paramsFormsManager.getForms() %}
                    {{ paramForm.renderFields() }}
                {% endfor %}
            </li>
        {% endif %}

        {% if hasMetadata %}
            <li class="uk-form-horizontal">
                {{ formsManager.get('metadata').renderFields() }}
            </li>
        {% endif %}
    </ul>
    <input type="hidden" name="postAction" value=""/>
    {{ helper('Form::tokenInput') }}
</form>