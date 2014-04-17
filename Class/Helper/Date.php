<?php

/**
 * Хелпер для работы с датами.
 * 
 * @author goorus, morph
 * @Service("helperDate")
 */
class Helper_Date
{
	/**
	 * Нулевая дата
	 * 
     * @var string
	 */
	const NULL_DATE = '0000-00-00';

	/**
	 * Unix формат представления даты.
	 * 
     * @var string
	 */
    const UNIX_DATE_FORMAT = 'Y-m-d';

    /**
     * Unix формат представления даты и времени.
     * 
     * @var string
     */
    const UNIX_FORMAT = 'Y-m-d H:i:s';

    /**
     * Unix формат времени
     * 
     * @var string
     */
    const UNIX_TIME_FORMAT = 'H:i:s';

    /**
     * Названия дней недели
     * 
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
     * Русские названия месяцев.
     * 
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
	 * Сравнение двух дат в формате UNIX.
	 * 
     * @param string $now
	 * @param string $then
	 * @return integer
	 * 		-1 если $now < $then
	 * 		 0 если $now == $then
	 * 		+1 если $now > $then
	 */
	public function cmpUnix($now, $then)
	{
		return strcmp($now, $then); // :D
	}

    /**
     * Получение даты по номеру недели в году
     * 
     * @param integer $week_number Номер недели в году в формате ISO-8601.
     * @param integer $year Четырехзначный номер года.
     * Если параметр не указан, будет взят номер текущего года.
     * @return integer|false unix timestamp
     */
    public function dateByWeek ($week, $year = null)
    {
        $year = $year ? $year : date('Y');
        $week = sprintf('%02d', $week);
        return strtotime($year . 'W' . $week . '1 00:00:00');
    }

    /**
     * Количество дней в месяце.
     * 
     * @param integer $month Месяц (от 1 до 12)
     * @param integer $year [optional] Год (от 1901 до 2099)
     * @return integer Номер последнего дня в месяце (от 28 до 31)
     */
    public static function daysInMonth($month, $year = null)
    {
    	if (!$year) {
    		$year = date('Y');
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
	public function datetime($string, $showYear = false, $format = 0,
		$showTime = false)
	{
		$year = strtok($string, "-");
        $month = self::$monthesRu[2][(int) strtok("-")];
		$day = strtok("-");
		$tmpDate = explode(' ', $string);
        $hour = 0;
        $minute = 0;
        if (isset($tmpDate[1])) {
            $hour = strtok($tmpDate[1], ':');
            $minute = strtok(':');
        }
        if (!$format) {
            return intval($day) . "&nbsp;" . $month .
				(($year != date("Y") || $showYear) 
                    ? ("&nbsp;" . $year . "&nbsp;г.") : "") .
                    ($showTime ? "&nbsp;" . $hour . ':' . $minute : '');
        } elseif ($format == 1) {
            $return = intval($day) . "&nbsp;" . $month . 
                (($year != date("Y") || $showYear) 
                ? ("&nbsp;" . $year . "&nbsp;года") : "");
            $return .= ' ' . $hour . ':' . $minute;
            return $return;
        }
	}
    
    /**
     * Возвращает дату в сокращенном формате
     */
    public function dateTimeMin($date)
    {
        static $months = array(
			1 => 'янв.',
			2 => 'фев.',
			3 => 'мар.',
			4 => 'апр.',
			5 => 'мая',
			6 => 'июн.',
			7 => 'июл.',
			8 => 'авг.',
			9 => 'сен.',
			10 => 'окт.',
			11 => 'ноя.',
			12 => 'дек.'
		);
		if (!$date) {
			$date = new DateTime();
			$m = $date->format('m');
			$y =  $date->format('Y');
            $d = $date->format('d');
		} else {
			$date = explode('-', $date);
			$y = (int) $date[0];
			$m = (int) $date[1];
            $d = (int) $date[2];
		}
		return $d . ' ' . $months[$m] . ' ' . $y;
    }

	/**
	 * Возвращает номер дня от начала эры.
	 * 
     * @param integer $date Дата.
	 * @return integer Номер дня.
	 */
	public function eraDayNum($date = null)
	{
		if ($date === null) {
			$date = time();
		}
		$d = date('d', $date);
		$m = date('m', $date);
		$y = date('Y', $date);
		if ($m > 2) {
			$m++;
		} else {
		    $m += 13;
		    $y--;
		}
		return (int) (365.25 * $y + 30.6 * $m + $d);
	}

	/**
	 * Возвращает номер часа от начала эры
	 * 
     * @param integer $date Дата.
	 * @return intger Номер недели.
	 */
	public function eraHourNum($date = false)
	{
		if ($date === null) {
			$date = time();
		}
		return $this->eraDayNum($date) * 24 + (int) date('H', $date);
	}

	/**
	 * Возвращает номер минуты от начала эры
	 * 
     * @param integer $date Дата.
	 * @return intger Номер недели.
	 */
	public function eraMinNum($delta, $date = false)
	{
		if ($date === null) {
			$date = time();
		}
		return (($this->eraDayNum($date) * 24 + (int) date('H', $date)) * 60 +
			(int) date('i', $date)) * ((int) (60 / $delta));
	}

	/**
	 * Возвращает номер недели от начала эры
	 * 
     * @param integer $date Дата.
	 * @return intger Номер недели.
	 */
	public function eraWeekNum($date = false)
	{
		return (int) ($this->eraDayNum($date) / 7);
	}

    /**
	 * Получить дату в формате "Месяц YYYY"
     * 
	 * @param string $date дата
	 * @return string  месяц и год
	 */
	public static function monthAndYear($date = null) 
    {
		static $months = array(
			1 => 'Январь',
			2 => 'Февраль',
			3 => 'Март',
			4 => 'Апрель',
			5 => 'Май',
			6 => 'Июнь',
			7 => 'Июль',
			8 => 'Август',
			9 => 'Сентябрь',
			10 => 'Октябрь',
			11 => 'Ноябрь',
			12 => 'Декабрь'
		);
		if (!$date) {
			$date = new DateTime();
			$m = $date->format('m');
			$y =  $date->format('Y');
		} else {
			$date = explode('-', $date);
			$y = (int) $date[0];
			$m = (int) $date[1];
		}
		return $months[$m] . ' ' . $y;
	}
    
	/**
	 * Возвращает название месяца.
	 * 
     * @param integer $monthNum Номер месяца (от 1 до 12).
	 * @param integer $form Возвращаемая форма. 1 - именительный патеж,
	 * 2 - родительный.
	 * @return string Название месяца.
	 */
	public function monthName($monthNum, $form = 1)
	{
		return self::$monthesR[$form][(int) $monthNum];
	}

	/**
	 * Получение даты и времени из строки.
     *
	 * @param mixed $date
	 * @return DateTime|null
	 */
	public function parseDateTime($str)
	{
		if (is_numeric($str)) {
			$dt = new DateTime('@' . $str);
			$dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
			return $dt;
		}
		if (strlen($str) < 8) {
			return null;
		}
		$n = 0;
		$arr = array_fill(0, 6, '');
		for ($i = 0; $i < strlen($str); ++$i) {
			if (strpos('-0123456789', $str[$i]) == 0) {
				if (strlen($arr[$n]) > 0) {
					$arr[$n] = (int) $arr[$n];
					++$n;
				}
			} else {
				$arr[$n] .= $str[$i];
			}
		}
		for ($i = $n; $i <= 5; ++$i) {
			$arr[$i] = (int) $arr[$i];
		}
		$str = implode('.', $arr);
		if (strlen ($arr [0]) == 4) {
			// Y-m-d H:i:s
			return DateTime::createFromFormat('Y.m.d.H.i.s', $str);
		} elseif (strlen($arr[2]) == 4) {
			// d.m.Y H:i:s
			return DateTime::createFromFormat('d.m.Y.H.i.s', $str);
		} elseif (strlen($arr [3]) == 4) {
			// H:i:s Y-m-d
			return DateTime::createFromFormat('H.i.s.Y.m.d', $str);
		}
		elseif (strlen($arr[5]) == 4) {
			// H:i:s d.m.Y
			return DateTime::createFromFormat('H.i.s.d.m.Y', $str);
		}
		return null;
	}
    
    /**
	 * Получение даты и времени из строки.
	 * В качестве исходной строки может выступать запись
	 * даты и времени практически в любом формате, не зависимо от разделителя
	 * и порядка данных.
	 * 
     * @param string|integer $str Строка с датой или unix timestamp.
	 * @param integer $default Возвращаемое значение по умолчанию.
	 * @return integer Опередленная дата или $def, если дату определить
	 * не удалось.
	 */
	public function strToTimestamp($str, $default = 0)
	{
		if (is_numeric($str)) {
			return (int) $str;
		}
		if (strlen($str) < 8) {
			return $default;
		}
		$n = 0;
		$arr = array('', '', '', '', '', '');
		for ($i = 0; $i < strlen($str); ++$i) {
			if (strpos('-0123456789', $str[$i]) == 0) {
				if (strlen($arr[$n]) > 0) {
					$arr[$n] = (int) $arr[$n];
					++$n;
				}
			} else {
				$arr[$n] .= $str[$i];
			}
		}
		for ($i = $n; $i <= 5; ++$i) {
			$arr[$i] = (int) $arr[$i];
		}
		if (strlen($arr[0]) == 4) {
			// Y-m-d H:i:s
			return mktime(
                $arr[3], $arr[4], $arr[5], $arr[1], $arr[2], min(2040, $arr[0])
            );
		} elseif (strlen($arr[2]) == 4) {
			// d.m.Y H:i:s
			return mktime(
                $arr[3], $arr[4], $arr[5], $arr[1], $arr[0], min(2040, $arr[2])
            );
		} elseif (strlen($arr[3]) == 4) {
			// H:i:s Y-m-d
			return mktime(
                $arr[0], $arr[1], $arr[2], $arr[4], $arr[5], min(2040, $arr[3])
            );
		} elseif (strlen($arr[5]) == 4) {
			// H:i:s d.m.Y
			return mktime(
                $arr[0], $arr[1], $arr[2], $arr[4], $arr[3], min(2040, $arr[5])
            );
		}
		return $default;
	}
    
	/**
	 * Перевод даты из любого распознаваемого форматав формат в Unix.
     *
	 * @param string $date [optional] Если параметр не будет передан или будет
	 * передано null, будет использована текущая дата.
	 * @return string Дата в формате UNIX "YYYY-MM-DD HH:II:SS"
	 */
	public function toUnix($date = null)
	{
		if (!$date) {
			return date(self::UNIX_FORMAT);
		}
		$date = $this->parseDateTime($date);
		if (!$date) {
			return null;
		}
		return $date->format(self::UNIX_FORMAT);
	}
}