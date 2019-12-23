<div class="widget-item{{ widget.get('id') }} widget-{{ widget.get('manifest.name') }}">
    {% if title is not empty %}
        <div class="widget-title">
            {{ title }}
        </div>
    {% endif %}

    {% if content is not empty %}
        <div class="widget-title">
            {{ content }}
        </div>
    {% endif %}
</div>