{% extends 'Template/HtmlBase.volt' %}
{% block body %}
<div class="uk-background-muted" id="site-app">
    {# Block before content #}
    {{ partial('Block/BeforeContent') }}

    {# Block main content #}
    {{ partial('Block/Content') }}

    {# Block after content #}
    {{ partial('Block/AfterContent') }}
</div>
{% endblock %}