{% set ucmItem = helper('State::getMark', 'displayUcmItem') %}
{% set fieldsData = ucmItem.getFieldsData() %}

{% if fieldsData %}
    {% set tabsTitle = '' %}
    {% set tabsContent = '' %}
    {% set groups = helper('UcmField::findGroups', ucmItem.context, ucmItem.parentId) %}
    {% set fields = helper('UcmField::findFields', ucmItem.context, ucmItem.parentId) %}
    <div class="uk-margin">
        {% if groups.count() %}
            {% for group in groups %}
                {% if (group.fields.count()) %}
                    {% set tabContent = '' %}

                    {% for field in group.fields %}
                        {% set fieldValue = ucmItem.getFieldValue(field.name) %}

                        {% if fieldValue is not empty %}
                            {% set tabContent = tabContent ~ '<tr><th class="uk-table-shrink uk-text-nowrap">' ~ field.t('label') ~ '</th><td class="uk-table-expand">' ~ fieldValue ~ '</td></tr>' %}
                        {% endif %}

                    {% endfor %}

                    {% if tabContent is not empty %}
                        {% set tabsTitle = tabsTitle ~ '<li><a href="#">' ~ group.t('title') ~ '</a></li>' %}
                        {% set tabsContent = tabsContent ~ '<li><table class="uk-table uk-table-small uk-table-striped uk-table-divider"><tbody>' ~ tabContent ~ '</tbody></table></li>' %}
                    {% endif %}

                {% endif %}
            {% endfor %}
        {% endif %}

        {% if fields.count() %}
            {% for field in fields %}
                {% set fieldValue = ucmItem.getFieldValue(field.name) %}

                {% if fieldValue is not empty %}
                    {% set tabsTitle = tabsTitle ~ '<li><a href="#">' ~ field.t('label') ~ '</a></li>' %}
                    {% set tabsContent = tabsContent ~ '<li>' ~ fieldValue ~ '</li>' %}
                {% endif %}
            {% endfor %}
        {% endif %}

        {% if tabsContent is empty %}
            <div class="{{ ucmItem.context }}-text uk-margin">
                {{ ucmItem.t('description') }}
            </div>
        {% else %}
            <ul class="uk-tab" uk-tab>
                <li>
                    <a href="#">
                        {{ _('description') }}
                    </a>
                </li>
                {{ tabsTitle }}
            </ul>
            <ul class="uk-switcher uk-margin">
                <li>
                    <div class="{{ ucmItem.context }}-text uk-margin">
                        {{ ucmItem.t('description') }}
                    </div>
                </li>
                {{ tabsContent }}
            </ul>
        {% endif %}
    </div>
{% else %}
    <div class="{{ ucmItem.context }}-text uk-margin">
        {{ ucmItem.t('description') }}
    </div>
{% endif %}