<?php

/**
 * Хелпер для обработки аннотаций типа "Orm"
 * 
 * @author morph
 * @Service("helperAnnotationOrm")
 */
class Helper_Annotation_Orm extends Helper_Abstract
{
    /**
     * Сравнить схему моделей и схему аннотаций модели
     * 
     * @param string $className
     * @return boolean
     */
    public function compare($className)
    {
        $helperAnnotationModel = $this->getService('helperAnnotationModel');
        $annotations = $helperAnnotationModel->getList();
        $scheme = $this->getService('modelScheme')->scheme($className)
            ->__toArray();
        foreach ($annotations as $annotation) {
            $annotationValue = $annotation->getData($className);
            $field = $annotation->getField();
            $schemeValue = !empty($scheme[$field]) ? $scheme[$field] : array();
            if (!$annotation->compare($annotationValue, $schemeValue)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Пересобрать схему моделей
     * 
     * @param string $className
     * @param array $entity
     */
    public function rewriteModelScheme($className, $entity)
    {
        $scheme = $this->getService('modelScheme'); 
        $models = $scheme->getModels();
        $schemeModelName = strtolower($className);
        if (!isset($models[$schemeModelName])) {
            $models[$schemeModelName] = array();
        }
        foreach ($entity as $field => $value) {
            unset($entity[$field]);
            $entity[strtolower($field)] = $value;
        }
        $models[$schemeModelName] = array_merge(
            $models[$schemeModelName], $entity
        );
        ksort($models);
        $behavior = $scheme->getBehavior();
        $schemeConfig = $this->getService('configManager')->get(
            'Model_Scheme_' . $behavior
        );
        $default = $schemeConfig->default;
        $output = $this->getService('helperCodeGenerator')->fromTemplate(
            'modelScheme', array (
                'default'   => $default,
                'models'    => $models
            )
        );
        $result = array();
        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            $baseLine = $line;
            $line = str_replace(array("\n", "\r"), '', trim($line));
            if (!$line) {
                continue;
            }
            $result[] = $baseLine;
        }
        $filename = IcEngine::root() . 'Ice/Config/Model/Scheme/' . 
            $behavior . '.php';
        file_put_contents($filename, implode(PHP_EOL, $result));
    }
}