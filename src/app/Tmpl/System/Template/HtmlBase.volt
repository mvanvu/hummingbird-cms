<!DOCTYPE html>
<html lang="{{ _('@code') | escape_attr }}"
      dir="{{ _('@direction') | lower }}"
      data-uri-home="{{ isHome() ? '1' : '0' }}"
      data-uri-root="{{ rootUri() | escape_attr }}"
      data-uri-base="{{ baseUri() | escape_attr }}"
      data-currency-code="{{ currencyCode | escape_attr }}"
      data-currency-symbol="{{ currencySymbol | escape_attr }}"
      data-currency-decimals="{{ currencyDecimals | escape_attr }}"
      data-currency-separator="{{ currencySeparator | escape_attr }}"
      data-currency-point="{{ currencyPoint | escape_attr }}"
      data-currency-format="{{ currencyFormat | escape_attr }}"
>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex, nofollow"/>
    <meta name="csrf" content="{{ csrf() }}"/>
    <title>{{ get_title() }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ public('images/favicon.ico') }}"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/css/uikit.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/js/uikit-icons.min.js"></script>
    {{ css() }}
</head>
<body>
{% block body %}{% endblock %}
{{ js() }}
</body>
</html>