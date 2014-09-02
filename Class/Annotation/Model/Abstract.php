<?php

/**
 * Абстрактная аннотация модели
 * 
 * @author morph
 */
abstract class Annotation_Model_Abstract
{
    /**
     * Поле аннотации
     * 
     * @var string
     */
    protected $field;
    
    /**
     * Сравнить схему аннотаций и схему конфига
     * 
     * @param array $annotationValue
     * @param array $schemeValue
     * @return boolean
     */
    public function compare($annotationValue, $schemeValue)
    {
        return true;
    }
    
    /**
     * Преобразовать значение для отправки в схему
     * 
     * @param Dto $dto
     * @param Config_Array $scheme
     * @return mixed
     */
    abstract public function convertValue($dto, $scheme);
    
    /**
     * Получить данные аннотации
     * 
     * @param string $modelName
     * @return array
     */
    abstract public function getData($modelName);
    
    /**
     * Получить поле аннотации
     * 
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}