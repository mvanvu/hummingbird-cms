{% set allowUserRegistration = cmsConfig.get('allowUserRegistration') === 'Y' %}
<div class="uk-container">
    <div class="uk-margin-top uk-margin-bottom uk-form-horizontal">
        {{ flashSession.output() }}
        <div class="uk-alert uk-margin">
            {{ _('user-' ~ (allowUserRegistration ? 'account' : 'login') ~ '-desc') }}
        </div>
        <div class="uk-grid-divider uk-flex-center uk-child-width-1-2@s uk-margin" uk-grid>
            <div>
                <h3 class="uk-heading-bullet uk-h5">
                    {{ _('login') }}
                </h3>
                <form class="user-login" action="{{ helper('Uri::route', 'user/login', true) }}" method="post">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="login-username">
                            {{ _('username') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="login-username" name="username" type="text"
                                   autocomplete="off"
                                   required autofocus
                            />
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="login-password">
                            {{ _('password') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="login-password" name="password" type="password"
                                   autocomplete="off"
                                   required/>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <div class="uk-form-controls-text">
                            <a class="uk-link-muted" href="{{ helper('Uri::route', 'user/forgot', true) }}">
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
            </div>

            {% if allowUserRegistration %}
                <div>
                    <h3 class="uk-heading-bullet uk-h5">
                        {{ _('sign-up') }}
                    </h3>
                    <form class="user-register" action="{{ route('user/register') }}" method="post">
                        <div class="uk-margin">
                            <label class="uk-form-label" for="register-name">
                                {{ _('your-name') ~ '*' }}
                            </label>
                            <div class="uk-form-controls">
                                <input class="uk-input" id="register-name" name="name" type="text"
                                       autocomplete="off"
                                       required autofocus
                                       value="{{ isSet(registerData['name']) ? registerData['name'] : '' }}"/>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="register-username">
                                {{ _('username') ~ '*' }}
                            </label>
                            <div class="uk-form-controls">
                                <input class="uk-input" id="register-username" name="username" type="text"
                                       autocomplete="off"
                                       required
                                       value="{{ isSet(registerData['username']) ? registerData['username'] : '' }}"/>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="register-email">
                                {{ _('email') ~ '*' }}
                            </label>
                            <div class="uk-form-controls">
                                <input class="uk-input" id="register-email" name="email" type="email"
                                       autocomplete="off"
                                       required
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
                                       required
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
                                       required
                                       value="{{ isSet(registerData['confirmPassword']) ? registerData['confirmPassword'] : '' }}"/>
                            </div>
                        </div>
                        {{ trigger('onBeforeRegisterSubmitButton', [], ['System', 'Cms']) | j2nl }}
                        <div class="uk-margin">
                            <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                                {{ _('register') }}
                            </button>
                        </div>
                        {{ helper('Form::tokenInput') }}
                        {{ trigger('onAfterRegisterForm', [], ['System', 'Cms']) | j2nl }}
                    </form>
                </div>
            {% endif %}
        </div>
    </div>
</div>