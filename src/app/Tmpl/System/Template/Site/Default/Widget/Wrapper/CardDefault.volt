<div class="widget-item{{ widget['id'] }} widget-{{ widget['manifest.name'] }} uk-margin">
    <div class="uk-card uk-card-default">
        {% if title is not empty %}
            <div class="uk-card-header uk-background-muted">
                <h4 class="uk-card-title widget-title">
                    {{ title }}
                </h4>
            </div>
        {% endif %}

        {% if content is not empty %}
            <div class="widget-content uk-card-body">
                {{ content }}
            </div>
        {% endif %}
    </div>
</div>