{% extends 'Template/HtmlBase.volt' %}
{% block body %}
<div class="uk-background-cover uk-flex uk-flex-center uk-flex-middle uk-light uk-height-viewport uk-background-center-center"
     style="background-image: url({{ public('images/bg-login.jpg') }})">
    <div class="uk-position-cover uk-overlay-primary"></div>
    <div class="uk-card">
        <div class="uk-card-header uk-text-center">
            <h1 class="uk-heading-large uk-text-uppercase">{{ _('offline') }}</h1>
            <p>{{ cmsConfig.get('siteOfflineMsg', 'This site is down for maintenance.<br />Please check back again soon.') }}</p>
        </div>

        {% if user.is('guest') %}
            <div class="uk-card-body">
                <form class="user-login" action="{{ route('user/login', true) }}" method="post">
                    <div class="uk-margin">
                        <input class="uk-input" id="login-username" name="username" type="text"
                               autocomplete="off"
                               placeholder="{{ _('username') | escape_attr ~ '*' }}"
                               required autofocus
                        />
                    </div>
                    <div class="uk-margin">
                        <input class="uk-input" id="login-password" name="password" type="password"
                               autocomplete="off"
                               placeholder="{{ _('password') | escape_attr ~ '*' }}"
                               required/>
                    </div>
                    <div class="uk-margin">
                        <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                            {{ _('login') }}
                        </button>
                    </div>
                    {{ csrfInput() }}
                </form>
            </div>
        {% endif %}
    </div>
</div>
{% endblock %}