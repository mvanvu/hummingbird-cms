<nav data-active-uri="{{ helper('Uri::getActive').getVar('uri') | escape_attr }}">
    <ul class="uk-nav-default uk-nav-parent-icon" uk-nav="multiple: true">
        <li>
            <a class="uk-nav-header home-dashboard" href="{{ route('') }}">
                {{ helper('IconSvg::render', 'pie-chart') }}
                {{ _('dashboard') }}
            </a>
        </li>
        <li class="uk-nav-divider"></li>
        <li class="uk-open uk-parent">
            <a class="uk-nav-header" href="#">
                {{ helper('IconSvg::render', 'pencil') }}
                {{ _('posts') }}
            </a>

            <ul class="uk-nav-sub">
                <li>
                    <a href="{{ route('content/post/index') }}">
                        {{ helper('IconSvg::render', 'file-edit') }}
                        {{ _('posts') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('content/post-category/index') }}">
                        {{ helper('IconSvg::render', 'albums') }}
                        {{ _('categories') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('media/index') }}">
                        {{ helper('IconSvg::render', 'pictures') }}
                        {{ _('media') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('post/comment/index') }}">
                        {{ helper('IconSvg::render', 'bubble') }}
                        {{ _('comments') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('group-field/post/index') }}">
                        {{ helper('IconSvg::render', 'albums') }}
                        {{ _('field-groups') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('field/post/index') }}">
                        {{ helper('IconSvg::render', 'field') }}
                        {{ _('fields') }}
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <a class="uk-nav-header" href="{{ route('tag/index') }}">
                {{ helper('IconSvg::render', 'tag') }}
                {{ _('tags') }}
            </a>
        </li>

        {% if systemMenus is not empty %}
            {% for moduleTitle, moduleMenus in systemMenus %}
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
            {% endfor %}
        {% endif %}

        {% if user.access('super') %}
            <li class="uk-open uk-parent">
                <a class="uk-nav-header" href="#">
                    {{ helper('IconSvg::render', 'ios-settings') }}
                    {{ _('system') }}
                </a>
                <ul class="uk-nav-sub">
                    <li>
                        <a href="{{ route('config/index') }}">
                            {{ helper('IconSvg::render', 'cog') }}
                            {{ _('settings') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('plugin/index') }}">
                            {{ helper('IconSvg::render', 'plug') }}
                            {{ _('sys-plugins') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('widget/index') }}">
                            {{ helper('IconSvg::render', 'settings') }}
                            {{ _('sys-widgets') }}
                        </a>
                    </li>
                    {{ trigger('onAfterSystemMenus', [], ['System']) | j2nl }}
                </ul>
            </li>
        {% endif %}

        <li>
            <a class="uk-nav-header" href="{{ route('menu/index') }}">
                {{ helper('IconSvg::render', 'menu') }}
                {{ _('menus') }}
            </a>
        </li>

        <li>
            <a class="uk-nav-header" href="{{ route('user/index') }}">
                {{ helper('IconSvg::render', 'users-o') }}
                {{ _('users') }}
            </a>
        </li>
    </ul>
</nav>