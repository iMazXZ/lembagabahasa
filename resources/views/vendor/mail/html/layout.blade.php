<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <style>
        body {
            background-color: #f4f4f4;
            color: #2c3e50;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
        }

        a {
            color: #005aab;
            text-decoration: none;
        }

        .wrapper {
            width: 100%;
            padding: 30px 0;
            background-color: #f4f4f4;
        }

        .content {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .header {
            background-color: #005aab;
            padding: 20px;
            text-align: center;
        }

        .header a {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
        }

        .inner-body {
            width: 100%;
            padding: 30px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777777;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .footer a {
            color: #005aab;
        }

        @media only screen and (max-width: 600px) {
            .inner-body {
                padding: 15px !important;
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <h1 style="color: inherit; margin: 0; font-size: 22px;">
                                Lembaga Bahasa UM Metro
                            </h1>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="inner-body" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        {!! Illuminate\Mail\Markdown::parse($slot) !!}
                                        {!! $subcopy ?? '' !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td>
                            <table class="footer" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell" align="center">
                                        &copy; {{ date('Y') }} Lembaga Bahasa UM Metro.<br>
                                        Jl. Ki Hajar Dewantara No.116, Metro Barat, Kota Metro<br>
                                        <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
