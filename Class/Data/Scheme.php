<?php

/**
 * Схема данных (наполняется источником данных)
 * 
 * @author morph
 */
class Data_Scheme 
{
    /**
     * Источник данных схемы данных
     * 
     * @var Data_Source
     */
    protected $dataSource;
    
    /**
     * Объект передачи данных схемы
     * 
     * @var Data_Scheme_Dto
     */
    protected $dto;
    
    /**
     * Конструктор 
     * 
     * @param Data_Scheme_Dto $dto
     */
    public function __construct($dto)
    {
        $this->dto = $dto;
    }
    
    /**
     * Получить источник данных
     * 
     * @return Data_Source
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }
    
    /**
     * Получить объект передачи данных
     * 
     * @return Data_Scheme_Dto
     */
    public function getDto()
    {
        return $this->dto;
    }
    
    /**
     * Получить поля схемы
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->dto->fields ?: array();
    }
    
    /**
     * Получить индексы схемы
     * 
     * @return array
     */
    public function getIndexes()
    {
        return $this->dto->indexes ?: array();
    }
    
    /**
     * Информация о схеме модели
     * 
     * @return array
     */
    public function getInfo()
    {
        return $this->dto->info ?: array();
    }
    
    /**
     * Получить имя модели
     * 
     * @return string
     */
    public function getModelName()
    {
        return $this->dto->modelName;
    }
    
    /**
     * Изменить источник данных
     * 
     * @param Data_Source $dataSource
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
    }
    
    /**
     * Изменить объект передачи данных
     * 
     * @param Data_Scheme_Dto $dto
     */
    public function setDto($dto)
    {
        $this->dto = $dto;
    }
}