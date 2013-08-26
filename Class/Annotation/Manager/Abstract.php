<?php

/**
 * Абстрактный менеджер аннотаций
 *
 * @author morph, goorus
 */
abstract class Annotation_Manager_Abstract
{
	/**
	 * Хранилище полученных аннотаций
	 *
	 * @var Data_Provider_Abstract
	 */
	protected $repository;

	/**
	 * Источник аннотаций
	 *
	 * @var Annotation_Source_Abstract
	 */
	protected $source;

    /**
     * Сравнить новый и существующий сеты аннотаций
     * 
     * @param string $className
     * @param Annotation_Set $annotationSet
     * @return boolean
     */
    public function compare($className)
    {
        $existsAnnotationSet = $this->repository->get($className);
        if (!$existsAnnotationSet) {
            return false;
        }
        $parsedAnnotationSet = $this->source->get($className);
        return $existsAnnotationSet->compare($parsedAnnotationSet);
    }
    
	/**
	 * Получить набор аннотациий класса по экземпляру класса
	 *
	 * @param \StdClass $class
	 * @param string $annotationName
	 * @return Annotation_Set
	 */
	public function getAnnotation($class)
	{
		$className = is_string($class) ? $class : get_class($class);
		$annotationSet = $this->repository->get($className);
		if (!$annotationSet) {
			$annotationSet = $this->source->get($className);
			$this->repository->set($className, $annotationSet);
		}
		return $annotationSet;
	}

	/**
	 * Получить текущее хранилище данных
	 *
	 * @return Data_Provider_Abstract
	 */
	public function getRepository()
	{
		return $this->repository;
	}

	/**
	 * Получить текущий источник аннотаций
	 *
	 * @return Annotation_Source_Abstract
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Изменить текущее хранилище данных
	 *
	 * @param Data_Provider_Abstract $repository
	 */
	public function setRepository($repository)
	{
		$this->repository = $repository;
	}

	/**
	 * Изменить текущий источник аннотаций
	 *
	 * @param Annotation_Source_Abstract $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}
}