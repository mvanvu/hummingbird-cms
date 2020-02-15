{% if field.get('translate', false) %}
    {% set tabTitle = [], tabContent = [], i = 0, def = helper('Config::get', 'siteLanguage') %}
    {% for langCode, language in helper('Language::getExistsLanguages') %}
        {% set title = '<li><a href="#" title="' ~ language.get('locale.title') | escape_attr ~ '" uk-tooltip="pos: bottom">' ~ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) ~  '</a></li>' %}
        {% set content = '<li>' ~ helper('Volt::toLanguageField', field, language.get('locale.code')).toString() ~ '</li>' %}

        {% if def === langCode %}
            {{ array_unshift(tabTitle, title) | void }}
            {{ array_unshift(tabContent, content) | void }}
        {% else %}
            {{ array_push(tabTitle, title) | void }}
            {{ array_push(tabContent, content) | void }}
        {% endif %}

        {% set i = i + 1 %}
    {% endfor %}
    <div class="uk-flex uk-flex-column uk-flex-column-reverse">
        <ul class="element-translation-tab uk-tab-bottom" uk-tab>
            {{ tabTitle | j2nl }}
        </ul>
        <ul class="element-translation-switcher uk-switcher" uk-switcher>
            {{ tabContent | j2nl }}
        </ul>
    </div>
{% else %}
    {{ field.toString() }}
{% endif %}