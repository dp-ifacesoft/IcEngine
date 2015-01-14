<?php

/**
 * Абстрактный делегат менеджера схем данных модели
 * 
 * @author morph
 */
abstract class Data_Scheme_Manager_Delegate_Abstract
{
    /**
     * Получить поля модели
     * 
     * @param string $modelName
     * @return array
     */
    abstract public function getFields($modelName);
    
    /**
     * Получить индексы модели
     * 
     * @param string $modelName;
     * @return array
     */
    public function getIndexes($modelName)
    {
        return array();
    }
    
    /**
     * Получить информацию о моделе
     * 
     * @param string $modelName
     * @return array
     */
    public function getInfo($modelName)
    {
        return array();
    }
    
    /**
     * Наполнить схему данных
     * 
     * @param Data_Scheme $dataScheme
     * @return Data_Scheme
     */
    public function getScheme($dataScheme)
    {
        $dto = $dataScheme->getDto();
        $modelName = $dataScheme->getModelName();
        $dto->info = $this->getInfo($modelName);
        $dto->fields = $this->getFields($modelName);
        $dto->indexes = $this->getIndexes($modelName);
        return $dataScheme;
    }
}