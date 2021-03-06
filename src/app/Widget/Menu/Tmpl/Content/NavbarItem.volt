{% set title = item.title %}

{% if item.icon is not empty %}
    {% set title = helper('IconSvg::render', item.icon) ~ '&nbsp;' ~ title %}
{% endif %}

{% set children = item.getChildren() %}
{% set itemClass = 'menu-item' ~ item.id ~ (children ? ' uk-parent' : '') %}

{% if item.type === 'header' %}
    {% set headerType = item.params.get('headerType') %}
    {% set itemClass = itemClass ~ ' uk-nav-' ~ headerType %}
    {% set itemContent = 'header' === headerType ? item.title : '' %}
    {% set closed = '' %}
{% else %}
    {% if item.active %}
        {% set itemClass = itemClass ~ ' uk-active' %}
    {% endif %}

    {% set nofollow = item.nofollow ? ' rel="nofollow"' : '' %}
    {% set itemContent = '<a href="' ~ item.link ~ '"' ~ (item.target ? ' target="' . item.target ~ '"' : '') ~ nofollow ~ '>' ~ title %}
    {% set closed = '</a>' %}
{% endif %}

{% if children | length %}
    {% if level === 1 %}
        {% set itemContent = itemContent ~ '<span class="uk-margin-small-left uk-icon" uk-icon="icon: chevron-down; ratio: .75"></span>' %}
    {% else %}
        {% set itemContent = itemContent ~ '<span class="uk-margin-small-left uk-icon" uk-icon="icon: chevron-right; ratio: .75"></span>' %}
    {% endif %}

    {% set itemContent = itemContent ~ closed %}
    {% set itemContent = itemContent ~ renderer.getPartial('Content/NavbarSub', ['items': children, 'level': level]) %}
{% else %}
    {% set itemContent = itemContent ~ closed %}
{% endif %}

<li class="{{ itemClass }}">
    {{ itemContent }}
</li>