<div class="widget-item{{ widget.get('id') }} widget-{{ widget.get('manifest.name') }}">
    {% if title is not empty %}
        <div class="widget-title">
            <h4 class="uk-heading-line uk-text-bold"><span>{{ title }}</span></h4>
        </div>
    {% endif %}

    {% if content is not empty %}
        <div class="widget-title">
            {{ content }}
        </div>
    {% endif %}
</div>