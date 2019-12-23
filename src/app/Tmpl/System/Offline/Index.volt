<!DOCTYPE html>
<html lang="{{ _('locale.code') }}" dir="{{ _('locale.direction') }}">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" type="image/x-icon" href="{{ constant('ROOT_URI') ~ '/assets/images/favicon.ico' }}"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/uikit@3.2.6/dist/css/uikit.min.css"/>
    <style>
        body {
            background: -webkit-linear-gradient(-45deg, #183850 0, #183850 25%, #192C46 50%, #22254C 75%, #22254C 100%);
        }
    </style>
</head>
<body>

<div class="uk-container uk-container-xsmall uk-height-viewport uk-light uk-flex uk-flex-center uk-flex-middle">
    <div class="uk-card">
        <div class="uk-card-header uk-text-center">
            <h1 class="uk-heading-large uk-text-uppercase">{{ _('offline') }}</h1>
            <p>{{ cmsConfig.get('siteOfflineMsg', 'This site is down for maintenance.<br />Please check back again soon.') }}</p>
        </div>

        {% if user.isGuest() %}
            <div class="uk-card-body">
                <form class="user-login" action="{{ helper('Uri::route', 'user/login', true) }}" method="post">
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

                    {{ helper('Form::tokenInput') }}
                </form>
            </div>
        {% endif %}
    </div>
</div>

</body>
</html>