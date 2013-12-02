<?php
/**
 * @Service("helperActivation")
 * @desc Помощник активации.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Activation
{

	/**
	 * @desc Генерация циферного кода для активации.
	 * Генерируется случайное число длинной от $from до $to,
	 * поэтому чаще будут генерироваться числа с максимальным количеством
	 * знаков.
	 * @param integer $from Минимальное количество цифр.
	 * @param integer $to Максимальное количество цифр.
	 */
	public function generateNumeric ($from = 5, $to = 7)
	{
		return rand (
			str_pad ("1", $from, '0'),	// от 10000
			str_repeat ('9', $to)		// до 9999999
		);
	}

	/**
	 * @desc Создание активации с коротким кодом.
	 * @param string $prefix Префикс для кода.
	 * @param integer $from Минимальное количество символов.
	 * @param integer $to Максимальное количество символов.
	 * @return string Свободный код (с префиксом)
	 */
	public function newShortCode ($prefix, $from = 5, $to = 7)
	{
		do {
			$code = $prefix . self::generateNumeric ($from, $to);
			$activation = Activation::byCode ($code);
		} while ($activation);

		return $code;
	}

	/**
	 * @desc Поиск активации по префиксу и короткому коду.
	 * @param string $prefix
	 * @param string $code
	 * @return Activation
	 */
	public function byShortCode ($prefix, $code)
	{
		return Activation::byCode ($prefix . $code);
	}

}