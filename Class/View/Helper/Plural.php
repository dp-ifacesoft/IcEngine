<?php

class View_Helper_Plural extends View_Helper_Abstract
{

	/**
	 * @desc Стандартная функция выбора формы
	 * @param integer $n Число
	 * @param array $forms Формы, разделенные запятой
	 * @return string Подходящая форма
	 */
	public function _pluralDefault ($n, array $forms)
	{
		$plural = ($n % 10 == 1 && $n % 100 != 11 ? 0 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 or $n % 100 >= 20) ? 1 : 2));

		if (isset ($forms [$plural]))
		{
			return $forms [$plural];
		}

		reset ($forms);
		return current ($forms);
	}

	/**
	 * @desc Получение подходящей формы слова через Morphy
	 * @param integer $value Число
	 * @param string $word Слово в произвольной форме
	 * @return string Слово в подходящей форме
	 */
	public function _pluralMorphy ($n, $word)
	{
		Loader::requireOnce ('Morphy.php', 'includes');
		$morphy = Morphy::get ();
		$word = $morphy->getBaseForm ($word);

		$plural = ($n % 10 == 1 && $n % 100 != 11 ? 0 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 or $n % 100 >= 20) ? 1 : 2));

		if ($plural == 0)
		{
			return $word;
		}

		switch ($plural)
		{
			case 1:
				$word = $morphy->castFormByGramInfo ($word, null, array ('ЕД', 'РД'));
				return $word [0];
			case 2:
				$word = $morphy->castFormByGramInfo ($word, null, array ('МН', 'РД'));
				return $word [0];
		}

		return $word;
	}

	/**
	 * @desc Выбирает подходящую форму для числа
	 * @param array $params
	 * 		$params ['value'] integer
	 * 		Число
	 *  	$params ['forms'] string
	 *  	Формы слова, разделенные запятой ('день,дня,дней')
	 *  @return string Слово в подходящей форме
	 */
	public function get (array $params)
	{
		$value = (int) $params ['value'];
		$forms = $params['forms'];
		if (!is_array($forms)) {
			$forms = explode (',', $params ['forms']);
		}

		if (count ($forms) > 1)
		{
			return $this->_pluralDefault ($value, $forms);
		}

		return $this->_pluralMorphy ($value, $forms [0]);
	}

}