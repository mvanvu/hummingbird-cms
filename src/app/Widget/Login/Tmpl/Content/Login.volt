{% set widgetId = widget.get('id') %}
<div class="widget-login{{ widgetId }}">
    <div class="uk-card uk-card-body uk-background-muted">
        {% if user().isGuest() %}
            <form class="widget-login-form" action="{{ route('user/login') }}" method="post">
                <div class="uk-margin">
                    <label class="uk-form-label" for="login-username{{ widgetId }}">
                        {{ _('username') ~ '*' }}
                    </label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="login-username{{ widgetId }}" name="username" type="text"
                               autocomplete="off" placeholder="{{ _('username') | escape_attr ~ '*' }}"
                               required
                        />
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="login-password{{ widgetId }}">
                        {{ _('password') ~ '*' }}
                    </label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="login-password{{ widgetId }}" name="password" type="password"
                               autocomplete="off" placeholder="{{ _('password') | escape_attr ~ '*' }}"
                               required/>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-form-controls-text">
                        <a class="uk-link-muted" href="{{ helper('Uri::route', 'user/forgot') }}">
                            {{ _('forgot-login-question') }}
                        </a>
                    </div>
                </div>
                {{ trigger('onBeforeLoginSubmitButton', [], ['System', 'Cms']) | j2nl }}
                <div class="uk-margin">
                    <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                        {{ _('login') }}
                    </button>
                </div>
                {{ helper('Form::tokenInput') }}
                {{ trigger('onAfterLoginForm', [], ['System', 'Cms']) | j2nl }}
            </form>
        {% else %}
            <h2 class="uk-text-lead">
                {{ _('hi-name', ['name': user().name]) }}
            </h2>

            <form action="{{ route('user/logout') }}" method="post">
                <button class="uk-button uk-button-primary" type="submit">
                    {{ _('logout') }}
                </button>
                {{ helper('Form::tokenInput') }}
            </form>
        {% endif %}
    </div>
</div>