<?php

/**
 * Аннотация модели Orm\Index
 * 
 * @author morph
 */
class Annotation_Model_Orm_Index extends Annotation_Model_Abstract
{
    /**
     * @inheritdoc
     */
    protected $field = 'indexes';
    
    /**
     * @inheritdoc
     */
    public function compare($annotationValue, $schemeValue)
    {
        $annotationIndexesKeys = array();
        $schemeIndexesKeys = array();
        foreach ($annotationValue as $index) {
            $indexFields = $index->getFields();
            sort($indexFields);
            $annotationIndexesKeys[implode('/', $indexFields)] = 
                $index->getType();
        }
        foreach ($schemeValue as $schemeIndex) {
            $schemeIndexFields = $schemeIndex[1];
            sort($schemeIndexFields);
            $schemeIndexesKeys[implode('/', $schemeIndexFields)] =
                $schemeIndex[0];
        }
        return !(
            array_diff($annotationIndexesKeys, $schemeIndexesKeys) ||
            array_diff(
                array_keys($annotationIndexesKeys),
                array_keys($schemeIndexesKeys)
            )
        );
    }
    
    /**
     * @inheritdoc
     */
    public function convertValue($dto, $scheme)
    {
        $schemeIndexes = array();
        foreach ($dto->indexes as $index) {
            $schemeIndexes[$index->getName()] = array(
                $index->getType(), $index->getFields()
            );
        }
        return $schemeIndexes;
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
        $preIndexes = array();
        foreach ($data as $properyName => $annotations) {
            foreach ($annotations as $annotationName => $annotation) {
                $arrayAnnotation = (array) $annotation;
                $annotation = reset($arrayAnnotation);
                if (strpos($annotationName, 'Orm\\Index') === false) {
                    continue;
                }
                $indexNames = (array) $properyName;
                if (is_array($annotation)) {
                    $indexNames = $annotation;
                }
                foreach ($indexNames as $indexName) {
                    if (!isset($preIndexes[$indexName])) {
                        list(,,$type) = explode('\\', $annotationName);
                        $preIndexes[$indexName] = array(
                            $type, array($properyName)
                        );
                    } else {
                        $preIndexes[$indexName][1][] = $properyName;
                    }
                }
            }
        }
        $resultIndexes = array();
        foreach ($preIndexes as $indexName => $data) {
            $index = new Model_Index($indexName);
            $index
                ->setType($data[0])
                ->setFields($data[1]);
            $resultIndexes[$indexName] = $index;
        }
        return $resultIndexes;
    }
}