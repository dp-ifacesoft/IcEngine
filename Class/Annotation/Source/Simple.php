<?php

/**
 * Простой источник аннотаций. Разбирет аннотация вида phpdoc
 *
 * @author morph, goorus
 */
class Annotation_Source_Simple extends Annotation_Source_Abstract
{
	/**
	 * Рефлексия класса
	 *
	 * @var array
	 */
	private $reflections = array();

	/**
	 * Разбирает строку на части
	 *
	 * @param string $string
	 * @return array
	 */
	protected function extract($string)
	{
		$parts = explode('@', $string);
		array_shift($parts);
		if (!$parts) {
			return;
        }
		foreach($parts as $i => $part) {
			$lines = explode("\n", $part);
			$parts[$i] = array();
			foreach ($lines as $line) {
				$line = trim($line, "*\t\r/ ");
				if (!$line) {
					continue;
				}
				$parts[$i][] = $line;
			}
			$parts[$i] = join('', $parts[$i]);
		}
		return array_values($parts);
	}

	/**
	 * @inheritdoc
	 * @see Annotation_Source_Abstract::getClass
	 */
	public function getClass($className)
	{
		$reflection = $this->getReflection($className);
		$doc = $reflection->getDocComment();
		$data = $this->parse($doc);
		return $data;
	}

	/**
	 * @inheritdoc
	 * @see Annotation_Source_Abstract::getMethods
	 */
	public function getMethods($className)
	{
		$reflection = $this->getReflection($className);
		$methods = $reflection->getMethods();
		$resultMethods = array();
		foreach ($methods as $method) {
			$data = $this->parse($method->getDocComment());
			$resultMethods[$method->name] = $data;
		}
		return $resultMethods;
	}

	/**
	 * @inheritdoc
	 * @see Annotation_SourceA_bstract::getProperties
	 */
	public function getProperties($className)
	{
		$reflection = $this->getReflection($className);
		$properties = $reflection->getProperties();
		$resultProperties = array();
		foreach ($properties as $property) {
			$data = $this->parse($property->getDocComment());
			$resultProperties[$property->name] = $data;
		}
		return $resultProperties;
	}

	/**
	 * Получить рефлексию класса
	 *
	 * @param string $class
	 * @return \ReflectionClass
	 */
	protected function getReflection($className)
	{
		if (!isset($this->reflections[$className])) {
			$this->reflections[$className] = new \ReflectionClass($className);
		}
		return $this->reflections[$className];
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
        $regExp = '#^([a-zA-Z]+) ([a-zA-Z_]+)(?: \$([a-zA-Z]+))?#';
		foreach ($parts as $param) {
            $matches = array();
            preg_match_all($regExp, $param, $matches);
            if (empty($matches[1][0])) {
                continue;
            }
            $annotation = $matches[1][0];
            $var = $matches[2][0];
            $type = isset($matches[3][0]) ? $matches[3][0] : null;
            if (!isset($result[$annotation])) {
                $result[$annotation] = array();
            }
            if ($type) {
                $result[$annotation][$var] = $type;
            } else {
                $result[$annotation][] = $var;
            }
		}
		return $result;
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
