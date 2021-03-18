<ul class="uk-grid-small" uk-grid>
    {% for code, language in languages %}
        {% if active.get('attributes.code') == code %}
            <li class="uk-active">
                <span uk-tooltip="{{ active.get('attributes.name') | escape_attr }}">
                    {{ active.get('attributes.emoji') }}
                </span>
            </li>
        {% else %}
            <li>
                <a href="{{ routes[code] }}" uk-tooltip="{{ language.get('attributes.name') | escape_attr }}">
                    {{ language.get('attributes.emoji') }}
                </a>
            </li>
        {% endif %}
    {% endfor %}
</ul>