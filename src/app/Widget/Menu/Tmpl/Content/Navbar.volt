<ul class="uk-navbar-nav">
    {% for item in items %}
        {{ renderer.getPartial('Content/NavItem', ['item': item, 'level': 1]) }}
    {% endfor %}
</ul>