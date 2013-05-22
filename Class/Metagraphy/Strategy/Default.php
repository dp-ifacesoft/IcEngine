<?php

/**
 * Стратегия транслита по умолчанию
 *
 * @author markov
 */
class Metagraphy_Strategy_Default extends Metagraphy_Strategy_Abstract
{
    /**
	 * @inheritdoc
	 */
	public function process($text, $lang = null)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $helperString = $serviceLocator->getService('helperString'); 
        $textTrimed = trim($text);   
        $textScreened = $helperString->replaceSpecialChars($textTrimed, '');
		if (!isset($lang)) {
			$regexpRus = '/^[а-яА-Я]+/';
			$lang = preg_match($regexpRus, $textScreened) ? 'en' : 'ru';
		}
		if ($lang == 'en') {
			$textProcessed = $this->processToEn($textScreened);
		} elseif ($lang == 'ru') {
			$textProcessed = $this->processToRu($textScreened);
		}
		return $textProcessed;
    }
    
    /**
	 * @inheritdoc
	 */
    public function processToEn($text)
    {
        // Сначала заменяем "односимвольные" фонемы.
        $textChangedDown = $this->u_strtr($text, 
            "абвгдеёзийклмнопрстуфхыэ ", 
            "abvgdeeziyklmnoprstufhie_"
        );
        $textChangedUp = $this->u_strtr($textChangedDown, 
            "АБВГДЕЁЗИЙКЛМНОПРСТУФХЫЭ ", 
            "ABVGDEEZIYKLMNOPRSTUFHIE_"
        );
        // Затем - "многосимвольные".
        $textChanged = $this->u_strtr($textChangedUp,
            array(
                "ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh",
                "щ"=>"shch","ь"=>"", "ъ"=>"", "ю"=>"yu", "я"=>"ya",
                "Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH",
                "Щ"=>"SHCH","Ь"=>"", "Ъ"=>"", "Ю"=>"YU", "Я"=>"YA",
                "ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye",
                "&nbsp;"=>"_"
            )
        );
        return $textChanged;
    }
    
    /**
	 * @inheritdoc
	 */
    public function processToRu($text)
    {
        // Сначала заменяем"многосимвольные".
        $textChanged = $this->u_strtr($text,
            array(
                "zh"=>"ж", "ts"=>"ц", "ch"=>"ч", "sh"=>"ш",
                "shch"=>"щ", "yu"=>"ю", "ya"=>"я",
                "ZH"=>"Ж", "TS"=>"Ц", "CH"=>"Ч", "SH"=>"Ш",
                "SHCH"=>"Щ", "YU"=>"Ю", "YA"=>"Я",
                "&nbsp;"=>"_"
            )
        );
        //  Затем - "односимвольные" фонемы.
        $textChangedDown = $this->u_strtr($textChanged,
            "abvgdeziyklmnoprstufh_",
            "абвгдезийклмнопрстуфх "
        );
        $textChangedUp = $this->u_strtr($textChangedDown,
            "ABVGDEZIYKLMNOPRSTUFH_",
            "АБВГДЕЗИЙКЛМНОПРСТУФХ "
        );
        return $textChangedUp;
    }
    
    /**
	 * @inheritdoc
	 */
	public function makeUrlLink($text)
	{
		$link = $this->translit($text, 'en');
		$linkReplaced = preg_replace('/([^0-9a-zA-Z_])+/', '', $link);
		$linkReplaced2 = preg_replace('/[_]{2,}/', '_', $linkReplaced);
		$linkReplaced3 = preg_replace('/^_/', '', $linkReplaced2);
		$linkReplaced4 = preg_replace('/_$/', '', $linkReplaced3);
		return $linkReplaced4;
	}
}
