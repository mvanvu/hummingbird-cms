<ul class="uk-nav-sub">
    {% for item in items %}
        {{ renderer.getPartial('Content/NavItem', ['item': item]) }}
    {% endfor %}
</ul>