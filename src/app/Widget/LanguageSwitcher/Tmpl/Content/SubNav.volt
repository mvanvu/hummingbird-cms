<ul class="uk-subnav uk-subnav-divider">
    {% for code, language in languages %}
        {% if active.get('attributes.code') == code %}
            <li class="uk-active">
                <span>
                    {{ language.get('attributes.emoji') }}
                    {{ language.get('attributes.name') }}
                </span>
            </li>
        {% else %}
            <li>
                <a href="{{ routes[code] }}">
                    {{ language.get('attributes.emoji') }}
                    {{ language.get('attributes.name') }}
                </a>
            </li>
        {% endif %}
    {% endfor %}
</ul>