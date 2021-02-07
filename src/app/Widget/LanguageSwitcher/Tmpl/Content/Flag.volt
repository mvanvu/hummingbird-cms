<ul class="uk-grid-small" uk-grid>
    {% for code, language in languages %}
        {% if active.get('locale.code') == code %}
            <li class="uk-active">
                <span uk-tooltip="{{ active.get('locale.title') | escape_attr }}">
                    {{ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) }}
                </span>
            </li>
        {% else %}
            <li>
                <a href="{{ routes[code] }}" uk-tooltip="{{ language.get('locale.title') | escape_attr }}">
                    {{ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) }}
                </a>
            </li>
        {% endif %}
    {% endfor %}
</ul>