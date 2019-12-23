{% set dropdown = level === 1 ? 'uk-dropdown' : 'uk-dropdown="pos: right-top"' %}

<div class="uk-navbar-dropdown" {{ dropdown }}>
    <ul class="uk-nav uk-navbar-dropdown-nav">
        {% for item in items %}
            {{ renderer.getPartial('Content/NavItem', ['item': item, 'level': level + 1]) }}
        {% endfor %}
    </ul>
</div>