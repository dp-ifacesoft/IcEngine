<?php

/**
 * Хелпер для работы с аннотациями полей модели
 * 
 * @author morph
 * @Service("helperAnnotationModelField")
 * @Injectable
 */
class Helper_Annotation_Model_Field 
{
    /**
     * Получить аннотации полей Orm для указаной модели
     *
     * @param string $modelName
     * @Inject("helperAnnotation")
     * @return array
     */
    public function getAnnotations($modelName, $helperAnnotation)
    {
        $annotations = $helperAnnotation->getAnnotation($modelName)->getData();
        $annotationProperties = $annotations['properties'];
        $resultAnnotations = array();        
        foreach ($annotationProperties as $propertyName => $annotations) {
            if (!$annotations) {
                continue;
            }
            foreach ($annotations as $annotationName => $data) {
                if (strpos($annotationName, 'Orm') === false) {
                    continue;
                }
                $resultAnnotations[$propertyName][$annotationName] =
                    (array) $data;
            }
        }
        return $resultAnnotations;
    }
}