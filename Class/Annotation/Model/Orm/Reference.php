<?php

/**
 * Аннотация модели Orm\Reference
 * 
 * @author morph
 */
class Annotation_Model_Orm_Reference extends Annotation_Model_Abstract
{
    /**
     * @inheritdoc
     */
    protected $field = 'references';
    
    /**
     * @inheritdoc
     */
    public function compare($annotationValue, $schemeValue)
    {
        ksort($annotationValue);
        ksort($schemeValue);
        $annotationReferencesKeys = array_keys($annotationValue);
        $schemeReferencesKeys = array_keys($schemeValue);
        if (array_diff($annotationReferencesKeys, $schemeReferencesKeys) ||
            count($annotationReferencesKeys) != count($schemeReferencesKeys)) {
            return false;
        }
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function convertValue($dto, $scheme)
    {
        return $dto->references ?: $scheme->references;
    }
    
    /**
     * @inheritdoc
     */
    public function getData($modelName)
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperAnnotationModelField = $serviceLocator->getService(
            'helperAnnotationModelField'
        );
        $data = $helperAnnotationModelField->getAnnotations($modelName);
        $resultReferences = array();
        foreach ($data as $properyName => $annotations) {
            foreach ($annotations as $annotationName => $annotation) {
                $arrayAnnotation = (array) $annotation;
                $annotation = reset($arrayAnnotation);
                if (strpos($annotationName, 'Orm\\Reference') === false) {
                    continue;
                }
                list(,,$type) = explode('\\', $annotationName);
                $reference = array(
                    $type, array()
                );
                $reference[1]['Target'] = $annotation['Target'];
                if (isset($annotation['JoinColumn'])) {
                    if (isset($annotation['JoinColumn']['on'])) {
                        $reference[1]['JoinColumn'] = array(
                            0       => reset($annotation['JoinColumn']),
                            'on'    => $annotation['JoinColumn']['on']
                        );
                    } else {
                        $reference[1]['JoinColumn'] = $annotation['JoinColumn'];
                    }
                }
                if (isset($annotation['JoinTable'])) {
                    $reference[1]['JoinTable'] = $annotation['JoinTable'];
                }
                $resultReferences[$properyName] = $reference;
            }
        }
        return $resultReferences;
    }
}