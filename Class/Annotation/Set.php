<?php

/**
 * Набор аннотаций класса (в том числе аннотаций методов и полей)
 *
 * @author morph, goorus
 */
class Annotation_Set
{
	/**
	 * Набор аннотаций класса
	 *
	 * @var array
	 */
	protected $classAnnotation;

	/**
	 * Полученные аннотации методов
	 *
	 * @var array
	 */
	protected $methodAnnotations;

	/**
	 * Полученные аннотации полей
	 *
	 * @var array
	 */
	protected $propertyAnnotations;

	/**
	 * Конструктор
	 *
	 * @param Annotation_Row Аннотация класса
	 * @param array $methods Аннотации методов
	 * @param array $properties Аннотации полей
	 */
	public function __construct($class, $methods, $properties)
	{
		$this->classAnnotation = $class;
		$this->methodAnnotations = $methods;
		$this->propertyAnnotations = $properties;
	}

	/**
	 * Получить аннотацию класса
	 *
	 * @return Annotation_Row
	 */
	public function get($name)
	{
		return isset($this->classAnnotation[$name])
            ? $this->classAnnotation[$name] : null;
	}

    /**
     * Получить данные аннотации 
     * 
     * @return array
     */
    public function getData()
    {
        return array(
            'class'         => $this->classAnnotation,
            'methods'       => $this->methodAnnotations,
            'properties'    => $this->propertyAnnotations
        );
    }
    
	/**
	 * Изменить аннотацию класса
	 *
	 * @param Annotation_Row $annotation
	 */
	public function set($name, $value)
	{
		$this->classAnnotation[$name] = $value;
	}

	/**
	 * Получить аннотацию метода
	 *
	 * @param string $methodName
	 * @return Annotation_Row
	 */
	public function getMethod($methodName)
	{
		return isset($this->methodAnnotations[$methodName])
			? $this->methodAnnotations[$methodName] : null;
	}

	/**
	 * Получить аннотацию поля
	 *
	 * @param string $propertyName
	 * @return Annotation_Row
	 */
	public function getProperty($propertyName)
	{
		return isset($this->propertyAnnotations[$propertyName])
			? $this->propertyAnnotations[$propertyName] : null;
	}

	/**
	 * Изменить аннотацию метода
	 *
	 * @param string $methodName
	 * @param Annotation_Row $annotationRow
	 */
	public function setMethod($methodName, $annotationRow)
	{
		$this->methodAnnotations[$methodName] = $annotationRow;
	}

	/**
	 * Изменить аннотацию поля
	 *
	 * @param string $propertyName
	 * @param Annotation_Row $annotationRow
	 */
	public function setProperty($propertyName, $annotationRow)
	{
		$this->propertyAnnotations[$propertyName] = $annotationRow;
	}
}