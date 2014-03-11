<?php
/**
 *
 * @desc Хелпер для работы с датами.
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Date
{

	/**
	 * @desc Нулевая дата
	 * @var string
	 */
	const NULL_DATE = '0000-00-00';

	/**
	 * @desc Unix формат представления даты.
	 * @var string
	 */
    const UNIX_DATE_FORMAT = 'Y-m-d';

    /**
     * @desc Unix формат представления даты и времени.
     * @var string
     */
    const UNIX_FORMAT = 'Y-m-d H:i:s';

    /**
     * @desc Unix формат времени
     * @var string
     */
    const UNIX_TIME_FORMAT = 'H:i:s';

    /**
     * @desc Названия дней недели
     * @var array
     */
    public static $daysRu = array (
        1 => array (
            0 => 'воскресенье',
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среда',
            4 => 'четверг',
            5 => 'пятница',
            6 => 'суббота',
            7 => 'воскресенье'
        ),
        'Short' => array (
            0 => 'Вс',
            1 => 'Пн',
            2 => 'Вт',
            3 => 'Ср',
            4 => 'Чт',
            5 => 'Пт',
            6 => 'Сб',
            7 => 'Вс'
        )
    );

    /**
     * @desc Русские названия месяцев.
     * @var array
     */
    public static $monthesRu = array (
		1 => array (
			1 => 'январь',
			2 => 'февраль',
			3 => 'март',
			4 => 'апрель',
			5 => 'май',
			6 => 'июнь',
			7 => 'июль',
			8 => 'август',
			9 => 'сентябрь',
			10 => 'октябрь',
			11 => 'ноябрь',
			12 => 'декабрь'
		),
		2 => array(
			1 => 'января',
			2 => 'февраля',
			3 => 'марта',
			4 => 'апреля',
			5 => 'мая',
			6 => 'июня',
			7 => 'июля',
			8 => 'августа',
			9 => 'сентября',
			10 => 'октября',
			11 => 'ноября',
			12 => 'декабря'
		)
	);

	/**
	 * @desc Сравнение двух дат в формате UNIX.
	 * @param string $now
	 * @param string $then
	 * @return integer
	 * 		-1 если $now < $then
	 * 		 0 если $now == $then
	 * 		+1 если $now > $then
	 */
	public static function cmpUnix ($now, $then)
	{
		return strcmp ($now, $then); // :D
	}

    /**
     * @desc Получение даты по номеру недели в году
     * @param integer $week_number Номер недели в году в формате ISO-8601.
     * @param integer $year Четырехзначный номер года.
     * Если параметр не указан, будет взят номер текущего года.
     * @return integer|false unix timestamp
     */
    public static function dateByWeek ($week, $year = null)
    {
        $year = $year ? $year : date ('Y');
        $week = sprintf ('%02d', $week);
        return strtotime ($year . 'W' . $week . '1 00:00:00');
    }

    /**
     * @desc Количество дней в месяце.
     * @param integer $month Месяц (от 1 до 12)
     * @param integer $year [optional] Год (от 1901 до 2099)
     * @return integer Номер последнего дня в месяце (от 28 до 31)
     */
    public static function daysInMonth ($month, $year = null)
    {
    	if (!$year)
    	{
    		$year = date ('Y');
    	}
    	return 31 - (($month - 1) % 7 % 2) - (($month == 2) << !!($year % 4));
    }

    /**
     * format = 1 : 15 мая 2012 года 10:40
     *
     * @param type $string
     * @param type $showYear
     * @param type $format
     * @return type
     */
	public static function datetime ($string, $showYear = false, $format = 0,
		$showTime = false)
	{
		$year = strtok($string, "-");
		$month = strtok("-");
		$day = strtok("-");
		switch ($month)
		{
			case "1": $month = "января";
				break;
			case "2": $month = "февраля";
				break;
			case "3": $month = "марта";
				break;
			case "4": $month = "апреля";
				break;
			case "5": $month = "мая";
				break;
			case "6": $month = "июня";
				break;
			case "7": $month = "июля";
				break;
			case "8": $month = "августа";
				break;
			case "9": $month = "сентября";
				break;
			case "10": $month = "октября";
				break;
			case "11": $month = "ноября";
				break;
			case "12": $month = "декабря";
				break;
		}
		$_date = explode(' ', $string);
		$hour = strtok($_date[1], ':');
		$minute = strtok(':');
        if(!$format){
            return intval($day) . "&nbsp;" . $month .
				(($year != date("Y") || $showYear) ? ("&nbsp;" . $year . "&nbsp;г.") : "") .
			($showTime ? "&nbsp;" . $hour . ':' . $minute : '');
        }elseif ($format == 1) {
            $return = intval($day) . "&nbsp;" . $month . (($year != date("Y") || $showYear) ? ("&nbsp;" . $year . "&nbsp;года") : "");
            $return .= ' ' . $hour . ':' . $minute;
            return $return;
        }
	}

	/**
	 * @desc Возвращает номер дня от начала эры.
	 * @param integer $date Дата.
	 * @return integer Номер дня.
	 */
	public static function eraDayNum ($date = null)
	{
		if ($date === null)
		{
			$date = time ();
		}

		$d = date ('d', $date);
		$m = date ('m', $date);
		$y = date ('Y', $date);

		if ($m > 2)
		{
			$m++;
		}
		else
		{
		    $m += 13;
		    $y--;
		}
		return (int) (365.25 * $y + 30.6 * $m + $d);
	}

	/**
	 * @desc Возвращает номер часа от начала эры
	 * @param integer $date Дата.
	 * @return intger Номер недели.
	 */
	public static function eraHourNum($date = false)
	{
		if ($date === null) {
			$date = time();
		}

		return self::eraDayNum($date) * 24 + (int) date('H', $date);
	}

	/**
	 * @desc Возвращает номер минуты от начала эры
	 * @param integer $date Дата.
	 * @return intger Номер недели.
	 */
	public static function eraMinNum($delta = 1, $date = null)
	{
		if ($date === null) {
			$date = time();
		}
        $parts = 60 / $delta;
        $part = 1;
        for ($i = $parts; $i >= 1; $i--) {
            if ((int) date('i', $date) > ($i - 1) * $delta) {
                $part = $i;
                break;
            }
        }
		return ((self::eraDayNum ($date) * 24 + (int) date('H', $date)) * 60 +
			$part * $delta);
	}

	/**
	 * @desc Возвращает номер недели от начала эры
	 * @param integer $date Дата.
	 * @return intger Номер недели.
	 */
	public static function eraWeekNum ($date = false)
	{
		return (int) (self::eraDayNum ($date) / 7);
	}

	/**
	 * @desc Получение времени с точностью до микросекунд
	 * @return float
	 */
	public static function getmicrotime ()
	{
		$usec = $sec = '';
		list ($usec, $sec) = explode (" ", microtime ());
		$usec = substr ($usec, 0, 6);
		return (float) ((float) $usec + $sec);
	}

	/**
	 * @desc Возвращает максимальную из дат
	 * @param DateTime $a
	 * @param DateTime $b
	 * @return DateTime
	 */
	public static function max ($a, $b)
	{
		if (!($a instanceof DateTime))
		{
			if ($a == null)
			{
				return $b;
			}
			$a = self::parseDateTime ($a);
		}

		if (!($b instanceof DateTime))
		{
			if ($b == null)
			{
				return $a;
			}
			$b = self::parseDateTime ($b);
		}

		$diff = $a->diff ($b);

		return $diff->invert ? $a : $b;
	}

	/**
	 * @desc Возвращает минимальную из дат
	 * @param DateTime $a
	 * @param DateTime $b
	 * @return DateTime
	 */
	public static function min ($a, $b)
	{
		if (!($a instanceof DateTime))
		{
			if ($a == null)
			{
				return $b;
			}
			$a = self::parseDateTime ($a);
		}

		if (!($b instanceof DateTime))
		{
			if ($b == null)
			{
				return $a;
			}
			$b = self::parseDateTime ($b);
		}

		$diff = $a->diff ($b);

		return $diff->invert ? $b : $a;
	}

	/**
	 * @desc Сравнение месяцев двух дат.
	 * @param integer $date1 Первая дата.
	 * @param integer $date2 Вторая дата.
	 * @return boolean true, если даты относятся к одному месяцу, иначе false.
	 */
	public static function monthEqual ($date1, $date2)
	{
		return date ('m', $date1) == date ('m', $date2);
	}

	/**
	 * @desc Возвращает название месяца.
	 * @param integer $month_num Номер месяца (от 1 до 12).
	 * @param integer $form Возвращаемая форма. 1 - именительный патеж,
	 * 2 - родительный.
	 * @return string Название месяца.
	 */
	public static function monthName ($month_num, $form = 1)
	{
		return self::$monthesRu [$form][(int) $month_num];
	}

	/**
	 * @desc Возвращает следующее время согласно периоду
	 * @param integer $time Исходная метка времени (unix timestamp)
	 * @param string $period Период.
	 * Задается либо в секундах, либо строкой.
	 * @return integer Следующая метка времени (unix timestamp).
	 * Если период указан неверно, возвращается false
	 */
	public static function nextTime ($time, $period)
	{
		if (is_numeric ($period))
		{
			return $time + $period;
		}
		else
		{
			switch ($period)
			{
				case 'second';
				case '1 second':
					return $time + 1;
				break;
				case 'minute';
				case '1 minute':
					return $time + 60;
				break;
				case 'hour';
				case '1 hour':
					return $time + 60 * 60;
				break;
				case '4 hour':
					return $time + 60 * 60 * 4;
				break;
				case 'day':
					return $time + 60 * 60 * 24;
				break;
				case 'week':
					return $time + 60 * 60 * 24 * 7;
				break;
				case 'month':
					return strtotime ('+1 month', $time);
				break;
			}
		}

		return false;
	}

	/**
	 * @desc Получение даты и времени из строки.
	 * @param mixed $date
	 * @return DateTime|null
	 */
	public static function parseDateTime ($str)
	{
		if (is_numeric ($str))
		{
			$dt = new DateTime ('@' . $str);
			$dt->setTimezone (new DateTimeZone (date_default_timezone_get ()));
			return $dt;
		}

		if (strlen ($str) < 8)
		{
			return null;
		}
		$n = 0;

		$arr = array_fill (0, 6, '');

		for ($i = 0; $i < strlen ($str); ++$i)
		{
			if (strpos ('-0123456789', $str [$i]) == 0)
			{
				if (strlen ($arr [$n]) > 0)
				{
					$arr [$n] = (int) $arr [$n];
					++$n;
				}
			}
			else
			{
				$arr [$n] .= $str [$i];
			}
		}

		for ($i = $n; $i <= 5; ++$i)
		{
			$arr [$i] = (int) $arr [$i];
		}

		$str = implode ('.', $arr);

		if (strlen ($arr [0]) == 4)
		{
			// Y-m-d H:i:s
			return DateTime::createFromFormat ('Y.m.d.H.i.s', $str);
		}
		elseif (strlen ($arr [2]) == 4)
		{
			// d.m.Y H:i:s
			return DateTime::createFromFormat ('d.m.Y.H.i.s', $str);
		}
		elseif (strlen ($arr [3]) == 4)
		{
			// H:i:s Y-m-d
			return DateTime::createFromFormat ('H.i.s.Y.m.d', $str);
		}
		elseif (strlen ($arr [5]) == 4)
		{
			// H:i:s d.m.Y
			return DateTime::createFromFormat ('H.i.s.d.m.Y', $str);
		}

		return null;
	}

	/**
	 * @desc Разница между датами в секундах.
	 * @param string $now
	 * @param string $then
	 */
	public static function secondsBetween ($now, $then = null)
	{
		return
			self::strToTimestamp ($then, time ()) -
			self::strToTimestamp ($now, 0);
	}

	/**
	 * @desc Получение даты и времени из строки.
	 * В качестве исходной строки может выступать запись
	 * даты и времени практически в любом формате, не зависимо от разделителя
	 * и порядка данных.
	 * @param string|integer $str Строка с датой или unix timestamp.
	 * @param integer $default Возвращаемое значение по умолчанию.
	 * @return integer Опередленная дата или $def, если дату определить
	 * не удалось.
	 */
	public static function strToTimestamp($str, $default = 0)
	{
		if (is_numeric ($str))
		{
			return (int) $str;
		}
		if (strlen ($str) < 8)
		{
			return $default;
		}
		$n = 0;

		$arr = array (
			'', '', '',
			'', '', ''
		);

		for ($i = 0; $i < strlen ($str); ++$i)
		{
			if (strpos ('-0123456789', $str [$i]) == 0)
			{
				if (strlen ($arr [$n]) > 0)
				{
					$arr [$n] = (int) $arr [$n];
					++$n;
				}
			}
			else
			{
				$arr [$n] .= $str [$i];
			}
		}

		for ($i = $n; $i <= 5; ++$i)
		{
			$arr [$i] = (int) $arr [$i];
		}

		if (strlen ($arr[0]) == 4)
		{
			// Y-m-d H:i:s
			return mktime ($arr[3], $arr[4], $arr[5], $arr[1], $arr[2], min(2040, $arr[0]));
		}
		elseif (strlen ($arr[2]) == 4)
		{
			// d.m.Y H:i:s
			return mktime ($arr[3], $arr[4], $arr[5], $arr[1], $arr[0], min(2040, $arr[2]));
		}
		elseif (strlen ($arr[3]) == 4)
		{
			// H:i:s Y-m-d
			return mktime ($arr[0], $arr[1], $arr[2], $arr[4], $arr[5], min(2040, $arr[3]));
		}
		elseif (strlen ($arr[5]) == 4)
		{
			// H:i:s d.m.Y
			return mktime ($arr[0], $arr[1], $arr[2], $arr[4], $arr[3], min(2040, $arr[5]));
		}

		return $default;
	} # function str_to_timestamp

	/**
	 * @desc Перевод строки в unix timestamp
	 * @param string $str Исходная строка.
	 * @param integer $def Возвращаемое значение по умолчанию.
	 * @return integer Время в unix timestamp.
	 * Если не удалось определить время, возвращается $def
	 */
	public static function strToTimeDef ($str, $def = 0)
	{
		if (is_numeric ($str))
		{
			return $str;
		}
		if (strlen ($str) < 3)
		{
			return $def;
		}

		$n = 0;

		$arr = array ('', '', '');
		for ($i = 0; $i < strlen ($str); ++$i)
		{
			if (strpos ('-0123456789', $str [$i]) == 0)
			{
				if (strlen ($arr [$n]) > 0)
				{
					$n++;
				}
			}
			else
			{
				$arr [$n] .= $str [$i];
			}
		}

		return mktime ((int) $arr [0], (int) $arr [1], (int) $arr [2]);
	}

	/**
	 * @desc Преобразует дату в "24 февраля 2010" (?) года.
	 * Без года, если дата соответсвует текущему году.
	 * @param string $date
	 * @return string
	 */
	public static function toCasualDate ($date)
	{
		$date = date ('Y-m-d', strtotime ($date));

		if ($date >= 0)
		{
			list (
				$year,
				$month,
				$day
			) = explode ('-', $date);

			$currentYear = date ('Y');

			$result =
				(int) $day .
				'&nbsp' .
				self::$monthesRu [2][(int) $month] .
				($year != $currentYear ? ' ' . $year : '');

			return $result;

		}
	}

	/**
	 * @descs Разбирает дату и возвращает DateTime.
	 * @param string $date Дата в формате UNIX.
	 * @return DateTime
	 */
	public static function toDateTime ($date)
	{
		return DateTime::createFromFormat (self::UNIX_FORMAT, $date);
	}

	/**
	 * @desc Перевод даты из любого распознаваемого форматав формат в Unix.
	 * @param string $date [optional] Если параметр не будет передан или будет
	 * передано null, будет использована текущая дата.
	 * @return string Дата в формате UNIX "YYYY-MM-DD HH:II:SS"
	 */
	public static function toUnix ($date = null)
	{
		if (!$date)
		{
			return date (self::UNIX_FORMAT);
		}
		$date = self::parseDateTime ($date);
		if (!$date) {
			return null;
		}
		return $date->format (self::UNIX_FORMAT);
	}

}