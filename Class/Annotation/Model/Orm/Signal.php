<?php

/**
 * Аннотация модели Orm\Signal
 * 
 * @author morph
 */
class Annotation_Model_Orm_Signal extends Annotation_Model_Abstract
{
    /**
     * @inheritdoc
     */
    protected $field = 'signals';
    
    /**
     * @inheritdoc
     */
    public function compare($annotationValue, $schemeValue)
    {
        $annotationSignalsKeys = array_keys($annotationValue);
        $schemeSignalKeys = array_keys($schemeValue);
        if (array_diff($annotationSignalsKeys, $schemeSignalKeys) ||
            count($annotationSignalsKeys) != count($schemeSignalKeys)) {
            return false;
        }
        foreach ($annotationValue as $signalName => $annotationSignal) {
            $schemeSignal = $schemeValue[$signalName];
            if (array_diff($annotationSignal, $schemeSignal) ||
                count($annotationSignal) != count($schemeSignal)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function convertValue($dto, $scheme)
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperConverter = $serviceLocator->getService('helperConverter');
        if ($dto->signals) {
            return $helperConverter->arrayToString($dto->signals);
        }
        return array();
    }
    
    /**
     * @inheritdoc
     */
    public function getData($modelName)
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperAnnotation = $serviceLocator->getService('helperAnnotation');
        $annotations = $helperAnnotation->getAnnotation($modelName)->getData();
        $classData = $annotations['class'];
        $result = array();
        foreach ($classData as $annotationName => $data) {
            if (strpos($annotationName, 'Orm\\After') !== false ||
                strpos($annotationName, 'Orm\\Before') !== false ||
                strpos($annotationName, 'Orm\\On') !== false) {
                $parts = explode('\\', $annotationName);
                array_shift($parts);
                $parts[0] = lcfirst($parts[0]);
                $signalCategory = implode('', $parts);
                $result[$signalCategory] = reset($data);
            }
        }
        return $result;
    }
}