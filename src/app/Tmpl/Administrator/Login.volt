{% extends 'Index.base.volt' %}
{% block adminContent %}
    {% set defaultLangCode = helper('Config::get', 'administratorLanguage') %}
    <div class="uk-background-cover uk-flex uk-flex-center uk-flex-middle uk-light uk-height-viewport uk-background-center-center"
         style="background-image: url({{ constant('ROOT_URI') ~ '/assets/images/bg-login.jpg' }})">
        <div class="uk-position-cover uk-overlay-primary"></div>
        <div class="uk-width-large uk-position-z-index">
            <form action="{{ helper('Uri::getActive') }}" method="post">
                {{ flashSession.output() }}
                <div class="uk-margin">
                    <h3 class="uk-margin-remove-bottom">
                        {{ helper('Config::get', 'siteName') }}
                    </h3>
                    <p class="uk-text-meta uk-margin-remove-top">
                        {{ helper('Date::getInstance').toDisplay() }}
                    </p>
                </div>
                <div class="uk-margin">
                    <input class="uk-input uk-border-pill" name="username" type="text" required autofocus
                           placeholder="{{ _('username') }}"/>
                </div>
                <div class="uk-margin">
                    <input class="uk-input uk-border-pill" name="password" type="password" required
                           placeholder="{{ _('password') }}"/>
                </div>
                <div class="uk-margin">
                    <div class="uk-grid-small uk-child-width-1-2" uk-grid>
                        <div>
                            <select class="uk-select uk-border-pill not-chosen" name="language">
                                {% for code, language in helper('Language::getExistsLanguages') %}
                                    <option value="{{ code }}"{{ defaultLangCode === code ? ' selected' : '' }}>
                                        {{ language.get('locale.title') | escape }}
                                    </option>
                                {% endfor %}
                            </select>
                        </div>
                        <div>
                            <button class="uk-button uk-button-primary uk-width-1-1 uk-border-pill" type="submit">
                                {{ _('login') }}
                            </button>
                        </div>
                    </div>
                </div>
                {{ helper('Form::tokenInput') }}
            </form>
        </div>
    </div>
{% endblock %}