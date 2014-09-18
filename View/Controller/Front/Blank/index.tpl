<!DOCTYPE html>
<html lang="ru" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    {Controller call="View_Resource" name="{$resourceName}.css"}
    {Controller call="View_Resource" name="{$resourceName}-jtpl.js"}
    {Controller call="View_Resource" name="{$resourceName}.js"}
    <link href="/images_site/favicon.png" type="image/x-icon" rel="shortcut icon"/>
</head>
<body>
{$content}
</body>
</html>
