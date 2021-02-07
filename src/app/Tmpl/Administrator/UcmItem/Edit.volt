{% set form = formsManager.get('UcmItem') %}
{% set itemId = model.id %}
{% set hasMetadata = formsManager.has('metadata') %}
{% set hasParams   = formsManager.has('params') %}

<form id="admin-edit-form" action="{{ uri.routeTo('edit/' ~ (itemId ? itemId : 0)) }}" method="post">
    <div class="item-details">
        <ul uk-tab>
            <li class="uk-active">
                <a href="#">
                    {{ _('general') }}
                </a>
            </li>

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

            {% if hasParams %}
                <li>
                    <a href="#">
                        {{ _('ucm-form-params-label') }}
                    </a>
                </li>
            {% endif %}

            {% if hasMetadata %}
                <li>
                    <a href="#">
                        {{ _('metadata') }}
                    </a>
                </li>
            {% endif %}
        </ul>
        <ul class="uk-switcher uk-margin">
            <li class="uk-active">
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
                        {{ form.renderField('description') }}
                    </div>

                    <div class="uk-with-1-4@m uk-width-1-3@s">
                        {% if formsManager.has('aside') %}
                            {{ formsManager.renderFormFields('aside') }}
                        {% endif %}
                    </div>
                </div>
            </li>

            {% set fieldsData = helper('UcmField::getFieldsData', model.context, model.id) %}
            {{ formsFields.bind(fieldsData) | void }}
            {% for group, gFormFields in formsFields.getForms() %}
                <li>
                    <div class="uk-width-2xlarge@s uk-form-horizontal">
                        {{ gFormFields.renderFields() }}
                    </div>
                </li>
            {% endfor %}

            {{ formFields.bind(fieldsData) | void }}
            {% for name, field in formFields.getFields() %}
                <li>
                    <div class="uk-width-2xlarge@s uk-form-horizontal">
                        {{ field.render() }}
                    </div>
                </li>
            {% endfor %}

            {% if hasParams %}
                <li>
                    <div class="uk-width-2xlarge@s uk-form-horizontal">
                        {{ formsManager.renderFormFields('params') }}
                    </div>
                </li>
            {% endif %}

            {% if hasMetadata %}
                <li>
                    <div class="uk-width-2xlarge@s uk-form-horizontal">
                        {{ formsManager.renderFormFields('metadata') }}
                    </div>
                </li>
            {% endif %}
        </ul>
    </div>
    {{ form.getField('id') }}
    {{ form.getField('context') }}
    {{ form.getField('level') }}
    {{ form.getField('ordering') }}
    <input type="hidden" name="postAction" value=""/>
    {{ csrfInput() }}
</form>