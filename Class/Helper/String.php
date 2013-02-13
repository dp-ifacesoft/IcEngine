<?php

/**
 * Помощник для работы со строками
 *
 * @author goorus, neon, markov
 * @Service("helperString")
 */
class Helper_String
{
    /**
     * Переносы строки
     * 
     * @param string $title
     * @return string
     */
    public function parts($title)
	{
		$result = '';
		$line = '';
		$parts = explode(' ', trim($title));
		foreach ($parts as $part) {
			$partLen = mb_strlen($part, 'UTF-8');
			if ($partLen > 14) {
				$n = 14;
				if ($partLen <= 16) {
					$n = 12;
				}
				$part1 = mb_substr($part, 0, $n, 'UTF-8');
				$part2 = mb_substr($part, $n, $partLen - $n, 'UTF-8');
				$part = $part1 . '-<br />' . $part2;
				$result .= $part;
			} else {
				$newLine = $line . ' ' . $part;
				$newLineLen = mb_strlen($newLine, 'UTF-8');
				if ($newLineLen >= 16) {
					if ($partLen >= 8) {
						$n = round($partLen / 2);
						$part1 = mb_substr($part, 0, $n, 'UTF-8');
						$part2 = mb_substr($part, $n, $partLen - $n, 'UTF-8');
						$part = $part1 . '-<br />' . $part2;
						$line .= ' ' . $part;
					} else {
						$line .= '<br />' . $part;
					}
					$result .= $line;
					$line = '';
				} else {
					$line .= ' ' . $part;
				}
			}
		}
		if ($line) {
			$result .= $line;
		}
		return $result;
	}
    
    /**
     * Заменяет символы спец. символы на указанный
     * 
     * @param string $string
     * @param string $value
     * @return string
     */
    public function replaceSpecialChars($string, $value = ' ')
    {
        $value = str_replace(
            array(
                "\r", "\n", "\t", ',', '(', ')',
                '[', ']', '{', '}', '-', '_',
                '!', '@', '#', '$', '%', '^', ':',
                '&', '*', ',', '.', '+', '=',
                '/', ' \\', '|', '\'', '"', '~', ' '
            ),
            $value, $string
        );
        return $value;
    }
    
    /**
	 * Получение превью для текста
     * 
	 * @param string $text
	 * @param integer $length Ориентировочно ожидаемая длина превью
	 * @return string
	 */
	public function smartPreview($text, $length = 100)
	{
		$text =  stripslashes($text) . ' ';
		if (!isset($text[$length])) {
			return $text;
		}
		$spacePos = strpos($text, ' ', $length);
		$result = substr($text, 0, $spacePos);
		return $result;
	}
}
