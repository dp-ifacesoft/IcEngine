<?php

/**
 * Помощник для перевода текста в транслит
 *
 * @author Юрий Шведов, neon
 * @package IcEngine
 * @Service("helperTranslit")
 */
class Helper_Translit extends Helper_Abstract
{
    /**
     * Инициализированные стратегии
     */
    public $strategies = array();
    
    /**
     * Название текущей стратегии
     */
    protected $strategyName = 'Helper_Translit_Strategy_Default';

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
		$str = strtr($string, $converter);
		// заменям все ненужное нам на ""
		//$str = preg_replace('~[^-a-z0-9_]+~u', '', $str);
		// удаляем начальные и конечные '-'
		$str = trim($str, "-");
		return $str;
	}

    /**
     * Устанавливает стратегию транслитерации
     */
    public function setStrategy($name)
    {
        $this->strategyName = 'Helper_Translit_Strategy_' . $name;
    }
    
    /**
     * получает стратегию транслитерации
     */
    public function getStrategy()
    {
        if (!isset($this->strategies[$this->strategyName])) {
            $this->strategies[$this->strategyName] = new $this->strategyName;
        }
        return $this->strategies[$this->strategyName];
    }

    /**
     * Перевод строки в транслит
     *
     * @param $text
     * @param string $lang [optional] Направление перевода
     *        Если "en" - из русского на транслит,
     *        если "ru" - из транслита на русский
     * @param string $strategyName
     * @internal param string $value Исходна стока
     * @return mixed Результат транслитации.
     */
	public function translit($text, $lang = null, $strategyName = "Default")
	{
        $this->setStrategy($strategyName);
        return $this->getStrategy()->translit($text, $lang);
	}

    /**
     * Формирует из названия статьи ссылку.
     *
     * @param $text
     * @param string $strategyName
     * @internal param string $value Исходное название
     * @return string Ссылка
     */
	public function makeUrlLink($text, $strategyName = "Default")
	{
        $this->setStrategy($strategyName);
		return $this->getStrategy()->makeUrlLink($text);
	}
}