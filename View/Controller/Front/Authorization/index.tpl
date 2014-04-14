<!DOCTYPE html>
<html lang="ru" xmlns="http://www.w3.org/1999/xhtml">
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