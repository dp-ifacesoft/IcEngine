<?php

/**
 * Схема-посредник связей модели
 * 
 * @author morph
 * @Service("modelMapperScheme", disableConstruct=true)
 */
class Model_Mapper_Scheme 
{
    /**
     * Модель схемы
     * 
     * @var Model
     * @Generator 
     */
    protected $model;
    
    /**
     * Менеджер связей моделей
     * 
     * @var Model_Mapper_Reference
     * @Generator
     * @Inject
     */
    protected $modelMapperReference;
    
    /**
     * Связи
     * 
     * @var array
     * @Generator
     */
    protected $references;
    
    /**
     * Конструктор
     * 
     * @param Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }
    
    /**
     * Получить связь по имени (объект состояния)
     * 
     * @param string $propertyName
     * @return mixed
     */
    public function get($propertyName)
    {
        return isset($this->references[$propertyName])
            ? $this->references[$propertyName]->execute() : null;
    }
    
    /**
     * Изменить данные о связи
     * 
     * @param string $propertyName
     * @param array $referenceData
     */
    public function set($propertyName, $referenceData)
    {
        $referenceName = reset($referenceData);
        $referenceArgs = isset($referenceData[1]) ? $referenceData[1] : array();
        $reference = $this->modelMapperReference->get($referenceName);
        $reference->setModel($this->model);
        $reference->setField($propertyName);
        $reference->setArgs($referenceArgs);
        $this->references[$propertyName] = $reference;
    }
    
    /**
     * Getter for "model"
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
        
    /**
     * Setter for "model"
     *
     * @param Model model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    
    /**
     * Getter for "modelMapperReference"
     *
     * @return Model_Mapper_Scheme_Reference
     */
    public function getModelMapperReference()
    {
        return $this->modelMapperReference;
    }
        
    /**
     * Setter for "modelMapperReference"
     *
     * @param Model_Mapper_Scheme_Reference modelMapperReference
     */
    public function setModelMapperReference($modelMapperReference)
    {
        $this->modelMapperReference = $modelMapperReference;
    }
    
    
    /**
     * Getter for "references"
     *
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
        
    /**
     * Setter for "references"
     *
     * @param array references
     */
    public function setReferences($references)
    {
        $this->references = $references;
    }
    
}