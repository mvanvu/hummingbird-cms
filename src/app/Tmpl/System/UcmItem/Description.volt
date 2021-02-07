{% set ucmItem = helper('State::getMark', 'displayUcmItem') %}
{% set fieldsData = helper('UcmField::getFieldsData', ucmItem.context, ucmItem.id) %}

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

                        {% if isSet(fieldsData['fields'][field.name]) AND strlen(fieldsData['fields'][field.name]) > 0 %}
                            {% set tabContent = tabContent ~ '<tr><th class="uk-table-shrink uk-text-nowrap">' ~ field.t('label') ~ '</th><td class="uk-table-expand">' ~ fieldsData['fields'][field.name] ~ '</td></tr>' %}
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
                {% if isSet(fieldsData['fields'][field.name]) AND strlen(fieldsData['fields'][field.name]) > 0 %}
                    {% set tabsTitle = tabsTitle ~ '<li><a href="#">' ~ field.t('label') ~ '</a></li>' %}
                    {% set tabsContent = tabsContent ~ '<li>' ~ fieldsData['fields'][field.name] ~ '</li>' %}
                {% endif %}
            {% endfor %}
        {% endif %}

        {% if tabsContent is empty %}
            <div class="{{ ucmItem.context }}-text uk-margin">
                {{ ucmItem.content() }}
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
                        {{ ucmItem.content() }}
                    </div>
                </li>
                {{ tabsContent }}
            </ul>
        {% endif %}
    </div>
{% else %}
    <div class="{{ ucmItem.context }}-text uk-margin">
        {{ ucmItem.content() }}
    </div>
{% endif %}