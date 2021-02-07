{% set isSuper = user.is('super') %}
<div class="dashboard">
    <div class="uk-margin">
        <h1 class="uk-heading-primary">
            {{ _('dashboard-welcome-title', ['siteName': siteName]) }}
        </h1>
        <p class="uk-text-meta">
            {{ _('dashboard-welcome-desc', ['version': cmsVersion]) }}
        </p>
    </div>

    <div class="uk-grid-match uk-grid-small uk-child-width-1-2@s" uk-grid>
        <div>
            <div class="uk-padding-small uk-background-muted">
                <h2 class="uk-heading-bullet uk-text-uppercase uk-h4">
                    {{ _('site') }}
                </h2>
                <div class="uk-padding-small uk-background-default">
                    <ul class="uk-list uk-list-divider">
                        {% if user.authorise('user.manage') %}
                            <li>
                                <div uk-grid>
                                    <div class="uk-width-expand">
                                        <a class="uk-link-reset uk-text-emphasis" href="{{ route('user/index') }}">
                                            {{ icon('users-o') ~ '&nbsp;' ~ helper('Text::plural', 'users-count', usersCount) }}
                                        </a>
                                    </div>
                                    <div class="uk-width-auto">
                                        <a class="uk-link-reset" href="{{ route('user/edit/0') }}">
                                            <span uk-icon="icon: plus"></span>
                                            {{ _('admin-user-add-title') }}
                                        </a>
                                    </div>
                                </div>
                            </li>
                        {% endif %}
                        {% if user.authorise('media.manage') %}
                            <li>
                                <div uk-grid>
                                    <div class="uk-width-expand">
                                        <a class="uk-link-reset uk-text-emphasis" href="{{ route('media/index') }}">
                                            {{ icon('pictures') ~ '&nbsp;' ~ helper('Text::plural', 'media-count', mediaCount) }}
                                        </a>
                                    </div>
                                    <div class="uk-width-auto">
                                        <a class="uk-link-reset" href="{{ route('media/index') }}">
                                            {{ icon('link') ~ '&nbsp;' ~ _('media') }}
                                        </a>
                                    </div>
                                </div>
                            </li>
                        {% endif %}

                        {% if isSuper %}
                            <li>
                                <div uk-grid>
                                    <div class="uk-width-expand">
                                        <a class="uk-link-reset uk-text-emphasis" href="{{ route('widget/index') }}">
                                            {{ icon('settings') ~ '&nbsp;' ~ helper('Text::plural', 'widgets-count', widgetsCount) }}
                                        </a>
                                    </div>
                                    <div class="uk-width-auto">
                                        <a class="uk-link-reset" href="{{ route('widget/index') }}">
                                            {{ icon('link') ~ '&nbsp;' ~ _('sys-widgets') }}
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div uk-grid>
                                    <div class="uk-width-expand">
                                        <a class="uk-link-reset uk-text-emphasis" href="{{ route('plugin/index') }}">
                                            {{ icon('plug') ~ '&nbsp;' ~ helper('Text::plural', 'plugins-count', pluginsCount) }}
                                        </a>
                                    </div>
                                    <div class="uk-width-auto">
                                        <a class="uk-link-reset" href="{{ route('plugin/index') }}">
                                            {{ icon('link') ~ '&nbsp;' ~ _('sys-plugins') }}
                                        </a>
                                    </div>
                                </div>
                            </li>
                        {% endif %}
                    </ul>
                </div>
            </div>
        </div>
        <div>
            <div class="uk-padding-small uk-background-muted">
                <h2 class="uk-heading-bullet uk-text-uppercase uk-h4">
                    {{ _('system') }}
                </h2>
                <div class="uk-padding-small uk-background-default">
                    <ul class="uk-list uk-list-divider">
                        {% if isSuper %}
                            <li>
                                <a class="uk-button uk-button-text" href="{{ route('config/index') }}">
                                    <span uk-icon="icon: settings"></span>
                                    {{ _('go-to-settings-page') }}
                                </a>
                            </li>
                        {% endif %}
                        <li>
                            {{ _('cms-version', ['version': cmsVersion]) }}
                        </li>
                        <li>
                            {{ _('php-version', ['version': phpVersion]) }}
                        </li>
                        <li>
                            {{ _('database-version', ['version': databaseVersion]) }}
                        </li>
                        <li>
                            {{ _('phalcon-version', ['version': phalconVersion]) }}
                        </li>

                        <li>
                            <div class="uk-text-emphasis uk-margin">
                                {{ _('minimal-system-requirements') ~ ':' }}
                            </div>
                            {% for extension, loaded in extensions %}
                                <span class="uk-margin-small-right uk-text-{{ loaded ? 'success' : 'danger' }}"
                                      uk-icon="icon: {{ loaded ? 'check' : 'close' }}">
                                    {{ extension | capitalize }}
                                </span>
                            {% endfor %}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>