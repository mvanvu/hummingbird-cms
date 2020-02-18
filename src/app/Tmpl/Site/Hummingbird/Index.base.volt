<!DOCTYPE html>
<html lang="{{ _('locale.code') }}"
      dir="{{ _('locale.direction') }}"
      data-uri-home="{{ home() ? 'true' : 'false' }}"
      data-uri-root="{{ constant('ROOT_URI') }}"
      data-uri-base="{{ helper('Uri::getBaseUriPrefix') }}">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    {% if metadata is defined %}

        {% if metadata.metaKeys is not empty %}
            <meta name="keywords" content="{{ metadata.metaKeys }}"/>
        {% endif %}

        {% if metadata.metaDesc is not empty %}
            <meta name="description" content="{{ metadata.metaDesc }}"/>
        {% endif %}

        {% if metadata.contentRights is not empty %}
            <meta name="rights" content="{{ metadata.contentRights }}"/>
        {% endif %}

        {% if metadata.metaRobots is not empty %}
            <meta name="robots" content="{{ metadata.metaRobots }}"/>
        {% endif %}

        {% if metadata.metaTitle is not empty %}
            <title>{{ metadata.metaTitle }}</title>
        {% endif %}

    {% endif %}

    {% block head %}{% endblock %}
    <link rel="shortcut icon" type="image/x-icon" href="{{ constant('ROOT_URI') ~ '/assets/images/favicon.ico' }}"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/uikit@3.3.2/dist/css/uikit.min.css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.3.2/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.3.2/dist/js/uikit-icons.min.js"></script>
    {{ trigger('onSiteHead', ['System', 'Cms']) | j2nl }}
</head>
<body>
{# Hook before content #}
{{ trigger('onSiteBeforeContent', [], ['System', 'Cms']) | j2nl }}

{# Block before content #}
{% block siteBeforeContent %}{% endblock %}

{# Block main content #}
{% block siteContent %}{% endblock %}

{# Block after content #}
{% block siteAfterContent %}{% endblock %}

{# Hook after content #}
{{ trigger('onSiteAfterContent', [], ['System', 'Cms']) | j2nl }}

{% block afterBody %}{% endblock %}

{# Hook after render #}
{{ trigger('onSiteAfterRender', [], ['System', 'Cms']) | j2nl }}
</body>
</html>