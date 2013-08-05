<?php

/**
 * Стандартный источник аннотаций
 *
 * @author morph, goorus
 */
class Annotation_Source_Standart extends Annotation_Source_Simple
{
    /**
     * Вернуть количество открывающихся и закрывающихся фигурных скобок
     * 
     * @param string $part
     * @return array 
     */
    protected function calcBreakCount($part)
    {
        $openedSt = -1;
        $openedCount = 0;
        while ($openedSt !== false) {
            $openedSt = strpos($part, '{', $openedSt + 1);
            $openedCount++;
        }
        $endedCount = 0;
        $endedSt = -1;
        while ($endedSt !== false) {
            $endedSt = strpos($part, '}', $endedSt + 1);
            $endedCount++;
        }
        $openedCount--;
        $endedCount--;
        return array($openedCount, $endedCount);
    }
    
	/**
	 * Выполнить регулярное выражение
	 *
	 * @param string $string
	 * @return array
	 */
	public function parse($string)
	{
		$result = array();
		$parts = $this->extract($string);
		if (!$parts) {
			return;
		}
		foreach ($parts as $param) {
            $param = trim($param);
			$b = strpos($param, '(');
			if ($b !== false) {
				$e = strrpos($param, ')');
				if ($e === false) {
					continue;
				}
				$value = trim(substr($param, $b + 1, $e - $b - 1));
				$param = trim(substr($param, 0, $b));
				if (!$param || !preg_match('#[A-Z]#', $param[0])) {
					continue;
				}
				$r = $this->parsePart($value);
                if ($r) {
                    $result[$param][] = $r;
				}
			} elseif ($param) {
				$e = strrpos($param, ')');
				if ($e !== false || !preg_match('#[A-Z]#', $param[0])) {
					continue;
				}
				$result[$param] = $param;
			}
		}
		return $result;
	}

	/**
	 * Разбирает часть запроса
	 *
	 * @param string $value
	 * @return mixed
	 */
	protected function parsePart($value)
	{
        $parts = explode(',', $value);
        $markedItems = array();
        $currentString = null;
        $count = count($parts);
        foreach ($parts as $i => $part) {
            if (in_array($i, $markedItems)) {
                continue;
            }
            if (!$currentString) {
                $currentString = $part;
            }
            list($openedCount, $endedCount) = $this->calcBreakCount(
                $currentString
            );
            if ($openedCount != $endedCount) {
                if ($i == $count - 1) {
                    continue;
                } elseif ($i < $count - 1) {
                    $currentString = $parts[$i] . ',' . $parts[$i + 1];
                }
                $parts[$i + 1] = $currentString;
                unset($parts[$i]);
            } else {
                $parts[$i] = $currentString;
                $currentString = null;
            }
        } 
        $result = new Objective(array());
        $indexes = array(0);
        foreach ($parts as $part) {
            $stack = array();
            array_push($stack, $result);
            $source = $result;
            $currentName = '';
            $currentValue = '';
            $lastValue = '';
            $bufferValue = '';
            $endOnString = false;
            $valueChanged = false;
            $collectionValue = false;
            $openOnSingle = false;
            $hasClosed = false;
            $openOnDouble = false;
            $levelCount = 0;
            $lastClosedAtLevel = 0;
            for ($i = 0, $length = strlen($part); $i < $length; $i++) {
                $ch = $part[$i];
                if ($collectionValue) {
                    if (($ch == '"' || $ch == '\'') && 
                        !$openOnSingle && !$openOnDouble) {
                        if ($ch == '"') {
                            $openOnDouble = true;
                        } elseif ($ch == '\'') {
                            $openOnSingle = true;
                        }
                        continue;
                    } elseif (($ch == '"' && $openOnDouble) || 
                        ($ch == '\'' && $openOnSingle)) {
                        $collectionValue = false;
                        $openOnSingle = false;
                        $openOnDouble = false;
                        $endOnString = true;
                        $lastValue = $currentValue;
                        continue;
                    } elseif ($openOnSingle || $openOnDouble || 
                        ($ch != '{' && $ch != '}' && $ch != ',')) {
                        $currentValue .= $ch;
                        $valueChanged = true;
                        continue;
                    } elseif (!$openOnSingle && !$openOnDouble && 
                        ($ch == '{' || $ch == '}' || $ch == ',')) {
                        $collectionValue = false;
                        --$i;
                        continue;
                    } 
                }
                switch($ch) {
                    case '\'': case '"':
                        $collectionValue = true;
                        if ($ch == '\'') {
                            $openOnSingle = true;
                        } elseif ($ch == '"') {
                            $openOnDouble = true;
                        }
                        break;
                    case ' ': case "\t": case "\n": case "\r":
                        break;
                    case ',': 
                        if (!$collectionValue) {
                            $currentValue = $valueChanged 
                            ? $currentValue : $currentName;
                        }
                        if (!$currentValue) {
                            $currentValue = $bufferValue;
                        }
                        if ($hasClosed && $levelCount == $lastClosedAtLevel) {
                            end($stack);
                            $source = current($stack);
                        }
                        if ($currentName) {
                            $source[$currentName] = $currentValue;
                        } elseif ($currentValue !== '') {
                            $index = array_pop($indexes);
                            $source[$index] = $currentValue;
                            array_push($indexes, $index + 1);
                        }
                        $endOnString = false;
                        $lastValue = $currentValue;
                        $currentName = '';
                        $bufferValue = '';
                        $currentValue = '';
                        $valueChanged = false;
                        $collectionValue = false;
                        $hasClosed = false;
                        break;
                    case '=': 
                        if ($endOnString) {
                            $currentName = $lastValue;
                            $lastValue = '';
                            $currentValue = '';
                            $endOnString = false;
                        } else {
                            $currentName = $bufferValue;
                            $bufferValue = '';
                        }
                        $collectionValue = true;
                        break;
                    case '{':
                        if ($currentName) {
                            $collectionValue = false; 
                            end($stack);
                            $last = current($stack);
                            if (!isset($last[$currentName])) {
                                $last[$currentName] = new Objective();
                            }
                            $source = $last[$currentName];
                            array_push($stack, $source);
                            $currentName = '';
                        } else {
                            end($stack);
                            $last = current($stack);
                            $index = array_pop($indexes);
                            if (!isset($last[$index])) {
                                $last[$index] = new Objective();
                            } 
                            $source = $last[$index];
                            array_push($stack, $source);
                            array_push($indexes, $index + 1);
                            array_push($indexes, 0);
                        }
                        $currentValue = '';
                        $levelCount++;
                        break;
                    case '}': 
                        if (!$collectionValue) {
                            $currentValue = $valueChanged 
                                ? $currentValue : $currentName;
                        }
                        if (!$currentValue) {
                            $currentValue = $bufferValue;
                        }
                        $source = array_pop($stack);
                        if ($currentName) {
                            $source[$currentName] = $currentValue;
                            $currentName = '';
                        } elseif ($currentValue !== '') {
                            $index = array_pop($indexes);
                            array_push($indexes, 0);
                            $source[$index] = $currentValue;
                        }
                        $valueChanged = false;
                        $collectionValue = false; 
                        $hasClosed = true;
                        $lastValue = '';
                        $currentValue = '';
                        $bufferValue = '';
                        $levelCount--;
                        $lastClosedAtLevel = $levelCount;
                        break;
                    default:
                        $bufferValue .= $ch;
                }
            }
            if ($currentName) {
                $source[$currentName] = $currentValue;
            } elseif ($lastValue !== '' || $bufferValue !== '') {
                $index = array_pop($indexes);
                $source[$index] = $lastValue ?: $bufferValue;
                array_push($indexes, $index + 1);
            }
        }
        return $result->__toArray();
	}
}