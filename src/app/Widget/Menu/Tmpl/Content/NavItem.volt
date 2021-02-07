{% set title = item.title %}

{% if item.icon is not empty %}
    {% set title = helper('IconSvg::render', item.icon) ~ '&nbsp;' ~ title %}
{% endif %}

{% set children = item.getChildren() %}
{% set itemClass = 'menu-item' ~ item.id ~ (children ? ' uk-parent' : '') %}

{% if item.type === 'header' %}
    {% set headerType = item.params.get('headerType') %}

    {% if headerType == 'header' %}
        {% set itemContent = '<span class="uk-nav-header">' ~ item.title ~ '</span>' %}
    {% else %}
        {% set itemClass = itemClass ~ ' uk-nav-divider' %}
        {% set itemContent = '' %}
    {% endif %}
{% else %}
    {% if item.active %}
        {% set itemClass = itemClass ~ ' uk-active' %}
    {% endif %}

    {% set nofollow = item.nofollow ? ' rel="nofollow"' : '' %}
    {% set itemContent = '<a href="' ~ item.link ~ '"' ~ (item.target ? ' target="' . item.target ~ '"' : '') ~ nofollow ~ '>' ~ title ~ '</a>' %}
{% endif %}

{% if children | length %}
    {% set itemContent = itemContent ~ renderer.getPartial('Content/NavSub', ['items': children]) %}
{% endif %}

<li class="{{ itemClass }}">
    {{ itemContent }}
</li>