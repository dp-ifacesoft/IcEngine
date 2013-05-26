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
        $helperModelMigrateSync = $this->getService('helperModelMigrateSync');
        $annotationFields = $helperModelMigrateSync->getAnnotationFields(
            $className
        );
        $annotationIndexes = $helperModelMigrateSync->getAnnotationIndexes(
            $className
        );
        $annotationReferences = $helperModelMigrateSync
            ->getAnnotationReferences($className);
        $scheme = $this->getService('modelScheme')->scheme($className)
            ->__toArray();
        $isEqual = true;
        if (!$this->compareFields($annotationFields, $scheme['fields'])) {
            $isEqual = false;
        } elseif (!$this->compareIndexes(
            $annotationIndexes, !empty($scheme['indexes']) 
            ? $scheme['indexes'] : array())) {
            $isEqual = false;
        } elseif (!$this->compareReferences(
            $annotationReferences, !empty($scheme['references']) 
            ? $scheme['references'] : array())) {
            $isEqual = false;
        }
        return $isEqual;
    }
    
    /**
     * Сравить поля схемы аннотации и схемы модели
     * 
     * @param array $annotationFields
     * @param array $schemeFields
     * @return boolean
     */
    public function compareFields($annotationFields, $schemeFields)
    {
        $annotationFieldsKeys = array_keys($annotationFields);
        $schemeFieldsKeys = array_keys($schemeFields);
        ksort($annotationFieldsKeys);
        ksort($schemeFieldsKeys);
        if (array_diff($annotationFieldsKeys, $schemeFieldsKeys)) {
            return false;
        }
        static $compareFieldArray = array(
            'Unsigned'          => Model_Field::ATTR_UNSIGNED, 
            'Binary'            => Model_Field::ATTR_BINARY, 
            'Auto_Increment'    => Model_Field::ATTR_AUTO_INCREMENT
        );
        foreach ($annotationFields as $fieldName => $field) {
            if (!isset($schemeFields[$fieldName])) {
                return false;
            }
            $schemeField = $schemeFields[$fieldName];
            $schemeFieldData = !empty($schemeField[1]) 
                ? $schemeField[1] : array();
            $field = $field->getAttrs();
            if ($schemeField[0] != $field[Model_Field::ATTR_TYPE]) {
                return false;
            } elseif (isset($schemeFieldData['Size']) &&
                $field[Model_Field::ATTR_SIZE] != $schemeFieldData['Size']) {
                return false;
            } elseif (empty($schemeFieldData['Comment']) && 
                !empty($field[Model_Field::ATTR_COMMENT])) {
                return false;
            } elseif (isset($schemeFieldData['Comment']) &&
                $field[Model_Field::ATTR_COMMENT] != 
                $schemeFieldData['Comment']) {
                return false;
            } elseif (!isset($schemeFieldData['Default']) &&
                isset($field[Model_Field::ATTR_DEFAULT])) {
                return false;
            } elseif (isset($schemeFieldData['Default']) &&
                $schemeFieldData['Default'] 
                != $field[Model_Field::ATTR_DEFAULT]) {
                return false;
            } elseif (in_array('Null', $schemeFieldData) && 
                !empty($field[Model_Field::ATTR_NOT_NULL])) {
                return false;
            } elseif (in_array('Not_Null', $schemeFieldData) && 
                !empty($field[Model_Field::ATTR_NULL])) {
                return false;
            } elseif (!$this->compareFieldArray(
                $field, $schemeFieldData, $compareFieldArray)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Сравнить атрибуты схемы и аннотации в прямом и обратном порядке по
     * массиву, переданному третьим аргументом
     * 
     * @param array $field
     * @param array $schemeFieldData
     * @param array $compareArray
     * @boolean
     */
    public function compareFieldArray($field, $schemeFieldData, $compareArray)
    {
        foreach ($compareArray as $attrScheme => $attrAnnotation) {
            if (!in_array($attrScheme, $schemeFieldData) && 
                !empty($field[$attrAnnotation])) {
                return false;
            } elseif (in_array($attrScheme, $schemeFieldData) &&
                empty($field[$attrAnnotation])) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Сравнить индекцы аннотации модели и индексы схемы модели
     * 
     * @param array $annotationIndexes
     * @param array $schemeIndexes
     * @return boolean
     */
    public function compareIndexes($annotationIndexes, $schemeIndexes)
    {
        $annotationIndexesKeys = array();
        $schemeIndexesKeys = array();
        foreach ($annotationIndexes as $index) {
            $indexFields = $index->getFields();
            sort($indexFields);
            $annotationIndexesKeys[implode('/', $indexFields)] = 
                $index->getType();
        }
        foreach ($schemeIndexes as $schemeIndex) {
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
     * Сравнить связи аннотации модели и связи схемы модели
     * 
     * @param array $annotationReferences
     * @param array $schemeReferences
     * @return boolean
     */
    public function compareReferences($annotationReferences, $schemeReferences)
    {
        ksort($annotationReferences);
        ksort($schemeReferences);
        $annotationReferencesKeys = array_keys($annotationReferences);
        $schemeReferencesKeys = array_keys($schemeReferences);
        if (array_diff($annotationReferencesKeys, $schemeReferencesKeys)) {
            return false;
        }
        return $annotationReferences == $schemeReferences;
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