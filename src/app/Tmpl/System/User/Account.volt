{% set allowUserRegistration = cmsConfig.get('allowUserRegistration') === 'Y' %}
<div id="user-account-container">
    {{ flashSession.output() }}
    <div class="uk-grid-divider uk-flex-center uk-child-width-1-2@s uk-margin" uk-grid>
        <div>
            <div class="uk-alert uk-margin">
                {{ _('user-' ~ (allowUserRegistration ? 'account' : 'login') ~ '-desc') }}
            </div>
            <h3 class="uk-heading-bullet uk-h5">
                {{ _('login') }}
            </h3>
            <form class="user-login" action="{{ helper('Uri::route', 'user/login', true) }}" method="post"
                  novalidate data-form-validation autocomplete="off">
                <div class="uk-margin">
                    <label class="uk-form-label" for="login-username">
                        {{ _('username') ~ '*' }}
                    </label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="login-username" name="username" type="text"
                               readonly
                               onfocus="this.removeAttribute('readonly')"
                               data-msg-required="{{ _('username-required-msg') | escape_attr }}"
                               required
                        />
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="login-password">
                        {{ _('password') ~ '*' }}
                    </label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="login-password" name="password" type="password"
                               data-msg-required="{{ _('password-required-msg') | escape_attr }}"
                               required/>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label uk-margin-remove" for="login-remember">
                        {{ _('user-remember') }}
                    </label>
                    <div class="uk-form-controls">
                        <input class="uk-checkbox" id="login-remember" name="remember" type="checkbox" value="Y"/>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-form-controls uk-form-controls-text">
                        <a class="uk-link-muted" href="{{ helper('Uri::route', 'user/forgot', true) }}">
                            {{ _('forgot-login-question') }}
                        </a>
                    </div>
                </div>
                {{ trigger('onBeforeLoginSubmitButton', [], ['Cms']) | j2nl }}
                <div class="uk-margin">
                    <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                        {{ _('login') }}
                    </button>
                </div>

                {{ trigger('onAfterLoginForm', [], ['Cms']) | j2nl }}

                {% if forward %}
                    <input name="forward" value="{{ forward | escape_attr }}" type="hidden"/>
                {% endif %}
                {{ csrfInput() }}
            </form>
        </div>

        {% if allowUserRegistration %}
            <div>
                <h3 class="uk-heading-bullet uk-h5">
                    {{ _('sign-up') }}
                </h3>
                <form class="user-register" action="{{ route('user/register') }}" method="post" novalidate
                      data-form-validation>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="register-name">
                            {{ _('your-name') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="register-name" name="name" type="text"
                                   autocomplete="off"
                                   required
                                   data-msg-required="{{ _('name-required-msg') | escape_attr }}"
                                   value="{{ isSet(registerData['name']) ? registerData['name'] : '' }}"/>
                        </div>
                    </div>

                    {% if cmsConfig.get('userEmailAsUsername', 'Y') !== 'Y' %}
                        <div class="uk-margin">
                            <label class="uk-form-label" for="register-username">
                                {{ _('username') ~ '*' }}
                            </label>
                            <div class="uk-form-controls">
                                <input class="uk-input" id="register-username" name="username" type="text"
                                       autocomplete="off" readonly onfocus="this.removeAttribute('readonly')"
                                       required data-msg-required="{{ _('username-required-msg') | escape_attr }}"
                                       value="{{ isSet(registerData['username']) ? registerData['username'] : '' }}"/>
                            </div>
                        </div>
                    {% endif %}

                    <div class="uk-margin">
                        <label class="uk-form-label" for="register-email">
                            {{ _('email') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="register-email" name="email" type="email"
                                   autocomplete="off"
                                   required data-msg-required="{{ _('email-required-msg') | escape_attr }}"
                                   data-rule-email data-msg-email="{{ _('email-invalid-msg') | escape_attr }}"
                                   value="{{ isSet(registerData['email']) ? registerData['email'] : '' }}"/>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="register-password">
                            {{ _('password') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="register-password" name="password" type="password"
                                   autocomplete="off"
                                   required data-msg-required="{{ _('password-required-msg') | escape_attr }}"
                                   value="{{ isSet(registerData['password']) ? registerData['password'] : '' }}"/>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="register-confirmPassword">
                            {{ _('confirm-password') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="register-confirmPassword" name="confirmPassword"
                                   type="password"
                                   autocomplete="off"
                                   data-rule-equal-to="#register-password"
                                   data-msg-equal-to="{{ _('password-not-match') | escape_attr }}"
                                   value="{{ isSet(registerData['confirmPassword']) ? registerData['confirmPassword'] : '' }}"/>
                        </div>
                    </div>
                    {{ reCaptcha() }}
                    {{ trigger('onBeforeRegisterSubmitButton', [], ['Cms']) | j2nl }}
                    <div class="uk-margin">
                        <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                            {{ _('register') }}
                        </button>
                    </div>
                    {{ trigger('onAfterRegisterForm', [], ['Cms']) | j2nl }}
                    {{ csrfInput() }}
                </form>
            </div>
        {% endif %}
    </div>
</div>