<?php

/**
 * Абстрактный мета-класс
 * 
 * @author morph
 */
class Meta
{
    /**
     * Аннотации класса
     * 
     * @var array
     */
    public static $classAnnotations = array();

    /**
     * Аннотации методов
     * 
     * @var array
     */
    public static $methodsAnnotations = array();

    /**
     * Аннотации свойств
     * 
     * @var array
     */
    public static $propertiesAnnotations = array();
    
    /**
     * Получить сет аннотаций
     * 
     * @return Annotation_Set
     */
    public static function getAnnotationSet()
    {
        return new Annotation_Set(
            static::$classAnnotations, 
            static::$methodsAnnotations,
            static::$propertiesAnnotations
        );
    }
    
    /**
     * Получить мета данные
     * 
     * @return arrya
     */
    public static function getData()
    {
        return array(
            'class'         => static::$classAnnotations,
            'methods'       => static::$methodsAnnotations,
            'properties'    => static::$propertiesAnnotations
        );
    }
}