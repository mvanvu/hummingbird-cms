{% set paginate = paginator.paginate() %}
{% set pages = paginate.getLast() %}
{% set uri = helper('Uri::getActive') %}

{% if pages > 1 %}
    <nav class="uk-margin">
        <ul class="uk-pagination uk-flex-center">
            {% if paginate.previous == paginate.current %}
                <li class="uk-active">
                    <span><span uk-pagination-previous></span></span>
                </li>
            {% else %}
                <li>
                    <a href="{{ uri.toString(['page' : paginate.previous]) }}">
                        <span uk-pagination-previous></span>
                    </a>
                </li>
            {% endif %}

            {% for page in 1..pages %}
                {% if page == paginate.current %}
                    <li class="uk-active"><span>{{ page }}</span></li>
                {% else %}
                    <li>
                        <a href="{{ uri.toString(['page' : page]) }}">
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
                    <a href="{{ uri.toString(['page' : paginate.next]) }}">
                        <span uk-pagination-next></span>
                    </a>
                </li>
            {% endif %}
        </ul>
    </nav>
{% endif %}