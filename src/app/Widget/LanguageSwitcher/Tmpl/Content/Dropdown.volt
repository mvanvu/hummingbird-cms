<div class="uk-inline">
    <button class="uk-button uk-button-text" type="button">
        {{ helper('Utility::getCountryFlagEmoji', active.get('locale.code2')) }}
        {{ active.get('locale.title') }}
    </button>
    <div uk-drop>
        <div class="uk-card uk-card-body uk-card-default uk-card-small">
            <ul class="uk-nav">
                {% for code, language in languages %}
                    {% if active.get('locale.code') == code %}
                        <li class="uk-text-muted">
                            <span>
                                {{ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) }}
                                {{ language.get('locale.title') }}
                            </span>
                        </li>
                    {% else %}
                        <li>
                            <a class="uk-link-reset" href="{{ routes[code] }}">
                                {{ helper('Utility::getCountryFlagEmoji', language.get('locale.code2')) }}
                                {{ language.get('locale.title') }}
                            </a>
                        </li>
                    {% endif %}
                {% endfor %}
            </ul>
        </div>
    </div>
</div>