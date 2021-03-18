{% extends 'Template/HtmlBase.volt' %}
{% block body %}
    <div id="admin-app">
        <main class="main-container uk-padding-small">
            {{ flashSession.output() }}
            {{ helper('Toolbar::render') }}
            {{ content() }}
        </main>
    </div>
{% endblock %}