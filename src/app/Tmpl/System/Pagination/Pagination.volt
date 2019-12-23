{% set paginate = paginator.paginate() %}
{% if admin() and uri is defined %}
    {% set prefix = 'index/?page=' %}
{% else %}
    {% set prefix = '/?page=' %}
    {% set uri = helper('Uri::getActive') %}
{% endif %}

{% if paginate.last > 1 %}
    <nav class="uk-margin">
        <ul class="uk-pagination uk-flex-center">
            {% if paginate.previous == paginate.current %}
                <li class="uk-active">
                    <span><span uk-pagination-previous></span></span>
                </li>
            {% else %}
                <li>
                    <a href="{{ uri.routeTo(prefix ~ paginate.before) }}">
                        <span uk-pagination-previous></span>
                    </a>
                </li>
            {% endif %}

            {% for page in 1..paginate.last %}
                {% if page == paginate.current %}
                    <li class="uk-active"><span>{{ page }}</span></li>
                {% else %}
                    <li>
                        <a href="{{ uri.routeTo(prefix ~ page) }}">
                            {{ page }}
                        </a>
                    </li>
                {% endif %}
            {% endfor %}

            {% if paginate.next == paginate.current %}
                <li class="uk-active">
                    <span><span uk-pagination-next></span></span>
                </li>
            {% else %}
                <li>
                    <a href="{{ uri.routeTo(prefix ~ paginate.next) }}">
                        <span uk-pagination-next></span>
                    </a>
                </li>
            {% endif %}
        </ul>
    </nav>
{% endif %}