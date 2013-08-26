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
     * Хелпер для работы с массивами
     * 
     * @var Helper_Array
     */
    protected static $helper;

	/**
	 * Полученные аннотации методов
	 *
	 * @var array
	 */
	protected $methodsAnnotations;

	/**
	 * Полученные аннотации полей
	 *
	 * @var array
	 */
	protected $propertiesAnnotations;

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
		$this->methodsAnnotations = $methods;
		$this->propertiesAnnotations = $properties;
	}
    
    /**
     * Сравнить сеты аннотаций
     * 
     * @param Annotation_Set $annotationSet
     * @return boolean
     */
    public function compare($annotationSet)
    {
        $data = $this->getData();
        $otherData = $annotationSet->getData();
        return !$this->helper()->diffRecursive($data, $otherData) &&
            !$this->helper()->diffRecursive($otherData, $data);
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
            'methods'       => $this->methodsAnnotations,
            'properties'    => $this->propertiesAnnotations
        );
    }
    
    /**
     * Получить хелпер
     * 
     * @return Helper_Array
     */
    public function helper()
    {
        if (!self::$helper) {
            self::$helper = new Helper_Array();
        }
        return self::$helper;
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
		return isset($this->methodsAnnotations[$methodName])
			? $this->methodsAnnotations[$methodName] : null;
	}

	/**
	 * Получить аннотацию поля
	 *
	 * @param string $propertyName
	 * @return Annotation_Row
	 */
	public function getProperty($propertyName)
	{
		return isset($this->propertiesAnnotations[$propertyName])
			? $this->propertiesAnnotations[$propertyName] : null;
	}

	/**
	 * Изменить аннотацию метода
	 *
	 * @param string $methodName
	 * @param Annotation_Row $annotationRow
	 */
	public function setMethod($methodName, $annotationRow)
	{
		$this->methodsAnnotations[$methodName] = $annotationRow;
	}

	/**
	 * Изменить аннотацию поля
	 *
	 * @param string $propertyName
	 * @param Annotation_Row $annotationRow
	 */
	public function setProperty($propertyName, $annotationRow)
	{
		$this->propertiesAnnotations[$propertyName] = $annotationRow;
	}
}