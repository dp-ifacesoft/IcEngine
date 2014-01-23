<?php

/**
 * Помощник для работы со строками
 *
 * @author goorus, neon, markov, morph
 * @Service("helperString")
 */
class Helper_String
{
    /**
     * Возвращает массив строк, разделенных через запятую, с убранными
     * пробелами по бокам
     *
     * @param string $text
     * @return array
     */
    public function expand($text)
    {
        $result = array();
        if ($text) {
            if (strpos($text, ',') === false) {
                $result[] = $text;
                return $result;
            }
            $textExploded = explode(',', $text);
            foreach ($textExploded as $item) {
               $result[] = trim($item);
            }
        }
        return $result;
    }


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
        $value = str_replace(array(
            "\r", "\n", "\t", ',', '(', ')',
            '[', ']', '{', '}', '-', '_',
            '!', '@', '#', '$', '%', '^', ':',
            '&', '*', ',', '.', '+', '=',
            '/', ' \\', '|', '\'', '"', '~', ' '),
            $value, $string
        );
        return $value;
    }

    /**
     * Нормализовать строку по шаблону
     *
     * @param array $row
     * @param array $fields
     * @param array $params
     * @return array
     */
    public function normalizeFields($row, $fields, $params)
    {
        foreach ($fields as $field) {
            $matches = array();
            $template = $row[$field];
            preg_match_all(
                '#{\$([^\.}]+)(?:\.([^}]+))?}#', $template, $matches
            );
            if (!empty($matches[1][0])) {
                $template = $row[$field];
                foreach ($matches[1] as $i => $table) {
                    $key = isset($matches[2][$i]) ? $matches[2][$i] : null;
                    if (!$key) {
                        if (!isset($params[$table])) {
                            continue;
                        }
                        $template = str_replace(
                            '{$' . $table . '}', $params[$table], $template
                        );
                    } else {
                        if (!isset($params[$table], $params[$table][$key])) {
                            continue;
                        }
                        $template = str_replace(
                            '{$' . $table . '.' . $key . '}',
                            $params[$table][$key],
                            $template
                        );
                    }
                }
            }
            $row[$field] = $template;
        }
        return $row;
    }

    /**
     * Получение превью для текста.
     * @param string    $text
     * @param int       $length
     * @param bool      $wordsafe
     * @param bool      $dots
     * @return string   Длина превью с учетом кодировки.
     */
    public function superSmartPreview ($text, $length = 150,
        $wordsafe = true, $dots = true)
    {
        $text = stripslashes($text);
        $text = $this->html2text($text);
        $text = trim($text);
        return $this->truncateUtf8($text, $length, $wordsafe, $dots);

    }

    /**
     * @desc Перевод html в текст
     * @param string $document Исходный текст с тэгами
     * @return string Полученный чистый текст
     */
    public function html2text ($document)
    {
        $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
        );
        $text = preg_replace($search, '', $document);
        return $text;
    }

    /**
     * Возвращает строку, усеченную до заданной длины с учетом кодировки.
     * Гарантируется, что в конце строки не останется части мультибайтового символа.
     * 10x to Drupal
     *
     * @param string $string
     * 		Исходная строка
     * @param integer $len
     * 		Необходимая длина
     * @param boolean $wordsafe
     * 		Сохранение цельных слов. Если true, усечение произойдет по пробелу.
     * @param boolean $dots
     * 		Вставить многоточие в конец строки, если строка была усечена.
     * @return string
     * 		Усеченная строка.
     */
    public function truncateUtf8($string, $len, $wordsafe = false, $dots = false)
    {
        $slen = strlen ($string);

        if ($slen <= $len)
        {
            return $string;
        }

        if ($wordsafe)
        {
            $end = $len;
            while (($string[--$len] != ' ') && ($len > 0)) {};
            if ($len == 0)
            {
                $len = $end;
            }
        }
        if ((ord($string[$len]) < 0x80) || (ord($string[$len]) >= 0xC0))
        {
        	return substr($string, 0, $len) . ($dots ? ' ...' : '');
        }
        $p = 0;
        while ($len > 0 && $p < strlen ($string))
        {
            if (ord ($string[$p]) >= 0x80 && ord ($string[$p]) < 0xC0)
            {
                $p++;
            }
            $len--;
            $p++;
        };
        if (
            $p < strlen ($string) &&
            ord ($string[$p]) >= 0x80 && ord ($string[$p]) < 0xC0
        )
        {
            $p++;
        }

        return substr ($string, 0, $p) . ($dots ? ' ...' : '');
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

    /**
     * Первую букву в верхний регистр, остальные символы без изменений
     *
     * @param string $value
     * @return string
     */
    public function ucfirst($value) {
        return mb_strtoupper(mb_substr($value, 0, 1)) .
            mb_substr($value, 1);
    }
}