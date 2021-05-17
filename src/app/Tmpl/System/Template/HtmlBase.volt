<!DOCTYPE html>
<html lang="{{ _('@code') | escape_attr }}" dir="{{ _('@direction') | lower }}">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex, nofollow"/>
    <meta name="csrf" content="{{ csrf() }}"/>
    <!--block:metadata-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ public('images/favicon.ico') }}"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/css/uikit.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/js/uikit-icons.min.js"></script>
    <!--block:afterHead-->
</head>
<body>
<!--block:beforeBody-->
{% block body %}{% endblock %}
<!--block:afterBody-->
</body>
</html>