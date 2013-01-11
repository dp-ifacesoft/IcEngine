<?php

/**
 * Абстрактная ссылка для связи схемы моделей
 * 
 * @author morph
 */
abstract class Model_Mapper_Scheme_Resource_Link_Abstract
{
    /**
     * Объект связи
     * 
     * @var Model_Mapper_Scheme_Resource 
     */
	protected $resource;

    /**
     * Связывание моделей через заданную связь
     * 
     * @param Model $model1
     * @param Model $model2
     * @param Model_Mapper_Scheme_Reference_Abstract $reference
     */
    abstract public function link($model1, $model2, $reference);
    
    /**
     * Изменить объект связи
     * 
     * @param Model_Mapper_Scheme_Resource $resource
     */
	public function setResource($resource)
	{
		$this->resource = $resource;
	}
}