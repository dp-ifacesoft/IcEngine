/**
 * @desc Контроллер для авторизации в универсальной админке
 */
var Authorization_Login_Password_Sms = {

	/**
	 * @desc Провайдеры
	 */
	_providers: ['First_Success', 'Sms_Littlesms', 'Sms_Dcnk', 'Sms_Yakoon' ],

	_currentProvider: 0,

	_form: null,

	/**
	 * @desc Авторизация или отправка кода
	 * @param $form
	 */
	login: function ($form, send)
	{
		var cntr = Authorization_Login_Password_Sms;
		cntr._form = $form;
		var code = $form.find ('input[name=code]').val ();
		var $btn = $form.find ('span.btnSendCode');
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

			if (result.html) {
                $('#resultHtml').html(result.html);
			}

			if (result.error)
			{
				$btn.show ();
				return ;
			}

			if (result.data.redirect)
			{
				window.location.href = result.data.redirect;
			}
		}

		var provider = cntr._providers [cntr._currentProvider];
		Controller.call (
			'Authorization_Login_Password_Sms/login',
			{
				name: $form.find ('input[name=name]').val (),
				pass: $form.find ('input[name=pass]').val (),
				a_id: $form.find ('input[name=activation_id]').val (),
				code: code,
				href: location.href,
				provider: provider,
				send: send ? true : false
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
			'Authorization_Login_Password_Sms/logout',
			{
				href: window.location.href
			},
			callback, true
		);
	},

	/**
	 * @desc Отправить СМС еще раз
	 */
	rotate: function ()
	{
		var cntr = Authorization_Login_Password_Sms;

		cntr._currentProvider++;

		if (cntr._currentProvider >= cntr._providers.length)
		{
			cntr._currentProvider = 1;
		}

		cntr.login (cntr._form, true);
	}
};