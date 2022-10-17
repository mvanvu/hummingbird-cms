<div class="widget-item{{ widget['id'] }} widget-{{ widget['manifest.name'] }} uk-margin">
    {% if title is not empty %}
        <h4 class="uk-heading-line widget-title">
            <span>{{ title }}</span>
        </h4>
    {% endif %}

    {% if content is not empty %}
        <div class="widget-content">
            {{ content }}
        </div>
    {% endif %}
</div>