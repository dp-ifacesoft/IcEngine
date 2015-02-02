<?php

/**
 * Помощник для перевода текста в транслит
 *
 * @author goorus, neon, markov
 * @package IcEngine
 * @Service("helperMetagraphy")
 */
class Helper_Metagraphy extends Helper_Abstract
{
	/**
	 * для перевода СМС.
	 *
	 * @param string $string
	 * @author Sergey
	 * @return string
	 */
	public function rus2translit($string)
	{
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'h',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
			'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
			'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
		);
		// переводим в транслит
		$strTred = strtr($string, $converter);
		// заменям все ненужное нам на ""
		//$str = preg_replace('~[^-a-z0-9_]+~u', '', $str);
		// удаляем начальные и конечные '-'
		$strTrimed = trim($strTred, "-");
		return $strTrimed;
	}
    
	/**
	 * Перевод строки в транслит
	 *
	 * @param string $value Исходна стока
	 * @param string $lang [optional] Направление перевода
	 * 		Если "en" - из русского на транслит,
	 * 		если "ru" - из транслита на русский
	 * @return Результат транслитации.
	 */
	public function process($text, $lang = null, $strategyName = "Default")
	{
        $metagraphyStrategyManager = $this->getService(
            'metagraphyStrategyManager'
        );
        $strategy = $metagraphyStrategyManager->get($strategyName);
        return $strategy->process($text, $lang);
	}

	/**
	 * Формирует из названия статьи ссылку.
	 *
	 * @param string $value Исходное название
	 * @return string Ссылка
	 */
	public function makeUrlLink($text, $strategyName = "Default")
	{
        $metagraphyStrategyManager = $this->getService(
            'metagraphyStrategyManager'
        );
        $strategy = $metagraphyStrategyManager->get($strategyName);
		return $strategy->makeUrlLink($text);
	}
}