{% if field.get('translate', false) %}
    {% set tabTitle = '', tabContent = '', i = 0 %}
    {% for language in helper('Language::getExistsLanguages') %}
        {% set tabTitle = tabTitle ~ '<li><a href="#" title="' ~ language.get('locale.title') | escape_attr ~ '" uk-tooltip="pos: bottom">' ~ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) ~  '</a></li>' %}
        {% set tabContent = tabContent ~ '<li>' ~ helper('Volt::toLanguageField', field, language.get('locale.code')).toString() ~ '</li>' %}
        {% set i = i + 1 %}
    {% endfor %}
    <div class="uk-flex uk-flex-column uk-flex-column-reverse">
        <ul class="element-translation-tab uk-tab-bottom" uk-tab>
            {{ tabTitle }}
        </ul>
        <ul class="element-translation-switcher uk-switcher" uk-switcher>
            {{ tabContent }}
        </ul>
    </div>
{% else %}
    {{ field.toString() }}
{% endif %}