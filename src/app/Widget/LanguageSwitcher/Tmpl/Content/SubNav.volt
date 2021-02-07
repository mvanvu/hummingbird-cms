<ul class="uk-subnav uk-subnav-divider">
    {% for code, language in languages %}
        {% if active.get('locale.code') == code %}
            <li class="uk-active">
                <span>
                    {{ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) }}
                    {{ language.get('locale.title') }}
                </span>
            </li>
        {% else %}
            <li>
                <a href="{{ routes[code] }}">
                    {{ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) }}
                    {{ language.get('locale.title') }}
                </a>
            </li>
        {% endif %}
    {% endfor %}
</ul>