<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #2b2b2b;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f4f4;
            padding-bottom: 40px;
        }

        .main {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-spacing: 0;
            font-family: sans-serif;
            color: #4a4a4a;
        }

        .header {
            background-color: #2b2b2b;
            padding: 20px;
            text-align: center;
        }

        .logo {
            max-width: 150px;
            height: auto;
        }

        .content {
            padding: 30px;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #c1c9cf;
            color: #2b2b2b !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="logo">
                </td>
            </tr>

            <tr>
                <td class="content">
                    @yield('content')
                </td>
            </tr>

            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Tutti i diritti riservati.
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
