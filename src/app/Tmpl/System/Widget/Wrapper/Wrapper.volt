<div class="widget-item{{ widget['id'] }} widget-{{ widget['manifest.name'] }} uk-margin">
    {% if title is not empty %}
        <div class="widget-title">
            {{ title }}
        </div>
    {% endif %}

    {% if content is not empty %}
        <div class="widget-content">
            {{ content }}
        </div>
    {% endif %}
</div>