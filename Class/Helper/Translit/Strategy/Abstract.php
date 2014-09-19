<?php

/**
 * Абстрактный класс стратегий транслита
 *
 * @author markov
 */
abstract class Helper_Translit_Strategy_Abstract extends Helper_Abstract
{
    /**
	 * Заменяет символы в строке согласно переданным наборам.
	 *
	 * @param string $value Исходая строка.
	 * @param string|array $to Символы, которые будут вставлены на места
	 * заменяемых.
	 * @param string $from [optional] Символы, которые будут заменены.
	 * Если этот аргумент не передан, в $to ожидается ассоциативный
	 * массив вида "заменяемый символ" => "символ для замены".
	 * @return string Результат замены
	 */
	protected function u_strtr($value, $to, $from = null)
	{
		if (is_null($from)) {
			arsort($to, SORT_LOCALE_STRING);
			foreach ($to as $c => $r) {
				$value = str_replace($c, $r, $value);
			}
		} else {
			$len = min(strlen($to), strlen($from));
			for ($i = 0; $i < $len; ++$i) {
				$value = str_replace(
					mb_substr($to, $i, 1, 'UTF-8'),
					mb_substr($from, $i, 1, 'UTF-8'),
					$value
				);
			}
		}
		return $value;
	}
    
    abstract public function translit($text, $lang = null);
    abstract public function makeUrlLink($text);
}