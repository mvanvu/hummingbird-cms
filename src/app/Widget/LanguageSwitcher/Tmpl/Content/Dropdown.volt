<div class="uk-inline">
    <a class="uk-link-reset">
        {{ active.get('attributes.emoji') }}
        {{ active.get('attributes.name') }}
    </a>
    <div uk-drop>
        <div class="uk-card uk-card-body uk-card-default uk-card-small">
            <ul class="uk-nav">
                {% for code, language in languages %}
                    {% if active.get('attributes.code') == code %}
                        <li>
                            <span>
                                {{ language.get('attributes.emoji') }}
                                {{ language.get('attributes.name') }}
                            </span>
                        </li>
                    {% else %}
                        <li>
                            <a class="uk-link-reset" href="{{ routes[code] }}">
                                {{ language.get('attributes.emoji') }}
                                {{ language.get('attributes.name') }}
                            </a>
                        </li>
                    {% endif %}
                {% endfor %}
            </ul>
        </div>
    </div>
</div>