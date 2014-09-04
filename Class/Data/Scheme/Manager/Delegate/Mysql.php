<?php

/**
 * Делегат менеджера схем данных модели для работы с mysql
 * 
 * @author morph
 */
class Data_Scheme_Manager_Delegate_Mysql extends 
    Data_Scheme_Manager_Delegate_Abstract
{
    /**
     * @inheritdoc
     */
    public function getFields($modelName)
    {
        $fields = array();
        $table = $this->helper()->fields($this->table($modelName));
        foreach ($table as $field) {
            $size = 0;
            $typeParts = explode(' ', $field['Type']);
            $type = $typeParts[0];
            $unsigned = !empty($typeParts[1]) && 
                strtolower($typeParts[1]) == 'unsigned';
            $sizeStPos = strpos($type, '(');
            if ($sizeStPos !== false) {
                $sizeEndPos = strpos($type, ')');
                $size = substr(
                    $type, $sizeStPos + 1, $sizeEndPos - $sizeStPos - 1
                );
                $type = substr($type, 0, $sizeStPos);
                if (strpos($size, ',') !== false) {
                    $sizeParts = explode(',', $size);
                    $size = array_map('trim', $sizeParts);
                }
            }
            $newField = new Model_Field($field['Field']);
            $newField
                ->setType(ucfirst($type))
                ->setSize($size)
                ->setDefault($field['Default'])
                ->setAutoIncrement($field['Extra'] == 'auto_increment')
                ->setNullable($field['Null'] != 'NO')
                ->setComment($field['Comment'])
                ->setUnsigned($unsigned)
                ->setCollate($field['Collation']);
            $fields[$newField->getName()] = $newField;
        }
        return $fields;
    }
    
    /**
     * @inheritdoc
     */
    public function getIndexes($modelName)
    {
        $indexes = array();
        $table = $this->helper()->indexes($this->table($modelName));
        foreach ($table as $index) {
            $indexName = $index['Key_name'];
            $unique = !$index['Non_unique'];
            $primary = false;
            if ($indexName == 'PRIMARY') {
                $primary = true;
                $indexName = $index['Column_name'];
            }
            if (isset($indexes[$indexName])) {
                $indexes[$indexName]['columns'][] = $index['Column_name'];
            } else {
                $indexes[$indexName] = array(
                    'Type'      => $primary ? 'Primary' : (
                        $unique ? 'Unique' : 'Key'
                    ),
                    'Name'      => $indexName,
                    'Fields'   => array($index['Column_name'])
                );
            }
        }
        $resultIndexes = array();
        foreach ($indexes as $index) {
            $newIndex = new Model_Index($index['Name']);
            $newIndex
                ->setType($index['Type'])
                ->setFields($index['Fields']);
            $resultIndexes[$newIndex->getName()] = $newIndex;
        }
        return $resultIndexes;
    }
    
    /**
     * @inheritdoc
     */
    public function getInfo($modelName)
    {
        return $this->helper()->table($this->table($modelName));
    }
    
    /**
     * Получить хелпер для работы со схемой mysql
     * 
     * @return Helper_Mysql
     */
    protected function helper()
    {
        return IcEngine::serviceLocator()->getService('helperMysql');
    }
    
    /**
     * Получить имя таблицы по имени модели
     * 
     * @param string $modelName
     * @return string
     */
    protected function table($modelName)
    {
        $table = IcEngine::serviceLocator()->getService('modelScheme')
            ->table($modelName);
        return '`' . $table . '`';
    }
}