<nav data-active-uri="{{ helper('Uri::getActive').getVar('uri') | escape_attr }}">
    <ul class="uk-nav-default uk-nav-parent-icon" uk-nav="multiple: true">
        <li>
            <a class="uk-nav-header home-dashboard" href="{{ route('') }}">
                {{ helper('IconSvg::render', 'pie-chart') }}
                {{ _('dashboard') }}
            </a>
        </li>
        <li class="uk-nav-divider"></li>
        {% if systemMenus is not empty %}
            {% for moduleTitle, moduleMenus in systemMenus %}
                {% if moduleTitle is numeric %}
                    <li>
                        <a class="uk-nav-header" href="{{ moduleMenus['url'] }}">
                            {{ moduleMenus['title'] }}
                        </a>
                    </li>
                {% else %}
                    <li class="uk-open uk-parent">
                        <a class="uk-nav-header" href="#">
                            {{ moduleTitle }}
                        </a>
                        <ul class="uk-nav-sub">
                            {% for moduleMenu in moduleMenus %}
                                <li>
                                    <a href="{{ moduleMenu['url'] }}">
                                        {{ moduleMenu['title'] }}
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