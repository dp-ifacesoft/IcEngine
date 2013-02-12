<?php

/**
 * Помощник для работы со строками
 *
 * @author Юрий Шведов, neon, markov
 * 
 * @Service("helperString")
 */
class Helper_String
{
    public function replaceSpecialChars($string, $value=' ')
    {
        $value = str_replace(
            array(
                "\r", "\n", "\t", ',', '(', ')',
                '[', ']', '{', '}', '-', '_',
                '!', '@', '#', '$', '%', '^', ':',
                '&', '*', ',', '.', '+', '=',
                '/', ' \\', '|', '\'', '"', '~', ' '
            ),
            $value,
            $string
        );
        return $value;
    }
    
    /**
	 * Получение превью для текста.
	 * @param string $text
	 * @param integer $length
	 * 		Ориентировочно ожидаемая длина превью.
	 * @return string
	 */
	public function smartPreview($text, $length = 100)
	{
		$text =  stripslashes($text) . ' ';
		if (!isset($text {$length}))
		{
			return $text;
		}
		$space_pos = strpos ($text, ' ', $length);
		$result = substr ($text, 0, $space_pos);
		return $result;
	}
}
