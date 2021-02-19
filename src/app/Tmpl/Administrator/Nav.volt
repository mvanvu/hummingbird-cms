<nav data-active-uri="{{ helper('Uri::getActive').getVar('uri') | escape_attr }}">
    <ul class="uk-nav-default uk-nav-parent-icon" uk-nav="multiple: true">
        <li>
            <a class="uk-nav-header home-dashboard" href="{{ route('') }}">
                {{ helper('IconSvg::render', 'pie-chart') }}
                {{ _('dashboard') }}
            </a>
        </li>
        <li class="uk-nav-divider"></li>
        {% if adminMenus is not empty %}
            {% for name, menus in adminMenus %}
                {% if menus['items'] is empty %}
                    <li>
                        <a class="uk-nav-header" href="{{ menus['url'] }}">
                            {{ menus['title'] }}
                        </a>
                    </li>
                {% else %}
                    <li class="uk-open uk-parent">
                        <a class="uk-nav-header" href="#">
                            {{ menus['title'] }}
                        </a>
                        <ul class="uk-nav-sub">
                            {% for item in menus['items'] %}
                                <li>
                                    <a href="{{ item['url'] }}">
                                        {{ item['title'] }}
                                    </a>
                                </li>
                            {% endfor %}
                        </ul>
                    </li>
                {% endif %}
            {% endfor %}
        {% endif %}
    </ul>
</nav>