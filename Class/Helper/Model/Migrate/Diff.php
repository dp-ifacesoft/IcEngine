<?php

/**
 * Хелпер для определения различий между текущей схемой модели и схемой
 * это модели в источнике данных
 * 
 * @author morph
 * @Service("helperModelMigrateDiff")
 */
class Helper_Model_Migrate_Diff extends Helper_Abstract
{
    /**
     * Создать поле
     * 
     * @param string $modelName 
     * @param string $fieldName
     * @param array $fieldAttrs
     * @return Query_Abstract
     */
    public function addField($modelName, $fieldName, $fieldAttrs)
    {
        $newField = $this->newField($fieldName, $fieldAttrs);
        $query = $this->getService('query')
            ->alterTable($modelName)
            ->addField($newField);
        return $query;
    }
    
    /**
     *  Изменить поле
     * 
     * @param string $modelName
     * @param string $fieldName
     * @param array $fieldAttrs
     * @return Query_Abstract
     */
    public function changeField($modelName, $fieldName, $fieldAttrs)
    {
        $newField = $this->newField($fieldName, $fieldAttrs);
        $query = $this->getService('query')
            ->alterTable($modelName)
            ->changeField($newField, $fieldName);
        return $query;
    }
    
    /**
     * Найти различия текущей схемы и схемы источника
     * 
     * @param string $modelName
     */
    public function diff($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataSchemeDto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName);
        $dataScheme = new Data_Scheme($dataSchemeDto);
        $dataSource->getScheme($dataScheme);
        $status = $this->getService('helperModelMigrateSync')
            ->resync($modelName);
        if (!$status) {
            return false;
        }
        $currentScheme = $modelScheme->scheme($modelName);
        $currentSchemeFields = $currentScheme->fields->__toArray();
        $resultMigrations = array();
        $setStates = array();
        $annotation = $this->getService('annotationModelManager')
            ->get('Orm_Field');
        foreach ($currentSchemeFields as $fieldName => $fieldAttrs) {
            if (isset($fieldAttrs['Rename'])) {
                $setStates[$fieldAttrs['Rename']] = $fieldName;
            }
        }
        foreach ($dataSchemeDto->fields as $fieldName => $fieldAttrs) {
            if (isset($setStates[$fieldName])) {
                $resultMigrations[] = $this->changeField(
                    $modelName, $fieldName, 
                    $currentSchemeFields[$setStates[$fieldName]]
                );
                $dataSchemeDto->fields[$fieldName] = $fieldAttrs;
            } elseif (!isset($currentSchemeFields[$fieldName])) {
                $resultMigrations[] = $this->dropField($modelName, $fieldName);
            }
        }
        foreach ($currentSchemeFields as $fieldName => $fieldAttrs) {
            if (!isset($dataSchemeDto->fields[$fieldName])) {
                $resultMigrations[] = $this->addField(
                    $modelName, $fieldName, $fieldAttrs
                );
                continue;    
            }
            $newDto = array($fieldName => $dataSchemeDto->fields[$fieldName]);
            $newAttrs = array($fieldName => $fieldAttrs);
            if (!$annotation->compare($newDto, $newAttrs)) {
                $resultMigrations[] = $this->changeField(
                    $modelName, $fieldName, $currentSchemeFields[$fieldName]
                );
            }
        }
        return $resultMigrations;
    }
    
    /**
     * Удаление поля
     * 
     * @param string $modelName
     * @param string $fieldName
     * @return Query_Abstract
     */
    public function dropField($modelName, $fieldName)
    {
        $query = $this->getService('query')
            ->alterTable($modelName)
            ->dropField($fieldName);
        return $query;
    }

    /**
     * Создать новое поле модели
     * 
     * @param string $fieldName
     * @param array $fieldAttrs
     * @return \Model_Field
     */
    public function newField($fieldName, $fieldAttrs)
    {
        if (is_object($fieldAttrs)) {
            return $fieldAttrs;
        }
        $newField = new Model_Field($fieldName);
        $newField->setType($fieldAttrs[0]);
        if (!empty($fieldAttrs[1]['Size'])) {
            $newField->setSize($fieldAttrs[1]['Size']);
        }
        $newField->setNullable(in_array('Not_Null', $fieldAttrs[1]));
        $newField->setAutoIncrement(
            in_array('Auto_Icrement', $fieldAttrs[1], true)
        );
        $newField->setUnsigned(in_array('Unsigned', $fieldAttrs[1], true));
        if (!empty($fieldAttrs[1]['Comment'])) {
            $newField->setComment($fieldAttrs[1]['Comment']);
        }
        if (isset($fieldAttrs[1]['Default'])) {
            $newField->setDefault($fieldAttrs[1]['Default']);
        }
        return $newField;
    }
}