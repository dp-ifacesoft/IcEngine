<?php

/**
 * Хелпер по работе со схемой модели
 *
 * @author morph
 * @Service("helperModelScheme")
 */
class Helper_Model_Scheme extends Helper_Abstract
{
    /**
     * Хелпер по генерации кода
     *
     * @Inject("helperCodeGenerator")
     * @var Helper_Code_Generator
     */
    protected $helperCodeGenerator;

    /**
     * Входящий транспорт
     *
     * @var Data_Transport
     * @Service(
     *      "helperModelSchemeInput",
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
     * Создать пустую схему моделей
     *
     * @param string $modelName
     * @param Model_Scheme_Dto $dto
     */
    public function create($modelName, $dto)
    {
        $author = $dto->author ?: $this->input['author'];
        $comment = $dto->comment ?: $this->input['comment'];
        $parts = explode('/', str_replace('_', '/', $modelName));
        $lastName = array_pop($parts);
        $path = IcEngine::root() . 'Ice/Config/Model/Mapper' .
            ($parts ? '/' . implode('/', $parts) : '');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $filename = $path . '/' . $lastName. '.php';
        $output = $this->getService('helperCodeGenerator')->fromTemplate(
            'scheme', array_merge($dto->getFields(), array(
                'author' => $author, 'comment' => $comment
            ))
        );
        file_put_contents($filename, $output);
    }

    /**
     * Создает dto для создания пустой схемы модели
     *
     * @return Model_Scheme_Dto
     */
    public function createDefaultDto()
    {
        $dto = $this->getService('dto')->newInstance('Model_Scheme');
        $dto->setFields(array(
            'id'    => array(
                'Int', array(
                    'Size'      => 11,
                    'Not_Null',
                    'Auto_Increment'
                )
            )
        ));
        $dto->setIndexes(array(
            'id'    => array('Primary', array('id'))
        ));
        return $dto;
    }

    /**
     * Создает dto для создания модели по схеме
     *
     * @param string $modelName
     * @return Model_Create_Dto
     */
    public function createDto($modelName)
    {
        $scheme = $this->getService('modelScheme')->scheme($modelName);
        $dto = $this->getService('dto')->newInstance('Model_Create')
            ->setAuthor($scheme->author)
            ->setComment($scheme->comment)
            ->setFields($this->getFields($modelName))
            ->setModelName($modelName);
        return $dto;
    }

    /**
     * Получить список полей для создания из схемы
     *
     * @param string $modelName
     * @return array
     */
    public function getFields($modelName)
    {
        $scheme = $this->getService('modelScheme')->scheme($modelName);
        $fields = array();
        foreach ($scheme->fields as $fieldName => $data) {
            $output = array();
            $size = !empty($data[1]) && !empty($data[1]['Size'])
                ? $data[1]['Size'] : 0;
            if ($size) {
                $output[] = 'Size=' . $size;
            }
            $unsigned = !empty($data[1]) && in_array('Unsigned', $data[1]);
            if ($unsigned) {
                $output[] = 'Unsigned';
            }
            $notNull = !empty($data[1]) && in_array('Not_Null', $data[1]);
            if (!$notNull) {
                $output[] = 'Not_Null';
            } else {
                $output[] = 'Null';
            }
            $autoIncrement = !empty($data[1]) &&
                in_array('Auto_Increment', $data[1]);
            if ($autoIncrement) {
                $output[] = 'Auto_Increment';
            }
            $comment = !empty($data[1]) && !empty($data[1]['Comment'])
                ? $data[1]['Comment'] : '';
            $fields[$fieldName] = array(
                'type'          => $data[0],
                'size'          => $size,
                'field'         => $fieldName,
                'unsigned'      => $unsigned,
                'notNull'       => $notNull,
                'autoIncrement' => $autoIncrement,
                'comment'       => $comment,
                'output'        => $output ? implode(', ', $output) : null,
                'indexes'       => array()
            );
        }
        foreach ($scheme->indexes as $indexName => $data) {
            foreach ($data[1] as $field) {
                $fields[$field]['indexes'][$data[0]][] = $indexName;
            }
        }
        return $fields;
    }

    /**
     * Изменить хелпер по генерации кода
     *
     * @param Helper_Code_Generator $helperCodeGenerator
     */
    public function setHelperCodeGenerator($helperCodeGenerator)
    {
        $this->helperCodeGenerator = $helperCodeGenerator;
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