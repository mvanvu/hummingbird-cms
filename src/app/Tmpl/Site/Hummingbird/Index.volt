{% extends 'Index.base.volt' %}

{% block siteContent %}
    {{ partial('Block/Content') }}
{% endblock %}

{% block siteBeforeContent %}
    {{ partial('Block/BeforeContent') }}
{% endblock %}

{% block siteAfterContent %}
    {{ partial('Block/AfterContent') }}
{% endblock %}