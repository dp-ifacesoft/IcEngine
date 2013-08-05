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