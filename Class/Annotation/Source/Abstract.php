<?php

/**
 * Абстрактный источник аннотаций
 *
 * @author morph, goorus
 */
abstract class Annotation_Source_Abstract
{
    /**
     * Имя класса текущей аннотации
     * 
     * @var string
     */
    protected $className;
    
	/**
	 * Получить набор аннотаций класса
	 *
	 * @param string $class
	 * @return Annotation_Set
	 */
	final public function get($className)
	{
        $this->className = $className;
		$classAnnotation = $this->getClass($className);
		$methodAnnotations = $this->getMethods($className);
		$propertyAnnotations = $this->getProperties($className);
		$annotationSet = new Annotation_Set(
			$classAnnotation, $methodAnnotations, $propertyAnnotations
		);
		return $annotationSet;
	}

	/**
	 * Распарсить аннотацию класса
	 *
	 * @param string $class
	 * @return array
	 */
	abstract protected function getClass($className);

	/**
	 * Распарсить аннотации методов класса
	 *
	 * @param string $class
	 * @return array
	 */
	abstract protected function getMethods($className);

	/**
	 * Распарсить аннотации полей класса
	 *
	 * @param string $class
	 * @return array
	 */
	abstract protected function getProperties($className);
}