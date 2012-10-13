<form method="post" action="javascript:void(0);" onsubmit="Authorization_Login_Password_Sms.login ($(this));">
	<table style="border-collapse:separate; border-spacing: 8px; margin: 20px auto;">
		<tr>
			<td><label for="name">Логин</label></td>
			<td><input name="name" type="text" style="font-size: 170%; width: 500px;" /></td>
		</tr>
		<tr>
			<td><label for="pass">Пароль</label></td>
			<td><input name="pass" type="password" style="font-size: 170%; width: 500px;" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input name="btnSendCode" type="submit" value="Отправить код" style="font-size: 170%;" /></td>
		</tr>
		<tr>
			<td><label for="code">Смс код</label></td>
			<td><input name="code" type="text" value="" style="font-size: 170%; width: 500px;" /></td>
		</tr>
		<tr>
			<td><input name="activation_id" type="hidden" value="" /></td>
			<td><input type="submit" value="Войти" style="font-size: 170%;" /></td>
		</tr>
	</table>
</form>