<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            {Controller call="View_Resource" name="style.css"}
            {Controller call="View_Resource" name="jtpl.js"}
            {Controller call="View_Resource" name="javascript.js"}
    </head>
    <body>
        {Controller call="Authorization_Login_Password_Sms/index"}
    </body>
</html>