{% set showOn = field.getShowOn() %}
<div class="field field-{{ field.type }} uk-margin"{{ showOn | length ? ' data-show-on="' ~ showOn | json_encode | escape_attr ~ '"' : '' }}>
    {% if field.label | length %}
        {% set descripton = field.get('description') | escape_attr %}
        <label class="uk-form-label"
               for="{{ field.getId() }}"{{ descripton | length ? ' uk-tooltip="' ~ _(descripton) ~ '"' : '' }}>
            {{ _(field.label) ~ (field.get('required') ? '*' : '') }}
            {{ descripton | length ? '&#33;' : '' }}
        </label>
    {% endif %}

    <div class="uk-form-controls">
        {{ partial('Form/FieldToString') }}
    </div>
</div>