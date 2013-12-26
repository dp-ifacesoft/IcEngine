/**
 * @desc Контроллер для авторизации в универсальной админке
 */
var Authorization_Email_Password_Sms = {
	
	/**
	 * @desc Авторизация или отправка кода
	 * @param $form
	 */
	login: function ($form)
	{
		var code = $form.find ('input[name=code]').val ();
		var $btn = $form.find ('input[name=btnSendCode]');
		$btn.nextAll ('div').remove ();
		
		if (!code)
		{
			$btn.hide ();
		}
		
		function callback (result)
		{
			if (result && result.data && result.data.activation_id)
			{
				$form.find ('input[name=activation_id]').val (
					result.data.activation_id
				);
			}
			
			if (result.html)
			{
				$btn.after ('<div>' + result.html + '<div>');
			}
			
			if (result.error)
			{
				$btn.show ();
				alert (result.error);
				return ;
			}
			
			if (result.redirect)
			{
				window.location.href = result.redirect;
			}
		}
		
		Controller.call (
			'Authorization_Email_Password_Sms/login',
			{
				name: $form.find ('input[name=name]').val (),
				pass: $form.find ('input[name=pass]').val (),
				a_id: $form.find ('input[name=activation_id]').val (),
				code: code,
				href: window.location.href
			},
			callback, true
		);
	},
	
	/**
	 * @desc Деавторизация
	 */
	logout: function ()
	{
		function callback (result)
		{
			window.location.href =
				result.redirect ?
					result.redirect :
					window.location.href;
		}
		
		Controller.call (
			'Authorization_Email_Password_Sms/logout',
			{
				href: window.location.href
			},
			callback, true
		);
	}
	
};