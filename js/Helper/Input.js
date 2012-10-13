var Helper_Input = {
	
	/**
	 * Проверка введенных данных 
	 * @param $input
	 * 		Элемент
	 * @param error_class
	 * 		Класс, который будет присвоен элементу, если допущена ошибка.
	 * 		По умолчанию "err"
	 */
	check: function ($input, error_class)
	{
		if ($input.length == 0)
		{
			return true;
		}
		
		var result = true;
		
		if ($input.length > 1)
		{
			$input.each (function () {
				if (!Helper_Input.check ($(this)))
				{
					result = false;
				}
			});
			return result;
		}
		
		var empty_text = $input.attr ('placeholder');
		var value = $input.val ();
		if (empty_text && value == empty_text)
		{
			value = "";
		}
		
		// емейл
		if ($input.hasClass ('input_email'))
		{
			result = Helper_Input.checkEmail (value);
		}
		
		// обязательное поле
		if ($input.hasClass ('input_required'))
		{
			result = result && Helper_Input.checkRequired (value);
		}
		
		if ($input.hasClass ('input_phone'))
		{
			result = result && Helper_Phone.parseMobile (value); 
		}
		
		if (!error_class)
		{
			error_class = "err";
		}
		
		if (result)
		{
			$input.removeClass (error_class);
		}
		else
		{
			$input.addClass (error_class);
		}
		
		return result;
	},
	
	/**
	 * Проверка корректности электронного адреса.
	 * @param email
	 * @returns {Boolean}
	 */
	checkEmail: function (email)
	{
		if (email.length == 0)
		{
			return true;
		}
		
		var p_at = email.indexOf ('@');
		
		if (p_at < 0)
		{
			return false;
		}
		
		var p_dot = email.indexOf ('.', p_at);
		
		if (p_dot < 0 || p_dot == email.length - 1)
		{
			return false;
		}
		
		return true;
	},
	
	/**
	 * Проверка корректности телефонного номера.
	 * В номере должны быть только цифры и пробелы и длина отличной от 1.
	 * @param phone
	 * @returns {Boolean}
	 */
	checkPhone: function (phone)
	{
		return !(/[^0-9\s]/.test (phone)) && phone.length != 1;
	},
	
	/**
	 * Проверка обязательного поля.
	 * @param value
	 * @returns {Boolean}
	 */
	checkRequired: function (value)
	{
		return value.length > 0;
	}
	
};