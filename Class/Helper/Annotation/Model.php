<?php

/**
 * Хелпер для аннотаций модели
 * 
 * @author morph
 * @Service("helperAnnotationModel")
 * @Injectible
 */
class Helper_Annotation_Model
{
    /**
     * Аннотации для получения
     */
    protected static $annotations = array(
        'Orm_Field', 'Orm_Index', 'Orm_Reference', 'Orm_Signal'
    );
    
    /**
     * Получить список аннотаций модели
     * 
     * @Inject("annotationModelManager")
     */
    public function getList($annotationModelManager)
    {
        $annotations = array();
        foreach (self::$annotations as $annotation) {
            $annotation = $annotationModelManager->get($annotation);
            $annotations[] = $annotation;
        }
        return $annotations;
    }
}