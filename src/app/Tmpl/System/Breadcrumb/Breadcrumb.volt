<ul class="uk-breadcrumb uk-flex-center uk-margin-remove">
    {% for breadcrumb in breadcrumbs %}
        <li>
            {% if breadcrumb['link'] is not empty %}
                <a href="{{ breadcrumb['link'] }}">
                    {{ breadcrumb['title'] }}
                </a>
            {% else %}
                <span>{{ breadcrumb['title'] }}</span>
            {% endif %}
        </li>
    {% endfor %}
</ul>