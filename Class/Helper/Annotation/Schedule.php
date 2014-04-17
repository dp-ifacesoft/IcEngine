<?php

/**
 * Хелпер для обновления заданий планировщика
 * 
 * @author morph
 * @Service("helperAnnotationSchedule")
 */
class Helper_Annotation_Schedule extends Helper_Abstract
{
    /**
     * Получить дельту в секундах
     * 
     * @param array $scheduleData
     * @return integer 
     */
    public function delta($scheduleData)
    {
        $interval = substr($scheduleData['interval'], 1, -1);
        $multiplier = substr($scheduleData['interval'], -1);
        switch(strtolower($multiplier)) {
            case 'd': $multiplier = 86400; break;
            case 'h': $multiplier = 3600; break;
            case 'm': $multiplier = 60; break;
            default:  $multiplier = 1;
        }
        $deltaSec = intval($interval) * $multiplier;
        return $deltaSec;
    }
    
    /**
     * Получить имя сервиса по имени класса
     * 
     * @param string $className
     * @return array
     */
    public function getName($className)
    {
        $controllerManager = $this->getService('controllerManager');
        $annotationManager = $controllerManager->getAnnotationManager();
        $annotation = $annotationManager->getAnnotation($className)
            ->getData()['class'];
        if (!isset($annotation['Service'])) {
            return null;
        }
        return !empty($annotation['Service'][0])
            ? reset($annotation['Service'][0]) : null;
    }
}