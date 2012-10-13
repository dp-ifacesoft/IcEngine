/**
 * @desc Помощник для работы с телефонными номерами
 */
var Helper_Phone = {
	
	/**
	 * @desc Длина номера мобильного телефона.
	 * @var integer
	 */
	mobileLength: 11,
		
	/**
	 * @desc Разбор номера мобильного
	 * @param string str Исходная строка
	 * @tutorial
	 * 		parseMobile ("+7 123 456 78 90") = 71234567890
	 * 		parseMobile ("8-123(456)78 90") = 71234567890
	 * @returns string|false Телефонный номер (11 цифр без "+") или false.
	 */
	parseMobile: function (str)
	{
		if (str.length < Helper_Phone.mobileLength)
		{
			return false;
		}
		
		var i = 0;
		var c = str [0];
		var result = "";
		
		if (c == "+")
		{
			i = 1;
		}
		else if (c == "8")
		{
			i = 1;
			result = "7";
		}
		
		var digits = "0123456789";
		var ignores = "-() +";
		for (; i < str.length; ++i)
		{
			var c = str.charAt (i);
			if (digits.indexOf (c) >= 0)
			{
				result += "" + c;
			}
			else if (ignores.indexOf (c) < 0)
			{
				return false;
			}
		}
		
		return (result.length == Helper_Phone.mobileLength) ? result : false;
	}
		
};