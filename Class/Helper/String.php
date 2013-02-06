<?php
/**
 *
 * @desc Помощник для работы со строками
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_String
{
	protected static $_utf8win1251 = array
    (
	    "\xD0\x90" => "\xC0", "\xD0\x91" => "\xC1", "\xD0\x92" => "\xC2", "\xD0\x93" => "\xC3", "\xD0\x94" => "\xC4",
	    "\xD0\x95" => "\xC5", "\xD0\x81" => "\xA8", "\xD0\x96" => "\xC6", "\xD0\x97" => "\xC7", "\xD0\x98" => "\xC8",
	    "\xD0\x99" => "\xC9", "\xD0\x9A" => "\xCA", "\xD0\x9B" => "\xCB", "\xD0\x9C" => "\xCC", "\xD0\x9D" => "\xCD",
	    "\xD0\x9E" => "\xCE", "\xD0\x9F" => "\xCF", "\xD0\xA0" => "\xD0", "\xD0\xA1" => "\xD1", "\xD0\xA2" => "\xD2",
	    "\xD0\xA3" => "\xD3", "\xD0\xA4" => "\xD4", "\xD0\xA5" => "\xD5", "\xD0\xA6" => "\xD6", "\xD0\xA7" => "\xD7",
	    "\xD0\xA8" => "\xD8", "\xD0\xA9" => "\xD9", "\xD0\xAA" => "\xDA", "\xD0\xAB" => "\xDB", "\xD0\xAC" => "\xDC",
	    "\xD0\xAD" => "\xDD", "\xD0\xAE" => "\xDE", "\xD0\xAF" => "\xDF", "\xD0\x87" => "\xAF", "\xD0\x86" => "\xB2",
	    "\xD0\x84" => "\xAA", "\xD0\x8E" => "\xA1", "\xD0\xB0" => "\xE0", "\xD0\xB1" => "\xE1", "\xD0\xB2" => "\xE2",
	    "\xD0\xB3" => "\xE3", "\xD0\xB4" => "\xE4", "\xD0\xB5" => "\xE5", "\xD1\x91" => "\xB8", "\xD0\xB6" => "\xE6",
	    "\xD0\xB7" => "\xE7", "\xD0\xB8" => "\xE8", "\xD0\xB9" => "\xE9", "\xD0\xBA" => "\xEA", "\xD0\xBB" => "\xEB",
	    "\xD0\xBC" => "\xEC", "\xD0\xBD" => "\xED", "\xD0\xBE" => "\xEE", "\xD0\xBF" => "\xEF", "\xD1\x80" => "\xF0",
	    "\xD1\x81" => "\xF1", "\xD1\x82" => "\xF2", "\xD1\x83" => "\xF3", "\xD1\x84" => "\xF4", "\xD1\x85" => "\xF5",
	    "\xD1\x86" => "\xF6", "\xD1\x87" => "\xF7", "\xD1\x88" => "\xF8", "\xD1\x89" => "\xF9", "\xD1\x8A" => "\xFA",
	    "\xD1\x8B" => "\xFB", "\xD1\x8C" => "\xFC", "\xD1\x8D" => "\xFD", "\xD1\x8E" => "\xFE", "\xD1\x8F" => "\xFF",
	    "\xD1\x96" => "\xB3", "\xD1\x97" => "\xBF", "\xD1\x94" => "\xBA", "\xD1\x9E" => "\xA2"
	);

	protected static $_win1251utf8 = array
    (
	    "\xC0" => "\xD0\x90", "\xC1" => "\xD0\x91", "\xC2" => "\xD0\x92", "\xC3" => "\xD0\x93", "\xC4" => "\xD0\x94",
	    "\xC5" => "\xD0\x95", "\xA8" => "\xD0\x81", "\xC6" => "\xD0\x96", "\xC7" => "\xD0\x97", "\xC8" => "\xD0\x98",
	    "\xC9" => "\xD0\x99", "\xCA" => "\xD0\x9A", "\xCB" => "\xD0\x9B", "\xCC" => "\xD0\x9C", "\xCD" => "\xD0\x9D",
	    "\xCE" => "\xD0\x9E", "\xCF" => "\xD0\x9F", "\xD0" => "\xD0\xA0", "\xD1" => "\xD0\xA1", "\xD2" => "\xD0\xA2",
	    "\xD3" => "\xD0\xA3", "\xD4" => "\xD0\xA4", "\xD5" => "\xD0\xA5", "\xD6" => "\xD0\xA6", "\xD7" => "\xD0\xA7",
	    "\xD8" => "\xD0\xA8", "\xD9" => "\xD0\xA9", "\xDA" => "\xD0\xAA", "\xDB" => "\xD0\xAB", "\xDC" => "\xD0\xAC",
	    "\xDD" => "\xD0\xAD", "\xDE" => "\xD0\xAE", "\xDF" => "\xD0\xAF", "\xAF" => "\xD0\x87", "\xB2" => "\xD0\x86",
	    "\xAA" => "\xD0\x84", "\xA1" => "\xD0\x8E", "\xE0" => "\xD0\xB0", "\xE1" => "\xD0\xB1", "\xE2" => "\xD0\xB2",
	    "\xE3" => "\xD0\xB3", "\xE4" => "\xD0\xB4", "\xE5" => "\xD0\xB5", "\xB8" => "\xD1\x91", "\xE6" => "\xD0\xB6",
	    "\xE7" => "\xD0\xB7", "\xE8" => "\xD0\xB8", "\xE9" => "\xD0\xB9", "\xEA" => "\xD0\xBA", "\xEB" => "\xD0\xBB",
	    "\xEC" => "\xD0\xBC", "\xED" => "\xD0\xBD", "\xEE" => "\xD0\xBE", "\xEF" => "\xD0\xBF", "\xF0" => "\xD1\x80",
	    "\xF1" => "\xD1\x81", "\xF2" => "\xD1\x82", "\xF3" => "\xD1\x83", "\xF4" => "\xD1\x84", "\xF5" => "\xD1\x85",
	    "\xF6" => "\xD1\x86", "\xF7" => "\xD1\x87", "\xF8" => "\xD1\x88", "\xF9" => "\xD1\x89", "\xFA" => "\xD1\x8A",
	    "\xFB" => "\xD1\x8B", "\xFC" => "\xD1\x8C", "\xFD" => "\xD1\x8D", "\xFE" => "\xD1\x8E", "\xFF" => "\xD1\x8F",
	    "\xB3" => "\xD1\x96", "\xBF" => "\xD1\x97", "\xBA" => "\xD1\x94", "\xA2" => "\xD1\x9E"
	);

	/**
	 * @desc Возвращает символы после первого вхождения $substr до конца строки.
	 * Если $substr не найден, возвращается вся строка целиком.
	 *
	 * @param string $str Исходная строка.
	 * @param string $substr Подстрока.
	 * @return string Подстрока, после вхождения $substr.
	 */
	public static function after ($str, $substr)
	{
		$p = strpos ($str, $substr);
		if ($p !== false)
		{
			return substr ($str, $p + strlen ($substr));
		}
		return $str;
	}

	/**
	 * @desc Переводи строку из неопределенной кодировки в заданную.
	 * @param string $str Исходная строка.
	 * @param string $output_charset Необходимая кодировка.
	 * @return string Строка в заданной кодировке.
	 */
	public static function autoIconv ($str, $output_charset = 'UTF-8')
	{
		// 'auto' расширяется в 'ASCII, JIS, UTF-8, EUC-JP, SJIS'
		$charset = mb_detect_encoding($str, 'auto');

		if ($charset == $output_charset)
		{
			return $str;
		}

		if (empty($charset))
		{
			// Неопределно - стопудово windows-1251
			return iconv ('windows-1251', $output_charset, $str);
		}

		return iconv ($charset, $output_charset, $str);
	}

	/**
	 * @desc Возвращает символы с начала строки до первого вхождения $substr.
	 * Если $substr не найден, возвращается вся строка целиком.
	 *
	 * @param string $str Исходная строка.
	 * @param string $substr Подстрока.
	 * @return string Подстрока, до вхождения $substr.
	 */
	public static function before ($str, $substr)
	{
		$p = strpos ($str, $substr);
		if ($p !== false)
		{
			return substr ($str, 0, $p);
		}
		return $str;
	}

	/**
	 * @desc Возвращает true, если строка $str начинается с $subStr.
	 * @param string $str Строка.
	 * @param string $substr Подстрока.
	 * @return boolean true, если строка $str начинается с $substr.
	 */
	public static function begin ($str, $substr)
	{
		return (strncmp ($str, $substr, strlen ($substr)) == 0);
	}

	/**
	 * @desc Возврщает подстроку между $start и $end
	 * @param string $string
	 * @param string $start
	 * @param string $end
	 * @return string
	 */
	public static function between ($string, $start, $end = null)
	{
		$ini = strpos ($string, $start);
		if ($ini === false)
		{
			return '';
		}
		$ini += strlen ($start);
		if ($end === null)
		{
			return substr ($string, $ini);
		}
		$len = strpos ($string, $end, $ini) - $ini;
		return substr ($string, $ini, $len);
	}

	/**
	 * Приводит строку к кодировке utf-8.
	 * Для конвертирования используется внешний модуль a.charset.php
	 *
	 * @param string $str
	 * 		Строка в windows-1251, koi-8 or utf-8
	 * @return string
	 * 		Строка в кодировке utf-8
	 */
	public static function charset_x_utf8 ($str)
	{
		Loader::requireOnce ('a.charset.php', 'includes');

		return iconv ('windows-1251', 'utf-8', charset_x_win ($str));
	}

	/**
	 * Приводит строку к кодировке windows-1251.
	 * Для конвертирования используется внешний модуль a.charset.php
	 *
	 * @param string $str
	 * 		Строка в windows-1251, koi-8 or utf-8
	 * @return string
	 * 		Строка в кодировке windows-1251
	 */
	public static function charset_x_windows1251 ($str)
	{
		Loader::requireOnce ('a.charset.php', 'includes');

		return charset_x_win ($str);
	}

	/**
	 * @desc Проверяет, что строка заканчивается на $substr
	 * @param string $str
	 * @param string $substr
	 * @return boolean
	 */
	public static function end ($str, $substr)
	{
		$len = strlen ($substr);

		if (strlen ($str) < $len)
		{
			return false;
		}

		return substr ($str, -$len) == $substr;
	}

	/**
	 * @desc Возвращает только цифры из строки
	 * @param string $str Исходная строка.
	 * @return string Строка, содержащая только чифры из исходной.
	 */
	public static function extractNums ($str)
	{
		$res = '';

		for ($i = 0; $i < strlen ($str); $i++)
		{
			if (is_numeric ($str[$i]))
			{
				$res .= $str[$i];
			}
		}

		return $res;
	}

	/**
	 * Расшифровка строки "1,2-5" в "1,2,3,4,5"
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function idsExtract ($str)
	{
		$ids = array ();

		$str = explode (',', $str);

		foreach ($str as $s)
		{
			$p = strpos ($s, '-');
			if ($p > 0)
			{
				$x1 = substr ($s, 0, $p);
				$x2 = substr ($s, $p + 1);
				for ($x = $x1; $x <= $x2; $x++)
				{
					$ids[] = (int) $x;
				}
			}
			else
			{
				$ids[] = (int) $s;
			}
		}

		return array_unique ($ids);
	}

	/**
	 * Ищет числовые значения с прификсами
	 *
	 * @param string $str
	 * 		Строка вида "a1b222ccc33"
	 * @return array
	 * 		Массив с найденными числами.
	 *      В качестве ключей используются префиксы, соответсвующие им.
	 * 		Если префикс встречается несколько раз, будет установлено последнее
	 * 		для него значение.
	 */
	public static function prefixedInts ($str)
	{
	    $last = strlen ($str) - 1;

	    $pref = '';    // Текущий префикс
	    $int = '';     // Текущее число

	    $result = array ();

	    for ($i = 0; $i <= $last; $i++)
	    {
	        if (ctype_digit ($str [$i]))
	        {
	            $int .= $str [$i];
	        }
	        elseif ($int !== '')
	        {
	            if ($pref !== '')
	            {
    	            $result [$pref] = $int;
	            }
	            $pref = $str [$i];
	            $int = '';
	        }
	        else
	        {
	            $pref .= $str [$i];
	        }
	    }

	    if ($pref !== '' && $int !== '')
	    {
	        $result [$pref] = $int;
	    }

	    return $result;
	}

	/**
	 * Получение превью для текста.
	 * @param string $text
	 * @param integer $length
	 * 		Ориентировочно ожидаемая длина превью.
	 * @return string
	 */
	public static function smartPreview ($text, $length = 150)
	{
		$text =  stripslashes ($text) . ' ';

		if (!isset ($text {$length}))
		{
			return $text;
		}

		$space_pos = strpos ($text, ' ', $length);
		if (!$space_pos) {
			$space_pos = $length;
		}
		$result = substr ($text, 0, $space_pos);
		return $result;
	}


	/**
	 * @desc Перевод строки в число.
	 * @param string $string Исходная строка.
	 * @param boolean $concat Склеивать фрагменты числа, если их несколько
	 * в строке (число разеделено пробелами и т.п.).
	 * @param integer $def Значение по умолчанию.
	 * @return integer Полученное число
	 */
	public static function str2int ($str, $concat = true, $def = 0)
	{
		if (empty ($str))
		{
			return $def;
		}
		else
		{
			$str = (string) $str;
		}

		$int = '';
		$concat_flag = true;
		for ($i = 0, $length = strlen ($str); $i < $length; ++$i)
		{
			if (is_numeric ($str [$i]) && $concat_flag)
			{
				$int .= $str [$i];
			}
			elseif (!$concat && strlen ($int) > 0)
			{
				if ($concat_flag)
				{
					$concat_flag = false;
				}
				else
				{
					return (int) $int;
				}
			}
		}

		if (is_numeric ($int))
		{
			if ($str [0] == '-')
			{
				return -(int) $int;
			}
			else
			{
				return (int) $int;
			}
		}
		else
		{
			return $def;
		}
	}

	/**
	 * Возвращает строку, усеченную до заданной длины с учетом кодировки.
	 * Гарантируется, что в конце строки не останется части мультибайтового символа.
	 * 10x to Drupal
	 *
	 * @param string $string
	 * 		Исходная строка
	 * @param integer $len
	 * 		Необходимая длина
	 * @param boolean $wordsafe
	 * 		Сохранение цельных слов. Если true, усечение произойдет по пробелу.
	 * @param boolean $dots
	 * 		Вставить многоточие в конец строки, если строка была усечена.
	 * @return string
	 * 		Усеченная строка.
	 */
	public static function truncateUtf8($string, $len, $wordsafe = false, $dots = false)
	{
		$slen = strlen ($string);

		if ($slen <= $len)
		{
			return $string;
		}

		if ($wordsafe)
		{
			$end = $len;
			while (($string[--$len] != ' ') && ($len > 0)) {};
			if ($len == 0)
			{
				$len = $end;
			}
		}
		//if ((ord($string[$len]) < 0x80) || (ord($string[$len]) >= 0xC0))
		//{
		//	return substr($string, 0, $len) . ($dots ? ' ...' : '');
		//}
		$p = 0;
		while ($len > 0 && $p < strlen ($string))
		{
			if (ord ($string[$p]) >= 0x80 && ord ($string[$p]) < 0xC0)
			{
				$p++;
			}
			$len--;
			$p++;
		};
		if (
			$p < strlen ($string) &&
			ord ($string[$p]) >= 0x80 && ord ($string[$p]) < 0xC0
		)
		{
			$p++;
		}

		return substr ($string, 0, $p) . ($dots ? ' ...' : '');
	}
	/**
	 *
	 * Extended stripslashes
	 * @param string|array $value
	 */
	private static function stripslashes_deep ($value)
	{
	    return is_array($value) ? array_map('Helper_String::stripslashes_deep', $value) : stripslashes($value);
	}
	/**
	 *
	 * Extended trim
	 * @param string|array $value
	 */
	private static function trim_deep ($value)
	{
	    return is_array($value) ? array_map('Helper_String::trim_deep', $value) : trim($value);
	}
	/**
	 *
	 * Extended htmlspecialchars
	 * @param string|array $value
	 */
	private static function htmlspecialchars_deep ($value)
	{
	    return is_array($value) ? array_map('Helper_String::htmlspecialchars_deep', $value) : htmlspecialchars($value);
	}

	/**
	 *
	 * Extended mysql_real_escape_string
	 * @param string|array $value
	 */
	private static function mysql_real_escape_string_deep ($value)
	{
	    return is_array($value) ? array_map('Helper_String::mysql_real_escape_string_deep', $value) : mysql_real_escape_string($value);
	}

	/**
	 *
	 * @param string|array $var
	 */
	public static function secure($var)
	{
		$result = self::trim_deep($var);
	    $result = self::stripslashes_deep($result);
	    $result = self::htmlspecialchars_deep($result);
	    $result = self::mysql_real_escape_string_deep($result);
		return $result;
	}

	/**
	 * Преобразовать первую букву к верхнему регистру (для UTF-8)
	 *
	 * @param string $str
	 * @return string
	 */
	public static function ucfirst($str)
	{
		return mb_strtoupper(
			mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8'
		) . mb_substr($str, 1, mb_strlen($str, 'UTF-8') - 1, 'UTF-8');
	}

	public static function lcfirst($str)
	{
		return mb_strtolower(
			mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8'
		) . mb_substr($str, 1, mb_strlen($str, 'UTF-8') - 1, 'UTF-8');
	}

	/**
	 *
	 * @desc utf8 -> win1251
	 * @param string|array $var
	 */
	public static function utf8_win1251 ($var)
	{
	    if (is_array($var))
	    {
	        foreach ($var as $key => $value)
	        {
	            if (is_array($value))
	            {
	                $var[$key] = self::utf8_win1251($value);
	            }
	            else
	            {
	                $var[$key] = strtr($value, self::$_utf8win1251);
	            }
	        }
	        return $var;
	    }
	    else
	    {
	        return strtr($var, self::$_utf8win1251);
	    }
	}

	/**
	 *
	 * @desc win1251 -> utf8
	 * @param string|array $var
	 * @return utf-8
	 */
	public static function win1251_utf8 ($var)
	{
	    if (is_array($var))
	    {
	        foreach ($var as $key => $value)
	        {
	            if (is_array($value))
	            {
	                $var[$key] = self::utf8_win1251($value);
	            }
	            else
	            {
	                $var[$key] = strtr($value, self::$_win1251utf8);
	            }
	        }
	        return $var;
	    }
	    else
	    {
	        return strtr($var, self::$_win1251utf8);
	    }
	}
        
        /**
         * @desc Удаление из строки спец. символы
         * @param string $value Исходна стока
         * @return Результат очистки.
         */
        public static function replaceSpecialChars($value)
        {
            $value = str_replace(
                    array("\r", "\n", "\t", ',', '(', ')', '[', ']', '{', '}'), '', $value
            );
            return $value;
        }
}


/**
 * Для PHP < 5.3
 */
if (!function_exists ('lcfirst'))
{
	function lcfirst ($str)
	{
		if (empty ($str))
		{
			return $str;
		}

		return strtolower (substr ($str, 0, 1)) . substr ($str, 1);
	}
}
