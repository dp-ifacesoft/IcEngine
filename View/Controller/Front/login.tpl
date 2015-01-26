<!DOCTYPE html>
<html lang="ru" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="/cache/cssAdmin.css?{$time}" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/cache/jsAdmin.js?{$time}"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Авторизация</title>
</head>
<body>
    <style>
        .main:after {
            background: url("http://twitter.github.com/bootstrap/assets/img/bs-docs-masthead-pattern.png") repeat scroll center center transparent;
            bottom: 0;
            content: "";
            z-index:0;
            display: block;
            left: 0;
            opacity: 0.4;
            position: absolute;
            right: 0;
            top: 0;
        }
    </style>
    <div style="height:380px; position:relative; background: linear-gradient(45deg, #020031 0%, #6D3353 100%) repeat scroll 0 0 transparent;
        box-shadow: 0 3px 7px rgba(0, 0, 0, 0.2) inset, 0 -3px 7px rgba(0, 0, 0, 0.2) inset; border-radius:0; margin:40px 0" class="main hero-unit green">
        <h1 style="font-family:Helvetica,&#8203;Arial,&#8203;sans-serif; 0 1px 3px rgba(0, 0, 0, 0.4), 0 0 30px rgba(0, 0, 0, 0.075); letter-spacing:-2px; text-rendering: optimizelegibility; color:white; font-size:120px; text-align:center">Войдите</h1>
        <div style="left:50%; margin-left:-300px; position:absolute; z-index:10">
            {$content}
        </div>
    </div>
    {$helperViewResource->embedJs()}
    {$helperViewResource->embedCss()}
    {$helperViewResource->embedJtpl()}
</body>
</html>