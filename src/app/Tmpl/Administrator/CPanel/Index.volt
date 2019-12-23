{% set isSuper = user.access('super') %}
<div class="dashboard">
    <div class="uk-margin">
        <h1 class="uk-heading-primary">
            {{ _('dashboard-welcome-title', ['siteName': siteName]) }}
        </h1>
        <p class="uk-text-meta">
            {{ _('dashboard-welcome-desc', ['version': constant('CMS_VERSION')]) }}
        </p>
    </div>

    <div class="uk-grid-match uk-grid-small uk-child-width-1-2@s" uk-grid>
        <div>
            <div class="uk-padding-small uk-background-muted">
                <h2 class="uk-heading-bullet uk-text-uppercase uk-h4">
                    {{ _('site') }}
                </h2>
                <div class="uk-grid-small uk-grid-match uk-child-width-1-2 uk-child-width-1-3@l" uk-grid>
                    <div>
                        <div class="uk-card uk-card-small uk-card-default uk-card-body uk-flex uk-flex-column uk-flex-center uk-flex-middle">
                            <a class="uk-link-reset uk-text-emphasis"
                               href="{{ route('user/index') }}">
                                <span class="uk-text-emphasis">
                                    {{ helper('Text::plural', 'users-count', usersCount, ['count': usersCount]) }}
                                </span>
                            </a>
                            <div class="uk-margin-small">
                                {{ helper('IconSvg::render', 'users-o', 32, 32) }}
                            </div>
                            <a class="uk-link-reset" href="{{ route('user/edit/0') }}">
                                <small>
                                    <span uk-icon="icon: plus"></span>
                                    {{ _('admin-user-add-title') }}
                                </small>
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="uk-card uk-card-small uk-card-default uk-card-body uk-flex uk-flex-column uk-flex-center uk-flex-middle">
                            <a class="uk-link-reset uk-text-emphasis"
                               href="{{ route('content/post/index') }}">
                                <span class="uk-text-emphasis">
                                    {{ helper('Text::plural', 'posts-count', postsCount, ['count': postsCount]) }}
                                </span>
                            </a>
                            <div class="uk-margin-small">
                                {{ helper('IconSvg::render', 'file-edit', 32, 32) }}
                            </div>
                            <a class="uk-link-reset" href="{{ route('content/post/edit/0') }}">
                                <small>
                                    <span uk-icon="icon: plus"></span>
                                    {{ _('post-admin-add-title') }}
                                </small>
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="uk-card uk-card-small uk-card-default uk-card-body uk-flex uk-flex-column uk-flex-center uk-flex-middle">
                            <a class="uk-link-reset uk-text-emphasis"
                               href="{{ route('content/post-category/index') }}">
                                <span class="uk-text-emphasis">
                                    {{ helper('Text::plural', 'categories-count', categoriesCount, ['count': categoriesCount]) }}
                                </span>
                            </a>
                            <div class="uk-margin-small">
                                {{ helper('IconSvg::render', 'albums', 32, 32) }}
                            </div>
                            <a class="uk-link-reset" href="{{ route('content/post-category/edit/0') }}">
                                <small>
                                    <span uk-icon="icon: plus"></span>
                                    {{ _('post-category-admin-add-title') }}
                                </small>
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="uk-card uk-card-small uk-card-default uk-card-body uk-flex uk-flex-column uk-flex-center uk-flex-middle">
                            <a class="uk-link-reset"
                               href="{{ route('media/index') }}">
                                <span class="uk-text-emphasis">
                                    {{ helper('Text::plural', 'media-count', mediaCount, ['count': mediaCount]) }}
                                </span>
                            </a>
                            <div class="uk-margin-small">
                                {{ helper('IconSvg::render', 'pictures', 32, 32) }}
                            </div>
                            <a class="uk-link-reset" href="{{ route('media/index') }}">
                                <small>
                                    {{ helper('IconSvg::render', 'link') }}
                                    {{ _('media') }}
                                </small>
                            </a>
                        </div>
                    </div>

                    {% if isSuper %}
                        <div>
                            <div class="uk-card uk-card-small uk-card-default uk-card-body uk-flex uk-flex-column uk-flex-center uk-flex-middle">
                                <span class="uk-text-emphasis">
                                    {{ helper('Text::plural', 'widgets-count', widgetsCount, ['count': widgetsCount]) }}
                                </span>
                                <div class="uk-margin-small">
                                    {{ helper('IconSvg::render', 'settings', 32, 32) }}
                                </div>
                                <a class="uk-link-reset" href="{{ route('widget/index') }}">
                                    <small>
                                        {{ helper('IconSvg::render', 'link') }}
                                        {{ _('sys-widgets') }}
                                    </small>
                                </a>
                            </div>
                        </div>
                        <div>
                            <div class="uk-card uk-card-small uk-card-default uk-card-body uk-flex uk-flex-column uk-flex-center uk-flex-middle">
                                <span class="uk-text-emphasis">
                                    {{ helper('Text::plural', 'plugins-count', pluginsCount, ['count': pluginsCount]) }}
                                </span>
                                <div class="uk-margin-small">
                                    {{ helper('IconSvg::render', 'plug', 32, 32) }}
                                </div>
                                <a class="uk-link-reset" href="{{ route('plugin/index') }}">
                                    <small>
                                        {{ helper('IconSvg::render', 'link') }}
                                        {{ _('sys-plugins') }}
                                    </small>
                                </a>

                            </div>
                        </div>
                    {% endif %}
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