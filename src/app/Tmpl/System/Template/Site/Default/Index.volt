<!DOCTYPE html>
<html lang="{{ _('locale.code') }}"
      dir="{{ _('locale.direction') }}"
      data-uri-home="{{ isHome() ? '1' : '0' }}"
      data-uri-root="{{ rootUri() }}"
      data-uri-base="{{ baseUri() }}">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf" content="{{ csrf() }}"/>
    <link rel="shortcut icon" type="image/x-icon" href="{{ public('images/favicon.ico') }}"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/uikit@3.6.17/dist/css/uikit.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.17/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.17/dist/js/uikit-icons.min.js"></script>
    {{ metadata() }}
    {{ css() }}
</head>
<body class="uk-background-muted">
<div id="site-app">
    {# Block before content #}
    {{ partial('Block/BeforeContent') }}

    {# Block main content #}
    {{ partial('Block/Content') }}

    {# Block after content #}
    {{ partial('Block/AfterContent') }}
</div>
{{ js() }}
</body>
</html>