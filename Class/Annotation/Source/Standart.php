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
					if (!isset($result[$param])) {
						$result[$param] = array();
					}
					$result[$param] = array_merge($result[$param], array($r));
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
                if ($i == count($parts) - 1) {
                    continue;
                }
                if ($i < $count - 1) {
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
        foreach ($parts as $part) {
            if (strpos($part, '=') === false) {
                $part = trim($part, '\'" ');
                $result[$part] = array();
                $subParts = explode(',', $part);
                if (count($subParts) > 1) {
                    foreach ($subParts as $subPart) {
                        $result[$part][] = trim($subPart, '\'" ');
                    }
                } else {
                    $result[$part] = $part;
                }
                continue;
            }
            $stack = array();
            array_push($stack, $result);
            $source = $result;
            $currentName = '';
            $currentValue = '';
            $valueChanged = false;
            $collectionValue = false;
            $hasEqual = false;
            for ($i = 0, $length = strlen($part); $i < $length; $i++) {
                $ch = $part[$i];
                switch($ch) {
                    case '\'': case '"': 
                        break;
                    case ' ': 
                        if ($collectionValue) {
                            $currentValue .= $ch;
                        } 
                        break;
                    case "\t":
                        break;
                    case ',': 
                        if ($currentName) {
                            if (!$collectionValue) {
                                $currentValue = $valueChanged 
                                ? $currentValue : $currentName;
                            }
                            $source[$currentName] = $currentValue;
                        }
                        $currentName = '';
                        $currentValue = '';
                        $valueChanged = false;
                        $collectionValue = false;
                        break;
                    case '=': 
                        $hasEqual = true;
                        $collectionValue = true;
                        break;
                    case '{': 
                        if (!$hasEqual) {
                            break;
                        }
                        $collectionValue = false; 
                        end($stack);
                        $last = current($stack);
                        if ($currentName) {
                            if (!isset($last[$currentName])) {
                                $last[$currentName] = array();
                            }
                            $source = $last[$currentName];
                            array_push($stack, $source);
                        }
                        $currentName = '';
                        $currentValue = '';
                        break;
                    case '}': 
                        if (!$collectionValue) {
                            $currentValue = $valueChanged 
                                ? $currentValue : $currentName;
                        }
                        if ($currentName) {
                            $source[$currentName] = $currentValue;
                            $currentName = '';
                            $currentValue = '';
                            $valueChanged = false;
                            $collectionValue = false; 
                        }
                        $source = array_pop($stack);
                        break;
                    default: 
                        if (!$collectionValue) {
                            $currentName .= $ch; 
                        } else {
                            $currentValue .= $ch;
                            $valueChanged = true;
                        }
                        break;
                }
            }
            if ($currentName) {
                $source[$currentName] = $currentValue;
                $collectionValue = false;
            }
        }
        return $result->__toArray();
	}
    
    /**
     * Изменить рефлексию класса
     * 
     * @param ReflectionClass $reflection
     */
    public function setReflection($reflection)
    {
        $this->reflection = $reflection;
    }
}