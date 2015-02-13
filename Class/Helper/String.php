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
        return preg_replace("/[^a-zA-ZА-Яа-я\d\s]/u",$value,$string);
    }
    
    
    /**
     * Заменяет указанные символы на указанные
     * 
     * @param string $string исходный текст
     * @param mixed $search заменяемые символы
     * @param mixed $replacement заменяющие символы
     * @return string
     */
    public function superReplaceSpecialChars($string, $search, $replacement)
    {
        if (!is_array($search)) {
            $search = explode('', $search);
        }
        if (!is_array($replacement)) {
            $replacement = explode('', $replacement);
        }
        $value = str_replace($search, $replacement, $string);
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
     * Получение превью для текста. У него есть суперсила
     * @param string    $text
     * @param int       $length
     * @param bool      $wordsafe
     * @param string    $etc
     * @param bool      $middle
     * @return string   Длина превью с учетом кодировки.
     */
    public function superSmartPreview ($text, $length = 150,
        $wordsafe = true, $etc = ' ...', $middle = false)
    {
        $text = htmlspecialchars_decode($text);
        $text = stripslashes($text);
        $text = $this->html2text($text);
        $text = trim($text);
        return $this->truncateUtf8($text, $length, $wordsafe, $etc, $middle);

    }
    
    /**
     * Удаляет дублирующиеся пробелы
     * @param string $text исходный
     * @param string $value чем заменить двойные пробелы
     * @return sting итоговый текст
     */
    public function removeMultiWhiteSpace($text, $value=' ') 
    {
        return preg_replace("/\s{2,}/u",$value,$text);
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
     * @param string $etc
     * 		Вставить многоточие в конец строки, если строка была усечена.
     * @param bool $middle
     *      отрезать середину а не конец
     * @return string
     * 		Усеченная строка.
     */
    public function truncateUtf8($string, $len, $wordsafe = false, $etc = '', $middle = false)
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
            if (!$middle) {
                $result = substr($string, 0, $len) . ($etc ? $etc : '');
            } else {
                $result = substr($string, 0, $len/2) . ($etc ? $etc : '')
                    . substr($string, -$len / 2);
            }
        	return $result;
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
        if (!$middle) {
            $result = substr($string, 0, $len) . ($etc ? $etc : '');
        } else {
            $result = substr($string, 0, $p/2) . ($etc ? $etc : '')
                . substr($string, -$p / 2);
        }
        return $result;
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
    
    /**
     * Оставляет дефолтные теги
     * 
     * @param string $text текст
     * @return string
     */
    public function stripTagsDefault($text)
    {
        return strip_tags($text, '<table><td><tr><tbody><thead><p><strong><em><span><ul><ol><li><a><div><br><img><h1><h2><h3><h4><h5><h6>');
    }
    
    /**
     * Ищем слово в тексте по ливенштейну
     * @param string|array $search что ищем
     * @param string $text где ищем
     * @param integer $neededDistance 
     * @param char $delimiter
     */
    public function levenstein(
        $search, $text, $neededDistance = 2, $delimiter=' '
    )
    {
        if (!$search || !$text) {
            return false;
        }
        $clearText = $this->replaceSpecialChars($text, '');
        $clearTextFixedSpaces = $this->removeMultiWhiteSpace($clearText);
        $words = explode($delimiter, $clearTextFixedSpaces);
        if (!is_array($search)) {
            foreach ($words as $word) {
                $distance = levenshtein($search, $word);
                if ($distance <= $neededDistance) {
                    return true;
                }
            }
        } else {
            foreach ($words as $word) {
                foreach($search as $searchWord) {
                    $distance = levenshtein($searchWord, $word);
                    if ($distance <= $neededDistance) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * ucfirst для мультибайта
     * 
     * @param string $string текст
     * @param string $encoding кодировка
     */
    function mb_ucfirst($string, $encoding = 'UTF-8')
    {
        $len = mb_strlen($string, $encoding);
        return mb_strtoupper(
            mb_substr($string, 0, 1, $encoding)
        ) . mb_substr($string, 1, $len - 1, $encoding);
    }

}