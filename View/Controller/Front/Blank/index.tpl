<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
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
