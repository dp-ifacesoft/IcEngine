/**
 * 
 * @desc Контроллер авторизации
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
var Controller_Authorization = {
	
	/**
	 * @desc Определить тип авторизации по логину
	 * @param jQuery $form
	 */
	determine: function ($form)
	{
		var login = $form.find ('input[name=auth_login]').val ();
		var $part_determined = $form.find ('.auth_part_determined');
		var $part_default = $form.find ('.auth_part_default');
		
		if (
			!Helper_Phone.parseMobile (login) &&
			!Helper_Email.parseEmail (login)
		)
		{
			$part_determined.hide ();
			$part_default.show ();
			return ;
		}
		
		if (login == $form.data ('last_login'))
		{
			return ;
		}
		
		$form.data ('last_login', login);
		
		var auth_data = Helper_Form.asArray ($form, 'input[name^=auth_]');
		
		function callback (result)
		{
			if (login != $form.data ('last_login'))
			{
				// данные уже изменены
				return ;
			}
			
			$form.data ('auth_type', result.data.auth_type);
			
			if (result.data.auth_type)
			{
				$part_default.hide ();
				$part_determined.html (result.html);
				$part_determined.show ();
			}
			else
			{
				$part_determined.hide ();
				$part_default.show ();
			}
			
			$form.find ('.submit').click (function () {
				Controller_Authorization.submit ($(this).closest ('form'));
			});
		}
		
		Controller.call (
			'Authorization/determine',
			auth_data,
			callback, true
		);
	},
	
	/**
	 * @desc Инициализация инпута для логина
	 * @param jQuery $input
	 */
	initLoginInput: function ($input)
	{
		$input.change (function () {
			Controller_Authorization.determine ($(this).closest ('form'));
		});
		$input.keypress (function () {
			Controller_Authorization.determine ($(this).closest ('form'));
		});
		$input.keyup (function () {
			Controller_Authorization.determine ($(this).closest ('form'));
		});
	},
	
	/**
	 * @desc Авторизация
     * @param $form
	 */
	submit: function ($form)
	{
		var auth_type = $form.data ('auth_type');
		
		if (!auth_type)
		{
			return ;
		}
		
		function callback (result)
		{
			Helper_Form.defaultCallback ($form, result);
		}
		
		var auth_data = Helper_Form.asArray ($form, 'input[name^=auth_]');
		auth_data ['type'] = auth_type;
		auth_data ['redirect'] = window.location.href;
		
		Controller.call (
			'Authorization/submit',
			auth_data,
			callback, true
		);
	}
		
};