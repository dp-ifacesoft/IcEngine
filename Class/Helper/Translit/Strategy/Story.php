<?php

/**
 * Стратегия транслита для отзывов туристов
 *
 * @author markov
 */
class Helper_Translit_Strategy_Story extends Helper_Translit_Strategy_Default
{
     /**
	 * Перевод строки в транслит
	 *
	 * @param string $value Исходна стока
	 * @param string $lang [optional] Направление перевода
	 * 		Если "en" - из русского на транслит,
	 * 		если "ru" - из транслита на русский
	 * @return Результат транслитации.
	 */
	public function translit($text, $lang = null)
	{
        $helperString = $this->getService('helperString'); 
        $text = trim($text);   
		if (!isset($lang)) {
			$regexpRus = '/^[а-яА-Я]+/';
			$lang = preg_match($regexpRus, $text) ? 'en' : 'ru';
		}
		if ($lang == 'en') {
			// Сначала заменяем "односимвольные" фонемы.
			$text = $this->u_strtr($text, "абвгдеёзийклмнопрстуфхыэ ", 
                "abvgdeeziyklmnoprstufhie_"
            );
			$text = $this->u_strtr($text, "АБВГДЕЁЗИЙКЛМНОПРСТУФХЫЭ ", 
                "ABVGDEEZIYKLMNOPRSTUFHIE_"
            );
			// Затем - "многосимвольные".
			$text = $this->u_strtr(
				$text,
				array(
					"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh",
					"щ"=>"shch","ь"=>"", "ъ"=>"", "ю"=>"yu", "я"=>"ya",
					"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH",
					"Щ"=>"SHCH","Ь"=>"", "Ъ"=>"", "Ю"=>"YU", "Я"=>"YA",
					"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye",
					"&nbsp;"=>"_"
				)
			);
		} elseif ($lang == 'ru') {
			// Сначала заменяем"многосимвольные".
			$text = $this->u_strtr(
				$text,
				array(
					"zh"=>"ж", "ts"=>"ц", "ch"=>"ч", "sh"=>"ш",
					"shch"=>"щ", "yu"=>"ю", "ya"=>"я",
					"ZH"=>"Ж", "TS"=>"Ц", "CH"=>"Ч", "SH"=>"Ш",
					"SHCH"=>"Щ", "YU"=>"Ю", "YA"=>"Я",
					"&nbsp;"=>"_"
				)
			);
			//  Затем - "односимвольные" фонемы.
			$text = $this->u_strtr(
				$text,
				"abvgdeziyklmnoprstufh_",
				"абвгдезийклмнопрстуфх "
			);
			$text = $this->u_strtr(
				$text,
				"ABVGDEZIYKLMNOPRSTUFH_",
				"АБВГДЕЗИЙКЛМНОПРСТУФХ "
			);
		}
		return $text;
    }
}
