<ul class="uk-nav-default uk-nav">
    {% for item in items %}
        {{ renderer.getPartial('Content/NavItem', ['item': item]) }}
    {% endfor %}
</ul>