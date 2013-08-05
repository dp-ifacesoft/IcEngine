/**
 * @desc Контроллер авторизации по СМС
 */
var Controller_Authorization_Phone_Sms_Send = {
	
	/**
	 * @desc Зарегистрирован ли пользователь с указанным телефонам.
	 * @var boolean
	 */
	_phoneRegistered: null,
	
	/**
	 * @desc Авторизация.
     * @param $form
	 */
	login: function ($form)
	{
		var $phone = $form.find ('input[name=email]');
		var $sms_session_id = $form.find ('input[name=sms_session_id]');
		var $sms_session_code = $form.find ('input[name=sms_session_code]');
		var redirect = $form.find ('input[name=redirect]').val ();
		
		function callback (result)
		{
			$phone.attr ('disabled', '');
			$sms_code.attr ('disabled', '');
			
			if (
				result && result.data && 
				(result.data.redirect || result.data.removeForm)
			)
			{
				// Удачная авторизация/регистрация
				result.data.removeForm = false;
				$('a.btn_green', $form).remove ();
			}
			else
			{
				$('div.loading', $form).hide ();
				Authorization_Phone_Sms_Send.onSmsCodeChange ($sms_session_code);
			}
		}
		
		Controller.call (
			'Authorization_Phone_Sms_Send/loginOrReg',
			{
				"phone": $phone.val (),
				"sms_session_id": $sms_session_id.val (),
				"sms_session_code": $sms_session_code.val (),
				"redirect": redirect ? redirect : location.href
			},
			callback, true
		);
		
		$form.find ('a.btn_green').css ({display:"none"});
		$form.find ('div.loading').show ();
		$phone.attr ('disabled', 'disabled');
		$sms_code.attr ('disabled', 'disabled');
	},
		
	/**
	 * @desc При изменении кода СМС
	 * @param jQuery $sms_code Элемент, где указывается код, пришедший по смс.
	 */
	onSmsCodeChange: function ($sms_code)
	{
		return ;
		var code = $sms_code.val ();
	
		var $form = $sms_code.closest ('form');
		
		$form.find ('a.btn_green,a.btn_gray').hide ();
		if (code.length > 4)
		{
			if (Authorization_Phone_Sms_Send._phoneRegistered)
			{
				$form.find ('a.btn_enter').show ();
			}
			else
			{
				$form.find ('a.btn_reg').show ();
			}
		}
		else
		{
			$form.find ('a.btn_gray').show ();
		}
	},
	
	/**
	 * @desc Отправка СМС с кодом подтверждения
	 * @param jQuery $form
	 */
	sendSmsCode: function ($form)
	{
		var phone = $form.find ('input[name=auth_login]').val ();
		
		$send_sms_button = $form.find ('.send_sms_button');
		$send_sms_button.hide ();
		
		/**
		 * @desc Ответ сервера
		 * @param object result
		 */
		function callback (result)
		{
			$form.find ('.send_sms_text').html (result.html);
			
			if (!result || !result.data || !result.data.activation_id)
			{
				return;
			}
			
			$send_sms_button.after (result.html);
			
			$form.find ('input[name=auth_activation_id]').val (result.data.activation_id);
			$code_input = $form.find ('input[name=auth_activation_code]');
			$code_input.val (result.data.code ? result.data.code : '');
			Controller_Authorization_Phone_Sms_Send.onSmsCodeChange ($code_input);
		}
		
		Controller.call (
			'Authorization_Phone_Sms_Send/sendSmsCode',
			{
				phone: phone
			},
			callback, true
		);
	}
	
};