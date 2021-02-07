{% set parts = preg_split('/\s+/', activeOrder, 2) %}
{% set active = parts[0] === column %}
{% set order = preg_match('/asc$/i', activeOrder) ? 'DESC' : 'ASC' %}

<a class="{{ active ? 'sort-active' : 'sort-inactive' }}" data-column="{{ column }}" data-direction="{{ order }}"
   data-sort="{{ column ~ ' ' ~ order }}">
    {{ text }}
    {% if active %}
        <span uk-icon="icon: chevron-{{ order === 'DESC' ? 'up' : 'down' }}"></span>
    {% endif %}
</a>
