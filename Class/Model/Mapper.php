<?php

/**
 * Реализация ORM
 *
 * @author morph, neon
 * @Service("modelMapper")
 */
class Model_Mapper extends Manager_Abstract
{
    /**
     * Уже полученные связи
     * 
     * @author morph
     */
    protected $references;
    
	/**
	 * Получить ресурсы связи модели
	 *
	 * @param string $modelName
	 */
	public function getReferences($modelName)
	{
        return $this->getService('modelScheme')->scheme($modelName)
            ->references->__toArray();
	}
    
    /**
     * Получить схему связей по модели
     * 
     * @param Model $model
     */
    public function scheme($model)
    {
        $modelName = is_object($model) ? $model->modelName() : $model; 
        $references = isset($this->references[$modelName])
            ? $this->references[$modelName]
            : $this->getReferences($modelName);
        $this->references[$modelName] = $references;
        $scheme = $this->getService('modelMapperScheme')->newInstance($model);
        foreach ($references as $propertyName => $reference) {
            $scheme->set($propertyName, $reference);
        }
        return $scheme;
    }
}