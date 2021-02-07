{% if toolbars is not empty %}
    <div class="toolbars-container uk-margin">
        <div class="uk-flex">
            {% for name, toolbar in toolbars %}
                {% if is_array(toolbar) %}
                    <a class="toolbar-{{ name }}" href="{{ toolbar['route'] }}">
                        {{ helper('IconSvg::render', toolbar['icon']) }}
                        <span>{{ toolbar['text'] }}</span>
                    </a>
                {% else %}
                    {{ toolbar }}
                {% endif %}
            {% endfor %}
        </div>
    </div>
{% endif %}