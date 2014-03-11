<?php
/**
 * 
 * @desc Помощник для работы с телефонными номерами
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Phone
{
	
	/**
	 * @desc Длина номера мобильного телефона.
	 * @var integer
	 */
	public static $mobileLength = 11;
	
	/**
	 * @desc Возвращает номер мобильного телефона в формате "+7 123 456 78 90"
	 * @param string $phone 11 цифр номера
	 * @return string Отформатированный номер телефона.
	 */
	public static function formatMobile ($phone)
	{
		return 
			'+' .
			$phone [0] . ' ' . 
			substr ($phone, 1, 3) . ' ' .
			substr ($phone, 4, 3) . ' ' .
			substr ($phone, 7, 2) . ' ' .
			substr ($phone, 9, 2);
	}
	
	/**
	 * @desc Поиск в строке номера мобильного телефона
	 * @param string $str
	 * @tutorial
	 * 		parseMobile ("+7 123 456 78 90") = 71234567890
	 * 		parseMobile ("8-123(456)78 90") = 71234567890
	 * 		parseMobile ("61-61-61") = false
	 * @return string|false Номер телефона или false.
	 */
	public static function parseMobile ($str)
	{
		if (strlen ($str) < self::$mobileLength)
		{
			return false;
		}
		
		$i = 0;
		$c = $str [0];
		$result = "";
		
		if ($c == "+")
		{
			$i = 1;
		}
		else if ($c == "8")
		{
			// Россия, номер начинается с 8
			$i = 1;
			$result = "7";
		}
		
		$digits = "0123456789";
		$ignores = "-() +";
		for (; $i < strlen ($str); ++$i)
		{
			$c = $str [$i];
			if (strpos ($digits, $c) !== false)
			{
				$result .= $c;
			}
			else if (strpos ($ignores, $c) === false)
			{
				return false;
			}
		}
		
		return (strlen ($result) == self::$mobileLength) ? $result : false;
	}
	
}