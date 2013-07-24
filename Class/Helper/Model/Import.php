<?php

/**
 * Хелпер для импорта модели
 * 
 * @author morph
 * @Service("helperModelImport")
 */
class Helper_Model_Import extends Helper_Abstract
{
    /**
     * Входящий транспорт
     * 
     * @var Data_Transport
     * @Service(
     *      "helperModelImportInput", 
     *      args={"cliInput"},
     *      isStatic=true,
     *      source={
     *          name="dataTransportManager",
     *          method="get"
     *      }
     * )
     */
    protected $input;
    
    /**
     * Получить поля
     * 
     * @param Data_Scheme $dto
     * @return array
     */
    public function getFields($dto)
    {
        $fields = array();
        foreach ($dto->fields as $fieldName => $field) {
            $output = array();
            if ($field->getSize()) {
                $output[] = 'Size=' . implode(',', (array) $field->getSize());
            }
            if ($field->getUnsigned()) {
                $output[] = 'Unsigned';
            }
            if (!$field->getNullable()) {
                $output[] = 'Not_Null';
            } else {
                $output[] = 'Null';
            }
            if ($field->getAutoIncrement()) {
                $output[] = 'Auto_Increment';
            }
            $fields[$fieldName] = array(
                'type'          => $field->getType(),
                'size'          => $field->getSize(),
                'field'         => $fieldName,
                'unsigned'      => $field->getUnsigned(),
                'notNull'       => !$field->getNullable(),
                'autoIncrement' => $field->getAutoincrement(),
                'comment'       => $field->getComment(),
                'output'        => $output ? implode(', ', $output) : null,
                'indexes'       => array()
            );
        }
        foreach ($dto->indexes as $indexName => $index) {
            foreach ($index->getFields() as $field) {
                $fields[$field]['indexes'][$index->getType()][] = $indexName;
            }
        }
        return $fields;
    }
    
    /**
     * Импорт модели
     * 
     * @param string $modelName
     */
    public function import($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataSchemeDto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName);
        $dataSource->getScheme(new Data_Scheme($dataSchemeDto));
        $fields = $this->getFields($dataSchemeDto);
        $author = $this->input['author'];
        $comment = $this->input['comment'] ?: 
            ($dataSchemeDto->info && !empty($dataSchemeDto->info['Comment'])
            ? $dataSchemeDto->info['Comment'] : '');
        $modelCreateDto = $this->getService('dto')
            ->newInstance('Model_Create')
            ->setAuthor($author)
            ->setWithoutTable(true)
            ->setComment($comment)
            ->setFields($fields)
            ->setModelName($modelName);
        $this->getService('helperModelCreate')->create($modelCreateDto);
    }
    
    /**
     * Изменить входящий транспорт
     * 
     * @param Data_Transport $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }
}