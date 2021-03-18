<div class="uk-inline">
    <button class="uk-button uk-button-text" type="button">
        {{ active.get('attributes.emoji') }}
        {{ active.get('attributes.title') }}
    </button>
    <div uk-drop>
        <div class="uk-card uk-card-body uk-card-default uk-card-small">
            <ul class="uk-nav">
                {% for code, language in languages %}
                    {% if active.get('attributes.code') == code %}
                        <li class="uk-text-muted">
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