{% extends 'Index.base.volt' %}
{% block adminContent %}
    <div class="uk-grid-collapse" uk-grid>
        <aside class="uk-card uk-card-default uk-height-viewport" id="admin-aside">
            <div class="site-name uk-light uk-padding-small">
                <div class="uk-grid-collapse uk-flex uk-flex-middle" uk-grid>
                    <div class="uk-width-expand">
                        <h1 class="uk-h6 uk-margin-remove uk-text-truncate"
                            title="{{ siteName | escape_attr }}" uk-tooltip>
                            {{ siteName | escape }}
                        </h1>
                    </div>
                    <div class="uk-width-auto">
                        <a class="uk-link-reset"
                           href="{{ helper('Uri::getInstance', ['uri' : '/', 'client': 'site']) }}"
                           title="{{ _('view-site') }}" uk-tooltip target="_blank">
                            <span uk-icon="icon: home"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="uk-padding-small{{ isEmpty(disableNavbar) ? '' : ' uk-disabled' }}">
                {{ partial('Nav') }}
            </div>
        </aside>
        <main class="uk-width-expand uk-height-viewport" id="admin-main-container">
            <div class="admin-overlay uk-overlay uk-overlay-default uk-position-fixed uk-width-1-1 uk-height-viewport"
                 uk-toggle="target: #admin-aside; cls: open"></div>
            <section class="uk-section uk-padding-small section-header uk-light">
                <div class="uk-grid-small uk-flex-middle" uk-grid>
                    <div class="uk-width-expand">
                        <a class="uk-margin-small-right uk-hidden@m" uk-icon="icon: menu"
                           uk-toggle="target: #admin-aside; cls: open"></a>
                        <span class="uk-visible@s">
                            {% set title = get_title() | striptags %}
                            {{ isEmpty(title) ? siteName : title }}
                        </span>
                    </div>
                    <div class="uk-width-auto uk-flex uk-flex-middle uk-flex-right">
                        <div class="uk-inline uk-margin-small-right">
                            <a class="uk-link-reset">
                                {{ _('locale.title') }}
                                <span uk-icon="icon: triangle-down"></span>
                            </a>
                            <div uk-dropdown="mode: click">
                                <ul class="uk-nav uk-dropdown-nav">
                                    {% for code, language in helper('Language::getExistsLanguages') %}
                                        <li>
                                            <a class="uk-link-reset uk-active"
                                               href="{{ helper('Uri::getInstance', ['language': language.get('locale.sef')]) }}">
                                                {{ language.get('locale.title') }}
                                            </a>
                                        </li>
                                    {% endfor %}
                                </ul>
                            </div>
                        </div>
                        <div class="uk-inline">
                            <a class="uk-link-reset">
                                <span uk-icon="icon: user"></span>
                                {{ user.name }}
                                <span uk-icon="icon: triangle-down"></span>
                            </a>
                            <div uk-dropdown="mode: click">
                                <form name="logoutForm"
                                      action="{{ helper('Uri::getInstance', ['uri': 'user/logout']) }}"
                                      method="post">
                                    {{ helper('Form::tokenInput') }}
                                </form>
                                <ul class="uk-nav uk-dropdown-nav">
                                    <li class="uk-nav-header">
                                        <span uk-icon="icon: user"></span>
                                        {{ user.name }}
                                    </li>
                                    <li>
                                        <a class="uk-link-reset"
                                           href="{{ helper('Uri::getInstance', ['uri': 'user/edit/' ~ user.id]) }}">
                                            <span uk-icon="icon: pencil"></span>
                                            {{ _('edit-profile') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="uk-link-reset" href=""
                                           onclick="document.logoutForm.submit(); return false;">
                                            <span uk-icon="icon: sign-out"></span>
                                            {{ _('logout') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="admin-main-body uk-card uk-card-body">
                {{ flashSession.output() }}
                {{ helper('Toolbar::render') }}
                {{ content() }}
            </div>
        </main>
    </div>
{% endblock %}