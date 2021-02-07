{% extends 'Index.base.volt' %}
{% block adminContent %}
    <main class="main-container uk-padding-small">
        {{ flashSession.output() }}
        {{ helper('Toolbar::render') }}
        {{ content() }}
    </main>
{% endblock %}