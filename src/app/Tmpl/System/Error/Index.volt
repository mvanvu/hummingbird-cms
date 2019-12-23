<!DOCTYPE html>
<html lang="{{ _('locale.iso') }}" dir="{{ _('locale.direction') }}">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>{{ code ~ ' - ' ~ title }}</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,latin-ext" rel="stylesheet"
          type="text/css">

    <style>
        * {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            padding: 0;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            background: #05172a;
            color: white;
            overflow: hidden;
        }

        h1 {
            margin: 0;
            font-size: 22px;
            line-height: 24px;
        }

        .error-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100vw;
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 75%;
            max-width: 364px;
            padding: 24px;
            background: #fff;
            color: rgba(5, 23, 42, .8);
            border-radius: 8px;
            box-shadow: 0 28px 50px rgba(0, 0, 0, 0.16);
        }

        a {
            margin: 0;
            text-decoration: none;
            font-weight: 600;
            line-height: 24px;
            color: #00ad9f;
        }

        table {
            border: 0;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 5px 8px;
        }

        code {
            display: block;
            margin-bottom: 10px;
            line-height: 18px;
        }
    </style>

</head>

<body>

<div class="error-container">
    {{ content() }}
</div>
</body>
</html>